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
    
    if ($aplicaCargos === 'no') {
        return 'General';
    }
    
    $departamento = $sondeo['codigo_departamento'] ?? '';
    $municipio = $sondeo['codigo_municipio'] ?? '';
    
    if (empty($departamento)) {
        return 'Nacional';
    } elseif (!empty($departamento) && empty($municipio)) {
        return 'Departamental';
    } else {
        return 'Municipal';
    }
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
        if ($tipoDB === 'si/no') {
            $item['tipo'] = 'si_no';
        } else {
            $item['tipo'] = 'candidatos';
        }
    }

    // Determinar alcance
    $item['alcance'] = determinarAlcanceSondeo($item);

    // Marcar si el sondeo ya fue votado
    $yaVotado = in_array($sondeoId, $sondeosVotados);
    $item['contestado'] = $yaVotado;

    // Separar en arrays diferentes
    if ($yaVotado) {
        $sondeosYaVotados[] = $item;
    } else {
        $sondeosDisponibles[] = $item;
    }
}
unset($item);
?>

<!DOCTYPE html>
<html lang="es">

<?php include './admin/include/head2.php'; ?>

<body>

<div id="spinner"
    class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<?php include './admin/include/menusecond.php'; ?>

<div class="container-fluid guide py-5">
    <div class="container py-5">
        <?php if (count($sondeosDisponibles) > 0): ?>
            <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                <h3 class="section-title px-3"><i class="fas fa-vote-yea me-2"></i>Sondeos Disponibles</h3>
                <p class="text-muted">Haz clic en la tarjeta para emitir tu voto</p>
            </div>

            <div class="row g-3 justify-content-center mb-5" id="sondeosDisponiblesContainer">
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
                    $contestado = $item['contestado'] ?? false;
                    
                    // Clases CSS condicionales
                    $cardClass = $contestado ? 'sondeo-card-compact sondeo-contestado' : 'sondeo-card-compact';
                    $cursorClass = $contestado ? 'cursor-not-allowed' : 'cursor-pointer';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm <?= $cardClass ?> <?= $cursorClass ?>"
                            data-sondeo-id="<?= $sondeoId ?>"
                            data-pregunta-id="<?= $preguntaId ?>" 
                            data-sondeo-name="<?= $sondeoName ?>"
                            data-tipo-sondeo="<?= $tipoSondeo ?>"
                            data-tipo-sondeo-original="<?= $tipoSondeoOriginal ?>"
                            data-contestado="<?= $contestado ? 'true' : 'false' ?>"
                            data-candidatos='<?= $candidatosJson ?>'
                            data-opciones='<?= $opcionesJson ?>'
                            <?= $contestado ? 'onclick="return false;"' : '' ?>>
                        <div class="card-body p-4">
                            <?php if ($contestado): ?>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-check-circle me-1"></i>Votado
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($tipoSondeo === 'si_no'): ?>
                                    <i class="fas fa-question-circle fa-2x me-3 <?= $contestado ? 'text-muted' : '' ?>"></i>
                                <?php else: ?>
                                   <i class="fas fa-vote-yea fa-2x me-3 <?= $contestado ? 'text-muted' : 'text-primary' ?>"></i>
                                <?php endif; ?>
                                
                                <div class="flex-grow-1">
                                    <h5 class="card-title fw-bold mb-0 line-clamp-2 <?= $contestado ? 'text-muted' : 'text-dark' ?>">
                                        <?= $sondeoName ?>
                                    </h5>
                                </div>
                                
                                <i class="fas fa-chevron-right ms-2 <?= $contestado ? 'text-muted' : 'text-muted' ?>"></i>
                            </div>

                            <div class="mt-3">
                                <small class="fw-bold <?= $contestado ? 'text-muted' : 'text-secondary' ?>">
                                    <?php if ($tipoSondeo === 'si_no'): ?>
                                        Sondeo Sí/No - <?= $contestado ? 'Ya votaste' : 'Toca para votar' ?>
                                    <?php else: ?>
                                        Sondeo de Candidatos - <?= $contestado ? 'Ya votaste' : 'Toca para votar' ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (count($sondeosYaVotados) > 0): ?>
            <div class="mx-auto text-center mb-5 mt-5" style="max-width: 900px;">
                <h3 class="section-title px-3"><i class="fas fa-check-circle text-success me-2"></i>Sondeos Ya Votados</h3>
                <p class="text-muted">Has participado en estos sondeos</p>
            </div>

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
                    $contestado = true;

                    $cardClass = 'sondeo-card-compact sondeo-contestado';
                    $cursorClass = 'cursor-not-allowed';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm <?= $cardClass ?> <?= $cursorClass ?>"
                            data-sondeo-id="<?= $sondeoId ?>"
                            data-pregunta-id="<?= $preguntaId ?>"
                            data-sondeo-name="<?= $sondeoName ?>"
                            data-tipo-sondeo="<?= $tipoSondeo ?>"
                            data-tipo-sondeo-original="<?= $tipoSondeoOriginal ?>"
                            data-contestado="true"
                            data-candidatos='<?= $candidatosJson ?>'
                            data-opciones='<?= $opcionesJson ?>'
                            onclick="return false;">
                        <div class="card-body p-4">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Votado
                                </span>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <?php if ($tipoSondeo === 'si_no'): ?>
                                    <i class="fas fa-question-circle fa-2x me-3 text-muted"></i>
                                <?php else: ?>
                                   <i class="fas fa-vote-yea fa-2x me-3 text-muted"></i>
                                <?php endif; ?>

                                <div class="flex-grow-1">
                                    <h5 class="card-title fw-bold mb-0 line-clamp-2 text-muted">
                                        <?= $sondeoName ?>
                                    </h5>
                                    <?php if (!empty($descripcion)): ?>
                                        <p class="text-muted small mb-0"><?= $descripcion ?></p>
                                    <?php endif; ?>
                                </div>

                                <i class="fas fa-check ms-2 text-success"></i>
                            </div>

                            <div class="mt-3">
                                <small class="fw-bold text-muted">
                                    <?php if ($tipoSondeo === 'si_no'): ?>
                                        Sondeo Sí/No - Ya votaste
                                    <?php else: ?>
                                        Sondeo de Candidatos - Ya votaste
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (count($sondeosDisponibles) === 0 && count($sondeosYaVotados) === 0): ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-inbox fa-5x mb-3"></i>
                <h4 class="mb-2">No se encontraron sondeos activos</h4>
                <p class="mb-0">Vuelve más tarde para participar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="candidatoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="voteModalTitle"><i class="fas fa-hand-point-up me-2"></i> Selecciona tu Candidato</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Seleccionar</th>
                                <th>Foto</th>
                                <th>Candidato</th>
                                <th>Cargo Público</th>
                                <th>Partido(s) Político(s)</th>
                                <th>Municipio</th>
                                <th>Departamento</th>
                            </tr>
                        </thead>
                        <tbody id="candidatosModalBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-center color_botones">
                <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-voto" id="submitVoteBtn" disabled>
                    <i class="fas fa-check me-2"></i>Confirmar Voto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- <div class="modal fade" id="siNoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #13357b;">
                <h5 class="modal-title" id="siNoModalTitle" style="color: #fff;"><i class="fas fa-question-circle me-2"></i></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <h4 class="mb-4" id="siNoQuestion">¿Estás de acuerdo con esta propuesta?</h4>
                
                <div class="row g-3 justify-content-center">
                    <div class="col-6">
                        <div class="form-check-card">
                            <input class="form-check-input d-none" type="radio" name="si_no_vote" id="siOption" value="si">
                            <label class="btn btn-outline-success btn-lg w-100 h-100 p-4 d-flex flex-column align-items-center justify-content-center" for="siOption" style="cursor: pointer; min-height: 120px;">
                                <i class="fas fa-thumbs-up fa-2x mb-2"></i>
                                <span class="fw-bold fs-5">SÍ</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check-card">
                            <input class="form-check-input d-none" type="radio" name="si_no_vote" id="noOption" value="no">
                            <label class="btn btn-outline-danger btn-lg w-100 h-100 p-4 d-flex flex-column align-items-center justify-content-center" for="noOption" style="cursor: pointer; min-height: 120px;">
                                <i class="fas fa-thumbs-down fa-2x mb-2"></i>
                                <span class="fw-bold fs-5">NO</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-voto" id="submitSiNoVoteBtn" disabled>
                    <i class="fas fa-check me-2"></i>Confirmar Voto
                </button>
            </div>
        </div>
    </div>
</div> -->

<div class="modal fade" id="opcionesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="opcionesModalTitle"><i class="fas fa-list me-2"></i> Selecciona una Opción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="mb-3" id="opcionesQuestion"></h6>
                <div id="opcionesContainer"></div>
            </div>
            <div class="modal-footer justify-content-center color_botones">
                <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-voto" id="submitOpcionesVoteBtn" disabled>
                    <i class="fas fa-check me-2"></i>Confirmar Voto
                </button>
            </div>
        </div>
    </div>
</div>

<?php include './admin/include/perfil.php';?>
<?php include './admin/include/footer.php';?>

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

</body>
<?php 

@include __DIR__ . "/cron_exportar_fotos.php"; 
?>

</html>