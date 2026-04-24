<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Filtro de Autenticación
 * Verifica que el usuario tenga sesión activa.
 * Si no, redirige al login.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('usuario_id')) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Debe iniciar sesión para acceder al sistema.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere acción post-respuesta
        return null;
    }
}
