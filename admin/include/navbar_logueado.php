<?php
  // ✅ Define el nombre una sola vez (evita duplicados)
  $nombreCompleto = $_SESSION['session_user']['nombre_completo']
    ?? $_SESSION['session_user']['usuario']
    ?? 'Usuario';

  $partesNombre = preg_split('/\s+/', trim($nombreCompleto));
  $nombreCorto  = trim(($partesNombre[0] ?? 'Usuario') . ' ' . ($partesNombre[1] ?? ''));
  $userId = (int)($_SESSION['session_user']['id'] ?? 0);
?>

<div class="container-fluid p-0">
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-pro" id="mainNavbar" aria-label="Barra de navegación principal">
    <div class="container-fluid px-3 px-lg-4">

      <!-- BRAND -->
     
       
        <img   src="assets/img/admin/estadistica3.png"  alt="Ilustración de estadísticas"  class="img-estadistica"/>

     
   

      <!-- TOGGLER (Mobile) -->
      <button class="navbar-toggler shadow-sm" type="button"
              data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
              aria-controls="navbarCollapse" aria-expanded="false" aria-label="Abrir/Cerrar menú">
        <span class="toggler-icon" aria-hidden="true">
          <span></span><span></span><span></span>
        </span>
      </button>

      <!-- CONTENT -->
      <div class="collapse navbar-collapse" id="navbarCollapse">

        <!-- LEFT (opcional) -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-none d-lg-flex">
          <!-- Puedes activar links aquí si quieres -->
        </ul>

        <!-- RIGHT DESKTOP -->
        <div class="navbar-nav ms-auto d-none d-lg-flex align-items-center gap-2">

        <a href="dash_responder.php" class="nav-link nav-chip d-flex align-items-center">
          <i class="fas fa-clipboard-check me-2"></i>
            Tienes encuestas por responder
        </a>


        <a href="visualizar.php" class="nav-link d-flex align-items-center">
          <i class="fas fa-chart-bar me-2"></i>
            Resultados
        </a>


          <!-- Profile dropdown -->
          <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">

              <span class="avatar">
                <i class="fas fa-user"></i>
              </span>

              <div class="text-start lh-sm">
                <div class="fw-bold text-white" style="font-size:.92rem;">
                  <?php echo htmlspecialchars($nombreCorto); ?>
                </div>
                <div class="text-white-50" style="font-size:.78rem;">Mi cuenta</div>
              </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-pro shadow-lg border-0">
              <li class="dropdown-head px-3 pt-3 pb-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="avatar avatar-lg">
                    <i class="fas fa-user"></i>
                  </span>
                  <div class="lh-sm">
                    <div class="fw-bold" style="color:#0f172a;">
                      <?php echo htmlspecialchars($nombreCompleto); ?>
                    </div>
                    <div class="text-muted" style="font-size:.85rem;">
                      Acceso autorizado
                    </div>
                  </div>
                </div>
              </li>

              <li><hr class="dropdown-divider my-2"></li>

              <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="#"
                   onclick="PERFIL.loadProfile(<?php echo $userId; ?>); return false;">
                  <i class="fas fa-user"></i> Mi perfil
                </a>
              </li>

              <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="dash_responder.php">
                  <i class="fas fa-clipboard-check"></i> Pendientes
                </a>
              </li>

              <li>
                <a class="dropdown-item d-flex align-items-center gap-2"
                   href="visualizar.php">
                  <i class="fas fa-chart-bar"></i> Visualización
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

        <!-- MOBILE MENU -->
        <div class="navbar-nav ms-auto d-lg-none w-100 mt-3 mobile-panel">
          <div class="mobile-user">
            <span class="avatar avatar-lg">
              <i class="fas fa-user"></i>
            </span>
            <div class="flex-grow-1">
              <div class="fw-bold text-white" style="font-size:1rem;">
                <?php echo htmlspecialchars($nombreCorto); ?>
              </div>
              <div class="text-white-50" style="font-size:.85rem;">Menú de usuario</div>
            </div>
          </div>

          <a href="dash_responder.php" class="nav-link mobile-link">
            <i class="fas fa-clipboard-check"></i>
            <span>Pendientes</span>
            <i class="fas fa-chevron-right ms-auto"></i>
          </a>

          <a href="visualizar.php" class="nav-link mobile-link">
            <i class="fas fa-chart-bar"></i>
            <span>Visualización</span>
            <i class="fas fa-chevron-right ms-auto"></i>
          </a>

          <a href="#" class="nav-link mobile-link"
             onclick="PERFIL.loadProfile(<?php echo $userId; ?>); return false;">
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
    --brand:#13357b;
    --brand2:#0b1a89;

    --ink:#0f172a;
    --muted:#64748b;

    --line:rgba(255,255,255,.18);
    --shadow:0 16px 40px rgba(2,6,23,.14);

    --r-xl:18px;
    --r-lg:14px;
  }

  /* ===========================
     NAVBAR (SaaS / Glass)
  =========================== */
  .navbar-pro{
    background:
      radial-gradient(900px 260px at 0% 0%, rgba(255,255,255,.14), transparent 55%),
      linear-gradient(135deg, var(--brand), var(--brand2));
    border-bottom: 1px solid rgba(255,255,255,.10);
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    padding-top: .55rem;
    padding-bottom: .55rem;
    z-index: 1050;
  }
  .navbar-pro.scrolled{
    box-shadow: var(--shadow);
    backdrop-filter: blur(10px);
  }

  /* BRAND */
  .brand-logo{
    width:44px; height:44px;
    display:grid; place-items:center;
    border-radius: 14px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.16);
    overflow:hidden;
  }
  .brand-logo img{ height:34px; width:auto; display:block; }

  .brand-text{
    font-weight: 900;
    letter-spacing:.2px;
    color:#fff;
    line-height: 1;
  }
  .brand-sub{
    font-weight: 700;
    color: rgba(255,255,255,.70);
    font-size: .78rem;
    margin-top: 2px;
  }

  /* TOGGLER (hamburger -> X) */
  .navbar-toggler{
    border: 1px solid rgba(255,255,255,.22) !important;
    border-radius: 14px !important;
    padding: .55rem .6rem !important;
    background: rgba(255,255,255,.10);
  }
  .navbar-toggler:focus{ box-shadow: 0 0 0 .2rem rgba(255,255,255,.18) !important; }

  .toggler-icon{
    width: 26px; height: 18px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
  }
  .toggler-icon span{
    height: 2px;
    border-radius: 99px;
    background: rgba(255,255,255,.95);
    display:block;
    transition: transform .2s ease, opacity .2s ease;
    transform-origin: center;
  }

  /* ✅ cuando está abierto el collapse, el botón queda "activo" (X) */
  .navbar-toggler[aria-expanded="true"] .toggler-icon span:nth-child(1){
    transform: translateY(8px) rotate(45deg);
  }
  .navbar-toggler[aria-expanded="true"] .toggler-icon span:nth-child(2){
    opacity: 0;
  }
  .navbar-toggler[aria-expanded="true"] .toggler-icon span:nth-child(3){
    transform: translateY(-8px) rotate(-45deg);
  }

  /* LINKS */
  #mainNavbar .nav-link{
    color: rgba(255,255,255,.92) !important;
    font-weight: 800;
    border-radius: 14px;
    padding: .55rem .8rem !important;
    transition: all .15s ease;
  }
  #mainNavbar .nav-link:hover{
    background: rgba(255,255,255,.12);
    color:#fff !important;
  }

  .nav-chip{
    border: 1px solid rgba(255,255,255,.20);
    background: rgba(255,255,255,.08);
  }

  /* AVATAR */
  .avatar{
    width: 42px; height: 42px;
    display:grid; place-items:center;
    border-radius: 16px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
    color:#fff;
    flex: 0 0 auto;
  }
  .avatar-lg{ width:50px; height:50px; border-radius: 18px; }

  /* DROPDOWN */
  .dropdown-pro{
    border-radius: 18px;
    min-width: 300px;
    overflow:hidden;
  }
  .dropdown-pro .dropdown-item{
    padding: .72rem 1rem;
    font-weight: 800;
    color: #0f172a;
  }
  .dropdown-pro .dropdown-item i{
    width: 22px;
    text-align:center;
    color:#334155;
  }
  .dropdown-pro .dropdown-item:hover{
    background: #f1f5ff;
  }
  .dropdown-pro .dropdown-item.text-danger i{
    color: #ef4444;
  }

  /* MOBILE PANEL */
  .mobile-panel{
    padding: 10px;
    border-radius: 18px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.16);
  }
  .mobile-user{
    display:flex; align-items:center; gap:12px;
    padding: 12px;
    border-radius: 16px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.16);
    margin-bottom: 10px;
  }
  .mobile-link{
    display:flex !important; align-items:center; gap:12px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    margin-bottom: 10px;
    padding: 12px 12px !important;
  }
  .mobile-link i:first-child{ width: 20px; text-align:center; }
  .mobile-link.danger{
    background: rgba(239,68,68,.14);
    border-color: rgba(239,68,68,.22);
    color:#fff !important;
  }

  /* Mejor tacto en móvil */
  @media (max-width: 991px){
    #mainNavbar .nav-link{ padding: .75rem .85rem !important; }
  }
</style>

<script>
  // ✅ efecto scrolled + padding top exacto
  function syncNavbarOffset(){
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    document.body.style.paddingTop = nav.offsetHeight + "px";
  }

  document.addEventListener("scroll", () => {
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    nav.classList.toggle("scrolled", window.scrollY > 8);
  });

  document.addEventListener("DOMContentLoaded", () => {
    syncNavbarOffset();

    // ✅ Cerrar collapse al hacer click en links (móvil)
    const collapseEl = document.getElementById('navbarCollapse');
    if (collapseEl) {
      collapseEl.addEventListener('click', (e) => {
        const a = e.target.closest('a.nav-link');
        if (!a) return;
        const bsCollapse = bootstrap.Collapse.getInstance(collapseEl) || new bootstrap.Collapse(collapseEl, { toggle: false });
        if (window.innerWidth < 992) bsCollapse.hide();
      });
    }
  });

  window.addEventListener("resize", syncNavbarOffset);
</script>
