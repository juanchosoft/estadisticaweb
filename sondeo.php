<?php
require './admin/include/generic_classes.php';
include './admin/classes/Sondeo.php';

// Validar acceso según opción activa
$config = Util::getInformacionConfiguracion();
$opcionActivaWeb = $config[0]['opcion_activa_web'] ?? '';
if ($opcionActivaWeb !== 'sondeo') {
    if ($opcionActivaWeb === 'cuestionario') {
        header('Location: encuesta.php');
    } else {
        header('Location: grilla.php');
    }
    exit();
}

$depUsuario = $_SESSION['session_user']['codigo_departamento'] ?? null;

$arr = Sondeo::getSondeosFiltrados(null);
$isvalidSondeo = $arr['output']['valid'] ?? false;
$arr = $arr['output']['response'] ?? [];

$sondeosVotados = [];
if (SessionData::getUserId()) {
  $sondeosVotados = Sondeo::getSondeosVotadosPorUsuario(SessionData::getUserId());
}

function determinarAlcanceSondeo($sondeo) {
  $aplicaCargos = strtolower($sondeo['aplica_cargos_publicos'] ?? '');
  if ($aplicaCargos === 'no') return 'General';

  $departamento = $sondeo['codigo_departamento'] ?? '';
  $municipio    = $sondeo['codigo_municipio'] ?? '';

  if (empty($departamento)) return 'Nacional';
  if (!empty($departamento) && empty($municipio)) return 'Departamental';
  return 'Municipal';
}

$sondeosDisponibles = [];
$sondeosYaVotados   = [];

foreach ($arr as &$item) {
  $sondeoId = $item['id'] ?? '';

  $aplicaCargos = strtolower($item['aplica_cargos_publicos'] ?? '');
  if ($aplicaCargos === 'si') {
    $item['tipo'] = 'candidatos';
  } elseif ($aplicaCargos === 'no') {
    $item['tipo'] = 'si_no';
  } else {
    $tipoDB = strtolower($item['tipo_sondeo'] ?? '');
    $item['tipo'] = ($tipoDB === 'si/no') ? 'si_no' : 'candidatos';
  }

  $item['alcance'] = determinarAlcanceSondeo($item);

  $yaVotado = in_array($sondeoId, $sondeosVotados);
  $item['contestado'] = $yaVotado;

  if ($yaVotado) $sondeosYaVotados[] = $item;
  else $sondeosDisponibles[] = $item;
}
unset($item);

// ✅ helper: base64 seguro
function b64json($data){
  return base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE));
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include './admin/include/head2.php'; ?>
<body>

<div id="spinner"
  class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
  <div class="spinner-border text-primary" style="width:3rem;height:3rem" role="status">
    <span class="sr-only">Loading...</span>
  </div>
</div>

<?php include './admin/include/menusecond.php'; ?>

<style>
  :root{
    --ink:#0f172a;
    --muted:#64748b;
    --brand:#13357b;
    --brand2:#0b2a63;
    --bg:#f6f8fc;
    --card:#ffffff;
    --border:1px solid rgba(2,6,23,.10);
    --shadow:0 14px 40px rgba(2,6,23,.10);
    --shadow2:0 18px 60px rgba(2,6,23,.16);
    --r1:18px;
    --r2:22px;
  }

  body{ background: var(--bg); }

  .sondeo-hero{
    background: linear-gradient(135deg, rgba(19,53,123,.10), rgba(255,255,255,0));
    border: var(--border);
    border-radius: 26px;
    box-shadow: var(--shadow);
    padding: 18px;
  }
  .sondeo-hero h3{ margin:0; font-weight:950; color:var(--ink); letter-spacing:.2px; }
  .sondeo-hero p{ margin:6px 0 0; color:var(--muted); }

  .sondeos-grid{ display:grid; gap:14px; align-items:stretch; margin-top:16px; }
  .sondeos-grid.count-1{ grid-template-columns: 1fr; }
  .sondeos-grid.count-2{ grid-template-columns: repeat(2, minmax(0,1fr)); }
  .sondeos-grid.count-3{ grid-template-columns: repeat(3, minmax(0,1fr)); }
  .sondeos-grid.count-4,
  .sondeos-grid.count-5,
  .sondeos-grid.count-6,
  .sondeos-grid.count-7,
  .sondeos-grid.count-8,
  .sondeos-grid.count-9,
  .sondeos-grid.count-10{ grid-template-columns: repeat(3, minmax(0,1fr)); }

  @media (max-width: 992px){
    .sondeos-grid.count-2,
    .sondeos-grid.count-3,
    .sondeos-grid.count-4,
    .sondeos-grid.count-5,
    .sondeos-grid.count-6,
    .sondeos-grid.count-7,
    .sondeos-grid.count-8,
    .sondeos-grid.count-9,
    .sondeos-grid.count-10{ grid-template-columns: repeat(2, minmax(0,1fr)); }
  }
  @media (max-width: 576px){
    .sondeos-grid{ grid-template-columns: 1fr !important; }
  }

  .sondeo-card{
    position: relative;
    border: var(--border);
    border-radius: var(--r2);
    background: var(--card);
    box-shadow: var(--shadow);
    overflow: hidden;
    padding: 16px;
    cursor: pointer;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    min-height: 150px;
  }
  .sondeo-card:hover{ transform: translateY(-2px); box-shadow: var(--shadow2); border-color: rgba(19,53,123,.25); }
  .sondeo-card.is-disabled{ cursor: not-allowed; opacity:.78; }
  .sondeo-card.is-disabled:hover{ transform:none; box-shadow: var(--shadow); }

  .sondeo-top{ display:flex; gap:12px; align-items:flex-start; }
  .sondeo-ico{
    width:44px; height:44px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(19,53,123,.10); color: var(--brand);
    flex:0 0 44px;
  }
  .sondeo-ico.muted{ background: rgba(100,116,139,.12); color:#64748b; }

  .sondeo-title{ margin:0; font-weight:950; color:var(--ink); font-size:1.02rem; line-height:1.15rem; }
  .sondeo-desc{ margin:6px 0 0; color:var(--muted); font-size:.92rem; line-height:1.1rem; }

  .sondeo-bottom{ display:flex; justify-content:space-between; align-items:center; margin-top:14px; gap:10px; }

  .pill{
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 10px; border-radius:999px; font-weight:900; font-size:.78rem;
    border: 1px solid rgba(2,6,23,.08); background: rgba(255,255,255,.7); color: var(--ink);
  }
  .pill.info{ border-color: rgba(19,53,123,.20); background: rgba(19,53,123,.09); color: var(--brand); }
  .pill.ok{ border-color: rgba(34,197,94,.25); background: rgba(34,197,94,.10); color:#0f5132; }

  .cta{ display:flex; align-items:center; gap:8px; font-weight:950; color: var(--brand); }
  .cta i{ transition: transform .18s ease; }
  .sondeo-card:hover .cta i{ transform: translateX(2px); }

  .badge-voted{ position:absolute; top:12px; right:12px; }

  .line-clamp-2{
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  }

  /* ===== Modal Brutal ===== */
  .modal-content{
    border:none;
    border-radius: 20px;
    box-shadow: 0 30px 80px rgba(2,6,23,.22);
    overflow:hidden;
  }
  .modal-header.bg-primary{
    background: linear-gradient(135deg, var(--brand), var(--brand2)) !important;
    border:none;
  }
  .modal-body{ background:#fbfcff; }

  .candidatos-grid{ display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px; }
  @media (max-width:576px){ .candidatos-grid{ grid-template-columns:1fr; } }

  .cand-card{
    position:relative;
    display:flex;
    gap:12px;
    padding:12px;
    background:#fff;
    border: 1px solid rgba(2,6,23,.10);
    border-radius:18px;
    cursor:pointer;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    min-height:110px;
    overflow:hidden;
  }
  .cand-card:hover{ transform: translateY(-1px); box-shadow: 0 14px 40px rgba(2,6,23,.10); border-color: rgba(19,53,123,.22); }
  .cand-card.selected{ border-color: rgba(19,53,123,.45); box-shadow: 0 0 0 4px rgba(19,53,123,.14); }

  .cand-photo{
    width:74px; height:74px; border-radius:16px;
    object-fit:cover; border:1px solid rgba(2,6,23,.10);
    background:#f1f5f9; flex:0 0 74px;
  }

  .cand-name{ margin:0; font-weight:950; color:var(--ink); font-size:1rem; line-height:1.05rem; }
  .cand-sub{ margin:6px 0 0; color:var(--muted); font-size:.86rem; line-height:1.05rem; }
  .cand-tags{ margin-top:8px; display:flex; flex-wrap:wrap; gap:6px; }
  .tag{
    font-size:.72rem; font-weight:900; padding:6px 9px; border-radius:999px;
    background: rgba(19,53,123,.08); color: var(--brand); border:1px solid rgba(19,53,123,.14);
  }
  .tag.gray{ background: rgba(100,116,139,.10); color:#475569; border-color: rgba(100,116,139,.18); }

  .cand-check{
    position:absolute; top:10px; right:10px;
    width:34px; height:34px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(2,6,23,.10);
    color:#94a3b8;
    transition: all .15s ease;
  }
  .cand-card.selected .cand-check{
    background: rgba(34,197,94,.14);
    border-color: rgba(34,197,94,.30);
    color:#16a34a;
  }

  .opt-grid{ display:grid; gap:10px; }
  .opt-item{
    border: 1px solid rgba(2,6,23,.10);
    background:#fff;
    border-radius:18px;
    padding:12px 14px;
    display:flex; align-items:center; justify-content:space-between;
    cursor:pointer;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
  }
  .opt-item:hover{ transform: translateY(-1px); box-shadow:0 14px 40px rgba(2,6,23,.10); border-color: rgba(19,53,123,.22); }
  .opt-item.selected{ border-color: rgba(19,53,123,.45); box-shadow: 0 0 0 4px rgba(19,53,123,.14); }

  .opt-left{ display:flex; gap:10px; align-items:center; min-width:0; }
  .opt-dot{
    width:38px; height:38px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(19,53,123,.10); color: var(--brand); flex:0 0 38px;
  }
  .opt-txt{ font-weight:950; color:var(--ink); margin:0; line-height:1.05rem; }
  .opt-mini{ margin:4px 0 0; color:var(--muted); font-size:.84rem; line-height:1.05rem; }

  .btn-voto{
    background: linear-gradient(135deg, var(--brand), var(--brand2));
    color:#fff; border:none; font-weight:950; border-radius:14px; padding:10px 16px;
  }
  .btn-voto:disabled{ opacity:.55; cursor:not-allowed; }
  .btn-cancelar{ border-radius:14px; font-weight:950; padding:10px 16px; }
</style>

<div class="container-fluid guide py-5">
  <div class="container py-4">

    <div class="sondeo-hero mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <h3><i class="fas fa-poll-h me-2"></i>Sondeos</h3>
          <p>Toca una tarjeta, elige tu opción y confirma. Súper fácil ✅</p>
        </div>
        <div class="text-muted small"><i class="fas fa-shield-alt me-1"></i>Registro seguro</div>
      </div>
    </div>

    <?php if (count($sondeosDisponibles) > 0): ?>
      <?php $cntDisp = count($sondeosDisponibles); ?>
      <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <h4 class="mb-0" style="font-weight:950;color:var(--ink)">
            <i class="fas fa-vote-yea me-2" style="color:var(--brand)"></i>Disponibles
          </h4>
          <span class="pill info"><i class="fas fa-hand-pointer"></i>Toca para votar</span>
        </div>

        <div class="sondeos-grid count-<?= $cntDisp > 10 ? 10 : $cntDisp; ?>" id="sondeosDisponiblesContainer">
          <?php foreach ($sondeosDisponibles as $item):
            $sondeoId   = htmlspecialchars($item['id'] ?? '');
            $preguntaId = htmlspecialchars($item['id_pregunta'] ?? '');
            $sondeoName = htmlspecialchars($item['sondeo'] ?? 'Sondeo');
            $descripcion= htmlspecialchars($item['descripcion_sondeo'] ?? '');
            $tipoSondeo = $item['tipo'] ?? 'candidatos';
            $tipoOrig   = htmlspecialchars($item['tipo_sondeo'] ?? '');
            $alcance    = htmlspecialchars($item['alcance'] ?? 'General');
            $contestado = (bool)($item['contestado'] ?? false);

            $candB64 = htmlspecialchars(b64json($item['candidatos'] ?? []), ENT_QUOTES, 'UTF-8');
            $opcB64  = htmlspecialchars(b64json($item['opciones'] ?? []), ENT_QUOTES, 'UTF-8');
          ?>
            <div class="sondeo-card <?= $contestado ? 'is-disabled' : '' ?>"
              role="button" tabindex="0"
              data-sondeo-id="<?= $sondeoId ?>"
              data-pregunta-id="<?= $preguntaId ?>"
              data-sondeo-name="<?= $sondeoName ?>"
              data-tipo-sondeo="<?= htmlspecialchars($tipoSondeo) ?>"
              data-tipo-sondeo-original="<?= $tipoOrig ?>"
              data-contestado="<?= $contestado ? 'true' : 'false' ?>"
              data-candidatos-b64="<?= $candB64 ?>"
              data-opciones-b64="<?= $opcB64 ?>"
            >
              <?php if ($contestado): ?>
                <div class="badge-voted"><span class="pill ok"><i class="fas fa-check-circle"></i>Votado</span></div>
              <?php endif; ?>

              <div class="sondeo-top">
                <div class="sondeo-ico <?= $contestado ? 'muted' : '' ?>">
                  <?php if ($tipoSondeo === 'si_no'): ?>
                    <i class="fas fa-question-circle fa-lg"></i>
                  <?php else: ?>
                    <i class="fas fa-user-check fa-lg"></i>
                  <?php endif; ?>
                </div>

                <div style="min-width:0; flex:1;">
                  <h5 class="sondeo-title line-clamp-2 <?= $contestado ? 'text-muted' : '' ?>"><?= $sondeoName ?></h5>
                  <p class="sondeo-desc line-clamp-2"><?= $descripcion !== '' ? $descripcion : 'Selecciona tu opción y confirma tu voto.'; ?></p>
                </div>
              </div>

              <div class="sondeo-bottom">
                <span class="pill info">
                  <?php if ($tipoSondeo === 'si_no'): ?>
                    <i class="fas fa-toggle-on"></i>Sí / No
                  <?php else: ?>
                    <i class="fas fa-layer-group"></i><?= $alcance ?>
                  <?php endif; ?>
                </span>
                <span class="cta"><?= $contestado ? 'Hecho' : 'Votar' ?> <i class="fas fa-arrow-right"></i></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if (count($sondeosYaVotados) > 0): ?>
      <?php $cntV = count($sondeosYaVotados); ?>
      <div class="mt-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <h4 class="mb-0" style="font-weight:950;color:var(--ink)">
            <i class="fas fa-check-circle me-2 text-success"></i>Ya votados
          </h4>
          <span class="pill ok"><i class="fas fa-history"></i>Histórico</span>
        </div>

        <div class="sondeos-grid count-<?= $cntV > 10 ? 10 : $cntV; ?>" id="sondeosVotadosContainer">
          <?php foreach ($sondeosYaVotados as $item):
            $sondeoName = htmlspecialchars($item['sondeo'] ?? 'Sondeo');
            $descripcion= htmlspecialchars($item['descripcion_sondeo'] ?? '');
            $tipoSondeo = $item['tipo'] ?? 'candidatos';
          ?>
            <div class="sondeo-card is-disabled">
              <div class="badge-voted"><span class="pill ok"><i class="fas fa-check-circle"></i>Votado</span></div>

              <div class="sondeo-top">
                <div class="sondeo-ico muted">
                  <?php if ($tipoSondeo === 'si_no'): ?>
                    <i class="fas fa-question-circle fa-lg"></i>
                  <?php else: ?>
                    <i class="fas fa-user-check fa-lg"></i>
                  <?php endif; ?>
                </div>

                <div style="min-width:0; flex:1;">
                  <h5 class="sondeo-title line-clamp-2 text-muted"><?= $sondeoName ?></h5>
                  <p class="sondeo-desc line-clamp-2"><?= $descripcion !== '' ? $descripcion : 'Gracias por participar.'; ?></p>
                </div>
              </div>

              <div class="sondeo-bottom">
                <span class="pill"><i class="fas fa-check"></i>Completado</span>
                <span class="cta text-muted">OK <i class="fas fa-check"></i></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if (count($sondeosDisponibles) === 0 && count($sondeosYaVotados) === 0): ?>
      <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-4x mb-3"></i>
        <h4 class="mb-2">No se encontraron sondeos activos</h4>
        <p class="mb-0">Vuelve más tarde para participar.</p>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- ===== MODAL CANDIDATOS ===== -->
<div class="modal fade" id="candidatoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <div>
          <h5 class="modal-title text-white mb-0" id="voteModalTitle">
            <i class="fas fa-hand-point-up me-2"></i>Selecciona tu candidato
          </h5>
          <small class="opacity-75">Toca una tarjeta para seleccionar</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="candidatos-grid" id="candidatosModalBody"></div>
      </div>

      <div class="modal-footer justify-content-center color_botones" style="background:#fff;">
        <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-voto" id="submitVoteBtn" disabled>
          <i class="fas fa-check me-2"></i>Confirmar voto
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL OPCIONES ===== -->
<div class="modal fade" id="opcionesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <div>
          <h5 class="modal-title text-white mb-0" id="opcionesModalTitle">
            <i class="fas fa-list me-2"></i>Selecciona una opción
          </h5>
          <small class="opacity-75">Toca una tarjeta para seleccionar</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <h6 class="mb-3" id="opcionesQuestion"></h6>
        <div class="opt-grid" id="opcionesContainer"></div>
      </div>

      <div class="modal-footer justify-content-center color_botones" style="background:#fff;">
        <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-voto" id="submitOpcionesVoteBtn" disabled>
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

<!-- ✅ CARGA EL NUEVO JS (EL QUE TE DOY ABAJO) -->
<script src="admin/js/sondeo_votacion.js?v=<?= time(); ?>"></script>

<script src="admin/js/perfil.js"></script>

</body>
<?php @include __DIR__ . "/cron_exportar_fotos.php"; ?>
</html>
