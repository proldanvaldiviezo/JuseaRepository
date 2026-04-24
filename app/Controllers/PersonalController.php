<?php
namespace App\Controllers;

use App\Models\PersonaModel;
use App\Models\SancionModel;
use App\Models\ActuacionModel;
use App\Config\ActuacionesCampos;
use CodeIgniter\Controller;

/**
 * PersonalController — v2
 * Gestión del Padrón de Personal CMN.
 * CRUD + importación/exportación CSV + acción masiva + filtro por arma.
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
        $q      = trim($this->request->getGet('q')     ?? '');
        $tipo   = trim($this->request->getGet('tipo')  ?? '');
        $arma   = trim($this->request->getGet('arma')  ?? '');
        $activo = $this->request->getGet('activo') ?? '1';
        $vista  = $this->request->getGet('vista')  ?? 'tabla';   // tabla | agrupado
        $page   = max(1, (int)($this->request->getGet('page') ?? 1));
        $perPage = 25;

        $builder = $this->model->orderBy('tipo', 'ASC')->orderBy('apellido', 'ASC');

        if ($tipo !== '') {
            $builder->where('tipo', $tipo);
        }
        if ($arma !== '') {
            $builder->like('arma_especialidad', $arma, 'both');
        }
        if ($activo !== '') {
            $builder->where('activo', (int)$activo);
        }
        if ($q !== '') {
            $builder->groupStart()
                    ->like('apellido', $q, 'both')
                    ->orLike('nombre', $q, 'both')
                    ->orLike('grado', $q, 'both')
                    ->orLike('dni', $q, 'both')
                    ->orLike('destino_interno', $q, 'both')
                    ->orLike('cargo', $q, 'both')
                    ->groupEnd();
        }

        $total = $builder->countAllResults(false);

        // Vista agrupada: traer TODOS (sin paginación), agrupar en PHP
        if ($vista === 'agrupado') {
            $personas = $builder->findAll();
            $pager    = null;
            $agrupado = $this->agruparPorTipo($personas);
        } else {
            $personas = $builder->paginate($perPage, 'default', $page);
            $pager    = $this->model->pager;
            $agrupado = [];
        }

        // Stats rápidos
        $stats = [
            'total'      => $this->model->where('activo', 1)->countAllResults(),
            'cuadros'    => $this->model->where('tipo', 'cuadro')->where('activo', 1)->countAllResults(),
            'cadetes'    => $this->model->where('tipo', 'cadete')->where('activo', 1)->countAllResults(),
            'autoridades'=> $this->model->whereIn('tipo', ['instructor','autoridad'])->where('activo', 1)->countAllResults(),
            'civiles'    => $this->model->where('tipo', 'civil')->where('activo', 1)->countAllResults(),
            'inactivos'  => $this->model->where('activo', 0)->countAllResults(),
        ];

        // Lista de armas/especialidades distintas para el filtro
        $armasRows = $this->model->db->table('personas')
            ->select('arma_especialidad')->distinct()
            ->where('arma_especialidad !=', '')
            ->where('activo', 1)
            ->orderBy('arma_especialidad', 'ASC')
            ->get()->getResultArray();
        $armasList = array_column($armasRows, 'arma_especialidad');

        return view('layouts/main', [
            'titulo'    => 'Padrón de Personal CMN',
            'contenido' => view('personal/lista',
                compact('personas','pager','stats','q','tipo','arma','armasList',
                        'activo','total','perPage','vista','agrupado')),
        ]);
    }

    // =========================================================
    // NUEVO
    // =========================================================

    public function nuevo()
    {
        $this->requireAdmin();
        return view('layouts/main', [
            'titulo'    => 'Nuevo Integrante — Padrón CMN',
            'contenido' => view('personal/form', [
                'persona' => null,
                'grados'  => ActuacionesCampos::grados_completos(),
            ]),
        ]);
    }

    public function guardar()
    {
        $this->requireAdmin();

        if (!$this->validate($this->getValidationRules())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->model->db->table('personas')->insert($this->buildData($this->request->getPost()));
        return redirect()->to(site_url('personal'))
            ->with('success', 'Integrante registrado en el Padrón CMN.');
    }

    // =========================================================
    // EDITAR
    // =========================================================

    public function editar(int $id)
    {
        $this->requireAdmin();
        $persona = $this->model->find($id);
        if (!$persona) {
            return redirect()->to(site_url('personal'))->with('error', 'Registro no encontrado.');
        }
        return view('layouts/main', [
            'titulo'    => 'Editar Integrante — Padrón CMN',
            'contenido' => view('personal/form', [
                'persona' => $persona,
                'grados'  => ActuacionesCampos::grados_completos(),
            ]),
        ]);
    }

    public function actualizar(int $id)
    {
        $this->requireAdmin();
        if (!$this->model->find($id)) {
            return redirect()->to(site_url('personal'))->with('error', 'Registro no encontrado.');
        }
        if (!$this->validate($this->getValidationRules())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }
        $data       = $this->buildData($this->request->getPost());
        $data['id'] = $id;
        $this->model->save($data);
        return redirect()->to(site_url('personal'))
            ->with('success', 'Integrante actualizado correctamente.');
    }

    // =========================================================
    // DESACTIVAR / REACTIVAR (soft-delete individual)
    // =========================================================

    public function eliminar(int $id)
    {
        $this->requireAdmin();
        if (!$this->model->find($id)) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }
        $this->model->db->table('personas')->where('id', $id)->update(['activo' => 0]);
        return redirect()->to(site_url('personal'))
            ->with('success', 'Integrante desactivado.');
    }

    public function reactivar(int $id)
    {
        $this->requireAdmin();
        $this->model->db->table('personas')->where('id', $id)->update(['activo' => 1]);
        return redirect()->to(site_url('personal?activo=0'))
            ->with('success', 'Integrante reactivado correctamente.');
    }

    // =========================================================
    // ACCIÓN MASIVA (checkbox multiple)
    // =========================================================

    public function accionMasiva()
    {
        $this->requireAdmin();

        $ids    = $this->request->getPost('ids');     // array de IDs
        $accion = $this->request->getPost('accion');  // 'desactivar' | 'reactivar'

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'No se seleccionó ningún integrante.');
        }

        // Sanitizar: solo enteros positivos
        $ids = array_filter(array_map('intval', $ids), fn($i) => $i > 0);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'IDs inválidos.');
        }

        if (!in_array($accion, ['desactivar', 'reactivar'])) {
            return redirect()->back()->with('error', 'Acción no válida.');
        }

        $nuevoEstado = $accion === 'reactivar' ? 1 : 0;
        $this->model->db->table('personas')
            ->whereIn('id', $ids)
            ->update(['activo' => $nuevoEstado]);

        $verbo = $accion === 'reactivar' ? 'reactivado(s)' : 'desactivado(s)';
        return redirect()->to(site_url('personal'))
            ->with('success', count($ids) . ' integrante(s) ' . $verbo . ' correctamente.');
    }

    // =========================================================
    // HISTORIAL POR PERSONA
    // =========================================================

    /**
     * Muestra el historial de sanciones disciplinarias y actuaciones
     * administrativas (bienes + accidente/enfermedad) de una persona.
     *
     * Accesible a todos los roles autenticados (no requiere admin).
     */
    public function historial(int $id)
    {
        $persona = $this->model->find($id);
        if (!$persona) {
            return redirect()->to(site_url('personal'))
                ->with('error', 'Integrante no encontrado en el Padrón.');
        }

        // ── Sanciones disciplinarias (JOIN directo por FK) ────────────────────
        $sancionModel = new SancionModel();
        $sanciones    = $sancionModel->obtenerPorCausante($id);

        // ── Actuaciones por pérdida de bienes ────────────────────────────────
        $actuacionModel = new ActuacionModel();
        $bienes    = $actuacionModel->obtenerPorPersona(
            $persona->apellido,
            'bienes',
            $persona->dni ?? ''
        );

        // ── Actuaciones por accidente/enfermedad ──────────────────────────────
        $accidentes = $actuacionModel->obtenerPorPersona(
            $persona->apellido,
            'accidente',
            $persona->dni ?? ''
        );

        return view('layouts/main', [
            'titulo'    => 'Historial — ' . $persona->apellido . ($persona->nombre ? ', ' . $persona->nombre : ''),
            'contenido' => view('personal/historial', compact(
                'persona', 'sanciones', 'bienes', 'accidentes'
            )),
        ]);
    }

    // =========================================================
    // IMPORTAR CSV (actualización masiva)
    // =========================================================

    public function importar()
    {
        $this->requireAdmin();

        if ($this->request->getMethod() !== 'post') {
            return view('layouts/main', [
                'titulo'    => 'Importar Personal — Padrón CMN',
                'contenido' => view('personal/importar'),
            ]);
        }

        $archivo = $this->request->getFile('archivo_csv');
        if (!$archivo || !$archivo->isValid()) {
            return redirect()->back()->with('error', 'Archivo inválido o no recibido.');
        }
        $ext = strtolower($archivo->getExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return redirect()->back()->with('error', 'El archivo debe ser .csv.');
        }

        $handle = fopen($archivo->getTempName(), 'r');

        // Descartar BOM UTF-8
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        fgetcsv($handle); // Saltar encabezado

        $insertados   = 0;
        $actualizados = 0;
        $errores      = [];
        $fila         = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $fila++;
            $row = array_map('trim', $row);

            $dni = $row[0] ?? '';
            if (!is_numeric($dni) || strlen($dni) < 7 || strlen($dni) > 10) {
                $errores[] = "Fila {$fila}: DNI inválido «{$dni}»";
                continue;
            }

            $tipo = strtolower($row[7] ?? 'cuadro');
            if (!in_array($tipo, ['cuadro','cadete','instructor','autoridad','civil'])) {
                $tipo = 'cuadro';
            }

            $data = [
                'dni'               => $dni,
                'apellido'          => mb_strtoupper($row[1] ?? ''),
                'nombre'            => mb_strtoupper($row[2] ?? ''),
                'grado'             => $row[3] ?? '',
                'arma_especialidad' => $row[4] ?? '',
                'destino_interno'   => $row[5] ?? '',
                'cargo'             => $row[6] ?? '',
                'tipo'              => $tipo,
                'activo'            => 1,
            ];

            $existente = $this->model->where('dni', $dni)->where('tipo', $tipo)->first();
            if ($existente) {
                $this->model->db->table('personas')->where('id', $existente->id)->update($data);
                $actualizados++;
            } else {
                $this->model->db->table('personas')->insert($data);
                $insertados++;
            }
        }
        fclose($handle);

        $msg = "Importación completada: {$insertados} nuevo(s), {$actualizados} actualizado(s).";
        if ($errores) {
            $msg .= ' ' . count($errores) . ' fila(s) con error.';
        }

        return redirect()->to(site_url('personal'))
            ->with('success', $msg)
            ->with('import_errors', $errores);
    }

    // =========================================================
    // EXPORTAR CSV
    // =========================================================

    public function exportar()
    {
        $tipo   = trim($this->request->getGet('tipo')  ?? '');
        $arma   = trim($this->request->getGet('arma')  ?? '');
        $q      = trim($this->request->getGet('q')     ?? '');
        $activo = $this->request->getGet('activo') ?? '1';

        $builder = $this->model->orderBy('tipo','ASC')->orderBy('apellido','ASC');
        if ($tipo   !== '') $builder->where('tipo', $tipo);
        if ($activo !== '') $builder->where('activo', (int)$activo);
        if ($arma   !== '') $builder->like('arma_especialidad', $arma, 'both');
        if ($q      !== '') {
            $builder->groupStart()
                    ->like('apellido', $q, 'both')
                    ->orLike('nombre', $q, 'both')
                    ->groupEnd();
        }
        $personas = $builder->findAll();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="padron_cmn_' . date('Ymd_His') . '.csv"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, ['DNI','APELLIDO','NOMBRE','GRADO','ARMA_ESPECIALIDAD','DESTINO_INTERNO','CARGO','TIPO']);

        foreach ($personas as $p) {
            fputcsv($out, [
                $p->dni,
                $p->apellido,
                $p->nombre            ?? '',
                $p->grado             ?? '',
                $p->arma_especialidad ?? '',
                $p->destino_interno   ?? '',
                $p->cargo             ?? '',
                $p->tipo,
            ]);
        }
        fclose($out);
        exit;
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================

    private function requireAdmin(): void
    {
        if (session()->get('usuario_rol') !== 'admin') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    private function getValidationRules(): array
    {
        return [
            'dni'      => 'required|min_length[7]|max_length[10]|numeric',
            'apellido' => 'required|min_length[2]|max_length[100]',
            'nombre'   => 'permit_empty|max_length[100]',
            'grado'    => 'permit_empty|max_length[80]',
            'tipo'     => 'required|in_list[cuadro,cadete,instructor,autoridad,civil]',
        ];
    }

    private function buildData(array $post): array
    {
        return [
            'dni'               => trim($post['dni']               ?? ''),
            'apellido'          => mb_strtoupper(trim($post['apellido'] ?? '')),
            'nombre'            => mb_strtoupper(trim($post['nombre']   ?? '')),
            'grado'             => trim($post['grado']             ?? ''),
            'arma_especialidad' => trim($post['arma_especialidad'] ?? ''),
            'destino_interno'   => trim($post['destino_interno']   ?? ''),
            'cargo'             => trim($post['cargo']             ?? ''),
            'tipo'              => trim($post['tipo']              ?? 'cuadro'),
            'activo'            => isset($post['activo']) ? 1 : 0,
        ];
    }

    /**
     * Agrupa un array de objetos persona por tipo.
     * Retorna ['cuadro' => [...], 'cadete' => [...], ...]
     */
    private function agruparPorTipo(array $personas): array
    {
        $grupos = [];
        foreach ($personas as $p) {
            $grupos[$p->tipo][] = $p;
        }
        // Orden canónico
        $orden = ['autoridad','instructor','cuadro','cadete','civil'];
        $result = [];
        foreach ($orden as $t) {
            if (!empty($grupos[$t])) {
                $result[$t] = $grupos[$t];
            }
        }
        return $result;
    }
}
