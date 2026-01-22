<style>
  :root{
    --nav-blue: #20427F;     /* RGB 32,66,127 */
    --nav-blue-2:#132b52;    /* oscuro */
    --nav-blue-3:#2e58a8;    /* brillo */

    --white: #ffffff;
    --ink: #0f172a;
    --muted: #64748b;

    --radius-xl: 22px;
    --radius-lg: 16px;

    --shadow-soft: 0 12px 30px rgba(2, 6, 23, .16);
    --shadow-mid:  0 18px 40px rgba(2, 6, 23, .22);
  }

  /* ===== NAVBAR PRO (AZUL) ===== */
  #mainNavbar{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    border-bottom: 1px solid rgba(255,255,255,.10);
    transition: all .25s ease;
  }
  #mainNavbar.scrolled{
    box-shadow: var(--shadow-soft);
    background: linear-gradient(135deg, #1e3d77, #132b52) !important;
  }

  /* Logo capsula */
  .brand-pill{
    display:flex;
    align-items:center;
    gap:.65rem;
    padding: .45rem .75rem;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.20);
    transition: transform .2s ease, background .2s ease;
  }
  .brand-pill:hover{
    transform: translateY(-1px);
    background: rgba(255,255,255,.18);
  }
  .brand-pill .logo-chip{
    height: 38px;
    width: auto;
    border-radius: 999px;
    background: #fff;
    padding: 6px;
    box-shadow: 0 10px 24px rgba(2,6,23,.18);
  }
  .brand-pill .brand-text{
    color: rgba(255,255,255,.95);
    font-weight: 900;
    letter-spacing: .2px;
    line-height: 1.05;
    font-size: .95rem;
    white-space: nowrap;
  }
  .brand-pill .brand-sub{
    display:block;
    font-weight: 800;
    opacity: .85;
    font-size: .75rem;
    letter-spacing: .2px;
  }

  /* Links */
  #mainNavbar .nav-link{
    color: rgba(255,255,255,.92) !important;
    font-weight: 800;
    padding: .85rem 1rem;
    border-radius: 14px;
    transition: all .2s ease;
  }
  #mainNavbar .nav-link:hover,
  #mainNavbar .nav-link:focus{
    background: rgba(255,255,255,.12);
    color: #fff !important;
    outline: none;
  }
  #mainNavbar .nav-link.active{
    background: rgba(255,255,255,.16);
    color: #fff !important;
  }

  /* Toggler */
  #mainNavbar .navbar-toggler{
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 14px;
    width: 46px;
    height: 46px;
    display:flex;
    align-items:center;
    justify-content:center;
    color: #fff;
    background: rgba(255,255,255,.08);
  }
  #mainNavbar .navbar-toggler:focus{
    box-shadow: 0 0 0 .25rem rgba(255,255,255,.18);
  }

  /* Botones */
  .btn-vota{
    position: relative;
    border: 0 !important;
    color: #fff !important;
    font-weight: 900;
    letter-spacing: .2px;
    padding: .78rem 1.08rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #ff2d55, #ff7a00, #ffcc00);
    box-shadow: 0 16px 34px rgba(255, 122, 0, .28);
    transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    white-space: nowrap;
  }
  .btn-vota:hover{
    transform: translateY(-1px);
    box-shadow: 0 20px 40px rgba(255, 122, 0, .34);
    filter: brightness(1.02);
  }
  .btn-vota .pulse-dot{
    width: 10px; height: 10px;
    border-radius: 999px;
    background: #fff;
    margin-right: .55rem;
    box-shadow: 0 0 0 0 rgba(255,255,255,.75);
    animation: pulse 1.6s infinite;
    display:inline-block;
    vertical-align: middle;
  }
  @keyframes pulse{
    0% { box-shadow: 0 0 0 0 rgba(255,255,255,.75); }
    70%{ box-shadow: 0 0 0 14px rgba(255,255,255,0); }
    100%{ box-shadow: 0 0 0 0 rgba(255,255,255,0); }
  }

  .btn-login{
    border-radius: 999px;
    font-weight: 900;
    padding: .78rem 1.05rem;
    border: 1px solid rgba(255,255,255,.25) !important;
    background: rgba(255,255,255,.10) !important;
    color: #fff !important;
    transition: all .2s ease;
    white-space: nowrap;
  }
  .btn-login:hover{
    background: rgba(255,255,255,.16) !important;
    transform: translateY(-1px);
  }

  /* ===== MODALES PRO (PALETA AZUL) ===== */
  .modal-pro .modal-content{
    border: 0;
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-mid);
  }

  .modal-pro .hero{
    padding: 1.15rem 1.25rem;
    color:#fff;
    background:
      radial-gradient(1200px 400px at 0% 0%, rgba(255,255,255,.18), transparent 60%),
      linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
  }
  .modal-pro .hero h5{ margin: 0; font-weight: 900; letter-spacing: .2px; }
  .modal-pro .hero p{ margin: .35rem 0 0; opacity: .95; font-weight: 700; }

  /* Logo capsula modal */
  .modal-logo-pill{
    display:flex;
    align-items:center;
    gap:.65rem;
    padding: .45rem .7rem;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.20);
  }
  .modal-logo-pill img{
    height: 42px;
    width: auto;
    border-radius: 999px;
    background: #fff;
    padding: 6px;
    box-shadow: 0 10px 24px rgba(2,6,23,.18);
  }

  .modal-pro .modal-body{
    padding: 1.15rem 1.25rem;
    background: #fff;
  }
  .modal-pro .modal-footer{
    border-top: 1px solid rgba(2,6,23,.06);
    padding: .9rem 1.25rem 1.1rem;
    background: #fff;
  }

  .benefits{
    display: grid;
    gap: .6rem;
    margin-top: .85rem;
  }
  .benefit{
    display:flex;
    gap:.65rem;
    padding:.78rem .9rem;
    border-radius: var(--radius-lg);
    background: rgba(32,66,127,.06);
    border: 1px solid rgba(32,66,127,.12);
  }
  .benefit i{
    margin-top: .15rem;
    color: var(--nav-blue);
  }

  .btn-primary-blue{
    border: 0 !important;
    border-radius: 14px;
    padding: .9rem 1rem;
    font-weight: 900;
    color:#fff !important;
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
    box-shadow: 0 14px 30px rgba(32,66,127,.22);
  }
  .btn-primary-blue:hover{ filter: brightness(1.03); }

  .btn-soft{
    border-radius: 14px;
    font-weight: 900;
    padding: .9rem 1rem;
    background: rgba(2,6,23,.06) !important;
    border: 1px solid rgba(2,6,23,.10) !important;
    color:#0f172a !important;
  }
  .btn-soft:hover{
    background: rgba(32,66,127,.08) !important;
    border-color: rgba(32,66,127,.18) !important;
    color: var(--nav-blue) !important;
  }

  /* Login inputs pro */
  .form-pro .input-wrap{ position: relative; }
  .form-pro .input-wrap i{
    position:absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(2,6,23,.45);
    pointer-events:none;
  }
  .form-pro .form-control{
    border-radius: 14px;
    padding: .85rem .95rem .85rem 2.65rem;
    border: 1px solid rgba(2,6,23,.12);
    font-weight: 800;
    color: #0f172a;
    background: #fff;
    transition: box-shadow .2s ease, border-color .2s ease;
  }
  .form-pro .form-control:focus{
    border-color: rgba(32,66,127,.55);
    box-shadow: 0 0 0 .25rem rgba(32,66,127,.14);
  }
  .login-mini-links a{
    text-decoration: none;
    font-weight: 900;
    color: var(--nav-blue);
  }
  .login-mini-links a:hover{ text-decoration: underline; }
</style>

<div class="container-fluid p-0">
  <nav class="navbar navbar-expand-lg fixed-top d-flex align-items-center" id="mainNavbar">

    <!-- LOGO (capsula blanca premium) -->
    <a href="index.php" class="navbar-brand ms-3" aria-label="Ir al inicio">
      <div class="brand-pill">
        <img src="assets/img/admin/estadistica3.png" alt="Logo" class="logo-chip">
        <!-- Si NO quieres texto, borra este bloque -->        
      </div>
    </a>

    <!-- Acciones mobile -->
    <div class="d-lg-none ms-auto d-flex align-items-center gap-2 me-2">    

      <button type="button" class="btn btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">
        <i class="fa fa-user me-2"></i>Ingresar
      </button>
    </div>

    <!-- Toggler -->
    <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Abrir/Cerrar menú">
      <span class="fa fa-bars"></span>
    </button>

    <!-- Menú -->
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <div class="navbar-nav ms-auto py-2 py-lg-0">
        <a href="index.php" class="nav-item nav-link">Inicio</a>
        <a href="nosotros.php" class="nav-item nav-link">Quienes somos</a>
        <a href="servicios.php" class="nav-item nav-link">Servicios</a>
        <a href="contacto.php" class="nav-item nav-link">Contacto</a>
      </div>

      <!-- Acciones desktop -->
      <div class="d-none d-lg-flex align-items-center gap-2 ms-lg-3 me-3">    

        <button type="button" class="btn btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">
        <i class="fa fa-user me-2"></i>Ingresar
      </button>
      </div>
    </div>

  </nav>
</div>

<!-- MODAL VOTA (invita a registrarse) -->
<div class="modal fade modal-pro" id="votaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="hero">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div>
            <div class="modal-logo-pill mb-2">
              <img src="assets/img/admin/estadistica3.png" alt="Logo">
              <div class="fw-bold">Votaciones</div>
            </div>
            <h5><i class="fa fa-bolt me-2"></i>¡Tu voto cuenta!</h5>
            <p>Regístrate en menos de 1 minuto y participa.</p>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
      </div>

      <div class="modal-body">
        <div class="fw-bold" style="color:#0f172a;font-size:1.06rem;">
          Antes de votar, necesitamos validar tu registro ✅
        </div>
        <div class="text-muted mt-1" style="font-weight:700;">
          Esto ayuda a que el proceso sea transparente y seguro.
        </div>

        <div class="benefits">
          <div class="benefit">
            <i class="fa fa-shield"></i>
            <div>
              <div class="fw-bold" style="color:#0f172a;">Votación segura</div>
              <div class="text-muted" style="font-weight:700;">Protegemos la integridad del voto.</div>
            </div>
          </div>
          <div class="benefit">
            <i class="fa fa-clock"></i>
            <div>
              <div class="fw-bold" style="color:#0f172a;">Rápido y sencillo</div>
              <div class="text-muted" style="font-weight:700;">Te toma menos de 60 segundos.</div>
            </div>
          </div>
          <div class="benefit">
            <i class="fa fa-star"></i>
            <div>
              <div class="fw-bold" style="color:#0f172a;">Tu voz tiene poder</div>
              <div class="text-muted" style="font-weight:700;">Participa y haz parte del cambio.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer d-flex gap-2">
        <button type="button" class="btn btn-soft w-50" data-bs-dismiss="modal">Ahora no</button>
        <a href="registro.php" target="_blank" class="btn btn-primary-blue w-50">
          <i class="fa fa-user-plus me-2"></i>Inscríbete
        </a>
      </div>

    </div>
  </div>
</div>

<!-- MODAL LOGIN (colores iguales al navbar) -->
<div class="modal fade modal-pro" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="hero">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div>
            <div class="modal-logo-pill mb-2">
              <img src="assets/img/admin/estadistica3.png" alt="Logo">
              <div class="fw-bold">Votaciones</div>
            </div>
            <h5><i class="fa fa-unlock-alt me-2"></i>Inicia sesión</h5>
            <p>Ingresa para votar, ver resultados y participar.</p>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
      </div>

      <div class="modal-body">
        <form id="loginForm" class="form-pro" autocomplete="off" method="POST" action="index.php">
          <input type="hidden" name="op" value="pms_usrlogin">

          <div class="mb-3">
            <label class="form-label fw-bold">Usuario o correo</label>
            <div class="input-wrap">
              <i class="fa fa-user"></i>
              <input type="text" id="nickname" name="nickname" class="form-control"
                     placeholder="Ej: correo@dominio.com" required>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label fw-bold">Contraseña</label>
            <div class="input-wrap">
              <i class="fa fa-lock"></i>
              <input type="password" id="hashpass" name="hashpass" class="form-control"
                     placeholder="Ingresa tu contraseña" required>
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-between mt-2 login-mini-links">
            <a href="#" class="small">¿Olvidaste tu contraseña?</a>
            <a href="registro.php" target="_blank" class="small">Crear cuenta</a>
          </div>
          

          <button type="submit" class="btn btn-primary-blue w-100 mt-3">
            <i class="fa fa-sign-in me-2"></i>Iniciar sesión
          </button>
        </form>
      </div>

      <div class="modal-footer d-flex gap-2">
        <button type="button" class="btn btn-soft w-50" data-bs-dismiss="modal">Ahora no</button>
        <a href="registro.php" target="_blank" class="btn btn-primary-blue w-50">
          <i class="fa fa-user-plus me-2"></i>Registrarme
        </a>
      </div>

    </div>
  </div>
</div>

<script>
  // Sombra al hacer scroll
  document.addEventListener("scroll", function() {
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    nav.classList.toggle("scrolled", window.scrollY > 10);
  });

  // Margen superior automático para que el navbar no tape contenido
  function aplicarMargenNavbar() {
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    document.body.style.marginTop = nav.offsetHeight + "px";
  }
  document.addEventListener("DOMContentLoaded", aplicarMargenNavbar);
  window.addEventListener("resize", aplicarMargenNavbar);

  // En móvil, al dar click a un link del menú, colapsa
  document.addEventListener("DOMContentLoaded", () => {
    const navbarCollapse = document.getElementById("navbarCollapse");
    if (!navbarCollapse) return;

    navbarCollapse.querySelectorAll("a.nav-link").forEach(a => {
      a.addEventListener("click", () => {
        const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
        if (bsCollapse) bsCollapse.hide();
      });
    });
  });
</script>
