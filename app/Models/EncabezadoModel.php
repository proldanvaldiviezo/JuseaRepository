<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Encabezado Institucional
 */
class EncabezadoModel extends Model
{
    protected $table         = 'encabezado';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['anio', 'membrete', 'unidad', 'updated_by'];

    protected $validationRules = [
        'anio'     => 'required|exact_length[4]|numeric',
        'membrete' => 'required|min_length[3]|max_length[255]',
        'unidad'   => 'required|min_length[3]|max_length[255]',
    ];

    /**
     * Obtener el encabezado vigente (siempre es registro id=1).
     */
    public function obtenerVigente(): ?object
    {
        return $this->first();
    }

    /**
     * Actualizar encabezado institucional.
     */
    public function actualizarEncabezado(array $data, int $usuarioId): bool
    {
        $data['updated_by'] = $usuarioId;
        $encabezado = $this->first();

        if ($encabezado) {
            return $this->update($encabezado->id, $data);
        }

        return (bool) $this->insert($data);
    }
}
