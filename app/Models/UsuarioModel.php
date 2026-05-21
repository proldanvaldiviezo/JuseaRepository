<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * UsuarioModel — v3 (SQL Server / AspNetUsers)
 * Autenticacion contra AspNetUsers con PBKDF2-HMAC (ASP.NET Core Identity).
 * Roles JUSEA almacenados en JUSEA_UsuarioRol.
 *
 * ROLES:
 *   admin    — Control total (usuarios, config, todo)
 *   jefe     — Jefe División: operaciones completas, sin gestión usuarios
 *   operador — Carga/consulta sanciones y actuaciones; sin eliminar
 *   consulta — Solo lectura: historial y padrón
 */
class UsuarioModel extends Model
{
    protected $table            = 'AspNetUsers';
    protected $primaryKey       = 'Id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
    protected $allowedFields    = [];
    protected $useTimestamps    = false;

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

    public static function puede(string $accion, string $rol): bool
    {
        return \App\Models\PermisosModel::puede($accion, $rol);
    }

    public static function puedeActual(string $accion): bool
    {
        return self::puede($accion, session()->get('usuario_rol') ?? '');
    }

    // ── Autenticación vía APICPS ──────────────────────────────────────────

    /**
     * Busca el usuario en AspNetUsers por DNI (LEFT JOIN con JUSEA_UsuarioRol).
     * Retorna null si bajaUnidad = 1 (dado de baja en el sistema Partes).
     */
    public function obtenerPorDni(string $dni): ?object
    {
        $row = $this->db->table('AspNetUsers')
            ->select('AspNetUsers.Id, AspNetUsers.UserName,
                      AspNetUsers.Nombre, AspNetUsers.Apellido, AspNetUsers.display,
                      AspNetUsers.DNI, AspNetUsers.idGrado AS GRADO, AspNetUsers.bajaUnidad,
                      JUSEA_UsuarioRol.rol, JUSEA_UsuarioRol.activo AS activo_jusea')
            ->join('JUSEA_UsuarioRol', 'JUSEA_UsuarioRol.user_id = AspNetUsers.Id', 'left')
            ->where('AspNetUsers.DNI', $dni)
            ->get()->getRow();

        if (!$row) return null;

        // Bloquear si fue dado de baja en Partes
        if ((int) ($row->bajaUnidad ?? 0) === 1) return null;

        $usuario                  = new \stdClass();
        $usuario->id              = $row->Id;
        $usuario->username        = $row->UserName;
        $usuario->nombre_completo = trim(($row->Apellido ?? '') . ', ' . ($row->Nombre ?? ''))
                                    ?: ($row->display ?? $row->UserName);
        $usuario->rol             = $row->rol ?? '';
        $usuario->activo          = (bool) ($row->activo_jusea ?? false);
        $usuario->dni             = $row->DNI;
        $usuario->grado           = $row->GRADO ?? '';

        return $usuario;
    }

    /**
     * Crea un usuario en AspNetUsers a partir de los datos de APICPS.
     * Id = UserName = DNI = el DNI traído de la API.
     * Sin rol JUSEA asignado (acceso mínimo hasta que un admin lo configure).
     */
    public function crearDesdeApicps(string $dni): bool
    {
        return (bool) $this->db->table('AspNetUsers')->insert([
            'Id'                   => $dni,
            'UserName'             => $dni,
            'NormalizedUserName'   => strtoupper($dni),
            'DNI'                  => $dni,
            'PasswordHash'         => '',
            'SecurityStamp'        => strtoupper(bin2hex(random_bytes(16))),
            'ConcurrencyStamp'     => strtolower(bin2hex(random_bytes(16))),
            'EmailConfirmed'       => 0,
            'PhoneNumberConfirmed' => 0,
            'TwoFactorEnabled'     => 0,
            'LockoutEnabled'       => 0,
            'AccessFailedCount'    => 0,
            'bajaUnidad'           => 0,
        ]);
    }

    // ── Consultas ─────────────────────────────────────────────────────────

    public function obtenerTodos(): array
    {
        return $this->db->table('AspNetUsers')
            ->select('AspNetUsers.Id, AspNetUsers.UserName,
                      AspNetUsers.Nombre, AspNetUsers.Apellido, AspNetUsers.display,
                      AspNetUsers.DNI, AspNetUsers.idGrado AS GRADO, AspNetUsers.bajaUnidad,
                      JUSEA_UsuarioRol.rol, JUSEA_UsuarioRol.activo AS activo_jusea')
            ->join('JUSEA_UsuarioRol', 'JUSEA_UsuarioRol.user_id = AspNetUsers.Id', 'inner')
            ->orderBy('JUSEA_UsuarioRol.activo', 'DESC')
            ->orderBy('JUSEA_UsuarioRol.rol', 'ASC')
            ->orderBy('AspNetUsers.Apellido', 'ASC')
            ->get()->getResultObject();
    }

    public function obtenerActivos(): array
    {
        return $this->db->table('AspNetUsers')
            ->select('AspNetUsers.Id, AspNetUsers.UserName,
                      AspNetUsers.Nombre, AspNetUsers.Apellido, AspNetUsers.display,
                      AspNetUsers.DNI, AspNetUsers.idGrado AS GRADO,
                      JUSEA_UsuarioRol.rol')
            ->join('JUSEA_UsuarioRol', 'JUSEA_UsuarioRol.user_id = AspNetUsers.Id', 'inner')
            ->where('JUSEA_UsuarioRol.activo', 1)
            ->where('AspNetUsers.bajaUnidad', 0)
            ->orderBy('AspNetUsers.Apellido', 'ASC')
            ->get()->getResultObject();
    }

    // ── Gestión de roles JUSEA ────────────────────────────────────────────

    /**
     * Asigna o actualiza el rol JUSEA de un usuario de AspNetUsers.
     * Si no tiene entrada en JUSEA_UsuarioRol, la crea.
     */
    public function asignarRol(string $userId, string $rol): bool
    {
        $existe = $this->db->table('JUSEA_UsuarioRol')
            ->where('user_id', $userId)->get()->getRow();

        if ($existe) {
            return (bool) $this->db->table('JUSEA_UsuarioRol')
                ->where('user_id', $userId)
                ->update(['rol' => $rol, 'activo' => 1]);
        }

        return (bool) $this->db->table('JUSEA_UsuarioRol')
            ->insert(['user_id' => $userId, 'rol' => $rol, 'activo' => 1]);
    }

    public function desactivar(string $id): bool
    {
        return (bool) $this->db->table('JUSEA_UsuarioRol')
            ->where('user_id', $id)->update(['activo' => 0]);
    }

    public function reactivar(string $id): bool
    {
        return (bool) $this->db->table('JUSEA_UsuarioRol')
            ->where('user_id', $id)->update(['activo' => 1]);
    }
}
