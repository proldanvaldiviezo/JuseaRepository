<?php
namespace App\Controllers;

use App\Models\SancionModel;
use App\Models\EncabezadoModel;
use App\Libraries\DocumentGenerator;
use CodeIgniter\Controller;

/**
 * Controlador de Sanciones Disciplinarias
 * Gestiona sanciones tanto para Cuadros como para Cadetes.
 * Refactorizado: lógica unificada, validación server-side, método POST.
 */
class SancionController extends Controller
{
    protected $sancionModel;
    protected $encabezadoModel;

    public function __construct()
    {
        $this->sancionModel    = new SancionModel();
        $this->encabezadoModel = new EncabezadoModel();
    }

    // =========================================================
    // FORMULARIOS
    // =========================================================

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

    // =========================================================
    // GUARDAR (método unificado para cuadros y cadetes)
    // =========================================================

    public function guardarCuadros()
    {
        return $this->procesarSancion('cuadros');
    }

    public function guardarCadetes()
    {
        return $this->procesarSancion('cadetes');
    }

    /**
     * Lógica unificada para guardar sanción.
     * Elimina la duplicación de código del sistema original.
     */
    private function procesarSancion(string $tipo): \CodeIgniter\HTTP\RedirectResponse
    {
        // Validación server-side
        $rules = $this->getValidationRules();
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        // Preparar datos del infractor
        $datosInfractor = [
            'dni'               => trim($post['dni_infractor']),
            'apellido'          => mb_strtoupper(trim($post['apellido_infractor'])),
            'nombre'            => mb_strtoupper(trim($post['nombre_infractor'])),
            'grado'             => trim($post['grado_infractor']),
            'arma_especialidad' => trim($post['arma_infractor'] ?? ''),
            'destino_interno'   => trim($post['destino_infractor'] ?? ''),
            'tipo'              => ($tipo === 'cadetes') ? 'cadete' : 'cuadro',
        ];

        // Preparar datos del instructor/autoridad
        $datosInstructor = [
            'dni'               => trim($post['dni_instructor']),
            'apellido'          => mb_strtoupper(trim($post['apellido_instructor'])),
            'nombre'            => mb_strtoupper(trim($post['nombre_instructor'] ?? '-')),
            'grado'             => trim($post['grado_instructor']),
            'arma_especialidad' => '',
            'destino_interno'   => '',
            'cargo'             => trim($post['cargo_instructor'] ?? ''),
            'tipo'              => 'autoridad',
        ];

        // Preparar datos del revisor (cadetes Y cuadros - para planilla ANEXO 2)
        // El campo DNI del revisor puede venir como 'revisor_dni' (cuadros form) o 'dni_revisor' (cadetes form)
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

        // Preparar datos de la infracción
        $datosInfraccion = [
            'fecha_comision'      => $post['fecha_comision'],
            'reg_act_dis'         => trim($post['reg_act_dis'] ?? ''),
            'inciso'              => trim($post['inciso'] ?? ''),
            'motivo'              => trim($post['motivo']),
            'tipo_sancion'        => trim($post['tipo_sancion_desc'] ?? ''),
            'duracion'            => trim($post['duracion'] ?? ''),
            'lugar_cumplimiento'  => trim($post['lugar_cumplimiento'] ?? ''),
            'cargo_autoridad'     => trim($post['cargo_instructor'] ?? ''),
            'letra'               => mb_strtoupper(trim($post['letra'] ?? '')),
            'nro'                 => trim($post['nro'] ?? ''),
        ];

        // Datos del revisor (tanto cuadros como cadetes)
        if ($datosRevisor) {
            $datosInfraccion['revisor_grado']   = $datosRevisor['grado'];
            $datosInfraccion['revisor_nombre']  = $datosRevisor['apellido'];
            $datosInfraccion['revisor_dni']     = $datosRevisor['dni'];
            $datosInfraccion['revisor_cargo']   = $datosRevisor['cargo'];
        }

        // Registrar con transacción
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

    // =========================================================
    // DESCARGAR DOCUMENTO
    // =========================================================

    /**
     * Descargar documento de sanción en formato docx o pdf.
     * @param int $id       ID de la sanción
     * @param string $formato  'docx' o 'pdf'
     */
    public function descargarCuadros(int $id, string $formato = 'docx')
    {
        return $this->generarDocumento($id, 'cuadros', $formato);
    }

    public function descargarCadetes(int $id, string $formato = 'docx')
    {
        return $this->generarDocumento($id, 'cadetes', $formato);
    }

    /**
     * Genera la Planilla de Revisión de Oficio (ANEXO 2) para una sanción de cuadros.
     */
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

    // =========================================================
    // VALIDACIÓN
    // =========================================================

    private function getValidationRules(): array
    {
        return [
            'dni_infractor'       => 'required|numeric|min_length[7]|max_length[10]',
            'apellido_infractor'  => 'required|min_length[2]',
            'nombre_infractor'    => 'required|min_length[1]',
            'grado_infractor'     => 'required',
            'fecha_comision'      => 'required|valid_date[Y-m-d]',
            'motivo'              => 'required|min_length[5]',
            'dni_instructor'      => 'required|numeric|min_length[7]|max_length[10]',
            'apellido_instructor' => 'required|min_length[2]',
            'nombre_instructor'   => 'required|min_length[1]',
            'grado_instructor'    => 'required',
        ];
    }

    // =========================================================
    // ELIMINAR (solo admin)
    // =========================================================

    public function eliminarCuadros(int $id)
    {
        return $this->eliminarSancion($id, 'cuadros');
    }

    public function eliminarCadetes(int $id)
    {
        return $this->eliminarSancion($id, 'cadetes');
    }

    private function eliminarSancion(int $id, string $tipo)
    {
        if (session()->get('usuario_rol') !== 'admin') {
            return redirect()->back()->with('error', 'Acción no autorizada.');
        }

        $sancion = $this->sancionModel->find($id);

        if (!$sancion) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }

        $this->sancionModel->delete($id);

        return redirect()
            ->to(site_url('historial'))
            ->with('success', 'Sanción #' . $id . ' eliminada correctamente.');
    }

}
