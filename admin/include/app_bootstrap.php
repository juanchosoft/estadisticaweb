<?php
declare(strict_types=1);

/**
 * =========================================================
 *  HARDENING BÁSICO (PRODUCCIÓN)
 * =========================================================
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/**
 * ✅ Cookies de sesión seguras (antes de session_start)
 */
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if ($https) {
  ini_set('session.cookie_secure', '1');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

/**
 * ✅ Headers de seguridad
 */
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Resource-Policy: same-site');

if ($https) {
  header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

/**
 * ✅ CSP (ajustada a tus CDNs actuales)
 */
$csp = implode('; ', [
  "default-src 'self'",
  "base-uri 'self'",
  "object-src 'none'",
  "frame-ancestors 'none'",
  "img-src 'self' data: https:",
  "font-src 'self' data: https:",
  "style-src 'self' 'unsafe-inline' https:",
  "script-src 'self' 'unsafe-inline' https: https://ajax.googleapis.com https://cdn.jsdelivr.net",
  "connect-src 'self' https:",
  "upgrade-insecure-requests"
]);
header("Content-Security-Policy: {$csp}");

/**
 * ✅ CSRF token
 */
if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || strlen($_SESSION['csrf_token']) < 32) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$CSRF_TOKEN = $_SESSION['csrf_token'];

function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
