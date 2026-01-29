<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
if (ob_get_length()) { ob_clean(); }

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../include/generic_classes.php';
require_once __DIR__ . '/../include/mail_config.php';
require_once __DIR__ . '/../include/mailer.php';

date_default_timezone_set('America/Bogota');

function out(bool $ok, string $msg): void {
  echo json_encode(['ok' => $ok, 'msg' => $msg]);
  exit;
}

function clean(string $v): string {
  return trim($v);
}

function randomTempPassword(int $len = 10): string {
  $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#%';
  $out = '';
  for ($i=0; $i<$len; $i++) $out .= $chars[random_int(0, strlen($chars)-1)];
  return $out;
}

try {
  $login = clean((string)($_POST['login'] ?? ''));

  if ($login === '') {
    out(false, 'Debes escribir tu correo o usuario.');
  }

  $isEmail = (bool)filter_var($login, FILTER_VALIDATE_EMAIL);

  $db  = new DbConection();
  $pdo = $db->openConect();

  $table = $db->getTable('tbl_votantes');

  // Busca por email o username
  if ($isEmail) {
    $sql = "SELECT id, email, username FROM {$table} WHERE email = :v LIMIT 1";
  } else {
    $sql = "SELECT id, email, username FROM {$table} WHERE username = :v LIMIT 1";
  }

  $st = $pdo->prepare($sql);
  $st->execute([':v' => $login]);
  $user = $st->fetch(PDO::FETCH_ASSOC);

  // Respuesta genérica (seguridad: no revela si existe o no)
  if (!$user) {
    out(true, 'Si existe en el sistema, te enviamos una contraseña temporal al correo registrado.');
  }

  $email = (string)($user['email'] ?? '');
  if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(false, 'Este usuario no tiene un correo válido registrado. Contacta soporte.');
  }

  $tempPass = randomTempPassword(10);

  /**
   * ✅ COMPATIBILIDAD CON TU LOGIN:
   * - Si HOY tu sistema compara password en texto plano: DEJA false
   * - Si ya usas password_verify(): pon true
   */
  $PASSWORD_IS_HASHED = false;

  $savePass = $PASSWORD_IS_HASHED ? password_hash($tempPass, PASSWORD_BCRYPT) : $tempPass;

  // Actualiza password en tbl_votantes
  $up = $pdo->prepare("UPDATE {$table} SET password = :p WHERE id = :id LIMIT 1");
  $up->execute([':p' => $savePass, ':id' => (int)$user['id']]);

  // Email
  $toName  = (string)($user['username'] ?? 'Usuario');
  $subject = 'Contraseña temporal - Acceso al sistema';

  $html = "
  <div style='font-family:Arial,sans-serif;background:#f6f8fc;padding:18px;'>
    <div style='max-width:620px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid rgba(15,23,42,.10)'>
      <div style='padding:16px 18px;color:#fff;background:linear-gradient(135deg,#021b5a,#0B3EDC);'>
        <div style='font-weight:900;font-size:16px;'>Recuperación de contraseña</div>
        <div style='opacity:.92;font-size:13px;font-weight:700;'>Contraseña temporal generada</div>
      </div>
      <div style='padding:16px 18px;color:#0f172a;'>
        <p style='margin:0 0 10px;font-weight:800;'>Hola {$toName},</p>
        <p style='margin:0 0 12px;font-weight:700;color:#334155;'>
          Se generó una contraseña temporal para que puedas ingresar al sistema.
        </p>

        <div style='padding:12px 14px;border-radius:14px;background:rgba(11,62,220,.08);border:1px solid rgba(11,62,220,.20);'>
          <div style='font-weight:900;color:#021b5a;margin-bottom:6px;'>Tu contraseña temporal:</div>
          <div style='font-size:20px;font-weight:950;letter-spacing:1px;color:#0B3EDC;'>{$tempPass}</div>
        </div>

        <p style='margin:12px 0 0;font-weight:700;color:#475569;font-size:12px;'>
          Recomendación: ingresa y cambia tu contraseña apenas accedas.
        </p>
      </div>
      <div style='padding:12px 18px;background:#f8fafc;color:#64748b;font-size:11px;font-weight:700;'>
        Si tú no solicitaste este cambio, por favor contacta al administrador.
      </div>
    </div>
  </div>";

  $sent = Mailer::sendSMTP($email, $toName, $subject, $html);
  if (!$sent['ok']) {
    out(false, 'No fue posible enviar el correo en este momento. Intenta más tarde.');
  }

  out(true, 'Listo. Si existe en el sistema, te enviamos una contraseña temporal al correo registrado.');

} catch (Throwable $e) {
  out(false, 'Error interno. Intenta más tarde.');
}
