<?php
require_once __DIR__ . '/LoginSecurity.php';

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Login
{

    public function __construct() {}

    public static function login($rqst)
    {
        // Obtención de parámetros de entrada
        $username = isset($rqst['nickname']) ? trim($rqst['nickname']) : '';
        $password = isset($rqst['hashpass']) ? $rqst['hashpass'] : '';




        // Validación de entrada - longitudes máximas y mínimas
        if (empty($username) || empty($password)) {
            self::logFailedAttempt($username, 'Campos vacíos');
            return Util::error_wrong_data_login();
        }

        if (strlen($username) > 100 || strlen($username) < 3) {
            self::logFailedAttempt($username, 'Longitud de usuario inválida');
            return Util::error_wrong_data_login();
        }


        if (strlen($password) < 3 || strlen($password) > 255) {
            self::logFailedAttempt($username, 'Longitud de contraseña inválida');
            return Util::error_wrong_data_login();
        }


        // Protección contra fuerza bruta - verificar si la cuenta está bloqueada
        $loginSecurity = new LoginSecurity();
        $bloqueoInfo = $loginSecurity->verificarBloqueo($username);

        if ($bloqueoInfo['bloqueada']) {
            self::logFailedAttempt($username, 'Cuenta bloqueada - demasiados intentos');
            // Delay adicional para dificultar ataques
            sleep(2);
            return [
                'output' => [
                    'valid' => false,
                    'message' => $bloqueoInfo['mensaje']
                ]
            ];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        // Hash de la contraseña
        if (strlen($password) > 2) {
            $password = Util::make_hash_pass($password);
        }

        // Consulta SOLO los campos necesarios (no SELECT *)
        $q = "SELECT id, username, nombre_completo, habilitado, codigo_departamento, codigo_municipio
              FROM " . $db->getTable('tbl_votantes') . "
              WHERE username = :username AND habilitado='si' LIMIT 1";
        $arrparam = [":username" => $username];

        $result = $pdo->prepare($q);
        $loginSuccess = false;

        if ($result->execute($arrparam)) {
            $arr = $result->fetchAll(PDO::FETCH_ASSOC);

            if (count($arr) > 0) {
                $user = $arr[0];
                // OBTENER NOMBRE DEL MUNICIPIO
                $qMuni = "SELECT municipio 
                        FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
                        WHERE codigo_departamento = :dep
                            AND codigo_muncipio = :mun
                        LIMIT 1";
                $stmtMuni = $pdo->prepare($qMuni);
                $stmtMuni->execute([
                    ":dep" => $user['codigo_departamento'],
                    ":mun" => $user['codigo_municipio']
                ]);
                $infoMuni = $stmtMuni->fetch(PDO::FETCH_ASSOC);
                if ($infoMuni) {
                    $user['municipio_nombre'] = $infoMuni['municipio'];
                } else {
                    $user['municipio_nombre'] = "Municipio no identificado";
                }
                // OBTENER NOMBRE DEL DEPARTAMENTO desde tbl_departamentos
                $qDepto = "SELECT departamento 
                        FROM " . $db->getTable('tbl_departamentos') . "
                        WHERE codigo_departamento = :dep
                        LIMIT 1";

                $stmtDepto = $pdo->prepare($qDepto);
                $stmtDepto->execute([":dep" => $user['codigo_departamento']]);

                $infoDepto = $stmtDepto->fetch(PDO::FETCH_ASSOC);

                if ($infoDepto) {
                    $user['departamento_nombre'] = $infoDepto['departamento'];
                } else {
                    $user['departamento_nombre'] = "Departamento no identificado";
                }

                // Obtener el hash de la contraseña almacenado
                $qPass = "SELECT password FROM " . $db->getTable('tbl_votantes') . " WHERE id = :id LIMIT 1";
                $resultPass = $pdo->prepare($qPass);
                $resultPass->execute([":id" => $user['id']]);
                $passData = $resultPass->fetch(PDO::FETCH_ASSOC);

                // Comparación timing-safe del password
                if ($passData && hash_equals($passData['password'], $password)) {
                    $loginSuccess = true;

                    // Registrar login exitoso: actualiza ultimo_acceso, limpia intentos_login y cuenta_bloqueada_hasta
                    $loginSecurity->registrarLoginExitoso($user['id']);

                    // No exponer datos sensibles como password en la respuesta
                    $user['application'][] = Util::get_app_id();

                    $arrjson = [
                        'output' => [
                            'valid' => true,
                            'response' => [$user]
                        ]
                    ];

                    Util::trace_session_user(['usuarioId' => $user['id']]);

                    // Log de login exitoso
                    self::logSuccessfulLogin($username, $user['id']);
                } else {
                    $loginSuccess = false;
                }
            }
        }

        // Si el login falló por cualquier razón
        if (!$loginSuccess) {
            // Registrar intento fallido en base de datos
            $bloqueada = $loginSecurity->registrarIntentoFallido($username);

            if ($bloqueada) {
                self::logFailedAttempt($username, 'Cuenta bloqueada por múltiples intentos fallidos');
            } else {
                self::logFailedAttempt($username, 'Credenciales inválidas');
            }

            // Delay aleatorio para prevenir timing attacks
            usleep(rand(100000, 500000)); // 0.1 a 0.5 segundos

            $arrjson = Util::error_wrong_data_login();
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Registra intentos fallidos en log para auditoría
     */
    private static function logFailedAttempt($username, $reason)
    {
        $ip = self::getClientIP();
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] FAILED LOGIN - User: {$username}, IP: {$ip}, Reason: {$reason}\n";

        // Log a archivo (asegúrate de que el directorio exista y tenga permisos)
        $logFile = __DIR__ . '/../logs/failed_logins.log';
        @error_log($logMessage, 3, $logFile);
    }

    /**
     * Registra logins exitosos en log para auditoría
     */
    private static function logSuccessfulLogin($username, $userId)
    {
        $ip = self::getClientIP();
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] SUCCESSFUL LOGIN - User: {$username}, ID: {$userId}, IP: {$ip}\n";

        $logFile = __DIR__ . '/../logs/successful_logins.log';
        @error_log($logMessage, 3, $logFile);
    }

    /**
     * Obtiene la IP real del cliente considerando proxies
     */
    private static function getClientIP()
    {
        $ip = 'UNKNOWN';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Puede contener múltiples IPs, tomar la primera
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Validar que sea una IP válida
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return 'INVALID_IP';
    }
}