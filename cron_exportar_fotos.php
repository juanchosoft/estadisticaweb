<!-- METODO PARA SINCRONIZAR LAS FOTOS DE LOS VOTANTES GUARDADAS EN BLOB HACIA ARCHIVOS FÍSICOS -->
<?php
require_once __DIR__ . "/admin/include/generic_classes.php";

$db = new DbConection();
$pdo = $db->openConect();
$pdo->query("USE " . $db->getDbName());
// BUSCAR FOTOS NUEVAS
$q = "SELECT id, foto, foto_blob 
      FROM tbl_participantes 
      WHERE foto_blob IS NOT NULL AND foto_exportada = 0";

$stmt = $pdo->query($q);
$pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pendientes)) {
    exit;
}

$rutaBase = __DIR__ . "/uploads/fotos/";

if (!is_dir($rutaBase)) {
    mkdir($rutaBase, 0777, true);
}

foreach ($pendientes as $p) {
    $archivo = $rutaBase . $p['foto'];

    // Exportar
    file_put_contents($archivo, $p['foto_blob']);

    // Marcar como exportada
    $upd = $pdo->prepare("UPDATE tbl_participantes SET foto_exportada = 1 WHERE id = :id");
    $upd->execute([":id" => $p['id']]);

    echo " Exportada: {$p['foto']}<br>";
}

echo "FIN.";
