<?php
session_start();
/**
 * en este archivo se atienden todas las peticiones AJAX
 */
$rqst = $_REQUEST;
$op = isset($rqst['op']) ? $rqst['op'] : '';
header("Content-type: application/javascript; charset=utf-8");
header("Cache-Control: max-age=15, must-revalidate");
header('Access-Control-Allow-Origin: *');

include '../classes/DbConection.php';
include '../classes/Util.php';
include '../classes/SessionData.php';

switch ($op) {

  // Rutas para el módulo: Votantes
  case 'votantesget':
    include '../classes/Votantes.php';
    echo json_encode(Votantes::getAll($rqst));
    break;

  case 'votantessave':
    include '../classes/Votantes.php';
    echo json_encode(Votantes::save($rqst));
    break;

  case 'votantesdelete':
    include '../classes/Votantes.php';
    echo json_encode(Votantes::delete($rqst));
    break;

  case 'votantesavailable':
    include '../classes/Votantes.php';
    echo json_encode(Votantes::available($rqst));
    break;
  
  case 'votantesactualizarperfil':
    include '../classes/Votantes.php';
    echo json_encode(Votantes::actualizarPerfil($rqst));
    break;

  // Rutas para el módulo: FichaTecnicaEncuesta
  case 'fichaTecnicaEncuestaget':
    include '../classes/FichaTecnicaEncuesta.php';
    echo json_encode(FichaTecnicaEncuesta::getAll($rqst));
    break;

  case 'fichaTecnicaEncuestasave':
    include '../classes/FichaTecnicaEncuesta.php';
    echo json_encode(FichaTecnicaEncuesta::save($rqst));
    break;

  case 'fichaTecnicaEncuestadelete':
    include '../classes/FichaTecnicaEncuesta.php';
    echo json_encode(FichaTecnicaEncuesta::delete($rqst));
    break;

  // Rutas para el módulo: Grilla
  case 'grillaget':
    include '../classes/Grilla.php';
    echo json_encode(Grilla::getAll($rqst));
    break;

  case 'grillasave':
    include '../classes/Grilla.php';
    echo json_encode(Grilla::save($rqst));
    break;


  case 'grillacandidatoguardarrespuestas':
    include '../classes/GrillaCandidatoRespuesta.php';
    echo json_encode(GrillaCandidatoRespuesta::guardarRespuestas($rqst));
    break;

  case 'grillacandidatoguardarpreguntasadicionales':
    include '../classes/GrillaCandidatoRespuesta.php';
    echo json_encode(GrillaCandidatoRespuesta::guardarPreguntasAdicionales($rqst));
    break;

  case 'grillacandidatoverificarvotoduplicado':
    include '../classes/GrillaCandidatoRespuesta.php';
    echo json_encode(GrillaCandidatoRespuesta::verificarVotoDuplicado($rqst));
    break;

  case 'grillacandidatoresultadosentiemporeal':
    include '../classes/GrillaCandidatoRespuesta.php';
    echo json_encode(GrillaCandidatoRespuesta::obtenerResultadosEnTiempoReal($rqst));
    break;

  case 'grillavalidarpreguntas':
    include '../classes/PreguntaGrilla.php';
    echo json_encode(PreguntaGrilla::validarPreguntasGrilla($rqst));
    break;

  // Rutas para el módulo: Preguntas de Grilla
  case 'preguntasgrillaget':
    include '../classes/PreguntaGrilla.php';
    echo json_encode(PreguntaGrilla::getAll($rqst));
    break;

  case 'preguntasgrillaobtenerconsubpreguntas':
    include '../classes/PreguntaGrilla.php';
    echo json_encode(PreguntaGrilla::obtenerPreguntasConSubpreguntas($rqst));
    break;

  case 'preguntasgrillaporid':
    include '../classes/PreguntaGrilla.php';
    echo json_encode(PreguntaGrilla::obtenerPreguntaPorId($rqst));
    break;

  case 'preguntasgrillasave':
    include '../classes/PreguntaGrilla.php';
    echo json_encode(PreguntaGrilla::save($rqst));
    break;

  case 'preguntasgrilladelete':
    include '../classes/PreguntaGrilla.php';
    echo json_encode(PreguntaGrilla::delete($rqst));
    break;

  // Rutas para el módulo: Sondeo
  case 'sondeoget':
    include '../classes/Sondeo.php';
    echo json_encode(Sondeo::getAll($rqst));
    break;

  case 'sondeovotar': 
    include '../classes/Sondeo.php';
    echo json_encode(Sondeo::registrarVoto($rqst)); 
    break;
    
  case 'pms_usrlogin':
    include '../classes/Usuario.php';
    echo json_encode(Usuario::login($rqst));
    break;
  //Llamados AJAX Usuario
  case 'pms_usrsave':
    // Util::verify_user_app_access();
    include '../classes/Usuario.php';
    echo json_encode(Usuario::save($rqst));
    break;

  case 'pms_usrget':
    include '../classes/Usuario.php';
    echo json_encode(Usuario::getAll($rqst));
    break;

  case 'pms_usravailable':
    include '../classes/Usuario.php';
    echo json_encode(Usuario::available($rqst));
    break;
    // Fin Llamados AJAX Usuario

    // Llamados municipios
  case 'ciudadget':
    include '../classes/Ciudad.php';
    echo json_encode(Ciudad::getAll($rqst));
    break;

  // Rutas para el módulo: RespuestaCuestionario
  case 'respuestasave':
    include '../classes/RespuestaCuestionario.php';
    echo json_encode(RespuestaCuestionario::save($rqst));
    break;

  case 'respuestavotante':
    include '../classes/RespuestaCuestionario.php';
    $rqst['votante_id'] = SessionData::getUserId();
    echo json_encode(RespuestaCuestionario::getRespuestasVotante($rqst));
    break;

  case 'grillarespuestavotante':
    include '../classes/GrillaCandidatoRespuesta.php';
    $rqst['votante_id'] = SessionData::getUserId();
    echo json_encode(GrillaCandidatoRespuesta::getRespuestasVotante($rqst));
    break;

    //sobre sondeos en vializacion
case 'consultasondeo':
    include '../classes/Sondeo.php';
    echo json_encode(Sondeo::filtrarPorLugar($rqst));
    break;
case 'consultar_respuestas_sondeo':
    include '../classes/Sondeo.php';
    echo json_encode(Sondeo::obtenerRespuestas($rqst));
    break;
    
//sobre encuestas en vializacion

// ===============================================
// LISTAR TODAS LAS ENCUESTAS (FICHA TÉCNICA)
// ===============================================
case 'listar_encuestas':
    include '../classes/EncuestaVisualizacion.php';
    echo json_encode(EncuestaVisualizacion::listarEncuestas($rqst));
    break;

// ===============================================
// LISTAR PREGUNTAS DE UNA ENCUESTA
// ===============================================
case 'listar_preguntas':
    include '../classes/EncuestaVisualizacion.php';
    echo json_encode(EncuestaVisualizacion::listarPreguntas($rqst));
    break;

// ===============================================
// LISTAR RESPUESTAS CRUD DE UNA PREGUNTA
// ===============================================
case 'listar_respuestas':
    include '../classes/EncuestaVisualizacion.php';
    echo json_encode(EncuestaVisualizacion::listarRespuestas($rqst));
    break;

// ===============================================
// CONTAR RESPUESTAS POR OPCIÓN
// ===============================================
case 'contar_respuestas':
    include '../classes/EncuestaVisualizacion.php';
    echo json_encode(EncuestaVisualizacion::contarRespuestas($rqst));
    break;

// ===============================================
// RESUMEN FINAL: ENCUESTA + PREGUNTA + CONTEO
// ===============================================
case 'resumen_pregunta':
    include '../classes/EncuestaVisualizacion.php';
    echo json_encode(EncuestaVisualizacion::resumenPregunta($rqst));
    break;

case 'mapa_ganador_nacional':
    include '../classes/Sondeo.php';
    echo json_encode(Sondeo::obtenerGanadorNacional());
break;


    
case 'departamentos_lista':
    $db = Util::getNombreMunicipioPorCodigoUnificado(null);   
    echo json_encode([
        "success" => true,
        "data" => $db
    ]);
    break;
//el index.php principal con el sondeo presidencial al incio
case 'sondeo_presidencial_mapa':
    include '../classes/Sondeo.php';
    echo json_encode(Sondeo::obtenerSondeoMapa($rqst));
break;
case 'sondeo_presidencial_general':
    include '../classes/Sondeo.php';
    echo json_encode(Sondeo::obtenerSondeoGeneral($rqst));
break;

case 'mapa_colores_departamentos':
    include '../classes/Sondeo.php';

    $departamentos = $_POST['departamentos'] ?? [];
    $respuesta = [];

    foreach ($departamentos as $dep) {
        $data = Sondeo::ganadorPorDepartamento($dep);
        $respuesta[$dep] = $data;
    }

    echo json_encode([
        "success" => true,
        "data" => $respuesta
    ]);
break;

}
