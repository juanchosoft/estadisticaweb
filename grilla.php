<?php
require './admin/include/generic_classes.php';
include './admin/classes/Usuario.php';
include './admin/classes/CargosPublicos.php';
include './admin/classes/Departamento.php';
include './admin/classes/Grilla.php';
include './admin/classes/GrillaCandidatoRespuesta.php';

// Validar acceso según opción activa
$config = Util::getInformacionConfiguracion();
$opcionActivaWeb = $config[0]['opcion_activa_web'] ?? '';
if ($opcionActivaWeb !== 'estudio') {
    if ($opcionActivaWeb === 'sondeo') {
        header('Location: sondeo.php');
    } else {
        header('Location: encuesta.php');
    }
    exit();
}

// Información de grillas
$arr = Grilla::getAll(null);
$isvalidGrilla = $arr['output']['valid'];
$arr = $arr['output']['response'];

// Obtener ID del votante actual
$usuarioId = SessionData::getUserId();

// Obtener grillas ya votadas por este usuario
$grillasVotadas = GrillaCandidatoRespuesta::getGrillasVotadasPorUsuario($usuarioId);

// Separar grillas en disponibles y ya votadas
$grillasDisponibles = [];
$grillasYaVotadas = [];

foreach ($arr as &$item) {
    $grillaId = intval($item['id']);
    $yaVotado = in_array($grillaId, $grillasVotadas);

    $item['ya_votado'] = $yaVotado;

    if ($yaVotado) {
        $grillasYaVotadas[] = $item;
    } else {
        $grillasDisponibles[] = $item;
    }
}

// Cargos públicos
$arrCargosPub = CargosPublicos::getAll(null);
$arrCargosPub = $arrCargosPub['output']['response'];
$optionCargosPub = "";
foreach ($arrCargosPub as $val) {
    $optionCargosPub .= "<option value='" . $val['id'] . "'>" . $val['nombre'] . " </option>";
}

// Departamentos
$arrDep = Departamento::getAll(null);
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
        <meta charset="utf-8">
        <title>Elecciones Colombia</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="" name="keywords">
        <meta content="" name="description">
        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600&family=Roboto&display=swap" rel="stylesheet"> 
        <!-- Icon Font Stylesheet -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
        <!-- Libraries Stylesheet -->
        <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
        <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
        <!-- Customized Bootstrap Stylesheet -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">
    </head>

<body>

<!-- Spinner -->
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" style="width:3rem; height:3rem;" role="status"></div>
</div>
<?php include './admin/include/menusecond.php'; ?>
<!-- TABLA DE ESTUDIOS -->
<div class="container my-5">

    <!-- ESTUDIOS DISPONIBLES -->
    <?php if (count($grillasDisponibles) > 0): ?>
    <div class="mb-5">
        <div class="mx-auto text-center mb-4" style="max-width: 900px;">
            <h3 class="section-title px-3">Estudios Disponibles</h3>
            <p class="text-muted">Estos estudios están disponibles para que participes</p>
        </div>
        <div class="table-responsive d-flex justify-content-center">
            <table class="table table-striped table-sm mx-auto" style="max-width: 750px;">
                <thead class="text-center" style="background: #13357b; color: #fff;">
                    <tr>
                        <th>Ver Estudio</th>
                        <th>Grilla</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($grillasDisponibles as $item):
                    $itemJson = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
                    $grillaId = intval($item['id']);
                ?>
                <tr data-grilla-id="<?= $grillaId ?>" data-ya-votado="false">
                    <td class="text-center align-middle">
                        <button class="btn btn-sm" style="background: #163377; color: #fff;"
                                onclick="GRILLA.showGrilla('<?= $itemJson ?>', <?= $grillaId ?>)">
                            <i class="fas fa-vote-yea"></i>
                        </button>
                    </td>
                    <td class="text-center"><?= htmlspecialchars($item['grilla']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['dtcreate']) ?></td>
                    <td class="text-center">
                        <?= ($item['habilitado'] === 'si')
                            ? '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Activo</span>'
                            : '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactivo</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ESTUDIOS YA VOTADOS -->
    <?php if (count($grillasYaVotadas) > 0): ?>
    <div class="mb-5">
        <div class="mx-auto text-center mb-4" style="max-width: 900px;">
            <h3 class="section-title px-3">Estudios Completados</h3>
            <p class="text-muted">Ya has participado en estos estudios</p>
        </div>
        <div class="table-responsive d-flex justify-content-center">
            <table class="table table-striped table-sm mx-auto" style="max-width: 750px;">
                <thead class="text-center" style="background: #6c757d; color: #fff;">
                    <tr>
                        <th>Grilla</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Participación</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($grillasYaVotadas as $item): ?>
                <tr style="opacity: 0.7;">
                    <td class="text-center"><?= htmlspecialchars($item['grilla']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['dtcreate']) ?></td>
                    <td class="text-center">
                        <?= ($item['habilitado'] === 'si')
                            ? '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Activo</span>'
                            : '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactivo</span>' ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary ms-2" onclick="GRILLA.verMisRespuestas(<?= $item['id'] ?>)">
                            <i class="fas fa-eye"></i> Ver respuestas
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($grillasDisponibles) === 0 && count($grillasYaVotadas) === 0): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> No hay estudios electorales disponibles en este momento.
    </div>
    <?php endif; ?>

</div>

<!-- Modal para Ver Respuestas -->
<div class="modal fade" id="modalVerRespuestas" tabindex="-1" aria-labelledby="modalVerRespuestasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #13357b;">
                <h5 class="modal-title text-white" id="modalVerRespuestasLabel">
                    <i class="fas fa-clipboard-check me-2"></i>Mis Respuestas - Estudio Electoral
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="contenedor-respuestas">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando tus respuestas...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include './admin/include/perfil.php';?>
<?php include './admin/include/footer.php'; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="admin/js/lib/util.js"></script>
<script src="admin/js/opcion_preguntas.js"></script>
<script src="js/main.js"></script>
<script src="admin/js/grilla.js"></script>
<script src="admin/js/perfil.js"></script>
<script>
    GRILLA.handleCargoPublicoChange();
    GRILLA.handleSondeParaCargoPublicoChange();
    setTimeout(function() {
      DEPARTAMENTO.getMunicipios();
    }, 1000);
</script>
</body>
</html>
