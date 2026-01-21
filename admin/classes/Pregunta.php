<?php

/**
 * Clase Pregunta
 * Gestiona las operaciones CRUD para las tablas 'tbl_preguntas' y 'tbl_opciones_respuesta'.
 */
class Pregunta
{
    public function __construct()
    {
    }

    /**
     * Obtiene preguntas, opcionalmente filtradas por ID de pregunta o ID de encuesta.
     * Incluye las opciones de respuesta asociadas a cada pregunta.
     */
    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $ids = isset($rqst['ids']) ? $rqst['ids'] : ''; // Soporte para múltiples IDs
        $tbl_ficha_tecnica_encuesta_id = isset($rqst['tbl_ficha_tecnica_encuesta_id']) ? intval($rqst['tbl_ficha_tecnica_encuesta_id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT
                p.id,
                p.tbl_ficha_tecnica_encuesta_id,
                p.texto_pregunta,
                p.tipo_pregunta,
                p.orden,
                p.habilitado,
                p.visualizacion,
                p.tbl_usuario_id,
                p.dtcreate,
                e.tema,
                GROUP_CONCAT(CONCAT_WS(':', o.id, o.texto_opcion) ORDER BY o.orden SEPARATOR ';') AS opciones_str
            FROM " . $db->getTable('tbl_preguntas') . " p
                LEFT JOIN " . $db->getTable('tbl_opciones_respuesta') . " o
                    ON p.id = o.tbl_pregunta_id
                LEFT JOIN " . $db->getTable('tbl_encuestas') . " e
                    ON p.tbl_ficha_tecnica_encuesta_id = e.id";
        $params = [];
        $conditions = [];

        if ($id > 0) {
            $conditions[] = "p.id = :id";
            $params[':id'] = $id;
        }
        // Soporte para múltiples IDs separados por coma
        elseif (!empty($ids)) {
            $idsArray = explode(',', $ids);
            $idsArray = array_map('intval', $idsArray);
            $idsArray = array_filter($idsArray, function($val) { return $val > 0; });
            if (!empty($idsArray)) {
                $placeholders = implode(',', array_fill(0, count($idsArray), '?'));
                $conditions[] = "p.id IN ($placeholders)";
                $params = array_merge($params, $idsArray);
            }
        }
        if ($tbl_ficha_tecnica_encuesta_id > 0) {
            $conditions[] = "p.tbl_ficha_tecnica_encuesta_id = :tbl_ficha_tecnica_encuesta_id";
            $params[':tbl_ficha_tecnica_encuesta_id'] = $tbl_ficha_tecnica_encuesta_id;
        }

        if (!empty($conditions)) {
            $q .= " WHERE " . implode(' AND ', $conditions);
        }
        $q .= " GROUP BY p.id ORDER BY p.orden ASC";

        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Post-procesar para convertir 'opciones_str' en un array de objetos
            foreach ($arr as &$pregunta) {
                $pregunta['opciones'] = [];
                if (!empty($pregunta['opciones_str'])) {
                    $opcionesRaw = explode(';', $pregunta['opciones_str']);
                    foreach ($opcionesRaw as $opcionRaw) {
                        list($opcionId, $textoOpcion) = explode(':', $opcionRaw, 2);
                        $pregunta['opciones'][] = ['id' => intval($opcionId), 'texto' => $textoOpcion];
                    }
                }
                unset($pregunta['opciones_str']); 
            }
            if ($arr) {
                $arrjson = array('output' => array('valid' => true, 'response' => $arr));
            } else {
                $arrjson = array('output' => array('valid' => true, 'response' => []));
            }
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al obtener los datos de preguntas.');
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_ficha_tecnica_encuesta_id = isset($rqst['tbl_ficha_tecnica_encuesta_id']) ? intval($rqst['tbl_ficha_tecnica_encuesta_id']) : 0;
        $texto_pregunta = isset($rqst['texto_pregunta']) ? trim($rqst['texto_pregunta']) : '';
        $tipo_pregunta = isset($rqst['tipo_pregunta']) ? trim($rqst['tipo_pregunta']) : null;
        $orden = isset($rqst['orden']) ? intval($rqst['orden']) : null;
        $limite_respuesta_multiple = isset($rqst['limite_respuesta_multiple']) ? intval($rqst['limite_respuesta_multiple']) : null;
        $habilitado = isset($rqst['habilitado']) ? trim($rqst['habilitado']) : 'si';
        $visualizacion = isset($rqst['visualizacion']) ? trim($rqst['visualizacion']) : 'si';
        $tbl_usuario_id = isset($_SESSION['session_user']['id']) ? intval($_SESSION['session_user']['id']) : 1; // Asume ID 1 si no hay sesión

        // Opciones de respuesta como un array de strings (ej. ['Opcion A', 'Opcion B'])
        // Si vienen con IDs (para actualizar), se procesarán en la lógica de actualización.
        $opciones_respuesta = isset($rqst['opciones']) && is_array($rqst['opciones']) ? $rqst['opciones'] : [];

        // Validaciones
        if ($tbl_ficha_tecnica_encuesta_id == 0) {
            return Util::error_missing_data_description('La encuesta es requerida.');
        }
        if (empty($texto_pregunta)) {
            return Util::error_missing_data_description('El texto de la pregunta es requerido.');
        }
        // Validar que haya opciones si el tipo de pregunta no es 'Texto Libre' (ejemplo)
        if (empty($opciones_respuesta)) {
            return Util::error_missing_data_description('Se requiere al menos una opción de respuesta.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        $pdo->beginTransaction();

        try {
            $pregunta_id = $id;

            if ($id > 0) {
                // Actualizar pregunta existente
                $q = "UPDATE " . $db->getTable('tbl_preguntas') . "
                      SET tbl_ficha_tecnica_encuesta_id = :tbl_ficha_tecnica_encuesta_id,
                          texto_pregunta = :texto_pregunta,
                          tipo_pregunta = :tipo_pregunta,
                          orden = :orden,
                          tbl_usuario_id = :tbl_usuario_id,
                          limite_respuesta_multiple = :limite_respuesta_multiple,
                          habilitado = :habilitado,
                          visualizacion = :visualizacion
                      WHERE id = :id";
                $stmt = $pdo->prepare($q);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            } else {
                // Insertar nueva pregunta
                $q = "INSERT INTO " . $db->getTable('tbl_preguntas') . "
                      (tbl_ficha_tecnica_encuesta_id, texto_pregunta, tipo_pregunta, orden, tbl_usuario_id, dtcreate, limite_respuesta_multiple, habilitado, visualizacion)
                      VALUES (:tbl_ficha_tecnica_encuesta_id, :texto_pregunta, :tipo_pregunta, :orden, :tbl_usuario_id, NOW(), :limite_respuesta_multiple, :habilitado, :visualizacion)";
                $stmt = $pdo->prepare($q);
            }

            $stmt->bindValue(':tbl_ficha_tecnica_encuesta_id', $tbl_ficha_tecnica_encuesta_id, PDO::PARAM_INT);
            $stmt->bindValue(':texto_pregunta', $texto_pregunta, PDO::PARAM_STR);
            $stmt->bindValue(':tipo_pregunta', $tipo_pregunta, is_null($tipo_pregunta) ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':orden', $orden, is_null($orden) ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':tbl_usuario_id', $tbl_usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':limite_respuesta_multiple', $limite_respuesta_multiple, is_null($limite_respuesta_multiple) ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':habilitado', $habilitado, PDO::PARAM_STR);
            $stmt->bindValue(':visualizacion', $visualizacion, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new Exception('Error al guardar la pregunta principal.');
            }

            if ($id === 0) { // Si es una nueva pregunta
                $pregunta_id = $pdo->lastInsertId();
            }

            // Gestionar opciones de respuesta
            // 1. Eliminar opciones antiguas
            $delete_opciones_q = "DELETE FROM " . $db->getTable('tbl_opciones_respuesta') . " WHERE tbl_pregunta_id = :tbl_pregunta_id";
            $delete_opciones_stmt = $pdo->prepare($delete_opciones_q);
            $delete_opciones_stmt->bindValue(':tbl_pregunta_id', $pregunta_id, PDO::PARAM_INT);
            if (!$delete_opciones_stmt->execute()) {
                throw new Exception('Error al eliminar opciones de respuesta antiguas.');
            }

            // 2. Insertar nuevas opciones
            if (!empty($opciones_respuesta)) {
                $insert_opcion_q = "INSERT INTO " . $db->getTable('tbl_opciones_respuesta') . " 
                                    (tbl_pregunta_id, texto_opcion, orden, dtcreate)
                                    VALUES (:tbl_pregunta_id, :texto_opcion, :orden, NOW())";
                $insert_opcion_stmt = $pdo->prepare($insert_opcion_q);
                $opcion_orden_counter = 1;
                foreach ($opciones_respuesta as $opcionTexto) {
                    $insert_opcion_stmt->bindValue(':tbl_pregunta_id', $pregunta_id, PDO::PARAM_INT);
                    $insert_opcion_stmt->bindValue(':texto_opcion', $opcionTexto, PDO::PARAM_STR);
                    $insert_opcion_stmt->bindValue(':orden', $opcion_orden_counter++, PDO::PARAM_INT);
                    if (!$insert_opcion_stmt->execute()) {
                        throw new Exception('Error al insertar opción de respuesta: ' . $opcionTexto);
                    }
                }
            }

            $pdo->commit();
            $arrjson = array('output' => array('valid' => true, 'id' => $pregunta_id));

        } catch (Exception $e) {
            $pdo->rollBack();
            $arrjson = Util::error_general('Error al guardar la pregunta y sus opciones: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Guarda múltiples preguntas en batch
     * @param array $rqst - Request con 'preguntas' como JSON string de array de preguntas
     * @return array - Respuesta estándar
     */
    public static function saveBatch($rqst)
    {
        $preguntasJson = isset($rqst['preguntas']) ? $rqst['preguntas'] : '';

        if (empty($preguntasJson)) {
            return Util::error_missing_data_description('No se recibieron preguntas para guardar');
        }

        $preguntas = json_decode($preguntasJson, true);
        if ($preguntas === null || !is_array($preguntas)) {
            return Util::error_general('Error al decodificar las preguntas: ' . json_last_error_msg());
        }

        if (count($preguntas) === 0) {
            return Util::error_missing_data_description('El array de preguntas está vacío');
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        $preguntasGuardadas = 0;
        $errores = [];

        try {
            $pdo->beginTransaction();

            foreach ($preguntas as $index => $preguntaData) {
                $preguntaNum = $index + 1;

                // Validar datos requeridos
                if (empty($preguntaData['tbl_ficha_tecnica_encuesta_id']) || empty($preguntaData['texto_pregunta'])) {
                    $errores[] = "Pregunta $preguntaNum: Faltan datos requeridos";
                    continue;
                }

                $id = isset($preguntaData['id']) ? intval($preguntaData['id']) : 0;
                $tbl_ficha_tecnica_encuesta_id = intval($preguntaData['tbl_ficha_tecnica_encuesta_id']);
                $texto_pregunta = trim($preguntaData['texto_pregunta']);
                $tipo_pregunta = isset($preguntaData['tipo_pregunta']) ? trim($preguntaData['tipo_pregunta']) : null;
                $orden = isset($preguntaData['orden']) ? intval($preguntaData['orden']) : null;
                $limite_respuesta_multiple = isset($preguntaData['limite_respuesta_multiple']) ? intval($preguntaData['limite_respuesta_multiple']) : 1;
                $tbl_usuario_id = isset($_SESSION['session_user']['id']) ? intval($_SESSION['session_user']['id']) : 1;
                $opciones_respuesta = isset($preguntaData['opciones']) && is_array($preguntaData['opciones']) ? $preguntaData['opciones'] : [];

                // Guardar la pregunta
                if ($id > 0) {
                    // Actualizar pregunta existente
                    $qUpdate = "UPDATE " . $db->getTable('tbl_preguntas') . "
                               SET tbl_ficha_tecnica_encuesta_id = :tbl_ficha_tecnica_encuesta_id,
                                   texto_pregunta = :texto_pregunta,
                                   tipo_pregunta = :tipo_pregunta,
                                   orden = :orden,
                                   limite_respuesta_multiple = :limite_respuesta_multiple
                               WHERE id = :id";

                    $stmtUpdate = $pdo->prepare($qUpdate);
                    $stmtUpdate->execute([
                        ':id' => $id,
                        ':tbl_ficha_tecnica_encuesta_id' => $tbl_ficha_tecnica_encuesta_id,
                        ':texto_pregunta' => $texto_pregunta,
                        ':tipo_pregunta' => $tipo_pregunta,
                        ':orden' => $orden,
                        ':limite_respuesta_multiple' => $limite_respuesta_multiple
                    ]);

                    $pregunta_id = $id;
                } else {
                    // Insertar nueva pregunta
                    $qInsert = "INSERT INTO " . $db->getTable('tbl_preguntas') . "
                               (tbl_ficha_tecnica_encuesta_id, texto_pregunta, tipo_pregunta, orden,
                                limite_respuesta_multiple, tbl_usuario_id, dtcreate)
                               VALUES (:tbl_ficha_tecnica_encuesta_id, :texto_pregunta, :tipo_pregunta, :orden,
                                       :limite_respuesta_multiple, :tbl_usuario_id, NOW())";

                    $stmtInsert = $pdo->prepare($qInsert);
                    $stmtInsert->execute([
                        ':tbl_ficha_tecnica_encuesta_id' => $tbl_ficha_tecnica_encuesta_id,
                        ':texto_pregunta' => $texto_pregunta,
                        ':tipo_pregunta' => $tipo_pregunta,
                        ':orden' => $orden,
                        ':limite_respuesta_multiple' => $limite_respuesta_multiple,
                        ':tbl_usuario_id' => $tbl_usuario_id
                    ]);

                    $pregunta_id = $pdo->lastInsertId();
                }

                // Eliminar opciones anteriores (si existe la pregunta)
                if ($id > 0) {
                    $qDeleteOpciones = "DELETE FROM " . $db->getTable('tbl_opciones_respuesta') . " WHERE tbl_pregunta_id = :pregunta_id";
                    $stmtDeleteOpciones = $pdo->prepare($qDeleteOpciones);
                    $stmtDeleteOpciones->execute([':pregunta_id' => $pregunta_id]);
                }

                // Insertar nuevas opciones
                if (!empty($opciones_respuesta)) {
                    $qOpcion = "INSERT INTO " . $db->getTable('tbl_opciones_respuesta') . "
                               (tbl_pregunta_id, texto_opcion, orden, dtcreate)
                               VALUES (:tbl_pregunta_id, :texto_opcion, :orden, NOW())";
                    $stmtOpcion = $pdo->prepare($qOpcion);

                    foreach ($opciones_respuesta as $orden_opcion => $texto_opcion) {
                        $stmtOpcion->execute([
                            ':tbl_pregunta_id' => $pregunta_id,
                            ':texto_opcion' => trim($texto_opcion),
                            ':orden' => $orden_opcion + 1
                        ]);
                    }
                }

                $preguntasGuardadas++;
            }

            $pdo->commit();

            if (count($errores) > 0) {
                $mensaje = "$preguntasGuardadas pregunta(s) guardada(s). Errores: " . implode(', ', $errores);
                $arrjson = array('output' => array('valid' => true, 'response' => $mensaje));
            } else {
                $arrjson = array('output' => array('valid' => true, 'response' => "$preguntasGuardadas pregunta(s) guardada(s) correctamente"));
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            $arrjson = Util::error_general('Error al guardar las preguntas: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }
}