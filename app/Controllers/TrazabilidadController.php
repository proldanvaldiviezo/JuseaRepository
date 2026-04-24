<?php
namespace App\Controllers;

use App\Models\TrazabilidadModel;
use App\Models\SancionModel;
use App\Models\ActuacionModel;
use CodeIgniter\Controller;

/**
 * TrazabilidadController
 * Gestiona el seguimiento de expedientes entre áreas institucionales.
 *
 * Flujos:
 *  - Sanciones disciplinarias: Personal → Autoridad → (Personal/Archivo)
 *  - Actuaciones Accidente/Enf.: Sanidad → Personal → Actuante → Personal → (Archivo)
 *  - Actuaciones Bienes: Unidad Requirente → Materiales → Personal → Actuante → Materiales → (Archivo)
 */
class TrazabilidadController extends Controller
{
    protected TrazabilidadModel $model;

    public function __construct()
    {
        $this->model = new TrazabilidadModel();
    }

    // =========================================================
    // PANEL GLOBAL
    // =========================================================

    /**
     * Panel de trazabilidad: todos los expedientes activos agrupados por área.
     */
    public function panel()
    {
        $tipo = trim($this->request->getGet('tipo') ?? '');
        $area = trim($this->request->getGet('area') ?? '');

        $filas = $this->model->panelActivos($tipo ?: null, $area ?: null);

        // Enriquecer cada fila con datos del expediente origen
        foreach ($filas as &$fila) {
            $exp = $this->resolverExpediente($fila['tipo_expediente'], (int) $fila['id_expediente']);
            $fila['causante']    = $exp['causante']   ?? '—';
            $fila['tipo_label']  = $exp['tipo_label'] ?? TrazabilidadModel::labelTipo($fila['tipo_expediente']);
            $fila['nro_display'] = $fila['nro_expediente'] ?: ($exp['nro'] ?? "ID {$fila['id_expediente']}");
            $fila['dias']        = TrazabilidadModel::diasEntre($fila['fecha_entrada']);
            $fila['estilo']      = TrazabilidadModel::estiloArea($fila['area']);
        }
        unset($fila);

        // Agrupar por área para la vista
        $porArea = [];
        foreach ($filas as $f) {
            $porArea[$f['area']][] = $f;
        }

        // Contadores por tipo
        $totales = [];
        foreach (['sancion_cuadros','sancion_cadetes','actuacion_bienes','actuacion_accidente'] as $t) {
            $totales[$t] = count(array_filter($filas, fn($f) => $f['tipo_expediente'] === $t));
        }

        $todasLasAreas = TrazabilidadModel::todasLasAreas();

        return view('layouts/main', [
            'titulo'    => 'Trazabilidad de Expedientes',
            'contenido' => view('trazabilidad/panel',
                compact('porArea', 'totales', 'tipo', 'area', 'filas', 'todasLasAreas')),
        ]);
    }

    // =========================================================
    // TIMELINE DE UN EXPEDIENTE
    // =========================================================

    /**
     * Muestra la cadena completa de pases de un expediente.
     * URL: /trazabilidad/ver/{tipo}/{id}
     *
     * @param string $tipo  sancion_cuadros | sancion_cadetes | actuacion_bienes | actuacion_accidente
     * @param int    $id    PK del expediente en su tabla origen
     */
    public function ver(string $tipo, int $id)
    {
        $tiposValidos = ['sancion_cuadros','sancion_cadetes','actuacion_bienes','actuacion_accidente'];
        if (!in_array($tipo, $tiposValidos, true)) {
            return redirect()->to(site_url('trazabilidad'))
                ->with('error', 'Tipo de expediente no válido.');
        }

        $expediente = $this->resolverExpediente($tipo, $id);
        if ($expediente === null) {
            return redirect()->to(site_url('trazabilidad'))
                ->with('error', 'Expediente no encontrado en el sistema.');
        }

        $cadena      = $this->model->obtenerCadena($tipo, $id);
        $pasoActual  = $this->model->obtenerPasoActual($tipo, $id);
        $areasDisp   = TrazabilidadModel::areasParaTipo($tipo);
        $estadosPaso = TrazabilidadModel::estadosPaso();
        $tipoLabel   = TrazabilidadModel::labelTipo($tipo);

        return view('layouts/main', [
            'titulo'    => 'Trazabilidad — ' . ($expediente['nro'] ?? "Exp.#{$id}"),
            'contenido' => view('trazabilidad/timeline',
                compact('tipo', 'id', 'expediente', 'cadena', 'pasoActual',
                        'areasDisp', 'estadosPaso', 'tipoLabel')),
        ]);
    }

    // =========================================================
    // REGISTRAR PASE (POST)
    // =========================================================

    /**
     * Registra un nuevo pase de área para un expediente.
     * Cierra automáticamente el paso activo anterior.
     */
    public function registrarPaso()
    {
        $post  = $this->request->getPost();
        $tipo  = trim($post['tipo_expediente'] ?? '');
        $idExp = (int) ($post['id_expediente'] ?? 0);

        if (!$tipo || !$idExp) {
            return redirect()->back()->with('error', 'Datos de expediente incompletos.');
        }

        // Validar área
        $areas = TrazabilidadModel::areasParaTipo($tipo);
        $area  = trim($post['area'] ?? '');
        if (!array_key_exists($area, $areas)) {
            return redirect()->back()->with('error', "Área «{$area}» no válida para este tipo de expediente.");
        }

        // Validar fecha
        $fechaEntrada = trim($post['fecha_entrada'] ?? '');
        if (!$fechaEntrada) {
            $fechaEntrada = date('Y-m-d H:i:s');
        }

        // Obtener nro_expediente del expediente origen para denormalización
        $exp    = $this->resolverExpediente($tipo, $idExp);
        $nroExp = $exp['nro'] ?? '';

        $resultado = $this->model->registrarPaso([
            'tipo_expediente'    => $tipo,
            'id_expediente'      => $idExp,
            'nro_expediente'     => $nroExp,
            'area'               => $area,
            'estado_paso'        => trim($post['estado_paso']        ?? 'en_tramite'),
            'id_responsable'     => ($post['id_responsable'] ?? '') ?: null,
            'responsable_nombre' => trim($post['responsable_nombre'] ?? ''),
            'responsable_cargo'  => trim($post['responsable_cargo']  ?? ''),
            'observaciones'      => trim($post['observaciones']      ?? ''),
            'fecha_entrada'      => $fechaEntrada,
        ], (int) session()->get('usuario_id'));

        if (!$resultado['success']) {
            $errMsg = implode(', ', $resultado['errors']);
            return redirect()->back()->with('error', "Error al registrar el pase: {$errMsg}");
        }

        return redirect()->to(site_url("trazabilidad/ver/{$tipo}/{$idExp}"))
            ->with('success', 'Pase registrado correctamente.');
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================

    /**
     * Resuelve los datos de un expediente origen según su tipo.
     * Retorna array normalizado con claves: nro, causante, tipo_label, motivo, fecha, estado, raw.
     */
    private function resolverExpediente(string $tipo, int $id): ?array
    {
        if (in_array($tipo, ['sancion_cuadros', 'sancion_cadetes'], true)) {
            $m   = new SancionModel();
            $raw = $m->obtenerSancionCompleta($id);
            if (!$raw) return null;

            $letra = trim($raw['letra'] ?? '');
            $nro   = trim($raw['nro']   ?? '');
            $nroDisplay = ($letra || $nro) ? trim("{$letra} / {$nro}", ' /') : "S-{$id}";

            return [
                'nro'       => $nroDisplay,
                'causante'  => trim(implode(' ', array_filter([
                    $raw['grado_infractor']   ?? '',
                    $raw['apellido_infractor']?? '',
                    $raw['nombre_infractor']  ? ', ' . $raw['nombre_infractor'] : '',
                ]))),
                'tipo_label'=> TrazabilidadModel::labelTipo($tipo),
                'motivo'    => $raw['motivo']        ?? '',
                'fecha'     => $raw['fecha_comision']?? '',
                'estado'    => $raw['estado']         ?? '',
                'raw'       => $raw,
            ];

        } else {
            $m   = new ActuacionModel();
            $raw = $m->obtenerConDatos($id);
            if (!$raw) return null;

            return [
                'nro'       => $raw->nro_expediente ?: "A-{$id}",
                'causante'  => trim(($raw->causante_grado ?? '') . ' ' . ($raw->causante_apellido ?? '')),
                'tipo_label'=> TrazabilidadModel::labelTipo($tipo),
                'motivo'    => '',
                'fecha'     => $raw->created_at ?? '',
                'estado'    => $raw->estado ?? '',
                'raw'       => (array) $raw,
            ];
        }
    }
}
