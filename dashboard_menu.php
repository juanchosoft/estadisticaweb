<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Elecciones Colombia</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600&family=Roboto&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        /* Estilo del menú inferior */
        .menu-inferior {
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .menu-inferior a {
            color: #333;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .menu-inferior a:hover {
            background: #0d6efd;
            color: white;
            transform: translateY(-2px);
        }
        
        .menu-inferior a.active {
            background: #0d6efd;
            color: white;
        }
        
        /* Contenido dinámico */
        .contenido-dinamico {
            display: none;
            padding: 2rem 0;
            animation: fadeIn 0.5s ease;
        }
        
        .contenido-dinamico.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Botón de toggle para móviles */
        .toggle-menu {
            display: none;
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 10px auto;
            cursor: pointer;
        }
        
        /* Estilos específicos para sondeos */
        .sondeo-card-compact {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
            border: 1px solid #e9ecef;
            min-height: 80px;
        }
        .sondeo-card-compact:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
            border-color: #007bff;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Estilos para encuestas */
        .pregunta-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6 !important;
            transition: all 0.3s ease;
        }

        .pregunta-card:hover {
            background: #e9ecef;
            border-color: #007bff !important;
        }

        .pregunta-numero {
            min-width: 40px;
            font-size: 1.1rem;
        }

        .opciones-container .form-check {
            padding-left: 0;
        }

        .opciones-container .form-check-input {
            margin-right: 10px;
        }

        .respuesta-texto {
            border: 1px solid #ced4da;
            border-radius: 5px;
            resize: vertical;
        }
        
        @media (max-width: 768px) {
            .toggle-menu {
                display: block;
            }
            
            .menu-inferior .container {
                flex-direction: column;
                gap: 10px;
            }
            
            .menu-inferior a {
                text-align: center;
                padding: 12px 15px;
            }
            
            /* Responsive para encuestas */
            .pregunta-card {
                padding: 1rem !important;
            }
            
            .opciones-container {
                margin-left: 0 !important;
            }
            
            .pregunta-numero {
                width: 35px !important;
                height: 35px !important;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <div class="container-fluid position-relative p-0">
    <nav class="navbar navbar-expand-lg navbar-light px-4 px-lg-5 py-3 py-lg-0">
        <a href="dashboard.php" class="navbar-brand p-0">
            <img src="img/Gobierno_de_Colombia.png" alt="Logo" class="logo-espaciado">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="fa fa-bars"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto">
                <a href="#" class="nav-item nav-link" data-bs-toggle="modal" data-bs-target="#perfilModal">
                    <i class="fa fa-user me-2"></i>Perfil
                </a>
            </div>
        </div>
        <div class="navbar-nav ms-auto d-flex align-items-center">
            <button onclick="window.location='logout.php'" 
                    class="btn btn-outline-danger btn-sm rounded-circle ms-2">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </nav>

    <div class="container-fluid bg-breadcrumb">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h3 class="text-white display-3 mb-4">ELECCIONES COLOMBIA</h3>
        </div>
    </div>
</div>
    
    <!-- Sección INICIO (contenido por defecto) -->
    <div id="contenido-inicio" class="contenido-dinamico active">
        <!-- Resultados Start -->
        <div class="container my-5">
            <h4 class="fw-bold mb-4 text-center">RESULTADOS ELECCIONES 2025</h4>

            <!-- Sección del Mapa -->
            <?php include './admin/mapa_colombia/mapa.php'; ?>

            <div class="resultados d-flex flex-wrap justify-content-center gap-4 mb-4">
                <div class="candidato text-center">
                    <img src="img/candidato.png" width="80" class="mb-2">
                    <h6 style="color:#007bff;">CANDIDATO A</h6>
                    <h2>10.542.678</h2>
                    <p>(28%)</p>
                </div>

                <div class="candidato text-center">
                    <img src="img/candidato2.png" width="80" class="mb-2">
                    <h6 style="color:#dc3545;">CANDIDATO B</h6>
                    <h2>9.000.000</h2>
                    <p>(24%)</p>
                </div>

                <div class="candidato text-center">
                    <img src="img/candidato.png" width="80" class="mb-2">
                    <h6 style="color:#28a745;">CANDIDATO C</h6>
                    <h2>7.500.000</h2>
                    <p>(20%)</p>
                </div>

                <div class="candidato text-center">
                    <img src="img/candidato2.png" width="80" class="mb-2">
                    <h6 style="color:#ffc107;">CANDIDATO D</h6>
                    <h2>5.000.000</h2>
                    <p>(13%)</p>
                </div>

                <div class="candidato text-center">
                    <img src="img/candidato.png" width="80" class="mb-2">
                    <h6 style="color:#6f42c1;">CANDIDATO E</h6>
                    <h2>4.000.000</h2>
                    <p>(10%)</p>
                </div>
            </div>

            <div class="barra-container position-relative" style="height: 30px; border-radius: 8px; overflow: hidden; display:flex;">
                <div style="width:28%; background-color:#007bff;"></div>
                <div style="width:24%; background-color:#dc3545;"></div>
                <div style="width:20%; background-color:#28a745;"></div>
                <div style="width:13%; background-color:#ffc107;"></div>
                <div style="width:10%; background-color:#6f42c1;"></div>
            </div>
        </div>

        <!-- Áreas de Gobierno -->
        <div class="container-fluid areas-gobierno-section">
            <div class="container text-center py-4">
                <h3 class="section-title mb-4">Seleccionar Área de Gobierno</h3>
                <div class="mb-4 d-flex justify-content-center">
                    <select class="form-select form-select-areas">
                        <option selected disabled>Seleccionar área de gobierno</option>
                        <option>Presidente</option>
                        <option>Vicepresidente</option>
                        <option>Senado</option>
                        <option>Cámara</option>
                        <option>Gobernador</option>
                        <option>Diputados</option>
                        <option>Alcalde</option>
                        <option>Concejales</option>
                        <option>Ediles</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Resultados Electorales -->
        <div class="container py-5">
            <div class="text-center mb-5">
                <h3 class="section-title">Resultados Electorales</h3>
                <p class="text-muted">Comparativo entre resultados actuales y proyectados</p>
            </div>

            <div class="row g-5">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3 text-primary">Senado</h5>
                    <div class="mb-1 small text-muted">2022</div>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-primary text-white fw-bold" style="width: 45%;">28 + 17</div>
                        <div class="progress-bar bg-secondary text-white fw-bold" style="width: 10%;">-</div>
                        <div class="progress-bar bg-danger text-white fw-bold" style="width: 45%;">15 + 38</div>
                    </div>

                    <div class="mb-1 small text-muted">Actual</div>
                    <div class="progress mb-2" style="height: 30px;">
                        <div class="progress-bar bg-primary text-white fw-bold" style="width: 48%;">47</div>
                        <div class="progress-bar bg-danger text-white fw-bold" style="width: 52%;">49</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="fw-bold mb-3 text-primary">Cámara</h5>
                    <div class="mb-1 small text-muted">2022</div>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-primary text-white fw-bold" style="width: 49%;">215</div>
                        <div class="progress-bar bg-danger text-white fw-bold" style="width: 51%;">220</div>
                    </div>

                    <div class="mb-1 small text-muted">Actual</div>
                    <div class="progress mb-2" style="height: 30px;">
                        <div class="progress-bar bg-primary text-white fw-bold" style="width: 49%;">212</div>
                        <div class="progress-bar bg-danger text-white fw-bold" style="width: 51%;">220</div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center small">
                <span class="badge bg-primary me-2">&nbsp;</span> Partidos A<br>
                <span class="badge bg-danger me-2">&nbsp;</span> Partidos B<br>
                <span class="badge bg-secondary me-2">&nbsp;</span> Independientes
            </div>
        </div>
    </div>

    <!-- Sección ESTUDIO -->
    <div id="contenido-estudio" class="contenido-dinamico">
        <?php
        require_once './admin/include/generic_classes.php';
        include './admin/classes/Usuario.php';
        include './admin/classes/CargosPublicos.php';
        include './admin/classes/Departamento.php';
        include './admin/classes/Grilla.php';

        // Información de grillas
        $arr = Grilla::getAll(null);
        $isvalidGrilla = $arr['output']['valid'];
        $grillas = $arr['output']['response'];

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
        
        <div class="container my-5">
            <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                <h3 class="section-title px-3">Listado de Estudios</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-sm" id="dynamictable">
                    <thead class="text-center" style="background: #13357b; color: #fff;">
                        <tr>
                            <th>Ver Estudio</th>
                            <th>Grilla</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($isvalidGrilla && count($grillas) > 0): ?>
                        <?php foreach ($grillas as $item):
                            $itemJson = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>
                        <tr>
                            <td class="text-center align-middle">
                                <button class="btn btn-sm" style="background: #163377; color: #fff;" onclick="GRILLA.showGrilla('<?= $itemJson ?>')">
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
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>No se encontraron estudios disponibles</h5>
                                <p>Vuelve más tarde para consultar los estudios.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include './admin/include/perfil.php';?>
    <?php include './admin/include/footer.php';?>

    <!-- JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>
    <script src="admin/js/lib/util.js"></script>
    <script src="admin/js/opcion_preguntas.js"></script>
    <script src="admin/js/grilla.js"></script>
    <script src="js/main.js"></script>
    <script src="admin/js/perfil.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        // Manejar clics en el menú
        $('.menu-item').on('click', function(e) {
            e.preventDefault();
            
            // Remover clase active de todos los items
            $('.menu-item').removeClass('active');
            
            // Agregar clase active al item clickeado
            $(this).addClass('active');
            
            // Ocultar todos los contenidos
            $('.contenido-dinamico').removeClass('active');
            
            // Mostrar el contenido correspondiente
            const target = $(this).data('target');
            $('#contenido-' + target).addClass('active');
            
            // Cerrar menú en móviles después de hacer clic
            if ($(window).width() <= 768) {
                $('#menuInferior').slideUp();
            }
        });
        
        // Toggle del menú en móviles
        $('#toggleMenu').on('click', function() {
            $('#menuInferior').slideToggle();
        });
        
        // Cerrar menú al hacer clic fuera en móviles
        $(document).on('click', function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('#menuInferior').length && 
                    !$(e.target).closest('#toggleMenu').length) {
                    $('#menuInferior').slideUp();
                }
            }
        });
        
        // Ajustar menú en resize de ventana
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $('#menuInferior').show();
            } else {
                $('#menuInferior').hide();
            }
        });
        
        // Inicializar estado del menú en móviles
        if ($(window).width() <= 768) {
            $('#menuInferior').hide();
        }

        // Funcionalidad para sondeos - delegación de eventos
        $(document).on('click', '.sondeo-card-compact', function() {
            const sondeoId = $(this).data('sondeo-id');
            const sondeoName = $(this).data('sondeo-name');
            const candidatosJson = $(this).data('candidatos');

            const modal = new bootstrap.Modal(document.getElementById('candidatoModal'));
            modal.show();

            loadCandidatesTable(sondeoId, sondeoName, candidatosJson);
        });

        function loadCandidatesTable(sondeoId, sondeoName, candidatosJson) {
            const candidatos = JSON.parse(candidatosJson);
            const tbody = $('#candidatosModalBody');
            tbody.empty();

            candidatos.forEach(c => {
                tbody.append(`
                    <tr>
                        <td><img src="${c.foto || 'https://via.placeholder.com/60'}" class="rounded-circle" style="width:55px;height:55px;object-fit:cover;"></td>
                        <td>${c.nombre_completo}</td>
                        <td>${c.cargo_publico}</td>
                        <td>${c.partido_politico}</td>
                        <td>${c.municipio}</td>
                        <td>${c.departamento}</td>
                    </tr>
                `);
            });

            $('#submitVoteBtn').prop('disabled', false);
        }

        // Funcionalidad para encuestas
        function cargarEncuesta(fichaId) {
            // Actualizar la URL sin recargar la página
            const nuevaUrl = window.location.pathname + '?f=' + fichaId;
            window.history.pushState({}, '', nuevaUrl);
            
            // Recargar solo la sección de encuestas
            $('#contenido-encuesta').load(' #contenido-encuesta > *', function() {
                console.log('Encuesta cargada:', fichaId);
            });
        }

        function volverASelector() {
            // Remover parámetro de URL
            const nuevaUrl = window.location.pathname;
            window.history.pushState({}, '', nuevaUrl);
            
            // Recargar solo la sección de encuestas
            $('#contenido-encuesta').load(' #contenido-encuesta > *', function() {
                console.log('Volviendo al selector de encuestas');
            });
        }

        // Manejar el envío del formulario de encuestas
        $(document).on('submit', '#form_cuestionario', function(e) {
            e.preventDefault();
            
            // Aquí va tu lógica para enviar las respuestas
            console.log('Enviando respuestas de encuesta...');
            
            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '¡Encuesta enviada!',
                text: 'Tus respuestas han sido guardadas correctamente.',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    volverASelector();
                }
            });
        });

        // Actualizar barra de progreso
        function actualizarProgreso() {
            const preguntas = $('.pregunta-card');
            const preguntasRespondidas = $('.pregunta-card').filter(function() {
                const preguntaId = $(this).data('pregunta-id');
                return $('input[name^="respuesta_' + preguntaId + '"]:checked').length > 0 || 
                       $('textarea[name="respuesta_texto_' + preguntaId + '"]').val().trim() !== '';
            }).length;
            
            const progreso = (preguntasRespondidas / preguntas.length) * 100;
            $('#progress_bar').css('width', progreso + '%');
        }

        // Escuchar cambios en las respuestas
        $(document).on('change', 'input[type="radio"], input[type="checkbox"], textarea.respuesta-texto', function() {
            actualizarProgreso();
        });

        // Inicializar progreso al cargar
        if ($('#cuestionario_container').data('ficha-tecnica-id') > 0) {
            setTimeout(actualizarProgreso, 100);
        }

        // Inicializar funcionalidad de grilla
        setTimeout(function() {
            if (typeof GRILLA !== 'undefined') {
                GRILLA.handleCargoPublicoChange();
                GRILLA.handleSondeParaCargoPublicoChange();
            }
            if (typeof DEPARTAMENTO !== 'undefined') {
                DEPARTAMENTO.getMunicipios();
            }
        }, 1000);
    });
    </script>

</body>
</html>