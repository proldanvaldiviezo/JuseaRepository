<?php
namespace App\Controllers;

use App\Models\PersonaModel;
use App\Models\SancionModel;
use App\Models\ActuacionModel;
use CodeIgniter\Controller;

/**
 * PersonalController — v3 (SQL Server / AspNetUsers)
 * Padrón de personal: solo lectura desde AspNetUsers.
 * Las altas/bajas/modificaciones se gestionan desde el sistema Partes.
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
                      AspNetUsers.GRADO AS grado,
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
                    ->orLike('AspNetUsers.GRADO', $q, 'both')
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
    // EXPORTAR CSV (solo lectura)
    // =========================================================

    public function exportar()
    {
        $arma   = trim($this->request->getGet('arma')  ?? '');
        $q      = trim($this->request->getGet('q')     ?? '');
        $activo = $this->request->getGet('activo') ?? '1';

        $builder = $this->model->db->table('AspNetUsers')
            ->select('DNI, Apellido, Nombre, GRADO, ARMA, display, bajaUnidad')
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
