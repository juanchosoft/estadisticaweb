<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['session_user']) || !isset($_SESSION['session_user']['id'])) {
  header('Location: index.php');
  exit();
}
require './admin/include/generic_classes.php';

// Obtener nombre del usuario para el mensaje
$nombreUsuario = $_SESSION['session_user']['nombre_completo'] ?? $_SESSION['session_user']['usuario'] ?? 'Usuario';
$partes = explode(' ', trim($nombreUsuario));
$primerNombre = $partes[0] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">

<?php include './admin/include/head2.php'; ?>
<?php include './admin/include/menusecond.php'; ?>

<head>
  <style>
    :root{
      --brand:#13357b;
      --brand-2:#0b1a89;
      --ink:#0f172a;
      --muted:#64748b;
      --card:#ffffff;
      --bg:#f6f8fc;
      --r-xl:22px;
      --r-lg:18px;
      --r-md:14px;
      --shadow: 0 18px 45px rgba(2,6,23,.10);
      --shadow-soft: 0 10px 25px rgba(2,6,23,.08);
      --border: 1px solid rgba(15,23,42,.10);
    }

    body{ background: var(--bg); }

    /* ====== Layout principal ====== */
    .page-shell{
      width: 100%;
      max-width: 1320px;
      margin: 0 auto;
      padding: 18px 16px 28px;
    }

    .main-card{
      background: var(--card);
      border-radius: var(--r-xl);
      box-shadow: var(--shadow);
      border: var(--border);
      padding: 18px;
    }

    .grid-wrap{
      display:grid;
      grid-template-columns: 1.25fr .75fr;
      gap: 18px;
      align-items: stretch;
    }
    @media (max-width: 992px){
      .grid-wrap{ grid-template-columns: 1fr; }
    }

    /* ====== Panel mapa ====== */
    .map-panel{
      background: #fff;
      border-radius: var(--r-xl);
      border: var(--border);
      box-shadow: var(--shadow-soft);
      overflow: hidden;
      position: relative;
      min-height: 640px;
      display:flex;
      flex-direction: column;
    }

    /* Barra superior tipo tip */
    .map-tip{
      display:flex;
      align-items:center;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 14px;
      border-bottom: 1px solid rgba(15,23,42,.08);
      background: linear-gradient(180deg, rgba(19,53,123,.08), rgba(19,53,123,.02));
    }

    .tip-left{
      display:flex;
      gap: 10px;
      align-items:flex-start;
    }

    .tip-icon{
      width: 38px;
      height: 38px;
      border-radius: 12px;
      display:flex;
      align-items:center;
      justify-content:center;
      background: rgba(19,53,123,.12);
      border: 1px solid rgba(19,53,123,.18);
      color: var(--brand);
      flex: 0 0 auto;
    }

    .tip-title{
      font-weight: 800;
      color: var(--ink);
      font-size: 14px;
      line-height: 1.1;
      margin: 0;
    }
    .tip-sub{
      margin: 2px 0 0;
      color: var(--muted);
      font-size: 13px;
    }

    .tip-btn{
      border-radius: 999px;
      border: 1px solid rgba(19,53,123,.20);
      color: var(--brand);
      background: #fff;
      font-weight: 800;
      padding: 8px 12px;
      display:inline-flex;
      align-items:center;
      gap: 8px;
      transition: .18s ease;
      box-shadow: 0 6px 14px rgba(2,6,23,.06);
    }
    .tip-btn:hover{
      transform: translateY(-1px);
      box-shadow: 0 10px 20px rgba(2,6,23,.10);
    }

    /* ====== STAGE del mapa (FIX DEFINITIVO) ====== */
    .map-stage{
      position: relative;
      flex: 1 1 auto;
      padding: 12px;
      display:flex;
      align-items:center;
      justify-content:center;
      background: #fff;
    }

    /* Este wrapper fuerza un viewport real para el SVG */
    #mapaContainer{
      width: 100%;
      height: 100%;
      min-height: 520px;   /* ✅ fuerza tamaño mínimo */
      display:flex;
      align-items:center;
      justify-content:center;
    }

    /* Si el include mete un div extra, esto lo domina */
    #mapaContainer > *{
      width: 100%;
      height: 100%;
    }

    /* Fuerza al SVG a ocupar el stage sin deformarse */
    #mapaContainer svg{
      width: 100% !important;
      height: 100% !important;
      max-width: 980px;   /* ✅ se ve grande y centrado */
      max-height: 560px;
      display:block;
      margin: 0 auto;
    }

    /* Responsive: en móvil el mapa queda grande */
    @media (max-width: 992px){
      .map-panel{ min-height: 520px; }
      #mapaContainer{ min-height: 440px; }
      #mapaContainer svg{ max-height: 520px; max-width: 100%; }
    }

    /* Hover pro para paths */
    #mapaContainer svg g:hover path{
      filter: brightness(1.06);
      transition: .18s ease;
    }

    /* ====== Panel resumen ====== */
    .summary-panel{
      background:#fff;
      border-radius: var(--r-xl);
      border: var(--border);
      box-shadow: var(--shadow-soft);
      padding: 16px;
      min-height: 640px;
      display:flex;
      flex-direction: column;
      gap: 12px;
    }

    .summary-title{
      display:flex;
      gap:10px;
      align-items:center;
      justify-content:center;
      margin-top: 4px;
    }

    .summary-title h4{
      margin:0;
      font-weight: 900;
      color: var(--brand);
      letter-spacing: .2px;
    }

    .summary-sub{
      margin:0;
      color: var(--muted);
      font-size: 13px;
      text-align:center;
      line-height:1.35;
    }

    .kpi{
      display:flex;
      align-items:center;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 12px;
      border-radius: 16px;
      border: 1px solid rgba(15,23,42,.08);
      background: #fff;
      box-shadow: 0 10px 22px rgba(2,6,23,.06);
    }

    .kpi-left{
      display:flex;
      align-items:center;
      gap: 10px;
    }

    .kpi-ico{
      width: 40px;
      height: 40px;
      border-radius: 14px;
      display:flex;
      align-items:center;
      justify-content:center;
      color: #fff;
      flex: 0 0 auto;
    }

    .kpi-meta{ line-height: 1.1; }
    .kpi-meta b{ display:block; font-size: 14px; color: var(--ink); }
    .kpi-meta small{ color: var(--muted); font-weight: 700; }
    .kpi-val{
      font-weight: 900;
      font-size: 18px;
      color: var(--ink);
      min-width: 26px;
      text-align:right;
    }

    .cta{
      margin-top: auto;
      border-radius: 18px;
      background: linear-gradient(135deg, #0b1a89, #13357b);
      padding: 14px;
      color: #fff;
      display:flex;
      align-items:center;
      justify-content: space-between;
      gap: 12px;
      box-shadow: 0 14px 35px rgba(11,26,137,.25);
    }

    .cta p{ margin:0; font-weight: 800; }
    .cta small{ display:block; opacity:.85; font-weight: 600; margin-top: 2px; }

    .cta-btn{
      border: none;
      border-radius: 14px;
      padding: 10px 14px;
      font-weight: 900;
      background: #fff;
      color: var(--brand-2);
      display:inline-flex;
      align-items:center;
      gap: 10px;
      box-shadow: 0 10px 22px rgba(2,6,23,.18);
      transition: .18s ease;
    }
    .cta-btn:hover{ transform: translateY(-1px); }

    /* ===== Modal pendientes ===== */
    .alert-modal .modal-content{
      border-radius: 22px;
      border: 1px solid rgba(15,23,42,.10);
      box-shadow: 0 20px 60px rgba(2,6,23,.18);
      overflow:hidden;
    }
    .alert-modal .modal-header{
      background: linear-gradient(135deg, #0b1a89, #13357b);
      border: none;
      padding: 14px 16px;
    }
    .btn-close-custom{
      border:none;
      background: rgba(255,255,255,.12);
      color:#fff;
      width: 36px;
      height: 36px;
      border-radius: 12px;
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .btn-option{
      border-radius: 16px;
      padding: 14px 12px;
      background: #0b1a89;
      color: #fff;
      border: 1px solid rgba(255,255,255,.18);
      box-shadow: 0 12px 25px rgba(11,26,137,.22);
      transition: .18s ease;
    }
    .btn-option:hover{
      transform: translateY(-1px);
      background: #13357b;
      color:#fff;
    }

    /* Pequeña corrección: elimina "mocho" por headers sticky */
    .page-offset{
      padding-top: 10px;
    }
  </style>
</head>

<body>

<!-- Spinner -->
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
  <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
    <span class="sr-only">Loading...</span>
  </div>
</div>

<!-- Modal de Alerta Pendientes -->
<div class="modal fade alert-modal" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-white" id="alertModalLabel">
          <i class="fas fa-info-circle me-2"></i>¡Formularios Pendientes!
        </h5>
        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body p-4">
        <h4 class="mb-1" style="font-weight: 900; color:#0f172a;">
          HOLA, <?= htmlspecialchars($primerNombre); ?>
        </h4>
        <p class="text-muted mb-3">Tienes formularios pendientes por responder</p>

        <div class="alert alert-info mb-3" style="border-radius: 16px;">
          <i class="fas fa-lightbulb me-2"></i>
          Elige el formulario que quieres completar. Te llevaremos directo.
        </div>

        <div class="d-grid gap-2">
          <button type="button" class="btn btn-option d-flex align-items-center justify-content-between" onclick="redirectToEncuesta()">
            <div class="d-flex align-items-center gap-3">
              <i class="fas fa-clipboard-list fa-lg"></i>
              <div class="text-start">
                <strong>Responder Encuesta</strong>
                <small class="d-block text-white-50">Completar formulario de encuesta</small>
              </div>
            </div>
            <i class="fas fa-arrow-right"></i>
          </button>

          <button type="button" class="btn btn-option d-flex align-items-center justify-content-between" onclick="redirectToSondeo()">
            <div class="d-flex align-items-center gap-3">
              <i class="fas fa-poll fa-lg"></i>
              <div class="text-start">
                <strong>Responder Sondeo</strong>
                <small class="d-block text-white-50">Completar formulario de sondeo</small>
              </div>
            </div>
            <i class="fas fa-arrow-right"></i>
          </button>

          <button type="button" class="btn btn-option d-flex align-items-center justify-content-between" onclick="redirectToEstudio()">
            <div class="d-flex align-items-center gap-3">
              <i class="fas fa-chart-bar fa-lg"></i>
              <div class="text-start">
                <strong>Responder Estudio</strong>
                <small class="d-block text-white-50">Completar formulario de estudio</small>
              </div>
            </div>
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CONTENIDO -->
<div class="page-shell page-offset">
  <div class="main-card">
    <div class="grid-wrap">

      <!-- MAPA -->
      <div class="map-panel">
        <div class="map-tip">
          <div class="tip-left">
            <div class="tip-icon">
              <i class="fas fa-map-marked-alt"></i>
            </div>
            <div>
              <p class="tip-title">Tip: haz clic en el mapa</p>
              <p class="tip-sub">Selecciona un departamento para ver tus pendientes.</p>
            </div>
          </div>

          <button type="button" class="tip-btn" onclick="tryOpenPendientesModal()">
            <i class="fas fa-list-ul"></i>
            Mis pendientes
          </button>
        </div>

        <div class="map-stage">
          <!-- ✅ Contenedor fijo del SVG -->
          <div id="mapaContainer">
            <?php include './admin/mapa_colombia/mapa.php'; ?>
          </div>
        </div>
      </div>

      <!-- RESUMEN -->
      <div class="summary-panel">
        <div class="summary-title">
          <i class="fas fa-list-alt" style="color:var(--brand);"></i>
          <h4>Tu resumen</h4>
        </div>
        <p class="summary-sub">
          Aquí verás en tiempo real cuántos formularios te faltan por completar.
        </p>

        <div class="kpi">
          <div class="kpi-left">
            <div class="kpi-ico" style="background:#16a34a;">
              <i class="fas fa-poll"></i>
            </div>
            <div class="kpi-meta">
              <b>Sondeos</b>
              <small>Pendientes</small>
            </div>
          </div>
          <div class="kpi-val" id="pendientes_sondeos">0</div>
        </div>

        <div class="kpi">
          <div class="kpi-left">
            <div class="kpi-ico" style="background:#0ea5e9;">
              <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="kpi-meta">
              <b>Encuestas</b>
              <small>Pendientes</small>
            </div>
          </div>
          <div class="kpi-val" id="pendientes_encuestas">0</div>
        </div>

        <div class="kpi">
          <div class="kpi-left">
            <div class="kpi-ico" style="background:#f97316;">
              <i class="fas fa-chart-bar"></i>
            </div>
            <div class="kpi-meta">
              <b>Estudios</b>
              <small>Pendientes</small>
            </div>
          </div>
          <div class="kpi-val" id="pendientes_estudios">0</div>
        </div>

        <div class="cta">
          <div>
            <p>¿Listo para responder?</p>
            <small>Abre el modal y elige el formulario que quieres completar.</small>
          </div>
          <button class="cta-btn" type="button" onclick="tryOpenPendientesModal()">
            <i class="fas fa-play"></i> Empezar
          </button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="./admin/js/lib/data-md5.js"></script>
<script src="admin/js/perfil.js"></script>
<script src="admin/js/responder.js"></script>
<script src="js/main.js"></script>

<script>
/**
 * ✅ FIX DEFINITIVO: normaliza SVG para que escale bien
 * - añade viewBox si no existe
 * - fuerza preserveAspectRatio
 * - elimina width/height rígidos si existen
 */
function normalizeMapaSvg(){
  const svg = document.querySelector('#mapaContainer svg');
  if(!svg) return;

  // Si viene con width/height fijo, los quitamos para permitir responsive real
  svg.removeAttribute('width');
  svg.removeAttribute('height');

  // Crear viewBox si no existe
  if(!svg.getAttribute('viewBox')){
    // intentamos con bbox (cuando ya está en DOM)
    const box = svg.getBBox ? svg.getBBox() : null;
    if(box && box.width && box.height){
      svg.setAttribute('viewBox', `${box.x} ${box.y} ${box.width} ${box.height}`);
    }else{
      // fallback razonable
      svg.setAttribute('viewBox', '0 0 1000 800');
    }
  }

  // Mantener proporción y centrado
  svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');

  // Asegura que tome el alto del contenedor
  svg.style.width = '100%';
  svg.style.height = '100%';
}

// Abre modal pendientes si existe (o fallback a alert)
function tryOpenPendientesModal(){
  const el = document.getElementById('alertModal');
  if(!el){
    Swal.fire({ icon:'info', title:'Pendientes', text:'No se encontró el modal de pendientes.' });
    return;
  }
  const modal = new bootstrap.Modal(el);
  modal.show();
}

document.addEventListener('DOMContentLoaded', function(){
  // Normaliza el mapa apenas cargue
  setTimeout(normalizeMapaSvg, 80);

  // Re-normaliza por si el mapa se inyecta con delay
  setTimeout(normalizeMapaSvg, 400);

  // Recalcula al cambiar tamaño
  window.addEventListener('resize', () => setTimeout(normalizeMapaSvg, 50));
});
</script>

</body>
</html>
