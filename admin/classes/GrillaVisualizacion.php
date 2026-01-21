<?php

class GrillaVisualizacion
{
    public static function obtenerResultados($rqst = [])
    {
        $grilla_id = isset($rqst['grilla_id']) ? intval($rqst['grilla_id']) : 0;

        if ($grilla_id <= 0) {
            return [
                'valid' => false,
                'message' => 'ID de grilla inválido.'
            ];
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $tblGrilla     = $db->getTable('tbl_grilla');
            $tblPreg       = $db->getTable('tbl_grilla_preguntas');
            $tblSubpreg    = $db->getTable('tbl_grilla_subpreguntas');
            $tblCand       = $db->getTable('tbl_candidatos');
            $tblRes        = $db->getTable('tbl_grilla_respuestas');

            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    grilla,
                    descripcion_grilla,
                    tipo_inferenciales,
                    aplica_cargos_publicos,
                    codigo_departamento,
                    codigo_municipio
                FROM $tblGrilla
                WHERE id = :id
            ");
            $stmt->execute([':id' => $grilla_id]);
            $grilla = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$grilla) {
                return ['valid' => false, 'message' => 'Grilla no encontrada.'];
            }

            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    codigo_pregunta,
                    texto_pregunta,
                    orden
                FROM $tblPreg
                WHERE tbl_grilla_id = :id
                ORDER BY orden ASC
            ");
            $stmt->execute([':id' => $grilla_id]);
            $preguntas_principales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    codigo_pregunta,
                    texto_pregunta,
                    orden
                FROM $tblSubpreg
                WHERE tbl_grilla_id = :id
                ORDER BY orden ASC
            ");
            $stmt->execute([':id' => $grilla_id]);
            $subpreguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("
                SELECT 
                    id,
                    nombre_completo AS nombre,
                    foto
                FROM $tblCand
                WHERE habilitado = 'si'
                ORDER BY nombre ASC
            ");
            $stmt->execute();
            $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("
                SELECT 
                    tbl_candidato_id AS candidato_id,
                    codigo_pregunta,
                    valor,
                    COUNT(*) AS total
                FROM $tblRes
                WHERE tbl_grilla_id = :id
                GROUP BY tbl_candidato_id, codigo_pregunta, valor
            ");
            $stmt->execute([':id' => $grilla_id]);
            $resFilas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultados_preguntas = [];
            $resultados_subpreguntas = [];
            $total_votantes = 0;

            foreach ($resFilas as $r) {
                $cod = strtoupper($r['codigo_pregunta']);

                if (!isset($resultados_preguntas[$cod])) {
                    $resultados_preguntas[$cod] = [];
                }
                $resultados_preguntas[$cod][] = $r;

                if (!isset($resultados_subpreguntas[$cod])) {
                    $resultados_subpreguntas[$cod] = [];
                }
                $resultados_subpreguntas[$cod][] = $r;

                $total_votantes += $r['total'];
            }

            $totales = [];
            foreach ($preguntas_principales as $p) {
                $codigo = strtoupper($p['codigo_pregunta']);
                $totales[$codigo] = 0;

                if (isset($resultados_preguntas[$codigo])) {
                    foreach ($resultados_preguntas[$codigo] as $r) {
                        $totales[$codigo] += intval($r['total']);
                    }
                }
            }

            $total_aprobaciones = 0;
            if (isset($resultados_preguntas['APROBACION'])) {
                foreach ($resultados_preguntas['APROBACION'] as $r) {
                    $total_aprobaciones += intval($r['total']);
                }
            }

            return [
                'valid' => true,
                'grilla' => $grilla,
                'candidatos' => $candidatos,
                'preguntas_principales' => $preguntas_principales,
                'subpreguntas' => $subpreguntas,
                'resultados_preguntas' => $resultados_preguntas,
                'resultados_subpreguntas' => $resultados_subpreguntas,
                'totales' => $totales,
                'total_aprobaciones' => $total_aprobaciones,
                'total_votantes' => $total_votantes
            ];

        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => 'Error en la consulta.'
            ];
        } finally {
            $db->closeConect();
        }
    }
}
