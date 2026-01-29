<?php
  $nombreCompleto = $_SESSION['session_user']['nombre_completo']
    ?? $_SESSION['session_user']['usuario']
    ?? 'Usuario';

  $p = preg_split('/\s+/', trim($nombreCompleto));
  $nombreCorto = trim(($p[0] ?? 'Usuario') . ' ' . ($p[1] ?? ''));
  $userId = (int)($_SESSION['session_user']['id'] ?? 0);

  // Verificar si el usuario puede ver los registros (si ya ha votado)
  $puedeVerRegistros = false;
  if ($userId > 0) {
    require_once __DIR__ . '/../classes/DbConection.php';
    require_once __DIR__ . '/../classes/Util.php';
    require_once __DIR__ . '/../classes/Sondeo.php';
    require_once __DIR__ . '/../classes/RespuestaCuestionario.php';
    
$config = Util::getInformacionConfiguracion();
  $opcionActivaWeb = $config[0]['opcion_activa_web'] ?? 'sondeo';
  
  error_log("DEBUG menusecond: Config obtenida: " . json_encode($config));
  error_log("DEBUG menusecond: Opción activa web: $opcionActivaWeb");
    
    $dbConnection = new DbConection();
    
    error_log("DEBUG menusecond: Opción activa web: $opcionActivaWeb, Usuario ID: $userId");
    
    if ($opcionActivaWeb === 'sondeo') {
      $sondeo = new Sondeo($dbConnection);
      $puedeVerRegistros = $sondeo->verificarSiUsuarioVoto($userId);
    } elseif ($opcionActivaWeb === 'cuestionario') {
      $cuestionario = new RespuestaCuestionario($dbConnection);
      $puedeVerRegistros = $cuestionario->verificarSiUsuarioRespondio($userId);
    } else {
      // Si no hay opción activa definida, revisamos si el usuario ha respondido cuestionarios
      error_log("DEBUG menusecond: Opción activa no reconocida, verificando si usuario respondió cuestionarios");
      $cuestionario = new RespuestaCuestionario($dbConnection);
      $puedeVerRegistros = $cuestionario->verificarSiUsuarioRespondio($userId);
      
      // Si puede ver registros por cuestionarios, actualizamos la opción activa
      if ($puedeVerRegistros) {
        $opcionActivaWeb = 'cuestionario';
        error_log("DEBUG menusecond: Usuario ha respondido cuestionarios, actualizando opción activa a cuestionario");
      }
    }
    
    error_log("DEBUG menusecond: ¿Puede ver registros?: " . ($puedeVerRegistros ? 'SÍ' : 'NO'));
  }
?>

<style>
  :root{
    --menu-solid:#0E2A52;
    --menu-solid-2:#143B73;
    --menu-shadow: 0 10px 26px rgba(2,6,23,.22);
  }

  /* NAVBAR sólido y compacto */
  #mainNavbar.navbar-saas{
    background: linear-gradient(135deg, var(--menu-solid), var(--menu-solid-2)) !important;
    opacity: 1 !important;
    border-bottom: 1px solid rgba(255,255,255,.12) !important;
    box-shadow: var(--menu-shadow) !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
    padding: .42rem 0 !important;
    z-index: 1050;
  }
  @media (max-width: 991.98px){
    #mainNavbar.navbar-saas{ padding: .48rem 0 !important; }
  }

  /* LOGO */
  .img-estadistica{
    width:auto !important;
    height:38px !important;
    max-height:38px !important;
    display:block;
    margin:0;
  }
  @media (max-width: 992px){
    .img-estadistica{ height:42px !important; max-height:42px !important; }
  }
  @media (max-width: 576px){
    .img-estadistica{ height:36px !important; max-height:36px !important; }
  }

  /* TOGGLER */
  #mainNavbar .navbar-toggler{
    border: 1px solid rgba(255,255,255,.22) !important;
    border-radius: 12px !important;
    padding: .42rem .55rem !important;
    background: rgba(255,255,255,.10) !important;
  }
  #mainNavbar .navbar-toggler:focus{
    box-shadow: 0 0 0 .2rem rgba(255,255,255,.18) !important;
  }
  .hamburger{
    width:28px;height:22px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
  }
  .hamburger span{
    height:3px;border-radius:4px;background:#fff;display:block;
  }

  /* LINKS */
  #mainNavbar .nav-link{
    color:#fff !important;
    font-weight:800;
    border-radius:12px;
    padding:.55rem .85rem !important;
  }
  #mainNavbar .nav-link:hover{
    background: rgba(255,255,255,.12);
  }

  /* CTA registros */
  .btn-registros{
    display:inline-flex;
    align-items:center;
    gap:10px;
    background: linear-gradient(135deg,#2563eb,#1e40af) !important;
    color:#fff !important;
    font-weight:950;
    padding:.62rem 1.05rem;
    border-radius:14px;
    box-shadow:0 12px 40px rgba(37,99,235,.45);
    border:1px solid rgba(255,255,255,.25);
    transition: transform .14s ease, box-shadow .14s ease, filter .14s ease;
    text-decoration:none !important;
    white-space:nowrap;
  }
  .btn-registros:hover{
    transform:translateY(-2px);
    box-shadow:0 18px 60px rgba(37,99,235,.65);
    filter:saturate(1.08);
  }

  /* AVATAR */
  .avatar{
    width:42px;height:42px;
    display:grid;place-items:center;
    border-radius:16px;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.25);
    color:#fff;
  }
  .avatar-lg{ width:52px;height:52px;border-radius:18px; }

  /* DROPDOWN (Asegura que se vea encima) */
  .dropdown-menu{
    z-index: 2000 !important;
  }
  .dropdown-pro{
    border-radius:18px;
    min-width:300px;
    overflow:hidden;
    box-shadow: 0 18px 55px rgba(2,6,23,.20);
    border: 1px solid rgba(15,23,42,.08);
  }

  /* MOBILE PANEL */
  .mobile-panel{
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.20);
    border-radius:20px;
    padding:14px;
  }
  .mobile-user{
    display:flex;
    align-items:center;
    gap:14px;
    padding:14px;
    border-radius:18px;
    background: rgba(255,255,255,.16);
    border: 1px solid rgba(255,255,255,.20);
    margin-bottom:12px;
  }

  .mobile-link{
    display:flex;
    align-items:center;
    gap:14px;
    padding:14px;
    border-radius:16px;
    margin-bottom:12px;
    font-weight:900;
    color:#fff !important;
    text-decoration:none !important;
    border: 1px solid rgba(255,255,255,.18);
  }
  .mobile-link i:first-child{ width:20px; text-align:center; }

  .mobile-link.primary{
    background:linear-gradient(135deg,#2563eb,#1e40af);
    box-shadow:0 14px 40px rgba(37,99,235,.30);
  }
  .mobile-link.danger{
    background:linear-gradient(135deg,#dc2626,#7f1d1d);
    box-shadow:0 14px 40px rgba(220,38,38,.22);
  }
</style>

<div class="container-fluid p-0">
  <nav class="navbar navbar-expand-lg fixed-top navbar-saas" id="mainNavbar" aria-label="Menú principal">
    <div class="container-fluid px-3 px-lg-4 d-flex align-items-center">

      <!-- LOGO -->
      <img src="assets/img/admin/estadistica3.png" alt="Estadísticas 360" class="img-estadistica"/>

      <!-- TOGGLER -->
      <button class="navbar-toggler ms-auto" type="button"
              data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
              aria-controls="navbarCollapse" aria-expanded="false"
              aria-label="Abrir/Cerrar menú">
        <span class="hamburger" aria-hidden="true">
          <span></span><span></span><span></span>
        </span>
      </button>

      <div class="collapse navbar-collapse" id="navbarCollapse">

        <!-- DESKTOP -->
        <div class="navbar-nav ms-auto d-none d-lg-flex align-items-center gap-2">

          <?php if ($puedeVerRegistros): ?>
          <a href="resultado.php" class="btn-registros">
            <i class="fas fa-chart-bar"></i>
            Ver Resultados
          </a>
          <?php endif; ?>

          <!-- USER DROPDOWN -->
          <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
               href="#"
               id="userDropdown"
               role="button"
               data-bs-toggle="dropdown"
               data-bs-auto-close="outside"
               aria-expanded="false"
               onclick="return false;">
              <span class="avatar"><i class="fas fa-user"></i></span>
              <div class="lh-sm text-start">
                <div class="fw-bold text-white" style="font-size:.92rem;"><?= htmlspecialchars($nombreCorto) ?></div>
                <div class="text-white-50" style="font-size:.75rem;">Mi cuenta</div>
              </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-pro" aria-labelledby="userDropdown">
              <li class="px-3 pt-3 pb-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="avatar avatar-lg"><i class="fas fa-user"></i></span>
                  <div class="lh-sm">
                    <div class="fw-bold"><?= htmlspecialchars($nombreCompleto) ?></div>
                    <div class="text-muted" style="font-size:.85rem;">Acceso autorizado</div>
                  </div>
                </div>
              </li>

              <li><hr class="dropdown-divider my-2"></li>

              <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="#"
                   onclick="PERFIL.loadProfile(<?= $userId ?>); return false;">
                  <i class="fas fa-user"></i> Mi perfil
                </a>
              </li>

              <li><hr class="dropdown-divider my-2"></li>

              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 text-danger fw-bold"
                   href="logout.php">
                  <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- MOBILE / TABLET -->
        <div class="navbar-nav d-lg-none w-100 mt-3 mobile-panel">

          <div class="mobile-user">
            <span class="avatar avatar-lg"><i class="fas fa-user"></i></span>
            <div class="flex-grow-1">
              <div class="fw-bold text-white"><?= htmlspecialchars($nombreCorto) ?></div>
              <div class="text-white-50" style="font-size:.85rem;">Menú de usuario</div>
            </div>
          </div>

          <?php if ($puedeVerRegistros): ?>
          <a href="resultado.php" class="mobile-link primary">
            <i class="fas fa-chart-bar"></i>
            <span>Ver Resultados</span>
            <i class="fas fa-chevron-right ms-auto"></i>
          </a>
          <?php endif; ?>

          <a href="logout.php" class="mobile-link danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Salir</span>
            <i class="fas fa-chevron-right ms-auto"></i>
          </a>
        </div>

      </div>
    </div>
  </nav>
</div>

<script>
  // Si Bootstrap JS no está cargado, el dropdown NO abre.
  document.addEventListener("DOMContentLoaded", () => {
    if (typeof bootstrap === "undefined") {
      console.warn("⚠️ Bootstrap JS no está cargado. El dropdown de 'Mi cuenta' no funcionará.");
    }
  });

  // Sombra al scroll
  document.addEventListener("scroll", () => {
    const nav = document.getElementById("mainNavbar");
    if (nav) nav.classList.toggle("scrolled", window.scrollY > 10);
  });

  // Ajusta padding-top body
  function ajustarNavbar(){
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    document.body.style.paddingTop = nav.offsetHeight + "px";
  }

  document.addEventListener("DOMContentLoaded", () => {
    ajustarNavbar();

    // Cierra menú al tocar link en móvil/tablet (pero NO rompe dropdown desktop)
    const collapseEl = document.getElementById("navbarCollapse");
    if (collapseEl) {
      collapseEl.addEventListener("click", (e) => {
        const link = e.target.closest("a.nav-link, a.dropdown-item, a.mobile-link");
        if (!link) return;

        if (window.innerWidth < 992) {
          const bs = bootstrap.Collapse.getInstance(collapseEl)
            || new bootstrap.Collapse(collapseEl, { toggle: false });
          bs.hide();
        }
      });
    }
  });

  window.addEventListener("resize", ajustarNavbar);
</script>
