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

    // ── Verificación de password ASP.NET Core Identity ───────────────────

    private function verificarPasswordAspNet(string $password, string $hash): bool
    {
        if (empty($hash)) return false;

        $decoded = base64_decode($hash, true);
        if ($decoded === false || strlen($decoded) < 13) return false;

        $version = ord($decoded[0]);

        if ($version === 1) {
            // V3: PBKDF2-HMAC-SHA256
            $iterations = unpack('N', substr($decoded, 5, 4))[1];
            $saltLen    = unpack('N', substr($decoded, 9, 4))[1];
            if (strlen($decoded) < 13 + $saltLen) return false;
            $salt     = substr($decoded, 13, $saltLen);
            $expected = substr($decoded, 13 + $saltLen);
            $derived  = hash_pbkdf2('sha256', $password, $salt, $iterations, strlen($expected), true);
            return hash_equals($expected, $derived);
        }

        if ($version === 0) {
            // V2: PBKDF2-HMAC-SHA1, 1000 iteraciones
            if (strlen($decoded) < 49) return false;
            $salt     = substr($decoded, 1, 16);
            $expected = substr($decoded, 17);
            $derived  = hash_pbkdf2('sha1', $password, $salt, 1000, strlen($expected), true);
            return hash_equals($expected, $derived);
        }

        return false;
    }

    // ── Autenticación ─────────────────────────────────────────────────────

    public function autenticar(string $username, string $password): ?object
    {
        $row = $this->db->table('AspNetUsers')
            ->select('AspNetUsers.Id, AspNetUsers.UserName, AspNetUsers.PasswordHash,
                      AspNetUsers.Nombre, AspNetUsers.Apellido, AspNetUsers.display,
                      AspNetUsers.DNI, AspNetUsers.GRADO, AspNetUsers.bajaUnidad,
                      JUSEA_UsuarioRol.rol, JUSEA_UsuarioRol.activo AS activo_jusea')
            ->join('JUSEA_UsuarioRol', 'JUSEA_UsuarioRol.user_id = AspNetUsers.Id', 'inner')
            ->where('AspNetUsers.UserName', $username)
            ->where('JUSEA_UsuarioRol.activo', 1)
            ->where('AspNetUsers.bajaUnidad', 0)
            ->get()->getRow();

        if (!$row) return null;
        if (!$this->verificarPasswordAspNet($password, $row->PasswordHash)) return null;

        $usuario                  = new \stdClass();
        $usuario->id              = $row->Id;
        $usuario->username        = $row->UserName;
        $usuario->nombre_completo = trim(($row->Nombre ?? '') . ' ' . ($row->Apellido ?? ''))
                                    ?: ($row->display ?? $row->UserName);
        $usuario->rol             = $row->rol;
        $usuario->activo          = (bool) $row->activo_jusea;
        $usuario->dni             = $row->DNI;
        $usuario->grado           = $row->GRADO;

        return $usuario;
    }

    // ── Consultas ─────────────────────────────────────────────────────────

    public function obtenerTodos(): array
    {
        return $this->db->table('AspNetUsers')
            ->select('AspNetUsers.Id, AspNetUsers.UserName,
                      AspNetUsers.Nombre, AspNetUsers.Apellido, AspNetUsers.display,
                      AspNetUsers.DNI, AspNetUsers.GRADO, AspNetUsers.bajaUnidad,
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
                      AspNetUsers.DNI, AspNetUsers.GRADO,
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
