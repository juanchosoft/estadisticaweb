<?php
// obtener_resultados.php - VERSIÓN CON CONSULTA REAL
header('Content-Type: application/json');

try {
    // Incluir la clase de conexión
    require_once 'admin/classes/DbConection.php';
    
    // Obtener parámetros
    $departamento = $_GET['departamento'] ?? 'Colombia';
    $cargo_id = intval($_GET['cargo_id'] ?? 1);
    
    // Crear conexión
    $db = new DbConection();
    $pdo = $db->openConect();
    
    // Consulta MEJORADA para obtener candidatos reales
    $sql = "
        SELECT 
            p.id,
            p.nombre_completo,
            p.numero_candidato,
            p.puntos as votos,
            p.foto,
            c.nombre as cargo_nombre,
            'Partido por definir' as partido
        FROM tbl_participantes p
        INNER JOIN tbl_cargos_publicos c ON p.tbl_cargo_publico_id = c.id
        WHERE p.tbl_cargo_publico_id = ?
        AND p.habilitado = 'si'
        ORDER BY p.puntos DESC
        LIMIT 10
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cargo_id]);
    $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener nombre real del cargo
    $stmt_cargo = $pdo->prepare("SELECT nombre FROM tbl_cargos_publicos WHERE id = ?");
    $stmt_cargo->execute([$cargo_id]);
    $cargo_real = $stmt_cargo->fetch(PDO::FETCH_ASSOC);
    
    // Cerrar conexión
    $db->closeConect();
    
    // Enviar respuesta
    echo json_encode([
        'success' => true,
        'candidatos' => $candidatos,
        'cargo' => $cargo_real['nombre'] ?? 'Cargo Público',
        'departamento' => $departamento,
        'total' => count($candidatos),
        'origen' => 'base_datos_real'
    ]);
    
} catch (Exception $e) {
    // Datos de ejemplo en caso de error
    error_log("Error en obtener_resultados: " . $e->getMessage());
    
    $cargo_id = intval($_GET['cargo_id'] ?? 1);
    $departamento = $_GET['departamento'] ?? 'Colombia';
    
    $candidatosEjemplo = [
        1 => [
            ['nombre_completo' => 'ALVARO URIBE VELEZ', 'votos' => 212223, 'partido' => 'Pacto Histórico', 'numero_candidato' => '1'],
            ['nombre_completo' => 'RODOLFO HERNÁNDEZ', 'votos' => 174614, 'partido' => 'Liga de Gobernantes', 'numero_candidato' => '2']
        ],
        6 => [
            ['nombre_completo' => 'PEDRO ALCALDE CAUCASIA', 'votos' => 85000, 'partido' => 'Partido Local', 'numero_candidato' => '1']
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'candidatos' => $candidatosEjemplo[$cargo_id] ?? [
            ['nombre_completo' => 'ANDRES JULIAN RENDON', 'votos' => 100000, 'partido' => 'Partido Demo', 'numero_candidato' => '1']
        ],
        'cargo' => 'Cargo Público',
        'departamento' => $departamento,
        'debug' => 'Modo demo - Error BD: ' . $e->getMessage()
    ]);
}
?>