<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define("DS", DIRECTORY_SEPARATOR);
require_once __DIR__ . '/../classes/Colombia.php';
require_once __DIR__ . '/../db/colores.php';
require_once './admin/include/generic_classes.php';
$colombia = Colombia::getInformacionMapaColombia(NULL);
$isvalidColombia = $colombia['output']['valid'];
$responseColombia = $colombia['output']['response'];
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title> MAPA DE COLOMBIA</title>
</head>

  <script>
    $(function() {
      // Activar tooltips para todas las imágenes (por compatibilidad)
      $("img").each(function() {
        $(this).attr("data-bs-toggle", "tooltip");
        $(this).attr("data-bs-placement", "left");
        new bootstrap.Tooltip(this);
      });

      // Click sobre departamentos habilitados
      $(".mapaClick").on("click", function() {
        const url = $(this).data("url");
        if (url) location.href = url;
      });
    });
  </script>
	<script>
  $("img").each(function(index, el) {
    $(this).attr("data-bs-toggle", "tooltip");
    $(this).attr("data-bs-placement", "left");
    tooltip = new bootstrap.Tooltip($(this)[0], {})
  });
  $(".mapaClick").click(function(event) {
    location.href = $(this).data("url");
  });
</script>
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

<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
	 viewBox="60 60 1000 1000"  xml:space="preserve">
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
      <!-- Informacion de Colombia -->
      <?php foreach ($responseColombia as $key => $value) : ?>
        <?php if ($value['habilitado'] === 'si'): ?>

          <?php
          $urlMapaDepartamentoCod = 'dashboard_colombia.php?dep=' . urlencode($value['codigo_departamento']);
          // Definir cuáles departamentos pueden tener onclick
          $departamentosPermitidos = [SessionData::getCodigoDepartamentoSessionVotante()]; // Ejemplo : Santander, Antioquia, Putiumayo, Narino, huila
          $claseOnClick = in_array($value['codigo_departamento'], $departamentosPermitidos) ? 'mapaClick' : '';

					$claseColor = Util::getColorNeutroMapa(); // Color por defecto
					$estiloColor = "";
					if (SessionData::getCodigoDepartamentoSessionVotante() != "") {
						if ($value['codigo_departamento'] == SessionData::getCodigoDepartamentoSessionVotante()) {
							$claseColor = Util::getColorUbicacionActual();
						}
					}
					$estiloColor = "fill: " . $claseColor . ";";
          ?>

	<g id="<?php echo $value['departamento']; ?>">

		<path vector-effect="none"
			class="municipios <?php echo $claseOnClick; ?> <?php echo $value['class']; ?>"
			style="<?php echo $estiloColor; ?>"
			d="<?php echo $value['d']; ?>"
			data-url="<?php echo $urlMapaDepartamentoCod; ?>" />

		<?php if ($value['departamento'] == 'Valle Del Cauca'): ?>
			<text transform="matrix(1 0 0 1 342.466 585.532)">
				<tspan x="0" y="0" class="st1 st29">Valle de</tspan>
				<tspan x="0" y="15.4" class="st1 st29">el cauca</tspan>
			</text>
		<?php endif; ?>

	</g>

	<?php endif; ?>
	<?php endforeach; ?>

	<g id="labels-departamentos" style="pointer-events:none">

	<?php foreach ($responseColombia as $value): ?>
	<?php if ($value['habilitado'] === 'si'): ?>

		<?php
			$nombre = $value['departamento'];
			if ($nombre === 'Valle Del Cauca') {
				continue;
			}

			$transform = $value['transform'];
		?>

		<?php
			if ($nombre === 'Risaralda') {

				$nuevoTransform = "1 0 0 1 375 510";

				echo '
				<text transform="matrix(' . $nuevoTransform . ')" 
					class="st1"
					style="font-size:11px; pointer-events:none">
					' . $nombre . '
				</text>';

				continue;
			}
		?>
		<text transform="matrix(<?php echo $transform; ?>)"
			class="st1 st6"
			style="pointer-events:none">
			<?php echo $nombre; ?>
		</text>

	<?php endif; ?>
	<?php endforeach; ?>

	</g>

</svg>

