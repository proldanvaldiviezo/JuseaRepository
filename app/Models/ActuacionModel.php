<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Actuaciones Administrativas
 *
 * Almacena los datos del formulario como JSON en la columna `datos`,
 * evitando decenas de columnas para campos variables por módulo.
 *
 * Tipos soportados: 'bienes' | 'accidente'
 */
class ActuacionModel extends Model
{
    protected $table         = 'actuaciones';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'tipo', 'datos', 'nro_expediente', 'causante_apellido',
        'causante_grado', 'estado', 'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'tipo'   => 'required|in_list[bienes,accidente]',
        'datos'  => 'required',
    ];

    // --------------------------------------------------
    // Guardar actuación completa
    // --------------------------------------------------

    /**
     * Registra una nueva actuación con todos sus campos.
     *
     * @param string $tipo     'bienes' o 'accidente'
     * @param array  $campos   Array con todos los valores del formulario
     * @param int    $usuarioId
     * @return array ['success'=>bool, 'id'=>int|null, 'errors'=>array]
     */
    public function registrar(string $tipo, array $campos, int $usuarioId): array
    {
        $data = [
            'tipo'              => $tipo,
            'datos'             => json_encode($campos, JSON_UNESCAPED_UNICODE),
            'nro_expediente'    => trim($campos['NroExpH1'] ?? ''),
            'causante_apellido' => mb_strtoupper(trim($campos['ApeCausanteH1'] ?? '')),
            'causante_grado'    => trim($campos['GradCausanteH1'] ?? ''),
            'estado'            => 'activa',
            'created_by'        => $usuarioId,
        ];

        if (!$this->insert($data)) {
            return ['success' => false, 'id' => null, 'errors' => $this->errors()];
        }

        return ['success' => true, 'id' => $this->getInsertID(), 'errors' => []];
    }

    // --------------------------------------------------
    // Consultas
    // --------------------------------------------------

    public function listarPorTipo(string $tipo, int $limite = 50): array
    {
        return $this->where('tipo', $tipo)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limite)
                    ->findAll();
    }

    public function obtenerConDatos(int $id): ?object
    {
        $row = $this->find($id);
        if (!$row) return null;

        // Decodificar JSON para uso inmediato
        $row->campos = json_decode($row->datos, true) ?? [];
        return $row;
    }

    public function buscarPorApellido(string $tipo, string $apellido): array
    {
        return $this->where('tipo', $tipo)
                    ->like('causante_apellido', $apellido)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtener actuaciones en las que una persona figure como causante.
     * Usado por el historial de personal (PersonalController::historial).
     *
     * Dado que actuaciones no tiene FK a personas, busca por apellido (LIKE)
     * y opcionalmente refina por DNI si está presente en el JSON de datos.
     *
     * @param string $apellido  Apellido de la persona (mayúsculas)
     * @param string $tipo      'bienes' | 'accidente'
     * @param string $dni       DNI para filtrado adicional ('' = ignorar)
     */
    public function obtenerPorPersona(string $apellido, string $tipo, string $dni = ''): array
    {
        $builder = $this->db->table('actuaciones');
        $builder->select('id, tipo, nro_expediente, causante_apellido, causante_grado, estado, created_at, datos');
        $builder->where('tipo', $tipo);
        $builder->like('causante_apellido', $apellido, 'both');
        $builder->orderBy('created_at', 'DESC');

        $rows = $builder->get()->getResultArray();

        // Filtrado secundario por DNI (best-effort: prueba múltiples nombres de campo)
        if ($dni !== '') {
            $dniBuscado = trim($dni);
            $rows = array_values(array_filter($rows, function (array $row) use ($dniBuscado) {
                $datos = json_decode($row['datos'] ?? '{}', true) ?? [];
                // Nombres de campo posibles según versión del formulario
                foreach (['CAUSANTE_DNI', 'ApeCausanteH1_DNI', 'DNI_CAUSANTE', 'GradCausanteH1_DNI'] as $campo) {
                    if (isset($datos[$campo]) && trim((string)$datos[$campo]) === $dniBuscado) {
                        return true;
                    }
                }
                // Búsqueda genérica: cualquier campo que contenga _DNI y el valor coincide
                foreach ($datos as $key => $val) {
                    if (stripos($key, 'CAUSANTE') !== false && stripos($key, 'DNI') !== false) {
                        if (trim((string)$val) === $dniBuscado) return true;
                    }
                }
                return false;
            }));
        }

        return $rows;
    }
}
