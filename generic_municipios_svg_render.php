<?php
/**
 * REUSABLE SVG MAP RENDERER COMPONENT
 * Renders the municipality map based on the $responseMapa array provided in the scope.
 *
 * NOTE: This component assumes the following functions/classes are available in the calling scope:
 * - getColorByNum($num)
 * - getClaseColorVeredas($fill)
 * - Util::generarUrlMapaGeneralCiudadesPorCodDepartamentoYCodMunicipio(...)
 */

if (empty($responseMapa) || !is_array($responseMapa)) {
    echo '<div class="text-center p-4 text-gray-500">No hay datos de municipios para mostrar.</div>';
    return;
}

// Inclusión de los estilos estáticos CSS
?>
<style type="text/css">
  /* Estilo adicional para el contenedor del mapa */
  .cuerpoMapa {
      max-width: 100%;
      height: auto;
  }
  .mapaClick {
      cursor: pointer;
      transition: fill 0.2s ease;
  }
  .mapaClick:hover {
      opacity: 0.8;
  }
</style>

<div id="contenido-mapa" class="cuerpoMapa w-12">
    <!-- Contenedor principal de la vista SVG -->
    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1500 1580">
        <!-- Contenedor secundario para el contenido geográfico -->
          <?php foreach ($responseMapa as $value) :

            $fill = $claseColor = Util::getColorNeutroMapa();; // Color por defecto
            $claseColor = $fill;
            $estiloColor = "";

            // Verificar si es el municipio del votante
            if (!empty($codigoMunicipioSessionVotante)) {
                if ($value['codigo_muncipio'] == $codigoMunicipioSessionVotante) {
                    $claseColor = Util::getColorUbicacionActual(); // Color para el municipio del votante
                }
            }
            $estiloColor = "fill: " . $claseColor . ";";

            // Genera la URL de destino
            $archivo = 'dashboard.php';
            $url = Util::generarUrlMapaGeneralCiudadesPorCodDepartamentoYCodMunicipio(
                $archivo,
                urlencode($value['codigo_departamento'] ?? ''),
                urlencode($value['codigo_muncipio'] ?? ''),
                urlencode($value['municipio'] ?? '')
            );
          ?>
            <!-- Grupo SVG para el municipio (ruta + texto) -->
            <g id="<?= strtoupper($value['id'] ?? 'G_ERR') ?>">
              <path id="<?= $value['id'] ?? 'PATH_ERR' ?>"
                class="<?= $value['class'] ?? '' ?> municipios mapaClick"
                style="<?= $estiloColor ?>"
                data-url="<?= $url ?>"
                d="<?= $value['d'] ?? '' ?>" />
              <!-- Etiqueta de texto para el nombre del municipio -->
              <text transform="matrix(<?= $value['transform'] ?? '' ?>)"
                class="<?= $value['class2'] ?? '' ?>"><?= $value['municipio'] ?? 'S/N' ?></text>
            </g>
          <?php endforeach; ?>

        </svg>
    </svg>
</div>

<!-- <script>
  // Script de interacción (asume jQuery y Bootstrap están cargados)
  $(function() {
    // Inicialización de Tooltips (para elementos IMG, siguiendo tu código original)
    $("img").each(function(index, el) {
      $(this).attr("data-bs-toggle", "tooltip");
      $(this).attr("data-bs-placement", "left");
      if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
         new bootstrap.Tooltip($(this)[0], {});
      }
    });

    // Manejador de clic para las rutas SVG de los municipios
    $(".mapaClick").on('click', function(event) {
      const url = $(this).data("url");
      if (url) {
        // Redirige a la URL generada
        location.href = url;
      }
    });
  });
</script> -->
