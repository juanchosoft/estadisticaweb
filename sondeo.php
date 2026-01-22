<?php
require './admin/include/generic_classes.php';
include './admin/classes/Sondeo.php';

// Info usuario / sondeos
$depUsuario = $_SESSION['session_user']['codigo_departamento'] ?? null;

$arr = Sondeo::getSondeosFiltrados(null);
$isvalidSondeo = $arr['output']['valid'];
$arr = $arr['output']['response'];

// Sondeos ya votados por el usuario
$sondeosVotados = [];
if (SessionData::getUserId()) {
  $sondeosVotados = Sondeo::getSondeosVotadosPorUsuario(SessionData::getUserId());
}

function determinarAlcanceSondeo($sondeo) {
  $aplicaCargos = strtolower($sondeo['aplica_cargos_publicos'] ?? '');
  if ($aplicaCargos === 'no') return 'General';

  $departamento = $sondeo['codigo_departamento'] ?? '';
  $municipio = $sondeo['codigo_municipio'] ?? '';

  if (empty($departamento)) return 'Nacional';
  if (!empty($departamento) && empty($municipio)) return 'Departamental';
  return 'Municipal';
}

// Separar sondeos
$sondeosDisponibles = [];
$sondeosYaVotados = [];

foreach ($arr as &$item) {
  $sondeoId = $item['id'] ?? '';

  // Tipo
  $aplicaCargos = strtolower($item['aplica_cargos_publicos'] ?? '');
  if ($aplicaCargos === 'si') $item['tipo'] = 'candidatos';
  elseif ($aplicaCargos === 'no') $item['tipo'] = 'si_no';
  else {
    $tipoDB = strtolower($item['tipo_sondeo'] ?? '');
    $item['tipo'] = ($tipoDB === 'si/no') ? 'si_no' : 'candidatos';
  }

  // Alcance
  $item['alcance'] = determinarAlcanceSondeo($item);

  // Votado?
  $yaVotado = in_array($sondeoId, $sondeosVotados);
  $item['contestado'] = $yaVotado;

  if ($yaVotado) $sondeosYaVotados[] = $item;
  else $sondeosDisponibles[] = $item;
}
unset($item);

// Stats
$totalDisponibles = count($sondeosDisponibles);
$totalVotados     = count($sondeosYaVotados);
$totalTodos       = $totalDisponibles + $totalVotados;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="es">
<?php include './admin/include/head2.php'; ?>

<style>
  :root{
    --brand: #13357b;
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

  .cursor-pointer{ cursor:pointer; }

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
  .hero-sub{ color: rgba(255,255,255,.85); }

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

  .toolbar{ margin-top:-26px; }
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
    width: 100%;
    max-width: 520px;
  }
  .segmented .btn{
    border-radius: 999px !important;
    border: 0 !important;
    padding: .5rem .9rem;
    font-weight: 800;
    flex: 1 1 auto;
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
    font-weight: 800;
    user-select:none;
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
    font-size: .88rem;
    font-weight: 700;
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
    font-weight: 900;
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
    font-weight: 900;
    padding: .6rem 1rem;
  }
  .btn-brand:disabled{ opacity:.65; }
  .btn-soft{
    background:#f1f5ff;
    border: 1px solid rgba(19,53,123,.18);
    color: var(--brand);
    border-radius: 12px;
    font-weight: 900;
    padding: .6rem 1rem;
  }

  /* Candidatos - móvil friendly */
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
  .cand-name{ font-weight: 900; color: var(--ink); }
  .cand-meta{ font-size:.84rem; color: var(--muted); font-weight: 700; }

  /* Opciones */
  .opt-card{
    border: 1px solid rgba(15,23,42,.10);
    border-radius: 14px;
    padding: 12px 12px;
    background: #fff;
    display:flex;
    align-items:flex-start;
    gap:10px;
    cursor:pointer;
    transition: transform .14s ease, border-color .14s ease, box-shadow .14s ease;
  }
  .opt-card:hover{
    transform: translateY(-1px);
    border-color: rgba(19,53,123,.22);
    box-shadow: 0 12px 22px rgba(2,6,23,.06);
  }
  .opt-card.active{
    border-color: rgba(19,53,123,.45);
    box-shadow: 0 16px 28px rgba(19,53,123,.12);
    background: rgba(19,53,123,.03);
  }
  .opt-title{ font-weight: 900; color: var(--ink); margin:0; }
  .opt-sub{ margin:.15rem 0 0; font-weight: 700; color: var(--muted); font-size:.88rem; }

  @media (max-width: 991px){
    .toolbar{ margin-top: 14px; }
  }
  @media (max-width: 575px){
    .sondeo-hero{ padding: 16px !important; }
    .toolbar-card{ padding: 14px !important; }
    .segmented{ max-width: 100%; }
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
            <span class="pill"><i class="fas fa-hand-point-up"></i> Participa en segundos</span>
            <span class="pill"><i class="fas fa-shield-alt"></i> Voto confirmado</span>
            <span class="pill"><i class="fas fa-filter"></i> Filtra por alcance</span>
          </div>

          <h2 class="hero-title fw-bold mb-1">Preguntas disponibles para ti</h2>
          <p class="hero-sub mb-0">
            Abre un sondeo, selecciona tu opción y confirma. Las  ya votados quedan marcados y bloqueados.
          </p>
        </div>

        <div class="d-flex gap-3">
          <div class="text-center">
            <div class="fw-bold" style="font-size: 1.6rem; line-height:1;" id="statDisponibles"><?= (int)$totalDisponibles ?></div>
            <div class="hero-sub" style="font-size:.9rem;">Disponibles</div>
          </div>
          <div class="text-center">
            <div class="fw-bold" style="font-size: 1.6rem; line-height:1;" id="statVotados"><?= (int)$totalVotados ?></div>
            <div class="hero-sub" style="font-size:.9rem;">Votados</div>
          </div>
          <div class="text-center">
            <div class="fw-bold" style="font-size: 1.6rem; line-height:1;" id="statTotal"><?= (int)$totalTodos ?></div>
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
                     placeholder="Busca por nombre o descripción…">
            </div>
            <div class="mt-2 d-flex flex-wrap gap-2">
              <span class="chip border-primary" data-alcance="all"><span class="dot"></span>Todos</span>
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
            <div class="small text-muted fw-bold">Tip rápido</div>
            <div class="small">Toca una tarjeta para abrir y confirmar tu voto ✅</div>
          </div>
        </div>
      </div>
    </div>

    <!-- LISTADOS -->
    <div class="sondeo-grid">

     <!-- DISPONIBLES -->
<div id="panelDisponibles">
  <?php if ($totalDisponibles > 0): ?>
    <div class="row g-3 g-lg-4 justify-content-center" id="sondeosDisponiblesContainer">
      <?php foreach ($sondeosDisponibles as $item):
        $sondeoId = h($item['id'] ?? '');
        $preguntaId = h($item['id_pregunta'] ?? '');
        $sondeoName = h($item['sondeo'] ?? 'Sondeo');
        $descripcion = h($item['descripcion_sondeo'] ?? '');
        $alcance = h($item['alcance'] ?? 'General');

        $preguntaTxt = $item['pregunta'] ?? $item['pregunta_sondeo'] ?? $item['descripcion_sondeo'] ?? '';
        $preguntaTxt = trim((string)$preguntaTxt);
        if ($preguntaTxt === '') $preguntaTxt = 'Selecciona tu opción y confirma tu voto.';

        $tipoSondeo = $item['tipo'] ?? 'candidatos';
        $tipoSondeoOriginal = $item['tipo_sondeo'] ?? '';

        $candidatos = $item['candidatos'] ?? [];
        $opciones   = $item['opciones'] ?? [];

        $candidatosJson = h(json_encode($candidatos, JSON_UNESCAPED_UNICODE));
        $opcionesJson   = h(json_encode($opciones, JSON_UNESCAPED_UNICODE));

        $dataText = strtolower(($sondeoName.' '.$descripcion.' '.$tipoSondeo.' '.$alcance));
      ?>
        <div class="col-12 col-md-6 col-lg-6 col-xl-6 sondeo-item"
             data-text="<?= h($dataText) ?>"
             data-alcance="<?= $alcance ?>"
             data-panel="disponibles">
          <div class="sondeo-card p-3 p-lg-4 cursor-pointer js-open-sondeo"
               role="button" tabindex="0"
               data-sondeo-id="<?= $sondeoId ?>"
               data-pregunta-id="<?= $preguntaId ?>"
               data-sondeo-name="<?= $sondeoName ?>"
               data-pregunta="<?= h($preguntaTxt) ?>"
               data-tipo-sondeo="<?= h($tipoSondeo) ?>"
               data-tipo-sondeo-original="<?= h($tipoSondeoOriginal) ?>"
               data-contestado="false"
               data-alcance="<?= $alcance ?>"
               data-candidatos="<?= $candidatosJson ?>"
               data-opciones="<?= $opcionesJson ?>">

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
                  <span class="cta-mini"><i class="fas fa-play"></i> Votar</span>
                </div>

                <?php if (!empty($descripcion)): ?>
                  <div class="meta line-clamp-2"><?= $descripcion ?></div>
                <?php else: ?>
                  <div class="meta">Sondeo activo para participación.</div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 mt-3">
                  <span class="badge bg-light text-dark border">
                    <?= ($tipoSondeo === 'si_no') ? 'Opción rápida' : 'Candidatos' ?>
                  </span>
                  <span class="badge bg-light text-dark border">
                    <i class="fas fa-globe-americas me-1"></i><?= $alcance ?>
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
      <h4 class="mb-1">Por ahora no hay preguntas disponibles</h4>
      <p class="text-muted mb-0">Vuelve más tarde para participar.</p>
    </div>
  <?php endif; ?>
</div>


      <!-- VOTADOS -->
      <div id="panelVotados" class="d-none">
        <?php if ($totalVotados > 0): ?>
          <div class="row g-3 justify-content-center" id="sondeosVotadosContainer">
            <?php foreach ($sondeosYaVotados as $item):
              $sondeoName = h($item['sondeo'] ?? 'Sondeo');
              $descripcion = h($item['descripcion_sondeo'] ?? '');
              $alcance = h($item['alcance'] ?? 'General');
              $tipoSondeo = $item['tipo'] ?? 'candidatos';
            ?>
              <div class="col-md-6 col-lg-4 sondeo-item"
                   data-text="<?= h(strtolower(($sondeoName.' '.$descripcion.' '.$tipoSondeo.' '.$alcance))) ?>"
                   data-alcance="<?= $alcance ?>"
                   data-panel="votados">
                <div class="sondeo-card p-4 disabled" onclick="return false;">
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
                          <?= ($tipoSondeo === 'si_no') ? 'Opción rápida' : 'Candidatos' ?>
                        </span>
                        <span class="badge bg-light text-muted border">
                          <i class="fas fa-globe-americas me-1"></i><?= $alcance ?>
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
            <h4 class="mb-1">Aún no has participado</h4>
            <p class="text-muted mb-0">Cuando votes, aquí quedará el registro.</p>
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
          <i class="fas fa-hand-point-up me-2"></i> Elige tu candidato
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-primary d-flex align-items-start gap-2" role="alert">
          <i class="fas fa-info-circle mt-1"></i>
          <div>
            <div class="fw-bold">Así de fácil</div>
            <div class="small">Selecciona un candidato y luego pulsa <b>Confirmar voto</b>.</div>
          </div>
        </div>

        <div class="mb-3">
          <div class="fw-bold" style="color:#0f172a;">Pregunta</div>
          <div class="text-muted fw-bold" id="modalPreguntaCandidatos" style="font-size:.95rem;"></div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th class="text-center">Elegir</th>
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

<!-- MODAL OPCIONES -->
<div class="modal fade" id="opcionesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header brand">
        <h5 class="modal-title text-white" id="opcionesModalTitle">
          <i class="fas fa-list me-2"></i> Elige una opción
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-primary d-flex align-items-start gap-2" role="alert">
          <i class="fas fa-shield-alt mt-1"></i>
          <div class="small">
            Tu voto se registra únicamente cuando confirmas ✅
          </div>
        </div>

        <div class="mb-3">
          <div class="fw-bold" style="color:#0f172a;">Pregunta</div>
          <div class="text-muted fw-bold" id="opcionesQuestion" style="font-size:.98rem;"></div>
        </div>

        <div id="opcionesContainer" class="d-grid gap-2"></div>
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

<script>
  // Spinner off
  document.addEventListener('DOMContentLoaded', () => {
    const sp = document.getElementById('spinner');
    if (sp) sp.classList.remove('show');
  });

  // ======= FILTROS UI =======
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
      const text = (item.getAttribute('data-text') || '').toLowerCase();
      const alc = item.getAttribute('data-alcance') || '';

      const matchText = !q || text.includes(q);
      const matchAlc = (alcanceFiltro === 'all') || (alc === alcanceFiltro);

      if(matchText && matchAlc) item.classList.remove('d-none');
      else item.classList.add('d-none');
    });
  }
  applyFilters();

  // ======= VOTACIÓN (FUNCIONA CON CLICK) =======

  const candidatoModalEl = document.getElementById('candidatoModal');
  const opcionesModalEl  = document.getElementById('opcionesModal');
  const candidatoModal = candidatoModalEl ? new bootstrap.Modal(candidatoModalEl) : null;
  const opcionesModal  = opcionesModalEl ? new bootstrap.Modal(opcionesModalEl) : null;

  const candidatosBody = document.getElementById('candidatosModalBody');
  const submitVoteBtn  = document.getElementById('submitVoteBtn');
  const submitOpcBtn   = document.getElementById('submitOpcionesVoteBtn');

  const modalPreguntaCandidatos = document.getElementById('modalPreguntaCandidatos');
  const opcionesQuestion = document.getElementById('opcionesQuestion');
  const opcionesContainer = document.getElementById('opcionesContainer');

  // ✅ AJUSTA ESTA URL si tu backend es otro
  const VOTE_URL = 'admin/ajax/sondeo_votar_ajax.php';

  let current = {
    sondeoId: null,
    preguntaId: null,
    tipo: null,
    opcionElegida: null
  };

  function safeJsonParse(str, fallback){
    try { return JSON.parse(str || ''); } catch(e){ return fallback; }
  }

  function resetState(){
    current = { sondeoId:null, preguntaId:null, tipo:null, opcionElegida:null };
    if (submitVoteBtn) submitVoteBtn.disabled = true;
    if (submitOpcBtn) submitOpcBtn.disabled = true;
    if (candidatosBody) candidatosBody.innerHTML = '';
    if (opcionesContainer) opcionesContainer.innerHTML = '';
    if (modalPreguntaCandidatos) modalPreguntaCandidatos.textContent = '';
    if (opcionesQuestion) opcionesQuestion.textContent = '';
  }

  function openSondeo(card){
    if (!card || card.classList.contains('disabled')) return;

    resetState();

    const sondeoId = card.getAttribute('data-sondeo-id');
    const preguntaId = card.getAttribute('data-pregunta-id');
    const tipo = card.getAttribute('data-tipo-sondeo') || 'candidatos';
    const sondeoName = card.getAttribute('data-sondeo-name') || 'Sondeo';
    const preguntaTxt = card.getAttribute('data-pregunta') || 'Selecciona tu opción y confirma tu voto.';

    current.sondeoId = sondeoId;
    current.preguntaId = preguntaId;
    current.tipo = tipo;

    // Títulos
    const title1 = document.getElementById('voteModalTitle');
    const title2 = document.getElementById('opcionesModalTitle');
    if (title1) title1.innerHTML = `<i class="fas fa-hand-point-up me-2"></i> ${sondeoName}`;
    if (title2) title2.innerHTML = `<i class="fas fa-list me-2"></i> ${sondeoName}`;

    // Mostrar pregunta
    if (modalPreguntaCandidatos) modalPreguntaCandidatos.textContent = preguntaTxt;
    if (opcionesQuestion) opcionesQuestion.textContent = preguntaTxt;

    if (tipo === 'si_no') {
      // Opciones
      const opciones = safeJsonParse(card.getAttribute('data-opciones'), []);
      const finalOps = (Array.isArray(opciones) && opciones.length)
        ? opciones
        : [{id:'SI', label:'Sí'}, {id:'NO', label:'No'}];

      renderOpciones(finalOps);
      opcionesModal?.show();
    } else {
      // Candidatos
      const candidatos = safeJsonParse(card.getAttribute('data-candidatos'), []);
      renderCandidatos(candidatos);
      candidatoModal?.show();
    }
  }

  function renderCandidatos(list){
    if (!candidatosBody) return;

    if (!Array.isArray(list) || list.length === 0) {
      candidatosBody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center py-4 text-muted fw-bold">
            No hay candidatos disponibles en este sondeo.
          </td>
        </tr>`;
      return;
    }

    candidatosBody.innerHTML = list.map((c, idx) => {
      const id = (c.id ?? c.id_candidato ?? idx);
      const nombre = (c.candidato ?? c.nombre ?? c.nombre_candidato ?? 'Candidato');
      const cargo = (c.cargo ?? c.nombre_cargo ?? '—');
      const partido = (c.partido ?? c.partidos ?? '—');
      const municipio = (c.municipio ?? c.nombre_municipio ?? '—');
      const departamento = (c.departamento ?? c.nombre_departamento ?? '—');
      const foto = (c.foto ?? c.imagen ?? c.url_foto ?? '');

      const photoHtml = foto
        ? `<img class="cand-photo" src="${foto}" alt="Foto">`
        : `<div class="cand-photo d-grid place-items-center bg-light text-muted" style="font-weight:900;">?</div>`;

      return `
        <tr class="js-row-candidato" data-choice="${id}" style="cursor:pointer;">
          <td class="text-center">
            <input class="form-check-input js-cand-radio" type="radio" name="candidatoPick" value="${id}">
          </td>
          <td>${photoHtml}</td>
          <td>
            <div class="cand-name">${escapeHtml(nombre)}</div>
          </td>
          <td><span class="cand-meta">${escapeHtml(cargo)}</span></td>
          <td><span class="cand-meta">${escapeHtml(partido)}</span></td>
          <td><span class="cand-meta">${escapeHtml(municipio)}</span></td>
          <td><span class="cand-meta">${escapeHtml(departamento)}</span></td>
        </tr>
      `;
    }).join('');

    // seleccionar fila
    candidatosBody.querySelectorAll('.js-row-candidato').forEach(row=>{
      row.addEventListener('click', ()=>{
        const val = row.getAttribute('data-choice');
        const radio = row.querySelector('.js-cand-radio');
        if (radio) radio.checked = true;
        current.opcionElegida = val;
        if (submitVoteBtn) submitVoteBtn.disabled = false;
      });
    });

    // seleccionar radio
    candidatosBody.querySelectorAll('.js-cand-radio').forEach(r=>{
      r.addEventListener('change', ()=>{
        current.opcionElegida = r.value;
        if (submitVoteBtn) submitVoteBtn.disabled = false;
      });
    });
  }

  function renderOpciones(list){
    if (!opcionesContainer) return;

    opcionesContainer.innerHTML = list.map((o, idx)=>{
      const id = (o.id ?? o.value ?? idx);
      const label = (o.label ?? o.nombre ?? o.texto ?? o.opcion ?? `Opción ${idx+1}`);
      return `
        <div class="opt-card js-opt" data-choice="${id}">
          <input class="form-check-input mt-1 js-opt-radio" type="radio" name="opcionPick" value="${id}">
          <div>
            <p class="opt-title mb-0">${escapeHtml(label)}</p>
            <p class="opt-sub">Selecciona esta opción para continuar</p>
          </div>
        </div>
      `;
    }).join('');

    opcionesContainer.querySelectorAll('.js-opt').forEach(card=>{
      card.addEventListener('click', ()=>{
        opcionesContainer.querySelectorAll('.opt-card').forEach(x=> x.classList.remove('active'));
        card.classList.add('active');

        const val = card.getAttribute('data-choice');
        const radio = card.querySelector('.js-opt-radio');
        if (radio) radio.checked = true;

        current.opcionElegida = val;
        if (submitOpcBtn) submitOpcBtn.disabled = false;
      });
    });

    opcionesContainer.querySelectorAll('.js-opt-radio').forEach(r=>{
      r.addEventListener('change', ()=>{
        current.opcionElegida = r.value;
        if (submitOpcBtn) submitOpcBtn.disabled = false;
      });
    });
  }

  function escapeHtml(str){
    return String(str ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'",'&#039;');
  }

  async function enviarVoto(){
    if (!current.sondeoId || !current.opcionElegida) return;

    const btn = (current.tipo === 'si_no') ? submitOpcBtn : submitVoteBtn;
    if (btn) btn.disabled = true;

    try{
      const fd = new FormData();
      fd.append('sondeo_id', current.sondeoId);
      fd.append('pregunta_id', current.preguntaId || '');
      fd.append('respuesta', current.opcionElegida);
      fd.append('tipo', current.tipo || '');

      const res = await fetch(VOTE_URL, { method:'POST', body: fd, credentials:'same-origin' });
      const data = await res.json().catch(()=> null);

      if (!res.ok || !data || data.ok === false) {
        const msg = (data && (data.msg || data.message)) ? (data.msg || data.message) : 'No fue posible registrar el voto.';
        throw new Error(msg);
      }

      // ✅ UX OK
      Swal.fire({
        icon: 'success',
        title: '¡Voto registrado!',
        text: 'Tu participación quedó guardada correctamente.',
        confirmButtonText: 'Entendido'
      });

      // cerrar modal
      if (current.tipo === 'si_no') opcionesModal?.hide();
      else candidatoModal?.hide();

      // opcional: recargar para refrescar estado real desde DB
      setTimeout(()=> window.location.reload(), 700);

    }catch(err){
      Swal.fire({
        icon:'error',
        title:'Ups…',
        text: err?.message || 'Ocurrió un error al registrar tu voto.',
        confirmButtonText:'Cerrar'
      });
      if (btn) btn.disabled = false;
    }
  }

  submitVoteBtn?.addEventListener('click', enviarVoto);
  submitOpcBtn?.addEventListener('click', enviarVoto);

  // Click tarjeta
  document.addEventListener('click', (ev)=>{
    const card = ev.target.closest('.js-open-sondeo');
    if (card) openSondeo(card);
  });

  // Enter para accesibilidad
  document.addEventListener('keydown', (ev)=>{
    if (ev.key !== 'Enter') return;
    const focused = document.activeElement;
    if (focused && focused.classList.contains('js-open-sondeo')) openSondeo(focused);
  });
</script>

</body>
</html>
