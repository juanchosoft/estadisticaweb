<?php

/**
 * Clase RespuestaCuestionario
 * Gestiona las respuestas de los cuestionarios (Ficha Técnica de Encuesta)
 */
class RespuestaCuestionario
{
    /**
     * Obtiene votantes disponibles (que no han contestado) para una ficha técnica
     * @param array $rqst Parámetros de búsqueda
     * @return array Resultado de la operación
     */
    public static function getVotantesDisponibles($rqst)
    {
        $fichaTecnicaId = isset($rqst['ficha_tecnica_id']) ? intval($rqst['ficha_tecnica_id']) : 0;

        if ($fichaTecnicaId === 0) {
            return Util::error_missing_data_description('ID de ficha técnica requerido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener votantes que NO han contestado este cuestionario
            $q = "SELECT v.id, v.nombre_completo, v.username, v.email
                FROM " . $db->getTable('tbl_votantes') . " v
                WHERE v.estado = 'activo'
                AND v.id NOT IN (
                    SELECT DISTINCT i.tbl_votante_id
                    FROM " . $db->getTable('tbl_cuestionario_intentos') . " i
                    WHERE i.tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id
                    AND i.tbl_votante_id IS NOT NULL
                )
                ORDER BY v.nombre_completo ASC";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':ficha_tecnica_id' => $fichaTecnicaId]);
            $votantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return array('output' => array('valid' => true, 'response' => $votantes));

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general('Error al obtener votantes disponibles: ' . $e->getMessage());
        }
    }

    /**
     * Guarda las respuestas de un cuestionario
     * @param array $rqst Datos de la solicitud
     * @return array Resultado de la operación
     */
    public static function save($rqst)
    {
        // Validar que venga el JSON con los datos
        if (!isset($rqst['data']) || empty($rqst['data'])) {
            return Util::error_missing_data();
        }

        $data = json_decode($rqst['data'], true);

        $votanteId = SessionData::getUserId();
        $data['tbl_votante_id'] = $votanteId;

        if (!$data) {
            return Util::error_missing_data_description('Los datos no son válidos');
        }

        // Validar campos requeridos
        if (empty($data['ficha_tecnica_id'])) {
            return Util::error_missing_data_description('Faltan datos obligatorios (ficha técnica)');
        }

        if (empty($data['preguntas']) || !is_array($data['preguntas'])) {
            return Util::error_missing_data_description('No se enviaron respuestas de preguntas');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Verificar si el votante ya respondió este cuestionario
            $qVerificar = "SELECT id FROM " . $db->getTable('tbl_cuestionario_intentos') . "
                WHERE tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id
                AND tbl_votante_id = :tbl_votante_id
                LIMIT 1";

            $stmtVerificar = $pdo->prepare($qVerificar);
            $stmtVerificar->execute([
                ':ficha_tecnica_id' => $data['ficha_tecnica_id'],
                ':tbl_votante_id' => $data['tbl_votante_id']
            ]);

            if ($stmtVerificar->fetch()) {
                $db->closeConect();
                return Util::error_general('Este votante ya ha respondido este cuestionario');
            }

            // Iniciar transacción
            $pdo->beginTransaction();

            // 1. Insertar el intento de respuesta (cabecera)
            $qIntento = "INSERT INTO " . $db->getTable('tbl_cuestionario_intentos') . "
                (tbl_ficha_tecnica_encuesta_id, tbl_votante_id, fecha_respuesta, dtcreate)
                VALUES (:ficha_tecnica_id, :tbl_votante_id, NOW(), NOW())";

            $stmtIntento = $pdo->prepare($qIntento);
            $stmtIntento->execute([
                ':ficha_tecnica_id' => $data['ficha_tecnica_id'],
                ':tbl_votante_id' => $data['tbl_votante_id']
            ]);

            $intentoId = $pdo->lastInsertId();

            // 2. Insertar cada respuesta de pregunta
            $qRespuesta = "INSERT INTO " . $db->getTable('tbl_cuestionario_respuestas') . "
                (tbl_intento_id, tbl_pregunta_id, tbl_opcion_respuesta_id, respuesta_texto, dtcreate)
                VALUES (:intento_id, :pregunta_id, :opcion_id, :texto, NOW())";

            $stmtRespuesta = $pdo->prepare($qRespuesta);

            foreach ($data['preguntas'] as $pregunta) {
                $preguntaId = $pregunta['pregunta_id'];

                // Si hay opciones seleccionadas (radio/checkbox)
                if (!empty($pregunta['opciones']) && is_array($pregunta['opciones'])) {
                    foreach ($pregunta['opciones'] as $opcionId) {
                        $stmtRespuesta->execute([
                            ':intento_id' => $intentoId,
                            ':pregunta_id' => $preguntaId,
                            ':opcion_id' => $opcionId,
                            ':texto' => null
                        ]);
                    }
                }

                // Si hay respuesta de texto (textarea)
                if (!empty($pregunta['texto'])) {
                    $stmtRespuesta->execute([
                        ':intento_id' => $intentoId,
                        ':pregunta_id' => $preguntaId,
                        ':opcion_id' => null,
                        ':texto' => $pregunta['texto']
                    ]);
                }
            }

            // Commit de la transacción
            $pdo->commit();
            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'message' => 'Respuestas guardadas correctamente',
                    'intento_id' => $intentoId
                ]
            ];

        } catch (Exception $e) {
            // Rollback en caso de error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $db->closeConect();

            return Util::error_general('Error al guardar respuestas: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene todas las respuestas de una ficha técnica
     * @param array $rqst Parámetros de búsqueda
     * @return array Resultado de la operación
     */
    public static function getAll($rqst)
    {
        $fichaTecnicaId = isset($rqst['ficha_tecnica_id']) ? intval($rqst['ficha_tecnica_id']) : 0;
        $intentoId = isset($rqst['intento_id']) ? intval($rqst['intento_id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    i.id as intento_id,
                    i.tbl_ficha_tecnica_encuesta_id,
                    i.nombre_respondiente,
                    i.identificacion_respondiente,
                    i.email_respondiente,
                    i.telefono_respondiente,
                    i.fecha_respuesta,
                    i.dtcreate,
                    COUNT(DISTINCT r.tbl_pregunta_id) as total_preguntas_respondidas
                FROM " . $db->getTable('tbl_cuestionario_intentos') . " i
                LEFT JOIN " . $db->getTable('tbl_cuestionario_respuestas') . " r ON i.id = r.tbl_intento_id
                WHERE 1=1";

            $params = [];

            if ($intentoId > 0) {
                $q .= " AND i.id = :intento_id";
                $params[':intento_id'] = $intentoId;
            } elseif ($fichaTecnicaId > 0) {
                $q .= " AND i.tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id";
                $params[':ficha_tecnica_id'] = $fichaTecnicaId;
            }

            $q .= " GROUP BY i.id ORDER BY i.dtcreate DESC";

            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => $intentos
                ]
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general('Error al obtener respuestas: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el detalle de respuestas de un intento específico
     * @param array $rqst Parámetros de búsqueda
     * @return array Resultado de la operación
     */
    public static function getDetalle($rqst)
    {
        $intentoId = isset($rqst['intento_id']) ? intval($rqst['intento_id']) : 0;

        if ($intentoId === 0) {
            return Util::error_missing_data_description('ID de intento requerido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    r.id,
                    r.tbl_pregunta_id,
                    r.tbl_opcion_respuesta_id,
                    r.respuesta_texto,
                    p.texto_pregunta,
                    p.tipo_pregunta,
                    o.texto_opcion
                FROM " . $db->getTable('tbl_cuestionario_respuestas') . " r
                INNER JOIN " . $db->getTable('tbl_preguntas') . " p ON r.tbl_pregunta_id = p.id
                LEFT JOIN " . $db->getTable('tbl_opciones_respuesta') . " o ON r.tbl_opcion_respuesta_id = o.id
                WHERE r.tbl_intento_id = :intento_id
                ORDER BY p.orden ASC";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':intento_id' => $intentoId]);
            $respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => $respuestas
                ]
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general('Error al obtener detalle de respuestas: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene estadísticas generales de una ficha técnica
     * @param array $rqst Parámetros de búsqueda
     * @return array Resultado de la operación
     */
    public static function getEstadisticas($rqst)
    {
        $fichaTecnicaId = isset($rqst['ficha_tecnica_id']) ? intval($rqst['ficha_tecnica_id']) : 0;

        if ($fichaTecnicaId === 0) {
            return Util::error_missing_data_description('ID de ficha técnica requerido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Total de votantes activos
            $qTotal = "SELECT COUNT(*) as total
                FROM " . $db->getTable('tbl_votantes') . "
                WHERE estado = 'activo'";
            $stmtTotal = $pdo->prepare($qTotal);
            $stmtTotal->execute();
            $totalVotantes = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

            // Votantes que han respondido
            $qRespondieron = "SELECT COUNT(DISTINCT tbl_votante_id) as total
                FROM " . $db->getTable('tbl_cuestionario_intentos') . "
                WHERE tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id
                AND tbl_votante_id IS NOT NULL";
            $stmtRespondieron = $pdo->prepare($qRespondieron);
            $stmtRespondieron->execute([':ficha_tecnica_id' => $fichaTecnicaId]);
            $totalRespondieron = $stmtRespondieron->fetch(PDO::FETCH_ASSOC)['total'];

            // Votantes que NO han respondido
            $totalNoRespondieron = $totalVotantes - $totalRespondieron;

            // Porcentaje de respuestas
            $porcentajeRespuestas = $totalVotantes > 0 ? round(($totalRespondieron / $totalVotantes) * 100, 2) : 0;

            // Últimas 10 respuestas
            $qUltimas = "SELECT
                    i.id,
                    i.fecha_respuesta,
                    v.nombre_completo,
                    v.email,
                    COUNT(DISTINCT r.tbl_pregunta_id) as preguntas_respondidas
                FROM " . $db->getTable('tbl_cuestionario_intentos') . " i
                INNER JOIN " . $db->getTable('tbl_votantes') . " v ON i.tbl_votante_id = v.id
                LEFT JOIN " . $db->getTable('tbl_cuestionario_respuestas') . " r ON i.id = r.tbl_intento_id
                WHERE i.tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id
                AND i.tbl_votante_id IS NOT NULL
                GROUP BY i.id
                ORDER BY i.fecha_respuesta DESC
                LIMIT 10";
            $stmtUltimas = $pdo->prepare($qUltimas);
            $stmtUltimas->execute([':ficha_tecnica_id' => $fichaTecnicaId]);
            $ultimasRespuestas = $stmtUltimas->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => [
                        'total_votantes' => $totalVotantes,
                        'total_respondieron' => $totalRespondieron,
                        'total_no_respondieron' => $totalNoRespondieron,
                        'porcentaje_respuestas' => $porcentajeRespuestas,
                        'ultimas_respuestas' => $ultimasRespuestas
                    ]
                ]
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general('Error al obtener estadísticas: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene lista de votantes que respondieron
     * @param array $rqst Parámetros de búsqueda
     * @return array Resultado de la operación
     */
    public static function getVotantesQueRespondieron($rqst)
    {
        $fichaTecnicaId = isset($rqst['ficha_tecnica_id']) ? intval($rqst['ficha_tecnica_id']) : 0;

        if ($fichaTecnicaId === 0) {
            return Util::error_missing_data_description('ID de ficha técnica requerido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    v.id,
                    v.nombre_completo,
                    v.email,
                    v.username,
                    v.genero,
                    v.rango_edad,
                    v.ideologia,
                    i.fecha_respuesta,
                    i.id as intento_id,
                    COUNT(DISTINCT r.tbl_pregunta_id) as preguntas_respondidas
                FROM " . $db->getTable('tbl_votantes') . " v
                INNER JOIN " . $db->getTable('tbl_cuestionario_intentos') . " i ON v.id = i.tbl_votante_id
                LEFT JOIN " . $db->getTable('tbl_cuestionario_respuestas') . " r ON i.id = r.tbl_intento_id
                WHERE i.tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id
                AND v.estado = 'activo'
                GROUP BY v.id, i.id
                ORDER BY i.fecha_respuesta DESC";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':ficha_tecnica_id' => $fichaTecnicaId]);
            $votantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => $votantes
                ]
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general('Error al obtener votantes que respondieron: ' . $e->getMessage());
        }
    }

    /**
     * Verifica si un votante específico ya contestó una ficha técnica
     * @param array $rqst ['ficha_tecnica_id' => int, 'votante_id' => int]
     * @return array ['contestada' => bool]
     */
    public static function verificarSiYaContesto($rqst)
    {
        $fichaTecnicaId = isset($rqst['ficha_tecnica_id']) ? intval($rqst['ficha_tecnica_id']) : 0;
        $votanteId = isset($rqst['votante_id']) ? intval($rqst['votante_id']) : 0;

        if ($fichaTecnicaId === 0 || $votanteId === 0) {
            return ['output' => ['valid' => false, 'contestada' => false]];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT id FROM " . $db->getTable('tbl_cuestionario_intentos') . "
                  WHERE tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id
                  AND tbl_votante_id = :votante_id
                  LIMIT 1";

            $stmt = $pdo->prepare($q);
            $stmt->execute([
                ':ficha_tecnica_id' => $fichaTecnicaId,
                ':votante_id' => $votanteId
            ]);

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $contestada = !empty($resultado);

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'contestada' => $contestada
                ]
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return [
                'output' => [
                    'valid' => false,
                    'contestada' => false,
                    'error' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Obtiene las respuestas de un votante específico para una encuesta
     * @param array $rqst ['ficha_tecnica_id' => int, 'votante_id' => int]
     * @return array Resultado con las respuestas del votante
     */
    public static function getRespuestasVotante($rqst)
    {
        $fichaTecnicaId = isset($rqst['ficha_tecnica_id']) ? intval($rqst['ficha_tecnica_id']) : 0;
        $votanteId = isset($rqst['votante_id']) ? intval($rqst['votante_id']) : 0;

        if ($fichaTecnicaId === 0 || $votanteId === 0) {
            return Util::error_missing_data_description('Faltan parámetros requeridos');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener el intento del votante
            $qIntento = "SELECT id, fecha_respuesta
                        FROM " . $db->getTable('tbl_cuestionario_intentos') . "
                        WHERE tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id
                        AND tbl_votante_id = :votante_id
                        LIMIT 1";
            // print($qIntento);
            $stmtIntento = $pdo->prepare($qIntento);
            $stmtIntento->execute([
                ':ficha_tecnica_id' => $fichaTecnicaId,
                ':votante_id' => $votanteId
            ]);

            $intento = $stmtIntento->fetch(PDO::FETCH_ASSOC);

            if (!$intento) {
                $db->closeConect();
                return Util::error_general('No se encontraron respuestas para este votante');
            }

            // Obtener las respuestas con sus preguntas y opciones
            $qRespuestas = "SELECT
                                p.id as pregunta_id,
                                p.texto_pregunta,
                                p.tipo_pregunta,
                                p.orden,
                                GROUP_CONCAT(o.texto_opcion SEPARATOR '|') as opciones_seleccionadas,
                                MAX(r.respuesta_texto) as respuesta_texto
                            FROM " . $db->getTable('tbl_cuestionario_respuestas') . " r
                            INNER JOIN " . $db->getTable('tbl_preguntas') . " p ON r.tbl_pregunta_id = p.id
                            LEFT JOIN " . $db->getTable('tbl_opciones_respuesta') . " o ON r.tbl_opcion_respuesta_id = o.id
                            WHERE r.tbl_intento_id = :intento_id
                            GROUP BY p.id, p.texto_pregunta, p.tipo_pregunta, p.orden
                            ORDER BY p.orden ASC";

            $stmtRespuestas = $pdo->prepare($qRespuestas);
            $stmtRespuestas->execute([':intento_id' => $intento['id']]);
            $respuestas = $stmtRespuestas->fetchAll(PDO::FETCH_ASSOC);

            // Formatear respuestas
            $respuestasFormateadas = [];
            foreach ($respuestas as $respuesta) {
                $respuestasFormateadas[] = [
                    'pregunta_id' => $respuesta['pregunta_id'],
                    'texto_pregunta' => $respuesta['texto_pregunta'],
                    'tipo_pregunta' => $respuesta['tipo_pregunta'],
                    'orden' => $respuesta['orden'],
                    'opciones_seleccionadas' => !empty($respuesta['opciones_seleccionadas'])
                        ? explode('|', $respuesta['opciones_seleccionadas'])
                        : [],
                    'respuesta_texto' => $respuesta['respuesta_texto']
                ];
            }

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => [
                        'fecha_respuesta' => $intento['fecha_respuesta'],
                        'respuestas' => $respuestasFormateadas
                    ]
                ]
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general('Error al obtener respuestas: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene lista de votantes que NO han respondido
     * @param array $rqst Parámetros de búsqueda
     * @return array Resultado de la operación
     */
    public static function getVotantesQueNoRespondieron($rqst)
    {
        $fichaTecnicaId = isset($rqst['ficha_tecnica_id']) ? intval($rqst['ficha_tecnica_id']) : 0;

        if ($fichaTecnicaId === 0) {
            return Util::error_missing_data_description('ID de ficha técnica requerido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    v.id,
                    v.nombre_completo,
                    v.email,
                    v.username,
                    v.genero,
                    v.rango_edad,
                    v.ideologia
                FROM " . $db->getTable('tbl_votantes') . " v
                WHERE v.estado = 'activo'
                AND v.id NOT IN (
                    SELECT DISTINCT tbl_votante_id
                    FROM " . $db->getTable('tbl_cuestionario_intentos') . "
                    WHERE tbl_ficha_tecnica_encuesta_id = :ficha_tecnica_id
                    AND tbl_votante_id IS NOT NULL
                )
                ORDER BY v.nombre_completo ASC";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':ficha_tecnica_id' => $fichaTecnicaId]);
            $votantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => $votantes
                ]
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general('Error al obtener votantes que no respondieron: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene las preguntas de opción múltiple del cuestionario activo
     * para mostrar en el selector del index.php
     */
    public static function obtenerPreguntasCuestionarioActivo($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener la ficha técnica habilitada
            $qFicha = "SELECT id, realizada_por_o_encomendada_por as nombre
                       FROM " . $db->getTable('tbl_ficha_tecnica_encuestas') . "
                       WHERE habilitado = 'si'
                       ORDER BY dtcreate DESC
                       LIMIT 1";
            $stmt = $pdo->prepare($qFicha);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ficha) {
                $db->closeConect();
                return ["success" => false, "message" => "No hay encuestas habilitadas"];
            }

            // Obtener todas las preguntas de opción múltiple
            $qPreguntas = "SELECT p.id, p.texto_pregunta, p.orden
                          FROM " . $db->getTable('tbl_preguntas') . " p
                          WHERE p.tbl_ficha_tecnica_encuesta_id = :ficha_id
                          AND p.tipo_pregunta IN ('radio', 'checkbox', 'Seleccion_Multiple_unica_respuesta', 'Seleccion_Multiple_multiple_respuesta', 'Dicotomica', 'Preguntas_Cardinales', 'Preguntas_Ordinales')
                          ORDER BY p.orden ASC";
            $stmt = $pdo->prepare($qPreguntas);
            $stmt->execute([":ficha_id" => $ficha['id']]);
            $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                "success" => true,
                "ficha" => [
                    "id" => $ficha['id'],
                    "nombre" => $ficha['nombre']
                ],
                "preguntas" => $preguntas
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    /**
     * Obtiene resultados generales de encuestas para mostrar en el index (gráfico general)
     * Similar a obtenerSondeoGeneral pero para encuestas
     * Acepta pregunta_id para filtrar por pregunta específica
     */
    public static function obtenerEncuestaGeneralIndex($rqst)
    {
        $preguntaId = isset($rqst['pregunta_id']) ? intval($rqst['pregunta_id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener la primera ficha técnica habilitada
            $qFicha = "SELECT id, realizada_por_o_encomendada_por as nombre
                       FROM " . $db->getTable('tbl_ficha_tecnica_encuestas') . "
                       WHERE habilitado = 'si'
                       ORDER BY dtcreate DESC
                       LIMIT 1";
            $stmt = $pdo->prepare($qFicha);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ficha) {
                return ["success" => false, "message" => "No hay encuestas habilitadas"];
            }

            // Si se especifica pregunta_id, usarla; si no, obtener la primera
            if ($preguntaId > 0) {
                $qPregunta = "SELECT p.id, p.texto_pregunta
                              FROM " . $db->getTable('tbl_preguntas') . " p
                              WHERE p.id = :pregunta_id
                              AND p.tbl_ficha_tecnica_encuesta_id = :ficha_id";
                $stmt = $pdo->prepare($qPregunta);
                $stmt->execute([":pregunta_id" => $preguntaId, ":ficha_id" => $ficha['id']]);
            } else {
                $qPregunta = "SELECT p.id, p.texto_pregunta
                              FROM " . $db->getTable('tbl_preguntas') . " p
                              WHERE p.tbl_ficha_tecnica_encuesta_id = :ficha_id
                              AND p.tipo_pregunta IN ('radio', 'checkbox', 'Seleccion_Multiple_unica_respuesta', 'Seleccion_Multiple_multiple_respuesta', 'Dicotomica', 'Preguntas_Cardinales', 'Preguntas_Ordinales')
                              ORDER BY p.orden ASC
                              LIMIT 1";
                $stmt = $pdo->prepare($qPregunta);
                $stmt->execute([":ficha_id" => $ficha['id']]);
            }
            $pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pregunta) {
                return ["success" => false, "message" => "No hay preguntas de opción múltiple"];
            }

            // Obtener conteo de votos por opción
            $qVotos = "SELECT
                          o.id AS candidato_id,
                          o.texto_opcion AS nombre_completo,
                          '' AS foto,
                          'img/option_default.png' AS foto_url,
                          COUNT(r.id) AS total
                       FROM " . $db->getTable('tbl_opciones_respuesta') . " o
                       LEFT JOIN " . $db->getTable('tbl_cuestionario_respuestas') . " r
                          ON r.tbl_opcion_respuesta_id = o.id
                       WHERE o.tbl_pregunta_id = :pregunta_id
                       GROUP BY o.id, o.texto_opcion
                       ORDER BY total DESC";

            $stmt = $pdo->prepare($qVotos);
            $stmt->execute([":pregunta_id" => $pregunta['id']]);
            $votos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                "success" => true,
                "sondeo" => [
                    "id" => $ficha['id'],
                    "sondeo" => $ficha['nombre'],
                    "descripcion_sondeo" => $pregunta['texto_pregunta'],
                    "pregunta_id" => $pregunta['id']
                ],
                "votos" => $votos
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    /**
     * Obtiene resultados de encuesta filtrados por departamento (para el mapa del index)
     * Acepta pregunta_id para filtrar por pregunta específica
     */
    public static function obtenerEncuestaMapaIndex($rqst)
    {
        $depClick = $rqst['departamento_click'] ?? null;
        $preguntaId = isset($rqst['pregunta_id']) ? intval($rqst['pregunta_id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener la primera ficha técnica habilitada
            $qFicha = "SELECT id, realizada_por_o_encomendada_por as nombre
                       FROM " . $db->getTable('tbl_ficha_tecnica_encuestas') . "
                       WHERE habilitado = 'si'
                       ORDER BY dtcreate DESC
                       LIMIT 1";
            $stmt = $pdo->prepare($qFicha);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ficha) {
                return ["success" => false, "message" => "No hay encuestas habilitadas"];
            }

            // Si se especifica pregunta_id, usarla; si no, obtener la primera
            if ($preguntaId > 0) {
                $qPregunta = "SELECT p.id, p.texto_pregunta
                              FROM " . $db->getTable('tbl_preguntas') . " p
                              WHERE p.id = :pregunta_id
                              AND p.tbl_ficha_tecnica_encuesta_id = :ficha_id";
                $stmt = $pdo->prepare($qPregunta);
                $stmt->execute([":pregunta_id" => $preguntaId, ":ficha_id" => $ficha['id']]);
            } else {
                $qPregunta = "SELECT p.id, p.texto_pregunta
                              FROM " . $db->getTable('tbl_preguntas') . " p
                              WHERE p.tbl_ficha_tecnica_encuesta_id = :ficha_id
                              AND p.tipo_pregunta IN ('radio', 'checkbox', 'Seleccion_Multiple_unica_respuesta', 'Seleccion_Multiple_multiple_respuesta', 'Dicotomica', 'Preguntas_Cardinales', 'Preguntas_Ordinales')
                              ORDER BY p.orden ASC
                              LIMIT 1";
                $stmt = $pdo->prepare($qPregunta);
                $stmt->execute([":ficha_id" => $ficha['id']]);
            }
            $pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pregunta) {
                return ["success" => false, "message" => "No hay preguntas de opción múltiple"];
            }

            // Obtener conteo de votos por opción filtrado por departamento
            $depFilter = "";
            $params = [":pregunta_id" => $pregunta['id']];

            if (!empty($depClick)) {
                $depFilter = " AND v.codigo_departamento = :dep ";
                $params[":dep"] = $depClick;
            }

            $qVotos = "SELECT
                          o.id AS candidato_id,
                          o.texto_opcion AS nombre_completo,
                          '' AS foto,
                          'img/option_default.png' AS foto_url,
                          COUNT(r.id) AS total
                       FROM " . $db->getTable('tbl_opciones_respuesta') . " o
                       LEFT JOIN " . $db->getTable('tbl_cuestionario_respuestas') . " r
                          ON r.tbl_opcion_respuesta_id = o.id
                       LEFT JOIN " . $db->getTable('tbl_cuestionario_intentos') . " i
                          ON r.tbl_intento_id = i.id
                       LEFT JOIN " . $db->getTable('tbl_votantes') . " v
                          ON i.tbl_votante_id = v.id
                       WHERE o.tbl_pregunta_id = :pregunta_id
                       $depFilter
                       GROUP BY o.id, o.texto_opcion
                       ORDER BY total DESC";

            $stmt = $pdo->prepare($qVotos);
            $stmt->execute($params);
            $votos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                "success" => true,
                "sondeo" => [
                    "id" => $ficha['id'],
                    "sondeo" => $ficha['nombre'],
                    "descripcion_sondeo" => $pregunta['texto_pregunta'],
                    "pregunta_id" => $pregunta['id']
                ],
                "votos" => $votos
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    /**
     * Obtiene el ganador por cada departamento para el cuestionario activo (para colorear el mapa)
     * Similar a Sondeo::ganadorPorTodosLosDepartamentos()
     * Acepta pregunta_id opcional para filtrar por pregunta específica
     * @param int $preguntaIdParam ID de la pregunta (opcional, si es 0 usa la primera)
     * @return array ['codigo_departamento' => ['ganador' => opcion_id, 'empate' => bool], ...]
     */
    public static function ganadorPorTodosLosDepartamentosCuestionario($preguntaIdParam = 0)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener la ficha técnica habilitada
            $qFicha = "SELECT id FROM " . $db->getTable('tbl_ficha_tecnica_encuestas') . "
                       WHERE habilitado = 'si'
                       ORDER BY dtcreate DESC
                       LIMIT 1";
            $stmt = $pdo->prepare($qFicha);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ficha) {
                $db->closeConect();
                return [];
            }

            // Si se especifica pregunta_id, usarla; si no, obtener la primera
            if ($preguntaIdParam > 0) {
                $qPregunta = "SELECT p.id
                              FROM " . $db->getTable('tbl_preguntas') . " p
                              WHERE p.id = :pregunta_id
                              AND p.tbl_ficha_tecnica_encuesta_id = :ficha_id";
                $stmt = $pdo->prepare($qPregunta);
                $stmt->execute([":pregunta_id" => $preguntaIdParam, ":ficha_id" => $ficha['id']]);
            } else {
                $qPregunta = "SELECT p.id
                              FROM " . $db->getTable('tbl_preguntas') . " p
                              WHERE p.tbl_ficha_tecnica_encuesta_id = :ficha_id
                              AND p.tipo_pregunta IN ('radio', 'checkbox', 'Seleccion_Multiple_unica_respuesta', 'Seleccion_Multiple_multiple_respuesta', 'Dicotomica', 'Preguntas_Cardinales', 'Preguntas_Ordinales')
                              ORDER BY p.orden ASC
                              LIMIT 1";
                $stmt = $pdo->prepare($qPregunta);
                $stmt->execute([":ficha_id" => $ficha['id']]);
            }
            $pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pregunta) {
                $db->closeConect();
                return [];
            }

            // Obtener votos por opción agrupados por departamento
            $qVotos = "SELECT
                          v.codigo_departamento,
                          r.tbl_opcion_respuesta_id as opcion_id,
                          COUNT(r.id) as total
                       FROM " . $db->getTable('tbl_cuestionario_respuestas') . " r
                       INNER JOIN " . $db->getTable('tbl_cuestionario_intentos') . " i ON r.tbl_intento_id = i.id
                       INNER JOIN " . $db->getTable('tbl_votantes') . " v ON i.tbl_votante_id = v.id
                       INNER JOIN " . $db->getTable('tbl_opciones_respuesta') . " o ON r.tbl_opcion_respuesta_id = o.id
                       WHERE o.tbl_pregunta_id = :pregunta_id
                       AND v.codigo_departamento IS NOT NULL
                       AND v.codigo_departamento != ''
                       GROUP BY v.codigo_departamento, r.tbl_opcion_respuesta_id
                       ORDER BY v.codigo_departamento, total DESC";

            $stmt = $pdo->prepare($qVotos);
            $stmt->execute([":pregunta_id" => $pregunta['id']]);
            $votos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            // Procesar resultados por departamento
            $resultado = [];
            $votosPorDepartamento = [];

            // Agrupar votos por departamento
            foreach ($votos as $voto) {
                $dep = $voto['codigo_departamento'];
                if (!isset($votosPorDepartamento[$dep])) {
                    $votosPorDepartamento[$dep] = [];
                }
                $votosPorDepartamento[$dep][] = [
                    'opcion_id' => $voto['opcion_id'],
                    'total' => (int)$voto['total']
                ];
            }

            // Determinar ganador o empate por departamento
            foreach ($votosPorDepartamento as $dep => $votosDepto) {
                if (empty($votosDepto)) {
                    continue;
                }

                // Ordenar por total descendente
                usort($votosDepto, function($a, $b) {
                    return $b['total'] - $a['total'];
                });

                $maxVotos = $votosDepto[0]['total'];
                $ganador = $votosDepto[0]['opcion_id'];

                // Verificar si hay empate
                $empate = false;
                if (count($votosDepto) > 1 && $votosDepto[1]['total'] == $maxVotos) {
                    $empate = true;
                }

                $resultado[$dep] = [
                    'ganador' => $ganador,
                    'empate' => $empate
                ];
            }

            return $resultado;

        } catch (Exception $e) {
            $db->closeConect();
            return [];
        }
    }

    /**
     * Obtiene las opciones de la primera pregunta del cuestionario activo con sus IDs
     * (para asignar colores en el mapa)
     * @return array [['id' => opcion_id, 'texto' => texto_opcion], ...]
     */
    public static function obtenerOpcionesCuestionarioActivo()
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener la ficha técnica habilitada
            $qFicha = "SELECT id FROM " . $db->getTable('tbl_ficha_tecnica_encuestas') . "
                       WHERE habilitado = 'si'
                       ORDER BY dtcreate DESC
                       LIMIT 1";
            $stmt = $pdo->prepare($qFicha);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ficha) {
                $db->closeConect();
                return [];
            }

            // Obtener la primera pregunta de opción múltiple
            $qPregunta = "SELECT p.id
                          FROM " . $db->getTable('tbl_preguntas') . " p
                          WHERE p.tbl_ficha_tecnica_encuesta_id = :ficha_id
                          AND p.tipo_pregunta IN ('radio', 'checkbox', 'Seleccion_Multiple_unica_respuesta', 'Seleccion_Multiple_multiple_respuesta', 'Dicotomica', 'Preguntas_Cardinales', 'Preguntas_Ordinales')
                          ORDER BY p.orden ASC
                          LIMIT 1";
            $stmt = $pdo->prepare($qPregunta);
            $stmt->execute([":ficha_id" => $ficha['id']]);
            $pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pregunta) {
                $db->closeConect();
                return [];
            }

            // Obtener opciones de la pregunta
            $qOpciones = "SELECT id, texto_opcion as texto
                          FROM " . $db->getTable('tbl_opciones_respuesta') . "
                          WHERE tbl_pregunta_id = :pregunta_id
                          ORDER BY id";
            $stmt = $pdo->prepare($qOpciones);
            $stmt->execute([":pregunta_id" => $pregunta['id']]);
            $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();
            return $opciones;

        } catch (Exception $e) {
            $db->closeConect();
            return [];
        }
    }

    /**
     * Obtiene los colores para el mapa según una pregunta específica
     * Devuelve datos para actualizar el mapa dinámicamente vía AJAX
     * @param array $rqst Con pregunta_id
     * @return array Con ganadores por departamento y colores de opciones
     */
    public static function obtenerColoresMapaCuestionario($rqst)
    {
        $preguntaId = isset($rqst['pregunta_id']) ? intval($rqst['pregunta_id']) : 0;

        // Paleta de colores
        $paletaColores = [
            "#1f77b4", "#ff7f0e", "#2ca02c", "#d62728", "#9467bd",
            "#8c564b", "#e377c2", "#7f7f7f", "#bcbd22", "#17becf"
        ];

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener ficha técnica habilitada
            $qFicha = "SELECT id FROM " . $db->getTable('tbl_ficha_tecnica_encuestas') . "
                       WHERE habilitado = 'si'
                       ORDER BY dtcreate DESC
                       LIMIT 1";
            $stmt = $pdo->prepare($qFicha);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ficha) {
                $db->closeConect();
                return ["success" => false, "message" => "No hay cuestionario activo"];
            }

            // Obtener la pregunta (específica o la primera)
            if ($preguntaId > 0) {
                $qPregunta = "SELECT p.id FROM " . $db->getTable('tbl_preguntas') . " p
                              WHERE p.id = :pregunta_id AND p.tbl_ficha_tecnica_encuesta_id = :ficha_id";
                $stmt = $pdo->prepare($qPregunta);
                $stmt->execute([":pregunta_id" => $preguntaId, ":ficha_id" => $ficha['id']]);
            } else {
                $qPregunta = "SELECT p.id FROM " . $db->getTable('tbl_preguntas') . " p
                              WHERE p.tbl_ficha_tecnica_encuesta_id = :ficha_id
                              AND p.tipo_pregunta IN ('radio', 'checkbox', 'Seleccion_Multiple_unica_respuesta', 'Seleccion_Multiple_multiple_respuesta', 'Dicotomica', 'Preguntas_Cardinales', 'Preguntas_Ordinales')
                              ORDER BY p.orden ASC LIMIT 1";
                $stmt = $pdo->prepare($qPregunta);
                $stmt->execute([":ficha_id" => $ficha['id']]);
            }
            $pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pregunta) {
                $db->closeConect();
                return ["success" => false, "message" => "Pregunta no encontrada"];
            }

            // Obtener opciones y asignar colores
            $qOpciones = "SELECT id FROM " . $db->getTable('tbl_opciones_respuesta') . "
                          WHERE tbl_pregunta_id = :pregunta_id ORDER BY id";
            $stmt = $pdo->prepare($qOpciones);
            $stmt->execute([":pregunta_id" => $pregunta['id']]);
            $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $coloresOpciones = [];
            foreach ($opciones as $index => $opc) {
                $coloresOpciones[$opc['id']] = $paletaColores[$index % count($paletaColores)];
            }

            $db->closeConect();

            // Obtener ganadores por departamento
            $ganadores = self::ganadorPorTodosLosDepartamentosCuestionario($pregunta['id']);

            return [
                "success" => true,
                "colores" => $coloresOpciones,
                "ganadores" => $ganadores
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
}
