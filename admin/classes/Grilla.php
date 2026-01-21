<?php
class Grilla
{
    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_grilla');
        $params = [];
        if ($id > 0) {
            $q .= " WHERE id = :id";
            $params[':id'] = $id;
        }
        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Informacion de los candidatos a postular
            foreach ($arr as $key => $value) {
                $qGrillaCandidato = "SELECT * FROM " . $db->getTable('tbl_grilla_x_tbl_participantes') . " 
                WHERE tbl_grilla_id = :id";

                $qGrillaCandidato = "SELECT 
                    p.id, p.tbl_cargo_publico_id, p.nombre_completo, p.codigo_departamento, p.codigo_municipio, p.dtcreate, p.foto, p.habilitado,
                    cp.nombre AS cargo_publico, cp.sigla AS sigla_cargo,
                    d.departamento AS nombre_departamento,
                    c.municipio AS nombre_municipio,
                    p.habilitado,
                    GROUP_CONCAT(pxp.tbl_partido_politico_id) AS partidoPoliticoIds,
                    GROUP_CONCAT(pp.nombre_partido SEPARATOR ', ') AS nombres_partidos
                FROM " . $db->getTable('tbl_participantes') . " p
                INNER JOIN " . $db->getTable('tbl_grilla_x_tbl_participantes') . " sxp
                    ON p.id = sxp.tbl_participante_id
                LEFT JOIN " . $db->getTable('tbl_participantes_x_partidos_politicos') . " pxp
                    ON p.id = pxp.tbl_participante_id
                LEFT JOIN " . $db->getTable('tbl_partidos_politicos') . " pp
                    ON pxp.tbl_partido_politico_id = pp.id
                LEFT JOIN " . $db->getTable('tbl_cargos_publicos') . " cp
                    ON p.tbl_cargo_publico_id = cp.id
                LEFT JOIN " . $db->getTable('tbl_departamentos') . " d
                    ON p.codigo_departamento = d.codigo_departamento
                LEFT JOIN " . $db->getTable('tbl_ciudades') . " c
                    ON p.codigo_municipio = c.codigo_muncipio
                WHERE sxp.tbl_grilla_id = :id
                GROUP BY p.id";
                $params[':id'] = $value['id'];
                $stmtGrillaCandidato = $pdo->prepare($qGrillaCandidato);
                $stmtGrillaCandidato->execute($params);
                $arrGrillaCandidato = $stmtGrillaCandidato->fetchAll(PDO::FETCH_ASSOC);
                $arr[$key]['candidatos'] = $arrGrillaCandidato;
            }

            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener los datos de Sondeo.');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $grilla = isset($rqst['grilla']) ? trim($rqst['grilla']) : '';
        $descripcion_grilla = isset($rqst['descripcion_grilla']) ? trim($rqst['descripcion_grilla']) : '';
        $tipo_inferenciales = isset($rqst['tipo_inferenciales']) ? trim($rqst['tipo_inferenciales']) : '';
        $aplica_cargos_publicos = isset($rqst['aplica_cargos_publicos']) ? trim($rqst['aplica_cargos_publicos']) : '';
        $codigo_departamento = isset($rqst['codigo_departamento']) ? trim($rqst['codigo_departamento']) : '';
        $codigo_municipio = isset($rqst['codigo_municipio']) ? trim($rqst['codigo_municipio']) : '';
        $tbl_cargo_publico_id = isset($rqst['tbl_cargo_publico_id']) ? trim($rqst['tbl_cargo_publico_id']) : '';
        $tbl_ficha_tecnica_encuesta_id = isset($rqst['tbl_ficha_tecnica_encuesta_id']) ? intval($rqst['tbl_ficha_tecnica_encuesta_id']) : null;
        $habilitado = isset($rqst['habilitado']) ? trim($rqst['habilitado']) : '';
        $candidatos = isset($rqst['candidatos']) ? $rqst['candidatos'] : [];
        $opciones = isset($rqst['opciones']) ? $rqst['opciones'] : [];

        if (empty($grilla)) {
            return Util::error_missing_data_description('El campo "Grilla" es requerido.');
        }
        if (empty($tipo_inferenciales)) {
            return Util::error_missing_data_description('El campo "Tipo de Inferenciales" es requerido.');
        }
        if (empty($aplica_cargos_publicos)) {
            return Util::error_missing_data_description('El campo "Aplica a Cargos Públicos" es requerido.');
        }
        if (empty($habilitado)) {
            return Util::error_missing_data_description('El campo "Habilitado" es requerido.');
        }
        if ($aplica_cargos_publicos == 'si' && empty($tbl_cargo_publico_id)) {
            return Util::error_missing_data_description('El campo "Cargo Público" es requerido.');
        }
        if ($aplica_cargos_publicos == 'si' && empty($candidatos)) {
            return Util::error_missing_data_description('Debes seleccionar candidatos a postular para este grilla: ' . $grilla);
        }


        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                $table = $db->getTable('tbl_grilla');
                $arrfieldscomma = [
                    'grilla' => $grilla,
                    'descripcion_grilla' => $descripcion_grilla,
                    'tbl_ficha_tecnica_encuesta_id' => $tbl_ficha_tecnica_encuesta_id,
                    'tipo_inferenciales' => $tipo_inferenciales,
                    'aplica_cargos_publicos' => $aplica_cargos_publicos,
                    'codigo_departamento' => $codigo_departamento,
                    'codigo_municipio' => $codigo_municipio,
                    'tbl_cargo_publico_id' => $tbl_cargo_publico_id,
                    'habilitado' => $habilitado,
                ];
                $arrfieldsnocomma = array();
                $q_update = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);

                $pdo->query($q_update);
                $arrjson = array('output' => array('valid' => true, 'id' => $id));

                Grilla::saveGrillaParticipantes($rqst);

            } else {

                $q = "INSERT INTO " . $db->getTable('tbl_grilla') . " (grilla, descripcion_grilla, tbl_ficha_tecnica_encuesta_id, tipo_inferenciales, aplica_cargos_publicos, codigo_departamento, codigo_municipio, tbl_cargo_publico_id, dtcreate, habilitado) VALUES
                (:grilla, :descripcion_grilla, :tbl_ficha_tecnica_encuesta_id, :tipo_inferenciales, :aplica_cargos_publicos, :codigo_departamento, :codigo_municipio, :tbl_cargo_publico_id, :dtcreate, :habilitado)";
                $stmt = $pdo->prepare($q);
                $arrparam = [
                    ':grilla' => $grilla,
                    ':descripcion_grilla' => $descripcion_grilla,
                    ':tbl_ficha_tecnica_encuesta_id' => $tbl_ficha_tecnica_encuesta_id,
                    ':tipo_inferenciales' => $tipo_inferenciales,
                    ':aplica_cargos_publicos' => $aplica_cargos_publicos,
                    ':codigo_departamento' => $codigo_departamento,
                    ':codigo_municipio' => $codigo_municipio,
                    ':tbl_cargo_publico_id' => $tbl_cargo_publico_id,
                    ':dtcreate' => Util::date(),
                    ':habilitado' => $habilitado,
                ];

                $stmt->execute($arrparam);
                $id = $pdo->lastInsertId();

                $rqst['id'] = $id;

                Grilla::saveGrillaParticipantes($rqst);

                $arrjson = array('output' => array('valid' => true, 'response' => $id));
            }
            $pdo->commit();
        } catch (PDOException $e) {
             print_r($e);
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $arrjson = Util::error_general('Guardando datos en Grilla');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    public static function saveGrillaParticipantes($rqst)
    {

        $db = new DbConection();
        $pdo = $db->openConect();

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $aplica_cargos_publicos = isset($rqst['aplica_cargos_publicos']) ? trim($rqst['aplica_cargos_publicos']) : '';
        $candidatos = isset($rqst['candidatos']) ? $rqst['candidatos'] : [];

        // Información de participantes del grilla
        if ($aplica_cargos_publicos == 'si' && !empty($candidatos)) {

            $qDelete = "DELETE FROM " . $db->getTable('tbl_grilla_x_tbl_participantes') . " WHERE tbl_grilla_id = :tbl_grilla_id";
            $stmtDelete = $pdo->prepare($qDelete);
            $stmtDelete->execute([':tbl_grilla_id' => $id]);
            if (!$stmtDelete) {
                throw new Exception('Error al eliminar los candidatos del grilla.');
            }

            $qSondeParticipantes = "INSERT INTO " . $db->getTable('tbl_grilla_x_tbl_participantes') . " (tbl_grilla_id, tbl_participante_id, dtcreate) VALUES (:tbl_grilla_id, :tbl_participante_id, :dtcreate)";
            $stmtSondeParticipantes = $pdo->prepare($qSondeParticipantes);
            foreach ($candidatos as $candidato) {
                $arrparam = [
                    ':tbl_grilla_id' => $id,
                    ':tbl_participante_id' => $candidato,
                    ':dtcreate' => Util::date(),
                ];
                $stmtSondeParticipantes->execute($arrparam);
                if (!$stmtSondeParticipantes) {
                    throw new Exception('Error al guardar los candidatos del grilla.');
                }
            }
        }
        $db->closeConect();
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
            $q = "DELETE FROM " . $db->getTable('tbl_grilla') . " WHERE id = :id";
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
