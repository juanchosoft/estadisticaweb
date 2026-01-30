<?php
if (session_status() === PHP_SESSION_NONE) {
  ob_start();
  session_start();
}

require_once __DIR__ . '/admin/classes/SessionData.php';
require_once __DIR__ . '/admin/classes/DbConection.php';
require_once __DIR__ . '/admin/classes/Util.php';
require_once __DIR__ . '/admin/classes/Login.php';

header('Content-Type: application/json; charset=utf-8');

function jsonExit(array $payload, int $httpCode = 200): void {
  http_response_code($httpCode);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jsonExit(['status' => 'error', 'message' => 'Método no permitido.'], 405);
}

$nickname = isset($_POST['nickname']) ? trim((string)$_POST['nickname']) : '';
$hashpass = isset($_POST['hashpass']) ? trim((string)$_POST['hashpass']) : '';

if ($nickname === '' || $hashpass === '') {
  jsonExit(['status' => 'error', 'message' => 'Todos los campos son obligatorios.'], 422);
}

// ✅ Mantengo tu comportamiento: se hace md5
$hashpassMd5 = md5($hashpass);

try {
  $arr = ['nickname' => $nickname, 'hashpass' => $hashpassMd5];
  $res = Login::login($arr);

  $isvalid = (bool)($res['output']['valid'] ?? false);

  if ($isvalid) {
    $user = $res['output']['response'][0] ?? null;

    if (!$user) {
      // Si por alguna razón valid=true pero no hay usuario, lo tratamos como inválido
      jsonExit(['status' => 'error', 'message' => 'Usuario o contraseña inválida.'], 401);
    }

    $_SESSION['session_user'] = $user;
    $_SESSION['mostrar_bienvenida'] = true;

    jsonExit(['status' => 'success', 'redirect' => 'dashboard.php']);
  }

  // ✅ Mensaje exacto cuando usuario/contraseña no coinciden
  jsonExit(['status' => 'error', 'message' => 'Usuario o contraseña inválida.'], 401);

} catch (Throwable $e) {
  // No exponemos detalles
  jsonExit(['status' => 'error', 'message' => 'Ocurrió un error al iniciar sesión.'], 500);
}
