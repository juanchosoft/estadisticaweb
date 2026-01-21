<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define("DS", DIRECTORY_SEPARATOR);

// Inclusión de las clases necesarias
require_once './admin/classes/Util.php';
require_once './admin/classes/DbConection.php';
require_once './admin/classes/Colombia.php';

// Información del departamento con sus municipios e informacion asociada
$codigoDepartamento = isset($_REQUEST['dep']) ? $_REQUEST['dep'] : Util::getDepartamentoPrincipal();
$arr = array('codigo' => $codigoDepartamento);
$data = Colombia::getDepartamentoByCodigoCiudadesAccionUnificada($arr);
$isvalid = isset($arr['output']['valid']) ? $arr['output']['valid'] : false;
$responseMapa = isset($data['output']['response']) ? $data['output']['response'] : [];
?>