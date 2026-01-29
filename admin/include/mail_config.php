<?php
declare(strict_types=1);

/**
 * ============================
 *  SMTP HOSTINGER (Titan Mail)
 * ============================
 * Dominio: estadisticas360.com
 * Seguridad: SSL (recomendado)
 */

// Servidor SMTP
define('MAIL_HOST', 'smtp.titan.email');
define('MAIL_PORT', 465);
define('MAIL_SECURE', 'ssl');

// Credenciales (correo corporativo)
define('MAIL_USERNAME', 'soporte@estadisticas360.com');

// ⚠️ NO pongas la contraseña aquí en texto plano
// Usa una variable de entorno
define('MAIL_PASSWORD', getenv('Martint3933++--$$--'));

// Remitente
define('MAIL_FROM_EMAIL', 'soporte@estadisticas360.com');
define('MAIL_FROM_NAME', 'Soporte - Estadísticas 360');
