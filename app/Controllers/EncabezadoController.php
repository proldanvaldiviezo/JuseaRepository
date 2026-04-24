<?php
namespace App\Controllers;

use App\Models\EncabezadoModel;
use CodeIgniter\Controller;

/**
 * Controlador de Encabezado Institucional
 * Solo accesible para rol 'admin'.
 */
class EncabezadoController extends Controller
{
    protected $encabezadoModel;

    public function __construct()
    {
        $this->encabezadoModel = new EncabezadoModel();
    }

    public function index()
    {
        $encabezado = $this->encabezadoModel->obtenerVigente();

        return view('layouts/main', [
            'contenido' => view('encabezado/form', ['encabezado' => $encabezado]),
            'titulo'    => 'Configurar Encabezado Institucional',
        ]);
    }

    public function actualizar()
    {
        $rules = [
            'anio'     => 'required|exact_length[4]|numeric',
            'membrete' => 'required|min_length[3]|max_length[255]',
            'unidad'   => 'required|min_length[3]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'anio'     => $this->request->getPost('anio'),
            'membrete' => trim($this->request->getPost('membrete')),
            'unidad'   => trim($this->request->getPost('unidad')),
        ];

        $resultado = $this->encabezadoModel->actualizarEncabezado(
            $data,
            (int) session()->get('usuario_id')
        );

        if ($resultado) {
            return redirect()->to(site_url('encabezado'))
                ->with('success', 'Encabezado actualizado correctamente.');
        }

        return redirect()->back()
            ->with('error', 'Error al actualizar el encabezado.');
    }
}
