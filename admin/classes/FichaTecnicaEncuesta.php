<?php
class FichaTecnicaEncuesta
{
    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $soloHabilitadas = isset($rqst['solo_habilitadas']) && $rqst['solo_habilitadas'] === true;

        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT * FROM " . $db->getTable('tbl_ficha_tecnica_encuestas');
        $params = [];
        $conditions = [];

        if ($id > 0) {
            $conditions[] = "id = :id";
            $params[':id'] = $id;
        }

        if ($soloHabilitadas) {
            $conditions[] = "habilitado = 'si'";
        }

        if (!empty($conditions)) {
            $q .= " WHERE " . implode(" AND ", $conditions);
        }

        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener los datos de FichaTecnicaEncuesta.');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $realizada_por_o_encomendada_por = isset($rqst['realizada_por_o_encomendada_por']) ? trim($rqst['realizada_por_o_encomendada_por']) : '';
        $fuente_financiacion = isset($rqst['fuente_financiacion']) ? trim($rqst['fuente_financiacion']) : '';
        $tipo_tamano_muestra_y_procedimiento_utilizado = isset($rqst['tipo_tamano_muestra_y_procedimiento_utilizado']) ? trim($rqst['tipo_tamano_muestra_y_procedimiento_utilizado']) : '';
        $temas_concretos = isset($rqst['temas_concretos']) ? trim($rqst['temas_concretos']) : '';
        $texto_literal_de_la_encuesta_o_preguntas = isset($rqst['texto_literal_de_la_encuesta_o_preguntas']) ? trim($rqst['texto_literal_de_la_encuesta_o_preguntas']) : '';
        $candidatos_personas_instituciones_indagados = isset($rqst['candidatos_personas_instituciones_indagados']) ? trim($rqst['candidatos_personas_instituciones_indagados']) : '';
        $espacio_geografico_fecha_o_periodo_que_se_realizo = isset($rqst['espacio_geografico_fecha_o_periodo_que_se_realizo']) ? trim($rqst['espacio_geografico_fecha_o_periodo_que_se_realizo']) : '';
        $margen_error_porcentaje = isset($rqst['margen_error_porcentaje']) ? floatval($rqst['margen_error_porcentaje']) : 0.0;
        $tipo_estudio = isset($rqst['tipo_estudio']) ? trim($rqst['tipo_estudio']) : 'na';
        $proposito_del_estudio = isset($rqst['proposito_del_estudio']) ? trim($rqst['proposito_del_estudio']) : '';
        $universo_representado = isset($rqst['universo_representado']) ? trim($rqst['universo_representado']) : '';
        $metodo_recoleccion = isset($rqst['metodo_recoleccion']) ? trim($rqst['metodo_recoleccion']) : '';
        $nivel_confiabilidad_porcentaje = isset($rqst['nivel_confiabilidad_porcentaje']) ? floatval($rqst['nivel_confiabilidad_porcentaje']) : 0.0;
        $estadisticos_responsables = isset($rqst['estadisticos_responsables']) ? trim($rqst['estadisticos_responsables']) : '';
        $declaracion = isset($rqst['declaracion']) ? trim($rqst['declaracion']) : '';
        $avisos = isset($rqst['avisos']) ? trim($rqst['avisos']) : '';
        $habilitado = isset($rqst['habilitado']) ? ($rqst['habilitado']) : '';

        // Campos adicionales
        $tipo_estudio_descripcion = isset($rqst['tipo_estudio_descripcion']) ? ($rqst['tipo_estudio_descripcion']) : '';
        $tipo_tamano_muestra_y_procedimiento_utilizado_descripcion = isset($rqst['tipo_tamano_muestra_y_procedimiento_utilizado_descripcion']) ? ($rqst['tipo_tamano_muestra_y_procedimiento_utilizado_descripcion']) : '';
        $tamano_muestra = isset($rqst['tamano_muestra']) ? intval($rqst['tamano_muestra']) : 0;
        $procedimiento_utilizado = isset($rqst['procedimiento_utilizado']) ? ($rqst['procedimiento_utilizado']) : '';
        $espacio_geografico_fecha = isset($rqst['espacio_geografico_fecha']) ? ($rqst['espacio_geografico_fecha']) : '';
        $espacio_geografico_fecha_estado = isset($rqst['espacio_geografico_fecha_estado']) ? ($rqst['espacio_geografico_fecha_estado']) : '';
        $tipo_encuesta = isset($rqst['tipo_encuesta']) ? ($rqst['tipo_encuesta']) : '';
        $tbl_espacio_geografico_id = isset($rqst['tbl_espacio_geografico_id']) ? intval($rqst['tbl_espacio_geografico_id']) : 0;
        $poblacion_objetivo = isset($rqst['poblacion_objetivo']) ? ($rqst['poblacion_objetivo']) : 'habitantes';

        $tbl_usuario_id =  intval($_SESSION['session_user']['id']);

        if (empty($realizada_por_o_encomendada_por)) {
            return Util::error_missing_data_description('El campo "Realizada por o encomendada por" es requerido.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                $table = $db->getTable('tbl_ficha_tecnica_encuestas');
                $arrfieldscomma = [
                    'realizada_por_o_encomendada_por' => $realizada_por_o_encomendada_por,
                    'fuente_financiacion' => $fuente_financiacion,
                    'tipo_tamano_muestra_y_procedimiento_utilizado' => $tipo_tamano_muestra_y_procedimiento_utilizado,
                    'temas_concretos' => $temas_concretos,
                    'texto_literal_de_la_encuesta_o_preguntas' => $texto_literal_de_la_encuesta_o_preguntas,
                    'candidatos_personas_instituciones_indagados' => $candidatos_personas_instituciones_indagados,
                    'espacio_geografico_fecha_o_periodo_que_se_realizo' => $espacio_geografico_fecha_o_periodo_que_se_realizo,
                    'margen_error_porcentaje' => $margen_error_porcentaje,
                    'tipo_estudio' => $tipo_estudio,
                    'proposito_del_estudio' => $proposito_del_estudio,
                    'universo_representado' => $universo_representado,
                    'metodo_recoleccion' => $metodo_recoleccion,
                    'nivel_confiabilidad_porcentaje' => $nivel_confiabilidad_porcentaje,
                    'estadisticos_responsables' => $estadisticos_responsables,
                    'declaracion' => $declaracion,
                    'avisos' => $avisos,
                    'tbl_usuario_id' => $tbl_usuario_id,
                    'habilitado' => $habilitado,
                    // Campos adicionales
                    'tipo_estudio_descripcion' => $tipo_estudio_descripcion,
                    'tipo_tamano_muestra_y_procedimiento_utilizado_descripcion' => $tipo_tamano_muestra_y_procedimiento_utilizado_descripcion,
                    'tamano_muestra' => $tamano_muestra,
                    'procedimiento_utilizado' => $procedimiento_utilizado,
                    'espacio_geografico_fecha' => $espacio_geografico_fecha,
                    'espacio_geografico_fecha_estado' => $espacio_geografico_fecha_estado,
                    'tipo_encuesta' => $tipo_encuesta,
                    'tbl_espacio_geografico_id' => $tbl_espacio_geografico_id,
                    'poblacion_objetivo' => $poblacion_objetivo
                ];
                $arrfieldsnocomma = array('dtupdate' => Util::date_now_server());
                $q_update = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $pdo->query($q_update);
                $arrjson = array('output' => array('valid' => true, 'id' => $id));
            } else {

               $q = "INSERT INTO " . $db->getTable('tbl_ficha_tecnica_encuestas') . " 
    (realizada_por_o_encomendada_por, fuente_financiacion, tipo_tamano_muestra_y_procedimiento_utilizado, temas_concretos, texto_literal_de_la_encuesta_o_preguntas, candidatos_personas_instituciones_indagados, espacio_geografico_fecha_o_periodo_que_se_realizo, margen_error_porcentaje, tipo_estudio, proposito_del_estudio, universo_representado, metodo_recoleccion, nivel_confiabilidad_porcentaje, estadisticos_responsables, declaracion, avisos, dtcreate, tbl_usuario_id, habilitado, tipo_estudio_descripcion, tipo_tamano_muestra_y_procedimiento_utilizado_descripcion, tamano_muestra, procedimiento_utilizado, espacio_geografico_fecha, espacio_geografico_fecha_estado, tipo_encuesta, tbl_espacio_geografico_id, poblacion_objetivo)
    VALUES 
    (:realizada_por_o_encomendada_por, :fuente_financiacion, :tipo_tamano_muestra_y_procedimiento_utilizado, :temas_concretos, :texto_literal_de_la_encuesta_o_preguntas, :candidatos_personas_instituciones_indagados, :espacio_geografico_fecha_o_periodo_que_se_realizo, :margen_error_porcentaje, :tipo_estudio, :proposito_del_estudio, :universo_representado, :metodo_recoleccion, :nivel_confiabilidad_porcentaje, :estadisticos_responsables, :declaracion, :avisos, :dtcreate, :tbl_usuario_id, :habilitado, :tipo_estudio_descripcion, :tipo_tamano_muestra_y_procedimiento_utilizado_descripcion, :tamano_muestra, :procedimiento_utilizado, :espacio_geografico_fecha, :espacio_geografico_fecha_estado, :tipo_encuesta, :tbl_espacio_geografico_id, :poblacion_objetivo)";
                $stmt = $pdo->prepare($q);
                $arrparam = [
                    ':realizada_por_o_encomendada_por' => $realizada_por_o_encomendada_por,
                    ':fuente_financiacion' => $fuente_financiacion,
                    ':tipo_tamano_muestra_y_procedimiento_utilizado' => $tipo_tamano_muestra_y_procedimiento_utilizado,
                    ':temas_concretos' => $temas_concretos,
                    ':texto_literal_de_la_encuesta_o_preguntas' => $texto_literal_de_la_encuesta_o_preguntas,
                    ':candidatos_personas_instituciones_indagados' => $candidatos_personas_instituciones_indagados,
                    ':espacio_geografico_fecha_o_periodo_que_se_realizo' => $espacio_geografico_fecha_o_periodo_que_se_realizo,
                    ':margen_error_porcentaje' => $margen_error_porcentaje,
                    ':tipo_estudio' => $tipo_estudio,
                    ':proposito_del_estudio' => $proposito_del_estudio,
                    ':universo_representado' => $universo_representado,
                    ':metodo_recoleccion' => $metodo_recoleccion,
                    ':nivel_confiabilidad_porcentaje' => $nivel_confiabilidad_porcentaje,
                    ':estadisticos_responsables' => $estadisticos_responsables,
                    ':declaracion' => $declaracion,
                    ':avisos' => $avisos,
                    ':dtcreate' => Util::date(),
                    ':tbl_usuario_id' => $tbl_usuario_id,
                    ':habilitado' => $habilitado,
                    // Campos adicionales
                    ':tipo_estudio_descripcion' => $tipo_estudio_descripcion,
                    ':tipo_tamano_muestra_y_procedimiento_utilizado_descripcion' => $tipo_tamano_muestra_y_procedimiento_utilizado_descripcion,
                    ':tamano_muestra' => $tamano_muestra,
                    ':procedimiento_utilizado' => $procedimiento_utilizado,
                    ':espacio_geografico_fecha' => $espacio_geografico_fecha,
                    ':espacio_geografico_fecha_estado' => $espacio_geografico_fecha_estado,
                    ':tipo_encuesta' => $tipo_encuesta,
                    ':tbl_espacio_geografico_id' => $tbl_espacio_geografico_id,
                    ':poblacion_objetivo' => $poblacion_objetivo
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
            $arrjson = Util::error_general('Guardando datos en FichaTecnicaEncuesta');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
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
            $q = "DELETE FROM " . $db->getTable('tbl_ficha_tecnica_encuestas') . " WHERE id = :id";
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
}
