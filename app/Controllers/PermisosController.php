<?php
namespace App\Controllers;

use App\Models\PermisosModel;
use App\Models\UsuarioModel;
use CodeIgniter\Controller;

/**
 * PermisosController — v1
 * Gestión de la matriz de permisos por rol (exclusivo administradores).
 */
class PermisosController extends Controller
{
    public function index()
    {
        $permisos = PermisosModel::cargar();
        $roles    = UsuarioModel::$roles;   // usa el catálogo de etiquetas de roles

        return view('layouts/main', [
            'titulo'    => 'Configuración de Permisos',
            'contenido' => view('permisos/index', compact('permisos', 'roles')),
        ]);
    }

    public function guardar()
    {
        $model  = new PermisosModel();
        $matriz = $this->request->getPost('permisos') ?? [];

        $model->guardarMatriz($matriz);

        return redirect()->to(site_url('permisos'))
            ->with('success', 'Permisos actualizados correctamente.');
    }
}
