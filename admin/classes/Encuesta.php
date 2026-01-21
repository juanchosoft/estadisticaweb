<?php
class Encuesta
{
    public function __construct() { }

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT * FROM " . $db->getTable('tbl_encuestas');
        $params = [];
        if ($id > 0) {
            $q .= " WHERE id = :id";
            $params[':id'] = $id;
        }
        $q .= " ORDER BY id DESC";

        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener los datos de Encuesta.');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }
public static function save($rqst)
{
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $fecha_realizacion      = isset($rqst['fecha_realizacion']) ? trim($rqst['fecha_realizacion']) : '';
    $fecha_publicacion      = isset($rqst['fecha_publicacion']) ? trim($rqst['fecha_publicacion']) : '';
    $fecha_de_recibo        = isset($rqst['fecha_de_recibo']) ? trim($rqst['fecha_de_recibo']) : '';
    $fuente_financiamiento  = isset($rqst['fuente_financiamiento']) ? trim($rqst['fuente_financiamiento']) : '';
    $tema                   = isset($rqst['tema']) ? trim($rqst['tema']) : '';
    $tamano_de_la_muestra   = isset($rqst['tamano_de_la_muestra']) ? trim($rqst['tamano_de_la_muestra']) : '';
    $observaciones          = isset($rqst['observaciones']) ? trim($rqst['observaciones']) : '';
    $cumple_con_reglamentacion = isset($rqst['cumple_con_reglamentacion']) ? trim($rqst['cumple_con_reglamentacion']) : '';
    $tipo_muestra           = isset($rqst['tipo_muestra']) ? trim($rqst['tipo_muestra']) : '';
    $tecnica_de_recoleccion = isset($rqst['tecnica_de_recoleccion']) ? trim($rqst['tecnica_de_recoleccion']) : '';
    $enlace_documento       = isset($rqst['enlace_documento']) ? trim($rqst['enlace_documento']) : '';
    $habilitado             = isset($rqst['habilitado']) ? trim($rqst['habilitado']) : '';
    $tbl_usuario_id         = intval($_SESSION['session_user']['id']);

    if (empty($fecha_realizacion))     { return Util::error_missing_data_description('El campo "Fecha de Realización" es requerido.'); }
    if (empty($fecha_publicacion))     { return Util::error_missing_data_description('El campo "Fecha de publicación" es requerido.'); }
    if (empty($fecha_de_recibo))       { return Util::error_missing_data_description('El campo "Fecha de recibo" es requerido.'); }
    if (empty($fuente_financiamiento)) { return Util::error_missing_data_description('El campo "Fuente de financiamiento" es requerido.'); }
    if (empty($tema))                  { return Util::error_missing_data_description('El campo "Tema" es requerido.'); }
    if (empty($tamano_de_la_muestra))  { return Util::error_missing_data_description('El campo "Tamaño de la muestra" es requerido.'); }
    if (empty($observaciones))         { return Util::error_missing_data_description('El campo "Observaciones" es requerido.'); }
    if (empty($cumple_con_reglamentacion)) { return Util::error_missing_data_description('El campo "Cumple con la reglamentación" es requerido.'); }
    if (empty($tipo_muestra))          { return Util::error_missing_data_description('El campo "Tipo de muestra" es requerido.'); }
    if (empty($tecnica_de_recoleccion)){ return Util::error_missing_data_description('El campo "Tecnica de recolección" es requerido.'); }
    if (empty($habilitado))            { return Util::error_missing_data_description('El campo "Habilitado" es requerido.'); }

    $db  = new DbConection();
    $pdo = $db->openConect();

    try {
        $pdo->beginTransaction();

        if ($id > 0) {
            $table = $db->getTable('tbl_encuestas');

            $arrfieldscomma = [
                'fecha_realizacion'         => $fecha_realizacion,
                'fecha_publicacion'         => $fecha_publicacion,
                'fecha_de_recibo'           => $fecha_de_recibo,
                'fuente_financiamiento'     => $fuente_financiamiento,
                'tema'                      => $tema,
                'tamano_de_la_muestra'      => $tamano_de_la_muestra,
                'observaciones'             => $observaciones,
                'cumple_con_reglamentacion' => $cumple_con_reglamentacion,
                'tipo_muestra'              => $tipo_muestra,
                'tecnica_de_recoleccion'    => $tecnica_de_recoleccion,
                'enlace_documento'          => $enlace_documento,
                'habilitado'                => $habilitado,
                'tbl_usuario_id'            => $tbl_usuario_id
            ];

            // No tocamos dtcreate en el update
            $arrfieldsnocomma = [];

            $q_update = Util::make_query_update(
                $table,
                "id = '$id'",
                $arrfieldscomma,
                $arrfieldsnocomma
            );

            $pdo->query($q_update);
            $arrjson = ['output' => ['valid' => true, 'id' => $id]];

        } else {

            $q = "INSERT INTO " . $db->getTable('tbl_encuestas') . " (
                    dtcreate,
                    fecha_realizacion,
                    fecha_publicacion,
                    fecha_de_recibo,
                    fuente_financiamiento,
                    tema,
                    tamano_de_la_muestra,
                    observaciones,
                    cumple_con_reglamentacion,
                    tipo_muestra,
                    tecnica_de_recoleccion,
                    enlace_documento,
                    habilitado,
                    tbl_usuario_id
                  ) VALUES (
                    " . Util::date_now_server() . ",
                    :fecha_realizacion,
                    :fecha_publicacion,
                    :fecha_de_recibo,
                    :fuente_financiamiento,
                    :tema,
                    :tamano_de_la_muestra,
                    :observaciones,
                    :cumple_con_reglamentacion,
                    :tipo_muestra,
                    :tecnica_de_recoleccion,
                    :enlace_documento,
                    :habilitado,
                    :tbl_usuario_id
                  )";

            $stmt = $pdo->prepare($q);
            $arrparam = [
                ':fecha_realizacion'         => $fecha_realizacion,
                ':fecha_publicacion'         => $fecha_publicacion,
                ':fecha_de_recibo'           => $fecha_de_recibo,
                ':fuente_financiamiento'     => $fuente_financiamiento,
                ':tema'                      => $tema,
                ':tamano_de_la_muestra'      => $tamano_de_la_muestra,
                ':observaciones'             => $observaciones,
                ':cumple_con_reglamentacion' => $cumple_con_reglamentacion,
                ':tipo_muestra'              => $tipo_muestra,
                ':tecnica_de_recoleccion'    => $tecnica_de_recoleccion,
                ':enlace_documento'          => $enlace_documento,
                ':habilitado'                => $habilitado,
                ':tbl_usuario_id'            => $tbl_usuario_id
            ];

            $stmt->execute($arrparam);
            $arrjson = ['output' => ['valid' => true, 'response' => $pdo->lastInsertId()]];
        }

        $pdo->commit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $arrjson = Util::error_general('Guardando datos en Encuesta');
    } finally {
        $db->closeConect();
    }

    return $arrjson;
}


    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        if ($id <= 0) { return Util::error_missing_data(); }

        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $q = "DELETE FROM " . $db->getTable('tbl_encuestas') . " WHERE id = :id";
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