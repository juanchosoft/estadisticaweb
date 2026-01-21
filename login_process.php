<?php
if (session_status() === PHP_SESSION_NONE) {
  ob_start();
  session_start();
}

require_once __DIR__ . '/admin/classes/SessionData.php';
require_once __DIR__ . '/admin/classes/DbConection.php';
require_once __DIR__ . '/admin/classes/Util.php';
require_once __DIR__ . '/admin/classes/Login.php';

// Set header for JSON response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nickname'], $_POST['hashpass'])) {
    $nickname = trim($_POST['nickname']);
    $hashpass = trim($_POST['hashpass']);

    if (empty($nickname) || empty($hashpass)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    $hashpass = md5($hashpass);
    $arr = ['nickname' => $nickname, 'hashpass' => $hashpass];
    $res = Login::login($arr);
    $isvalid = $res['output']['valid'];

    if ($isvalid) {
        $_SESSION['session_user'] = $res['output']['response'][0];
      
        $_SESSION['mostrar_bienvenida'] = true;

        echo json_encode(['status' => 'success', 'redirect' => 'dashboard.php']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Credenciales incorrectas.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}
