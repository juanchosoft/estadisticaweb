<?php
/**
 * Clase para gestionar las preguntas y subpreguntas de la grilla de manera dinámica
 */
class PreguntaGrilla
{
    public function __construct() {}

    /**
     * Obtiene todas las preguntas y subpreguntas ordenadas para una grilla
     * @param array $rqst - Request (opcional: grilla_id para futuras personalizaciones)
     * @return array - Respuesta con formato estándar
     */
    public static function obtenerPreguntasConSubpreguntas($rqst = [])
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        // Filtro opcional por grilla_id
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : null;

        try {
            // Construir WHERE dinámico para filtrar por grilla usando la tabla intermedia
            $whereGrilla = '';
            $joinGrilla = '';
            $params = [];

            if ($grilla_id !== null && $grilla_id > 0) {
                // Usar LEFT JOIN con la tabla intermedia para incluir:
                // 1. Preguntas globales (sin asociaciones en tbl_grilla_x_preguntas)
                // 2. Preguntas específicas de esta grilla
                $joinGrilla = "LEFT JOIN " . $db->getTable('tbl_grilla_x_preguntas') . " gxp
                              ON p.id = gxp.tbl_pregunta_id";
                $whereGrilla = "AND (gxp.tbl_grilla_id = :grilla_id OR gxp.tbl_grilla_id IS NULL)";
                $params[':grilla_id'] = $grilla_id;
            }

            // Obtener PREGUNTAS PRINCIPALES (tipo_pregunta = 'pregunta')
            $qPreguntas = "SELECT DISTINCT
                            p.id,
                            p.tipo_pregunta,
                            p.texto_pregunta,
                            p.codigo_pregunta,
                            p.orden,
                            p.opciones_respuesta,
                            p.requiere_todas_si,
                            p.habilita_subpreguntas,
                            p.condicion_habilitacion,
                            p.activa_seccion_subpreguntas,
                            p.habilitado
                           FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p
                           $joinGrilla
                           WHERE p.tipo_pregunta = 'pregunta'
                             AND p.habilitado = TRUE
                             $whereGrilla
                           ORDER BY p.orden ASC";

            $stmtPreguntas = $pdo->prepare($qPreguntas);
            $stmtPreguntas->execute($params);
            $preguntas = $stmtPreguntas->fetchAll(PDO::FETCH_ASSOC);

            // Obtener SUBPREGUNTAS (tipo_pregunta = 'subpregunta')
            $qSubpreguntas = "SELECT DISTINCT
                                p.id,
                                p.tipo_pregunta,
                                p.texto_pregunta,
                                p.codigo_pregunta,
                                p.orden,
                                p.pregunta_padre_id,
                                p.habilitado
                              FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p
                              $joinGrilla
                              WHERE p.tipo_pregunta = 'subpregunta'
                                AND p.habilitado = TRUE
                                $whereGrilla
                              ORDER BY p.orden ASC";

            $stmtSubpreguntas = $pdo->prepare($qSubpreguntas);
            $stmtSubpreguntas->execute($params);
            $subpreguntas = $stmtSubpreguntas->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => array(
                        'preguntas' => $preguntas,
                        'subpreguntas' => $subpreguntas
                    )
                )
            );

        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al obtener preguntas: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Obtiene una pregunta por su ID
     * @param array $rqst - Request con id
     * @return array - Respuesta con formato estándar
     */
    public static function obtenerPreguntaPorId($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        if ($id <= 0) {
            return Util::error_missing_data_description('ID de pregunta no válido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT * FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . "
                  WHERE id = :id";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':id' => $id]);
            $pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pregunta) {
                return Util::error_no_result();
            }

            // Obtener grillas asociadas desde la tabla intermedia
            $qGrillas = "SELECT GROUP_CONCAT(tbl_grilla_id ORDER BY tbl_grilla_id SEPARATOR ',') AS grillas_ids
                        FROM " . $db->getTable('tbl_grilla_x_preguntas') . "
                        WHERE tbl_pregunta_id = :id";

            $stmtGrillas = $pdo->prepare($qGrillas);
            $stmtGrillas->execute([':id' => $id]);
            $grillasData = $stmtGrillas->fetch(PDO::FETCH_ASSOC);

            // Agregar las grillas asociadas al objeto pregunta
            $pregunta['grillas_ids'] = $grillasData['grillas_ids'] ?? '';

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => $pregunta
                )
            );

        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al obtener pregunta: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Guarda o actualiza una pregunta
     * @param array $rqst - Request con datos de la pregunta
     * @return array - Respuesta con formato estándar
     */
    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tipo_pregunta = isset($rqst['tipo_pregunta']) ? $rqst['tipo_pregunta'] : '';
        $texto_pregunta = isset($rqst['texto_pregunta']) ? trim($rqst['texto_pregunta']) : '';
        $orden = isset($rqst['orden']) ? intval($rqst['orden']) : 1;

        // 1. Extracción de los nuevos campos dinámicos
        $codigo_pregunta = isset($rqst['codigo_pregunta']) ? trim($rqst['codigo_pregunta']) : ''; // Aseguramos trim()
        $pregunta_padre_id = !empty($rqst['pregunta_padre_id']) ? intval($rqst['pregunta_padre_id']) : NULL;

        // IMPORTANTE: opciones_respuesta debe ser NULL si está vacío (no cadena vacía)
        // Esto evita el error de CHECK constraint que valida JSON válido
        $opciones_respuesta = isset($rqst['opciones_respuesta']) && trim($rqst['opciones_respuesta']) !== ''
            ? trim($rqst['opciones_respuesta'])
            : NULL;

        // Los campos booleanos deben manejarse como 1/0 o booleanos
        $habilita_subpreguntas = isset($rqst['habilita_subpreguntas']) ? (bool)$rqst['habilita_subpreguntas'] : FALSE;

        // IMPORTANTE: condicion_habilitacion debe ser NULL si está vacío (no cadena vacía)
        $condicion_habilitacion = isset($rqst['condicion_habilitacion']) && trim($rqst['condicion_habilitacion']) !== ''
            ? trim($rqst['condicion_habilitacion'])
            : NULL;

        $requiere_todas_si = isset($rqst['requiere_todas_si']) ? (bool)$rqst['requiere_todas_si'] : FALSE;
        $habilitado = isset($rqst['habilitado']) ? (bool)$rqst['habilitado'] : TRUE;

        // Validaciones iniciales
        if (empty($tipo_pregunta) || !in_array($tipo_pregunta, ['pregunta', 'subpregunta'])) {
            return Util::error_missing_data_description('Tipo de pregunta no válido');
        }

        if (empty($texto_pregunta)) {
            return Util::error_missing_data_description('El texto de la pregunta es requerido');
        }

        if (empty($codigo_pregunta)) {
            return Util::error_missing_data_description('El código único de la pregunta es requerido');
        }

        $tbl_usuario_id = SessionData::getUserId();
        if (!$tbl_usuario_id) {
            return Util::error_general('No se pudo obtener el ID del usuario');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            // 2. Validamos que el código de pregunta sea único al crear una nueva pregunta
            if ($id === 0) {
                $qCheck = "SELECT COUNT(id) FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " WHERE codigo_pregunta = :codigo_pregunta";
                $stmtCheck = $pdo->prepare($qCheck);
                $stmtCheck->execute([':codigo_pregunta' => $codigo_pregunta]);
                $count = $stmtCheck->fetchColumn();

                if ($count > 0) {
                    // Si el código ya existe, abortar y devolver un error amigable.
                    $pdo->rollBack();
                    return Util::error_missing_data_description("El código de pregunta '{$codigo_pregunta}' ya está en uso. Por favor, utilice un código diferente.");
                }
            }

            // 2.1 Obtener las grillas asociadas que se van a guardar
            $grillas_asociadas = isset($rqst['grillas_asociadas']) ? json_decode($rqst['grillas_asociadas'], true) : [];

            // 2.2 Validar que el ORDEN sea único según el tipo de pregunta Y LAS GRILLAS
            if ($tipo_pregunta === 'pregunta') {
                // PREGUNTAS PRINCIPALES: El orden debe ser único DENTRO DE CADA GRILLA

                if (!empty($grillas_asociadas) && is_array($grillas_asociadas)) {
                    // Caso 1: Pregunta asociada a grillas específicas
                    // Verificar que el orden no esté en uso en NINGUNA de las grillas seleccionadas
                    $placeholders = implode(',', array_fill(0, count($grillas_asociadas), '?'));

                    $qCheckOrden = "SELECT COUNT(DISTINCT p.id)
                                   FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p
                                   INNER JOIN " . $db->getTable('tbl_grilla_x_preguntas') . " gxp ON p.id = gxp.tbl_pregunta_id
                                   WHERE p.tipo_pregunta = 'pregunta'
                                     AND p.orden = ?
                                     AND p.id != ?
                                     AND gxp.tbl_grilla_id IN ($placeholders)";

                    $stmtCheckOrden = $pdo->prepare($qCheckOrden);
                    $params = array_merge([$orden, $id], $grillas_asociadas);
                    $stmtCheckOrden->execute($params);
                    $countOrden = $stmtCheckOrden->fetchColumn();

                    if ($countOrden > 0) {
                        $pdo->rollBack();
                        return Util::error_missing_data_description("Ya existe otra pregunta con el orden '{$orden}' en una de las grillas seleccionadas. Por favor, utilice un orden diferente.");
                    }
                } else {
                    // Caso 2: Pregunta GLOBAL (sin grillas asociadas)
                    // El orden debe ser único entre TODAS las preguntas globales
                    $qCheckOrden = "SELECT COUNT(p.id)
                                   FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p
                                   LEFT JOIN " . $db->getTable('tbl_grilla_x_preguntas') . " gxp ON p.id = gxp.tbl_pregunta_id
                                   WHERE p.tipo_pregunta = 'pregunta'
                                     AND p.orden = :orden
                                     AND p.id != :id
                                     AND gxp.tbl_pregunta_id IS NULL";

                    $stmtCheckOrden = $pdo->prepare($qCheckOrden);
                    $stmtCheckOrden->execute([':orden' => $orden, ':id' => $id]);
                    $countOrden = $stmtCheckOrden->fetchColumn();

                    if ($countOrden > 0) {
                        $pdo->rollBack();
                        return Util::error_missing_data_description("Ya existe otra pregunta global con el orden '{$orden}'. Por favor, utilice un orden diferente.");
                    }
                }

            } elseif ($tipo_pregunta === 'subpregunta' && !empty($pregunta_padre_id)) {
                // SUBPREGUNTAS: El orden debe ser único dentro de las subpreguntas del mismo padre
                $qCheckOrden = "SELECT COUNT(id) FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . "
                               WHERE tipo_pregunta = 'subpregunta'
                                 AND pregunta_padre_id = :pregunta_padre_id
                                 AND orden = :orden
                                 AND id != :id";
                $stmtCheckOrden = $pdo->prepare($qCheckOrden);
                $stmtCheckOrden->execute([
                    ':pregunta_padre_id' => $pregunta_padre_id,
                    ':orden' => $orden,
                    ':id' => $id
                ]);
                $countOrden = $stmtCheckOrden->fetchColumn();

                if ($countOrden > 0) {
                    $pdo->rollBack();
                    return Util::error_missing_data_description("Ya existe otra subpregunta con el orden '{$orden}' para esta pregunta principal. Por favor, utilice un orden diferente.");
                }
            }

            // 3. Parámetros comunes para ambos casos (INSERT/UPDATE)
            $params = [
                ':tipo_pregunta' => $tipo_pregunta,
                ':texto_pregunta' => $texto_pregunta,
                ':codigo_pregunta' => $codigo_pregunta,
                ':orden' => $orden,
                // Usamos un ternario para asegurar que el NULL de PHP se mapee correctamente si es necesario
                ':pregunta_padre_id' => $pregunta_padre_id !== NULL ? $pregunta_padre_id : NULL, 
                ':opciones_respuesta' => $opciones_respuesta,
                ':requiere_todas_si' => (int)$requiere_todas_si,
                ':habilita_subpreguntas' => (int)$habilita_subpreguntas,
                ':condicion_habilitacion' => $condicion_habilitacion,
                ':habilitado' => (int)$habilitado,
                ':tbl_usuario_id' => $tbl_usuario_id,
            ];

            if ($id > 0) {
                // ACTUALIZAR
                $q = "UPDATE " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . "
                      SET tipo_pregunta = :tipo_pregunta,
                          texto_pregunta = :texto_pregunta,
                          codigo_pregunta = :codigo_pregunta,
                          orden = :orden,
                          pregunta_padre_id = :pregunta_padre_id,
                          opciones_respuesta = :opciones_respuesta,
                          requiere_todas_si = :requiere_todas_si,
                          habilita_subpreguntas = :habilita_subpreguntas,
                          condicion_habilitacion = :condicion_habilitacion,
                          habilitado = :habilitado,
                          tbl_usuario_id = :tbl_usuario_id 
                      WHERE id = :id";
                
                $params[':id'] = $id;

                $stmt = $pdo->prepare($q);
                $stmt->execute($params);

                $mensaje = 'Pregunta actualizada correctamente';
                $preguntaId = $id;

            } else {
                // INSERTAR - Ahora con todos los campos NOT NULL
                $q = "INSERT INTO " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . "
                      (tipo_pregunta, texto_pregunta, codigo_pregunta, orden, pregunta_padre_id, 
                       opciones_respuesta, requiere_todas_si, habilita_subpreguntas, 
                       condicion_habilitacion, habilitado, tbl_usuario_id, dtcreate)
                      VALUES (:tipo_pregunta, :texto_pregunta, :codigo_pregunta, :orden, :pregunta_padre_id, 
                              :opciones_respuesta, :requiere_todas_si, :habilita_subpreguntas, 
                              :condicion_habilitacion, :habilitado, :tbl_usuario_id, NOW())";

                $stmt = $pdo->prepare($q);
                unset($params[':id']);
                $stmt->execute($params);

                $preguntaId = $pdo->lastInsertId();
                $mensaje = 'Pregunta creada correctamente';
            }

            // ===========================================================================
            // GESTIONAR GRILLAS ASOCIADAS (tabla intermedia tbl_grilla_x_preguntas)
            // ===========================================================================

            // Primero, eliminar todas las asociaciones existentes de esta pregunta
            $qDeleteAsoc = "DELETE FROM " . $db->getTable('tbl_grilla_x_preguntas') . " WHERE tbl_pregunta_id = :pregunta_id";
            $stmtDeleteAsoc = $pdo->prepare($qDeleteAsoc);
            $stmtDeleteAsoc->execute([':pregunta_id' => $preguntaId]);

            // Lógica diferente según el tipo de pregunta
            if ($tipo_pregunta === 'subpregunta' && !empty($pregunta_padre_id)) {
                // ==================================================================
                // SUBPREGUNTAS: Heredan automáticamente las grillas de su pregunta padre
                // ==================================================================
                $qPadreGrillas = "SELECT tbl_grilla_id FROM " . $db->getTable('tbl_grilla_x_preguntas') . "
                                 WHERE tbl_pregunta_id = :pregunta_padre_id";
                $stmtPadreGrillas = $pdo->prepare($qPadreGrillas);
                $stmtPadreGrillas->execute([':pregunta_padre_id' => $pregunta_padre_id]);
                $grillasDelPadre = $stmtPadreGrillas->fetchAll(PDO::FETCH_COLUMN);

                // Insertar las mismas asociaciones que tiene el padre
                if (!empty($grillasDelPadre)) {
                    $qInsertAsoc = "INSERT INTO " . $db->getTable('tbl_grilla_x_preguntas') . "
                                   (tbl_grilla_id, tbl_pregunta_id, dtcreate)
                                   VALUES (:grilla_id, :pregunta_id, NOW())";
                    $stmtInsertAsoc = $pdo->prepare($qInsertAsoc);

                    foreach ($grillasDelPadre as $grilla_id) {
                        $stmtInsertAsoc->execute([
                            ':grilla_id' => intval($grilla_id),
                            ':pregunta_id' => $preguntaId
                        ]);
                    }
                }
                // Si el padre no tiene asociaciones, la subpregunta tampoco (es global)

            } else {
                // ==================================================================
                // PREGUNTAS PRINCIPALES: Usan las grillas seleccionadas en el formulario
                // ==================================================================
                $grillas_asociadas = isset($rqst['grillas_asociadas']) ? json_decode($rqst['grillas_asociadas'], true) : [];

                // Insertar las nuevas asociaciones si existen
                if (!empty($grillas_asociadas) && is_array($grillas_asociadas)) {
                    $qInsertAsoc = "INSERT INTO " . $db->getTable('tbl_grilla_x_preguntas') . "
                                   (tbl_grilla_id, tbl_pregunta_id, dtcreate)
                                   VALUES (:grilla_id, :pregunta_id, NOW())";
                    $stmtInsertAsoc = $pdo->prepare($qInsertAsoc);

                    foreach ($grillas_asociadas as $grilla_id) {
                        if (!empty($grilla_id) && is_numeric($grilla_id)) {
                            $stmtInsertAsoc->execute([
                                ':grilla_id' => intval($grilla_id),
                                ':pregunta_id' => $preguntaId
                            ]);
                        }
                    }
                }
                // Si $grillas_asociadas está vacío, la pregunta es GLOBAL (sin asociaciones)
            }

            $pdo->commit();

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => array(
                        'msg' => $mensaje,
                        'id' => $preguntaId
                    )
                )
            );

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            print_r($e); // Descomentar para debug
            $arrjson = Util::error_general('Error al guardar pregunta: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }
    /**
     * Elimina una pregunta
     * @param array $rqst - Request con id
     * @return array - Respuesta con formato estándar
     */
    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        if ($id <= 0) {
            return Util::error_missing_data_description('ID de pregunta no válido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            $q = "DELETE FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . "
                  WHERE id = :id";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':id' => $id]);

            $pdo->commit();

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => array(
                        'msg' => 'Pregunta eliminada correctamente'
                    )
                )
            );

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $arrjson = Util::error_general('Error al eliminar pregunta: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Obtiene todas las preguntas (principales y subpreguntas) para administración
     * @param array $rqst - Request vacío o con filtros opcionales
     * @return array - Respuesta con formato estándar
     */
    public static function getAll($rqst = [])
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    id,
                    tipo_pregunta,
                    texto_pregunta,
                    orden,
                    tbl_usuario_id,
                    dtcreate
                  FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . "
                  ORDER BY tipo_pregunta ASC, orden ASC";

            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => $arr ? $arr : []
                )
            );

        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al obtener preguntas');
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Valida si una grilla tiene preguntas configuradas
     * @param array $rqst - Request con grilla_id
     * @return array - Respuesta con formato estándar con tiene_preguntas (bool)
     */
    public static function validarPreguntasGrilla($rqst)
    {
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;

        if ($grilla_id <= 0) {
            return Util::error_missing_data_description('ID de grilla no válido');
        }

        try {
            $preguntasResponse = self::obtenerPreguntasConSubpreguntas(['grilla_id' => $grilla_id]);

            $tiene_preguntas = false;
            if ($preguntasResponse['output']['valid']) {
                $preguntasData = $preguntasResponse['output']['response']['preguntas'];
                $tiene_preguntas = !empty($preguntasData);
            }

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => array(
                        'tiene_preguntas' => $tiene_preguntas
                    )
                )
            );

        } catch (Exception $e) {
            $arrjson = Util::error_general('Error al validar preguntas de grilla: ' . $e->getMessage());
        }

        return $arrjson;
    }
}
