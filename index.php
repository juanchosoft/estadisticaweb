<?php
declare(strict_types=1);

/**
 * =========================================================
 *  HARDENING BÁSICO (PRODUCCIÓN)
 * =========================================================
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/**
 * ✅ Cookies de sesión seguras (antes de session_start)
 */
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if ($https) {
  ini_set('session.cookie_secure', '1');
}

session_start();

/**
 * ✅ Headers de seguridad
 */
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Resource-Policy: same-site');

if ($https) {
  header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

/**
 * ✅ CSP
 */
$csp = implode('; ', [
  "default-src 'self'",
  "base-uri 'self'",
  "object-src 'none'",
  "frame-ancestors 'none'",
  "img-src 'self' data: https:",
  "font-src 'self' data: https:",
  "style-src 'self' 'unsafe-inline' https:",
  "script-src 'self' 'unsafe-inline' https: https://ajax.googleapis.com https://cdn.jsdelivr.net",
  "connect-src 'self' https:",
  "upgrade-insecure-requests"
]);
header("Content-Security-Policy: {$csp}");

/**
 * ✅ CSRF token
 */
if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || strlen($_SESSION['csrf_token']) < 32) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$CSRF_TOKEN = $_SESSION['csrf_token'];

function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * ✅ Includes con rutas absolutas
 */
include './admin/classes/DbConection.php';
include './admin/classes/Util.php';
include './admin/classes/Sondeo.php';
include './admin/classes/RespuestaCuestionario.php';
// Obtener la opción activa de configuración
$config = Util::getInformacionConfiguracion();
$opcionActivaWeb = $config[0]['opcion_activa_web'] ?? 'sondeo';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Elecciones Colombia</title>

  <!-- ✅ CSRF disponible para JS -->
  <meta name="csrf-token" content="<?= e($CSRF_TOKEN) ?>">

  <!-- Google Web Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600&family=Roboto&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">

  <!-- Icon Font Stylesheet -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Libraries Stylesheet -->
  <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
  <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

  <!-- Customized Bootstrap Stylesheet -->
  <link href="css/bootstrap.min.css" rel="stylesheet">

  <!-- Template Stylesheet -->
  <link href="css/style.css" rel="stylesheet">

  <style>
 :root{
  --altura-menu: 0px;
  --gap-top: 3px; /* ~2mm */

  --nav-blue: #20427F;
  --nav-blue-2: #132b52;

  --bg: #f4f7fb;
  --card: #ffffff;
  --ink: #0f172a;
  --muted: #64748b;

  --radius-xl: 24px;
  --radius-lg: 18px;

  --shadow-soft: 0 14px 34px rgba(2, 6, 23, .10);
  --shadow-mid: 0 22px 60px rgba(2, 6, 23, .16);

  --border: 1px solid rgba(2, 6, 23, .08);
}

body{
  padding-top: calc(var(--altura-menu) + var(--gap-top)) !important;
  margin-top: 0 !important;
}


    .custom-container{
      width: 100%;
      max-width: 1400px;
      margin-left: auto;
      margin-right: auto;
      padding-left: 16px;
      padding-right: 16px;
    }

    /* =========================
       HERO (SaaS)
    ========================= */
    .hero-wrap{ margin-top: 18px; margin-bottom: 18px; }

    .hero{
      border-radius: var(--radius-xl);
      overflow: hidden;
      background:
        radial-gradient(1000px 400px at 0% 0%, rgba(255,255,255,.16), transparent 55%),
        linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
      box-shadow: var(--shadow-mid);
      position: relative;
      padding: 18px 18px;
    }

    .hero:before{
      content:"";
      position:absolute;
      inset:-2px;
      background:
        radial-gradient(600px 220px at 20% 20%, rgba(255,255,255,.18), transparent 55%),
        radial-gradient(700px 260px at 80% 10%, rgba(255,255,255,.12), transparent 60%);
      pointer-events:none;
    }

    .hero .content{ position: relative; z-index: 2; }

    .hero h1{
      color:#fff;
      font-weight: 900;
      letter-spacing: .2px;
      margin: 0;
      line-height: 1.1;
    }

    .hero p{
      color: rgba(255,255,255,.90);
      font-weight: 700;
      margin: .55rem 0 0;
      max-width: 900px;
    }

    .hero-chip{
      display:inline-flex;
      align-items:center;
      gap:.5rem;
      padding:.45rem .75rem;
      border-radius: 999px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.20);
      color: rgba(255,255,255,.92);
      font-weight: 900;
      letter-spacing:.2px;
    }

    .hero-actions{
      display:flex;
      gap:10px;
      flex-wrap: wrap;
      justify-content: flex-end;
      align-items: center;
    }

    .btn-hero-primary{
      border: 0 !important;
      border-radius: 999px;
      padding: .85rem 1.1rem;
      font-weight: 900;
      color:#fff !important;
      background: linear-gradient(135deg, #ff2d55, #ff7a00, #ffcc00);
      box-shadow: 0 16px 34px rgba(255, 122, 0, .28);
      transition: transform .2s ease, box-shadow .2s ease;
      white-space: nowrap;
    }
    .btn-hero-primary:hover{
      transform: translateY(-1px);
      box-shadow: 0 20px 40px rgba(255, 122, 0, .34);
    }

    .btn-hero-ghost{
      border-radius: 999px;
      font-weight: 900;
      padding: .85rem 1.1rem;
      border: 1px solid rgba(255,255,255,.26) !important;
      background: rgba(255,255,255,.12) !important;
      color: #fff !important;
      transition: transform .2s ease, background .2s ease;
      white-space: nowrap;
    }
    .btn-hero-ghost:hover{
      background: rgba(255,255,255,.18) !important;
      transform: translateY(-1px);
    }

    .quick-guide{
      margin-top: 12px;
      display:flex;
      gap:10px;
      flex-wrap: wrap;
    }

    .qg{
      flex: 1 1 220px;
      border-radius: 16px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.16);
      padding: 10px 12px;
      color: rgba(255,255,255,.92);
      box-shadow: 0 10px 24px rgba(2,6,23,.12);
    }
    .qg .t{
      font-weight: 900;
      letter-spacing: .2px;
      margin: 0;
      font-size: .95rem;
    }
    .qg .d{
      margin: .25rem 0 0;
      font-weight: 700;
      color: rgba(255,255,255,.86);
      font-size: .88rem;
    }

    /* =========================
       CARD PRINCIPAL
    ========================= */
    .main-card{
      background: var(--card);
      border: var(--border);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-soft);
      overflow: hidden;
    }

    .main-card-header{
      padding: 14px 18px;
      border-bottom: 1px solid rgba(2,6,23,.06);
      background: linear-gradient(135deg, rgba(32,66,127,.08), rgba(255,255,255,0));
    }

    .main-card-header .title{
      font-weight: 900;
      color: var(--ink);
      margin: 0;
    }

    .main-card-header .subtitle{
      color: var(--muted);
      font-weight: 700;
      margin: .25rem 0 0;
    }

    /* =========================
       BLOQUES
    ========================= */
    .block{
      background: #fff;
      border: 1px solid rgba(2,6,23,.08);
      border-radius: var(--radius-lg);
      box-shadow: 0 10px 24px rgba(2,6,23,.06);
      overflow: hidden;
    }

    .block-head{
      padding: 14px 14px 10px;
      background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(255,255,255,0));
      border-bottom: 1px solid rgba(2,6,23,.06);
    }

    .block-title{
      margin:0;
      font-weight: 900;
      color: var(--ink);
      font-size: 1.05rem;
    }
    .block-sub{
      margin:.25rem 0 0;
      color: var(--muted);
      font-weight: 700;
      font-size: .92rem;
    }

    .block-body{ padding: 14px; }

    /* MAPA */
    #mapaContainer{ position: relative; z-index: 2; }

    #fingerClick {
      position: absolute;
      width: 96px;
      height: 96px;
      top: 52%;
      left: 78%;
      transform: translate(-50%, -50%);
      z-index: 5;
      pointer-events: none;
      animation: tapAnim 1.4s infinite ease-in-out;
      opacity: 0.95;
      filter: drop-shadow(0 14px 20px rgba(2,6,23,.25));
    }

    @keyframes tapAnim {
      0%   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
      40%  { transform: translate(-50%, -50%) scale(0.90); opacity: 0.75; }
      70%  { transform: translate(-50%, -50%) scale(1); opacity: 1; }
      100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }

    /* Charts */
    .chart-wrap{ height: 280px; }
    @media (max-width: 991px){
      .chart-wrap{ height: 240px; }
      #fingerClick{ left: 70%; top: 52%; width: 78px; height: 78px; }
    }
    @media (max-width: 575px){
      .hero-actions{ justify-content: flex-start; }
      .hero{ padding: 16px; }
      .chart-wrap{ height: 220px; }
      #fingerClick{ left: 72%; top: 55%; width: 70px; height: 70px; }
    }

    /* Card flotante */
    #resultadosCard{
      width: 380px;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 20px 55px rgba(2,6,23,.20);
    }
    #resultadosCard .card-header{
      background: #fff !important;
      border-bottom: 1px solid rgba(2,6,23,.08) !important;
    }
    #badgeElectoral{
      border-radius: 999px;
      font-weight: 900;
      letter-spacing: .2px;
    }

    @media (max-width: 480px){
      #resultadosCard{
        width: calc(100vw - 24px);
        left: 12px !important;
        right: 12px !important;
      }
    }

    /* Micro UX */
    .hint{
      display:flex;
      align-items:flex-start;
      gap:10px;
      padding: 10px 12px;
      border-radius: 14px;
      background: rgba(32,66,127,.06);
      border: 1px solid rgba(32,66,127,.12);
      color: var(--ink);
    }
    .hint .h-title{ font-weight: 900; margin:0; }
    .hint .h-desc{ margin:.15rem 0 0; color: var(--muted); font-weight: 700; font-size: .92rem; }
  </style>
</head>

<body>

<?php
require_once __DIR__ . '/admin/include/loading.php';
require_once __DIR__ . '/admin/include/menu.php';
require_once __DIR__ . '/modal_login.php';
?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // ✅ Ajuste PRO: solo suma altura si el header realmente es fixed o sticky
  const menu = document.querySelector('.menu-fixed, .navbar.fixed-top, header.fixed-top, #mainNavbar.fixed-top, #navbarDefault.fixed-top, .sticky-top');

  let altura = 0;

  if (menu) {
    const st = window.getComputedStyle(menu);
    const isFixedOrSticky = (st.position === 'fixed' || st.position === 'sticky');

    // Si NO es fixed/sticky, NO metemos altura (para evitar el hueco gigante)
    if (isFixedOrSticky) {
      altura = menu.offsetHeight || 0;
    }
  }

  document.documentElement.style.setProperty("--altura-menu", altura + "px");

  // ✅ CSRF injection (lo tuyo igual)
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const form = document.querySelector('#loginForm, form[action*="login"], form[data-login="1"]');
  if (form && token) {
    const exists = form.querySelector('input[name="csrf_token"]');
    if (!exists) {
      const inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'csrf_token';
      inp.value = token;
      form.appendChild(inp);
    }
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


<!-- =========================
     HERO SaaS
========================= -->
<div class="custom-container hero-wrap">
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
<div class="custom-container mb-5" id="panelResultados">
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

            <div class="block">
              <div class="block-head">
                <h3 class="block-title text-center">Resumen nacional</h3>
                <p class="block-sub text-center mb-0">
                  Vista general para entender la tendencia actual.
                </p>
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
                    <p class="h-desc">
                      Elige uno en el mapa y verás los resultados aquí de inmediato.
                    </p>
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
      <div class="fw-bold" style="color:#0f172a;">
        Detalle del departamento
      </div>
      <button type="button" class="btn-close" id="closeCard" aria-label="Cerrar detalle"></button>
    </div>

    <div class="mt-2 d-flex align-items-center justify-content-between gap-2 flex-wrap">
      <span class="badge bg-light text-dark border" id="badgeElectoral">
        Resultados del sondeo
      </span>
      <span class="text-muted fw-bold" style="font-size: 0.85rem;">
        Actualizado según registros disponibles
      </span>
    </div>

    <div class="mt-2 text-center">
      <span class="text-muted fw-bold" style="font-size: 0.85rem;">
        Pronóstico elecciones 2026 • Vista por territorio
      </span>
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
  // Opción activa de configuración para el index.js
  window.OPCION_ACTIVA_WEB = "<?php echo addslashes($opcionActivaWeb); ?>";
</script>
<script src="admin/js/index.js"></script>

<?php
/**
 * ⚠️ Recomendación: NO incluir cron en página pública.
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
/**
 * ✅ UX: cerrar card detalle
 * (Si tu admin/js/index.js ya maneja esto, no pasa nada; este bloque ayuda si no existe.)
 */
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
