<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * UsuarioModel — v2
 * ROLES:
 *   admin    — Control total (usuarios, config, todo)
 *   jefe     — Jefe División: operaciones completas, sin gestión usuarios
 *   operador — Carga/consulta sanciones y actuaciones; sin eliminar
 *   consulta — Solo lectura: historial y padrón
 */
class UsuarioModel extends Model
{
    protected $table         = 'usuarios';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'username', 'password_hash', 'nombre_completo',
        'email', 'rol', 'activo', 'ultimo_acceso'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'username'        => 'required|min_length[3]|max_length[50]|is_unique[usuarios.username,id,{id}]',
        'nombre_completo' => 'required|min_length[3]|max_length[150]',
        'email'           => 'permit_empty|valid_email',
        'rol'             => 'required|in_list[admin,jefe,operador,consulta]',
    ];

    protected $validationMessages = [
        'username' => ['is_unique' => 'El nombre de usuario ya existe.'],
    ];

    // ── Catálogo de roles ─────────────────────────────────────────────────

    public static array $roles = [
        'admin'    => ['label' => 'Administrador', 'badge' => 'bg-danger',
                       'desc'  => 'Control total del sistema'],
        'jefe'     => ['label' => 'Jefe División',  'badge' => 'bg-primary',
                       'desc'  => 'Operaciones completas, sin gestión de usuarios'],
        'operador' => ['label' => 'Operador',        'badge' => 'bg-success',
                       'desc'  => 'Carga y consulta de sanciones y actuaciones'],
        'consulta' => ['label' => 'Consulta',        'badge' => 'bg-secondary',
                       'desc'  => 'Solo lectura — historial y padrón'],
    ];

    /**
     * Verifica si un rol tiene permiso para una acción (delega a PermisosModel — BD).
     */
    public static function puede(string $accion, string $rol): bool
    {
        return \App\Models\PermisosModel::puede($accion, $rol);
    }

    /**
     * Verifica si el usuario de la sesión actual tiene permiso para una acción.
     */
    public static function puedeActual(string $accion): bool
    {
        return self::puede($accion, session()->get('usuario_rol') ?? '');
    }

    // ── Autenticación ─────────────────────────────────────────────────────

    public function autenticar(string $username, string $password): ?object
    {
        $usuario = $this->where('username', $username)->where('activo', 1)->first();
        if (!$usuario || !password_verify($password, $usuario->password_hash)) {
            return null;
        }
        try {
            $this->db->table($this->table)
                     ->where('id', $usuario->id)
                     ->update(['ultimo_acceso' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
            log_message('error', 'JUSEA: fallo ultimo_acceso — ' . $e->getMessage());
        }
        return $usuario;
    }

    // ── CRUD ──────────────────────────────────────────────────────────────

    public function crearUsuario(array $data): bool|int
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        unset($data['password'], $data['password_confirm']);
        return $this->insert($data);
    }

    public function cambiarPassword(int $id, string $nueva): bool
    {
        return (bool) $this->db->table($this->table)->where('id', $id)
            ->update(['password_hash' => password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12])]);
    }

    public function desactivar(int $id): bool
    {
        return (bool) $this->db->table($this->table)->where('id', $id)->update(['activo' => 0]);
    }

    public function reactivar(int $id): bool
    {
        return (bool) $this->db->table($this->table)->where('id', $id)->update(['activo' => 1]);
    }

    public function obtenerTodos(): array
    {
        return $this->orderBy('activo', 'DESC')
                    ->orderBy('rol', 'ASC')
                    ->orderBy('nombre_completo', 'ASC')
                    ->findAll();
    }

    public function obtenerActivos(): array
    {
        return $this->where('activo', 1)->orderBy('nombre_completo', 'ASC')->findAll();
    }
}
