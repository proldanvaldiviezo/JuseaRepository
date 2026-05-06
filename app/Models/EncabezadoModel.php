<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Encabezado Institucional
 */
class EncabezadoModel extends Model
{
    protected $table         = 'JUSEA_Encabezado';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['anio', 'membrete', 'unidad', 'updated_by'];

    protected $validationRules = [
        'anio'     => 'required|exact_length[4]|numeric',
        'membrete' => 'required|min_length[3]|max_length[255]',
        'unidad'   => 'required|min_length[3]|max_length[255]',
    ];

    /**
     * Obtener el encabezado vigente. Si la tabla está vacía retorna valores por defecto
     * para que los documentos puedan generarse aunque aún no se haya configurado.
     */
    public function obtenerVigente(): object
    {
        return $this->first() ?? (object)[
            'id'       => null,
            'anio'     => date('Y'),
            'membrete' => '',
            'unidad'   => 'Colegio Militar de la Nación',
        ];
    }

    /**
     * Actualizar encabezado institucional.
     */
    public function actualizarEncabezado(array $data, string $usuarioId): bool
    {
        $data['updated_by'] = $usuarioId;
        $encabezado = $this->first();

        if ($encabezado) {
            return $this->update($encabezado->id, $data);
        }

        return (bool) $this->insert($data);
    }
}
