<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * TrazabilidadModel
 * Registra y consulta la cadena de pases de expedientes entre áreas.
 * Cubre: Sanciones disciplinarias, Actuaciones por Bienes y Accidente/Enfermedad.
 */
class TrazabilidadModel extends Model
{
    protected $table      = 'JUSEA_Trazabilidad';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'tipo_expediente', 'id_expediente', 'nro_expediente',
        'area', 'area_label', 'estado_paso',
        'id_responsable', 'responsable_nombre', 'responsable_cargo',
        'observaciones', 'fecha_entrada', 'fecha_salida',
        'activo', 'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ──────────────────────────────────────────────────────────────────────────
    // Configuración estática: Áreas y Estados
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Áreas habilitadas por tipo de expediente, con su etiqueta y color Bootstrap.
     *
     * Flujo canónico:
     *   Sanciones   → personal → autoridad → personal (archivo)
     *   Accidente   → sanidad → personal → actuante → personal (guarda/cierre)
     *   Bienes      → unidad_requirente → materiales → personal → actuante → materiales (cierre)
     */
    public static function areasParaTipo(string $tipo): array
    {
        $mapa = [
            'sancion_cuadros' => [
                'personal'  => 'Área de Personal',
                'autoridad' => 'Autoridad Disciplinaria',
                'archivo'   => 'Archivo',
            ],
            'sancion_cadetes' => [
                'personal'  => 'Área de Personal',
                'autoridad' => 'Autoridad Disciplinaria',
                'archivo'   => 'Archivo',
            ],
            'actuacion_accidente' => [
                'sanidad'   => 'Área de Sanidad',
                'personal'  => 'Área de Personal',
                'actuante'  => 'Actuante Designado',
                'archivo'   => 'Archivo',
            ],
            'actuacion_bienes' => [
                'unidad_requirente' => 'Unidad Requirente',
                'materiales'        => 'Área de Materiales',
                'personal'          => 'Área de Personal',
                'actuante'          => 'Actuante Designado',
                'archivo'           => 'Archivo',
            ],
        ];
        return $mapa[$tipo] ?? [];
    }

    /**
     * Todas las áreas posibles (para filtros globales).
     */
    public static function todasLasAreas(): array
    {
        return [
            'personal'          => 'Área de Personal',
            'sanidad'           => 'Área de Sanidad',
            'materiales'        => 'Área de Materiales',
            'actuante'          => 'Actuante Designado',
            'autoridad'         => 'Autoridad Disciplinaria',
            'unidad_requirente' => 'Unidad Requirente',
            'archivo'           => 'Archivo',
        ];
    }

    /**
     * Badge Bootstrap y ícono para cada área.
     */
    public static function estiloArea(string $area): array
    {
        $estilos = [
            'personal'          => ['badge' => 'bg-primary',          'icon' => 'bi-person-badge-fill'],
            'sanidad'           => ['badge' => 'bg-success',          'icon' => 'bi-hospital-fill'],
            'materiales'        => ['badge' => 'bg-warning text-dark','icon' => 'bi-box-seam-fill'],
            'actuante'          => ['badge' => 'bg-info text-dark',   'icon' => 'bi-person-check-fill'],
            'autoridad'         => ['badge' => 'bg-danger',           'icon' => 'bi-star-fill'],
            'unidad_requirente' => ['badge' => 'bg-secondary',        'icon' => 'bi-building'],
            'archivo'           => ['badge' => 'bg-dark',             'icon' => 'bi-archive-fill'],
        ];
        return $estilos[$area] ?? ['badge' => 'bg-secondary', 'icon' => 'bi-circle'];
    }

    /**
     * Estados disponibles para un paso.
     */
    public static function estadosPaso(): array
    {
        return [
            'en_tramite' => 'En trámite',
            'recibido'   => 'Recibido',
            'elevado'    => 'Elevado',
            'devuelto'   => 'Devuelto',
            'suspendido' => 'Suspendido',
            'resuelto'   => 'Resuelto / Finalizado',
            'archivado'  => 'Archivado',
        ];
    }

    /**
     * Etiquetas legibles para tipo_expediente.
     */
    public static function labelTipo(string $tipo): string
    {
        $labels = [
            'sancion_cuadros'    => 'Sanción — Cuadros',
            'sancion_cadetes'    => 'Sanción — Cadetes',
            'actuacion_bienes'   => 'Actuación — Bienes',
            'actuacion_accidente'=> 'Actuación — Acc./Enf.',
        ];
        return $labels[$tipo] ?? $tipo;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Consultas
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Obtener la cadena completa de pases de un expediente, ordenada cronológicamente.
     */
    public function obtenerCadena(string $tipo, int $idExpediente): array
    {
        return $this->where('tipo_expediente', $tipo)
                    ->where('id_expediente', $idExpediente)
                    ->orderBy('fecha_entrada', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener el paso activo (vigente) de un expediente.
     */
    public function obtenerPasoActual(string $tipo, int $idExpediente): ?array
    {
        return $this->where('tipo_expediente', $tipo)
                    ->where('id_expediente', $idExpediente)
                    ->where('activo', 1)
                    ->orderBy('fecha_entrada', 'DESC')
                    ->first();
    }

    /**
     * Panel global: todos los expedientes activos (no archivados).
     * Opcionalmente filtrado por tipo y/o área.
     */
    public function panelActivos(?string $tipo = null, ?string $area = null): array
    {
        $builder = $this->db->table('JUSEA_Trazabilidad');
        $builder->select('id, tipo_expediente, id_expediente, nro_expediente,
                          area, area_label, estado_paso,
                          responsable_nombre, responsable_cargo,
                          fecha_entrada, observaciones');
        $builder->where('activo', 1);
        $builder->where('estado_paso !=', 'archivado');

        if ($tipo !== null && $tipo !== '') {
            $builder->where('tipo_expediente', $tipo);
        }
        if ($area !== null && $area !== '') {
            $builder->where('area', $area);
        }

        $builder->orderBy('area', 'ASC');
        $builder->orderBy('fecha_entrada', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Verificar si un expediente ya tiene al menos un paso registrado.
     */
    public function tieneTrazabilidad(string $tipo, int $idExpediente): bool
    {
        return $this->where('tipo_expediente', $tipo)
                    ->where('id_expediente', $idExpediente)
                    ->countAllResults() > 0;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Escritura
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Registrar un nuevo pase.
     * Cierra automáticamente el paso activo anterior.
     *
     * @param array $datos  Debe contener: tipo_expediente, id_expediente, area,
     *                      estado_paso, responsable_nombre, responsable_cargo,
     *                      fecha_entrada, observaciones (opcionales: id_responsable, nro_expediente)
     * @param int $usuarioId
     */
    public function registrarPaso(array $datos, string $usuarioId): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $tipo  = $datos['tipo_expediente'];
            $idExp = (int) $datos['id_expediente'];
            $ahora = date('Y-m-d H:i:s');

            // Cerrar el paso activo anterior (registrar fecha_salida)
            $pasoActual = $this->obtenerPasoActual($tipo, $idExp);
            if ($pasoActual) {
                $this->db->table('JUSEA_Trazabilidad')
                    ->where('id', $pasoActual['id'])
                    ->update([
                        'fecha_salida' => $datos['fecha_entrada'] ?? $ahora,
                        'activo'       => 0,
                        'updated_at'   => $ahora,
                    ]);
            }

            // Insertar nuevo paso
            $areas     = self::areasParaTipo($tipo);
            $areaKey   = $datos['area'];
            $areaLabel = $areas[$areaKey] ?? $datos['area_label'] ?? $areaKey;

            $this->insert([
                'tipo_expediente'    => $tipo,
                'id_expediente'      => $idExp,
                'nro_expediente'     => $datos['nro_expediente']     ?? '',
                'area'               => $areaKey,
                'area_label'         => $areaLabel,
                'estado_paso'        => $datos['estado_paso']        ?? 'en_tramite',
                'id_responsable'     => ($datos['id_responsable'] ?? '') ?: null,
                'responsable_nombre' => trim($datos['responsable_nombre'] ?? ''),
                'responsable_cargo'  => trim($datos['responsable_cargo']  ?? ''),
                'observaciones'      => trim($datos['observaciones']      ?? ''),
                'fecha_entrada'      => $datos['fecha_entrada']      ?? $ahora,
                'fecha_salida'       => null,
                'activo'             => 1,
                'created_by'         => $usuarioId,
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['success' => false, 'errors' => ['Error en la transacción de base de datos.']];
            }

            return ['success' => true, 'id' => $this->getInsertID()];

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'TrazabilidadModel::registrarPaso — ' . $e->getMessage());
            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers de tiempo
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Días transcurridos desde una fecha hasta hoy (o hasta $hasta).
     */
    public static function diasEntre(string $desde, ?string $hasta = null): int
    {
        try {
            $d = new \DateTime($desde);
            $h = $hasta ? new \DateTime($hasta) : new \DateTime();
            return max(0, (int) $h->diff($d)->days);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Formatear días como texto legible.
     */
    public static function labelDias(int $dias): string
    {
        if ($dias === 0) return 'Hoy';
        if ($dias === 1) return '1 día';
        return "{$dias} días";
    }

    /**
     * Color semáforo según cantidad de días (para destacar expedientes demorados).
     */
    public static function colorDias(int $dias): string
    {
        if ($dias <= 5)  return 'text-success';
        if ($dias <= 15) return 'text-warning';
        return 'text-danger fw-bold';
    }
}
