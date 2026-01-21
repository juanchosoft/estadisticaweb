<?php
class EncuestaVisualizacion
{
    /* ================================
       1) LISTAR ENCUESTAS (FICHAS)
       ================================ */
  public static function listarEncuestas($rqst = [])
    {
        $codigo_departamento = isset($rqst['codigo_departamento']) 
            ? trim($rqst['codigo_departamento']) 
            : '';

        $db  = new DbConection();
        $pdo = $db->openConect();

        $tablaFicha = $db->getTable('tbl_ficha_tecnica_encuestas');
        $tablaEspGeo = $db->getTable('tbl_espacio_geografico_x_departamentos_x_ciudades');

        // FILTRO dinámico
        $filtroDep = "";
        $params = [];

        if ($codigo_departamento !== "") {
            $filtroDep = " AND egdc.codigo_departamento = :codigo_departamento ";
            $params[':codigo_departamento'] = $codigo_departamento;
        }

        $sql = "
            SELECT 
                f.id,
                f.realizada_por_o_encomendada_por AS realizada_por,
                f.tipo_estudio,
                f.dtcreate
            FROM $tablaFicha f
            INNER JOIN $tablaEspGeo egdc 
                    ON egdc.tbl_espacio_geografico_id = f.tbl_espacio_geografico_id
            WHERE f.habilitado = 'si'
            $filtroDep
            GROUP BY f.id
            ORDER BY f.id DESC
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid'    => true,
                    'response' => $arr
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general('Error al listar encuestas.');
        } finally {
            $db->closeConect();
        }
    }


    /* ================================
       2) LISTAR PREGUNTAS POR ENCUESTA
       ================================ */
    public static function listarPreguntas($rqst = [])
    {
        $encuesta_id = isset($rqst['encuesta_id']) ? intval($rqst['encuesta_id']) : 0;

        if ($encuesta_id <= 0) {
            return Util::error_general('ID inválido.');
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $tabla = $db->getTable('tbl_preguntas');

            $sql = "
                SELECT 
                    id AS pregunta_id,
                    texto_pregunta,
                    tipo_pregunta,
                    orden
                FROM $tabla
                WHERE tbl_ficha_tecnica_encuesta_id = :id
                  AND habilitado = 'si'
                ORDER BY orden ASC, id ASC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $encuesta_id]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid'    => true,
                    'response' => $rows
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general('Error al listar preguntas.');
        } finally {
            $db->closeConect();
        }
    }

    /* ================================
       3) LISTAR RESPUESTAS (DETALLE)
       ================================ */
    public static function listarRespuestas($rqst = [])
    {
        $encuesta_id         = isset($rqst['encuesta_id']) ? intval($rqst['encuesta_id']) : 0;
        $pregunta_id         = isset($rqst['pregunta_id']) ? intval($rqst['pregunta_id']) : 0;
        $codigo_departamento = isset($rqst['codigo_departamento']) ? trim($rqst['codigo_departamento']) : '';
        $codigo_municipio    = isset($rqst['codigo_municipio']) ? trim($rqst['codigo_municipio']) : '';

        if ($encuesta_id <= 0 || $pregunta_id <= 0) {
            return Util::error_general('Datos incompletos para listar respuestas.');
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $tablaFicha      = $db->getTable('tbl_ficha_tecnica_encuestas');
            $tablaIntentos   = $db->getTable('tbl_cuestionario_intentos');
            $tablaRespuestas = $db->getTable('tbl_cuestionario_respuestas');
            $tablaPreguntas  = $db->getTable('tbl_preguntas');
            $tablaOpciones   = $db->getTable('tbl_opciones_respuesta');
            $tablaVotantes   = $db->getTable('tbl_votantes');

            $filtroGeo = '';
            $params = [
                ':encuesta_id' => $encuesta_id,
                ':pregunta_id' => $pregunta_id
            ];

            if ($codigo_departamento !== '') {
                $filtroGeo .= ' AND v.codigo_departamento = :codigo_departamento';
                $params[':codigo_departamento'] = $codigo_departamento;
            }
            if ($codigo_municipio !== '') {
                $filtroGeo .= ' AND v.codigo_municipio = :codigo_municipio';
                $params[':codigo_municipio'] = $codigo_municipio;
            }

            $sql = "
                SELECT 
                    r.id              AS respuesta_id,
                    i.id              AS intento_id,
                    f.id              AS ficha_id,
                    p.id              AS pregunta_id,
                    p.texto_pregunta,
                    o.id              AS opcion_id,
                    o.texto_opcion,
                    r.respuesta_texto,
                    i.fecha_respuesta,
                    v.codigo_departamento,
                    v.codigo_municipio,
                    r.dtcreate
                FROM $tablaFicha f
                INNER JOIN $tablaIntentos i
                        ON i.tbl_ficha_tecnica_encuesta_id = f.id
                INNER JOIN $tablaRespuestas r
                        ON r.tbl_intento_id = i.id
                INNER JOIN $tablaPreguntas p
                        ON p.id = r.tbl_pregunta_id
                LEFT  JOIN $tablaOpciones o
                        ON o.id = r.tbl_opcion_respuesta_id
                INNER JOIN $tablaVotantes v
                        ON v.id = i.tbl_votante_id
                WHERE f.id = :encuesta_id
                  AND p.id = :pregunta_id
                  $filtroGeo
                ORDER BY r.id
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid'    => true,
                    'response' => $rows
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general('Error al listar respuestas.');
        } finally {
            $db->closeConect();
        }
    }

    /* ================================
    4) CONTEO DE RESPUESTAS POR OPCIÓN
    ================================ */
    public static function contarRespuestas($rqst = [])
    {
        $encuesta_id         = isset($rqst['encuesta_id']) ? intval($rqst['encuesta_id']) : 0;
        $pregunta_id         = isset($rqst['pregunta_id']) ? intval($rqst['pregunta_id']) : 0;
        $codigo_departamento = isset($rqst['codigo_departamento']) ? trim($rqst['codigo_departamento']) : '';
        $codigo_municipio    = isset($rqst['codigo_municipio']) ? trim($rqst['codigo_municipio']) : '';

        if ($encuesta_id <= 0 || $pregunta_id <= 0) {
            return Util::error_general('Datos incompletos para contar respuestas.');
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $tablaFicha      = $db->getTable('tbl_ficha_tecnica_encuestas');
            $tablaIntentos   = $db->getTable('tbl_cuestionario_intentos');
            $tablaRespuestas = $db->getTable('tbl_cuestionario_respuestas');
            $tablaPreguntas  = $db->getTable('tbl_preguntas');
            $tablaOpciones   = $db->getTable('tbl_opciones_respuesta');
            $tablaVotantes   = $db->getTable('tbl_votantes');

            $filtroGeo = '';
            $params = [
                ':encuesta_id' => $encuesta_id,
                ':pregunta_id' => $pregunta_id
            ];

            if ($codigo_departamento !== '') {
                $filtroGeo .= ' AND v.codigo_departamento = :codigo_departamento';
                $params[':codigo_departamento'] = $codigo_departamento;
            }
            if ($codigo_municipio !== '') {
                $filtroGeo .= ' AND v.codigo_municipio = :codigo_municipio';
                $params[':codigo_municipio'] = $codigo_municipio;
            }

            $sql = "
                SELECT 
                    o.id         AS opcion_id,
                    o.texto_opcion,
                    COUNT(*)     AS cantidad
                FROM $tablaFicha f
                INNER JOIN $tablaIntentos i
                        ON i.tbl_ficha_tecnica_encuesta_id = f.id
                INNER JOIN $tablaRespuestas r
                        ON r.tbl_intento_id = i.id
                INNER JOIN $tablaPreguntas p
                        ON p.id = r.tbl_pregunta_id
                LEFT  JOIN $tablaOpciones o
                        ON o.id = r.tbl_opcion_respuesta_id
                INNER JOIN $tablaVotantes v
                        ON v.id = i.tbl_votante_id
                WHERE f.id = :encuesta_id
                  AND p.id = :pregunta_id
                  $filtroGeo
                GROUP BY o.id, o.texto_opcion
                ORDER BY o.id ASC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid'    => true,
                    'response' => $rows
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general('Error al contar respuestas.');
        } finally {
            $db->closeConect();
        }
    }

    public static function resumenPregunta($rqst = [])
    {
        return ['output' => ['valid' => true, 'response' => []]];
    }
}
