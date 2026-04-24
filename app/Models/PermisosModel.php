<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * PermisosModel — v1
 *
 * Matriz de permisos por rol almacenada en base de datos (tabla `permisos`).
 * Los permisos se cachean estáticamente para evitar múltiples consultas por request.
 *
 * La columna `admin` siempre vale 1 y no es editable desde la UI.
 */
class PermisosModel extends Model
{
    protected $table         = 'permisos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['accion', 'etiqueta', 'admin', 'jefe', 'operador', 'consulta', 'orden'];

    /** Caché estático — una sola consulta por request */
    private static ?array $cache = null;

    // ── Consulta ──────────────────────────────────────────────────────────

    /**
     * Carga todos los permisos desde BD (con caché estático por request).
     *
     * @return array<string, array{etiqueta:string, admin:bool, jefe:bool, operador:bool, consulta:bool}>
     */
    public static function cargar(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $model = new self();
        $rows  = $model->orderBy('orden', 'ASC')->findAll();

        self::$cache = [];
        foreach ($rows as $row) {
            self::$cache[$row->accion] = [
                'etiqueta' => $row->etiqueta,
                'admin'    => (bool) $row->admin,
                'jefe'     => (bool) $row->jefe,
                'operador' => (bool) $row->operador,
                'consulta' => (bool) $row->consulta,
            ];
        }

        return self::$cache;
    }

    /**
     * Verifica si un rol tiene permiso para una acción dada.
     */
    public static function puede(string $accion, string $rol): bool
    {
        $permisos = self::cargar();
        return isset($permisos[$accion]) && !empty($permisos[$accion][$rol]);
    }

    /**
     * Invalida el caché en memoria (llamar después de guardar cambios).
     */
    public static function invalidarCache(): void
    {
        self::$cache = null;
    }

    // ── Escritura ─────────────────────────────────────────────────────────

    /**
     * Persiste la matriz editada desde el formulario admin.
     *
     * $matriz forma: ['accion' => ['jefe' => 1, 'operador' => 0, 'consulta' => 0], ...]
     * Admin siempre se fuerza a 1.
     */
    public function guardarMatriz(array $matriz): void
    {
        $roles = ['jefe', 'operador', 'consulta'];
        $todos = $this->findAll();

        foreach ($todos as $row) {
            $accion = $row->accion;
            $data   = ['id' => $row->id, 'admin' => 1];
            foreach ($roles as $rol) {
                $data[$rol] = isset($matriz[$accion][$rol]) ? 1 : 0;
            }
            $this->save($data);
        }

        self::invalidarCache();
    }
}
