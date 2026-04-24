<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Sanciones
 * Vincula infracción + causante + autoridad.
 * Usa insert_id() en lugar de MAX(id) para evitar problemas de concurrencia.
 */
class SancionModel extends Model
{
    protected $table         = 'sanciones';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'id_infraccion', 'id_causante', 'id_autoridad',
        'tipo_sancion', 'estado', 'observaciones', 'created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id_infraccion' => 'required|integer',
        'id_causante'   => 'required|integer',
        'id_autoridad'  => 'required|integer',
        'tipo_sancion'  => 'required|in_list[cuadros,cadetes]',
    ];

    // --------------------------------------------------
    // Métodos de negocio
    // --------------------------------------------------

    /**
     * Registrar sanción completa (infractor + instructor + infracción + vínculo).
     * Usa transacciones para garantizar integridad.
     *
     * @param array $datosInfractor   Datos de la persona infractora
     * @param array $datosInstructor  Datos de quien impone la sanción
     * @param array $datosInfraccion  Datos de la infracción
     * @param string $tipoSancion    'cuadros' o 'cadetes'
     * @param int $usuarioId         ID del usuario que registra
     * @return array ['success' => bool, 'sancion_id' => int|null, 'errors' => array]
     */
    public function registrarSancionCompleta(
        array $datosInfractor,
        array $datosInstructor,
        array $datosInfraccion,
        string $tipoSancion,
        int $usuarioId
    ): array {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Insertar o actualizar infractor
            $personaModel = new PersonaModel();
            $idCausante = $personaModel->insertarOActualizar($datosInfractor);

            // 2. Insertar o actualizar instructor/autoridad
            $idAutoridad = $personaModel->insertarOActualizar($datosInstructor);

            // 3. Insertar infracción
            $infraccionModel = new InfraccionModel();
            $infraccionModel->insert($datosInfraccion);
            $idInfraccion = $infraccionModel->getInsertID();

            // 4. Crear vínculo de sanción (usa insert_id, NO max)
            $this->insert([
                'id_infraccion' => $idInfraccion,
                'id_causante'   => $idCausante,
                'id_autoridad'  => $idAutoridad,
                'tipo_sancion'  => $tipoSancion,
                'estado'        => 'activa',
                'created_by'    => $usuarioId,
            ]);
            $idSancion = $this->getInsertID();

            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['success' => false, 'sancion_id' => null, 'errors' => ['Error en la transacción de base de datos.']];
            }

            return ['success' => true, 'sancion_id' => (int) $idSancion, 'errors' => []];

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error al registrar sanción: ' . $e->getMessage());
            return ['success' => false, 'sancion_id' => null, 'errors' => [$e->getMessage()]];
        }
    }

    /**
     * Buscar historial de sanciones con JOINs.
     * Query completamente parametrizada.
     */
    public function buscarHistorial(string $criterio, string $valor): array
    {
        $builder = $this->db->table('sanciones s');
        $builder->select('
            s.id AS sancion_id,
            s.tipo_sancion AS categoria,
            s.estado,
            s.created_at AS fecha_registro,
            p1.dni AS dni_infractor,
            p1.nombre AS nombre_infractor,
            p1.apellido AS apellido_infractor,
            p1.grado AS grado_infractor,
            i.motivo,
            i.fecha_comision,
            i.tipo_sancion AS tipo,
            i.duracion,
            p2.dni AS dni_instructor,
            p2.nombre AS nombre_instructor,
            p2.apellido AS apellido_instructor,
            p2.grado AS grado_instructor
        ');
        $builder->join('personas p1', 's.id_causante = p1.id');
        $builder->join('personas p2', 's.id_autoridad = p2.id');
        $builder->join('infracciones i', 's.id_infraccion = i.id');

        if ($criterio === 'dni') {
            $builder->where('p1.dni', $valor);
        } elseif ($criterio === 'apellido') {
            $builder->like('p1.apellido', $valor, 'both');
        }

        $builder->orderBy('s.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener todas las sanciones en las que una persona figure como causante.
     * Usado por el historial de personal (PersonalController::historial).
     *
     * @param int $id_causante  PK de personas
     */
    public function obtenerPorCausante(int $id_causante): array
    {
        $builder = $this->db->table('sanciones s');
        $builder->select('
            s.id          AS sancion_id,
            s.tipo_sancion,
            s.estado,
            s.created_at  AS fecha_registro,
            i.fecha_comision,
            i.motivo,
            i.tipo_sancion AS tipo_sancion_desc,
            i.duracion,
            i.lugar_cumplimiento,
            i.letra,
            i.nro,
            p2.grado    AS grado_autoridad,
            p2.apellido AS apellido_autoridad,
            p2.nombre   AS nombre_autoridad
        ');
        $builder->join('infracciones i', 's.id_infraccion = i.id');
        $builder->join('personas p2',    's.id_autoridad  = p2.id');
        $builder->where('s.id_causante', $id_causante);
        $builder->orderBy('i.fecha_comision', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener sanción completa por ID (con todos los datos relacionados).
     */
    public function obtenerSancionCompleta(int $id): ?array
    {
        $builder = $this->db->table('sanciones s');
        $builder->select('
            s.*,
            p1.dni AS dni_infractor, p1.nombre AS nombre_infractor,
            p1.apellido AS apellido_infractor, p1.grado AS grado_infractor,
            p1.arma_especialidad AS arma_infractor, p1.destino_interno AS destino_infractor,
            p2.dni AS dni_instructor, p2.nombre AS nombre_instructor,
            p2.apellido AS apellido_instructor, p2.grado AS grado_instructor,
            p2.cargo AS cargo_instructor,
            i.fecha_comision, i.reg_act_dis, i.inciso, i.motivo,
            i.tipo_sancion AS tipo_sancion_desc, i.duracion, i.lugar_cumplimiento,
            i.cargo_autoridad, i.revisor_grado, i.revisor_nombre,
            i.revisor_dni, i.revisor_cargo
        ');
        $builder->join('personas p1', 's.id_causante = p1.id');
        $builder->join('personas p2', 's.id_autoridad = p2.id');
        $builder->join('infracciones i', 's.id_infraccion = i.id');
        $builder->where('s.id', $id);

        $result = $builder->get()->getRowArray();
        return $result ?: null;
    }
}
