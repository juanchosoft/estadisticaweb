<?php
require_once './admin/include/generic_classes.php';
include './admin/db/colores.php';
include './admin/classes/Main.php';
include './admin/classes/MapaConfig.php';

// Obtener código de municipio desde la sesión
$codigoMunicipioSessionVotante = SessionData::getCodigoMunicipioSessionVotante();

try {
  $codigoDepartamento = isset($_REQUEST['dep']) ? $_REQUEST['dep'] : Util::getDepartamentoPrincipal();
  $mapaMostrar = MapaConfig::obtenerRutaMapa($codigoDepartamento);
} catch (InvalidArgumentException $e) {
  echo "<script>
    alert('Información enviada no es correcta');
    window.location = 'dashboard.php';
  </script>";
  exit;
}

// Información del main
$arr = Main::getDataMain(['codigoDepartamento' => $codigoDepartamento]);
$isvalid = $arr['output']['valid'];
$visitas = $arr['output']['visitas'];
$lideres = $arr['output']['lideres'];
$municipios = $arr['output']['municipios'];
$inscritos = $arr['output']['inscritos'];
$reuniones = $arr['output']['reuniones'];
$departamentoInfo = $arr['output']['departamento'];

// Información del proyecto
$config = Util::getInformacionConfiguracion();
$nombreProyecto = $config[0]['nombre_proyecto'] ?? '';
$logo = $config[0]['logo'] ?? '';

$nombreUsuario = $_SESSION['session_user']['nombre_completo'] ?? $_SESSION['session_user']['usuario'] ?? 'Usuario';
$partes = explode(' ', $nombreUsuario);
$primerNombre = $partes[0] ?? 'Usuario';

// Información de la opción activa web
$opcionActivaWeb = $config[0]['opcion_activa_web'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Dashboard Colombia - <?php echo htmlspecialchars($departamentoInfo); ?></title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <!-- Tipografía PRO -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Libraries -->
  <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
  <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

  <!-- Bootstrap + Template -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">

  <style>
    :root{
      --nav-blue:#20427F;
      --nav-blue-2:#132b52;
      --bg:#f4f7fb;
      --ink:#0f172a;
      --muted:#64748b;

      --radius-xl: 24px;
      --radius-lg: 18px;

      --shadow-soft: 0 14px 34px rgba(2, 6, 23, .10);
      --shadow-mid:  0 22px 60px rgba(2, 6, 23, .16);

      --border: 1px solid rgba(2, 6, 23, .08);
    }

    body{
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:
        radial-gradient(1200px 520px at 10% 0%, rgba(32,66,127,.10), transparent 55%),
        radial-gradient(900px 420px at 90% 10%, rgba(32,66,127,.08), transparent 60%),
        var(--bg);
    }

    /* Spinner consistente */
    #spinner{
      background: rgba(255,255,255,.92) !important;
      backdrop-filter: blur(6px);
    }
    #spinner .spinner-border{
      color: var(--nav-blue) !important;
    }

    .page-wrap{
      width: 100%;
      max-width: 1400px;
      margin: 0 auto;
      padding: 18px 16px 40px;
    }

    /* Header hero interno */
    .hero{
      border-radius: var(--radius-xl);
      overflow: hidden;
      background:
        radial-gradient(1000px 400px at 0% 0%, rgba(255,255,255,.16), transparent 55%),
        linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
      box-shadow: var(--shadow-mid);
      padding: 16px 18px;
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

    /* Botón volver pro */
    .btn-back{
      border-radius: 999px;
      font-weight: 900;
      padding: .72rem 1rem;
      border: 1px solid rgba(255,255,255,.26) !important;
      background: rgba(255,255,255,.12) !important;
      color:#fff !important;
      transition: transform .2s ease, background .2s ease;
      white-space: nowrap;
    }
    .btn-back:hover{
      transform: translateY(-1px);
      background: rgba(255,255,255,.18) !important;
    }

    /* Card principal */
    .main-card{
      background: #fff;
      border: var(--border);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-soft);
      overflow: hidden;
    }
    .main-head{
      padding: 14px 18px;
      border-bottom: 1px solid rgba(2,6,23,.06);
      background: linear-gradient(135deg, rgba(32,66,127,.08), rgba(255,255,255,0));
    }
    .main-head h2{
      margin: 0;
      font-weight: 900;
      color: var(--ink);
    }
    .main-head p{
      margin: .25rem 0 0;
      font-weight: 700;
      color: var(--muted);
    }

    .block{
      background:#fff;
      border: 1px solid rgba(2,6,23,.08);
      border-radius: 18px;
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

    /* Tipografía PRO para SVG del mapa */
    #mapaContainer,
    #mapaContainer svg{
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif !important;
    }
    #mapaContainer svg text{
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
    #mapaContainer svg g:hover text{
      fill: var(--nav-blue);
      letter-spacing: .6px;
    }
    #mapaContainer svg g:hover path{
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
    .user-card .icon{
      font-size: 54px;
      color: var(--nav-blue);
      margin-bottom: 6px;
    }
    .user-card .hello{
      color: var(--nav-blue);
      font-weight: 900;
      margin-bottom: 8px;
    }
    .user-card .desc{
      color: var(--muted);
      font-weight: 700;
      margin-bottom: 0;
    }

    /* Mini chart */
    .chart-wrap{ height: 170px; }
    @media (max-width: 991px){
      .chart-wrap{ height: 150px; }
    }

    .note{
      font-size: 14px;
      color: var(--muted);
      font-weight: 700;
      margin: .9rem 0 0;
      text-align: center;
    }
  </style>
</head>

<body>
  <div id="spinner" class="show position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </div>

  <?php include './admin/include/menusecond.php'; ?>

  <div class="page-wrap">

    <!-- HERO -->
    <div class="hero">
      <div class="content">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
          <div>
         
            <h1 class="fs-2 fs-lg-1">Mapa del Departamento de <?php echo htmlspecialchars($departamentoInfo); ?></h1>
            <p class="mb-0">Selecciona un municipio en el mapa para explorar información y participación.</p>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <button onclick="goBack()" class="btn btn-back">
              <i class="fas fa-arrow-left me-2"></i>Volver
            </button>

            <!-- Si tu perfil.php tiene modal #perfilModal -->
            <button type="button" class="btn btn-back" data-bs-toggle="modal" data-bs-target="#perfilModal">
              <i class="fas fa-user-circle me-2"></i>Mi perfil
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
          <div class="col-lg-8 col-md-7">
            <div class="block">
              <div class="block-head">
                <h3 class="block-title">Selecciona en el mapa el municipio donde realizaste tu registro, identificado en color verde</h3>

               </div>
              <div class="block-body" id="mapaContainer">
                <?php require_once $mapaMostrar; ?>
              </div>
            </div>
          </div>

          <!-- PANEL DERECHO -->
          <div class="col-lg-4 col-md-5">
            <div class="d-grid gap-3 gap-lg-4">

              <div class="block user-card">
                <div class="block-head text-center">
                  <h3 class="block-title">
                    <i class="fas fa-user-check me-2" style="color:#20427F;"></i>
                    Hola <?php echo htmlspecialchars($primerNombre); ?>
                  </h3>
                  <p class="block-sub mb-0">Tu punto de votación registrado</p>
                  <input type="text" id="opcionActivaWeb" value="<?php echo htmlspecialchars($opcionActivaWeb); ?>">
                </div>
                <div class="block-body text-center">
                  <div class="icon">
                    <i class="fas fa-location-dot"></i>
                  </div>
                  <p class="desc fs-6">
                    Estás registrado para votar en el municipio de
                    <b><?php echo htmlspecialchars($_SESSION['session_user']['municipio_nombre']); ?></b>.
                  </p>
                </div>
              </div>

              <div class="block">
                <div class="block-head text-center">
                  <h3 class="block-title">
                    <i class="fas fa-chart-pie me-2" style="color:#20427F;"></i>
                    % de votantes estimado
                  </h3>
                  <p class="block-sub mb-0">Referencia aproximada basada en históricos.</p>
                </div>
                <div class="block-body">
                  <div class="chart-wrap">
                    <canvas id="graficoVotantes"></canvas>
                  </div>
                  <div class="note">Datos aproximados basados en elecciones previas.</div>
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

  <!-- JS -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="lib/easing/easing.min.js"></script>
  <script src="lib/waypoints/waypoints.min.js"></script>
  <script src="lib/owlcarousel/owl.carousel.min.js"></script>
  <script src="lib/lightbox/js/lightbox.min.js"></script>

  <script src="js/main.js"></script>
  <script src="admin/js/perfil.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    function goBack() {
      if (window.history.length > 1) window.history.back();
      else window.location.href = "dashboard.php";
    }
  </script>

  <script>
    window.MAPA_COLOR_NEUTRO = "<?= Util::getColorNeutroMapa(); ?>";
  </script>
  <script src="admin/js/Dashboard_colombia.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const ctx = document.getElementById("graficoVotantes");
      if (!ctx) return;

      new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: ["Votantes", "No votantes"],
          datasets: [{
            data: [58, 42],
            backgroundColor: ["#20427F", "#cbd5e1"],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "68%",
          plugins: {
            legend: {
              position: "bottom",
              labels: { font: { weight: "700" } }
            }
          }
        }
      });
    });
  </script>
</body>
</html>
