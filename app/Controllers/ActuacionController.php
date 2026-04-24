<?php

namespace App\Controllers;

use App\Models\ActuacionModel;
use App\Config\ActuacionesCampos;
use App\Libraries\ActuacionDocumentGenerator;
use CodeIgniter\Controller;

/**
 * Controlador unificado de Actuaciones Administrativas.
 *
 * Maneja dos tipos de actuación:
 *   - 'bienes'    → Pérdida / Ruptura / Inutilización de Bienes del Estado
 *   - 'accidente' → Accidente / Enfermedad
 *
 * Rutas:
 *   GET  /actuacion/bienes
 *   POST /actuacion/bienes/guardar
 *   GET  /actuacion/accidente
 *   POST /actuacion/accidente/guardar
 *   GET  /actuacion/bienes/historial
 *   GET  /actuacion/accidente/historial
 *   GET  /actuacion/{tipo}/ver/{id}
 */
class ActuacionController extends Controller
{
    protected ActuacionModel $model;

    public function __construct()
    {
        $this->model = new ActuacionModel();
    }

    // =========================================================
    // FORMULARIO NUEVO
    // =========================================================

    public function formBienes()
    {
        return $this->mostrarFormulario('bienes');
    }

    public function formAccidente()
    {
        return $this->mostrarFormulario('accidente');
    }

    private function mostrarFormulario(string $tipo)
    {
        $meta     = ActuacionesCampos::meta($tipo);
        $secciones = ActuacionesCampos::secciones($tipo);

        return view('layouts/main', [
            'titulo'   => $meta['titulo_corto'],
            'contenido' => view('actuacion/form_actuacion', [
                'tipo'      => $tipo,
                'meta'      => $meta,
                'secciones' => $secciones,
                'datos'     => session()->getFlashdata('_ci_old_input') ?? [],
            ]),
        ]);
    }

    // =========================================================
    // GUARDAR
    // =========================================================

    public function guardarBienes()
    {
        return $this->procesarGuardado('bienes');
    }

    public function guardarAccidente()
    {
        return $this->procesarGuardado('accidente');
    }

    private function procesarGuardado(string $tipo)
    {
        $post = $this->request->getPost();

        // Recolectar todos los campos del formulario (excepto csrf y _tipo)
        $campos = [];
        foreach ($post as $key => $value) {
            if (in_array($key, ['csrf_test_name', '_tipo', '_method'])) continue;
            $campos[$key] = is_string($value) ? trim($value) : $value;
        }

        if (empty($campos)) {
            return redirect()->back()->withInput()->with('error', 'No se recibieron datos del formulario.');
        }

        $resultado = $this->model->registrar($tipo, $campos, (int) session()->get('usuario_id'));

        if (!$resultado['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar la actuación: ' . implode(', ', $resultado['errors']));
        }

        return redirect()
            ->to(site_url("actuacion/{$tipo}/historial"))
            ->with('success', 'Actuación registrada correctamente (ID: ' . $resultado['id'] . ')')
            ->with('actuacion_id', $resultado['id'])
            ->with('actuacion_tipo', $tipo);
    }

    // =========================================================
    // HISTORIAL
    // =========================================================

    public function historialBienes()
    {
        return $this->mostrarHistorial('bienes');
    }

    public function historialAccidente()
    {
        return $this->mostrarHistorial('accidente');
    }

    private function mostrarHistorial(string $tipo)
    {
        $meta       = ActuacionesCampos::meta($tipo);
        $actuaciones = $this->model->listarPorTipo($tipo);

        return view('layouts/main', [
            'titulo'   => 'Historial — ' . $meta['titulo_corto'],
            'contenido' => view('actuacion/historial_actuacion', [
                'tipo'        => $tipo,
                'meta'        => $meta,
                'actuaciones' => $actuaciones,
            ]),
        ]);
    }

    // =========================================================
    // GENERAR EXPEDIENTE (ZIP con todos los documentos)
    // =========================================================

    public function generarBienes(int $id)
    {
        return $this->descargarExpediente($id, 'bienes');
    }

    public function generarAccidente(int $id)
    {
        return $this->descargarExpediente($id, 'accidente');
    }

    private function descargarExpediente(int $id, string $tipo)
    {
        $actuacion = $this->model->obtenerConDatos($id);

        if (!$actuacion || $actuacion->tipo !== $tipo) {
            return redirect()->back()->with('error', 'Actuación no encontrada.');
        }

        try {
            $generator = new ActuacionDocumentGenerator();
            $zipPath   = $generator->generarExpediente($actuacion);

            if (!file_exists($zipPath)) {
                return redirect()->back()->with('error', 'Error al generar los documentos.');
            }

            // Agregar archivos adjuntos subidos por el usuario al expediente ZIP
            $adjDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'actuaciones'
                    . DIRECTORY_SEPARATOR . $tipo . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
            if (is_dir($adjDir)) {
                $adjFiles = glob($adjDir . '*');
                if (!empty($adjFiles)) {
                    $zipAdj = new \ZipArchive();
                    if ($zipAdj->open($zipPath) === true) {
                        foreach ($adjFiles as $adjFile) {
                            if (is_file($adjFile)) {
                                $zipAdj->addFile($adjFile, '00_ADJUNTOS/' . basename($adjFile));
                            }
                        }
                        $zipAdj->close();
                    }
                }
            }

            $zipNombre = basename($zipPath);

            return $this->response
                ->setHeader('Content-Type', 'application/zip')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $zipNombre . '"')
                ->setHeader('Content-Length', (string) filesize($zipPath))
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
                ->setBody(file_get_contents($zipPath));

        } catch (\Throwable $e) {
            log_message('error', '[ActuacionController::descargarExpediente] ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar los documentos: ' . $e->getMessage());
        }
    }

    // =========================================================
    // VER DETALLE
    // =========================================================

    public function verBienes(int $id)
    {
        return $this->mostrarDetalle($id, 'bienes');
    }

    public function verAccidente(int $id)
    {
        return $this->mostrarDetalle($id, 'accidente');
    }

    private function mostrarDetalle(int $id, string $tipo)
    {
        $actuacion = $this->model->obtenerConDatos($id);

        if (!$actuacion || $actuacion->tipo !== $tipo) {
            return redirect()->back()->with('error', 'Actuación no encontrada.');
        }

        $meta      = ActuacionesCampos::meta($tipo);
        $secciones = ActuacionesCampos::secciones($tipo);

        return view('layouts/main', [
            'titulo'   => 'Ver Actuación — ' . $meta['titulo_corto'],
            'contenido' => view('actuacion/form_actuacion', [
                'tipo'       => $tipo,
                'meta'       => $meta,
                'secciones'  => $secciones,
                'datos'      => $actuacion->campos,
                'solo_lectura' => true,
                'actuacion_id' => $id,
            ]),
        ]);
    }

    // =========================================================
    // ELIMINAR (solo admin)
    // =========================================================

    public function eliminarBienes(int $id)
    {
        return $this->eliminarActuacion($id, 'bienes');
    }

    public function eliminarAccidente(int $id)
    {
        return $this->eliminarActuacion($id, 'accidente');
    }

    private function eliminarActuacion(int $id, string $tipo)
    {
        if (session()->get('usuario_rol') !== 'admin') {
            return redirect()->back()->with('error', 'Acción no autorizada.');
        }

        $actuacion = $this->model->find($id);

        if (!$actuacion || $actuacion->tipo !== $tipo) {
            return redirect()->back()->with('error', 'Actuación no encontrada.');
        }

        $this->model->delete($id);

        return redirect()
            ->to(site_url("actuacion/{$tipo}/historial"))
            ->with('success', 'Actuación #' . $id . ' eliminada correctamente.');
    }

    // =========================================================
    // ADJUNTOS (subida de archivos adicionales para el expediente)
    // =========================================================

    public function subirAdjuntoBienes(int $id)       { return $this->procesarAdjunto($id, 'bienes'); }
    public function subirAdjuntoAccidente(int $id)    { return $this->procesarAdjunto($id, 'accidente'); }
    public function listarAdjuntosBienes(int $id)     { return $this->listarAdjuntos($id, 'bienes'); }
    public function listarAdjuntosAccidente(int $id)  { return $this->listarAdjuntos($id, 'accidente'); }
    public function eliminarAdjuntoBienes(int $id)    { return $this->eliminarAdjunto($id, 'bienes'); }
    public function eliminarAdjuntoAccidente(int $id) { return $this->eliminarAdjunto($id, 'accidente'); }

    private function procesarAdjunto(int $id, string $tipo)
    {
        $json = fn($data, $code = 200) => $this->response->setStatusCode($code)->setJSON($data);

        $actuacion = $this->model->find($id);
        if (!$actuacion || $actuacion->tipo !== $tipo) {
            return $json(['ok' => false, 'error' => 'Actuación no encontrada.'], 404);
        }

        $file = $this->request->getFile('adjunto');
        if (!$file || !$file->isValid()) {
            return $json(['ok' => false, 'error' => 'Archivo inválido o no recibido.'], 422);
        }
        if ($file->hasMoved()) {
            return $json(['ok' => false, 'error' => 'El archivo ya fue procesado.'], 422);
        }

        $ext     = strtolower($file->getClientExtension());
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'doc', 'docx'];
        if (!in_array($ext, $allowed)) {
            return $json(['ok' => false, 'error' => 'Tipo no permitido. Use: ' . implode(', ', $allowed)], 422);
        }
        if ($file->getSize() > 15 * 1024 * 1024) {
            return $json(['ok' => false, 'error' => 'El archivo excede 15 MB.'], 422);
        }

        $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'actuaciones'
             . DIRECTORY_SEPARATOR . $tipo . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeName = date('Ymd_His_') . mb_strtolower(
            preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientName())
        );
        $file->move($dir, $safeName);

        return $json([
            'ok'   => true,
            'file' => $safeName,
            'size' => round(filesize($dir . $safeName) / 1024, 1) . ' KB',
        ]);
    }

    private function listarAdjuntos(int $id, string $tipo)
    {
        $dir   = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'actuaciones'
               . DIRECTORY_SEPARATOR . $tipo . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
        $files = [];

        if (is_dir($dir)) {
            foreach (glob($dir . '*') as $f) {
                if (is_file($f)) {
                    $files[] = [
                        'name' => basename($f),
                        'size' => round(filesize($f) / 1024, 1) . ' KB',
                        'ts'   => filemtime($f),
                    ];
                }
            }
            usort($files, fn($a, $b) => $b['ts'] <=> $a['ts']);
        }

        return $this->response->setJSON(['ok' => true, 'files' => $files]);
    }

    private function eliminarAdjunto(int $id, string $tipo)
    {
        $json = fn($data, $code = 200) => $this->response->setStatusCode($code)->setJSON($data);

        $rol = session()->get('usuario_rol') ?? '';
        if (!in_array($rol, ['admin', 'jefe'])) {
            return $json(['ok' => false, 'error' => 'No autorizado.'], 403);
        }

        $body   = $this->request->getJSON(true) ?? [];
        $nombre = basename($body['nombre'] ?? $this->request->getPost('nombre') ?? '');

        if (empty($nombre) || $nombre === '..' || $nombre === '.') {
            return $json(['ok' => false, 'error' => 'Nombre de archivo inválido.'], 422);
        }

        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'actuaciones'
              . DIRECTORY_SEPARATOR . $tipo . DIRECTORY_SEPARATOR . $id
              . DIRECTORY_SEPARATOR . $nombre;

        if (!file_exists($path)) {
            return $json(['ok' => false, 'error' => 'Archivo no encontrado.'], 404);
        }

        unlink($path);
        return $json(['ok' => true]);
    }

}
