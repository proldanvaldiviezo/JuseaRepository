<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Personas
 * Gestiona infractores, instructores y autoridades.
 * Todas las consultas usan query builder (parametrizadas) para prevenir SQL injection.
 */
class PersonaModel extends Model
{
    protected $table         = 'personas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'dni', 'apellido', 'nombre', 'grado',
        'arma_especialidad', 'destino_interno', 'cargo', 'tipo', 'activo'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // --------------------------------------------------
    // Reglas de validación
    // --------------------------------------------------
    protected $validationRules = [
        'dni'       => 'required|min_length[7]|max_length[10]|numeric',
        'apellido'  => 'required|min_length[2]|max_length[100]',
        'nombre'    => 'permit_empty|max_length[100]',
        'grado'     => 'permit_empty|max_length[80]',
        'tipo'      => 'required|in_list[cuadro,cadete,instructor,autoridad,civil]',
    ];

    protected $validationMessages = [
        'dni' => [
            'numeric'    => 'El DNI debe contener solo números.',
            'min_length' => 'El DNI debe tener al menos 7 dígitos.',
        ],
    ];

    // --------------------------------------------------
    // Métodos de negocio
    // --------------------------------------------------

    /**
     * Buscar persona por DNI (query parametrizada).
     */
    public function buscarPorDni(string $dni): ?object
    {
        return $this->where('dni', $dni)
                    ->where('activo', 1)
                    ->first();
    }

    /**
     * Buscar personas por apellido (LIKE parametrizado).
     */
    public function buscarPorApellido(string $apellido): array
    {
        return $this->like('apellido', $apellido, 'both')
                    ->where('activo', 1)
                    ->orderBy('apellido', 'ASC')
                    ->findAll();
    }

    /**
     * Insertar o actualizar persona. Si ya existe el DNI con mismo tipo,
     * actualiza los datos. Retorna el ID de la persona.
     */
    public function insertarOActualizar(array $data): int
    {
        $existente = $this->where('dni', $data['dni'])
                         ->where('tipo', $data['tipo'])
                         ->first();

        if ($existente) {
            $this->update($existente->id, $data);
            return (int) $existente->id;
        }

        $this->insert($data);
        return (int) $this->getInsertID();
    }

    /**
     * Obtener personas por tipo.
     */
    public function obtenerPorTipo(string $tipo): array
    {
        return $this->where('tipo', $tipo)
                    ->where('activo', 1)
                    ->orderBy('apellido', 'ASC')
                    ->findAll();
    }
}
