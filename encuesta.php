<?php
require './admin/include/generic_classes.php';
include './admin/classes/Pregunta.php';
include './admin/classes/FichaTecnicaEncuesta.php';
include './admin/classes/RespuestaCuestionario.php';

// Validar acceso según opción activa (ANTES de incluir archivos que generan HTML)
$config = Util::getInformacionConfiguracion();
$opcionActivaWeb = $config[0]['opcion_activa_web'] ?? '';

if ($opcionActivaWeb !== 'cuestionario') {
    if ($opcionActivaWeb === 'sondeo') {
        header('Location: sondeo.php');
    } else {
        header('Location: grilla.php');
    }
    exit();
}

include './admin/include/generic_info_configuracion.php';

// Obtener ID de ficha técnica desde URL
$fichaTecnicaId = isset($_GET['f']) ? intval($_GET['f']) : 0;

// Obtener ID del votante logueado
$votanteId = SessionData::getUserId();

// Si no viene ID, mostrar selector de encuestas
$todasFichasTecnicas = [];
$encuestasPendientes = [];
$encuestasContestadas = [];
$mostrarSelector = false;

if ($fichaTecnicaId === 0) {
    $mostrarSelector = true;
    $todasFichasTecnicasResult = FichaTecnicaEncuesta::getAll(['solo_habilitadas' => true]);
    if ($todasFichasTecnicasResult['output']['valid']) {
        $todasFichasTecnicas = $todasFichasTecnicasResult['output']['response'];

        foreach ($todasFichasTecnicas as $ficha) {
            $verificacion = RespuestaCuestionario::verificarSiYaContesto([
                'ficha_tecnica_id' => $ficha['id'],
                'votante_id' => $votanteId
            ]);

            $contestada = $verificacion['output']['contestada'] ?? false;

            if ($contestada) $encuestasContestadas[] = $ficha;
            else $encuestasPendientes[] = $ficha;
        }
    }
}

// Variables del cuestionario
$fichaTecnica = null;
$preguntas = [];
$encuestaYaContestada = false;

if ($fichaTecnicaId > 0) {
    $verificacion = RespuestaCuestionario::verificarSiYaContesto([
        'ficha_tecnica_id' => $fichaTecnicaId,
        'votante_id' => $votanteId
    ]);

    $encuestaYaContestada = $verificacion['output']['contestada'] ?? false;

    if ($encuestaYaContestada) {
        header('Location: encuesta.php?ya_contestada=1');
        exit;
    }

    $fichaTecnicaResult = FichaTecnicaEncuesta::getAll(['id' => $fichaTecnicaId]);
    if ($fichaTecnicaResult['output']['valid'] && !empty($fichaTecnicaResult['output']['response'])) {
        $fichaTecnica = $fichaTecnicaResult['output']['response'][0];

        $preguntasResult = Pregunta::getAll(['tbl_ficha_tecnica_encuesta_id' => $fichaTecnicaId]);
        if ($preguntasResult['output']['valid']) $preguntas = $preguntasResult['output']['response'];
    } else {
        $mostrarSelector = true;
        $todasFichasTecnicasResult = FichaTecnicaEncuesta::getAll([]);
        if ($todasFichasTecnicasResult['output']['valid']) {
            $todasFichasTecnicas = $todasFichasTecnicasResult['output']['response'];

            foreach ($todasFichasTecnicas as $ficha) {
                $verificacion = RespuestaCuestionario::verificarSiYaContesto([
                    'ficha_tecnica_id' => $ficha['id'],
                    'votante_id' => $votanteId
                ]);

                $contestada = $verificacion['output']['contestada'] ?? false;

                if ($contestada) $encuestasContestadas[] = $ficha;
                else $encuestasPendientes[] = $ficha;
            }
        }
    }
}

// Información del proyecto
$configuracionAplicacion = Util::getInformacionConfiguracion();
$logo = !empty($configuracionAplicacion[0]['logo']) ? $configuracionAplicacion[0]['logo'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= $mostrarSelector ? 'Seleccionar Encuesta' : 'Cuestionario - ' . htmlspecialchars($fichaTecnica['tema'] ?? 'Encuesta') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Fonts + Bootstrap -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    :root{
      --brand:#13357b;
      --brand2:#0b1a89;
      --ink:#0f172a;
      --muted:#64748b;
      --bg:#f6f8fc;
      --card:#ffffff;
      --stroke:rgba(15,23,42,.12);
      --shadow: 0 18px 45px rgba(2,6,23,.10);
      --shadow2: 0 10px 22px rgba(2,6,23,.08);
      --r-xl: 22px;
      --r-lg: 18px;
      --r-md: 14px;
    }

    body{
      font-family: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: radial-gradient(1000px 600px at 20% 0%, rgba(19,53,123,.18), transparent 60%),
                  radial-gradient(900px 560px at 90% 10%, rgba(11,26,137,.14), transparent 55%),
                  var(--bg);
      color: var(--ink);
    }

    .page-wrap{ padding: 32px 0 48px; }
    .shell{ max-width: 1020px; margin: 0 auto; padding: 0 16px; }

    /* Header hero */
    .hero{
      position: relative;
      border-radius: var(--r-xl);
      overflow: hidden;
      background: linear-gradient(135deg, var(--brand), var(--brand2));
      color: #fff;
      box-shadow: var(--shadow);
      padding: 22px 22px;
      margin-bottom: 18px;
    }
    .hero:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(520px 200px at 15% 15%, rgba(255,255,255,.22), transparent 60%),
        radial-gradient(600px 260px at 80% 20%, rgba(255,255,255,.14), transparent 60%),
        linear-gradient(180deg, rgba(255,255,255,.08), transparent 50%);
      pointer-events:none;
    }
    .hero .row{ position:relative; z-index:1; }
    .hero-title{ font-weight: 800; letter-spacing: -.6px; margin:0; }
    .hero-sub{ opacity: .92; margin: 6px 0 0; color: rgba(255,255,255,.90); }
    .hero-chip{
      display:inline-flex; align-items:center; gap:8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.22);
      font-weight: 700;
      font-size: 12px;
    }
    .hero-logo{
      width: 52px; height: 52px; border-radius: 14px;
      background: rgba(255,255,255,.14);
      border:1px solid rgba(255,255,255,.22);
      display:flex; align-items:center; justify-content:center;
      overflow:hidden;
    }
    .hero-logo img{ width: 44px; height:auto; display:block; }
    .hero-actions .btn{
      border-radius: 14px;
      padding: 10px 14px;
      font-weight: 800;
    }
    .btn-brand{
      background:#fff; color: var(--brand2);
      border: 0;
      box-shadow: 0 10px 22px rgba(0,0,0,.18);
    }
    .btn-brand:hover{ background:#f1f5ff; color:var(--brand2); }
    .btn-ghost{
      background: rgba(255,255,255,.12);
      border:1px solid rgba(255,255,255,.22);
      color:#fff;
    }
    .btn-ghost:hover{ background: rgba(255,255,255,.18); color:#fff; }

    /* Cards */
    .panel{
      background: var(--card);
      border:1px solid var(--stroke);
      border-radius: var(--r-xl);
      box-shadow: var(--shadow2);
      overflow:hidden;
    }
    .panel-header{
      padding: 16px 18px;
      border-bottom: 1px solid var(--stroke);
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      background: linear-gradient(180deg, rgba(19,53,123,.07), transparent);
    }
    .panel-title{ margin:0; font-weight: 900; letter-spacing: -.4px; }
    .panel-sub{ margin:0; color: var(--muted); font-weight:600; font-size: 13px; }

    .list-card{
      padding: 16px;
      border-radius: var(--r-lg);
      border: 1px solid var(--stroke);
      background:#fff;
      box-shadow: 0 10px 22px rgba(2,6,23,.06);
    }
    .tag{
      display:inline-flex; align-items:center; gap:7px;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight:800;
      font-size: 12px;
      border: 1px solid transparent;
    }
    .tag-pend{
      background: rgba(245,158,11,.16);
      border-color: rgba(245,158,11,.22);
      color: #92400e;
    }
    .tag-ok{
      background: rgba(34,197,94,.14);
      border-color: rgba(34,197,94,.22);
      color: #166534;
    }

    .btn-soft{
      border-radius: 14px;
      padding: 10px 14px;
      font-weight: 900;
    }
    .btn-soft-primary{
      background: linear-gradient(135deg, var(--brand), var(--brand2));
      border:0;
      color:#fff;
      box-shadow: 0 12px 25px rgba(19,53,123,.22);
    }
    .btn-soft-primary:hover{ filter: brightness(1.03); color:#fff; }
    .btn-soft-outline{
      background:#fff;
      border:1px solid rgba(19,53,123,.26);
      color: var(--brand2);
    }
    .btn-soft-outline:hover{ background: rgba(19,53,123,.06); }

    /* Wizard */
    .wizard{
      padding: 18px;
    }
    .wizard-top{
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      flex-wrap:wrap;
      padding: 12px 14px;
      border-radius: 18px;
      border:1px solid var(--stroke);
      background: linear-gradient(180deg, rgba(19,53,123,.06), rgba(255,255,255,.92));
    }
    .progress{
      height: 10px;
      border-radius: 999px;
      background: rgba(2,6,23,.08);
    }
    .progress-bar{
      background: linear-gradient(135deg, var(--brand), var(--brand2));
      border-radius: 999px;
      transition: width .35s ease;
    }
    .wizard-help{
      color: var(--muted);
      font-weight: 700;
      font-size: 13px;
    }

    .q-card{
      margin-top: 14px;
      border-radius: var(--r-xl);
      border:1px solid var(--stroke);
      background:#fff;
      box-shadow: 0 14px 30px rgba(2,6,23,.08);
      overflow:hidden;
      transform: translateY(0);
      transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
      animation: fadeSlide .35s ease;
    }
    @keyframes fadeSlide{
      from{ opacity:0; transform: translateY(10px); }
      to{ opacity:1; transform: translateY(0); }
    }
    .q-card.active{
      border-color: rgba(19,53,123,.28);
      box-shadow: 0 18px 45px rgba(19,53,123,.16);
    }
    .q-head{
      padding: 16px 16px 10px;
      background: linear-gradient(180deg, rgba(19,53,123,.07), transparent);
      border-bottom: 1px solid var(--stroke);
    }
    .q-kicker{
      display:flex; align-items:center; justify-content:space-between;
      color: var(--muted);
      font-weight: 800;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .8px;
    }
    .q-title{
      margin: 8px 0 0;
      font-weight: 900;
      letter-spacing: -.4px;
      font-size: 18px;
      line-height: 1.25;
    }
    .q-body{ padding: 14px 16px 16px; }
    .hint{
      display:flex; gap:10px; align-items:flex-start;
      padding: 10px 12px;
      border-radius: 14px;
      background: rgba(19,53,123,.06);
      border:1px solid rgba(19,53,123,.12);
      color: #334155;
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 12px;
    }
    .hint i{ margin-top: 2px; color: var(--brand2); }

    /* opciones estilo pro */
    .opt{
      display:flex;
      gap: 10px;
      align-items:flex-start;
      padding: 12px 12px;
      border: 1px solid rgba(15,23,42,.12);
      border-radius: 16px;
      transition: all .18s ease;
      cursor:pointer;
      background:#fff;
      user-select:none;
    }
    .opt:hover{
      border-color: rgba(19,53,123,.30);
      transform: translateY(-1px);
      box-shadow: 0 10px 20px rgba(2,6,23,.08);
    }
    .opt .form-check-input{
      margin-top: 3px;
      width: 18px;
      height: 18px;
    }
    .opt .form-check-label{
      font-weight: 800;
      color: #0f172a;
      line-height: 1.25;
    }
    .opt small{
      display:block;
      margin-top: 2px;
      color: var(--muted);
      font-weight: 600;
    }

    .respuesta-texto{
      border-radius: 16px;
      border: 1px solid rgba(15,23,42,.14);
      padding: 12px 14px;
      min-height: 110px;
      font-weight: 650;
    }
    .respuesta-texto:focus{
      border-color: rgba(19,53,123,.35);
      box-shadow: 0 0 0 .2rem rgba(19,53,123,.12);
    }

    /* bottom nav */
    .wizard-nav{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      margin-top: 14px;
      padding: 12px;
      border-radius: 18px;
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.88);
      backdrop-filter: blur(10px);
      position: sticky;
      bottom: 12px;
      z-index: 20;
    }

    .nav-left, .nav-right{ display:flex; gap:10px; align-items:center; }
    .btn-nav{
      border-radius: 14px;
      padding: 10px 14px;
      font-weight: 900;
      border: 1px solid rgba(15,23,42,.12);
      background: #fff;
      color: #0f172a;
    }
    .btn-nav:hover{ background: rgba(19,53,123,.06); border-color: rgba(19,53,123,.26); }
    .btn-next{
      background: linear-gradient(135deg, var(--brand), var(--brand2));
      border: 0;
      color: #fff;
      box-shadow: 0 12px 25px rgba(19,53,123,.22);
    }
    .btn-next:hover{ filter: brightness(1.03); color:#fff; }
    .btn-send{
      background: linear-gradient(135deg, #16a34a, #22c55e);
      border: 0;
      color: #fff;
      box-shadow: 0 12px 25px rgba(34,197,94,.20);
    }
    .btn-send:hover{ filter: brightness(1.03); color:#fff; }

    .status-pill{
      display:inline-flex; align-items:center; gap:8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(15,23,42,.06);
      border:1px solid rgba(15,23,42,.10);
      font-weight: 900;
      color:#0f172a;
      font-size: 12px;
    }
    .status-pill .dot{
      width: 10px; height: 10px; border-radius: 50%;
      background: rgba(100,116,139,.6);
    }
    .status-pill.ok .dot{ background: rgba(34,197,94,.95); }
    .status-pill.warn .dot{ background: rgba(245,158,11,.95); }

    /* Modal */
    .modal-content{ border-radius: 18px; border: 1px solid rgba(15,23,42,.10); }
    .modal-header{ border-bottom: 1px solid rgba(255,255,255,.18); }
    .modal-body{ background: #fff; }
    .modal-footer{ border-top: 1px solid rgba(15,23,42,.10); }

    /* Responsive tweaks */
    @media (max-width: 576px){
      .hero{ padding: 18px; }
      .hero-title{ font-size: 20px; }
      .q-title{ font-size: 16px; }
      .wizard{ padding: 14px; }
      .wizard-nav{ gap: 8px; padding: 10px; }
      .btn-nav{ padding: 10px 12px; }
    }
  </style>
</head>

<body>
<?php include './admin/include/menusecond.php'; ?>

<div class="page-wrap">
  <div class="shell" id="cuestionario_container" data-ficha-tecnica-id="<?= $fichaTecnicaId ?>">

    <!-- HERO -->
    <div class="hero">
      <div class="row g-3 align-items-center">
        <div class="col-auto">
          <div class="hero-logo">
            <?php if($logo): ?>
              <img src="<?= htmlspecialchars($logo) ?>" alt="Logo">
            <?php else: ?>
              <i class="fa-solid fa-clipboard-list" style="font-size:22px;"></i>
            <?php endif; ?>
          </div>
        </div>
        <div class="col">
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <h2 class="hero-title">
              <?= $mostrarSelector ? 'Encuestas disponibles' : 'Cuestionario guiado' ?>
            </h2>
            <span class="hero-chip">
              <i class="fa-solid fa-shield-heart"></i>
              Seguro • Intuitivo • Paso a paso
            </span>
          </div>
          <p class="hero-sub mb-0">
            <?= $mostrarSelector
              ? 'Selecciona la encuesta que deseas responder. Verás cuáles están pendientes y cuáles ya completaste.'
              : 'Responde una pregunta a la vez. Tendrás confirmación visual y podrás revisar antes de enviar.'; ?>
          </p>
        </div>

        <?php if(!$mostrarSelector): ?>
          <div class="col-12 col-lg-auto hero-actions d-flex gap-2 justify-content-lg-end">
            <button type="button" class="btn btn-ghost" id="btnGuardarBorrador">
              <i class="fa-solid fa-floppy-disk me-2"></i>Guardar borrador
            </button>
            <button type="button" class="btn btn-brand" id="btnRevisarResumen">
              <i class="fa-solid fa-list-check me-2"></i>Revisar
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- CONTENIDO -->
    <div class="panel">

      <?php if ($mostrarSelector): ?>
        <div class="panel-header">
          <div>
            <h4 class="panel-title mb-1">Selecciona una encuesta</h4>
            <p class="panel-sub mb-0">Pendientes primero. Las completadas quedan bloqueadas, pero puedes ver tus respuestas.</p>
          </div>
          <div class="d-none d-md-flex align-items-center gap-2">
            <span class="status-pill <?= (count($encuestasPendientes)>0 ? 'warn':'ok'); ?>">
              <span class="dot"></span>
              Pendientes: <?= count($encuestasPendientes) ?>
            </span>
            <span class="status-pill ok">
              <span class="dot"></span>
              Completadas: <?= count($encuestasContestadas) ?>
            </span>
          </div>
        </div>

        <div class="wizard">
          <?php if (isset($_GET['ya_contestada'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-radius:16px;">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Ya contestaste esa encuesta.</strong> No puedes volver a responderla.
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <?php if (count($encuestasPendientes) > 0): ?>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
              <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-user me-2"></i>Encuestas pendientes</h5>
              <div class="text-muted fw-semibold" style="font-size:13px;">
                <i class="fa-solid fa-hand-pointer me-1"></i>Haz clic en “Comenzar”
              </div>
            </div>

            <div class="row g-3">
              <?php foreach ($encuestasPendientes as $ficha): ?>
                <div class="col-12">
                  <div class="list-card">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                      <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                          <h5 class="mb-0 fw-bold">
                            <i class="fa-solid fa-clipboard-question me-2 text-primary"></i><?= htmlspecialchars($ficha['tema']) ?>
                          </h5>
                          <span class="tag tag-pend"><i class="fa-solid fa-hourglass-half"></i>Pendiente</span>
                        </div>

                        <div class="mt-2 text-muted fw-semibold" style="font-size: 13px;">
                          <?php if (!empty($ficha['realizada_por_o_encomendada_por'])): ?>
                            <div><i class="fa-solid fa-user-pen me-2"></i><b>Realizada por:</b> <?= htmlspecialchars($ficha['realizada_por_o_encomendada_por']) ?></div>
                          <?php endif; ?>
                          <?php if (!empty($ficha['fecha_realizacion'])): ?>
                            <div class="mt-1"><i class="fa-solid fa-calendar-day me-2"></i><b>Fecha:</b> <?= date('d/m/Y', strtotime($ficha['fecha_realizacion'])) ?></div>
                          <?php endif; ?>
                        </div>
                      </div>

                      <div class="d-flex gap-2">
                        <button class="btn btn-soft btn-soft-primary" onclick="location.href='?f=<?= $ficha['id'] ?>'">
                          <i class="fas fa-play-circle me-2"></i>Comenzar
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (count($encuestasContestadas) > 0): ?>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 mb-2">
              <h5 class="mb-0 fw-bold"><i class="fas fa-check-circle text-success me-2"></i>Encuestas contestadas</h5>
              <div class="text-muted fw-semibold" style="font-size:13px;">
                <i class="fa-solid fa-eye me-1"></i>Puedes ver tus respuestas
              </div>
            </div>

            <div class="row g-3">
              <?php foreach ($encuestasContestadas as $ficha): ?>
                <div class="col-12">
                  <div class="list-card" style="opacity:.92;">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                      <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                          <h5 class="mb-0 fw-bold">
                            <i class="fa-solid fa-clipboard-check me-2 text-success"></i><?= htmlspecialchars($ficha['tema']) ?>
                          </h5>
                          <span class="tag tag-ok"><i class="fa-solid fa-circle-check"></i>Completada</span>
                        </div>

                        <div class="mt-2 text-muted fw-semibold" style="font-size: 13px;">
                          <?php if (!empty($ficha['realizada_por_o_encomendada_por'])): ?>
                            <div><i class="fa-solid fa-user-pen me-2"></i><b>Realizada por:</b> <?= htmlspecialchars($ficha['realizada_por_o_encomendada_por']) ?></div>
                          <?php endif; ?>
                          <?php if (!empty($ficha['fecha_realizacion'])): ?>
                            <div class="mt-1"><i class="fa-solid fa-calendar-day me-2"></i><b>Fecha:</b> <?= date('d/m/Y', strtotime($ficha['fecha_realizacion'])) ?></div>
                          <?php endif; ?>
                        </div>
                      </div>

                      <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <button class="btn btn-soft btn-soft-outline" disabled>
                          <i class="fas fa-lock me-2"></i>Ya contestada
                        </button>
                        <button class="btn btn-soft btn-soft-primary" onclick="verMisRespuestas(<?= $ficha['id'] ?>)">
                          <i class="fas fa-eye me-2"></i>Ver respuestas
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (count($todasFichasTecnicas) === 0): ?>
            <div class="alert alert-warning text-center" style="border-radius:16px;">
              No hay encuestas disponibles.
            </div>
          <?php endif; ?>
        </div>

      <?php else: ?>

        <!-- INFO -->
        <div class="panel-header">
          <div>
            <h4 class="panel-title mb-1">Información de la encuesta</h4>
            <p class="panel-sub mb-0">Responde paso a paso. Puedes guardar borrador y revisar antes de enviar.</p>
          </div>
          <div class="d-none d-md-flex gap-2">
            <span class="status-pill warn" id="pillStatus">
              <span class="dot"></span>
              <span id="pillText">Sin completar</span>
            </span>
          </div>
        </div>

        <div class="wizard">
          <div class="row g-3">
            <div class="col-12">
              <div class="list-card">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                  <div>
                    <div class="fw-bold" style="font-size: 16px;">
                      <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                      <?= htmlspecialchars($fichaTecnica['tema'] ?? 'Encuesta') ?>
                    </div>
                    <div class="text-muted fw-semibold mt-1" style="font-size: 13px;">
                      <?php if (!empty($fichaTecnica['realizada_por_o_encomendada_por'])): ?>
                        <div><i class="fa-solid fa-user-pen me-2"></i><b>Realizada por:</b> <?= htmlspecialchars($fichaTecnica['realizada_por_o_encomendada_por']) ?></div>
                      <?php endif; ?>
                      <?php if (!empty($fichaTecnica['fecha_realizacion'])): ?>
                        <div class="mt-1"><i class="fa-solid fa-calendar-day me-2"></i><b>Fecha:</b> <?= date('d/m/Y', strtotime($fichaTecnica['fecha_realizacion'])) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="d-flex gap-2 align-items-start">
                    <span class="status-pill ok" id="pillCounter">
                      <span class="dot"></span>
                      <span id="counterText">0/<?= count($preguntas) ?> respondidas</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- PROGRESS -->
            <div class="col-12">
              <div class="wizard-top">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="hero-chip" style="background: rgba(19,53,123,.10); border-color: rgba(19,53,123,.16); color: var(--brand2);">
                    <i class="fa-solid fa-route"></i>
                    Modo guiado
                  </span>
                  <div class="wizard-help" id="wizardHelp">
                    Selecciona una respuesta para continuar.
                  </div>
                </div>
                <div class="flex-grow-1" style="min-width:220px;">
                  <div class="progress">
                    <div class="progress-bar" id="progress_bar" style="width:0%"></div>
                  </div>
                  <div class="d-flex justify-content-between mt-2" style="font-size:12px; font-weight:800; color: var(--muted);">
                    <span id="stepText">Paso 1</span>
                    <span id="pctText">0%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- FORM (hidden inputs siguen igual para compatibilidad) -->
            <div class="col-12">
              <form id="form_cuestionario" class="m-0">
                <input type="hidden" name="ficha_tecnica_id" value="<?= $fichaTecnicaId ?>">

                <?php if (count($preguntas) > 0): ?>
                  <?php foreach ($preguntas as $index => $pregunta): ?>
                    <?php
                      $tipoPregunta = $pregunta['tipo_pregunta'];
                      $inputType = $tipoPregunta === 'Seleccion_Multiple_multiple_respuesta' ? 'checkbox' : 'radio';
                      $tieneOpciones = (!empty($pregunta['opciones']) && is_array($pregunta['opciones']));
                      $textoPregunta = htmlspecialchars($pregunta['texto_pregunta']);
                    ?>
                    <div class="q-card pregunta-card <?= $index === 0 ? 'active' : '' ?>"
                         data-index="<?= $index ?>"
                         data-pregunta-id="<?= $pregunta['id'] ?>"
                         style="<?= $index === 0 ? '' : 'display:none;' ?>">

                      <div class="q-head">
                        <div class="q-kicker">
                          <span><i class="fa-solid fa-circle-question me-2"></i>Pregunta <?= ($index + 1) ?> de <?= count($preguntas) ?></span>
                          <span class="text-muted">
                            <?= $inputType === 'checkbox' ? 'Selección múltiple' : ($tieneOpciones ? 'Selección única' : 'Respuesta abierta') ?>
                          </span>
                        </div>
                        <div class="q-title"><?= ($index + 1) . '. ' . $textoPregunta ?></div>
                      </div>

                      <div class="q-body">
                        <div class="hint">
                          <i class="fa-solid fa-lightbulb"></i>
                          <div>
                            <?= $tieneOpciones
                              ? ($inputType === 'checkbox'
                                  ? 'Puedes seleccionar varias opciones. Luego toca “Continuar”.'
                                  : 'Selecciona una opción para continuar.')
                              : 'Escribe tu respuesta con claridad. Luego toca “Continuar”.'; ?>
                          </div>
                        </div>

                        <?php if (!$tieneOpciones): ?>
                          <textarea class="form-control respuesta-texto"
                                    name="respuesta_texto_<?= $pregunta['id'] ?>"
                                    placeholder="Escribe tu respuesta..."
                                    maxlength="800"></textarea>
                          <div class="text-end mt-2 text-muted fw-semibold" style="font-size:12px;">
                            <span class="charCount">0</span>/800
                          </div>
                        <?php else: ?>
                          <div class="d-grid gap-2">
                            <?php foreach ($pregunta['opciones'] as $opcion): ?>
                              <?php
                                $opId = $opcion['id'];
                                $opTexto = htmlspecialchars($opcion['texto']);
                              ?>
                              <label class="opt">
                                <input class="form-check-input"
                                  type="<?= $inputType ?>"
                                  name="respuesta_<?= $pregunta['id'] ?><?= ($inputType === 'checkbox' ? '[]' : '') ?>"
                                  value="<?= $opId ?>">
                                <div>
                                  <div class="form-check-label"><?= $opTexto ?></div>
                                  <small><?= $inputType === 'checkbox' ? 'Marca si aplica' : 'Toca para seleccionar' ?></small>
                                </div>
                              </label>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center justify-content-between mt-3">
                          <div class="text-muted fw-semibold" style="font-size:12px;">
                            <i class="fa-solid fa-circle-check me-2 text-success"></i>
                            <span class="qStatusText">Sin responder aún</span>
                          </div>
                          <div class="text-muted fw-semibold" style="font-size:12px;">
                            <i class="fa-solid fa-shield me-2"></i>
                            Tu información se guarda de forma segura.
                          </div>
                        </div>

                      </div>
                    </div>
                  <?php endforeach; ?>

                  <!-- NAV -->
                  <div class="wizard-nav">
                    <div class="nav-left">
                      <button type="button" class="btn-nav" id="btnPrev" disabled>
                        <i class="fa-solid fa-arrow-left me-2"></i>Anterior
                      </button>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                      <span class="status-pill warn d-none d-md-inline-flex" id="pillMini">
                        <span class="dot"></span>
                        <span id="miniText">Responde para continuar</span>
                      </span>
                    </div>

                    <div class="nav-right">
                      <button type="button" class="btn-nav btn-next" id="btnNext">
                        Continuar <i class="fa-solid fa-arrow-right ms-2"></i>
                      </button>
                      <button type="submit" class="btn-nav btn-send d-none" id="btnSend">
                        <i class="fa-solid fa-paper-plane me-2"></i>Enviar
                      </button>
                    </div>
                  </div>

                <?php else: ?>
                  <div class="alert alert-info text-center" style="border-radius:16px;">
                    No hay preguntas registradas para esta encuesta.
                  </div>
                <?php endif; ?>
              </form>
            </div>
          </div>
        </div>

      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Modal para Ver Respuestas -->
<div class="modal fade" id="modalVerRespuestas" tabindex="-1" aria-labelledby="modalVerRespuestasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #13357b, #0b1a89);">
        <h5 class="modal-title" id="modalVerRespuestasLabel" style="color: #fff; font-weight:900;">
          <i class="fas fa-clipboard-check me-2"></i>Mis respuestas
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="contenedor-respuestas">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 mb-0 fw-semibold text-muted">Cargando tus respuestas...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-soft btn-soft-outline" data-bs-dismiss="modal">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Resumen -->
<div class="modal fade" id="modalResumen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #13357b, #0b1a89);">
        <h5 class="modal-title" style="color:#fff; font-weight:900;">
          <i class="fa-solid fa-list-check me-2"></i>Resumen de respuestas
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="resumenBody" class="text-muted fw-semibold"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-soft btn-soft-outline" data-bs-dismiss="modal">Seguir respondiendo</button>
        <button type="button" class="btn btn-soft btn-soft-primary" id="btnIrEnviar">
          <i class="fa-solid fa-paper-plane me-2"></i>Ir a enviar
        </button>
      </div>
    </div>
  </div>
</div>

<?php include './admin/include/perfil.php'; ?>
<?php include './admin/include/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="admin/js/perfil.js"></script>
<script src="admin/js/lib/util.js"></script>

<?php if ($mostrarSelector): ?>
<script>
  function verMisRespuestas(fichaTecnicaId) {
    const modal = new bootstrap.Modal(document.getElementById('modalVerRespuestas'));
    modal.show();

    $('#contenedor-respuestas').html(`
      <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 mb-0 fw-semibold text-muted">Cargando tus respuestas...</p>
      </div>
    `);

    $.ajax({
      url: 'admin/ajax/rqst.php',
      type: 'POST',
      dataType: 'json',
      data: { op: 'respuestavotante', ficha_tecnica_id: fichaTecnicaId },
      success: function(response) {
        if (response.output && response.output.valid) {
          const data = response.output.response;
          const fechaRespuesta = data.fecha_respuesta;
          const respuestas = data.respuestas || [];

          let html = `
            <div class="alert alert-info" style="border-radius:16px;">
              <i class="fas fa-calendar-alt me-2"></i>
              <strong>Fecha de respuesta:</strong> ${formatearFecha(fechaRespuesta)}
            </div>
          `;

          if (respuestas.length === 0) {
            html += '<div class="alert alert-warning" style="border-radius:16px;">No se encontraron respuestas.</div>';
          } else {
            respuestas.forEach((respuesta, index) => {
              html += `
                <div class="card mb-3" style="border-radius:16px; border:1px solid rgba(15,23,42,.10);">
                  <div class="card-body">
                    <div class="text-muted fw-semibold" style="font-size:12px;">Pregunta ${index + 1}</div>
                    <div class="fw-bold mb-2">${escapeHtml(respuesta.texto_pregunta || '')}</div>
              `;

              if (respuesta.opciones_seleccionadas && respuesta.opciones_seleccionadas.length > 0) {
                html += `<div class="mt-2">
                  <div class="fw-bold mb-1">Respuesta:</div>
                  <ul class="list-unstyled ms-2 mb-0">`;
                respuesta.opciones_seleccionadas.forEach(opcion => {
                  html += `<li class="mb-1"><i class="fas fa-check-circle text-success me-2"></i>${escapeHtml(opcion)}</li>`;
                });
                html += `</ul></div>`;
              }

              if (respuesta.respuesta_texto) {
                html += `
                  <div class="mt-2">
                    <div class="fw-bold mb-1">Respuesta:</div>
                    <div class="alert alert-light mb-0" style="border-radius:14px;">
                      ${escapeHtml(respuesta.respuesta_texto)}
                    </div>
                  </div>
                `;
              }

              html += `</div></div>`;
            });
          }

          $('#contenedor-respuestas').html(html);
        } else {
          $('#contenedor-respuestas').html(`
            <div class="alert alert-danger" style="border-radius:16px;">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Error al cargar las respuestas.
            </div>
          `);
        }
      },
      error: function() {
        $('#contenedor-respuestas').html(`
          <div class="alert alert-danger" style="border-radius:16px;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Error de conexión. Por favor, intenta nuevamente.
          </div>
        `);
      }
    });
  }

  function formatearFecha(fechaStr) {
    const fecha = new Date(fechaStr);
    const opciones = { year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' };
    return fecha.toLocaleDateString('es-ES', opciones);
  }

  function escapeHtml(str){
    return String(str || '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }
</script>
<?php else: ?>

<script>
/**
 * ✅ MODO GUIADO (WIZARD)
 * - 1 pregunta a la vez
 * - confirmación visual
 * - animaciones suaves
 * - resumen antes de enviar
 * - borrador en localStorage
 *
 * ⚠️ El envío final se deja al submit normal.
 * Si tu admin/js/contestar_cuestionario.js maneja AJAX, seguirá funcionando.
 */

(function(){
  const fichaId = <?= (int)$fichaTecnicaId ?>;
  const storageKey = "encuesta_borrador_" + fichaId;

  const cards = Array.from(document.querySelectorAll(".pregunta-card"));
  const total = cards.length;

  const form = document.getElementById("form_cuestionario");
  const btnPrev = document.getElementById("btnPrev");
  const btnNext = document.getElementById("btnNext");
  const btnSend = document.getElementById("btnSend");

  const progress = document.getElementById("progress_bar");
  const stepText = document.getElementById("stepText");
  const pctText  = document.getElementById("pctText");
  const helpText = document.getElementById("wizardHelp");

  const pillCounter = document.getElementById("pillCounter");
  const counterText = document.getElementById("counterText");
  const pillStatus = document.getElementById("pillStatus");
  const pillText = document.getElementById("pillText");
  const pillMini = document.getElementById("pillMini");
  const miniText = document.getElementById("miniText");

  const btnGuardarBorrador = document.getElementById("btnGuardarBorrador");
  const btnRevisarResumen = document.getElementById("btnRevisarResumen");

  const modalResumenEl = document.getElementById("modalResumen");
  const resumenBody = document.getElementById("resumenBody");
  const btnIrEnviar = document.getElementById("btnIrEnviar");

  let current = 0;

  // Contador chars
  document.querySelectorAll(".respuesta-texto").forEach(tx => {
    tx.addEventListener("input", () => {
      const wrap = tx.closest(".q-body");
      const cc = wrap.querySelector(".charCount");
      if(cc) cc.textContent = String(tx.value.length);
      setAnsweredStateForCard(tx.closest(".pregunta-card"));
      autosave();
    });
  });

  // Cambios en radios / checkboxes
  document.querySelectorAll(".pregunta-card input[type='radio'], .pregunta-card input[type='checkbox']").forEach(inp => {
    inp.addEventListener("change", () => {
      setAnsweredStateForCard(inp.closest(".pregunta-card"));
      autosave();
      // UX: si es radio, avanzamos suave automáticamente
      if(inp.type === "radio"){
        setTimeout(() => {
          if(validateCurrent(false)){
            next();
          }
        }, 250);
      }
    });
  });

  // Navegación
  btnPrev.addEventListener("click", prev);
  btnNext.addEventListener("click", () => {
    if(!validateCurrent(true)) return;
    next();
  });

  // Guardar borrador
  if(btnGuardarBorrador){
    btnGuardarBorrador.addEventListener("click", () => {
      autosave(true);
      Swal.fire({
        icon:'success',
        title:'Borrador guardado',
        text:'Tus respuestas se guardaron en este dispositivo.',
        confirmButtonText:'Listo'
      });
    });
  }

  // Resumen
  if(btnRevisarResumen){
    btnRevisarResumen.addEventListener("click", () => {
      buildResumen();
      new bootstrap.Modal(modalResumenEl).show();
    });
  }
  if(btnIrEnviar){
    btnIrEnviar.addEventListener("click", () => {
      const m = bootstrap.Modal.getInstance(modalResumenEl);
      if(m) m.hide();
      goToLast();
    });
  }

  // Submit con confirmación
  form.addEventListener("submit", async (e) => {
    // Si tu contestar_cuestionario.js maneja AJAX, puede prevenirDefault allá.
    // Aquí ponemos confirmación ANTES (si ya fue prevenido, no aplica).
    if(e.defaultPrevented) return;

    // Si estamos aquí, es submit normal
    e.preventDefault();

    // Validar todo
    for(let i=0;i<total;i++){
      current = i;
      if(!validateCurrent(true)){
        showCard(i);
        return;
      }
    }

    const answered = countAnswered();
    const result = await Swal.fire({
      icon:'warning',
      title:'Confirmar envío',
      html: `
        <div style="text-align:left; font-weight:800; color:#334155;">
          Estás a punto de enviar tu encuesta.<br><br>
          <div style="display:flex; gap:10px; align-items:center; padding:10px 12px; border-radius:14px; background:rgba(32,66,127,.08); border:1px solid rgba(32,66,127,.16);">
            <i class="fa-solid fa-list-check" style="color:#20427F;"></i>
            <div>Respuestas completadas: <b>${answered}/${total}</b></div>
          </div>
          <div style="margin-top:10px; color:#64748b;">
            Después de enviar, no podrás editar tus respuestas.
          </div>
        </div>
      `,
      showCancelButton:true,
      confirmButtonText:'Sí, enviar',
      cancelButtonText:'Revisar',
      reverseButtons:true
    });

    if(!result.isConfirmed) return;

    // Si tu sistema es submit tradicional, lo disparo:
    // (Si tu backend recibe por AJAX, tu otro JS puede engancharse.)
    localStorage.removeItem(storageKey);
    form.submit();
  });

  // Restaurar borrador
  restore();

  // Inicializar
  showCard(0);
  updateUI();

  function showCard(idx){
    cards.forEach((c,i)=>{
      c.style.display = (i===idx) ? "" : "none";
      c.classList.toggle("active", i===idx);
    });
    current = idx;
    updateUI();

    // scroll suave
    cards[idx].scrollIntoView({ behavior:'smooth', block:'start' });
  }

  function next(){
    if(current < total-1){
      showCard(current+1);
    }
  }

  function prev(){
    if(current > 0){
      showCard(current-1);
    }
  }

  function goToLast(){
    showCard(total-1);
  }

  function validateCurrent(showAlert){
    const card = cards[current];
    const hasText = card.querySelector("textarea");
    const radios = card.querySelectorAll("input[type='radio']");
    const checks = card.querySelectorAll("input[type='checkbox']");

    let ok = false;

    if(hasText){
      ok = (hasText.value || "").trim().length >= 2;
      if(!ok && showAlert){
        toastNeed("Escribe una respuesta (mínimo 2 caracteres).");
      }
      return ok;
    }

    if(radios.length){
      ok = Array.from(radios).some(r=>r.checked);
      if(!ok && showAlert){
        toastNeed("Selecciona una opción para continuar.");
      }
      return ok;
    }

    if(checks.length){
      ok = Array.from(checks).some(c=>c.checked);
      if(!ok && showAlert){
        toastNeed("Selecciona al menos una opción para continuar.");
      }
      return ok;
    }

    // si no hay inputs (caso raro)
    return true;
  }

  function toastNeed(msg){
    Swal.fire({
      toast:true,
      position:'top',
      icon:'info',
      title: msg,
      showConfirmButton:false,
      timer: 2200,
      timerProgressBar:true
    });
  }

  function setAnsweredStateForCard(card){
    const status = card.querySelector(".qStatusText");
    const textarea = card.querySelector("textarea");
    const radios = card.querySelectorAll("input[type='radio']");
    const checks = card.querySelectorAll("input[type='checkbox']");

    let answered = false;

    if(textarea) answered = (textarea.value||"").trim().length >= 2;
    else if(radios.length) answered = Array.from(radios).some(r=>r.checked);
    else if(checks.length) answered = Array.from(checks).some(c=>c.checked);

    if(status){
      status.textContent = answered ? "Respuesta lista ✅" : "Sin responder aún";
      status.style.color = answered ? "#166534" : "#64748b";
    }

    updateUI();
  }

  function countAnswered(){
    let n=0;
    cards.forEach(card=>{
      const textarea = card.querySelector("textarea");
      const radios = card.querySelectorAll("input[type='radio']");
      const checks = card.querySelectorAll("input[type='checkbox']");
      let answered = false;

      if(textarea) answered = (textarea.value||"").trim().length >= 2;
      else if(radios.length) answered = Array.from(radios).some(r=>r.checked);
      else if(checks.length) answered = Array.from(checks).some(c=>c.checked);

      if(answered) n++;
    });
    return n;
  }

  function updateUI(){
    if(total <= 0) return;

    const answered = countAnswered();
    const pct = Math.round((answered/total)*100);

    progress.style.width = pct + "%";
    stepText.textContent = "Paso " + (current+1);
    pctText.textContent = pct + "%";

    counterText.textContent = answered + "/" + total + " respondidas";

    // botoncitos
    btnPrev.disabled = (current === 0);

    // último paso
    if(current === total-1){
      btnNext.classList.add("d-none");
      btnSend.classList.remove("d-none");
    }else{
      btnNext.classList.remove("d-none");
      btnSend.classList.add("d-none");
    }

    // ayudas
    const card = cards[current];
    const hasText = !!card.querySelector("textarea");
    const isRadio = card.querySelectorAll("input[type='radio']").length > 0;
    const isCheck = card.querySelectorAll("input[type='checkbox']").length > 0;

    if(hasText) helpText.textContent = "Escribe tu respuesta y luego toca “Continuar”.";
    else if(isRadio) helpText.textContent = "Toca una opción (avance automático).";
    else if(isCheck) helpText.textContent = "Marca una o varias opciones y toca “Continuar”.";
    else helpText.textContent = "Completa y continúa.";

    // estado general
    if(answered === total){
      pillStatus.classList.remove("warn");
      pillStatus.classList.add("ok");
      pillText.textContent = "Lista para enviar";
      if(pillMini){
        pillMini.classList.remove("warn");
        pillMini.classList.add("ok");
        miniText.textContent = "Todo listo ✅";
      }
    }else{
      pillStatus.classList.remove("ok");
      pillStatus.classList.add("warn");
      pillText.textContent = "En progreso";
      if(pillMini){
        pillMini.classList.remove("ok");
        pillMini.classList.add("warn");
        miniText.textContent = "Responde para continuar";
      }
    }
  }

  function autosave(force=false){
    try{
      const payload = {};
      cards.forEach(card=>{
        const pid = card.getAttribute("data-pregunta-id");
        const textarea = card.querySelector("textarea");
        const radios = card.querySelectorAll("input[type='radio']");
        const checks = card.querySelectorAll("input[type='checkbox']");

        if(textarea){
          payload[pid] = { type:"text", value: textarea.value || "" };
        }else if(radios.length){
          const sel = Array.from(radios).find(r=>r.checked);
          payload[pid] = { type:"radio", value: sel ? sel.value : "" };
        }else if(checks.length){
          const arr = Array.from(checks).filter(c=>c.checked).map(c=>c.value);
          payload[pid] = { type:"check", value: arr };
        }
      });

      localStorage.setItem(storageKey, JSON.stringify({
        current,
        payload,
        t: Date.now()
      }));

      if(force) console.log("BORRADOR GUARDADO", storageKey);
    }catch(e){
      // silencio
    }
  }

  function restore(){
    try{
      const raw = localStorage.getItem(storageKey);
      if(!raw) return;

      const data = JSON.parse(raw);
      if(!data || !data.payload) return;

      Object.keys(data.payload).forEach(pid=>{
        const item = data.payload[pid];
        const card = document.querySelector(`.pregunta-card[data-pregunta-id="${pid}"]`);
        if(!card) return;

        if(item.type === "text"){
          const tx = card.querySelector("textarea");
          if(tx) tx.value = item.value || "";
          const cc = card.querySelector(".charCount");
          if(cc && tx) cc.textContent = String(tx.value.length);
        }
        if(item.type === "radio"){
          const r = card.querySelector(`input[type="radio"][value="${item.value}"]`);
          if(r) r.checked = true;
        }
        if(item.type === "check" && Array.isArray(item.value)){
          item.value.forEach(v=>{
            const c = card.querySelector(`input[type="checkbox"][value="${v}"]`);
            if(c) c.checked = true;
          });
        }

        setAnsweredStateForCard(card);
      });

      if(typeof data.current === "number" && data.current >=0 && data.current < total){
        showCard(data.current);
      }
    }catch(e){}
  }

  function buildResumen(){
    const rows = [];
    cards.forEach((card, i) => {
      const pid = card.getAttribute("data-pregunta-id");
      const title = card.querySelector(".q-title") ? card.querySelector(".q-title").textContent.trim() : ("Pregunta " + (i+1));

      const textarea = card.querySelector("textarea");
      const radios = card.querySelectorAll("input[type='radio']");
      const checks = card.querySelectorAll("input[type='checkbox']");
      let ans = "";

      if(textarea){
        ans = (textarea.value || "").trim();
      }else if(radios.length){
        const sel = Array.from(radios).find(r=>r.checked);
        if(sel){
          const lab = sel.closest("label.opt");
          ans = lab ? lab.querySelector(".form-check-label").textContent.trim() : "Seleccionado";
        }
      }else if(checks.length){
        const sels = Array.from(checks).filter(c=>c.checked);
        ans = sels.map(s=>{
          const lab = s.closest("label.opt");
          return lab ? lab.querySelector(".form-check-label").textContent.trim() : "Seleccionado";
        }).join(", ");
      }

      rows.push(`
        <div class="card mb-2" style="border-radius:16px; border:1px solid rgba(15,23,42,.10);">
          <div class="card-body">
            <div class="text-muted fw-semibold" style="font-size:12px;">${i+1}/${total}</div>
            <div class="fw-bold mb-2">${escapeHtml(title)}</div>
            <div class="alert alert-light mb-0" style="border-radius:14px;">
              ${ans ? escapeHtml(ans) : '<span class="text-danger fw-bold">Sin respuesta</span>'}
            </div>
          </div>
        </div>
      `);
    });

    resumenBody.innerHTML = rows.join("");
  }

  function escapeHtml(str){
    return String(str || '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

})();
</script>

<!-- Si quieres dejar tu JS original, puedes mantenerlo.
     Si te genera conflicto con el submit, lo quitas. -->
<script src="admin/js/contestar_cuestionario.js"></script>

<?php endif; ?>
</body>
</html>
