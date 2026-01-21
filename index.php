<?php
session_start();

/*
  ✅ DEBUG LOGIN (deja comentado en producción)
  Si lo activas, se detiene la página al hacer login.
*/
// if (isset($_POST['op']) && $_POST['op'] === 'pms_usrlogin') {
//   echo "<pre>";
//   echo "ENTRÓ AL POST DEL LOGIN <br>";
//   print_r($_POST);
//   echo "</pre>";
//   exit();
// }

include './admin/include/head.php';
?>
<!DOCTYPE html>
<html lang="es">
<body>

<?php include './admin/include/loading.php'; ?>
<?php include './admin/include/menu.php'; ?>
<?php include './modal_login.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const menu = document.querySelector('.menu-fixed, .navbar, #menu, header, #mainNavbar');
  if (menu) {
    const altura = menu.offsetHeight || 0;
    document.body.style.setProperty("--altura-menu", altura + "px");
    document.body.style.marginTop = altura + "px";
  }
});
</script>

<style>
  :root{
    --nav-blue: #20427F;
    --nav-blue-2:#132b52;

    --bg: #f4f7fb;
    --card: #ffffff;
    --ink: #0f172a;
    --muted:#64748b;

    --radius-xl: 24px;
    --radius-lg: 18px;

    --shadow-soft: 0 14px 34px rgba(2, 6, 23, .10);
    --shadow-mid:  0 20px 55px rgba(2, 6, 23, .16);

    --border: 1px solid rgba(2, 6, 23, .08);
  }

  body{
    background: radial-gradient(1200px 500px at 10% 0%, rgba(32,66,127,.10), transparent 55%),
                radial-gradient(900px 420px at 90% 10%, rgba(32,66,127,.08), transparent 60%),
                var(--bg);
  }

  .custom-container{
    width: 100%;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 16px;
    padding-right: 16px;
  }

  /* Hero */
  .hero-wrap{
    margin-top: 18px;
    margin-bottom: 18px;
  }

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

  .hero .content{
    position: relative;
    z-index: 2;
  }

  .hero h1{
    color:#fff;
    font-weight: 900;
    letter-spacing: .2px;
    margin: 0;
    line-height: 1.1;
  }

  .hero p{
    color: rgba(255,255,255,.88);
    font-weight: 700;
    margin: .5rem 0 0;
  }

  .hero .hero-chip{
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
  }

  .btn-vota-hero{
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
  .btn-vota-hero:hover{
    transform: translateY(-1px);
    box-shadow: 0 20px 40px rgba(255, 122, 0, .34);
  }

  .btn-login-hero{
    border-radius: 999px;
    font-weight: 900;
    padding: .85rem 1.1rem;
    border: 1px solid rgba(255,255,255,.26) !important;
    background: rgba(255,255,255,.12) !important;
    color: #fff !important;
    transition: transform .2s ease, background .2s ease;
    white-space: nowrap;
  }
  .btn-login-hero:hover{
    background: rgba(255,255,255,.18) !important;
    transform: translateY(-1px);
  }

  /* Card principal */
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

  /* Bloques */
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

  .block-body{
    padding: 14px;
  }

  /* MAPA */
  #mapaContainer{
    position: relative;
    z-index: 2;
  }

  #fingerClick {
    position: absolute;
    width: 98px;
    height: 98px;
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
  .chart-wrap{
    height: 280px;
  }
  @media (max-width: 991px){
    .chart-wrap{ height: 240px; }
    #fingerClick{ left: 70%; top: 52%; width: 82px; height: 82px; }
  }
  @media (max-width: 575px){
    .hero-actions{ justify-content: flex-start; }
    .hero{ padding: 16px; }
    .chart-wrap{ height: 220px; }
    #fingerClick{ left: 72%; top: 55%; width: 72px; height: 72px; }
  }

  /* ResultadosCard flotante pro */
  #resultadosCard{
    width: 360px;
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

  /* Responsive para tarjeta flotante */
  @media (max-width: 480px){
    #resultadosCard{
      width: calc(100vw - 24px);
      left: 12px !important;
      right: 12px !important;
    }
  }
</style>

<div class="custom-container hero-wrap">
  <div class="hero">
    <div class="content">
      <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
        <div>
          <div class="hero-chip mb-2">
            <i class="fas fa-chart-line"></i>
            Estadísticas 360 • Interactivo
          </div>
          <h1 class="fs-2 fs-lg-1">Panorama Nacional en tiempo real</h1>
          <p class="mb-0">
            Explora el mapa, selecciona un departamento y visualiza resultados al instante.
          </p>
        </div>

        <div class="hero-actions">
          <button type="button" class="btn btn-vota-hero" data-bs-toggle="modal" data-bs-target="#votaModal">
            <i class="fas fa-bolt me-2"></i>¡Vota ya!
          </button>
          <button type="button" class="btn btn-login-hero" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fa fa-user me-2"></i>Ingresar
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="custom-container mb-5">
  <div class="main-card">

    <div class="main-card-header">
      <h2 class="title h5 mb-0">Mapa interactivo y resultados</h2>
      <p class="subtitle mb-0">Haz clic en un departamento para ver el detalle de votos.</p>
    </div>

    <div class="p-3 p-lg-4">
      <div class="row g-3 g-lg-4 align-items-stretch">

        <!-- IZQUIERDA: MAPA -->
        <div class="col-lg-7 col-md-7">
          <div class="block h-100" style="position:relative;">
            <div class="block-head">
              <h3 class="block-title">Mapa de Colombia</h3>
              <p class="block-sub">Selecciona un departamento para actualizar los resultados.</p>
            </div>

            <div class="block-body" style="position:relative; z-index:2;">
              <img id="fingerClick" src="assets/img/admin/finger.png" alt="Indicador">
              <div id="mapaContainer">
                <?php include './admin/mapa_colombia/mapa_index.php'; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- DERECHA: PANEL -->
        <div class="col-lg-5 col-md-5">
          <div class="d-grid gap-3 gap-lg-4">

            <!-- Resultados Nacionales -->
            <div class="block">
              <div class="block-head">
                <h3 class="block-title text-center">Resultados Nacionales</h3>
                <p class="block-sub text-center mb-0">Resumen general del sondeo.</p>
              </div>
              <div class="block-body">
                <div class="chart-wrap">
                  <canvas id="graficoGeneral"></canvas>
                </div>
              </div>
            </div>

            <!-- Resultados por departamento -->
            <div class="block">
              <div class="block-head">
                <h3 class="block-title text-center">Resultados por Departamento</h3>
                <p class="block-sub text-center mb-0">
                  Selecciona un departamento en el mapa para ver los resultados.
                </p>
              </div>
              <div class="block-body">
                <div class="chart-wrap" style="height:330px;">
                  <canvas id="graficoVotos"></canvas>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- Card flotante resultados -->
<div id="resultadosCard" class="card position-fixed d-none" style="z-index: 9999; border: none;">
  <div class="card-header py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="fw-bold" style="color:#0f172a;">Detalle</div>
      <button type="button" class="btn-close" id="closeCard"></button>
    </div>

    <div class="mt-2">
      <span class="badge bg-light text-dark border" id="badgeElectoral">VOTOS ELECTORALES</span>
    </div>

    <div class="mt-2 text-center">
      <span class="text-muted fw-bold" style="font-size: 0.85rem;">
        Pronóstico elecciones Presidente 2026 – Sondeo
      </span>
    </div>
  </div>

  <div class="card-body p-0">
    <div id="resultadosContent">
      <div class="text-center p-4">
        <div class="spinner-border" style="color:#20427F;" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 mb-0 text-muted fw-bold">Cargando resultados...</p>
      </div>
    </div>
  </div>
</div>

<?php include './admin/include/footer.php'; ?>

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
<script src="admin/js/index.js"></script>

<?php
@include __DIR__ . "/cron_exportar_fotos.php";
?>

</body>
</html>
