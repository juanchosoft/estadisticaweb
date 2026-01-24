<?php
declare(strict_types=1);

/**
 * ==========================================================
 *  Estadística 360 | generic_classes.php (PRO + Seguro)
 *  - Mantiene tus requires
 *  - Endurece sesiones
 *  - Headers de seguridad
 *  - Helpers: escape, base_url, csrf, json
 * ==========================================================
 */

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

/* ===========================
   ZONA HORARIA (Colombia)
=========================== */
date_default_timezone_set('America/Bogota');

/* ===========================
   HTTPS (si está detrás de proxy)
=========================== */
$isHttps = (
  (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
  (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
  (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
);

/* ===========================
   HEADERS DE SEGURIDAD
   (No rompe Bootstrap/CDNs)
=========================== */
if (!headers_sent()) {
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('Referrer-Policy: strict-origin-when-cross-origin');

  // Permissions-Policy: desactiva cosas no usadas por defecto
  header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

  // HSTS solo si es HTTPS (no activarlo en local http)
  if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
  }
}

/* ===========================
   SESIÓN SEGURA
=========================== */
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => $cookieParams['path'] ?? '/',
  'domain'   => $cookieParams['domain'] ?? '',
  'secure'   => $isHttps,     // ✅ solo secure en https
  'httponly' => true,         // ✅ evita JS access
  'samesite' => 'Lax',         // ✅ balance UX y seguridad
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// ✅ Evita session fixation
if (empty($_SESSION['__sess_init__'])) {
  session_regenerate_id(true);
  $_SESSION['__sess_init__'] = time();
}

// ✅ Timeout (30 min)
$maxIdle = 30 * 60;
if (!empty($_SESSION['__last_activity__']) && (time() - (int)$_SESSION['__last_activity__']) > $maxIdle) {
  session_unset();
  session_destroy();
  session_start();
}
$_SESSION['__last_activity__'] = time();

/* ===========================
   REQUIRES DEL SISTEMA (tus rutas)
=========================== */
require 'admin/classes/Util.php';
require 'admin/classes/DbConection.php';
require 'admin/include/generic_validate_session.php';
require 'admin/classes/SessionData.php';

/* ===========================
   HELPERS GENERALES
=========================== */

/**
 * Escape HTML (anti XSS) para imprimir en vistas
 */
function h(?string $str): string {
  return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Obtiene base URL del proyecto sin el archivo actual
 * (tu función, pero endurecida)
 */
function base_url(): string {
  $port = $_SERVER['SERVER_PORT'] ?? '80';

  $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
  $nameServer = ($port !== "80" && $port !== "443") ? ($serverName . ":" . $port) : $serverName;

  $scheme = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
  ) ? 'https' : 'http';

  $uri = $_SERVER['REQUEST_URI'] ?? '/';

  // Limpia querystring
  $uri = strtok($uri, '?') ?: '/';

  $url = sprintf("%s://%s%s", $scheme, $nameServer, $uri);

  // Quita el archivo actual si termina en .php
  $script = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
  if ($script && str_ends_with($script, '.php')) {
    $url = str_replace($script, '', $url);
  }

  return rtrim($url, '/') . '/';
}

/**
 * CSRF token
 */
function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return (string)$_SESSION['csrf_token'];
}

/**
 * Valida CSRF token
 */
function csrf_validate(?string $token): bool {
  if (empty($_SESSION['csrf_token']) || empty($token)) return false;
  return hash_equals((string)$_SESSION['csrf_token'], (string)$token);
}

/**
 * Respuesta JSON segura
 */
function json_response(array $payload, int $status = 200): void {
  if (!headers_sent()) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
  }
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

/**
 * Método request helper
 */
function request_method(): string {
  return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

/**
 * Detecta AJAX
 */
function is_ajax(): bool {
  return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}
