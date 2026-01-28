<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/include/app_bootstrap.php';

/**
 * ✅ Verificar sesión - Solo usuarios logueados
 */
if (empty($_SESSION['session_user']['id'])) {
    header('Location: registro.php');
    exit;
}

/**
 * ✅ Includes con rutas absolutas
 */
require_once __DIR__ . '/admin/classes/DbConection.php';
require_once __DIR__ . '/admin/classes/Util.php';
require_once __DIR__ . '/admin/classes/Sondeo.php';
require_once __DIR__ . '/admin/classes/RespuestaCuestionario.php';

/**
 * ✅ Configuración
 */
$config = Util::getInformacionConfiguracion();
$opcionActivaWeb = $config[0]['opcion_activa_web'] ?? 'sondeo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Estadísticas 360 Web</title>

  <!-- ✅ CSRF disponible para JS -->
  <meta name="csrf-token" content="<?= e($CSRF_TOKEN) ?>">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Libs -->
  <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
  <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

  <!-- Bootstrap + template -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">

  <!-- ✅ SaaS global -->
  <link href="admin/css/app.css" rel="stylesheet">
</head>

<body>

<?php
require_once __DIR__ . '/admin/include/loading.php';
require_once __DIR__ . '/admin/include/menusecond.php';
?>

<script>
/**
 * ✅ Calcula la altura REAL del header/menú fijo y lo aplica al layout SaaS
 * - No mete huecos si no es fixed/sticky
 */
document.addEventListener("DOMContentLoaded", function () {
  const menu = document.querySelector(
    '.menu-fixed, .navbar.fixed-top, header.fixed-top, #mainNavbar.fixed-top, #navbarDefault.fixed-top, .sticky-top'
  );

  let altura = 0;
  if (menu) {
    const st = window.getComputedStyle(menu);
    const isFixedOrSticky = (st.position === 'fixed' || st.position === 'sticky');
    if (isFixedOrSticky) altura = menu.offsetHeight || 0;
  }

  document.documentElement.style.setProperty("--altura-menu", altura + "px");

  // ✅ CSRF injection a login si existe
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const form = document.querySelector('#loginForm, form[action*="login"], form[data-login="1"]');
  if (form && token && !form.querySelector('input[name="csrf_token"]')) {
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'csrf_token';
    inp.value = token;
    form.appendChild(inp);
  }

  // ✅ Scroll suave a resultados
  const btn = document.getElementById('btnIrResultados');
  const target = document.getElementById('panelResultados');
  if (btn && target) {
    btn.addEventListener('click', function(){
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }
});
</script>

<div class="app-shell">

  <!-- =========================
       HERO SaaS
  ========================= -->
  <div class="app-container hero-wrap">
    <div class="hero" role="region" aria-label="Panel principal de resultados">
      <div class="content">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
          <div>
            <h1 class="fs-2 fs-lg-1">Resultados del sondeo por departamento</h1>
            <p class="mb-0">
              Aquí puedes <strong>explorar el mapa</strong>, seleccionar un departamento y ver el
              <strong>resumen nacional</strong> y el <strong>detalle por territorio</strong> en segundos.
            </p>

            <div class="quick-guide">
              <div class="qg">
                <p class="t">1) Selecciona un departamento</p>
                <p class="d">Haz clic en el mapa para abrir el detalle.</p>
              </div>
              <div class="qg">
                <p class="t">2) Revisa el comparativo</p>
                <p class="d">Mira el gráfico nacional y el del departamento.</p>
              </div>
              <div class="qg">
                <p class="t">3) Consulta el detalle</p>
                <p class="d">Se abre una tarjeta con los resultados específicos.</p>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- =========================
       PANEL PRINCIPAL
  ========================= -->
  <div class="app-container mb-5" id="panelResultados">
    <div class="main-card">
      <div class="p-3 p-lg-4">
        <div class="row g-3 g-lg-4 align-items-stretch">

          <!-- MAPA -->
          <div class="col-lg-7 col-md-7">
            <div class="block h-100" style="position:relative;">
              <div class="block-body" style="position:relative; z-index:2;">
                <img id="fingerClick" src="assets/img/admin/finger.png" alt="Indicador de clic">
                <div id="mapaContainer">
                  <?php require_once __DIR__ . '/admin/mapa_colombia/mapa_index.php'; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- GRAFICAS -->
          <div class="col-lg-5 col-md-5">
            <div class="d-grid gap-3 gap-lg-4">

              <?php if ($opcionActivaWeb === 'cuestionario'): ?>
              <div class="block">
                <div class="block-head">
                  <h3 class="block-title text-center">Cuestionario Activo</h3>
                  <p class="block-sub text-center mb-0" id="fichaTecnicaNombre">Cargando ficha técnica...</p>
                </div>
                <div class="block-body">
                  <label for="selectorPregunta" class="form-label fw-bold" style="color:#0f172a;">
                    Selecciona una pregunta:
                  </label>
                  <select id="selectorPregunta" class="form-select">
                    <option value="">Cargando preguntas...</option>
                  </select>
                </div>
              </div>
              <?php endif; ?>

              <div class="block">
                <div class="block-head">
                  <h3 class="block-title text-center">Resumen nacional</h3>
                  <p class="block-sub text-center mb-0">Vista general para entender la tendencia actual.</p>
                </div>
                <div class="block-body">
                  <div class="chart-wrap">
                    <canvas id="graficoGeneral" aria-label="Gráfico resumen nacional"></canvas>
                  </div>
                </div>
              </div>

              <div class="block">
                <div class="block-head">
                  <h3 class="block-title text-center">Detalle por departamento</h3>
                  <p class="block-sub text-center mb-0">
                    Selecciona un departamento en el mapa para actualizar esta gráfica.
                  </p>
                </div>
                <div class="block-body">
                  <div class="chart-wrap" style="height:330px;">
                    <canvas id="graficoVotos" aria-label="Gráfico por departamento"></canvas>
                  </div>

                  <div class="mt-3 hint">
                    <i class="bi bi-cursor-fill" style="font-size:1.15rem; color:#20427F;"></i>
                    <div>
                      <p class="h-title">Aún no seleccionas un departamento</p>
                      <p class="h-desc">Elige uno en el mapa y verás los resultados aquí de inmediato.</p>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

<!-- =========================
     CARD DETALLE (FLOTANTE)
========================= -->
<div id="resultadosCard" class="card position-fixed d-none" style="z-index: 9999; border: none;" aria-live="polite">
  <div class="card-header py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="fw-bold" style="color:#0f172a;">Detalle del departamento</div>
      <button type="button" class="btn-close" id="closeCard" aria-label="Cerrar detalle"></button>
    </div>

    <div class="mt-2 d-flex align-items-center justify-content-between gap-2 flex-wrap">
      <span class="badge bg-light text-dark border" id="badgeElectoral">Resultados del sondeo</span>
      <span class="text-muted fw-bold" style="font-size: 0.85rem;">Actualizado según registros disponibles</span>
    </div>

    <div class="mt-2 text-center">
      <span class="text-muted fw-bold" style="font-size: 0.85rem;">Pronóstico elecciones 2026 • Vista por territorio</span>
    </div>
  </div>

  <div class="card-body p-0">
    <div id="resultadosContent">
      <div class="text-center p-4">
        <div class="spinner-border" style="color:#20427F;" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 mb-0 text-muted fw-bold">Cargando resultados del departamento…</p>
        <small class="text-muted fw-bold d-block mt-1">Esto puede tardar unos segundos.</small>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/admin/include/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>

<script src="admin/js/lib/util.js"></script>
<script src="js/main.js"></script>
<script type="text/javascript" src="./admin/js/lib/data-md5.js"></script>

<script src="js/login.js"></script>
<script>
  window.OPCION_ACTIVA_WEB = "<?= addslashes($opcionActivaWeb); ?>";
</script>
<script src="admin/js/index.js"></script>

<?php
/**
 * ⚠️ Recomendación: NO incluir cron en página pública.
 * Lo dejamos igual a lo tuyo, solo para admin.
 */
$cronFile = __DIR__ . '/cron_exportar_fotos.php';
if (is_file($cronFile)) {
  $isAdmin = !empty($_SESSION['session_user']['id'])
    && !empty($_SESSION['session_user']['rol'])
    && ($_SESSION['session_user']['rol'] === 'admin');

  if ($isAdmin) {
    require_once $cronFile;
  }
}
?>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const card = document.getElementById('resultadosCard');
  const close = document.getElementById('closeCard');
  if (close && card) {
    close.addEventListener('click', function(){
      card.classList.add('d-none');
    });
  }
});
</script>

</body>
</html>