<?php
  // ==========================
  // Usuario (una sola vez)
  // ==========================
  $nombreCompleto = $_SESSION['session_user']['nombre_completo']
    ?? $_SESSION['session_user']['usuario']
    ?? 'Usuario';

  $p = preg_split('/\s+/', trim($nombreCompleto));
  $nombreCorto = trim(($p[0] ?? 'Usuario') . ' ' . ($p[1] ?? ''));
  $userId = (int)($_SESSION['session_user']['id'] ?? 0);
?>

<!-- ==========================
     NAVBAR ULTRA RESPONSIVE
========================== -->
<div class="container-fluid p-0">
  <nav class="navbar navbar-expand-lg fixed-top navbar-saas" id="mainNavbar" aria-label="Menú principal">
    <div class="container-fluid px-3 px-lg-4 d-flex align-items-center">

      <!-- BRAND (SOLO LOGO) -->
     
          <img   src="assets/img/admin/estadistica3.png"  alt="Ilustración de estadísticas"  class="img-estadistica"/>
   
   

      <!-- TOGGLER (BARRAS BLANCAS) -->
      <button class="navbar-toggler ms-auto" type="button"
              data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
              aria-controls="navbarCollapse" aria-expanded="false"
              aria-label="Abrir/Cerrar menú">
        <span class="hamburger" aria-hidden="true">
          <span></span>
          <span></span>
          <span></span>
        </span>
      </button>

      <!-- MENU -->
      <div class="collapse navbar-collapse" id="navbarCollapse">

        <!-- DESKTOP -->
        <div class="navbar-nav ms-auto d-none d-lg-flex align-items-center gap-2">

          <a href="resultado.php" class="nav-link">
            <i class="fas fa-chart-bar me-2"></i>
           Ver Resultados
          </a>

          <!-- USER -->
          <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">

              <span class="avatar"><i class="fas fa-user"></i></span>

              <div class="lh-sm text-start">
                <div class="fw-bold text-white" style="font-size:.92rem;">
                  <?= htmlspecialchars($nombreCorto) ?>
                </div>
                <div class="text-white-50" style="font-size:.76rem;">
                  Mi cuenta
                </div>
              </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-pro shadow-lg border-0">
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
                <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
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

          <a href="dash_responder.php" class="nav-link mobile-link">
            <i class="fas fa-clipboard-check"></i>
            <span>Formularios pendientes</span>
            <i class="fas fa-chevron-right ms-auto"></i>
          </a>

          <a href="resultado.php" class="nav-link mobile-link">
            <i class="fas fa-chart-bar"></i>
            <span>Consultar estadísticas</span>
            <i class="fas fa-chevron-right ms-auto"></i>
          </a>

          <hr class="my-2" style="opacity:.25">

          <a href="#" class="nav-link mobile-link"
             onclick="PERFIL.loadProfile(<?= $userId ?>); return false;">
            <i class="fas fa-user"></i>
            <span>Mi perfil</span>
            <i class="fas fa-chevron-right ms-auto"></i>
          </a>

          <a href="logout.php" class="nav-link mobile-link danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar sesión</span>
            <i class="fas fa-chevron-right ms-auto"></i>
          </a>

        </div>
      </div>
    </div>
  </nav>
</div>

<!-- ==========================
     ESTILOS (MÓVIL/TABLET PRO)
========================== -->
<style>
   .img-estadistica{
  width: 100%;
  max-width: 150px;   /* tamaño ideal en PC */
  height: auto;
  display: block;
  margin: 0 auto;     /* centra la imagen */
}

/* Tablet */
@media (max-width: 992px){
  .img-estadistica{
    max-width: 180px;
  }
}

/* Móvil */
@media (max-width: 576px){
  .img-estadistica{
    max-width: 140px;
  }
}
  :root{
    --menu-bg:#0E2A52;
    --menu-bg-2:#143B73;
    --shadow:0 14px 40px rgba(0,0,0,.35);
  }

  /* NAVBAR */
  .navbar-saas{
    background: linear-gradient(135deg, var(--menu-bg), var(--menu-bg-2));
    padding: .75rem 0;                 /* más alto en móvil/tablet */
    border-bottom: 1px solid rgba(255,255,255,.15);
    z-index: 1050;
  }
  .navbar-saas.scrolled{ box-shadow: var(--shadow); }

  /* BRAND (SOLO LOGO) */
  .brand-only{ padding: 0; }
  .brand-logo{
    width:56px; height:56px;           /* grande en móvil */
    display:grid; place-items:center;
    border-radius:16px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.20);
    overflow:hidden;
  }
  .brand-logo img{
    max-height:36px;
    width:auto;
    display:block;
  }

  /* TABLET: logo ligeramente más grande */
  @media (min-width: 576px) and (max-width: 991px){
    .navbar-saas{ padding: .85rem 0; }
    .brand-logo{ width:60px; height:60px; }
    .brand-logo img{ max-height:40px; }
  }

  /* PC: logo un poco más compacto */
  @media (min-width: 992px){
    .navbar-saas{ padding: .55rem 0; }
    .brand-logo{ width:50px; height:50px; border-radius:16px; }
    .brand-logo img{ max-height:34px; }
  }

  /* TOGGLER (BARRAS BLANCAS) */
  .navbar-toggler{
    border: 1px solid rgba(255,255,255,.22) !important;
    border-radius: 14px !important;
    padding: .55rem .65rem !important;
    background: rgba(255,255,255,.10);
  }
  .navbar-toggler:focus{
    box-shadow: 0 0 0 .2rem rgba(255,255,255,.18) !important;
  }

  .hamburger{
    width:28px;
    height:22px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
  }
  .hamburger span{
    height:3px;
    border-radius:4px;
    background:#fff;                   /* ✅ barras blancas */
    display:block;
  }

  /* LINKS */
  .nav-link{
    color:#fff !important;
    font-weight:800;
    border-radius:14px;
    padding:.75rem 1rem;               /* más táctil */
  }
  .nav-link:hover{
    background: rgba(255,255,255,.12);
  }

  /* CHIP */
  .nav-chip{
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.20);
  }

  /* AVATAR */
  .avatar{
    width:42px;height:42px;
    display:grid; place-items:center;
    border-radius:16px;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.25);
    color:#fff;
  }
  .avatar-lg{ width:52px;height:52px;border-radius:18px; }

  /* DROPDOWN */
  .dropdown-pro{
    border-radius:18px;
    min-width:300px;
    overflow:hidden;
  }

  /* MOBILE/TABLET PANEL */
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
    display:flex !important;
    align-items:center;
    gap:14px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.18);
    padding:14px;
    margin-bottom:12px;
    color:#fff !important;
  }
  .mobile-link i:first-child{ width:20px; text-align:center; }
  .mobile-link.danger{
    background: rgba(239,68,68,.25);
    border-color: rgba(239,68,68,.35);
  }
</style>

<!-- ==========================
     JS (OFFSET + CIERRE MÓVIL)
========================== -->
<script>
  document.addEventListener("scroll", () => {
    const nav = document.getElementById("mainNavbar");
    if (nav) nav.classList.toggle("scrolled", window.scrollY > 10);
  });

  function ajustarNavbar(){
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    document.body.style.paddingTop = nav.offsetHeight + "px";
  }

  document.addEventListener("DOMContentLoaded", () => {
    ajustarNavbar();

    // ✅ Cierra menú al tocar un link en móvil/tablet
    const collapse = document.getElementById("navbarCollapse");
    if (collapse) {
      collapse.addEventListener("click", (e) => {
        const link = e.target.closest("a.nav-link, a.dropdown-item");
        if (!link) return;

        if (window.innerWidth < 992) {
          const bs = bootstrap.Collapse.getInstance(collapse)
            || new bootstrap.Collapse(collapse, { toggle: false });
          bs.hide();
        }
      });
    }
  });

  window.addEventListener("resize", ajustarNavbar);
</script>
