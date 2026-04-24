<?php
/**
 * JUSEA CMN v2.0 - Deployer de correcciones
 * Coloca este archivo en C:\xampp\htdocs\JuseaCMN_v2\
 * Acceder via: http://localhost/JuseaCMN_v2/deployer.php
 * BORRAR despues de ejecutar.
 */

$base = __DIR__;
$resultados = [];

// ============================================================
// ARCHIVO 1: PersonaModel.php
// Correccion: eliminar alpha_space y relajar validacion nombre
// ============================================================
$archivo1 = $base . '/app/Models/PersonaModel.php';
$contenido1 = <<<'PHP'
<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Personas
 * Gestiona infractores, instructores y autoridades.
 * Todas las consultas usan query builder (parametrizadas) para prevenir SQL injection.
 */
class PersonaModel extends Model
{
    protected $table         = 'personas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'dni', 'apellido', 'nombre', 'grado',
        'arma_especialidad', 'destino_interno', 'cargo', 'tipo', 'activo'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'dni'       => 'required|min_length[7]|max_length[10]|numeric',
        'apellido'  => 'required|min_length[2]|max_length[100]',
        'nombre'    => 'permit_empty|max_length[100]',
        'grado'     => 'permit_empty|max_length[80]',
        'tipo'      => 'required|in_list[cuadro,cadete,instructor,autoridad,civil]',
    ];

    protected $validationMessages = [
        'dni' => [
            'numeric'    => 'El DNI debe contener solo números.',
            'min_length' => 'El DNI debe tener al menos 7 dígitos.',
        ],
    ];

    public function buscarPorDni(string $dni): ?object
    {
        return $this->where('dni', $dni)
                    ->where('activo', 1)
                    ->first();
    }

    public function buscarPorApellido(string $apellido): array
    {
        return $this->like('apellido', $apellido, 'both')
                    ->where('activo', 1)
                    ->orderBy('apellido', 'ASC')
                    ->findAll();
    }

    public function buscarPorNombre(string $nombre, string $tipo = ''): array
    {
        $builder = $this->like('nombre', $nombre, 'both')->where('activo', 1);
        if (!empty($tipo)) {
            $builder = $builder->where('tipo', $tipo);
        }
        return $builder->orderBy('apellido', 'ASC')->findAll();
    }

    public function insertarOActualizar(array $data): int
    {
        $existente = $this->where('dni', $data['dni'])
                         ->where('tipo', $data['tipo'])
                         ->first();

        if ($existente) {
            $this->update($existente->id, $data);
            return (int) $existente->id;
        }

        $this->insert($data);
        return (int) $this->getInsertID();
    }

    public function obtenerPorTipo(string $tipo): array
    {
        return $this->where('tipo', $tipo)
                    ->where('activo', 1)
                    ->orderBy('apellido', 'ASC')
                    ->findAll();
    }
}
PHP;

// ============================================================
// ARCHIVO 2: SancionController.php
// Correccion: nombre_infractor/nombre_instructor no requeridos
// ============================================================
$archivo2 = $base . '/app/Controllers/SancionController.php';
$contenido2 = <<<'PHP'
<?php
namespace App\Controllers;

use App\Models\SancionModel;
use App\Models\EncabezadoModel;
use App\Libraries\DocumentGenerator;
use CodeIgniter\Controller;

class SancionController extends Controller
{
    protected $sancionModel;
    protected $encabezadoModel;

    public function __construct()
    {
        $this->sancionModel    = new SancionModel();
        $this->encabezadoModel = new EncabezadoModel();
    }

    public function formCuadros()
    {
        return view('layouts/main', [
            'contenido' => view('sancion/form_cuadros'),
            'titulo'    => 'Sanción Disciplinaria - Cuadros',
        ]);
    }

    public function formCadetes()
    {
        return view('layouts/main', [
            'contenido' => view('sancion/form_cadetes'),
            'titulo'    => 'Sanción Disciplinaria - Cadetes',
        ]);
    }

    public function guardarCuadros()
    {
        return $this->procesarSancion('cuadros');
    }

    public function guardarCadetes()
    {
        return $this->procesarSancion('cadetes');
    }

    private function procesarSancion(string $tipo): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = $this->getValidationRules();
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $datosInfractor = [
            'dni'               => trim($post['dni_infractor']),
            'apellido'          => mb_strtoupper(trim($post['apellido_infractor'])),
            'nombre'            => mb_strtoupper(trim($post['nombre_infractor'] ?? '')),
            'grado'             => trim($post['grado_infractor']),
            'arma_especialidad' => trim($post['arma_infractor'] ?? ''),
            'destino_interno'   => trim($post['destino_infractor'] ?? ''),
            'tipo'              => ($tipo === 'cadetes') ? 'cadete' : 'cuadro',
        ];

        $datosInstructor = [
            'dni'               => trim($post['dni_instructor']),
            'apellido'          => mb_strtoupper(trim($post['apellido_instructor'])),
            'nombre'            => mb_strtoupper(trim($post['nombre_instructor'] ?? '')),
            'grado'             => trim($post['grado_instructor']),
            'arma_especialidad' => '',
            'destino_interno'   => '',
            'cargo'             => trim($post['cargo_instructor'] ?? ''),
            'tipo'              => 'autoridad',
        ];

        $dniRevisor = trim($post['revisor_dni'] ?? $post['dni_revisor'] ?? '');
        $datosRevisor = null;
        if (!empty($dniRevisor)) {
            $datosRevisor = [
                'dni'               => $dniRevisor,
                'apellido'          => mb_strtoupper(trim($post['revisor_nombre'] ?? $post['apellido_revisor'] ?? '')),
                'nombre'            => '',
                'grado'             => trim($post['revisor_grado'] ?? $post['grado_revisor'] ?? ''),
                'arma_especialidad' => '',
                'destino_interno'   => '',
                'cargo'             => trim($post['revisor_cargo'] ?? $post['cargo_revisor'] ?? ''),
                'tipo'              => 'autoridad',
            ];
        }

        $datosInfraccion = [
            'fecha_comision'      => $post['fecha_comision'],
            'reg_act_dis'         => trim($post['reg_act_dis'] ?? ''),
            'inciso'              => trim($post['inciso'] ?? ''),
            'motivo'              => trim($post['motivo']),
            'tipo_sancion'        => trim($post['tipo_sancion_desc'] ?? ''),
            'duracion'            => trim($post['duracion'] ?? ''),
            'lugar_cumplimiento'  => trim($post['lugar_cumplimiento'] ?? ''),
            'cargo_autoridad'     => trim($post['cargo_instructor'] ?? ''),
        ];

        if ($datosRevisor) {
            $datosInfraccion['revisor_grado']  = $datosRevisor['grado'];
            $datosInfraccion['revisor_nombre'] = $datosRevisor['apellido'];
            $datosInfraccion['revisor_dni']    = $datosRevisor['dni'];
            $datosInfraccion['revisor_cargo']  = $datosRevisor['cargo'];
        }

        $resultado = $this->sancionModel->registrarSancionCompleta(
            $datosInfractor,
            $datosInstructor,
            $datosInfraccion,
            $tipo,
            (int) session()->get('usuario_id')
        );

        if (!$resultado['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar la sanción: ' . implode(', ', $resultado['errors']));
        }

        $ruta = ($tipo === 'cuadros') ? 'sancion/cuadros' : 'sancion/cadetes';

        return redirect()->to(site_url($ruta))
            ->with('success', 'Sanción registrada correctamente (ID: ' . $resultado['sancion_id'] . ')')
            ->with('sancion_id', $resultado['sancion_id'])
            ->with('sancion_tipo', $tipo);
    }

    public function descargarCuadros(int $id, string $formato = 'docx')
    {
        return $this->generarDocumento($id, 'cuadros', $formato);
    }

    public function descargarCadetes(int $id, string $formato = 'docx')
    {
        return $this->generarDocumento($id, 'cadetes', $formato);
    }

    public function descargarRevisionCuadros(int $id)
    {
        $sancion = $this->sancionModel->obtenerSancionCompleta($id);
        if (!$sancion) {
            return redirect()->back()->with('error', 'Sanción no encontrada.');
        }
        $encabezado = $this->encabezadoModel->obtenerVigente();
        $generator = new DocumentGenerator();
        return $generator->generarSancionRevisionCuadrosDocx($sancion, $encabezado);
    }

    private function generarDocumento(int $id, string $tipo, string $formato)
    {
        $sancion = $this->sancionModel->obtenerSancionCompleta($id);
        if (!$sancion) {
            return redirect()->back()->with('error', 'Sanción no encontrada.');
        }
        $encabezado = $this->encabezadoModel->obtenerVigente();
        $generator = new DocumentGenerator();
        return $generator->generarSancion($sancion, $encabezado, $tipo, $formato);
    }

    private function getValidationRules(): array
    {
        return [
            'dni_infractor'       => 'required|numeric|min_length[7]|max_length[10]',
            'apellido_infractor'  => 'required|min_length[2]',
            'grado_infractor'     => 'required',
            'fecha_comision'      => 'required|valid_date[Y-m-d]',
            'motivo'              => 'required|min_length[5]',
            'dni_instructor'      => 'required|numeric|min_length[7]|max_length[10]',
            'apellido_instructor' => 'required|min_length[2]',
            'grado_instructor'    => 'required',
        ];
    }
}
PHP;

// Escribir archivos
$archivos = [
    $archivo1 => $contenido1,
    $archivo2 => $contenido2,
];

foreach ($archivos as $ruta => $contenido) {
    $nombre = basename($ruta);
    if (file_put_contents($ruta, $contenido) !== false) {
        $resultados[] = ['archivo' => $nombre, 'ok' => true,  'msg' => 'OK'];
    } else {
        $resultados[] = ['archivo' => $nombre, 'ok' => false, 'msg' => 'Error al escribir'];
    }
}

// Limpiar cache de CI4
$cacheDir = $base . '/writable/cache/';
$cacheOk = false;
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '*') as $f) {
        if (is_file($f)) @unlink($f);
    }
    $cacheOk = true;
}

$hayError = array_filter($resultados, fn($r) => !$r['ok']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>JUSEA - Deployer</title>
    <style>
        body { font-family: monospace; background: #1a1a2e; color: #eee; padding: 40px; }
        h2   { color: #00d4aa; }
        table { border-collapse: collapse; margin-top: 20px; }
        td, th { padding: 8px 20px; border: 1px solid #444; text-align: left; }
        .ok  { color: #00d4aa; }
        .err { color: #ff6b6b; }
        .box { background: #16213e; padding: 20px; border-radius: 8px; margin-top: 20px; max-width: 600px; }
        .final-ok  { background: #0d3b2e; border: 1px solid #00d4aa; padding: 16px; border-radius: 6px; margin-top: 24px; }
        .final-err { background: #3b0d0d; border: 1px solid #ff6b6b; padding: 16px; border-radius: 6px; margin-top: 24px; }
    </style>
</head>
<body>
    <h2>JUSEA CMN v2.0 — Deployer de correcciones</h2>
    <div class="box">
        <table>
            <tr><th>Archivo</th><th>Resultado</th></tr>
            <?php foreach ($resultados as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['archivo']) ?></td>
                <td class="<?= $r['ok'] ? 'ok' : 'err' ?>">
                    <?= $r['ok'] ? '✔ ' : '✘ ' ?><?= htmlspecialchars($r['msg']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td>Cache CI4</td>
                <td class="<?= $cacheOk ? 'ok' : 'err' ?>"><?= $cacheOk ? '✔ OK' : '✘ No se pudo limpiar' ?></td>
            </tr>
        </table>

        <?php if (empty($hayError)): ?>
        <div class="final-ok">
            ✅ <strong>Correcciones aplicadas.</strong><br>
            Podés registrar sanciones ahora.<br><br>
            <strong>Borrá este archivo:</strong> <code>deployer.php</code>
        </div>
        <?php else: ?>
        <div class="final-err">
            ⚠️ Algunos archivos no se pudieron escribir. Verificá permisos de la carpeta XAMPP.
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
