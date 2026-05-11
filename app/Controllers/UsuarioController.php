<?php
namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\Controller;

/**
 * UsuarioController — v3 (SQL Server / AspNetUsers)
 * Gestión de acceso JUSEA: asigna/modifica rol en JUSEA_UsuarioRol.
 * No crea ni modifica usuarios en AspNetUsers (gestionado por sistema Partes).
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
        $usuarios  = $this->model->obtenerTodos();
        $roles     = UsuarioModel::$roles;
        $rolActual = session()->get('usuario_rol');
        $idActual  = session()->get('usuario_id');

        $stats = [];
        foreach (array_keys($roles) as $r) {
            $stats[$r] = count(array_filter($usuarios, fn($u) => $u->rol === $r && $u->activo_jusea));
        }
        $stats['inactivos'] = count(array_filter($usuarios, fn($u) => !$u->activo_jusea));

        return view('layouts/main', [
            'titulo'    => 'Accesos JUSEA',
            'contenido' => view('usuarios/lista',
                compact('usuarios', 'roles', 'stats', 'rolActual', 'idActual')),
        ]);
    }

    // ─── Agregar acceso JUSEA ─────────────────────────────────────────────

    public function nuevo()
    {
        return view('layouts/main', [
            'titulo'    => 'Agregar Acceso JUSEA',
            'contenido' => view('usuarios/form', [
                'usuario' => null,
                'roles'   => UsuarioModel::$roles,
                'esNuevo' => true,
            ]),
        ]);
    }

    /**
     * Busca el username en AspNetUsers y le asigna un rol JUSEA.
     * No crea usuarios — solo habilita el acceso al sistema JUSEA.
     */
    public function guardar()
    {
        $rules = [
            'user_id' => 'required|min_length[3]|max_length[450]',
            'rol'     => 'required|in_list[admin,jefe,operador,consulta]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userId = $this->request->getPost('user_id');
        $rol    = $this->request->getPost('rol');

        // Verificar que el usuario existe en AspNetUsers
        $aspUser = \Config\Database::connect()
            ->table('AspNetUsers')
            ->select('Id, UserName, Nombre, Apellido')
            ->where('Id', $userId)
            ->get()->getRow();

        if (!$aspUser) {
            return redirect()->back()->withInput()
                ->with('error', 'El usuario seleccionado no existe en el sistema Partes.');
        }

        $this->model->asignarRol($aspUser->Id, $rol);

        $nombre = trim(($aspUser->Apellido ?? '') . ', ' . ($aspUser->Nombre ?? '')) ?: $aspUser->UserName;
        return redirect()->to(site_url('usuarios'))
            ->with('success', "Acceso JUSEA otorgado a {$nombre} con rol «{$rol}».");
    }

    // ─── Editar rol ───────────────────────────────────────────────────────

    public function editar(string $id)
    {
        $usuario = $this->buscarUsuarioPorId($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        return view('layouts/main', [
            'titulo'    => 'Editar Acceso JUSEA',
            'contenido' => view('usuarios/form', [
                'usuario' => $usuario,
                'roles'   => UsuarioModel::$roles,
                'esNuevo' => false,
            ]),
        ]);
    }

    public function actualizar(string $id)
    {
        $usuario = $this->buscarUsuarioPorId($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        // No permitir que el único admin se quite el rol admin
        if ($usuario->rol === 'admin' && $this->request->getPost('rol') !== 'admin') {
            $adminsActivos = \Config\Database::connect()
                ->table('JUSEA_UsuarioRol')
                ->where('rol', 'admin')->where('activo', 1)->countAllResults();
            if ($adminsActivos <= 1) {
                return redirect()->back()->withInput()
                    ->with('error', 'No puede cambiar el rol del único administrador activo del sistema.');
            }
        }

        $rules = ['rol' => 'required|in_list[admin,jefe,operador,consulta]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->model->asignarRol($id, $this->request->getPost('rol'));

        return redirect()->to(site_url('usuarios'))
            ->with('success', 'Rol actualizado correctamente.');
    }

    // ─── Baja / Reactivar ─────────────────────────────────────────────────

    public function desactivar(string $id)
    {
        if ($id === session()->get('usuario_id')) {
            return redirect()->to(site_url('usuarios'))
                ->with('error', 'No puede desactivar su propia cuenta.');
        }

        $usuario = $this->buscarUsuarioPorId($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        if ($usuario->rol === 'admin') {
            $adminsActivos = \Config\Database::connect()
                ->table('JUSEA_UsuarioRol')
                ->where('rol', 'admin')->where('activo', 1)->countAllResults();
            if ($adminsActivos <= 1) {
                return redirect()->to(site_url('usuarios'))
                    ->with('error', 'No puede desactivar el único administrador activo del sistema.');
            }
        }

        $this->model->desactivar($id);
        return redirect()->to(site_url('usuarios'))
            ->with('success', 'Usuario ' . $usuario->UserName . ' desactivado.');
    }

    public function reactivar(string $id)
    {
        $usuario = $this->buscarUsuarioPorId($id);
        if (!$usuario) {
            return redirect()->to(site_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }
        $this->model->reactivar($id);
        return redirect()->to(site_url('usuarios'))
            ->with('success', 'Usuario ' . $usuario->UserName . ' reactivado.');
    }

    // ─── Helper ───────────────────────────────────────────────────────────

    private function buscarUsuarioPorId(string $id): ?object
    {
        return \Config\Database::connect()
            ->table('AspNetUsers')
            ->select('AspNetUsers.Id, AspNetUsers.UserName, AspNetUsers.Nombre,
                      AspNetUsers.Apellido, AspNetUsers.DNI, AspNetUsers.GRADO,
                      JUSEA_UsuarioRol.rol, JUSEA_UsuarioRol.activo AS activo_jusea')
            ->join('JUSEA_UsuarioRol', 'JUSEA_UsuarioRol.user_id = AspNetUsers.Id', 'inner')
            ->where('AspNetUsers.Id', $id)
            ->get()->getRow();
    }
}
