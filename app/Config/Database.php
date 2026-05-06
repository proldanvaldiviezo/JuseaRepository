<?php
namespace Config;
use CodeIgniter\Database\Config;
/**
 * JUSEA CMN v2.0 - Configuracion de Base de Datos
 *
 * Las credenciales se leen desde el archivo .env
 * Si no existe .env, se usan los valores por defecto.
 */
class Database extends Config
{
    /**
     * Grupo de conexion por defecto
     */
    public string $defaultGroup = 'default';
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => true,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];
    /**
     * Conexion principal
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => '10.16.224.10',
        'username'     => 'sa',
        'password'     => 'Cmn_1963W',
        'database'     => 'Partes',
        'DBDriver'     => 'SQLSRV',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'UTF-8',
        'DBCollat'     => '',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 1433,
        'numberNative' => false,
    ];
    public function __construct()
    {
        parent::__construct();
        // En produccion, desactivar debug SQL
        if (ENVIRONMENT === 'production') {
            $this->default['DBDebug'] = false;
        }
    }
}