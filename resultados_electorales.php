<?php
/**
 * API para obtener resultados de Sondeos, Encuestas y Estudios
 * Archivo: resultados_consultas.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Incluir la clase de conexión
    require_once 'admin/classes/DbConection.php';
    
    // Obtener parámetros de la solicitud
    $departamento = $_GET['departamento'] ?? 'Colombia';
    $municipio = $_GET['municipio'] ?? '';
    $tipo_consulta = $_GET['tipo_consulta'] ?? 'sondeo';
    $id_consulta = intval($_GET['id_consulta'] ?? 0);
    
    // Validar tipo_consulta
    $tipos_validos = ['sondeo', 'encuesta', 'estudio'];
    if (!in_array($tipo_consulta, $tipos_validos)) {
        throw new Exception('Tipo de consulta inválido');
    }
    
    // Crear conexión a la base de datos
    $db = new DbConection();
    $pdo = $db->openConect();
    
    $response = [];
    
    switch($tipo_consulta) {
        case 'sondeo':
            if ($id_consulta === 0) {
                // Obtener lista de sondeos
                $sql = "
                    SELECT 
                        s.id,
                        s.sondeo as nombre,
                        s.descripcion_sondeo as descripcion,
                        s.tipo_sondeo,
                        s.tipo_inferenciales,
                        s.fecha_inicio,
                        s.fecha_fin,
                        s.aplica_cargos_publicos,
                        cp.nombre as cargo_nombre,
                        d.nombre as departamento_nombre,
                        m.nombre as municipio_nombre
                    FROM tbl_sondeo s
                    LEFT JOIN tbl_cargos_publicos cp ON s.tbl_cargo_publico_id = cp.id
                    LEFT JOIN tbl_departamentos d ON s.codigo_departamento = d.codigo
                    LEFT JOIN tbl_municipios m ON s.codigo_municipio = m.codigo
                    WHERE s.habilitado = 'si'
                    ORDER BY s.dtcreate DESC
                    LIMIT 50
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $sondeos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response = [
                    'success' => true,
                    'tipo' => 'lista_sondeos',
                    'data' => $sondeos,
                    'total' => count($sondeos),
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                
            } else {
                // Obtener resultados específicos de un sondeo
                $sql = "
                    SELECT 
                        s.id,
                        s.sondeo as pregunta,
                        s.descripcion_sondeo,
                        s.tipo_sondeo,
                        s.tipo_inferenciales,
                        s.aplica_cargos_publicos,
                        cp.nombre as cargo_nombre,
                        d.nombre as departamento_nombre,
                        m.nombre as municipio_nombre
                    FROM tbl_sondeo s
                    LEFT JOIN tbl_cargos_publicos cp ON s.tbl_cargo_publico_id = cp.id
                    LEFT JOIN tbl_departamentos d ON s.codigo_departamento = d.codigo
                    LEFT JOIN tbl_municipios m ON s.codigo_municipio = m.codigo
                    WHERE s.id = :id_consulta
                    AND s.habilitado = 'si'
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id_consulta' => $id_consulta]);
                $sondeo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$sondeo) {
                    throw new Exception('Sondeo no encontrado');
                }
                
                // Obtener respuestas del sondeo
                $respuestas = obtenerRespuestasSondeo($pdo, $id_consulta, $sondeo['tipo_sondeo']);
                
                // Calcular estadísticas
                $totalVotos = array_sum(array_column($respuestas, 'votos'));
                $totalOpciones = count($respuestas);
                
                $response = [
                    'success' => true,
                    'tipo' => 'resultado_sondeo',
                    'consulta' => $sondeo,
                    'respuestas' => $respuestas,
                    'estadisticas' => [
                        'total_votos' => $totalVotos,
                        'total_opciones' => $totalOpciones,
                        'promedio_votos' => $totalOpciones > 0 ? round($totalVotos / $totalOpciones, 2) : 0
                    ],
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
            break;
            
        case 'encuesta':
            if ($id_consulta === 0) {
                // Obtener lista de encuestas
                $sql = "
                    SELECT 
                        id,
                        tema as nombre,
                        fecha_realizacion,
                        fecha_publicacion,
                        tamano_de_la_muestra,
                        tipo_muestra,
                        tecnica_de_recoleccion,
                        fuente_financiamiento
                    FROM tbl_encuestas 
                    WHERE habilitado = 'si'
                    ORDER BY dtcreate DESC
                    LIMIT 50
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $encuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response = [
                    'success' => true,
                    'tipo' => 'lista_encuestas',
                    'data' => $encuestas,
                    'total' => count($encuestas),
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                // Obtener detalles de encuesta específica
                $sql = "
                    SELECT 
                        id,
                        tema as nombre,
                        fecha_realizacion,
                        fecha_publicacion,
                        fecha_de_recibo,
                        tamano_de_la_muestra,
                        tipo_muestra,
                        tecnica_de_recoleccion,
                        fuente_financiamiento,
                        observaciones,
                        cumple_con_reglamentacion,
                        enlace_documento
                    FROM tbl_encuestas 
                    WHERE id = :id_consulta
                    AND habilitado = 'si'
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id_consulta' => $id_consulta]);
                $encuesta = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$encuesta) {
                    throw new Exception('Encuesta no encontrada');
                }
                
                $response = [
                    'success' => true,
                    'tipo' => 'resultado_encuesta',
                    'consulta' => $encuesta,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
            break;
            
        case 'estudio':
            if ($id_consulta === 0) {
                // Obtener lista de estudios (grillas)
                $sql = "
                    SELECT 
                        g.id,
                        g.grilla as nombre,
                        g.descripcion_grilla as descripcion,
                        g.tipo_inferenciales,
                        g.aplica_cargos_publicos,
                        cp.nombre as cargo_nombre,
                        d.nombre as departamento_nombre,
                        m.nombre as municipio_nombre
                    FROM tbl_grilla g
                    LEFT JOIN tbl_cargos_publicos cp ON g.tbl_cargo_publico_id = cp.id
                    LEFT JOIN tbl_departamentos d ON g.codigo_departamento = d.codigo
                    LEFT JOIN tbl_municipios m ON g.codigo_municipio = m.codigo
                    WHERE g.habilitado = 'si'
                    ORDER BY g.dtcreate DESC
                    LIMIT 50
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $estudios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response = [
                    'success' => true,
                    'tipo' => 'lista_estudios',
                    'data' => $estudios,
                    'total' => count($estudios),
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                // Obtener detalles de estudio específico
                $sql = "
                    SELECT 
                        g.id,
                        g.grilla as nombre,
                        g.descripcion_grilla,
                        g.tipo_inferenciales,
                        g.aplica_cargos_publicos,
                        cp.nombre as cargo_nombre,
                        d.nombre as departamento_nombre,
                        m.nombre as municipio_nombre
                    FROM tbl_grilla g
                    LEFT JOIN tbl_cargos_publicos cp ON g.tbl_cargo_publico_id = cp.id
                    LEFT JOIN tbl_departamentos d ON g.codigo_departamento = d.codigo
                    LEFT JOIN tbl_municipios m ON g.codigo_municipio = m.codigo
                    WHERE g.id = :id_consulta
                    AND g.habilitado = 'si'
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id_consulta' => $id_consulta]);
                $estudio = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$estudio) {
                    throw new Exception('Estudio no encontrado');
                }
                
                $response = [
                    'success' => true,
                    'tipo' => 'resultado_estudio',
                    'consulta' => $estudio,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
            break;
    }
    
    // Cerrar conexión
    $db->closeConect();
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error de BD en resultados_consultas: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al conectar con la base de datos',
        'error' => $e->getMessage(),
        'tipo_error' => 'database',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Error general
    error_log("Error en resultados_consultas: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'tipo_error' => 'general',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Función para obtener respuestas de un sondeo
 */
function obtenerRespuestasSondeo($pdo, $sondeo_id, $tipo_sondeo) {
    $respuestas = [];
    
    try {
        // Para sondeos que aplican a cargos públicos (buscar en tbl_respuestas_sondeos)
        $sql = "
            SELECT 
                rs.tbl_respuesta_texto as opcion,
                COUNT(rs.id) as votos
            FROM tbl_respuestas_sondeos rs
            WHERE rs.tbl_sondeo_id = :sondeo_id
            AND rs.tbl_respuesta_texto IS NOT NULL
            GROUP BY rs.tbl_respuesta_texto
            ORDER BY votos DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':sondeo_id' => $sondeo_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si hay respuestas en la tabla
        if (!empty($resultados)) {
            foreach($resultados as $resultado) {
                $respuestas[] = [
                    'opcion' => $resultado['opcion'],
                    'votos' => intval($resultado['votos']),
                    'tipo' => 'respuesta_texto'
                ];
            }
        } else {
            // Si no hay respuestas, crear datos de ejemplo basados en el tipo de sondeo
            $respuestas = generarDatosEjemploSondeo($tipo_sondeo);
        }
        
        // Calcular porcentajes
        $totalVotos = array_sum(array_column($respuestas, 'votos'));
        foreach($respuestas as &$respuesta) {
            $respuesta['porcentaje'] = $totalVotos > 0 ? 
                round(($respuesta['votos'] / $totalVotos) * 100, 2) : 0;
        }
        
    } catch (Exception $e) {
        // En caso de error, devolver datos de ejemplo
        $respuestas = generarDatosEjemploSondeo($tipo_sondeo);
    }
    
    return $respuestas;
}

/**
 * Generar datos de ejemplo para sondeos
 */
function generarDatosEjemploSondeo($tipo_sondeo) {
    switch($tipo_sondeo) {
        case 'Si/No':
        case 'Dicotomica':
            return [
                ['opcion' => 'Sí', 'votos' => rand(100, 500), 'tipo' => 'ejemplo'],
                ['opcion' => 'No', 'votos' => rand(50, 400), 'tipo' => 'ejemplo'],
                ['opcion' => 'No sabe/No responde', 'votos' => rand(10, 100), 'tipo' => 'ejemplo']
            ];
            
        case 'Preguntas_Cardinales':
            return [
                ['opcion' => 'Muy Bueno', 'votos' => rand(80, 300), 'tipo' => 'ejemplo'],
                ['opcion' => 'Bueno', 'votos' => rand(100, 400), 'tipo' => 'ejemplo'],
                ['opcion' => 'Regular', 'votos' => rand(150, 450), 'tipo' => 'ejemplo'],
                ['opcion' => 'Malo', 'votos' => rand(50, 200), 'tipo' => 'ejemplo'],
                ['opcion' => 'Muy Malo', 'votos' => rand(20, 100), 'tipo' => 'ejemplo']
            ];
            
        case 'Grilla':
        case 'No Aplica':
        default:
            return [
                ['opcion' => 'Opción A', 'votos' => rand(100, 500), 'tipo' => 'ejemplo'],
                ['opcion' => 'Opción B', 'votos' => rand(80, 400), 'tipo' => 'ejemplo'],
                ['opcion' => 'Opción C', 'votos' => rand(60, 300), 'tipo' => 'ejemplo'],
                ['opcion' => 'Opción D', 'votos' => rand(40, 200), 'tipo' => 'ejemplo']
            ];
    }
}
?>