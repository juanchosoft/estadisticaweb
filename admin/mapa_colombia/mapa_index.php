<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define("DS", DIRECTORY_SEPARATOR);

require_once __DIR__ . '/../classes/Colombia.php';
require_once __DIR__ . '/../db/colores.php';
require_once './admin/include/generic_classes.php';
require_once __DIR__ . '/../classes/Sondeo.php';

$colombia = Colombia::getInformacionMapaColombia(NULL);
$responseColombia = $colombia['output']['response'];

/* COLORES POR CANDIDATO */
$coloresCandidatos = [
    33 => "#2ca02c", // verde
    32 => "#1f77b4", // azul
    34 => "#d62728", // rojo
    35 => "#ff7f0e"  // naranja
];

$ganadoresDepartamentos = Sondeo::ganadorPorTodosLosDepartamentos();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>MAPA DE COLOMBIA</title>
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
    <pattern id="rayasAzules" patternUnits="userSpaceOnUse" width="12" height="12">
        <!-- Fondo azul claro -->
        <rect width="12" height="12" fill="#dce8ff"></rect>

        <!-- Rayas inclinadas azules -->
        <path d="M0 12 L12 0" stroke="#0057ff" stroke-width="2"></path>
        <path d="M-6 6 L6 -6" stroke="#0057ff" stroke-width="2"></path>
        <path d="M6 18 L18 6" stroke="#0057ff" stroke-width="2"></path>
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

        } elseif ($infoGanador["empate"] === true) {

            // EMPATE
$colorFill = "url(#rayasAzules)";

        } else {

            // GANADOR CLARO
            $colorFill = $coloresCandidatos[$infoGanador["ganador"]] ?? "#d9d9d9";
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
			stroke="#363636ff"
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
