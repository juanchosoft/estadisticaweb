<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['session_user']) || !isset($_SESSION['session_user']['id'])) {
  header('Location: index.php');
  exit();
}

require './admin/include/generic_classes.php';
include './admin/classes/Departamento.php';

// Nombre usuario
$nombreUsuario = $_SESSION['session_user']['nombre_completo'] ?? $_SESSION['session_user']['usuario'] ?? 'Usuario';
$partes = explode(' ', $nombreUsuario);
$primerNombre = $partes[0] ?? 'Usuario';

// Información de la opción activa web
$config = Util::getInformacionConfiguracion();
$opcionActivaWeb = $config[0]['opcion_activa_web'] ?? '';

// Obtener lista de departamentos
$departamentosResult = Departamento::getAll([]);
$departamentos = $departamentosResult['output']['response'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<?php include './admin/include/head2.php'; ?>
<?php include './admin/include/menusecond.php'; ?>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Inter (si ya lo cargas en head2.php, puedes omitirlo) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800;900&display=swap" rel="stylesheet">

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

    html, body{ height:100%; }

    body{
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:
        radial-gradient(1200px 520px at 10% 0%, rgba(32,66,127,.10), transparent 55%),
        radial-gradient(900px 420px at 90% 10%, rgba(32,66,127,.08), transparent 60%),
        var(--bg);
      color: var(--ink);

      /* ✅ Evita que el footer “mate” el bottom-sheet */
      padding-bottom: 96px;
    }

    /* ✅ Bloquea scroll cuando el panel flotante está abierto */
    body.overlay-open{
      overflow: hidden;
      touch-action: none;
    }

    /* Spinner pro */
    #spinner{
      background: rgba(255,255,255,.92) !important;
      backdrop-filter: blur(6px);
      z-index: 99999;
    }
    #spinner .spinner-border{ color: var(--nav-blue) !important; }

    .page-wrap{
      width: 100%;
      max-width: 1400px;
      margin: 0 auto;
      padding: 18px 16px 40px;
    }

    /* HERO */
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

    .btn-hero{
      border-radius: 999px;
      font-weight: 900;
      padding: .78rem 1rem;
      border: 1px solid rgba(255,255,255,.26) !important;
      background: rgba(255,255,255,.12) !important;
      color:#fff !important;
      transition: transform .2s ease, background .2s ease;
      white-space: nowrap;
    }
    .btn-hero:hover{
      transform: translateY(-1px);
      background: rgba(255,255,255,.18) !important;
    }

    /* Card principal */
    .main-card{
      background:#fff;
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
      margin:0;
      font-weight: 900;
      color: var(--ink);
    }
    .main-head p{
      margin:.25rem 0 0;
      font-weight: 700;
      color: var(--muted);
    }

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

    /* TIPOGRAFÍA PRO MAPA SVG */
    #mapaContainer, #mapaContainer svg{
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif !important;
    }
    #mapaContainer svg text{
      font-weight: 800;
      font-size: 12.5px;
      fill: #0f172a;
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

    /* Panel gráfico */
    .chart-wrap{ height: 260px; }
    @media (max-width: 991px){ .chart-wrap{ height: 230px; } }

    /* ===============================
       ✅ RESULTADOS CARD (FIX REAL)
       Desktop: panel flotante
       Mobile: bottom-sheet
    =============================== */
    #resultadosCard{
      width: 460px;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 20px 55px rgba(2,6,23,.20);
      backdrop-filter: blur(10px);

      /* ✅ por encima del footer y todo */
      z-index: 999999 !important;

      /* Desktop position */
      position: fixed;
      right: 18px;
      top: 92px;
      left: auto;
      bottom: auto;

      border: none !important;
    }

    #resultadosCard .card-header{
      background:
        radial-gradient(900px 220px at 0% 0%, rgba(32,66,127,.10), transparent 60%),
        #fff !important;
      border-bottom: 1px solid rgba(2,6,23,.08) !important;
    }

    #resultadosCard .btn-close{ opacity: .85; }

    #resultadosCard .badge{
      border-radius: 999px;
      font-weight: 900;
      letter-spacing: .2px;
      background: rgba(32,66,127,.10) !important;
      color: var(--nav-blue) !important;
      border: 1px solid rgba(32,66,127,.18);
    }

    #resultadosCard .form-select{
      border-radius: 14px;
      font-weight: 800;
      border: 1px solid rgba(2,6,23,.12);
      box-shadow: none !important;
    }
    #resultadosCard .form-select:focus{
      border-color: rgba(32,66,127,.55);
      box-shadow: 0 0 0 .25rem rgba(32,66,127,.14) !important;
    }

    /* ✅ Bottom-sheet móvil */
    @media (max-width: 575px){
      body{ padding-bottom: 140px; }

      #resultadosCard{
        width: calc(100vw - 20px) !important;
        left: 10px !important;
        right: 10px !important;
        top: auto !important;

        bottom: calc(env(safe-area-inset-bottom, 0px) + 10px) !important;

        border-radius: 18px;
        box-shadow: 0 28px 75px rgba(2,6,23,.28);
      }

      #resultadosCard .card-body{
        max-height: 58vh;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
      }
    }

    /* ✅ Modal bootstrap encima del panel flotante */
    .modal{ z-index: 1000000 !important; }
    .modal-backdrop{ z-index: 999999 !important; }

    /* ✅ Si perfilModal es grande, le damos scroll en móvil */
    @media (max-width: 575px){
      .modal-dialog{ margin: 10px; }
      .modal-content{ border-radius: 18px; }
      .modal-body{
        max-height: 70vh;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
      }
    }
  </style>
</head>

<body>
  <input type="hidden" id="opcionActivaWeb" value="<?php echo htmlspecialchars($opcionActivaWeb); ?>">

  <div id="spinner" class="show position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </div>

  <div class="page-wrap">

    <!-- HERO -->
    <div class="hero">
      <div class="content">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
          <div>
            <div class="hero-chip">
              <i class="fas fa-chart-bar"></i>
              Visualización • Consultas estadísticas
            </div>
            <h1 class="fs-2 fs-lg-1">Gráficos de Información</h1>
            <p class="mb-0">
              Hola <b><?php echo htmlspecialchars($primerNombre); ?></b>, interactúa con el mapa para consultar estadísticas por departamento.
            </p>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-hero" data-bs-toggle="modal" data-bs-target="#perfilModal">
              <i class="fas fa-user-circle me-2"></i>Mi perfil
            </button>
            <button type="button" class="btn btn-hero" id="btnAyuda">
              <i class="fas fa-circle-info me-2"></i>¿Cómo usar?
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD PRINCIPAL -->
    <div class="main-card">
      <div class="main-head">
        <h2 class="h5">Mapa + Estadísticas</h2>
        <p>Selecciona un departamento y elige el tipo de consulta.</p>
      </div>

      <div class="p-3 p-lg-4">
        <div class="row g-3 g-lg-4 align-items-stretch">

          <!-- MAPA -->
          <div class="col-lg-7 col-md-7">
            <div class="block">
              <div class="block-head">
                <h3 class="block-title">Mapa de Colombia</h3>
                <p class="block-sub">Haz clic en un departamento para abrir el panel de consultas.</p>
              </div>
              <div class="block-body" style="position:relative; z-index:5;">
                <div id="mapaContainer">
                  <?php include './admin/mapa_colombia/mapa.php'; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- PANEL DERECHO -->
          <div class="col-lg-5 col-md-5">
            <div class="d-grid gap-3 gap-lg-4">

              <!-- Panel de consultas (antes era modal flotante) -->
              <div class="block">
                <div class="block-head">
                  <h3 class="block-title">
                    <i class="fas fa-filter me-2" style="color:#20427F;"></i>
                    Consultas Estadísticas
                  </h3>
                  <p class="block-sub mb-0">Selecciona departamento y consulta para ver resultados.</p>
                </div>
                <div class="block-body">
                  <!-- Select de Departamento -->
                  <div class="mb-3">
                    <label class="form-label fw-bold small">Departamento</label>
                    <select class="form-select" id="selectorDepartamento">
                      <option value="">Seleccione un departamento...</option>
                      <?php foreach ($departamentos as $dep): ?>
                        <option value="<?= htmlspecialchars($dep['codigo_departamento']) ?>"><?= htmlspecialchars($dep['departamento']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Select de Consulta (Sondeo o Encuesta según configuración) -->
                  <?php if ($opcionActivaWeb === 'cuestionario'): ?>
                  <div class="mb-3" id="selectorEncuestaContainer">
                    <label class="form-label fw-bold small">Encuesta</label>
                    <select class="form-select" id="selectorEncuesta">
                      <option value="">Seleccione una encuesta...</option>
                    </select>
                  </div>
                  <div class="mb-3 d-none" id="selectorPreguntaContainer">
                    <label class="form-label fw-bold small">Pregunta</label>
                    <select class="form-select" id="selectorPregunta">
                      <option value="">Seleccione una pregunta...</option>
                    </select>
                  </div>
                  <?php endif; ?>

                  <!-- Contenedor oculto para resultados (usado internamente por JS) -->
                  <div id="resultadosContent" class="d-none"></div>
                </div>
              </div>

              <div class="block">
                <div class="block-head text-center">
                  <h3 class="block-title">
                    <i class="fas fa-chart-pie me-2" style="color:#20427F;"></i>
                    Estadísticas de Información
                  </h3>
                  <p class="block-sub mb-0">Visualización dinámica según tu selección.</p>
                </div>
                <div class="block-body">
                  <div class="chart-wrap">
                    <canvas id="graficoDatosGenerales"></canvas>
                  </div>
                  <p class="text-muted mt-3 text-center fw-bold" id="textoGraficoInfo">
                    Selecciona un departamento y una consulta para comenzar.
                  </p>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>

  </div>

  <!-- Card flotante consultas (comentado para uso futuro)
  <div id="resultadosCardModal" class="card d-none">
    ...
  </div>
  -->

  <?php include './admin/include/perfil.php'; ?>
  <?php include './admin/include/footer.php'; ?>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="lib/easing/easing.min.js"></script>
  <script src="lib/waypoints/waypoints.min.js"></script>
  <script src="lib/owlcarousel/owl.carousel.min.js"></script>
  <script src="lib/lightbox/js/lightbox.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script type="text/javascript" src="./admin/js/lib/data-md5.js"></script>
  <script src="admin/js/perfil.js"></script>
  <script src="js/main.js"></script>

  <script>
    window.USER_DEP = "<?= $_SESSION['session_user']['codigo_departamento'] ?>";
    window.USER_MUN = "<?= $_SESSION['session_user']['codigo_municipio'] ?>";
    console.log("DEP USER:", window.USER_DEP, "MUN USER:", window.USER_MUN);
  </script>

  <script>
    // Ayuda pro
    document.getElementById('btnAyuda')?.addEventListener('click', () => {
      Swal.fire({
        title: "¿Cómo usar esta vista?",
        html:
          "<div style='text-align:left;font-weight:700;color:#334155;line-height:1.35'>" +
          "1) Haz clic en un <b>departamento</b> en el mapa.<br>" +
          "2) Selecciona el <b>tipo</b>: Sondeo o Encuesta.<br>" +
          "3) Elige la <b>consulta</b> o la <b>pregunta</b> según corresponda.<br><br>" +
          "<span style='color:#64748b;'>El panel flotante se ajusta automáticamente en móvil.</span>" +
          "</div>",
        icon: "info",
        confirmButtonText: "Entendido",
        allowOutsideClick: true
      });
    });
  </script>

  <script src="admin/js/visualizar.js"></script>

</body>
</html>
