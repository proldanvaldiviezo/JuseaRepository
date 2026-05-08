<?php
namespace App\Controllers;

use App\Libraries\ApiCpsService;
use App\Models\UsuarioModel;
use CodeIgniter\Controller;

/**
 * Controlador de Autenticación
 * Login y logout. La verificación de credenciales se delega a APICPS.
 */
class AuthController extends Controller
{
    public function login()
    {
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

        // ── 1. Autenticar contra APICPS ───────────────────────────────────
        try {
            $apicps   = new ApiCpsService();
            $resultado = $apicps->login($username, $password);
        } catch (\RuntimeException $e) {
            log_message('error', '[AuthController] APICPS connection error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'No se pudo conectar con el servidor de autenticación. Intente nuevamente.');
        }

        if ($resultado === null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Usuario o contraseña incorrectos.');
        }

        $dni = $resultado['dni'];

        // ── 2. Buscar usuario local por DNI ───────────────────────────────
        $model   = new UsuarioModel();
        $usuario = $model->obtenerPorDni($dni);

        // ── 3. Si no existe, crearlo como usuario raso ────────────────────
        if ($usuario === null) {
            if (!$model->crearDesdeApicps($dni)) {
                log_message('error', '[AuthController] No se pudo crear usuario desde APICPS. DNI: ' . $dni);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Error al registrar el usuario. Contacte al administrador.');
            }
            $usuario = $model->obtenerPorDni($dni);
        }

        // Seguridad: obtenerPorDni retorna null si bajaUnidad = 1
        if ($usuario === null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Usuario dado de baja. Contacte al administrador.');
        }

        // ── 4. Establecer sesión ──────────────────────────────────────────
        session()->set([
            'usuario_id'      => $usuario->id,
            'usuario_nombre'  => $usuario->nombre_completo,
            'usuario_rol'     => $usuario->rol,
            'usuario_username'=> $usuario->username,
            'usuario_grado'   => $usuario->grado,
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
