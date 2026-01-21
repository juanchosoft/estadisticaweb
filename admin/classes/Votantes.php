<?php
class Votantes
{
    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT * FROM " . $db->getTable('tbl_votantes');
        $params = [];
        if ($id > 0) {
            $q .= " WHERE id = :id";
            $params[':id'] = $id;
        }
        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener los datos de Votantes.');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $dtcreate = isset($rqst['dtcreate']) ? trim($rqst['dtcreate']) : '';
        $tbl_usuario_id = isset($_SESSION['session_user']['id']) ? intval($_SESSION['session_user']['id']) : 0;
        $nombre_completo = isset($rqst['nombre_completo']) ? trim($rqst['nombre_completo']) : '';
        $ideologia = isset($rqst['ideologia']) ? trim($rqst['ideologia']) : '';
        $rango_edad = isset($rqst['rango_edad']) ? trim($rqst['rango_edad']) : '';
        $nivel_ingresos = isset($rqst['nivel_ingresos']) ? trim($rqst['nivel_ingresos']) : '';
        $email = isset($rqst['email']) ? trim($rqst['email']) : '';
        $username = isset($rqst['username']) ? trim($rqst['username']) : '';
        $password = isset($rqst['password']) ? trim($rqst['password']) : '';
        $genero = isset($rqst['genero']) ? trim($rqst['genero']) : '';
        $codigo_departamento = isset($rqst['codigo_departamento']) ? trim($rqst['codigo_departamento']) : '';
        $codigo_municipio = isset($rqst['codigo_municipio']) ? trim($rqst['codigo_municipio']) : '';
        $nivel_educacion = isset($rqst['nivel_educacion']) ? trim($rqst['nivel_educacion']) : '';
        $ocupacion = isset($rqst['ocupacion']) ? trim($rqst['ocupacion']) : '';
        $estado = isset($rqst['estado']) ? trim($rqst['estado']) : '';
        // $email_verificado = isset($rqst['email_verificado']) ? trim($rqst['email_verificado']) : '';
        // $ultimo_acceso = isset($rqst['ultimo_acceso']) ? trim($rqst['ultimo_acceso']) : '';
        // $intentos_login = isset($rqst['intentos_login']) ? trim($rqst['intentos_login']) : '';
        // $cuenta_bloqueada_hasta = isset($rqst['cuenta_bloqueada_hasta']) ? trim($rqst['cuenta_bloqueada_hasta']) : '';
        // $dtupdate = isset($rqst['dtupdate']) ? trim($rqst['dtupdate']) : '';
        $ip_registro = Util::get_real_ipaddress();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        if (empty($nombre_completo)) {
            return Util::error_missing_data_description('El campo "Nombre completo" es requerido.');
        }
        if (empty($ideologia)) {
            return Util::error_missing_data_description('El campo "Ideología política" es requerido.');
        }
        if (empty($rango_edad)) {
            return Util::error_missing_data_description('El campo "Rango de edad" es requerido.');
        }
        if (empty($nivel_ingresos)) {
            return Util::error_missing_data_description('El campo "Nivel socioeconómico" es requerido.');
        }
/*         if (!empty($username)) {
            if (empty($password)) {
                return Util::error_missing_data_description('El campo "Contraseña" es requerido.');
            }
        } */
        if (!empty($password)) {
            if (empty($username)) {
                return Util::error_missing_data_description('El campo "Username" es requerido.');
            }
        }

        if (!empty($username)) {
            if (strlen($username) < 4) {
                return Util::error_missing_data_description('El campo "Username" debe tener al menos 4 caracteres.');
            }
        }

        if (!Votantes::available(array('username' => $username))) {
            return Util::error_missing_data_description('El campo "Username" ya existe.');
        }

        if (strlen($password) > 2) {
            $password = Util::make_hash_pass($password);
        }

        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return Util::error_missing_data_description('El campo "Correo electrónico" es inválido.');
            }
        }
        if (empty($genero)) {
            return Util::error_missing_data_description('El campo "Género" es requerido.');
        }
        if (empty($codigo_departamento)) {
            return Util::error_missing_data_description('El campo "Código del departamento" es requerido.');
        }
        if (empty($codigo_municipio)) {
            return Util::error_missing_data_description('El campo "Código del municipio" es requerido.');
        }
        if (empty($estado)) {
            return Util::error_missing_data_description('El campo "Estado de la cuenta" es requerido.');
        }


        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                $table = $db->getTable('tbl_votantes');

                $arrfieldscomma = [
                    'tbl_usuario_id' => $tbl_usuario_id,
                    'nombre_completo' => $nombre_completo,
                    'ideologia' => $ideologia,
                    'rango_edad' => $rango_edad,
                    'nivel_ingresos' => $nivel_ingresos,
                    'email' => $email,
                    'username' => $username,
                    'password' => $password,
                    'genero' => $genero,
                    'codigo_departamento' => $codigo_departamento,
                    'codigo_municipio' => $codigo_municipio,
                    'nivel_educacion' => $nivel_educacion,
                    'ocupacion' => $ocupacion,
                    'estado' => $estado,
                    'dtupdate' => Util::date()
                ];
                if($password == ""){
                    unset($arrfieldscomma['password']);
                }
                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q_update = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);

                // La ejecución del query ahora puede lanzar una excepción si falla
                $pdo->query($q_update);
                $arrjson = array('output' => array('valid' => true, 'id' => $id));
            } else {

                $q = "INSERT INTO " . $db->getTable('tbl_votantes') . " 
                (dtcreate, tbl_usuario_id, nombre_completo, ideologia, rango_edad, nivel_ingresos, email, username, password, genero, codigo_departamento, codigo_municipio, nivel_educacion, ocupacion, estado, ip_registro, user_agent) VALUES 
                 (:dtcreate, :tbl_usuario_id, :nombre_completo, :ideologia, :rango_edad, :nivel_ingresos, :email, :username, :password, :genero, :codigo_departamento, :codigo_municipio, :nivel_educacion, :ocupacion, :estado, :ip_registro, :user_agent)";
                $stmt = $pdo->prepare($q);
                $arrparam = [
                    ':dtcreate' => Util::date(),
                    ':tbl_usuario_id' => $tbl_usuario_id,
                    ':nombre_completo' => $nombre_completo,
                    ':ideologia' => $ideologia,
                    ':rango_edad' => $rango_edad,
                    ':nivel_ingresos' => $nivel_ingresos,
                    ':email' => $email,
                    ':username' => $username,
                    ':password' => $password,
                    ':genero' => $genero,
                    ':codigo_departamento' => $codigo_departamento,
                    ':codigo_municipio' => $codigo_municipio,
                    ':nivel_educacion' => $nivel_educacion,
                    ':ocupacion' => $ocupacion,
                    ':estado' => $estado,
                    ':ip_registro' => $ip_registro,
                    ':user_agent' => $user_agent,
                ];

                $stmt->execute($arrparam);
                $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
            }
            $pdo->commit();
        } catch (PDOException $e) {
            print_r($e);
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $arrjson = Util::error_general('Guardando datos en Votantes');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function available($rqst)
    {
        $fieldValue = isset($rqst['fieldValue']) ? trim($rqst['fieldValue']) : '';
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        if (empty($fieldValue)) {
            return Util::error_missing_data_description('El campo "Nombre de usuario" es requerido.');
        }

        $validation = self::validateUsername($fieldValue);
        if (!$validation['valid']) {
            return Util::error_general($validation['message']);
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        $params = [':fieldValue' => $fieldValue];
        $q = "SELECT id FROM " . $db->getTable('tbl_votantes') . " WHERE username = :fieldValue";

        if ($id > 0) {
            $q .= " AND id != :id";
            $params[':id'] = $id;
        }

        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            if ($stmt->fetch()) {
                $arrjson = Util::error_general('El valor \"' . $fieldValue . '\" ya existe.');
            } else {
                $arrjson = array('output' => array('valid' => true, 'response' => 'available'));
            }
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al verificar la disponibilidad.');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function validateUsername($username) {
        // Remover espacios en blanco
        $username = trim($username);
        
        // Validaciones básicas
        if (empty($username)) {
            return Util::error_missing_data_description('El nombre de usuario es requerido.');
        }
        
        if (strlen($username) < 3 || strlen($username) > 20) {
            return Util::error_missing_data_description('El nombre de usuario debe tener entre 3 y 20 caracteres.');
        }
        
        // Expresión regular: solo letras, números, guión bajo y punto
        // No puede empezar ni terminar con punto o guión bajo
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._]*[a-zA-Z0-9]$|^[a-zA-Z0-9]$/', $username)) {
            return Util::error_missing_data_description('El nombre de usuario solo puede contener letras, números, puntos y guiones bajos. No puede empezar ni terminar con punto o guión bajo.');
        }
        
        // Validar que no tenga puntos o guiones bajos consecutivos
        if (preg_match('/[._]{2,}/', $username)) {
            return Util::error_missing_data_description('No se permiten puntos o guiones bajos consecutivos.');
        }
        
        return ['valid' => true, 'username' => $username];
    }

    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        if ($id <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $q = "DELETE FROM " . $db->getTable('tbl_votantes') . " WHERE id = :id";
            $stmt = $pdo->prepare($q);
            if ($stmt->execute([':id' => $id])) {
                $arrjson = array('output' => array('valid' => true));
            } else {
                $arrjson = Util::error_generaldelete();
            }
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al eliminar el registro.');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

public static function actualizarPerfil($rqst)
{
    $output = array(
        "valid" => false,
        "response" => "No se pudo actualizar el perfil"
    );

    try {
        // DEBUG
        error_log("=== INICIO actualizarPerfil ===");
        error_log("Datos recibidos: " . print_r($rqst, true));

        $id = isset($rqst['idVotantes']) ? intval($rqst['idVotantes']) : 0;
        $nombre_completo = isset($rqst['nombre_completo']) ? trim($rqst['nombre_completo']) : '';
        $email = isset($rqst['email']) ? trim($rqst['email']) : '';
        $username = isset($rqst['username']) ? trim($rqst['username']) : '';
        $current_password = isset($rqst['current_password']) ? trim($rqst['current_password']) : '';
        $new_password = isset($rqst['password']) ? trim($rqst['password']) : '';

        error_log("Current Password (recibido): $current_password");

        // Validaciones básicas
        if ($id <= 0) {
            $output['response'] = "ID de votante no válido";
            return $output;
        }

        if (empty($nombre_completo) || empty($email) || empty($username) || empty($current_password)) {
            $output['response'] = "Todos los campos marcados con * son requeridos";
            return $output;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $output['response'] = "El correo electrónico no es válido";
            return $output;
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        // Verificar usuario existe
        $stmt = $pdo->prepare("SELECT id, password, username, email FROM " . $db->getTable('tbl_votantes') . " WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $output['response'] = "Usuario no encontrado";
            $db->closeConect();
            return $output;
        }

        error_log("Password en BD: " . $user['password']);
        error_log("Password recibido: " . $current_password);

        // **IMPORTANTE: Usar el mismo método que en el registro**
        // Si el registro usa Util::make_hash_pass(), usamos lo mismo aquí
        $hashed_current_password = Util::make_hash_pass($current_password);
        
        // Verificar contraseña actual
        if ($user['password'] !== $hashed_current_password) {
            $output['response'] = "La contraseña actual es incorrecta";
            error_log("ERROR: Contraseña no coincide");
            error_log("BD: " . $user['password'] . " vs Input: " . $hashed_current_password);
            $db->closeConect();
            return $output;
        }

        // Verificar username único
        $stmt = $pdo->prepare("SELECT id FROM " . $db->getTable('tbl_votantes') . " WHERE username = :username AND id != :id");
        $stmt->execute([':username' => $username, ':id' => $id]);
        if ($stmt->fetch()) {
            $output['response'] = "El nombre de usuario '$username' ya está siendo utilizado";
            $db->closeConect();
            return $output;
        }

        // Verificar email único
        $stmt = $pdo->prepare("SELECT id FROM " . $db->getTable('tbl_votantes') . " WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $email, ':id' => $id]);
        if ($stmt->fetch()) {
            $output['response'] = "El correo electrónico '$email' ya está siendo utilizado";
            $db->closeConect();
            return $output;
        }

        // Preparar actualización
        $table = $db->getTable('tbl_votantes');
        $updates = [];
        $params = [':id' => $id];

        $updates[] = "nombre_completo = :nombre_completo";
        $params[':nombre_completo'] = $nombre_completo;

        $updates[] = "email = :email";
        $params[':email'] = $email;

        $updates[] = "username = :username";
        $params[':username'] = $username;

        $updates[] = "dtupdate = :dtupdate";
        $params[':dtupdate'] = Util::date();

        // Si hay nueva contraseña, aplicarle el mismo hash que en el registro
        if (!empty($new_password)) {
            $hashed_new_password = Util::make_hash_pass($new_password);
            $updates[] = "password = :password";
            $params[':password'] = $hashed_new_password;
            error_log("Nueva contraseña hasheada: " . $hashed_new_password);
        }

        $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE id = :id";
        
        error_log("SQL: " . $sql);

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        error_log("Resultado: " . ($result ? "TRUE" : "FALSE"));
        error_log("Filas afectadas: " . $stmt->rowCount());

        if ($result && $stmt->rowCount() > 0) {
            $output['valid'] = true;
            $output['response'] = "Perfil actualizado correctamente";
        } else {
            $output['response'] = "No se realizaron cambios en la base de datos";
        }

        $db->closeConect();
        error_log("=== FIN actualizarPerfil ===");

    } catch (Exception $e) {
        error_log("EXCEPCIÓN en actualizarPerfil: " . $e->getMessage());
        $output['response'] = "Error del sistema: " . $e->getMessage();
    }

    return $output;
}

}
