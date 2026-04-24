<?php
namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\Controller;

/**
 * Controlador de Autenticación
 * Login, logout y gestión de sesiones.
 */
class AuthController extends Controller
{
    public function login()
    {
        // Si ya tiene sesión, redirigir al dashboard
        if (session()->get('usuario_id')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('auth/login');
    }

    public function authenticate()
    {
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Complete todos los campos correctamente.');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $model = new UsuarioModel();
        $usuario = $model->autenticar($username, $password);

        if (!$usuario) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Credenciales inválidas o usuario inactivo.');
        }

        // Establecer sesión
        session()->set([
            'usuario_id'      => $usuario->id,
            'usuario_nombre'  => $usuario->nombre_completo,
            'usuario_rol'     => $usuario->rol,
            'usuario_username'=> $usuario->username,
            'logged_in'       => true,
        ]);

        return redirect()->to(site_url('dashboard'))
            ->with('success', 'Bienvenido, ' . $usuario->nombre_completo);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'))
            ->with('success', 'Sesión cerrada correctamente.');
    }
}
