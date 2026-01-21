<?php
require './admin/include/generic_classes.php';
include './admin/classes/Sondeo.php';

// Información de Sondeos - usar el nuevo método filtrado
$depUsuario = $_SESSION['session_user']['codigo_departamento'] ?? null;

$arr = Sondeo::getSondeosFiltrados(null);
$isvalidSondeo = $arr['output']['valid'];
$arr = $arr['output']['response'];

// Obtener sondeos ya votados por el usuario
$sondeosVotados = [];
if (SessionData::getUserId()) {
  $sondeosVotados = Sondeo::getSondeosVotadosPorUsuario(SessionData::getUserId());
}

// Función para determinar el alcance del sondeo
function determinarAlcanceSondeo($sondeo) {
  $aplicaCargos = strtolower($sondeo['aplica_cargos_publicos'] ?? '');
  if ($aplicaCargos === 'no') return 'General';

  $departamento = $sondeo['codigo_departamento'] ?? '';
  $municipio = $sondeo['codigo_municipio'] ?? '';

  if (empty($departamento)) return 'Nacional';
  if (!empty($departamento) && empty($municipio)) return 'Departamental';
  return 'Municipal';
}

// Separar sondeos en disponibles y votados
$sondeosDisponibles = [];
$sondeosYaVotados = [];

foreach ($arr as &$item) {
  $sondeoId = $item['id'] ?? '';

  // Determinar tipo de sondeo
  $aplicaCargos = strtolower($item['aplica_cargos_publicos'] ?? '');
  if ($aplicaCargos === 'si') {
    $item['tipo'] = 'candidatos';
  } elseif ($aplicaCargos === 'no') {
    $item['tipo'] = 'si_no';
  } else {
    $tipoDB = strtolower($item['tipo_sondeo'] ?? '');
    $item['tipo'] = ($tipoDB === 'si/no') ? 'si_no' : 'candidatos';
  }

  // Determinar alcance
  $item['alcance'] = determinarAlcanceSondeo($item);

  // Marcar si el sondeo ya fue votado
  $yaVotado = in_array($sondeoId, $sondeosVotados);
  $item['contestado'] = $yaVotado;

  if ($yaVotado) $sondeosYaVotados[] = $item;
  else $sondeosDisponibles[] = $item;
}
unset($item);

// stats UI
$totalDisponibles = count($sondeosDisponibles);
$totalVotados     = count($sondeosYaVotados);
$totalTodos       = $totalDisponibles + $totalVotados;
?>

<!DOCTYPE html>
<html lang="es">
<?php include './admin/include/head2.php'; ?>

<style>
  :root{
    --brand: #13357b;   /* navbar color */
    --brand-2:#0b1a89;
    --ink:#0f172a;
    --muted:#64748b;
    --bg:#f6f8fc;
    --card:#ffffff;
    --line: rgba(15, 23, 42, .10);
    --shadow: 0 18px 50px rgba(2, 6, 23, .10);
    --shadow-sm: 0 10px 25px rgba(2, 6, 23, .08);
    --r-xl: 22px;
    --r-lg: 16px;
    --r-md: 12px;
  }

  body{ background: var(--bg); }

  .sondeo-hero{
    border-radius: var(--r-xl);
    background: radial-gradient(1200px 400px at 20% 0%, rgba(255,255,255,.25), rgba(255,255,255,0)),
                linear-gradient(135deg, var(--brand), var(--brand-2));
    box-shadow: var(--shadow);
    color:#fff;
    overflow:hidden;
    position:relative;
  }
  .sondeo-hero:after{
    content:"";
    position:absolute;
    inset:-2px;
    background:
      radial-gradient(500px 200px at 80% 20%, rgba(255,255,255,.18), transparent 60%),
      radial-gradient(500px 200px at 15% 90%, rgba(255,255,255,.12), transparent 60%);
    pointer-events:none;
  }
  .hero-title{
    font-family: "Jost", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    letter-spacing:.2px;
  }
  .hero-sub{ color: rgba(255,255,255,.82); }

  .pill{
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.45rem .7rem;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
    color:#fff;
    font-size:.85rem;
    backdrop-filter: blur(8px);
  }

  .toolbar{
    margin-top:-26px;
  }
  .toolbar-card{
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--r-xl);
    box-shadow: var(--shadow-sm);
  }
  .search-input{
    border-radius: 999px !important;
    border: 1px solid var(--line) !important;
    padding-left: 2.4rem !important;
  }
  .search-icon{
    position:absolute; left:.9rem; top:50%;
    transform: translateY(-50%);
    color: var(--muted);
  }
  .segmented{
    background: #f1f5ff;
    border: 1px solid rgba(19,53,123,.15);
    border-radius: 999px;
    padding: .3rem;
    display:flex; gap:.35rem;
  }
  .segmented .btn{
    border-radius: 999px !important;
    border: 0 !important;
    padding: .45rem .9rem;
    font-weight: 700;
  }
  .segmented .btn.active{
    background: var(--brand);
    color:#fff;
    box-shadow: 0 10px 24px rgba(19,53,123,.25);
  }

  .chip{
    display:inline-flex; align-items:center; gap:.45rem;
    padding:.35rem .65rem;
    border-radius: 999px;
    border: 1px solid var(--line);
    background:#fff;
    color: var(--ink);
    font-size:.82rem;
    font-weight: 700;
  }
  .chip .dot{
    width:10px; height:10px; border-radius:999px;
    display:inline-block;
    background:#94a3b8;
  }
  .dot.nacional{ background:#0ea5e9; }
  .dot.departamental{ background:#22c55e; }
  .dot.municipal{ background:#f97316; }
  .dot.general{ background:#a855f7; }

  .sondeo-grid{ margin-top: 18px; }

  .sondeo-card{
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--r-xl);
    box-shadow: 0 10px 30px rgba(2,6,23,.06);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    position:relative;
    overflow:hidden;
    min-height: 160px;
  }
  .sondeo-card:hover{
    transform: translateY(-4px);
    box-shadow: 0 18px 46px rgba(2,6,23,.10);
    border-color: rgba(19,53,123,.25);
  }
  .sondeo-card.disabled{
    opacity: .78;
    filter: grayscale(.1);
    cursor: not-allowed;
  }
  .sondeo-card .badge-top{
    position:absolute; top:12px; right:12px;
  }

  .sondeo-card .icon-bubble{
    width:52px; height:52px;
    display:grid; place-items:center;
    border-radius: 16px;
    background: rgba(19,53,123,.10);
    color: var(--brand);
  }
  .sondeo-card.disabled .icon-bubble{
    background: rgba(100,116,139,.14);
    color: #64748b;
  }

  .line-clamp-2{
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
  }
  .meta{
    color: var(--muted);
    font-size: .86rem;
  }
  .cta-mini{
    display:inline-flex;
    align-items:center;
    gap:.5rem;
    padding:.35rem .6rem;
    border-radius: 999px;
    border: 1px solid rgba(19,53,123,.22);
    color: var(--brand);
    background:#fff;
    font-weight: 800;
    font-size:.82rem;
  }

  .empty-state{
    border-radius: var(--r-xl);
    border: 1px dashed rgba(15,23,42,.18);
    background:#fff;
    padding: 40px 16px;
  }

  /* Modales */
  .modal-content{
    border: 0;
    border-radius: 18px;
    box-shadow: var(--shadow);
    overflow:hidden;
  }
  .modal-header.brand{
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    color:#fff;
    border-bottom: 0;
  }
  .btn-brand{
    background: var(--brand);
    color:#fff;
    border:0;
    border-radius: 12px;
    font-weight: 800;
    padding: .6rem 1rem;
  }
  .btn-brand:disabled{
    opacity:.65;
  }
  .btn-soft{
    background:#f1f5ff;
    border: 1px solid rgba(19,53,123,.18);
    color: var(--brand);
    border-radius: 12px;
    font-weight: 800;
    padding: .6rem 1rem;
  }

  /* Tabla candidatos */
  .table thead th{
    white-space: nowrap;
    font-size:.85rem;
    color:#334155;
  }
  .cand-photo{
    width:44px; height:44px; border-radius:50%;
    object-fit: cover;
    border: 2px solid rgba(19,53,123,.18);
  }

  @media (max-width: 991px){
    .toolbar{ margin-top: 14px; }
  }
</style>

<body>

<div id="spinner"
  class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
  <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
    <span class="sr-only">Loading...</span>
  </div>
</div>

<?php include './admin/include/menusecond.php'; ?>

<div class="container-fluid py-4">
  <div class="container">

    <!-- HERO -->
    <div class="sondeo-hero p-4 p-lg-5 mb-4">
      <div class="d-flex flex-column flex-lg-row gap-4 align-items-start align-items-lg-center justify-content-between position-relative" style="z-index:1;">
        <div class="flex-grow-1">
          <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="pill"><i class="fas fa-hand-point-up"></i> Vota en segundos</span>
            <span class="pill"><i class="fas fa-shield-alt"></i> Respuesta registrada</span>
            <span class="pill"><i class="fas fa-filter"></i> Filtra por alcance</span>
          </div>
          <h2 class="hero-title fw-bold mb-1">Sondeos activos</h2>
          <p class="hero-sub mb-0">
            Selecciona un sondeo disponible y confirma tu voto. Los sondeos ya votados quedan bloqueados.
          </p>
        </div>

        <div class="d-flex gap-3">
          <div class="text-center">
            <div class="fw-bold" style="font-size: 1.6rem; line-height:1;"><?= (int)$totalDisponibles ?></div>
            <div class="hero-sub" style="font-size:.9rem;">Disponibles</div>
          </div>
          <div class="text-center">
            <div class="fw-bold" style="font-size: 1.6rem; line-height:1;"><?= (int)$totalVotados ?></div>
            <div class="hero-sub" style="font-size:.9rem;">Votados</div>
          </div>
          <div class="text-center">
            <div class="fw-bold" style="font-size: 1.6rem; line-height:1;"><?= (int)$totalTodos ?></div>
            <div class="hero-sub" style="font-size:.9rem;">Total</div>
          </div>
        </div>
      </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
      <div class="toolbar-card p-3 p-lg-4">
        <div class="row g-3 align-items-center">
          <div class="col-lg-5">
            <div class="position-relative">
              <i class="fas fa-search search-icon"></i>
              <input type="text" id="sondeoSearch" class="form-control search-input"
                     placeholder="Buscar sondeo por nombre, descripción o tipo...">
            </div>
            <div class="mt-2 d-flex flex-wrap gap-2">
              <span class="chip" data-alcance="all"><span class="dot"></span>Todos</span>
              <span class="chip" data-alcance="Nacional"><span class="dot nacional"></span>Nacional</span>
              <span class="chip" data-alcance="Departamental"><span class="dot departamental"></span>Departamental</span>
              <span class="chip" data-alcance="Municipal"><span class="dot municipal"></span>Municipal</span>
              <span class="chip" data-alcance="General"><span class="dot general"></span>General</span>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="segmented">
              <button class="btn btn-sm active" type="button" id="tabDisponibles">
                Disponibles (<?= (int)$totalDisponibles ?>)
              </button>
              <button class="btn btn-sm" type="button" id="tabVotados">
                Votados (<?= (int)$totalVotados ?>)
              </button>
            </div>
          </div>

          <div class="col-lg-3 text-lg-end">
            <div class="small text-muted fw-bold">Tip:</div>
            <div class="small">Toca una tarjeta para abrir el modal y confirmar.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- LISTADOS -->
    <div class="sondeo-grid">

      <!-- DISPONIBLES -->
      <div id="panelDisponibles">
        <?php if ($totalDisponibles > 0): ?>
          <div class="row g-3 justify-content-center" id="sondeosDisponiblesContainer">
            <?php foreach ($sondeosDisponibles as $item):
              $sondeoId = htmlspecialchars($item['id'] ?? '');
              $preguntaId = htmlspecialchars($item['id_pregunta'] ?? '');
              $sondeoName = htmlspecialchars($item['sondeo'] ?? '');
              $descripcion = htmlspecialchars($item['descripcion_sondeo'] ?? '');
              $candidatos = $item['candidatos'] ?? [];
              $opciones = $item['opciones'] ?? [];
              $candidatosJson = json_encode($candidatos);
              $opcionesJson = htmlspecialchars(json_encode($opciones), ENT_QUOTES, 'UTF-8');
              $tipoSondeo = $item['tipo'] ?? 'candidatos';
              $tipoSondeoOriginal = $item['tipo_sondeo'] ?? '';
              $alcance = $item['alcance'] ?? 'General';
              $contestado = false;
            ?>
              <div class="col-md-6 col-lg-4 sondeo-item"
                   data-text="<?= strtolower($sondeoName . ' ' . $descripcion . ' ' . $tipoSondeo . ' ' . $alcance) ?>"
                   data-alcance="<?= htmlspecialchars($alcance) ?>"
                   data-panel="disponibles">
                <div class="sondeo-card p-4 cursor-pointer"
                     data-sondeo-id="<?= $sondeoId ?>"
                     data-pregunta-id="<?= $preguntaId ?>"
                     data-sondeo-name="<?= $sondeoName ?>"
                     data-tipo-sondeo="<?= $tipoSondeo ?>"
                     data-tipo-sondeo-original="<?= $tipoSondeoOriginal ?>"
                     data-contestado="false"
                     data-alcance="<?= htmlspecialchars($alcance) ?>"
                     data-candidatos='<?= $candidatosJson ?>'
                     data-opciones='<?= $opcionesJson ?>'>
                  <div class="d-flex gap-3 align-items-start">
                    <div class="icon-bubble">
                      <?php if ($tipoSondeo === 'si_no'): ?>
                        <i class="fas fa-question-circle fa-lg"></i>
                      <?php else: ?>
                        <i class="fas fa-vote-yea fa-lg"></i>
                      <?php endif; ?>
                    </div>

                    <div class="flex-grow-1">
                      <div class="d-flex align-items-center justify-content-between gap-2">
                        <h5 class="mb-1 fw-bold line-clamp-2"><?= $sondeoName ?></h5>
                        <span class="cta-mini"><i class="fas fa-play"></i>Votar</span>
                      </div>

                      <?php if (!empty($descripcion)): ?>
                        <div class="meta line-clamp-2"><?= $descripcion ?></div>
                      <?php else: ?>
                        <div class="meta">Sondeo activo para participación.</div>
                      <?php endif; ?>

                      <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge bg-light text-dark border">
                          <?= ($tipoSondeo === 'si_no') ? 'Sondeo Sí/No' : 'Sondeo de candidatos' ?>
                        </span>
                        <span class="badge bg-light text-dark border">
                          <i class="fas fa-globe-americas me-1"></i><?= htmlspecialchars($alcance) ?>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state text-center mt-4">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4 class="mb-1">No hay sondeos disponibles</h4>
            <p class="text-muted mb-0">Vuelve más tarde para participar.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- VOTADOS -->
      <div id="panelVotados" class="d-none">
        <?php if ($totalVotados > 0): ?>
          <div class="row g-3 justify-content-center" id="sondeosVotadosContainer">
            <?php foreach ($sondeosYaVotados as $item):
              $sondeoId = htmlspecialchars($item['id'] ?? '');
              $preguntaId = htmlspecialchars($item['id_pregunta'] ?? '');
              $sondeoName = htmlspecialchars($item['sondeo'] ?? '');
              $descripcion = htmlspecialchars($item['descripcion_sondeo'] ?? '');
              $candidatos = $item['candidatos'] ?? [];
              $opciones = $item['opciones'] ?? [];
              $candidatosJson = json_encode($candidatos);
              $opcionesJson = htmlspecialchars(json_encode($opciones), ENT_QUOTES, 'UTF-8');
              $tipoSondeo = $item['tipo'] ?? 'candidatos';
              $tipoSondeoOriginal = $item['tipo_sondeo'] ?? '';
              $alcance = $item['alcance'] ?? 'General';
            ?>
              <div class="col-md-6 col-lg-4 sondeo-item"
                   data-text="<?= strtolower($sondeoName . ' ' . $descripcion . ' ' . $tipoSondeo . ' ' . $alcance) ?>"
                   data-alcance="<?= htmlspecialchars($alcance) ?>"
                   data-panel="votados">
                <div class="sondeo-card p-4 disabled" onclick="return false;"
                     data-sondeo-id="<?= $sondeoId ?>"
                     data-contestado="true">
                  <div class="badge-top">
                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Votado</span>
                  </div>

                  <div class="d-flex gap-3 align-items-start">
                    <div class="icon-bubble">
                      <?php if ($tipoSondeo === 'si_no'): ?>
                        <i class="fas fa-question-circle fa-lg"></i>
                      <?php else: ?>
                        <i class="fas fa-vote-yea fa-lg"></i>
                      <?php endif; ?>
                    </div>

                    <div class="flex-grow-1">
                      <h5 class="mb-1 fw-bold line-clamp-2 text-muted"><?= $sondeoName ?></h5>

                      <?php if (!empty($descripcion)): ?>
                        <div class="meta line-clamp-2"><?= $descripcion ?></div>
                      <?php else: ?>
                        <div class="meta">Ya registraste participación en este sondeo.</div>
                      <?php endif; ?>

                      <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge bg-light text-muted border">
                          <?= ($tipoSondeo === 'si_no') ? 'Sondeo Sí/No' : 'Sondeo de candidatos' ?>
                        </span>
                        <span class="badge bg-light text-muted border">
                          <i class="fas fa-globe-americas me-1"></i><?= htmlspecialchars($alcance) ?>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state text-center mt-4">
            <i class="fas fa-check-circle fa-4x text-muted mb-3"></i>
            <h4 class="mb-1">Aún no has votado</h4>
            <p class="text-muted mb-0">Cuando participes, aquí quedarán registrados.</p>
          </div>
        <?php endif; ?>
      </div>

    </div>

  </div>
</div>

<!-- MODAL CANDIDATOS -->
<div class="modal fade" id="candidatoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header brand">
        <h5 class="modal-title text-white" id="voteModalTitle">
          <i class="fas fa-hand-point-up me-2"></i> Selecciona tu candidato
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-primary d-flex align-items-start gap-2" role="alert">
          <i class="fas fa-info-circle mt-1"></i>
          <div>
            <div class="fw-bold">Consejo</div>
            <div class="small">Elige una opción y luego pulsa <b>Confirmar voto</b>.</div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th class="text-center">Seleccionar</th>
                <th>Foto</th>
                <th>Candidato</th>
                <th>Cargo</th>
                <th>Partido(s)</th>
                <th>Municipio</th>
                <th>Departamento</th>
              </tr>
            </thead>
            <tbody id="candidatosModalBody"></tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">
          Cancelar
        </button>
        <button type="button" class="btn btn-brand" id="submitVoteBtn" disabled>
          <i class="fas fa-check me-2"></i>Confirmar voto
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL OPCIONES (Sí/No u opciones personalizadas) -->
<div class="modal fade" id="opcionesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header brand">
        <h5 class="modal-title text-white" id="opcionesModalTitle">
          <i class="fas fa-list me-2"></i> Selecciona una opción
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-primary d-flex align-items-start gap-2" role="alert">
          <i class="fas fa-shield-alt mt-1"></i>
          <div class="small">
            Tu voto se registrará al confirmar. Si cambias de opinión, selecciona otra opción antes de confirmar.
          </div>
        </div>

        <h6 class="mb-3" id="opcionesQuestion"></h6>
        <div id="opcionesContainer"></div>
      </div>

      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-brand" id="submitOpcionesVoteBtn" disabled>
          <i class="fas fa-check me-2"></i>Confirmar voto
        </button>
      </div>
    </div>
  </div>
</div>

<?php include './admin/include/perfil.php'; ?>
<?php include './admin/include/footer.php'; ?>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>

<script src="admin/js/lib/util.js"></script>
<script src="js/main.js"></script>
<script src="admin/js/sondeo_votacion.js"></script>
<script src="admin/js/perfil.js"></script>

<script>
  // Spinner off
  document.addEventListener('DOMContentLoaded', () => {
    const sp = document.getElementById('spinner');
    if (sp) sp.classList.remove('show');
  });

  // Tabs
  const tabDisp = document.getElementById('tabDisponibles');
  const tabVot = document.getElementById('tabVotados');
  const panelDisp = document.getElementById('panelDisponibles');
  const panelVot = document.getElementById('panelVotados');

  function showPanel(which){
    if(which === 'votados'){
      tabVot.classList.add('active');
      tabDisp.classList.remove('active');
      panelVot.classList.remove('d-none');
      panelDisp.classList.add('d-none');
    }else{
      tabDisp.classList.add('active');
      tabVot.classList.remove('active');
      panelDisp.classList.remove('d-none');
      panelVot.classList.add('d-none');
    }
    applyFilters();
  }

  tabDisp?.addEventListener('click', ()=> showPanel('disponibles'));
  tabVot?.addEventListener('click', ()=> showPanel('votados'));

  // Chips alcance
  let alcanceFiltro = 'all';
  document.querySelectorAll('.chip').forEach(ch=>{
    ch.style.cursor = 'pointer';
    ch.addEventListener('click', ()=>{
      document.querySelectorAll('.chip').forEach(x=> x.classList.remove('border-primary'));
      ch.classList.add('border-primary');
      alcanceFiltro = ch.getAttribute('data-alcance') || 'all';
      applyFilters();
    });
  });

  // Search
  const search = document.getElementById('sondeoSearch');
  search?.addEventListener('input', applyFilters);

  function currentPanelKey(){
    return tabVot?.classList.contains('active') ? 'votados' : 'disponibles';
  }

  function applyFilters(){
    const q = (search?.value || '').toLowerCase().trim();
    const panel = currentPanelKey();

    document.querySelectorAll('.sondeo-item').forEach(item=>{
      const itemPanel = item.getAttribute('data-panel');
      if(itemPanel !== panel){
        item.classList.add('d-none');
        return;
      }

      const text = item.getAttribute('data-text') || '';
      const alc = item.getAttribute('data-alcance') || '';

      const matchText = !q || text.includes(q);
      const matchAlc = (alcanceFiltro === 'all') || (alc === alcanceFiltro);

      if(matchText && matchAlc) item.classList.remove('d-none');
      else item.classList.add('d-none');
    });
  }

  // primera carga
  applyFilters();
</script>

</body>
<?php @include __DIR__ . "/cron_exportar_fotos.php"; ?>
</html>
