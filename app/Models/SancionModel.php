<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * SancionModel — v3 (SQL Server)
 * id_causante e id_autoridad ahora son NVARCHAR(450) → AspNetUsers.Id
 */
class SancionModel extends Model
{
    protected $table         = 'JUSEA_Sanciones';
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
        'tipo_sancion'  => 'required|in_list[cuadros,cadetes]',
    ];

    // --------------------------------------------------
    // Registrar sanción completa
    // --------------------------------------------------

    /**
     * Registra infracción + sanción en una transacción.
     * id_causante e id_autoridad son los AspNetUsers.Id (GUID string).
     *
     * @param string $idCausante   AspNetUsers.Id del infractor
     * @param string $idAutoridad  AspNetUsers.Id de quien impone la sanción
     * @param array  $datosInfraccion
     * @param string $tipoSancion  'cuadros' o 'cadetes'
     * @param string $usuarioId    AspNetUsers.Id del usuario que registra
     */
    public function registrarSancionCompleta(
        string $idCausante,
        string $idAutoridad,
        array  $datosInfraccion,
        string $tipoSancion,
        string $usuarioId
    ): array {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $infraccionModel = new InfraccionModel();
            $infraccionModel->insert($datosInfraccion);
            $idInfraccion = $infraccionModel->getInsertID();

            $this->insert([
                'id_infraccion' => $idInfraccion,
                'id_causante'   => $idCausante ?: null,
                'id_autoridad'  => $idAutoridad ?: null,
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

    // --------------------------------------------------
    // Consultas con JOINs a AspNetUsers
    // --------------------------------------------------

    public function buscarHistorial(string $criterio, string $valor): array
    {
        $builder = $this->db->table('JUSEA_Sanciones s');
        $builder->select('
            s.id AS sancion_id,
            s.tipo_sancion AS categoria,
            s.estado,
            s.created_at AS fecha_registro,
            p1.DNI AS dni_infractor,
            p1.Nombre AS nombre_infractor,
            p1.Apellido AS apellido_infractor,
            p1.GRADO AS grado_infractor,
            i.motivo,
            i.fecha_comision,
            i.tipo_sancion AS tipo,
            i.duracion,
            p2.DNI AS dni_instructor,
            p2.Nombre AS nombre_instructor,
            p2.Apellido AS apellido_instructor,
            p2.GRADO AS grado_instructor
        ');
        $builder->join('AspNetUsers p1', 's.id_causante = p1.Id', 'left');
        $builder->join('AspNetUsers p2', 's.id_autoridad = p2.Id', 'left');
        $builder->join('JUSEA_Infracciones i', 's.id_infraccion = i.id');

        if ($criterio === 'dni') {
            $builder->where('p1.DNI', $valor);
        } elseif ($criterio === 'apellido') {
            $builder->like('p1.Apellido', $valor, 'both');
        }

        $builder->orderBy('s.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function obtenerPorCausante(string $id_causante): array
    {
        $builder = $this->db->table('JUSEA_Sanciones s');
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
            p2.GRADO    AS grado_autoridad,
            p2.Apellido AS apellido_autoridad,
            p2.Nombre   AS nombre_autoridad
        ');
        $builder->join('JUSEA_Infracciones i', 's.id_infraccion = i.id');
        $builder->join('AspNetUsers p2', 's.id_autoridad = p2.Id', 'left');
        $builder->where('s.id_causante', $id_causante);
        $builder->orderBy('i.fecha_comision', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function obtenerSancionCompleta(int $id): ?array
    {
        $builder = $this->db->table('JUSEA_Sanciones s');
        $builder->select('
            s.*,
            p1.DNI AS dni_infractor, p1.Nombre AS nombre_infractor,
            p1.Apellido AS apellido_infractor, p1.GRADO AS grado_infractor,
            p1.ARMA AS arma_infractor,
            p2.DNI AS dni_instructor, p2.Nombre AS nombre_instructor,
            p2.Apellido AS apellido_instructor, p2.GRADO AS grado_instructor,
            p2.display AS cargo_instructor,
            i.fecha_comision, i.reg_act_dis, i.inciso, i.motivo,
            i.tipo_sancion AS tipo_sancion_desc, i.duracion, i.lugar_cumplimiento,
            i.cargo_autoridad, i.revisor_grado, i.revisor_nombre,
            i.revisor_dni, i.revisor_cargo
        ');
        $builder->join('AspNetUsers p1', 's.id_causante = p1.Id', 'left');
        $builder->join('AspNetUsers p2', 's.id_autoridad = p2.Id', 'left');
        $builder->join('JUSEA_Infracciones i', 's.id_infraccion = i.id');
        $builder->where('s.id', $id);

        $result = $builder->get()->getRowArray();
        return $result ?: null;
    }
}
