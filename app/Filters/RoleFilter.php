<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * RoleFilter — v2
 * Soporta un rol único ('role:admin') o múltiples ('role:admin,jefe').
 * El usuario debe tener uno de los roles listados para pasar.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('usuario_id')) {
            return redirect()->to(site_url('login'));
        }

        $rolesPermitidos = $arguments[0] ?? null;
        if (!$rolesPermitidos) {
            return null; // Sin restricción de rol
        }

        $rolUsuario = $session->get('usuario_rol') ?? '';
        $lista      = array_map('trim', explode(',', $rolesPermitidos));

        if (!in_array($rolUsuario, $lista, true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'No tiene permisos para acceder a esa sección.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
