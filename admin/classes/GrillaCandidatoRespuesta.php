<?php
/**
 * Clase para gestionar las respuestas del estudio de votaciones de la grilla de candidatos
 */
class GrillaCandidatoRespuesta
{
    public function __construct() {}

    /**
     * Verifica si un usuario ya votó hoy en una grilla específica - NUEVA ESTRUCTURA
     * @param array $rqst - Request con grilla_id
     * @return array - Respuesta indicando si ya votó
     */
    public static function verificarVotoDuplicado($rqst)
    {
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;
        $tbl_votante_id = isset($rqst['votante_id']) ? intval($rqst['votante_id']) : 0;

        if ($grilla_id <= 0) {
            return Util::error_missing_data_description('ID de grilla no válido');
        }

        if ($tbl_votante_id <= 0) {
            return Util::error_missing_data_description('ID de votante no válido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Buscar si el votante ya votó HOY en esta grilla
            $q = "SELECT COUNT(*) as total
                  FROM " . $db->getTable('tbl_grilla_sesion_votacion') . "
                  WHERE tbl_grilla_id = :grilla_id
                    AND tbl_votante_id = :votante_id
                    AND DATE(dtcreate) = CURDATE()";

            $stmt = $pdo->prepare($q);
            $stmt->execute([
                ':grilla_id' => $grilla_id,
                ':votante_id' => $tbl_votante_id
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $yaVoto = $result['total'] > 0;

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => array(
                        'ya_voto' => $yaVoto,
                        'msg' => $yaVoto
                            ? 'Este votante ya emitió su voto para esta grilla el día de hoy. No puede votar nuevamente.'
                            : 'Puede proceder a votar.'
                    )
                )
            );

        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al verificar voto duplicado: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Guarda las respuestas del estudio de votaciones - NUEVA ESTRUCTURA DINÁMICA
     * @param array $rqst - Request con grilla_id, respuestas y subpreguntas (JSON strings)
     * @return array - Respuesta con formato estándar
     *
     * Estructura esperada:
     * - respuestas: {"candidato_id": {"codigo_pregunta": "valor"}}
     * - subpreguntas: {"codigo_pregunta": candidato_id}
     */
    public static function guardarRespuestas($rqst)
    {
        // Validar parámetros requeridos
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;
        $tbl_votante_id = SessionData::getUserId();
        $respuestasJson = isset($rqst['respuestas']) ? $rqst['respuestas'] : '';
        $subpreguntasJson = isset($rqst['subpreguntas']) ? $rqst['subpreguntas'] : '{}';

        if ($grilla_id <= 0) {
            return Util::error_missing_data_description('ID de grilla no válido');
        }

        if ($tbl_votante_id <= 0) {
            return Util::error_missing_data_description('ID de votante no válido');
        }

        if (empty($respuestasJson)) {
            return Util::error_missing_data_description('No se recibieron respuestas');
        }

        // VALIDACIÓN: Verificar si el votante ya votó hoy en esta grilla
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $qCheck = "SELECT COUNT(*) as total
                       FROM " . $db->getTable('tbl_grilla_sesion_votacion') . "
                       WHERE tbl_grilla_id = :grilla_id
                         AND tbl_votante_id = :votante_id
                         AND DATE(dtcreate) = CURDATE()";

            $stmtCheck = $pdo->prepare($qCheck);
            $stmtCheck->execute([
                ':grilla_id' => $grilla_id,
                ':votante_id' => $tbl_votante_id
            ]);
            $resultCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($resultCheck['total'] > 0) {
                $db->closeConect();
                return array(
                    'output' => array(
                        'valid' => false,
                        'response' => array(
                            'content' => 'Este votante ya ha emitido su voto para esta grilla el día de hoy. No puede votar nuevamente.'
                        )
                    )
                );
            }

            $db->closeConect();
        } catch (PDOException $e) {
            $db->closeConect();
            return Util::error_general('Error al verificar voto duplicado: ' . $e->getMessage());
        }

        // Decodificar JSON de respuestas y subpreguntas
        $respuestas = json_decode($respuestasJson, true);
        if ($respuestas === null && json_last_error() !== JSON_ERROR_NONE) {
            return Util::error_general('Error al decodificar las respuestas: ' . json_last_error_msg());
        }

        $subpreguntas = json_decode($subpreguntasJson, true);
        if ($subpreguntas === null && json_last_error() !== JSON_ERROR_NONE) {
            return Util::error_general('Error al decodificar las subpreguntas: ' . json_last_error_msg());
        }

        if (empty($respuestas)) {
            return Util::error_missing_data_description('Las respuestas están vacías');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            // Verificar que la grilla existe
            $qGrilla = "SELECT id FROM " . $db->getTable('tbl_grilla') . " WHERE id = :grilla_id";
            $stmtGrilla = $pdo->prepare($qGrilla);
            $stmtGrilla->execute([':grilla_id' => $grilla_id]);
            if (!$stmtGrilla->fetch()) {
                $pdo->rollBack();
                $db->closeConect();
                return Util::error_general('La grilla especificada no existe');
            }

            // ===========================================================================
            // Obtener mapa de código_pregunta => id de tbl_preguntas_sub_preguntas_grilla
            // Usando tabla intermedia para preguntas asociadas a grillas
            // ===========================================================================
            $qPreguntas = "SELECT DISTINCT p.id, p.codigo_pregunta, p.tipo_pregunta, p.texto_pregunta
                          FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p
                          LEFT JOIN " . $db->getTable('tbl_grilla_x_preguntas') . " gxp ON p.id = gxp.tbl_pregunta_id
                          WHERE (gxp.tbl_grilla_id = :grilla_id OR gxp.tbl_grilla_id IS NULL)
                            AND p.habilitado = TRUE
                          ORDER BY p.orden";
            $stmtPreguntas = $pdo->prepare($qPreguntas);
            $stmtPreguntas->execute([':grilla_id' => $grilla_id]);
            $preguntasMap = [];
            $subpreguntasRequeridas = [];

            while ($row = $stmtPreguntas->fetch(PDO::FETCH_ASSOC)) {
                $preguntasMap[$row['codigo_pregunta']] = [
                    'id' => $row['id'],
                    'tipo' => $row['tipo_pregunta'],
                    'texto' => $row['texto_pregunta']
                ];

                // Guardar las subpreguntas que son obligatorias
                if ($row['tipo_pregunta'] === 'subpregunta') {
                    $subpreguntasRequeridas[] = [
                        'codigo' => $row['codigo_pregunta'],
                        'texto' => $row['texto_pregunta']
                    ];
                }
            }

            // ===========================================================================
            // VALIDACIÓN: Calcular candidatos aprobados y validar solo subpreguntas ACTIVAS
            // ===========================================================================

            // Paso 1: Calcular cuántos candidatos aprobaron todas las preguntas principales
            $candidatosAprobados = 0;

            // Iterar sobre cada candidato y sus respuestas
            foreach ($respuestas as $candidatoId => $respuestasCandidato) {
                // Verificar si todas las respuestas del candidato a preguntas principales son positivas
                $todasPositivas = true;

                foreach ($respuestasCandidato as $codigoPregunta => $valorRespuesta) {
                    // Solo considerar preguntas principales
                    if (isset($preguntasMap[$codigoPregunta]) && $preguntasMap[$codigoPregunta]['tipo'] === 'pregunta') {
                        // Si alguna respuesta no es positiva, el candidato no aprueba
                        if ($valorRespuesta !== 'si' && $valorRespuesta !== 'favorable') {
                            $todasPositivas = false;
                            break;
                        }
                    }
                }

                // Si todas las preguntas principales fueron respondidas positivamente, contar este candidato
                if ($todasPositivas) {
                    $candidatosAprobados++;
                }
            }

            // Paso 2: Validar solo las primeras N subpreguntas (N = candidatos aprobados)
            if (!empty($subpreguntasRequeridas) && $candidatosAprobados > 0) {
                $subpreguntasFaltantes = [];

                // Solo validar las primeras N subpreguntas
                for ($i = 0; $i < min($candidatosAprobados, count($subpreguntasRequeridas)); $i++) {
                    $subpreguntaReq = $subpreguntasRequeridas[$i];
                    $codigo = $subpreguntaReq['codigo'];

                    // Verificar si la subpregunta no existe en el array o tiene valor null/vacío
                    if (!isset($subpreguntas[$codigo]) || $subpreguntas[$codigo] === null || $subpreguntas[$codigo] === '') {
                        $subpreguntasFaltantes[] = $subpreguntaReq['texto'];
                    }
                }

                if (!empty($subpreguntasFaltantes)) {
                    $pdo->rollBack();
                    $db->closeConect();
                    return array(
                        'output' => array(
                            'valid' => false,
                            'response' => array(
                                'content' => 'Debe responder las subpreguntas activas del estudio (' . $candidatosAprobados . ' candidato(s) aprobado(s) = ' . $candidatosAprobados . ' subpregunta(s) requerida(s))'
                            )
                        )
                    );
                }
            }

            // ===========================================================================
            // NUEVA ESTRUCTURA: Crear sesión de votación
            // ===========================================================================
            $qSesion = "INSERT INTO " . $db->getTable('tbl_grilla_sesion_votacion') . "
                        (tbl_grilla_id, tbl_votante_id, dtcreate)
                        VALUES (:grilla_id, :votante_id, NOW())";
            $stmtSesion = $pdo->prepare($qSesion);
            $stmtSesion->execute([
                ':grilla_id' => $grilla_id,
                ':votante_id' => $tbl_votante_id
            ]);
            $sesion_id = $pdo->lastInsertId();

            if (!$sesion_id) {
                $pdo->rollBack();
                $db->closeConect();
                return Util::error_general('Error al crear la sesión de votación');
            }

            // ===========================================================================
            // Guardar respuestas de preguntas principales (por candidato)
            // ===========================================================================
            $qRespuesta = "INSERT INTO " . $db->getTable('tbl_grilla_respuestas') . "
                          (tbl_sesion_votacion_id, tbl_pregunta_id, tbl_participante_id, respuesta, dtcreate)
                          VALUES (:sesion_id, :pregunta_id, :participante_id, :respuesta, NOW())";
            $stmtRespuesta = $pdo->prepare($qRespuesta);

            $totalGuardadas = 0;

            // Iterar sobre cada candidato y sus respuestas
            foreach ($respuestas as $candidatoId => $respuestaCandidato) {
                // Validar que el candidato existe
                $qCandidato = "SELECT id FROM " . $db->getTable('tbl_participantes') . " WHERE id = :candidato_id";
                $stmtCandidato = $pdo->prepare($qCandidato);
                $stmtCandidato->execute([':candidato_id' => $candidatoId]);

                if (!$stmtCandidato->fetch()) {
                    continue; // Candidato no existe, saltar
                }

                // Iterar sobre cada pregunta respondida para este candidato
                foreach ($respuestaCandidato as $codigoPregunta => $valorRespuesta) {
                    if (!isset($preguntasMap[$codigoPregunta])) {
                        continue; // Pregunta no configurada, saltar
                    }

                    if ($preguntasMap[$codigoPregunta]['tipo'] !== 'pregunta') {
                        continue; // Solo preguntas principales aquí
                    }

                    $pregunta_id = $preguntasMap[$codigoPregunta]['id'];

                    // Guardar respuesta
                    $stmtRespuesta->execute([
                        ':sesion_id' => $sesion_id,
                        ':pregunta_id' => $pregunta_id,
                        ':participante_id' => $candidatoId,
                        ':respuesta' => $valorRespuesta ?: 'no_aplica'
                    ]);

                    $totalGuardadas++;
                }
            }

            // ===========================================================================
            // Guardar respuestas de subpreguntas (candidato seleccionado)
            // ===========================================================================
            foreach ($subpreguntas as $codigoPregunta => $candidatoSeleccionado) {
                if (!isset($preguntasMap[$codigoPregunta])) {
                    continue; // Subpregunta no configurada, saltar
                }

                if ($preguntasMap[$codigoPregunta]['tipo'] !== 'subpregunta') {
                    continue; // Solo subpreguntas aquí
                }

                $pregunta_id = $preguntasMap[$codigoPregunta]['id'];

                // Validar que se haya seleccionado un candidato
                if (empty($candidatoSeleccionado)) {
                    continue; // No se seleccionó candidato, saltar
                }

                // Guardar respuesta: tbl_participante_id contiene el candidato seleccionado, respuesta = 'favorable'
                $stmtRespuesta->execute([
                    ':sesion_id' => $sesion_id,
                    ':pregunta_id' => $pregunta_id,
                    ':participante_id' => $candidatoSeleccionado,
                    ':respuesta' => 'favorable'
                ]);

                $totalGuardadas++;
            }

            // Confirmar transacción
            $pdo->commit();

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => array(
                        'msg' => "Se guardaron exitosamente {$totalGuardadas} respuestas",
                        'total_guardadas' => $totalGuardadas,
                        'sesion_id' => $sesion_id,
                        'grilla_id' => $grilla_id
                    )
                )
            );

        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $arrjson = Util::error_general('Error al guardar las respuestas: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Obtiene las respuestas de un usuario para una grilla específica
     * @param array $rqst - Request con grilla_id
     * @return array - Respuestas del usuario
     */
    public static function obtenerRespuestas($rqst)
    {
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;
        $tbl_usuario_id = SessionData::getUserId();

        if ($grilla_id <= 0) {
            return Util::error_missing_data_description('ID de grilla no válido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT * FROM " . $db->getTable('tbl_grilla_candidato_respuestas') . "
                  WHERE tbl_grilla_id = :grilla_id AND tbl_usuario_id = :usuario_id";
            $stmt = $pdo->prepare($q);
            $stmt->execute([
                ':grilla_id' => $grilla_id,
                ':usuario_id' => $tbl_usuario_id
            ]);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));

        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al obtener las respuestas');
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Obtiene resultados en tiempo real del estudio de votaciones - ESTRUCTURA COMPLETAMENTE DINÁMICA
     * @param array $rqst - Request con grilla_id
     * @return array - Resultados completos con estadísticas por candidato y subpreguntas
     *
     * Lee desde:
     * - tbl_grilla_sesion_votacion (sesiones de votación)
     * - tbl_grilla_respuestas (respuestas individuales)
     * - tbl_preguntas_sub_preguntas_grilla (configuración de preguntas)
     *
     * NOTA: Este método es 100% dinámico y se adapta a las preguntas configuradas en BD
     */
    public static function obtenerResultadosEnTiempoReal($rqst)
    {
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;

        if ($grilla_id <= 0) {
            return Util::error_missing_data_description('ID de grilla no válido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // ========================================================================
            // PASO 1: Obtener configuración de preguntas y subpreguntas
            // Usando tabla intermedia para preguntas asociadas a grillas
            // ========================================================================
            $qPreguntas = "SELECT DISTINCT p.id, p.codigo_pregunta, p.texto_pregunta, p.tipo_pregunta, p.orden
                          FROM " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p
                          LEFT JOIN " . $db->getTable('tbl_grilla_x_preguntas') . " gxp ON p.id = gxp.tbl_pregunta_id
                          WHERE (gxp.tbl_grilla_id = :grilla_id OR gxp.tbl_grilla_id IS NULL)
                            AND p.habilitado = TRUE
                          ORDER BY p.orden";
            $stmtPreguntas = $pdo->prepare($qPreguntas);
            $stmtPreguntas->execute([':grilla_id' => $grilla_id]);
            $preguntasConfig = $stmtPreguntas->fetchAll(PDO::FETCH_ASSOC);

            $preguntasPrincipales = [];
            $subpreguntas = [];

            foreach ($preguntasConfig as $pregunta) {
                if ($pregunta['tipo_pregunta'] === 'pregunta') {
                    $preguntasPrincipales[] = $pregunta;
                } else {
                    $subpreguntas[] = $pregunta;
                }
            }

            // ========================================================================
            // PASO 2: Obtener total de votos (sesiones de votación únicas)
            // ========================================================================
            $qTotalVotos = "SELECT COUNT(DISTINCT tbl_votante_id) as total_votantes
                           FROM " . $db->getTable('tbl_grilla_sesion_votacion') . "
                           WHERE tbl_grilla_id = :grilla_id";
            $stmtTotalVotos = $pdo->prepare($qTotalVotos);
            $stmtTotalVotos->execute([':grilla_id' => $grilla_id]);
            $totalVotantes = $stmtTotalVotos->fetch(PDO::FETCH_ASSOC)['total_votantes'];

            // ========================================================================
            // PASO 3: Obtener listado de candidatos de la grilla
            // ========================================================================
            $qCandidatos = "SELECT p.id, p.nombre_completo, p.foto
                           FROM " . $db->getTable('tbl_participantes') . " p
                           INNER JOIN " . $db->getTable('tbl_grilla_x_tbl_participantes') . " gxp
                             ON p.id = gxp.tbl_participante_id
                           WHERE gxp.tbl_grilla_id = :grilla_id
                           ORDER BY p.nombre_completo";
            $stmtCandidatos = $pdo->prepare($qCandidatos);
            $stmtCandidatos->execute([':grilla_id' => $grilla_id]);
            $candidatos = $stmtCandidatos->fetchAll(PDO::FETCH_ASSOC);

            // ========================================================================
            // PASO 4: Por cada candidato, obtener sus estadísticas dinámicamente
            // ========================================================================
            $resultadosCandidatos = [];

            foreach ($candidatos as $candidato) {
                $candidato_id = $candidato['id'];
                $estadisticas = [
                    'tbl_participante_id' => $candidato_id,
                    'nombre_completo' => $candidato['nombre_completo'],
                    'foto' => $candidato['foto'],
                    'total_votos' => 0,
                    // Campos de compatibilidad (se llenarán dinámicamente)
                    'conoce_si' => 0,
                    'conoce_no' => 0,
                    'conoce_si_pct' => 0,
                    'imagen_favorable' => 0,
                    'imagen_desfavorable' => 0,
                    'imagen_no_aplica' => 0,
                    'imagen_favorable_pct' => 0,
                    'votaria_si' => 0,
                    'votaria_no' => 0,
                    'votaria_no_aplica' => 0,
                    'votaria_si_pct' => 0,
                    'total_aprobaciones' => 0
                ];

                // Por cada pregunta principal, calcular estadísticas
                foreach ($preguntasPrincipales as $pregunta) {
                    $pregunta_id = $pregunta['id'];
                    $codigo = $pregunta['codigo_pregunta'];

                    // Obtener distribución de respuestas para esta pregunta y candidato
                    $qRespuestas = "SELECT
                                       gr.respuesta,
                                       COUNT(DISTINCT sv.tbl_votante_id) as cantidad
                                   FROM " . $db->getTable('tbl_grilla_respuestas') . " gr
                                   INNER JOIN " . $db->getTable('tbl_grilla_sesion_votacion') . " sv
                                     ON gr.tbl_sesion_votacion_id = sv.id
                                   WHERE sv.tbl_grilla_id = :grilla_id
                                     AND gr.tbl_pregunta_id = :pregunta_id
                                     AND gr.tbl_participante_id = :candidato_id
                                   GROUP BY gr.respuesta";

                    $stmtResp = $pdo->prepare($qRespuestas);
                    $stmtResp->execute([
                        ':grilla_id' => $grilla_id,
                        ':pregunta_id' => $pregunta_id,
                        ':candidato_id' => $candidato_id
                    ]);
                    $respuestas = $stmtResp->fetchAll(PDO::FETCH_ASSOC);

                    // Inicializar contadores
                    $totales = [
                        'si' => 0,
                        'no' => 0,
                        'favorable' => 0,
                        'desfavorable' => 0,
                        'no_aplica' => 0
                    ];

                    // Sumar respuestas
                    foreach ($respuestas as $resp) {
                        $valor = $resp['respuesta'];
                        $cantidad = (int)$resp['cantidad'];

                        if (isset($totales[$valor])) {
                            $totales[$valor] = $cantidad;
                        }
                    }

                    // Guardar estadísticas según el código de pregunta
                    // Para compatibilidad con frontend, mapear a los campos esperados
                    if ($codigo === 'conoce') {
                        $estadisticas['conoce_si'] = $totales['si'];
                        $estadisticas['conoce_no'] = $totales['no'];
                        $estadisticas['conoce_si_pct'] = $totalVotantes > 0
                            ? round(($totales['si'] * 100.0) / $totalVotantes, 2)
                            : 0;
                    } elseif ($codigo === 'imagen') {
                        $estadisticas['imagen_favorable'] = $totales['favorable'];
                        $estadisticas['imagen_desfavorable'] = $totales['desfavorable'];
                        $estadisticas['imagen_no_aplica'] = $totales['no_aplica'];
                        $estadisticas['imagen_favorable_pct'] = $totalVotantes > 0
                            ? round(($totales['favorable'] * 100.0) / $totalVotantes, 2)
                            : 0;
                    } elseif ($codigo === 'votaria') {
                        $estadisticas['votaria_si'] = $totales['si'];
                        $estadisticas['votaria_no'] = $totales['no'];
                        $estadisticas['votaria_no_aplica'] = $totales['no_aplica'];
                        $estadisticas['votaria_si_pct'] = $totalVotantes > 0
                            ? round(($totales['si'] * 100.0) / $totalVotantes, 2)
                            : 0;
                    }

                }

                // Calcular TOTAL DE VOTOS: SUMA de todas las respuestas afirmativas
                // Total Votos = (Conoce SI) + (Imagen Favorable) + (Votaría SI)
                $estadisticas['total_votos'] = $estadisticas['conoce_si']
                                             + $estadisticas['imagen_favorable']
                                             + $estadisticas['votaria_si'];

                // Calcular APROBACIONES: Votantes donde TODAS sus respuestas son positivas para este candidato
                // Un votante "aprueba" solo si respondió SÍ o FAVORABLE a TODAS las preguntas PRINCIPALES
                $totalPreguntasPrincipales = count($preguntasPrincipales);

                $qAprobaciones = "SELECT COUNT(DISTINCT sv.tbl_votante_id) as total
                                 FROM " . $db->getTable('tbl_grilla_sesion_votacion') . " sv
                                 WHERE sv.tbl_grilla_id = :grilla_id
                                   AND (
                                       SELECT COUNT(DISTINCT gr1.tbl_pregunta_id)
                                       FROM " . $db->getTable('tbl_grilla_respuestas') . " gr1
                                       INNER JOIN " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p1
                                         ON gr1.tbl_pregunta_id = p1.id
                                       WHERE gr1.tbl_sesion_votacion_id = sv.id
                                         AND gr1.tbl_participante_id = :candidato_id
                                         AND gr1.respuesta IN ('si', 'favorable')
                                         AND p1.tipo_pregunta = 'pregunta'
                                   ) = :total_preguntas";

                $stmtAprob = $pdo->prepare($qAprobaciones);
                $stmtAprob->execute([
                    ':grilla_id' => $grilla_id,
                    ':candidato_id' => $candidato_id,
                    ':total_preguntas' => $totalPreguntasPrincipales
                ]);
                $estadisticas['total_aprobaciones'] = (int)$stmtAprob->fetch(PDO::FETCH_ASSOC)['total'];

                $resultadosCandidatos[] = $estadisticas;
            }

            // Ordenar candidatos por total de aprobaciones (de mayor a menor)
            usort($resultadosCandidatos, function($a, $b) {
                if ($b['total_aprobaciones'] != $a['total_aprobaciones']) {
                    return $b['total_aprobaciones'] - $a['total_aprobaciones'];
                }
                return $b['total_votos'] - $a['total_votos'];
            });

            // ========================================================================
            // CALCULAR TOTALES POR PREGUNTA
            // ========================================================================
            $totalesPorPregunta = [];

            foreach ($preguntasPrincipales as $pregunta) {
                $codigo = $pregunta['codigo_pregunta'];
                $pregunta_id = $pregunta['id'];

                // Obtener totales para esta pregunta
                $qTotales = "SELECT gr.respuesta, COUNT(DISTINCT sv.tbl_votante_id) as cantidad
                            FROM " . $db->getTable('tbl_grilla_sesion_votacion') . " sv
                            INNER JOIN " . $db->getTable('tbl_grilla_respuestas') . " gr
                              ON sv.id = gr.tbl_sesion_votacion_id
                            WHERE sv.tbl_grilla_id = :grilla_id
                              AND gr.tbl_pregunta_id = :pregunta_id
                            GROUP BY gr.respuesta";

                $stmtTotales = $pdo->prepare($qTotales);
                $stmtTotales->execute([
                    ':grilla_id' => $grilla_id,
                    ':pregunta_id' => $pregunta_id
                ]);
                $totalesData = $stmtTotales->fetchAll(PDO::FETCH_ASSOC);

                $totales = [
                    'si' => 0,
                    'no' => 0,
                    'favorable' => 0,
                    'desfavorable' => 0,
                    'no_aplica' => 0
                ];

                foreach ($totalesData as $row) {
                    $respuesta = $row['respuesta'];
                    $cantidad = (int)$row['cantidad'];
                    if (isset($totales[$respuesta])) {
                        $totales[$respuesta] = $cantidad;
                    }
                }

                // Calcular porcentajes
                $totalesPorPregunta[$codigo] = [
                    'si' => $totales['si'],
                    'no' => $totales['no'],
                    'favorable' => $totales['favorable'],
                    'desfavorable' => $totales['desfavorable'],
                    'no_aplica' => $totales['no_aplica'],
                    'si_pct' => $totalVotantes > 0 ? round(($totales['si'] * 100.0) / $totalVotantes, 2) : 0,
                    'favorable_pct' => $totalVotantes > 0 ? round(($totales['favorable'] * 100.0) / $totalVotantes, 2) : 0
                ];
            }

            // ========================================================================
            // PASO 5: Obtener resultados de SUBPREGUNTAS
            // Las subpreguntas tienen el candidato seleccionado en tbl_participante_id
            // ========================================================================
            $qSubpreguntas = "SELECT
                    p.codigo_pregunta,
                    p.texto_pregunta,
                    gr.tbl_participante_id AS candidato_id,
                    pa.nombre_completo,
                    COUNT(DISTINCT sv.tbl_votante_id) AS votos
                  FROM " . $db->getTable('tbl_grilla_respuestas') . " gr
                  INNER JOIN " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p
                    ON gr.tbl_pregunta_id = p.id
                  INNER JOIN " . $db->getTable('tbl_grilla_sesion_votacion') . " sv
                    ON gr.tbl_sesion_votacion_id = sv.id
                  LEFT JOIN " . $db->getTable('tbl_participantes') . " pa
                    ON gr.tbl_participante_id = pa.id
                  WHERE sv.tbl_grilla_id = :grilla_id
                    AND p.tipo_pregunta = 'subpregunta'
                    AND gr.tbl_participante_id IS NOT NULL
                  GROUP BY p.codigo_pregunta, p.texto_pregunta, gr.tbl_participante_id, pa.nombre_completo
                  ORDER BY p.orden, votos DESC";

            $stmtSubpreguntas = $pdo->prepare($qSubpreguntas);
            $stmtSubpreguntas->execute([':grilla_id' => $grilla_id]);
            $subpreguntasData = $stmtSubpreguntas->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar subpreguntas por código_pregunta
            $votosSubpreguntasFormateados = [];

            foreach ($subpreguntasData as $row) {
                $codigoPregunta = $row['codigo_pregunta'];

                if (!isset($votosSubpreguntasFormateados[$codigoPregunta])) {
                    $votosSubpreguntasFormateados[$codigoPregunta] = [
                        'texto_pregunta' => $row['texto_pregunta'],
                        'resultados' => []
                    ];
                }

                $votosSubpreguntasFormateados[$codigoPregunta]['resultados'][] = [
                    'id' => $row['candidato_id'],
                    'nombre' => $row['nombre_completo'] ?: 'Candidato #' . $row['candidato_id'],
                    'votos' => (int)$row['votos'],
                    'porcentaje' => $totalVotantes > 0 ? round(((int)$row['votos'] * 100.0) / $totalVotantes, 2) : 0
                ];
            }

            // Para compatibilidad con el frontend antiguo
            $votosPA = isset($votosSubpreguntasFormateados['pa']) ? $votosSubpreguntasFormateados['pa']['resultados'] : [];
            $votosPB = isset($votosSubpreguntasFormateados['pb']) ? $votosSubpreguntasFormateados['pb']['resultados'] : [];
            $votosPC = isset($votosSubpreguntasFormateados['pc']) ? $votosSubpreguntasFormateados['pc']['resultados'] : [];

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => array(
                        'total_votantes' => $totalVotantes,
                        'candidatos' => $resultadosCandidatos,
                        'totales_por_pregunta' => $totalesPorPregunta,
                        'preguntas_adicionales' => array(
                            'pa' => array_values($votosPA),
                            'pb' => array_values($votosPB),
                            'pc' => array_values($votosPC)
                        ),
                        'subpreguntas_completas' => $votosSubpreguntasFormateados
                    )
                )
            );

        } catch (PDOException $e) {
            // Imprimir el error para depuración
            print_r($e); 
            $arrjson = Util::error_general('Error al obtener resultados: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Obtiene estadísticas agregadas de las respuestas para una grilla
     * @param array $rqst - Request con grilla_id
     * @return array - Estadísticas
     */
    public static function obtenerEstadisticas($rqst)
    {
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;

        if ($grilla_id <= 0) {
            return Util::error_missing_data_description('ID de grilla no válido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Estadísticas por candidato
            $q = "SELECT
                    gcr.tbl_participante_id,
                    p.nombre_completo,
                    SUM(CASE WHEN gcr.conoce_candidato = 'si' THEN 1 ELSE 0 END) AS total_conocen,
                    SUM(CASE WHEN gcr.conoce_candidato = 'no' THEN 1 ELSE 0 END) AS total_no_conocen,
                    SUM(CASE WHEN gcr.imagen_candidato = 'favorable' THEN 1 ELSE 0 END) AS total_imagen_favorable,
                    SUM(CASE WHEN gcr.imagen_candidato = 'desfavorable' THEN 1 ELSE 0 END) AS total_imagen_desfavorable,
                    SUM(CASE WHEN gcr.votaria_por_candidato = 'si' THEN 1 ELSE 0 END) AS total_votarian,
                    SUM(CASE WHEN gcr.votaria_por_candidato = 'no' THEN 1 ELSE 0 END) AS total_no_votarian,
                    COUNT(*) AS total_respuestas
                  FROM " . $db->getTable('tbl_grilla_candidato_respuestas') . " gcr
                  INNER JOIN " . $db->getTable('tbl_participantes') . " p ON gcr.tbl_participante_id = p.id
                  WHERE gcr.tbl_grilla_id = :grilla_id
                  GROUP BY gcr.tbl_participante_id, p.nombre_completo
                  ORDER BY total_votarian DESC";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':grilla_id' => $grilla_id]);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));

        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al obtener las estadísticas');
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Guarda las preguntas adicionales P(A), P(B), P(C)
     * @param array $rqst - Request con grilla_id, pregunta_pa, pregunta_pb, pregunta_pc
     * @return array - Respuesta con formato estándar
     */
    public static function guardarPreguntasAdicionales($rqst)
    {
        // Validar parámetros requeridos
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;
        $pregunta_pa = isset($rqst['pregunta_pa']) ? intval($rqst['pregunta_pa']) : null;
        $pregunta_pb = isset($rqst['pregunta_pb']) ? intval($rqst['pregunta_pb']) : null;
        $pregunta_pc = isset($rqst['pregunta_pc']) ? intval($rqst['pregunta_pc']) : null;

        if ($grilla_id <= 0) {
            return Util::error_missing_data_description('ID de grilla no válido');
        }

        // Validar que al menos una pregunta tenga respuesta
        if ($pregunta_pa === null && $pregunta_pb === null && $pregunta_pc === null) {
            return Util::error_missing_data_description('Debe responder al menos una pregunta adicional');
        }

        // Obtener ID del usuario de sesión
        $tbl_usuario_id = SessionData::getUserId();
        if (!$tbl_usuario_id) {
            return Util::error_general('No se pudo obtener el ID del usuario de la sesión');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            // Verificar que la tabla existe
            //self::verificarTablaPreguntasAdicionales($pdo, $db);

            // Verificar que la grilla existe
            $qGrilla = "SELECT id FROM " . $db->getTable('tbl_grilla') . " WHERE id = :grilla_id";
            $stmtGrilla = $pdo->prepare($qGrilla);
            $stmtGrilla->execute([':grilla_id' => $grilla_id]);
            if (!$stmtGrilla->fetch()) {
                $pdo->rollBack();
                $db->closeConect();
                return Util::error_general('La grilla especificada no existe');
            }

            // Validar que los candidatos seleccionados existan
            $candidatosValidos = [];
            foreach ([$pregunta_pa, $pregunta_pb, $pregunta_pc] as $candidatoId) {
                if ($candidatoId !== null && $candidatoId > 0) {
                    $qCandidato = "SELECT id FROM " . $db->getTable('tbl_participantes') . " WHERE id = :candidato_id";
                    $stmtCandidato = $pdo->prepare($qCandidato);
                    $stmtCandidato->execute([':candidato_id' => $candidatoId]);
                    if (!$stmtCandidato->fetch()) {
                        $pdo->rollBack();
                        $db->closeConect();
                        return Util::error_general("El candidato con ID {$candidatoId} no existe");
                    }
                    $candidatosValidos[] = $candidatoId;
                }
            }

            // Eliminar respuestas previas del usuario para esta grilla (si existen)
            $qDelete = "DELETE FROM " . $db->getTable('tbl_grilla_preguntas_adicionales') . "
                        WHERE tbl_grilla_id = :grilla_id AND tbl_usuario_id = :usuario_id";
            $stmtDelete = $pdo->prepare($qDelete);
            $stmtDelete->execute([
                ':grilla_id' => $grilla_id,
                ':usuario_id' => $tbl_usuario_id
            ]);

            // Insertar las nuevas respuestas
            $qInsert = "INSERT INTO " . $db->getTable('tbl_grilla_preguntas_adicionales') . "
                        (tbl_grilla_id, tbl_usuario_id, pregunta_pa, pregunta_pb, pregunta_pc, dtcreate)
                        VALUES (:grilla_id, :usuario_id, :pregunta_pa, :pregunta_pb, :pregunta_pc, NOW())";
            $stmtInsert = $pdo->prepare($qInsert);
            $stmtInsert->execute([
                ':grilla_id' => $grilla_id,
                ':usuario_id' => $tbl_usuario_id,
                ':pregunta_pa' => $pregunta_pa,
                ':pregunta_pb' => $pregunta_pb,
                ':pregunta_pc' => $pregunta_pc
            ]);

            // Confirmar transacción
            $pdo->commit();

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => array(
                        'msg' => 'Preguntas adicionales guardadas exitosamente',
                        'grilla_id' => $grilla_id,
                        'pregunta_pa' => $pregunta_pa,
                        'pregunta_pb' => $pregunta_pb,
                        'pregunta_pc' => $pregunta_pc
                    )
                )
            );

        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $arrjson = Util::error_general('Error al guardar las preguntas adicionales: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    /**
     * Verifica si existe la tabla de respuestas, si no la crea
     * @param PDO $pdo - Conexión PDO
     * @param DbConection $db - Instancia de DbConection
     */
    private static function verificarTablaRespuestas($pdo, $db)
    {
        $tableName = $db->getTable('tbl_grilla_candidato_respuestas');

        // Verificar si la tabla existe
        $qCheck = "SHOW TABLES LIKE :table_name";
        $stmtCheck = $pdo->prepare($qCheck);
        $stmtCheck->execute([':table_name' => $tableName]);

        if ($stmtCheck->rowCount() === 0) {
            // La tabla no existe, crearla
            $qCreate = "CREATE TABLE {$tableName} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tbl_grilla_id INT NOT NULL,
                tbl_participante_id INT NOT NULL,
                tbl_usuario_id INT NOT NULL,
                conoce_candidato VARCHAR(20) NOT NULL,
                imagen_candidato VARCHAR(20) NOT NULL,
                votaria_por_candidato VARCHAR(20) NOT NULL,
                dtcreate DATETIME NOT NULL,
                INDEX idx_grilla (tbl_grilla_id),
                INDEX idx_participante (tbl_participante_id),
                INDEX idx_usuario (tbl_usuario_id),
                UNIQUE KEY unique_respuesta (tbl_grilla_id, tbl_participante_id, tbl_usuario_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $pdo->exec($qCreate);
        }
    }

    /**
     * Verifica si existe la tabla de preguntas adicionales, si no la crea
     * @param PDO $pdo - Conexión PDO
     * @param DbConection $db - Instancia de DbConection
     */
    private static function verificarTablaPreguntasAdicionales($pdo, $db)
    {
        $tableName = $db->getTable('tbl_grilla_preguntas_adicionales');

        // Verificar si la tabla existe
        $qCheck = "SHOW TABLES LIKE :table_name";
        $stmtCheck = $pdo->prepare($qCheck);
        $stmtCheck->execute([':table_name' => $tableName]);

        if ($stmtCheck->rowCount() === 0) {
            // La tabla no existe, crearla
            $qCreate = "CREATE TABLE {$tableName} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tbl_grilla_id INT NOT NULL COMMENT 'ID de la grilla',
                tbl_usuario_id INT NOT NULL COMMENT 'ID del usuario que responde',
                pregunta_pa INT NULL COMMENT 'Si las elecciones fueran hoy, ¿por quién votaría? P(A)',
                pregunta_pb INT NULL COMMENT 'Si su candidato P(A) se retira, ¿por quién votaría? P(B)',
                pregunta_pc INT NULL COMMENT 'Si su candidato P(B) se retira, ¿por quién votaría? P(C)',
                dtcreate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                dtupdate DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_grilla (tbl_grilla_id),
                INDEX idx_usuario (tbl_usuario_id),
                INDEX idx_fecha (dtcreate),
                UNIQUE KEY unique_respuesta_adicional (tbl_grilla_id, tbl_usuario_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Preguntas adicionales del estudio de votaciones (P(A), P(B), P(C))'";

            $pdo->exec($qCreate);
        }
    }

    /**
     * Obtiene análisis demográfico de los votantes que respondieron a un candidato
     * @param array $rqst - Request con grilla_id, candidato_id, tipo_respuesta ('positivas', 'conoce', 'imagen', 'votaria')
     * @return array - Datos demográficos agrupados
     */
    public static function obtenerDemografiaVotantes($rqst)
    {
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;
        $candidato_id = isset($rqst['candidato_id']) ? intval($rqst['candidato_id']) : 0;
        $tipo_respuesta = isset($rqst['tipo_respuesta']) ? $rqst['tipo_respuesta'] : 'positivas';

        if ($grilla_id <= 0) {
            return Util::error_missing_data_description('ID de grilla no válido');
        }

        if ($candidato_id <= 0) {
            return Util::error_missing_data_description('ID de candidato no válido');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Construir condición WHERE según el tipo de respuesta solicitado
            $condicionRespuesta = "";

            switch ($tipo_respuesta) {
                case 'conoce':
                    // Solo votantes que respondieron SÍ a "¿Conoce al candidato?"
                    $condicionRespuesta = "AND EXISTS (
                        SELECT 1 FROM " . $db->getTable('tbl_grilla_respuestas') . " gr2
                        INNER JOIN " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p2
                          ON gr2.tbl_pregunta_id = p2.id
                        WHERE gr2.tbl_sesion_votacion_id = sv.id
                          AND gr2.tbl_participante_id = :candidato_id
                          AND p2.codigo_pregunta = 'conoce'
                          AND gr2.respuesta = 'si'
                    )";
                    break;

                case 'imagen':
                    // Solo votantes que respondieron FAVORABLE a "Imagen"
                    $condicionRespuesta = "AND EXISTS (
                        SELECT 1 FROM " . $db->getTable('tbl_grilla_respuestas') . " gr2
                        INNER JOIN " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p2
                          ON gr2.tbl_pregunta_id = p2.id
                        WHERE gr2.tbl_sesion_votacion_id = sv.id
                          AND gr2.tbl_participante_id = :candidato_id
                          AND p2.codigo_pregunta = 'imagen'
                          AND gr2.respuesta = 'favorable'
                    )";
                    break;

                case 'votaria':
                    // Solo votantes que respondieron SÍ a "¿Votaría?"
                    $condicionRespuesta = "AND EXISTS (
                        SELECT 1 FROM " . $db->getTable('tbl_grilla_respuestas') . " gr2
                        INNER JOIN " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p2
                          ON gr2.tbl_pregunta_id = p2.id
                        WHERE gr2.tbl_sesion_votacion_id = sv.id
                          AND gr2.tbl_participante_id = :candidato_id
                          AND p2.codigo_pregunta = 'votaria'
                          AND gr2.respuesta = 'si'
                    )";
                    break;

                case 'positivas':
                default:
                    // Votantes con TODAS las respuestas positivas (aprobaciones)
                    $condicionRespuesta = "AND (
                        SELECT COUNT(DISTINCT gr2.tbl_pregunta_id)
                        FROM " . $db->getTable('tbl_grilla_respuestas') . " gr2
                        INNER JOIN " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " p2
                          ON gr2.tbl_pregunta_id = p2.id
                        WHERE gr2.tbl_sesion_votacion_id = sv.id
                          AND gr2.tbl_participante_id = :candidato_id
                          AND gr2.respuesta IN ('si', 'favorable')
                          AND p2.tipo_pregunta = 'pregunta'
                    ) >= 3";  // Todas las preguntas principales
                    break;
            }

            // Query base para obtener votantes que votaron en esta grilla
            // Obtenemos TODOS los votantes que participaron, independiente de sus respuestas
            $qVotantes = "SELECT DISTINCT
                            v.id,
                            v.ideologia,
                            v.rango_edad,
                            v.nivel_ingresos,
                            v.genero,
                            v.nivel_educacion
                          FROM " . $db->getTable('tbl_grilla_sesion_votacion') . " sv
                          INNER JOIN " . $db->getTable('tbl_votantes') . " v
                            ON sv.tbl_votante_id = v.id
                          WHERE sv.tbl_grilla_id = :grilla_id";

            $stmt = $pdo->prepare($qVotantes);
            $stmt->execute([
                ':grilla_id' => $grilla_id
            ]);
            $votantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar datos por categoría
            $demografia = [
                'total_votantes' => count($votantes),
                'ideologia' => [],
                'rango_edad' => [],
                'nivel_ingresos' => [],
                'genero' => [],
                'nivel_educacion' => []
            ];

            foreach ($votantes as $votante) {
                // Ideología
                $ideologia = $votante['ideologia'] ?: 'sin_definir';
                if (!isset($demografia['ideologia'][$ideologia])) {
                    $demografia['ideologia'][$ideologia] = 0;
                }
                $demografia['ideologia'][$ideologia]++;

                // Rango de edad
                $edad = $votante['rango_edad'] ?: 'no_especificado';
                if (!isset($demografia['rango_edad'][$edad])) {
                    $demografia['rango_edad'][$edad] = 0;
                }
                $demografia['rango_edad'][$edad]++;

                // Nivel de ingresos
                $ingresos = $votante['nivel_ingresos'] ?: 'no_especificado';
                if (!isset($demografia['nivel_ingresos'][$ingresos])) {
                    $demografia['nivel_ingresos'][$ingresos] = 0;
                }
                $demografia['nivel_ingresos'][$ingresos]++;

                // Género
                $genero = $votante['genero'] ?: 'no_especificado';
                if (!isset($demografia['genero'][$genero])) {
                    $demografia['genero'][$genero] = 0;
                }
                $demografia['genero'][$genero]++;

                // Nivel de educación
                $educacion = $votante['nivel_educacion'] ?: 'no_especificado';
                if (!isset($demografia['nivel_educacion'][$educacion])) {
                    $demografia['nivel_educacion'][$educacion] = 0;
                }
                $demografia['nivel_educacion'][$educacion]++;
            }

            // Calcular porcentajes
            $total = $demografia['total_votantes'];
            if ($total > 0) {
                foreach (['ideologia', 'rango_edad', 'nivel_ingresos', 'genero', 'nivel_educacion'] as $categoria) {
                    foreach ($demografia[$categoria] as $valor => $cantidad) {
                        $demografia[$categoria][$valor] = [
                            'cantidad' => $cantidad,
                            'porcentaje' => round(($cantidad / $total) * 100, 2)
                        ];
                    }
                }
            }

            $arrjson = array(
                'output' => array(
                    'valid' => true,
                    'response' => $demografia
                )
            );

        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al obtener demografía: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    public static function getGrillasVotadasPorUsuario($votanteId)
    {
        if ($votanteId <= 0) {
            return [];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT DISTINCT tbl_grilla_id
                  FROM " . $db->getTable('tbl_grilla_sesion_votacion') . "
                  WHERE tbl_votante_id = :votante_id";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':votante_id' => $votanteId]);
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $db->closeConect();
            return $result ? $result : [];
        } catch (PDOException $e) {
            $db->closeConect();
            return [];
        }
    }

    /**
     * Obtiene las respuestas de un votante para una grilla específica
     * @param array $rqst ['grilla_id' => int, 'votante_id' => int]
     * @return array Resultado con las respuestas del votante
     */
    public static function getRespuestasVotante($rqst)
    {
        $grillaId = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;
        $votanteId = isset($rqst['votante_id']) ? intval($rqst['votante_id']) : 0;

        if ($grillaId === 0 || $votanteId === 0) {
            return Util::error_missing_data_description('Faltan parámetros requeridos');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener la sesión de votación
            $qSesion = "SELECT id, dtcreate
                        FROM " . $db->getTable('tbl_grilla_sesion_votacion') . "
                        WHERE tbl_grilla_id = :grilla_id
                        AND tbl_votante_id = :votante_id
                        LIMIT 1";

            $stmtSesion = $pdo->prepare($qSesion);
            $stmtSesion->execute([
                ':grilla_id' => $grillaId,
                ':votante_id' => $votanteId
            ]);

            $sesion = $stmtSesion->fetch(PDO::FETCH_ASSOC);

            if (!$sesion) {
                $db->closeConect();
                return Util::error_general('No se encontraron respuestas para este votante en esta grilla');
            }

            // Obtener las respuestas con información de candidatos y preguntas
            $qRespuestas = "SELECT
                                r.id,
                                r.respuesta,
                                p.nombre_completo as candidato_nombre,
                                p.foto as candidato_foto,
                                pg.texto_pregunta,
                                pg.codigo_pregunta,
                                pg.tipo_pregunta,
                                pg.orden
                            FROM " . $db->getTable('tbl_grilla_respuestas') . " r
                            INNER JOIN " . $db->getTable('tbl_participantes') . " p ON r.tbl_participante_id = p.id
                            INNER JOIN " . $db->getTable('tbl_preguntas_sub_preguntas_grilla') . " pg ON r.tbl_pregunta_id = pg.id
                            WHERE r.tbl_sesion_votacion_id = :sesion_id
                            ORDER BY p.nombre_completo ASC, pg.orden ASC";

            $stmtRespuestas = $pdo->prepare($qRespuestas);
            $stmtRespuestas->execute([':sesion_id' => $sesion['id']]);
            $respuestas = $stmtRespuestas->fetchAll(PDO::FETCH_ASSOC);

            // Separar respuestas por tipo: preguntas principales y subpreguntas
            $respuestasPorCandidato = [];
            $subpreguntasGlobales = [];

            foreach ($respuestas as $respuesta) {
                $tipoPregunta = $respuesta['tipo_pregunta'];

                if ($tipoPregunta === 'subpregunta') {
                    // Las subpreguntas se muestran globalmente, no por candidato
                    $subpreguntasGlobales[] = [
                        'pregunta' => $respuesta['texto_pregunta'],
                        'codigo_pregunta' => $respuesta['codigo_pregunta'],
                        'tipo_pregunta' => $respuesta['tipo_pregunta'],
                        'respuesta' => $respuesta['candidato_nombre'] // El candidato seleccionado
                    ];
                } else {
                    // Preguntas principales se agrupan por candidato
                    $candidatoNombre = $respuesta['candidato_nombre'];

                    if (!isset($respuestasPorCandidato[$candidatoNombre])) {
                        $respuestasPorCandidato[$candidatoNombre] = [
                            'candidato' => $candidatoNombre,
                            'foto' => $respuesta['candidato_foto'],
                            'respuestas' => []
                        ];
                    }

                    $respuestasPorCandidato[$candidatoNombre]['respuestas'][] = [
                        'pregunta' => $respuesta['texto_pregunta'],
                        'codigo_pregunta' => $respuesta['codigo_pregunta'],
                        'tipo_pregunta' => $respuesta['tipo_pregunta'],
                        'respuesta' => $respuesta['respuesta']
                    ];
                }
            }

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => [
                        'fecha_respuesta' => $sesion['dtcreate'],
                        'respuestas' => array_values($respuestasPorCandidato),
                        'subpreguntas' => $subpreguntasGlobales
                    ]
                ]
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general('Error al obtener respuestas: ' . $e->getMessage());
        }
    }
}
