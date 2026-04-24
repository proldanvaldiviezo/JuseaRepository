<?php
namespace App\Controllers;

use App\Models\SancionModel;
use CodeIgniter\Controller;

/**
 * Controlador de Historial de Sanciones
 * Búsqueda segura por DNI o Apellido con queries parametrizadas.
 */
class HistorialController extends Controller
{
    protected $sancionModel;

    public function __construct()
    {
        $this->sancionModel = new SancionModel();
    }

    public function index()
    {
        return view('layouts/main', [
            'contenido' => view('historial/index', ['resultados' => null]),
            'titulo'    => 'Historial de Sanciones',
        ]);
    }

    public function buscar()
    {
        $rules = [
            'criterio' => 'required|in_list[dni,apellido]',
            'valor'    => 'required|min_length[2]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ingrese un criterio de búsqueda válido.');
        }

        $criterio = $this->request->getPost('criterio');
        $valor    = trim($this->request->getPost('valor'));

        // Query completamente parametrizada (sin SQL injection)
        $resultados = $this->sancionModel->buscarHistorial($criterio, $valor);

        return view('layouts/main', [
            'contenido' => view('historial/index', [
                'resultados' => $resultados,
                'criterio'   => $criterio,
                'valor'      => $valor,
            ]),
            'titulo' => 'Historial de Sanciones',
        ]);
    }

    /**
     * Exportar resultados de búsqueda (futuro: Excel/PDF).
     */
    public function exportar()
    {
        // TODO: Implementar exportación a Excel/PDF
        return redirect()->back()->with('info', 'Funcionalidad de exportación en desarrollo.');
    }
}
