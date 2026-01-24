<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['session_user']) || !isset($_SESSION['session_user']['id'])) {
  header('Location: index.php');
  exit();
}

require './admin/include/generic_classes.php';

// Obtener la opción activa de configuración
$config = Util::getInformacionConfiguracion();
$opcionActivaWeb = $config[0]['opcion_activa_web'] ?? '';

// Si es la primera vez que entra (mostrar_bienvenida), redirigir directamente a la opción activa
if (isset($_SESSION['mostrar_bienvenida']) && $_SESSION['mostrar_bienvenida'] == true) {
    unset($_SESSION['mostrar_bienvenida']);
    if ($opcionActivaWeb === 'sondeo') {
        header('Location: sondeo.php');
        exit();
    } elseif ($opcionActivaWeb === 'estudio') {
        header('Location: grilla.php');
        exit();
    } elseif ($opcionActivaWeb === 'cuestionario') {
        header('Location: encuesta.php');
        exit();
    }
}

// Obtener primer nombre del usuario logueado
$nombreUsuario = $_SESSION['session_user']['nombre_completo']
  ?? $_SESSION['session_user']['usuario']
  ?? 'Usuario';

$partes = explode(' ', trim($nombreUsuario));
$primerNombre = $partes[0] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">

<?php include './admin/include/head2.php'; ?>

<body>

<?php include './admin/include/loading.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  :root{
    --nav-blue: #20427F;
    --nav-blue-2:#132b52;

    --bg: #f4f7fb;
    --card:#ffffff;
    --ink:#0f172a;
    --muted:#64748b;

    --radius-xl: 24px;
    --radius-lg: 18px;

    --shadow-soft: 0 14px 34px rgba(2, 6, 23, .10);
    --shadow-mid:  0 22px 60px rgba(2, 6, 23, .16);

    --border: 1px solid rgba(2, 6, 23, .08);
  }

  /* Fondo pro */
  body{
    background:
      radial-gradient(1200px 520px at 10% 0%, rgba(32,66,127,.10), transparent 55%),
      radial-gradient(900px 420px at 90% 10%, rgba(32,66,127,.08), transparent 60%),
      var(--bg);
  }

  /* Contenedor */
  .page-wrap{
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 18px 16px 40px;
  }

  /* Hero superior */
  .hero{
    border-radius: var(--radius-xl);
    overflow: hidden;
    background:
      radial-gradient(1000px 400px at 0% 0%, rgba(255,255,255,.16), transparent 55%),
      linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
    box-shadow: var(--shadow-mid);
    padding: 18px;
    position: relative;
    margin-bottom: 16px;
  }
  .hero:before{
    content:"";
    position:absolute;
    inset:-2px;
    background:
      radial-gradient(700px 240px at 20% 20%, rgba(255,255,255,.18), transparent 55%),
      radial-gradient(700px 260px at 80% 10%, rgba(255,255,255,.12), transparent 60%);
    pointer-events:none;
  }
  .hero .content{ position: relative; z-index: 2; }

  .hero-chip{
    display:inline-flex;
    align-items:center;
    gap:.55rem;
    padding:.45rem .8rem;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.20);
    color: rgba(255,255,255,.92);
    font-weight: 900;
    letter-spacing:.2px;
    margin-bottom: 8px;
  }
  .hero h1{
    color:#fff;
    font-weight: 900;
    margin: 0;
    line-height: 1.1;
    letter-spacing: .2px;
  }
  .hero p{
    color: rgba(255,255,255,.88);
    font-weight: 700;
    margin: .55rem 0 0;
  }

  .hero-actions{
    display:flex;
    gap:10px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .btn-hero{
    border-radius: 999px;
    padding: .85rem 1.1rem;
    font-weight: 900;
    letter-spacing: .2px;
    white-space: nowrap;
  }
  .btn-hero-primary{
    border: 0 !important;
    color:#fff !important;
    background: linear-gradient(135deg, #ff2d55, #ff7a00, #ffcc00);
    box-shadow: 0 16px 34px rgba(255, 122, 0, .28);
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .btn-hero-primary:hover{
    transform: translateY(-1px);
    box-shadow: 0 20px 40px rgba(255, 122, 0, .34);
  }
  .btn-hero-soft{
    border: 1px solid rgba(255,255,255,.26) !important;
    background: rgba(255,255,255,.12) !important;
    color:#fff !important;
    transition: transform .2s ease, background .2s ease;
  }
  .btn-hero-soft:hover{
    transform: translateY(-1px);
    background: rgba(255,255,255,.18) !important;
  }

  /* Card principal */
  .main-card{
    background: var(--card);
    border: var(--border);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-soft);
    overflow: hidden;
  }

  .main-card-head{
    padding: 14px 18px;
    border-bottom: 1px solid rgba(2,6,23,.06);
    background: linear-gradient(135deg, rgba(32,66,127,.08), rgba(255,255,255,0));
  }
  .main-card-head h2{
    margin: 0;
    font-weight: 900;
    color: var(--ink);
  }
  .main-card-head p{
    margin: .25rem 0 0;
    font-weight: 700;
    color: var(--muted);
  }

  /* Blocks */
  .block{
    background:#fff;
    border: 1px solid rgba(2,6,23,.08);
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 24px rgba(2,6,23,.06);
    overflow: hidden;
    height: 100%;
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

  /* Mapa wrap */
  .map-wrap{
    position: relative;
    overflow: hidden;
  }

  /* TIPOGRAFÍA PRO PARA MAPA SVG */
  #mapaContainer,
  #mapaContainer svg {
    font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif !important;
  }
  #mapaContainer svg text {
    font-weight: 800;
    font-size: 14px;
    fill: #171718ff;
    letter-spacing: .35px;
    pointer-events: none;
    text-transform: uppercase;

    paint-order: stroke fill;
    stroke: rgba(255,255,255,.86);
    stroke-width: 2px;
    stroke-linejoin: round;
  }
  #mapaContainer svg g:hover text {
    fill: #20427F;
    letter-spacing: .6px;
  }
  #mapaContainer svg g:hover path {
    filter: brightness(1.06);
    transition: all .2s ease;
  }

  @media (max-width: 768px){
    #mapaContainer svg text{ font-size: 11px; }
  }
  @media (min-width: 1400px){
    #mapaContainer svg text{ font-size: 13px; }
  }

  /* Panel derecho */
  .welcome-title{
    font-weight: 900;
    color: var(--nav-blue);
    margin-bottom: 8px;
  }
  .welcome-p{
    color: var(--muted);
    font-weight: 700;
    margin-bottom: 0;
  }

  /* Card gráfico */
  .mini-card{
    max-width: 320px;
    margin: 0 auto;
  }

  /* Ajustes responsive */
  @media (max-width: 991px){
    .hero-actions{ justify-content: flex-start; }
  }

  /* SweetAlert2 a juego */
  .swal2-popup{
    border-radius: 18px !important;
    box-shadow: 0 20px 60px rgba(2,6,23,.20) !important;
  }
  .swal2-confirm{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    border-radius: 12px !important;
    font-weight: 900 !important;
    padding: .7rem 1.1rem !important;
  }
</style>

<?php include './admin/include/navbar_logueado.php'; ?>

<div class="page-wrap">

  <!-- HERO -->
  <div class="hero">
    <div class="content">
      <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
        <div>
          <div class="hero-chip">
            <i class="fas fa-location-dot"></i>
            Panel ciudadano • Mapa interactivo
          </div>
          <h1 class="fs-2 fs-lg-1">Bienvenido, <?php echo htmlspecialchars($primerNombre); ?> 👋</h1>
          <p class="mb-0">
            Haz clic en tu municipio en el mapa para continuar con tu participación.
          </p>
        </div>

        <div class="hero-actions">
          <!-- Botón abre modal de perfil (si tu perfil.php usa un modal con id #perfilModal) -->
          <button type="button" class="btn btn-hero btn-hero-soft" data-bs-toggle="modal" data-bs-target="#perfilModal">
            <i class="fas fa-user-circle me-2"></i>Mi perfil
          </button>

          <!-- Botón tip -->
          <button type="button" class="btn btn-hero btn-hero-primary" id="btnTipMapa">
            <i class="fas fa-hand-pointer me-2"></i>¿Cómo votar?
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- CARD PRINCIPAL -->
  <div class="main-card">
     <div class="p-3 p-lg-4">
      <div class="row g-3 g-lg-4 align-items-stretch">

        <!-- MAPA -->
        <div class="col-lg-7 col-md-7">
          <div class="block map-wrap">
            <div class="block-body" id="mapaContainer">
              <?php include './admin/mapa_colombia/mapa.php'; ?>
            </div>
          </div>
        </div>

        <!-- PANEL DERECHO -->
        <div class="col-lg-5 col-md-5">
          <div class="d-grid gap-3 gap-lg-4">

           
              <div class="block">
              <div class="block-head text-center">
                <h3 class="block-title">
                  <i class="fas fa-hand-pointer me-2" style="color:#20427F;"></i>
                  ¿Qué debes hacer ahora?
                </h3>
                <p class="block-sub mb-0">
                  Haz clic en tu departamento para continuar.
                </p>
              </div>

              <div class="block-body text-center">
                <div class="mb-2">
                  <i class="fas fa-location-dot" style="font-size:52px; color:#20427F;"></i>
                </div>

                <div class="welcome-p">
                  No aparece tu departamento? Revisa que tus datos de registro estén correctos.
                </div>
              </div>
            </div>



            <!-- TARJETA GRÁFICO -->
            <div class="block mini-card">
              <div class="block-head text-center">
                <h3 class="block-title">
                  <i class="fas fa-chart-pie me-2" style="color:#20427F;"></i>
                  % de votantes en tu departamento
                </h3>
                <p class="block-sub mb-0">Dato estimado para referencia.</p>
              </div>
              <div class="block-body">
                <canvas id="graficoPorcentajeVotantes"></canvas>
                <p class="text-muted mt-3 text-center fw-bold" id="textoGraficoInfo">
                  Datos estimados según procesos electorales recientes.
                </p>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

</div>

<?php include './admin/include/perfil.php'; ?>
<?php include './admin/include/footer.php'; ?>

<!-- Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>

<script type="text/javascript" src="./admin/js/lib/data-md5.js"></script>
<script src="admin/js/perfil.js"></script>
<script src="js/main.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
      // tooltips
      $('[data-bs-toggle="tooltip"]').each(function() {
        try { new bootstrap.Tooltip(this); } catch (e) {}
      });

      // Click mapa => ir a url
      $('.mapaClick').on('click', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        if (url && url !== '#' && url !== '') {
          setTimeout(function() { window.location.href = url; }, 80);
        }
      });
    }, 100);
  });

  // Tip de ayuda "Cómo votar"
  document.getElementById('btnTipMapa')?.addEventListener('click', () => {
    Swal.fire({
      title: "¿Cómo votar?",
      html:
        "<div style='text-align:left;font-weight:700;color:#334155;line-height:1.35'>" +
        "1) Ubica tu <b>municipio</b> en el mapa.<br>" +
        "2) Haz clic sobre el municipio.<br>" +
        "3) Continúa al módulo de votación y confirma tu participación.<br><br>" +
        "<span style='color:#64748b;'>Tip: puedes hacer zoom usando el navegador si lo necesitas.</span>" +
        "</div>",
      icon: "question",
      confirmButtonText: "Entendido",
      allowOutsideClick: true
    });
  });

  // Gráfico (data quemada)
  document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById("graficoPorcentajeVotantes");
    if (!ctx) return;

    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Votantes", "No votantes"],
        datasets: [{
          data: [62, 38],
          backgroundColor: ["#20427F", "#cbd5e1"],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 0.9,
        plugins: {
          legend: {
            position: "bottom",
            labels: { font: { weight: "700" } }
          }
        },
        cutout: "68%"
      }
    });
  });
</script>

<?php
@include __DIR__ . "/cron_exportar_fotos.php";
?>
</body>
</html>
