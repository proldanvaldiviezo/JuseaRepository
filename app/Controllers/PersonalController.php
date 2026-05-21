<?php
namespace App\Controllers;

use App\Libraries\ApiCpsService;
use App\Models\PersonaModel;
use App\Models\SancionModel;
use App\Models\ActuacionModel;
use CodeIgniter\Controller;

/**
 * PersonalController — v4 (SQL Server / AspNetUsers)
 * Padrón de personal: alta desde APICPS, baja lógica (bajaUnidad).
 */
class PersonalController extends Controller
{
    protected PersonaModel $model;

    public function __construct()
    {
        $this->model = new PersonaModel();
    }

    // =========================================================
    // LISTADO CON FILTROS Y PAGINACIÓN
    // =========================================================

    public function index()
    {
        $q      = trim($this->request->getGet('q')    ?? '');
        $arma   = trim($this->request->getGet('arma') ?? '');
        $activo = $this->request->getGet('activo') ?? '1';
        $page   = max(1, (int)($this->request->getGet('page') ?? 1));
        $perPage = 25;

        $builder = $this->model->db->table('AspNetUsers')
            ->select('AspNetUsers.Id AS id,
                      AspNetUsers.DNI AS dni,
                      AspNetUsers.Apellido AS apellido,
                      AspNetUsers.Nombre AS nombre,
                      AspNetUsers.idGrado AS grado,
                      AspNetUsers.ARMA AS arma_especialidad,
                      AspNetUsers.display,
                      AspNetUsers.bajaUnidad');

        // Filtro activo/inactivo (bajaUnidad: 0=activo, 1=baja)
        if ($activo === '1') {
            $builder->where('AspNetUsers.bajaUnidad', 0);
        } elseif ($activo === '0') {
            $builder->where('AspNetUsers.bajaUnidad', 1);
        }

        if ($arma !== '') {
            $builder->like('AspNetUsers.ARMA', $arma, 'both');
        }

        if ($q !== '') {
            $builder->groupStart()
                    ->like('AspNetUsers.Apellido', $q, 'both')
                    ->orLike('AspNetUsers.Nombre', $q, 'both')
                    ->orLike('AspNetUsers.idGrado', $q, 'both')
                    ->orLike('AspNetUsers.DNI', $q, 'both')
                    ->groupEnd();
        }

        $builder->orderBy('AspNetUsers.Apellido', 'ASC');

        $total    = (clone $builder)->countAllResults();
        $offset   = ($page - 1) * $perPage;
        $personas = $builder->limit($perPage, $offset)->get()->getResultObject();

        $stats = [
            'total'     => $this->model->db->table('AspNetUsers')->where('bajaUnidad', 0)->countAllResults(),
            'inactivos' => $this->model->db->table('AspNetUsers')->where('bajaUnidad', 1)->countAllResults(),
        ];

        $armasRows = $this->model->db->table('AspNetUsers')
            ->select('ARMA')->distinct()
            ->where('ARMA IS NOT NULL', null, false)
            ->where("ARMA != ''")
            ->where('bajaUnidad', 0)
            ->orderBy('ARMA', 'ASC')
            ->get()->getResultArray();
        $armasList = array_column($armasRows, 'ARMA');

        return view('layouts/main', [
            'titulo'    => 'Padrón de Personal CMN',
            'contenido' => view('personal/lista',
                compact('personas', 'stats', 'q', 'arma', 'armasList',
                        'activo', 'total', 'perPage', 'page')),
        ]);
    }

    // =========================================================
    // HISTORIAL POR PERSONA
    // =========================================================

    public function historial(string $id)
    {
        $persona = $this->model->obtenerPorId($id);
        if (!$persona) {
            return redirect()->to(site_url('personal'))
                ->with('error', 'Integrante no encontrado.');
        }

        $sancionModel   = new SancionModel();
        $sanciones      = $sancionModel->obtenerPorCausante($id);

        $actuacionModel = new ActuacionModel();
        $bienes         = $actuacionModel->obtenerPorPersona(
            $persona->apellido ?? '',
            'bienes',
            $persona->dni ?? ''
        );
        $accidentes     = $actuacionModel->obtenerPorPersona(
            $persona->apellido ?? '',
            'accidente',
            $persona->dni ?? ''
        );

        return view('layouts/main', [
            'titulo'    => 'Historial — ' . ($persona->apellido ?? '') .
                           ($persona->nombre ? ', ' . $persona->nombre : ''),
            'contenido' => view('personal/historial',
                compact('persona', 'sanciones', 'bienes', 'accidentes')),
        ]);
    }

    // =========================================================
    // ALTA DESDE APICPS
    // =========================================================

    public function nuevo()
    {
        return view('layouts/main', [
            'titulo'    => 'Alta de Personal',
            'contenido' => view('personal/nuevo'),
        ]);
    }

    /**
     * AJAX — busca un DNI en APICPS y devuelve los datos del integrante.
     * GET personal/buscar-apicps?dni=XXXXX
     */
    public function buscarEnApicps()
    {
        $dni   = trim($this->request->getGet('dni') ?? '');
        $token = session()->get('apicps_token') ?? '';

        if ($dni === '' || !ctype_digit($dni)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'DNI inválido.']);
        }

        if ($token === '') {
            return $this->response->setJSON([
                'ok'  => false,
                'msg' => 'Sesión APICPS expirada. Por favor cierre sesión y vuelva a ingresar.',
            ]);
        }

        try {
            $apicps = new ApiCpsService();
            $datos  = $apicps->buscarPorDni($dni, $token);
        } catch (\RuntimeException $e) {
            log_message('error', '[PersonalController::buscarEnApicps] ' . $e->getMessage());
            return $this->response->setJSON([
                'ok'  => false,
                'msg' => 'No se pudo conectar con el servidor de autenticación.',
            ]);
        }

        if ($datos === null) {
            return $this->response->setJSON([
                'ok'  => false,
                'msg' => 'No se encontró ningún integrante con ese DNI en la APICPS.',
            ]);
        }

        // Informar si ya existe en el padrón local
        $yaExiste = $this->model->existePorDni($dni);

        return $this->response->setJSON([
            'ok'       => true,
            'datos'    => $datos,
            'yaExiste' => $yaExiste,
        ]);
    }

    /**
     * POST — guarda el integrante encontrado en APICPS en AspNetUsers.
     */
    public function guardar()
    {
        $dni = trim($this->request->getPost('dni') ?? '');

        if ($dni === '' || !ctype_digit($dni)) {
            return redirect()->to(site_url('personal/nuevo'))
                ->with('error', 'DNI inválido.');
        }

        if ($this->model->existePorDni($dni)) {
            return redirect()->to(site_url('personal'))
                ->with('error', 'El integrante con DNI ' . $dni . ' ya se encuentra en el padrón.');
        }

        $data = [
            'dni'      => $this->request->getPost('dni'),
            'nombre'   => $this->request->getPost('nombre'),
            'apellido' => $this->request->getPost('apellido'),
            'grado'    => $this->request->getPost('grado'),
            'arma'     => $this->request->getPost('arma'),
            'display'  => $this->request->getPost('display'),
        ];

        if (!$this->model->insertar($data)) {
            return redirect()->to(site_url('personal/nuevo'))
                ->with('error', 'Error al guardar el integrante. Intente nuevamente.');
        }

        return redirect()->to(site_url('personal'))
            ->with('success', 'Integrante ' . esc($data['apellido']) . ', ' . esc($data['nombre']) . ' dado de alta correctamente.');
    }

    // =========================================================
    // BAJA / REACTIVACIÓN LÓGICA
    // =========================================================

    public function darBaja(string $id)
    {
        $persona = $this->model->obtenerPorId($id);
        if (!$persona) {
            return redirect()->to(site_url('personal'))
                ->with('error', 'Integrante no encontrado.');
        }

        $this->model->darBaja($id);

        return redirect()->to(site_url('personal'))
            ->with('success', 'Integrante ' . esc($persona->apellido ?? $id) . ' dado de baja.');
    }

    public function reactivar(string $id)
    {
        $persona = $this->model->obtenerPorId($id);
        if (!$persona) {
            return redirect()->to(site_url('personal'))
                ->with('error', 'Integrante no encontrado.');
        }

        $this->model->reactivarPersona($id);

        return redirect()->to(site_url('personal?activo=0'))
            ->with('success', 'Integrante ' . esc($persona->apellido ?? $id) . ' reactivado.');
    }

    // =========================================================
    // EXPORTAR CSV (solo lectura)
    // =========================================================

    public function exportar()
    {
        $arma   = trim($this->request->getGet('arma')  ?? '');
        $q      = trim($this->request->getGet('q')     ?? '');
        $activo = $this->request->getGet('activo') ?? '1';

        $builder = $this->model->db->table('AspNetUsers')
            ->select('DNI, Apellido, Nombre, idGrado AS GRADO, ARMA, display, bajaUnidad')
            ->orderBy('Apellido', 'ASC');

        if ($activo === '1') $builder->where('bajaUnidad', 0);
        if ($activo === '0') $builder->where('bajaUnidad', 1);
        if ($arma !== '')    $builder->like('ARMA', $arma, 'both');
        if ($q !== '') {
            $builder->groupStart()
                    ->like('Apellido', $q, 'both')
                    ->orLike('Nombre', $q, 'both')
                    ->groupEnd();
        }

        $personas = $builder->get()->getResultObject();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="padron_cmn_' . date('Ymd_His') . '.csv"');
        header('Cache-Control: no-cache, must-revalidate');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, ['DNI', 'APELLIDO', 'NOMBRE', 'GRADO', 'ARMA', 'DISPLAY', 'BAJA']);
        foreach ($personas as $p) {
            fputcsv($out, [
                $p->DNI ?? '',
                $p->Apellido ?? '',
                $p->Nombre ?? '',
                $p->GRADO ?? '',
                $p->ARMA ?? '',
                $p->display ?? '',
                $p->bajaUnidad ? 'Sí' : 'No',
            ]);
        }
        fclose($out);
        exit;
    }
}
