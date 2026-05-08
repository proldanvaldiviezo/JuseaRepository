<?php
namespace App\Libraries;

/**
 * Cliente HTTP para la API CPS del Ejército Argentino.
 * Autentica usuarios contra el servidor central de identidades.
 */
class ApiCpsService
{
    private string $baseUrl;
    private int    $timeout = 10;

    private bool $sslVerify;

    public function __construct()
    {
        $this->baseUrl   = rtrim(env('APICPS_BASE_URL', 'https://apicps.ejercito.mil.ar/api/v1/'), '/');
        $this->sslVerify = filter_var(env('APICPS_SSL_VERIFY', 'true'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Autentica un usuario contra APICPS.
     *
     * @return array{dni: string, username: string, access_token: string}|null
     *         null  → credenciales inválidas (401 / respuesta sin token)
     * @throws \RuntimeException si no se puede conectar con el servidor
     */
    public function login(string $username, string $password): ?array
    {
        $url  = $this->baseUrl . '/login';
        $body = json_encode(['username' => $username, 'password' => $password]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->sslVerify,
            CURLOPT_SSL_VERIFYHOST => $this->sslVerify ? 2 : 0,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            log_message('error', '[ApiCpsService] curl error: ' . $curlError);
            throw new \RuntimeException('No se pudo conectar con el servidor de autenticación: ' . $curlError);
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException('El servidor de autenticación respondió con código ' . $httpCode);
        }

        $data = json_decode($response, true);

        if (
            empty($data['access_token']) ||
            empty($data['data']['dni'])
        ) {
            return null;
        }

        return [
            'dni'          => (string) $data['data']['dni'],
            'username'     => $data['data']['username'] ?? (string) $data['data']['dni'],
            'access_token' => $data['access_token'],
        ];
    }

    /**
     * Busca los datos completos de un integrante por DNI.
     * Requiere un token válido obtenido previamente con login().
     *
     * @return array{dni:string, nombre:string, apellido:string, grado:string, arma:string, username:string}|null
     *         null → no encontrado o token expirado
     * @throws \RuntimeException si no se puede conectar
     */
    public function buscarPorDni(string $dni, string $token): ?array
    {
        $url  = $this->baseUrl . '/personal';
        $body = json_encode(['dni' => (int) $dni]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->sslVerify,
            CURLOPT_SSL_VERIFYHOST => $this->sslVerify ? 2 : 0,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            log_message('error', '[ApiCpsService::buscarPorDni] curl error: ' . $curlError);
            throw new \RuntimeException('No se pudo conectar con el servidor de autenticación: ' . $curlError);
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException('El servidor de autenticación respondió con código ' . $httpCode);
        }

        $lista = json_decode($response, true);

        if (empty($lista) || !is_array($lista)) {
            return null;
        }

        $p = $lista[0];

        return [
            'dni'      => (string) ($p['dni']               ?? $dni),
            'nombre'   => $p['first_name']                  ?? '',
            'apellido' => $p['last_name']                   ?? '',
            'grado'    => $p['rank_code']                   ?? '',
            'arma'     => $p['arm_abbreviation']            ?? '',
            'username' => $p['username']                    ?? (string) $dni,
            'display'  => $p['display']                     ?? '',
        ];
    }
}
