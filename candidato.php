<?php
require './admin/include/generic_classes.php';
include './admin/include/generic_info_configuracion.php';
require './admin/classes/PreguntaGrilla.php';
require './admin/classes/Votantes.php';

if (isset($_POST['registro_data']) && !empty($_POST['registro_data'])) {
    $itemJson = $_POST['registro_data'];
    $grilla = json_decode($itemJson, true);
    if ($grilla === null && json_last_error() !== JSON_ERROR_NONE) {
        die("Error al decodificar los datos JSON: " . json_last_error_msg());
    }
} else {
    echo "Error: Acceso inválido o no se recibieron datos del registro.";
    exit;
}

// Cargar preguntas y subpreguntas desde la base de datos FILTRADAS por grilla_id
$grilla_id = isset($grilla['id']) ? $grilla['id'] : 0;
$preguntasResponse = PreguntaGrilla::obtenerPreguntasConSubpreguntas(['grilla_id' => $grilla_id]);
$preguntasData = [];
$subpreguntasData = [];

if ($preguntasResponse['output']['valid']) {
    $preguntasData = $preguntasResponse['output']['response']['preguntas'];
    $subpreguntasData = $preguntasResponse['output']['response']['subpreguntas'];
}

if (empty($preguntasData)) {
    die("Error: No se encontraron preguntas configuradas en el sistema. Por favor, configure las preguntas desde el panel de administración.");
}

// Cargar votantes activos que NO han votado hoy en esta grilla
$votantesData = [];
$db = new DbConection();
$pdo = $db->openConect();

try {
    $qVotantes = "SELECT v.*
                  FROM " . $db->getTable('tbl_votantes') . " v
                  WHERE v.estado = 'activo'
                    AND v.id NOT IN (
                        SELECT gsv.tbl_votante_id
                        FROM " . $db->getTable('tbl_grilla_sesion_votacion') . " gsv
                        WHERE gsv.tbl_grilla_id = :grilla_id
                          AND DATE(gsv.dtcreate) = CURDATE()
                    )
                  ORDER BY v.nombre_completo ASC";

    $stmt = $pdo->prepare($qVotantes);
    $stmt->execute([':grilla_id' => $grilla_id]);
    $votantesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $votantesData = [];
} finally {
    $db->closeConect();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Elecciones Colombia - Estudio (Grilla)</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Bootstrap + main -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">

  <!-- Select2 (lo dejo por si luego lo usas en esta pantalla) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

  <style>
    :root{
      --brand:#13357b;
      --brand2:#0b1a89;
      --ink:#0f172a;
      --muted:#64748b;
      --bg:#f6f8fc;
      --card:#ffffff;
      --stroke: rgba(15,23,42,.12);
      --shadow: 0 18px 45px rgba(2,6,23,.10);
      --shadow2: 0 10px 22px rgba(2,6,23,.08);
      --r-xl: 22px;
      --r-lg: 18px;
      --r-md: 14px;
    }

    body{
      font-family: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:
        radial-gradient(1000px 600px at 20% 0%, rgba(19,53,123,.16), transparent 60%),
        radial-gradient(900px 560px at 90% 10%, rgba(11,26,137,.12), transparent 55%),
        var(--bg);
      color: var(--ink);
    }

    /* NAVBAR PRO */
    .navbar-pro{
      background: #fff !important;
      border-bottom: 1px solid rgba(15,23,42,.08);
      box-shadow: 0 12px 30px rgba(2,6,23,.06);
    }
    .navbar-pro .nav-link{
      font-weight: 800;
      color: #0f172a !important;
      border-radius: 12px;
      padding: 10px 12px !important;
    }
    .navbar-pro .nav-link:hover{
      background: rgba(19,53,123,.06);
      color: var(--brand2) !important;
    }
    .navbar-pro .navbar-toggler{
      border: 1px solid rgba(15,23,42,.10);
      border-radius: 14px;
      width: 46px;
      height: 42px;
      display:flex;
      align-items:center;
      justify-content:center;
    }

    /* HERO */
    .hero{
      background: linear-gradient(135deg, var(--brand), var(--brand2));
      color: #fff;
      border-radius: var(--r-xl);
      box-shadow: var(--shadow);
      padding: 18px 18px;
      overflow: hidden;
      position: relative;
      margin-bottom: 18px;
    }
    .hero:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(520px 220px at 15% 15%, rgba(255,255,255,.22), transparent 60%),
        radial-gradient(600px 260px at 80% 20%, rgba(255,255,255,.14), transparent 60%),
        linear-gradient(180deg, rgba(255,255,255,.08), transparent 55%);
      pointer-events:none;
    }
    .hero .content{ position: relative; z-index: 2; }
    .hero h1{
      margin:0;
      font-weight: 900;
      letter-spacing: -.6px;
      font-size: 24px;
    }
    .hero p{
      margin: 6px 0 0;
      color: rgba(255,255,255,.90);
      font-weight: 650;
    }
    .chip{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.22);
      font-weight: 800;
      font-size: 12px;
      margin-top: 10px;
    }

    /* CARDS */
    .panel{
      background: var(--card);
      border: 1px solid var(--stroke);
      border-radius: var(--r-xl);
      box-shadow: var(--shadow2);
      overflow: hidden;
    }
    .panel-header{
      padding: 14px 16px;
      border-bottom: 1px solid var(--stroke);
      background: linear-gradient(180deg, rgba(19,53,123,.07), transparent);
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .panel-header h5{
      margin:0;
      font-weight: 900;
      letter-spacing: -.4px;
    }
    .panel-header .sub{
      margin:0;
      color: var(--muted);
      font-weight: 650;
      font-size: 13px;
    }

    /* TABLE PRO */
    .table-wrap{
      overflow:auto;
      border-radius: 0 0 var(--r-xl) var(--r-xl);
    }
    table.tabla_grilla{
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      min-width: 920px; /* para scroll horizontal elegante en tablets */
    }
    .tabla_grilla thead th{
      position: sticky;
      top: 0;
      z-index: 5;
      background: #fff;
      border-bottom: 1px solid rgba(15,23,42,.12);
      font-weight: 900;
      font-size: 12px;
      color: #0f172a;
      text-transform: uppercase;
      letter-spacing: .7px;
      padding: 14px 12px;
      white-space: nowrap;
    }
    .tabla_grilla tbody td{
      border-bottom: 1px solid rgba(15,23,42,.08);
      padding: 12px 12px;
      vertical-align: middle;
      background: #fff;
    }
    .tabla_grilla tbody tr:hover td{
      background: rgba(19,53,123,.03);
    }

    /* Candidato */
    .candidato-container{
      display:flex;
      align-items:center;
      gap: 10px;
      min-width: 260px;
    }
    .candidato-foto{
      width: 42px;
      height: 42px;
      border-radius: 14px;
      object-fit: cover;
      border: 1px solid rgba(15,23,42,.12);
      box-shadow: 0 10px 18px rgba(2,6,23,.08);
      background:#fff;
    }
    .candidato-detalles strong{
      font-weight: 900;
      letter-spacing: -.2px;
      display:block;
    }

    /* Toggle buttons premium */
    .toggle{
      display:flex;
      gap: 8px;
      justify-content:center;
      align-items:center;
    }
    .toggle-btn{
      border: 1px solid rgba(15,23,42,.14);
      background: #fff;
      color: #0f172a;
      border-radius: 999px;
      padding: 8px 12px;
      font-weight: 900;
      font-size: 12px;
      min-width: 44px;
      transition: all .18s ease;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap: 6px;
      box-shadow: 0 10px 18px rgba(2,6,23,.05);
    }
    .toggle-btn:hover{
      transform: translateY(-1px);
      border-color: rgba(19,53,123,.28);
      box-shadow: 0 12px 22px rgba(2,6,23,.08);
    }
    .toggle-btn.si.active{
      background: linear-gradient(135deg, #16a34a, #22c55e);
      border-color: rgba(34,197,94,.40);
      color:#fff;
    }
    .toggle-btn.no.active{
      background: linear-gradient(135deg, #ef4444, #f97316);
      border-color: rgba(239,68,68,.35);
      color:#fff;
    }

    /* no aplica */
    td.no-aplica{
      background: rgba(148,163,184,.14) !important;
      color: rgba(100,116,139,.9);
      text-align:center;
    }
    .no-aplica-text{
      display:inline-block;
      font-weight: 900;
      letter-spacing: .6px;
      color: rgba(100,116,139,.9);
    }

    /* Right column card */
    .card-candidatos-aprobados{
      border-radius: var(--r-xl);
      border:1px solid var(--stroke);
      box-shadow: var(--shadow2);
      overflow:hidden;
      background:#fff;
    }
    .aprobados-header{
      background: linear-gradient(135deg, var(--brand), var(--brand2));
      color:#fff;
      padding: 14px 14px;
      border-bottom: 1px solid rgba(255,255,255,.18);
    }
    .aprobados-header h6{
      margin:0;
      font-weight: 900;
      letter-spacing: -.3px;
    }
    .aprobados-header small{
      color: rgba(255,255,255,.85);
      font-weight: 650;
    }
    .badge-count{
      background: rgba(255,255,255,.16);
      border: 1px solid rgba(255,255,255,.22);
      color:#fff;
      font-weight: 900;
      border-radius: 999px;
      padding: 8px 12px;
      min-width: 56px;
      text-align:center;
    }
    #candidatosAprobadosContainer{
      max-height: calc(100vh - 250px);
      overflow: auto;
      scrollbar-width: thin;
    }
    #candidatosAprobadosContainer::-webkit-scrollbar{ width: 10px; }
    #candidatosAprobadosContainer::-webkit-scrollbar-thumb{
      background: rgba(2,6,23,.14);
      border-radius: 999px;
    }

    /* Footer actions sticky */
    .actions-bar{
      position: sticky;
      bottom: 0;
      z-index: 20;
      background: rgba(255,255,255,.88);
      backdrop-filter: blur(10px);
      border-top: 1px solid rgba(15,23,42,.10);
      padding: 12px;
      display:flex;
      justify-content:flex-end;
      gap: 10px;
    }
    .btn-pro{
      border-radius: 14px;
      padding: 10px 14px;
      font-weight: 900;
    }
    .btn-pro-primary{
      background: linear-gradient(135deg, var(--brand), var(--brand2));
      border:0;
      color:#fff;
      box-shadow: 0 12px 25px rgba(19,53,123,.20);
    }
    .btn-pro-primary:hover{ filter: brightness(1.03); color:#fff; }
    .btn-pro-outline{
      background:#fff;
      border: 1px solid rgba(15,23,42,.14);
      color:#0f172a;
    }
    .btn-pro-outline:hover{ background: rgba(19,53,123,.06); border-color: rgba(19,53,123,.26); }

    @media (max-width: 992px){
      .hero h1{ font-size: 20px; }
      .tabla_grilla{ min-width: 860px; }
      .actions-bar{ justify-content: center; }
    }
  </style>
</head>
<body>

<div class="container-fluid p-0">
  <nav class="navbar navbar-expand-lg navbar-light sticky-top navbar-pro px-3 px-lg-4 py-2">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
      <span class="fa fa-bars"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarCollapse">

      <!-- Desktop -->
      <div class="navbar-nav ms-auto d-none d-lg-flex align-items-center gap-1">
        <a href="dash_responder.php" class="nav-item nav-link">
          <i class="fas fa-tasks me-2"></i>Pendiente por responder
        </a>
        <a href="visualizar.php" class="nav-item nav-link">
          <i class="fas fa-chart-bar me-2"></i>Visualización
        </a>

        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle me-2 text-primary fa-2x"></i>
            <span class="welcome-text">
              <?php
              $nombreCompleto = $_SESSION['session_user']['nombre_completo'] ?? $_SESSION['session_user']['usuario'] ?? 'Usuario';
              $partes = explode(' ', $nombreCompleto);
              $dosNombres = implode(' ', array_slice($partes, 0, 2));
              echo htmlspecialchars($dosNombres);
              ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="#" onclick="PERFIL.loadProfile(<?php echo $_SESSION['session_user']['id']; ?>); return false;">
                <i class="fas fa-user me-2"></i>Mi Perfil
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Mobile -->
      <div class="navbar-nav ms-auto d-lg-none w-100 mt-3">
        <div class="p-3 mb-2" style="border-radius:18px; background: rgba(19,53,123,.06); border:1px solid rgba(19,53,123,.12);">
          <div class="d-flex align-items-center">
            <i class="fas fa-user-circle me-3 text-primary fa-2x"></i>
            <div>
              <div class="fw-bold">
                <?php
                $nombreCompleto = $_SESSION['session_user']['nombre_completo'] ?? $_SESSION['session_user']['usuario'] ?? 'Usuario';
                $partes = explode(' ', $nombreCompleto);
                $dosNombres = implode(' ', array_slice($partes, 0, 2));
                echo htmlspecialchars($dosNombres);
                ?>
              </div>
              <div class="text-muted fw-semibold" style="font-size:12px;">Panel de votaciones</div>
            </div>
          </div>
        </div>

        <a href="dash_responder.php" class="nav-item nav-link d-flex align-items-center">
          <i class="fas fa-tasks me-2"></i>Pendientes por responder
        </a>
        <a href="visualizar.php" class="nav-item nav-link d-flex align-items-center">
          <i class="fas fa-chart-bar me-2"></i>Visualización
        </a>
        <a href="#" class="nav-item nav-link d-flex align-items-center" onclick="PERFIL.loadProfile(<?php echo $_SESSION['session_user']['id']; ?>); return false;">
          <i class="fas fa-user me-2"></i>Mi Perfil
        </a>
        <a href="logout.php" class="nav-item nav-link d-flex align-items-center text-danger">
          <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
        </a>
      </div>

    </div>
  </nav>
</div>

<div class="container-fluid py-4">
  <div class="container">

    <!-- HERO -->
    <div class="hero">
      <div class="content">
        <h1><i class="fas fa-layer-group me-2"></i><?= htmlspecialchars($grilla['grilla']); ?></h1>
        <p>Responde por candidato y pregunta. El sistema te mostrará los <b>candidatos aprobados</b> en tiempo real.</p>
        <div class="d-flex flex-wrap gap-2">
          <span class="chip"><i class="fas fa-check-circle"></i> Interfaz rápida</span>
          <span class="chip"><i class="fas fa-bolt"></i> Cambios inmediatos</span>
          <span class="chip"><i class="fas fa-shield-alt"></i> Sesión segura</span>
        </div>
      </div>
    </div>

    <div class="row g-3">

      <!-- LEFT -->
      <div class="col-lg-8 col-md-12">
        <div class="panel">
          <div class="panel-header">
            <div>
              <h5><i class="fas fa-users me-2 text-primary"></i>Candidatos</h5>
              <p class="sub">Marca cada respuesta. Las siguientes preguntas se habilitan según la condición configurada.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <span class="badge rounded-pill bg-light text-dark" style="font-weight:900; border:1px solid rgba(15,23,42,.10);">
                Preguntas: <?= count($preguntasData) ?>
              </span>
              <span class="badge rounded-pill bg-light text-dark" style="font-weight:900; border:1px solid rgba(15,23,42,.10);">
                Candidatos: <?= isset($grilla['candidatos']) && is_array($grilla['candidatos']) ? count($grilla['candidatos']) : 0 ?>
              </span>
            </div>
          </div>

          <div class="table-wrap">
            <table class="tabla_grilla">
              <thead class="table-header">
                <tr>
                  <th style="min-width:300px;">Candidatos</th>
                  <?php foreach ($preguntasData as $pregunta): ?>
                    <th><?= htmlspecialchars(strtoupper($pregunta['texto_pregunta'])) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
              <?php
              if (isset($grilla['candidatos']) && is_array($grilla['candidatos'])) {
                foreach ($grilla['candidatos'] as $index => $candidato) {
                  $candidatoId = $candidato['id'];
                  $nombreCompleto = htmlspecialchars($candidato['nombre_completo']);
                  $fotoUrl = !empty($candidato['foto'])
                    ? 'assets/img/admin/' . htmlspecialchars($candidato['foto'])
                    : 'assets/img/candidato.png';
              ?>
                <tr data-candidato-id="<?php echo $candidatoId; ?>">
                  <td class="candidato-info">
                    <div class="candidato-container">
                      <img src="<?php echo $fotoUrl; ?>" alt="Foto <?php echo $nombreCompleto; ?>" class="candidato-foto">
                      <div class="candidato-detalles">
                        <strong><?php echo $nombreCompleto; ?></strong>
                        <span class="text-muted fw-semibold" style="font-size:12px;">
                          <i class="fas fa-user-tag me-1"></i> Evaluación por criterios
                        </span>
                      </div>
                    </div>
                  </td>

                  <?php foreach ($preguntasData as $indexPregunta => $pregunta):
                    $codigoPregunta = $pregunta['codigo_pregunta'];
                    $opcionesRespuesta = json_decode($pregunta['opciones_respuesta'], true);
                    $esPrimera = ($indexPregunta === 0);

                    $claseInicial = $esPrimera ? '' : 'no-aplica';
                    $toggleDisplay = $esPrimera ? 'flex' : 'none';
                    $mostrarNoAplica = !$esPrimera;
                  ?>
                    <td
                      data-pregunta="<?php echo $codigoPregunta; ?>"
                      data-orden="<?php echo $pregunta['orden']; ?>"
                      data-habilita-subpreguntas="<?php echo $pregunta['habilita_subpreguntas'] ? '1' : '0'; ?>"
                      data-condicion="<?php echo htmlspecialchars($pregunta['condicion_habilitacion'] ?? ''); ?>"
                      class="<?php echo $claseInicial; ?>"
                      style="min-width:140px;"
                    >
                      <div class="toggle" style="display: <?php echo $toggleDisplay; ?>;">
                        <?php
                        if ($opcionesRespuesta && is_array($opcionesRespuesta)) {
                          foreach ($opcionesRespuesta as $indexOpcion => $opcion) {
                            $esSegundaOpcion = ($indexOpcion === 1);
                            $claseActiva = ($esPrimera && $esSegundaOpcion) ? 'active' : '';
                            $icono = '';
                            $texto = strtoupper($opcion);
                            $claseBoton = $indexOpcion === 0 ? 'si' : 'no';

                            if ($opcion === 'si') { $icono = '<i class="fas fa-check"></i>'; $claseBoton = 'si'; }
                            elseif ($opcion === 'no') { $icono = '<i class="fas fa-times"></i>'; $claseBoton = 'no'; }
                            elseif ($opcion === 'favorable') { $texto = 'SÍ'; $claseBoton = 'si'; }
                            elseif ($opcion === 'desfavorable') { $texto = 'NO'; $claseBoton = 'no'; }
                        ?>
                          <button class="toggle-btn <?php echo $claseBoton; ?> <?php echo $claseActiva; ?>"
                                  data-value="<?php echo $opcion; ?>"
                                  type="button">
                            <?php echo !empty($icono) ? $icono : $texto; ?>
                          </button>
                        <?php
                          }
                        }
                        ?>
                      </div>

                      <?php if ($mostrarNoAplica): ?>
                        <span class="no-aplica-text">--</span>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php
                }
              } else {
                $totalColumnas = count($preguntasData) + 1;
                echo '<tr><td colspan="'.$totalColumnas.'" class="text-center text-muted fw-bold">No hay candidatos disponibles</td></tr>';
              }
              ?>
              </tbody>
            </table>
          </div>

          <div class="actions-bar">
            <button type="button" class="btn btn-pro btn-pro-outline" onclick="window.location.href='grilla.php';">
              <i class="fas fa-arrow-left me-2"></i>Volver
            </button>
            <button type="button" class="btn btn-pro btn-pro-primary" id="btnGuardarRespuestas">
              <i class="fas fa-save me-2"></i>Guardar respuestas
            </button>
          </div>

        </div>
      </div>

      <!-- RIGHT -->
      <div class="col-lg-4 col-md-12">
        <div class="card-candidatos-aprobados h-100">
          <div class="aprobados-header">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="mb-1">
                  <i class="fas fa-trophy" style="color:#a38630;"></i> Candidatos aprobados
                </h6>
                <small>Pasaron todas las preguntas</small>
              </div>
              <div class="badge-count">
                <span id="totalAprobados">0</span>
              </div>
            </div>
          </div>

          <div class="p-2" id="candidatosAprobadosContainer">
            <div class="text-center text-muted py-4" id="mensajeVacio">
              <i class="fas fa-info-circle fa-2x mb-2"></i>
              <p class="mb-0" style="font-size: 12px; font-weight:800;">
                Selecciona respuestas para ver aprobados
              </p>
            </div>
          </div>
        </div>

        <div class="mt-3 p-3" style="border-radius:18px; background:#fff; border:1px solid rgba(15,23,42,.10); box-shadow: var(--shadow2);">
          <div class="d-flex gap-2">
            <div style="width:42px; height:42px; border-radius:14px; background: rgba(19,53,123,.08); display:flex; align-items:center; justify-content:center;">
              <i class="fas fa-lightbulb" style="color: var(--brand2);"></i>
            </div>
            <div>
              <div class="fw-bold">Tip rápido</div>
              <div class="text-muted fw-semibold" style="font-size:12px;">
                Completa la primera columna por candidato y el sistema habilita la siguiente.
              </div>
            </div>
          </div>
        </div>

      </div>

    </div><!-- row -->
  </div>
</div>

<?php include './admin/include/perfil.php'; ?>
<?php include './admin/include/footer.php'; ?>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="admin/js/lib/util.js"></script>
<script src="admin/js/votaciones_grilla.js"></script>
<script>
  $(document).ready(function() {
    const grillaData = <?php echo json_encode($grilla ?? []); ?>;
    const preguntasConfig = <?php echo json_encode($preguntasData ?? []); ?>;
    const subpreguntasConfig = <?php echo json_encode($subpreguntasData ?? []); ?>;
    EstudioVotaciones.init(grillaData, preguntasConfig, subpreguntasConfig);
  });
</script>

</body>
</html>
