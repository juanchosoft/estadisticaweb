<?php
/**
 * Clase encargada de la seguridad del login
 * Maneja intentos fallidos, bloqueo de cuentas y último acceso
 * SOLO USA SQL ESTÁNDAR - NO procedimientos almacenados ni vistas
 * @author SPIDERSOFTWARE
 */
class LoginSecurity
{
    private $db;
    private $pdo;

    // Configuración de seguridad
    const MAX_INTENTOS_FALLIDOS = 10;
    const TIEMPO_BLOQUEO_MINUTOS = 30; // 30 minutos de bloqueo

    public function __construct()
    {
        $this->db = new DbConection();
        $this->pdo = $this->db->openConect();
    }

    public function __destruct()
    {
        if ($this->db) {
            $this->db->closeConect();
        }
    }

    /**
     * Verifica si una cuenta está bloqueada
     * Si el bloqueo expiró, se desbloquea automáticamente
     */
    public function verificarBloqueo($username)
    {
        // Query simple SELECT
        $q = "SELECT cuenta_bloqueada_hasta
              FROM " . $this->db->getTable('tbl_votantes') . "
              WHERE username = :username LIMIT 1";

        $result = $this->pdo->prepare($q);
        $result->execute([':username' => $username]);
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return ['bloqueada' => false, 'mensaje' => ''];
        }

        $cuentaBloqueadaHasta = $data['cuenta_bloqueada_hasta'];

        // Si no hay fecha de bloqueo, la cuenta no está bloqueada
        if (empty($cuentaBloqueadaHasta) || $cuentaBloqueadaHasta === null) {
            return ['bloqueada' => false, 'mensaje' => ''];
        }

        // Verificar si el tiempo de bloqueo ya pasó
        $ahora = new DateTime();
        $fechaBloqueo = new DateTime($cuentaBloqueadaHasta);

        if ($ahora >= $fechaBloqueo) {
            // El bloqueo expiró, limpiar con UPDATE simple
            $this->desbloquearCuenta($username);
            return ['bloqueada' => false, 'mensaje' => ''];
        }

        // La cuenta sigue bloqueada
        $minutosRestantes = ceil(($fechaBloqueo->getTimestamp() - $ahora->getTimestamp()) / 60);
        return [
            'bloqueada' => true,
            'mensaje' => "Cuenta bloqueada. Intente nuevamente en {$minutosRestantes} minutos."
        ];
    }

    /**
     * Registra un intento fallido de login
     * Incrementa el contador y bloquea si alcanza el límite
     */
    public function registrarIntentoFallido($username)
    {
        // SELECT simple para obtener intentos actuales
        $q = "SELECT intentos_login
              FROM " . $this->db->getTable('tbl_votantes') . "
              WHERE username = :username LIMIT 1";

        $result = $this->pdo->prepare($q);
        $result->execute([':username' => $username]);
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false; // Usuario no existe
        }

        $intentosActuales = (int)$data['intentos_login'];
        $nuevoIntento = $intentosActuales + 1;

        // Si alcanzó el límite, bloquear la cuenta
        if ($nuevoIntento >= self::MAX_INTENTOS_FALLIDOS) {
            $this->bloquearCuenta($username);

            // Log del bloqueo
            $this->logBloqueo($username, $nuevoIntento);

            return true; // Cuenta bloqueada
        }

        // UPDATE simple para incrementar intentos
        $qUpdate = "UPDATE " . $this->db->getTable('tbl_votantes') . "
                    SET intentos_login = :intentos
                    WHERE username = :username";

        $stmt = $this->pdo->prepare($qUpdate);
        $stmt->execute([
            ':intentos' => $nuevoIntento,
            ':username' => $username
        ]);

        return false; // No bloqueada aún
    }

    /**
     * Bloquea una cuenta por tiempo definido
     * UPDATE simple
     */
    private function bloquearCuenta($username)
    {
        $fechaBloqueo = new DateTime();
        $fechaBloqueo->modify('+' . self::TIEMPO_BLOQUEO_MINUTOS . ' minutes');

        // UPDATE simple
        $q = "UPDATE " . $this->db->getTable('tbl_votantes') . "
              SET cuenta_bloqueada_hasta = :fecha_bloqueo,
                  intentos_login = :intentos
              WHERE username = :username";

        $stmt = $this->pdo->prepare($q);
        $stmt->execute([
            ':fecha_bloqueo' => $fechaBloqueo->format('Y-m-d H:i:s'),
            ':intentos' => self::MAX_INTENTOS_FALLIDOS,
            ':username' => $username
        ]);
    }

    /**
     * Desbloquea una cuenta (cuando el tiempo de bloqueo expira)
     * UPDATE simple
     */
    private function desbloquearCuenta($username)
    {
        // UPDATE simple
        $q = "UPDATE " . $this->db->getTable('tbl_votantes') . "
              SET cuenta_bloqueada_hasta = NULL,
                  intentos_login = 0
              WHERE username = :username";

        $stmt = $this->pdo->prepare($q);
        $stmt->execute([':username' => $username]);
    }

    /**
     * Actualiza el último acceso y limpia intentos fallidos (login exitoso)
     * UPDATE simple con NOW()
     */
    public function registrarLoginExitoso($userId)
    {
        // UPDATE simple
        $q = "UPDATE " . $this->db->getTable('tbl_votantes') . "
              SET ultimo_acceso = NOW(),
                  intentos_login = 0,
                  cuenta_bloqueada_hasta = NULL
              WHERE id = :id";

        $stmt = $this->pdo->prepare($q);
        $stmt->execute([':id' => $userId]);
    }

    /**
     * Obtiene información de intentos para mostrar al usuario
     * SELECT simple
     */
    public function obtenerInfoIntentos($username)
    {
        // SELECT simple
        $q = "SELECT intentos_login, cuenta_bloqueada_hasta
              FROM " . $this->db->getTable('tbl_votantes') . "
              WHERE username = :username LIMIT 1";

        $result = $this->pdo->prepare($q);
        $result->execute([':username' => $username]);
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return ['intentos' => 0, 'bloqueada_hasta' => null];
        }

        return [
            'intentos' => (int)$data['intentos_login'],
            'bloqueada_hasta' => $data['cuenta_bloqueada_hasta'],
            'intentos_restantes' => self::MAX_INTENTOS_FALLIDOS - (int)$data['intentos_login']
        ];
    }

    /**
     * Log de bloqueo de cuenta en archivo
     */
    private function logBloqueo($username, $intentos)
    {
        $ip = $this->getClientIP();
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] CUENTA BLOQUEADA - User: {$username}, IP: {$ip}, Intentos: {$intentos}\n";

        $logFile = __DIR__ . '/../logs/blocked_accounts.log';
        @error_log($logMessage, 3, $logFile);
    }

    /**
     * Obtiene la IP real del cliente
     */
    private function getClientIP()
    {
        $ip = 'UNKNOWN';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return 'INVALID_IP';
    }
}
