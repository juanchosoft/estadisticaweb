<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define("DS", DIRECTORY_SEPARATOR);

// Incluir solo las clases necesarias con rutas absolutas
if (!class_exists('DbConection')) {
    require_once __DIR__ . '/../classes/DbConection.php';
}
if (!class_exists('Util')) {
    require_once __DIR__ . '/../classes/Util.php';
}
require_once __DIR__ . '/../classes/Colombia.php';
require_once __DIR__ . '/../db/colores.php';
require_once __DIR__ . '/../classes/Sondeo.php';
require_once __DIR__ . '/../classes/RespuestaCuestionario.php';

$colombia = Colombia::getInformacionMapaColombia(NULL);
$responseColombia = $colombia['output']['response'];

/* PALETA DE COLORES DISPONIBLES */
$paletaColores = [
    "#1f77b4", // azul
    "#ff7f0e", // naranja
    "#2ca02c", // verde
    "#d62728", // rojo
    "#9467bd", // morado
    "#8c564b", // marrón
    "#e377c2", // rosa
    "#7f7f7f", // gris
    "#bcbd22", // amarillo verdoso
    "#17becf"  // cian
];

/* OBTENER COLORES DINÁMICOS BASADOS EN EL MODO ACTIVO (sondeo o cuestionario) */
$coloresCandidatos = [];
$ganadoresDepartamentos = [];

// Obtener la configuración para saber el modo activo
$db = new DbConection();
$pdo = $db->openConect();

$qConfig = "SELECT opcion_activa_web FROM " . $db->getTable('tbl_configuracion') . " ORDER BY id DESC LIMIT 1";
$stmtConfig = $pdo->prepare($qConfig);
$stmtConfig->execute();
$config = $stmtConfig->fetch(PDO::FETCH_ASSOC);
$opcionActiva = $config['opcion_activa_web'] ?? 'sondeo';

if ($opcionActiva === 'cuestionario') {
    // ============ MODO CUESTIONARIO ============
    // Obtener opciones del cuestionario activo para asignar colores
    $opciones = RespuestaCuestionario::obtenerOpcionesCuestionarioActivo();

    foreach ($opciones as $index => $opc) {
        // Asegurar que la clave sea integer para consistencia
        $coloresCandidatos[intval($opc['id'])] = $paletaColores[$index % count($paletaColores)];
    }

    $db->closeConect();

    // Obtener ganadores por departamento para cuestionario
    $ganadoresDepartamentos = RespuestaCuestionario::ganadorPorTodosLosDepartamentosCuestionario();

} else {
    // ============ MODO SONDEO (default) ============
    $qSondeo = "SELECT id FROM " . $db->getTable('tbl_sondeo') . " WHERE habilitado = 'si' ORDER BY dtcreate DESC LIMIT 1";
    $stmt = $pdo->prepare($qSondeo);
    $stmt->execute();
    $sondeoActivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sondeoActivo) {
        $idSondeo = $sondeoActivo['id'];

        // Verificar si tiene candidatos
        $qCheck = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " WHERE tbl_sondeo_id = :id";
        $stmt = $pdo->prepare($qCheck);
        $stmt->execute([":id" => $idSondeo]);
        $tieneCandidatos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

        if ($tieneCandidatos) {
            // Obtener IDs de candidatos
            $qCand = "SELECT p.id FROM " . $db->getTable('tbl_participantes') . " p
                      INNER JOIN " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " sp ON sp.tbl_participante_id = p.id
                      WHERE sp.tbl_sondeo_id = :id ORDER BY p.id";
            $stmt = $pdo->prepare($qCand);
            $stmt->execute([":id" => $idSondeo]);
            $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($candidatos as $index => $cand) {
                // Asegurar que la clave sea integer para consistencia
                $coloresCandidatos[intval($cand['id'])] = $paletaColores[$index % count($paletaColores)];
            }
        } else {
            // Obtener IDs de opciones
            $qOpc = "SELECT id FROM " . $db->getTable('tbl_sondeo_x_opciones') . " WHERE tbl_sondeo_id = :id ORDER BY id";
            $stmt = $pdo->prepare($qOpc);
            $stmt->execute([":id" => $idSondeo]);
            $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($opciones as $index => $opc) {
                // Asegurar que la clave sea integer para consistencia
                $coloresCandidatos[intval($opc['id'])] = $paletaColores[$index % count($paletaColores)];
            }
        }
    }

    $db->closeConect();

    // Obtener ganadores por departamento para sondeo
    $ganadoresDepartamentos = Sondeo::ganadorPorTodosLosDepartamentos();
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>MAPA DE COLOMBIA</title>
  <base href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
</head>

<style>
    .mapaClick {
        transition: all 0.2s ease-in-out;
        transform-origin: center;
    }
    .mapaClick:hover {
        stroke: rgb(0, 238, 255);
        stroke-width: 2px;
        filter: drop-shadow(0 0 4px rgba(0, 0, 0, 0.7));
        cursor: pointer;
    }
</style>

<body>

<svg version="1.1" xmlns="http://www.w3.org/2000/svg" 
     viewBox="60 60 1000 1000" xml:space="preserve">
<style type="text/css">
	.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st1{font-family:'Roboto';}
	.st2{font-size:13.3251px;}
	.st3{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st4{font-size:13.0068px;}
	.st5{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st6{font-size:13.8544px;}
	.st7{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st8{font-size:13.0653px;}
	.st9{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st10{font-size:13.5816px;}
	.st11{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st12{font-size:13.099px;}
	.st13{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st14{font-size:13px;}
	.st15{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st16{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st17{font-size:13.0165px;}
	.st18{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st19{font-size:13.606px;}
	.st20{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st21{font-size:13.3833px;}
	.st22{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st23{font-size:13.2547px;}
	.st24{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st25{font-size:13px;}
	.st26{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st27{font-size:13.2946px;}
	.st28{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st29{font-size:13.8654px;}
	.st30{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st31{font-size:13.8335px;}
	.st32{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st33{font-size:13.3265px;}
	.st34{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st35{font-size:13.267px;}
	.st36{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st37{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st38{font-size:13.8655px;}
	.st39{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st40{font-size:13.9371px;}
	.st41{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st42{font-size:13.2352px;}
	.st43{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st44{font-size:13.2124px;}
	.st45{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st46{font-size:13.2484px;}
	.st47{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st48{font-size:13.7194px;}
	.st49{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st50{font-size:13.1011px;}
	.st51{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st52{font-size:13.2107px;}
	.st53{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-width:0.75;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st54{font-size:13.4449px;}
	.st55{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st56{font-size:13.0099px;}
	.st57{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-width:0.75;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st58{font-size:13.6172px;}
	.st59{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-width:0.75;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st60{font-size:13.7288px;}
	.st61{fill-rule:evenodd;clip-rule:evenodd;fill:#9b9b9b;stroke:#FFFFFF;stroke-linecap:square;stroke-linejoin:bevel;stroke-miterlimit:10;}
	.st62{font-size:13.4172px;}
</style>
<defs>
    <pattern id="rayasAzules" patternUnits="userSpaceOnUse" width="10" height="10" patternTransform="rotate(45)">
        <rect width="10" height="10" fill="#e8f0fe"></rect>
        <line x1="0" y1="5" x2="10" y2="5" stroke="#4285f4" stroke-width="3"></line>
    </pattern>
</defs>

<?php foreach ($responseColombia as $value): ?>

    <?php if ($value['habilitado'] !== "si") continue; ?>

    <?php
        $codigoDep = $value['codigo_departamento'];
        $infoGanador = $ganadoresDepartamentos[$codigoDep] ?? null;

        // ------------------------------
        // REGLA PARA COLORES
        // ------------------------------
        if (!$infoGanador) {
            // SIN DATOS
            $colorFill = "#d9d9d9";
        } elseif (!empty($infoGanador["empate"]) && $infoGanador["empate"] === true) {
            // EMPATE - usar URL absoluta para compatibilidad cross-browser
            $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $colorFill = "url(" . htmlspecialchars($currentUrl) . "#rayasAzules)";
        } else {
            // GANADOR CLARO - asegurar que el ID sea integer
            $ganadorId = isset($infoGanador["ganador"]) ? intval($infoGanador["ganador"]) : null;
            $colorFill = ($ganadorId !== null && isset($coloresCandidatos[$ganadorId]))
                ? $coloresCandidatos[$ganadorId]
                : "#d9d9d9";
        }
    ?>

    <g id="dep-<?php echo $codigoDep; ?>">
        <path
			d="<?php echo $value['d']; ?>"
			class="mapaClick"
			data-codigo="<?php echo $codigoDep; ?>"
			data-nombre="<?php echo $value['departamento']; ?>"
			fill="<?php echo $colorFill; ?>"
			fill-rule="evenodd"
			clip-rule="evenodd"
			stroke="#363636"
			stroke-linecap="square"
			stroke-linejoin="bevel"
			stroke-miterlimit="10"
			stroke-width="0.75"
		/>
	</g>

<?php endforeach; ?>

<g id="labels-departamentos" style="pointer-events:none">

<?php foreach ($responseColombia as $value): ?>
<?php if ($value['habilitado'] !== 'si') continue; ?>

    <?php 
        $nombre = $value['departamento'];
        $transform = $value['transform'];
    ?>

<?php 
if ($nombre === 'Valle Del Cauca'): ?>

<text transform="matrix(1 0 0 1 342 585)">
    <tspan x="0" y="0" class="st1 st29">Valle de</tspan>
    <tspan x="0" y="15" class="st1 st29">el Cauca</tspan>
</text>

<?php continue; endif; ?>

<text transform="matrix(<?php echo $transform; ?>)" 
      class="st1 st6">
      <?php echo $nombre; ?>
</text>

<?php endforeach; ?>

</g>


</svg>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Colores dinámicos generados desde PHP
window.ColoresCandidatosDinamicos = <?php echo json_encode($coloresCandidatos); ?>;

$(document).on("click", ".mapaClick", function(e) {

    e.preventDefault();
    e.stopPropagation();

    const codigo = $(this).data("codigo");
    console.log("CLICK EN MAPA:", codigo);

    if (!codigo) {
        console.error("SIN data-codigo");
        return;
    }

    MapaSondeo.manejarClickMapa(e);
});
</script>

</body>
</html>
