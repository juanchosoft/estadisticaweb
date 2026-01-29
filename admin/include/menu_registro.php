<style>
  :root{
    --nav-blue:#0E2A52;      /* ✅ mismo color sólido */
    --nav-blue-2:#143B73;    /* ✅ degradado sutil */
    --white:#ffffff;

    --shadow: 0 10px 26px rgba(2,6,23,.22);
  }

  /* ===== NAVBAR SÓLIDO, COMPACTO, SIN TRANSPARENCIA ===== */
  #mainNavbar{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    opacity: 1 !important;
    border-bottom: 1px solid rgba(255,255,255,.12) !important;
    box-shadow: var(--shadow) !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
    transition: box-shadow .2s ease, background .2s ease !important;

    padding: .42rem 0 !important;      /* ✅ mismo tamaño compacto */
    z-index: 1050;
  }

  #mainNavbar.scrolled{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    box-shadow: 0 14px 40px rgba(2,6,23,.30) !important;
  }

  /* ===== LOGO (SIN CAPSULA BLANCA, MISMO ESTILO COMPACTO) ===== */
  .navbar-brand{
    padding: 0 !important;
    margin-left: .85rem !important;
    display:flex;
    align-items:center;
  }

  .brand-pill{
    display:flex;
    align-items:center;
    gap:.6rem;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
  }

  /* Tamaño del logo controlado */
  .brand-pill .logo-chip{
    height: 38px !important;
    width: auto !important;
    display:block !important;

    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    filter: none !important;
  }

  /* Tablet */
  @media (max-width: 991px){
    #mainNavbar{ padding: .48rem 0 !important; }
    .brand-pill .logo-chip{ height: 42px !important; }
  }

  /* Celular */
  @media (max-width: 576px){
    #mainNavbar{ padding: .45rem 0 !important; }
    .brand-pill .logo-chip{ height: 36px !important; }
  }

  /* ===== LINKS ===== */
  #mainNavbar .nav-link{
    color: rgba(255,255,255,.92) !important;
    font-weight: 800;
    padding: .55rem .85rem !important;  /* ✅ compacto */
    border-radius: 12px;
    transition: background .2s ease, color .2s ease;
  }
  #mainNavbar .nav-link:hover,
  #mainNavbar .nav-link:focus{
    background: rgba(255,255,255,.12);
    color: #fff !important;
    outline: none;
  }

  /* ===== TOGGLER (BARRAS BLANCAS, COMPACTO) ===== */
  #mainNavbar .navbar-toggler{
    border: 1px solid rgba(255,255,255,.22) !important;
    border-radius: 12px !important;
    padding: .42rem .55rem !important;
    background: rgba(255,255,255,.10) !important;
    margin-right: .85rem !important;
  }
  #mainNavbar .navbar-toggler:focus{
    box-shadow: 0 0 0 .2rem rgba(255,255,255,.18) !important;
  }

  /* icono del toggler en blanco */
  #mainNavbar .navbar-toggler .fa{
    color:#fff !important;
    font-size: 18px !important;
    line-height: 1;
  }
</style>

<div class="container-fluid p-0">
  <nav class="navbar navbar-expand-lg fixed-top d-flex align-items-center" id="mainNavbar">

    <!-- LOGO -->
    <a href="index.php" class="navbar-brand" aria-label="Ir al inicio">
      <div class="brand-pill">
        <img src="assets/img/admin/estadistica3.png" alt="Logo" class="logo-chip">
      </div>
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler ms-auto" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false"
            aria-label="Abrir/Cerrar menú">
      <span class="fa fa-bars" aria-hidden="true"></span>
    </button>

    <!-- Menú -->
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <div class="navbar-nav ms-auto py-2 py-lg-0">
        <a href="index.php" class="nav-item nav-link">Inicio</a>
        <a href="nosotros.php" class="nav-item nav-link">Quienes somos</a>
        <a href="servicios.php" class="nav-item nav-link">Servicios</a>
        <a href="contacto.php" class="nav-item nav-link">Contacto</a>
      </div>
    </div>

  </nav>
</div>

<script>
  // Sombra al hacer scroll (sin cambiar color ni transparencia)
  document.addEventListener("scroll", function() {
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    nav.classList.toggle("scrolled", window.scrollY > 10);
  });

  // Padding superior para que NO tape contenido (mejor que marginTop)
  function aplicarOffsetNavbar() {
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    document.body.style.paddingTop = nav.offsetHeight + "px";
  }
  document.addEventListener("DOMContentLoaded", aplicarOffsetNavbar);
  window.addEventListener("resize", aplicarOffsetNavbar);

  // En móvil: al dar click en un link colapsa el menú
  document.addEventListener("DOMContentLoaded", () => {
    const navbarCollapse = document.getElementById("navbarCollapse");
    if (!navbarCollapse || typeof bootstrap === "undefined") return;

    navbarCollapse.querySelectorAll("a.nav-link").forEach(a => {
      a.addEventListener("click", () => {
        if (window.innerWidth < 992) {
          const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse)
            || new bootstrap.Collapse(navbarCollapse, { toggle: false });
          bsCollapse.hide();
        }
      });
    });
  });
</script>
