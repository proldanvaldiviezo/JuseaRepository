<?php
namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\Controller;

/**
 * UsuarioController — v2
 * Gestión completa de usuarios del sistema JUSEA CMN.
 * Exclusivo para administradores.
 */
class UsuarioController extends Controller
{
    protected UsuarioModel $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
    }

    // ─── Lista ────────────────────────────────────────────────────────────

    public function index()
    {
        $usuarios   = $this->model->obtenerTodos();
        $roles      = UsuarioModel::$roles;
        $rolActual  = session()->get('usuario_rol');
        $idActual   = session()->get('usuario_id');

        $stats = [];
        foreach (array_keys($roles) as $r) {
            $stats[$r] = count(array_filter($usuarios, fn($u) => $u->rol === $r && $u->activo));
        }
        $stats['inactivos'] = count(array_filter($usuarios, fn($u) => !$u->activo));

        return view('layouts/main', [
            'titulo'    => 'Gestión de Usuarios',
            'contenido' => view('usuarios/lista',
                compact('usuarios', 'roles', 'stats', 'rolActual', 'idActual')),
        ]);
    }

    // ─── Nuevo ────────────────────────────────────────────────────────────

    public function nuevo()
    {
        return view('layouts/main', [
            'titulo'    => 'Nuevo Usuario',
            'contenido' => view('usuarios/form', [
                'usuario'  => null,
                'roles'    => UsuarioModel::$roles,
                'esNuevo'  => true,
            ]),
        ]);
    }

    public function guardar()
    {
        $rules = [
            'username'         => 'required|min_length[3]|max_length[50]|is_unique[usuarios.username]',
            'nombre_completo'  => 'required|min_length[3]|max_length[150]',
            'email'            => 'permit_empty|valid_email',
            'rol'              => 'required|in_list[admin,jefe,operador,consulta]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->model->crearUsuario($this->request->getPost());

        return redirect()->to(site_url('usuarios'))
            ->with('success', 'Usuario creado correctamente.');
    }

    // ─── Editar ───────────────────────────────────────────────────────────

    public function editar(int $id)
    {
        $usuario = $this->model->find($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }
        return view('layouts/main', [
            'titulo'    => 'Editar Usuario',
            'contenido' => view('usuarios/form', [
                'usuario'  => $usuario,
                'roles'    => UsuarioModel::$roles,
                'esNuevo'  => false,
            ]),
        ]);
    }

    public function actualizar(int $id)
    {
        $usuario = $this->model->find($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        // No permitir que el único admin se quite el rol admin
        if ($usuario->rol === 'admin' && $this->request->getPost('rol') !== 'admin') {
            $adminsActivos = $this->model->where('rol', 'admin')->where('activo', 1)->countAllResults();
            if ($adminsActivos <= 1) {
                return redirect()->back()->withInput()
                    ->with('error', 'No puede cambiar el rol del único administrador activo del sistema.');
            }
        }

        $rules = [
            'username'        => "required|min_length[3]|max_length[50]|is_unique[usuarios.username,id,{$id}]",
            'nombre_completo' => 'required|min_length[3]|max_length[150]',
            'email'           => 'permit_empty|valid_email',
            'rol'             => 'required|in_list[admin,jefe,operador,consulta]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'              => $id,
            'username'        => $this->request->getPost('username'),
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'email'           => $this->request->getPost('email'),
            'rol'             => $this->request->getPost('rol'),
        ];
        $this->model->save($data);

        return redirect()->to(site_url('usuarios'))
            ->with('success', 'Usuario actualizado correctamente.');
    }

    // ─── Cambiar contraseña ───────────────────────────────────────────────

    public function cambiarPassword(int $id)
    {
        $usuario = $this->model->find($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        if ($this->request->getMethod() !== 'post') {
            return view('layouts/main', [
                'titulo'    => 'Cambiar Contraseña — ' . $usuario->nombre_completo,
                'contenido' => view('usuarios/cambiar_password', compact('usuario')),
            ]);
        }

        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->model->cambiarPassword($id, $this->request->getPost('password'));

        return redirect()->to(site_url('usuarios'))
            ->with('success', 'Contraseña de ' . $usuario->nombre_completo . ' actualizada.');
    }

    // ─── Baja / Reactivar ─────────────────────────────────────────────────

    public function desactivar(int $id)
    {
        // No puede darse de baja a sí mismo
        if ($id === (int) session()->get('usuario_id')) {
            return redirect()->to(site_url('usuarios'))
                ->with('error', 'No puede desactivar su propia cuenta.');
        }

        $usuario = $this->model->find($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        // No permitir dar de baja al único admin activo
        if ($usuario->rol === 'admin') {
            $adminsActivos = $this->model->where('rol', 'admin')->where('activo', 1)->countAllResults();
            if ($adminsActivos <= 1) {
                return redirect()->to(site_url('usuarios'))
                    ->with('error', 'No puede desactivar el único administrador activo del sistema.');
            }
        }

        $this->model->desactivar($id);
        return redirect()->to(site_url('usuarios'))
            ->with('success', 'Usuario ' . $usuario->username . ' desactivado.');
    }

    public function reactivar(int $id)
    {
        $usuario = $this->model->find($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }
        $this->model->reactivar($id);
        return redirect()->to(site_url('usuarios'))
            ->with('success', 'Usuario ' . $usuario->username . ' reactivado.');
    }
}
