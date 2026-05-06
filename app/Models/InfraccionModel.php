<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Infracciones
 */
class InfraccionModel extends Model
{
    protected $table         = 'JUSEA_Infracciones';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'fecha_comision', 'reg_act_dis', 'inciso',
        'motivo', 'tipo_sancion', 'duracion', 'lugar_cumplimiento',
        'cargo_autoridad', 'revisor_grado', 'revisor_nombre',
        'revisor_dni', 'revisor_cargo',
        'letra', 'nro'
    ];
    protected $useTimestamps = false;

    protected $validationRules = [
        'fecha_comision' => 'required|valid_date[Y-m-d]',
        'motivo'         => 'required|min_length[5]',
    ];

    protected $validationMessages = [
        'fecha_comision' => [
            'valid_date' => 'La fecha de comisión debe tener formato válido (AAAA-MM-DD).',
        ],
        'motivo' => [
            'min_length' => 'El motivo debe tener al menos 5 caracteres.',
        ],
    ];
}
