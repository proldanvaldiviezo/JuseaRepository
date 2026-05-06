<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * PersonaModel — v3 (SQL Server / AspNetUsers)
 * Solo lectura. Los datos de personas provienen de AspNetUsers.
 * Mapeo: dni→DNI, apellido→Apellido, nombre→Nombre, grado→GRADO, arma→ARMA.
 */
class PersonaModel extends Model
{
    protected $table            = 'AspNetUsers';
    protected $primaryKey       = 'Id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
    protected $allowedFields    = [];
    protected $useTimestamps    = false;

    private function selectBase(): \CodeIgniter\Database\BaseBuilder
    {
        return $this->db->table('AspNetUsers')
            ->select('AspNetUsers.Id AS id,
                      AspNetUsers.DNI AS dni,
                      AspNetUsers.Apellido AS apellido,
                      AspNetUsers.Nombre AS nombre,
                      AspNetUsers.GRADO AS grado,
                      AspNetUsers.ARMA AS arma_especialidad,
                      AspNetUsers.display AS display,
                      AspNetUsers.bajaUnidad');
    }

    public function buscarPorDni(string $dni): ?object
    {
        return $this->selectBase()
            ->where('AspNetUsers.DNI', $dni)
            ->get()->getRow();
    }

    public function buscarPorApellido(string $apellido): array
    {
        return $this->selectBase()
            ->like('AspNetUsers.Apellido', $apellido, 'both')
            ->orderBy('AspNetUsers.Apellido', 'ASC')
            ->get()->getResultObject();
    }

    /**
     * Busca una persona en AspNetUsers por DNI y retorna su Id (GUID).
     * Reemplaza el antiguo insertarOActualizar — ya no se crean personas desde JUSEA.
     */
    public function obtenerIdPorDni(string $dni): ?string
    {
        $row = $this->db->table('AspNetUsers')
            ->select('Id')
            ->where('DNI', $dni)
            ->get()->getRow();

        return $row ? $row->Id : null;
    }

    public function obtenerPorId(string $id): ?object
    {
        return $this->selectBase()
            ->where('AspNetUsers.Id', $id)
            ->get()->getRow();
    }

    public function buscarPorNombre(string $termino): array
    {
        return $this->selectBase()
            ->groupStart()
                ->like('AspNetUsers.Apellido', $termino, 'both')
                ->orLike('AspNetUsers.Nombre', $termino, 'both')
            ->groupEnd()
            ->orderBy('AspNetUsers.Apellido', 'ASC')
            ->get()->getResultObject();
    }
}
