<?php
namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Controlador del Dashboard (pantalla principal)
 */
class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'titulo'  => 'JUSEA CMN - Panel Principal',
            'usuario' => session()->get('usuario_nombre'),
            'rol'     => session()->get('usuario_rol'),
        ];

        return view('layouts/main', [
            'contenido' => view('dashboard/index', $data),
            'titulo'    => $data['titulo'],
        ]);
    }
}
