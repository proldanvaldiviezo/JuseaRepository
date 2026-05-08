<?php

use CodeIgniter\Router\RouteCollection;

/**
 * JUSEA CMN v2.0 - Definicion de Rutas
 *
 * @var RouteCollection $routes
 */

// Controlador por defecto
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('AuthController');
$routes->setDefaultMethod('login');

// ============================================================
// RUTAS PUBLICAS (sin autenticacion)
// ============================================================
$routes->get('/', 'AuthController::login');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::authenticate');
$routes->get('logout', 'AuthController::logout');

// ============================================================
// RUTAS PROTEGIDAS (requieren autenticacion)
// ============================================================
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // --- Dashboard ---
    $routes->get('dashboard', 'DashboardController::index');

    // --- Sanciones Disciplinarias ---
    $routes->group('sancion', function ($routes) {
        $routes->get('cuadros', 'SancionController::formCuadros');
        $routes->post('cuadros/guardar', 'SancionController::guardarCuadros');
        $routes->get('cuadros/descargar/(:num)/(:alpha)', 'SancionController::descargarCuadros/$1/$2');
        $routes->get('cuadros/revision/(:num)', 'SancionController::descargarRevisionCuadros/$1');

        $routes->get('cadetes', 'SancionController::formCadetes');
        $routes->post('cadetes/guardar', 'SancionController::guardarCadetes');
        $routes->get('cadetes/descargar/(:num)/(:alpha)', 'SancionController::descargarCadetes/$1/$2');

        // Eliminar (admin + jefe)
        $routes->post('cuadros/eliminar/(:num)', 'SancionController::eliminarCuadros/$1', ['filter' => 'role:admin,jefe']);
        $routes->post('cadetes/eliminar/(:num)', 'SancionController::eliminarCadetes/$1', ['filter' => 'role:admin,jefe']);
    });

    // --- Actuaciones Administrativas ---
    $routes->group('actuacion', function ($routes) {
        // Bienes del Estado
        $routes->get('bienes',                      'ActuacionController::formBienes');
        $routes->post('bienes/guardar',             'ActuacionController::guardarBienes');
        $routes->get('bienes/historial',            'ActuacionController::historialBienes');
        $routes->get('bienes/ver/(:num)',           'ActuacionController::verBienes/$1');
        $routes->get('bienes/generar/(:num)',       'ActuacionController::generarBienes/$1');

        // Accidente / Enfermedad
        $routes->get('accidente',                   'ActuacionController::formAccidente');
        $routes->post('accidente/guardar',          'ActuacionController::guardarAccidente');
        $routes->get('accidente/historial',         'ActuacionController::historialAccidente');
        $routes->get('accidente/ver/(:num)',        'ActuacionController::verAccidente/$1');
        $routes->get('accidente/generar/(:num)',    'ActuacionController::generarAccidente/$1');

        // Eliminar (admin + jefe)
        $routes->post('bienes/eliminar/(:num)',    'ActuacionController::eliminarBienes/$1',    ['filter' => 'role:admin,jefe']);
        $routes->post('accidente/eliminar/(:num)', 'ActuacionController::eliminarAccidente/$1', ['filter' => 'role:admin,jefe']);

        // ─── Adjuntos (archivos adicionales para el expediente ZIP) ─────────
        $routes->post('bienes/adjunto/subir/(:num)',        'ActuacionController::subirAdjuntoBienes/$1');
        $routes->get( 'bienes/adjunto/listar/(:num)',       'ActuacionController::listarAdjuntosBienes/$1');
        $routes->post('bienes/adjunto/eliminar/(:num)',     'ActuacionController::eliminarAdjuntoBienes/$1',    ['filter' => 'role:admin,jefe']);
        $routes->post('accidente/adjunto/subir/(:num)',     'ActuacionController::subirAdjuntoAccidente/$1');
        $routes->get( 'accidente/adjunto/listar/(:num)',    'ActuacionController::listarAdjuntosAccidente/$1');
        $routes->post('accidente/adjunto/eliminar/(:num)',  'ActuacionController::eliminarAdjuntoAccidente/$1', ['filter' => 'role:admin,jefe']);
    });

    // --- Padrón de Personal CMN ---
    $routes->group('personal', function ($routes) {
        $routes->get('/',                      'PersonalController::index');
        $routes->get('exportar',               'PersonalController::exportar');
        $routes->get('historial/(:segment)',   'PersonalController::historial/$1');

        // Alta / Baja (admin y jefe)
        $routes->get('nuevo',                  'PersonalController::nuevo',           ['filter' => 'role:admin,jefe']);
        $routes->get('buscar-apicps',          'PersonalController::buscarEnApicps',  ['filter' => 'role:admin,jefe']);
        $routes->post('guardar',               'PersonalController::guardar',         ['filter' => 'role:admin,jefe']);
        $routes->post('dar-baja/(:segment)',   'PersonalController::darBaja/$1',      ['filter' => 'role:admin,jefe']);
        $routes->post('reactivar/(:segment)',  'PersonalController::reactivar/$1',    ['filter' => 'role:admin,jefe']);
    });

    // --- Historial de Sanciones ---
    $routes->group('historial', function ($routes) {
        $routes->get('/', 'HistorialController::index');
        $routes->post('buscar', 'HistorialController::buscar');
        $routes->get('exportar', 'HistorialController::exportar');
    });

    // --- Encabezado (solo admin) ---
    $routes->group('encabezado', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/', 'EncabezadoController::index');
        $routes->post('actualizar', 'EncabezadoController::actualizar');
    });

    // --- Administracion de usuarios (solo admin) ---
    $routes->group('usuarios', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/',                          'UsuarioController::index');
        $routes->get('nuevo',                      'UsuarioController::nuevo');
        $routes->post('guardar',                   'UsuarioController::guardar');
        $routes->get('editar/(:segment)',          'UsuarioController::editar/$1');
        $routes->post('actualizar/(:segment)',     'UsuarioController::actualizar/$1');
        $routes->post('desactivar/(:segment)',     'UsuarioController::desactivar/$1');
        $routes->post('reactivar/(:segment)',      'UsuarioController::reactivar/$1');
    });

    // --- Configuracion de permisos (solo admin) ---
    $routes->group('permisos', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/',         'PermisosController::index');
        $routes->post('guardar',  'PermisosController::guardar');
    });

    // --- API (AJAX) ---
    $routes->group('api', function ($routes) {
        $routes->get('persona/buscar-dni',      'ApiController::buscarPersonaPorDni');
        $routes->get('persona/buscar-apellido', 'ApiController::buscarPersonaPorApellido');
        $routes->get('persona/buscar-nombre',   'ApiController::buscarPersonaPorNombre');
        $routes->post('ia/redactar-motivos',    'ApiController::redactarMotivos');
        $routes->post('ia/sugerir-normativa',   'ApiController::sugerirNormativa');
    });
});
