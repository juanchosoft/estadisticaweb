<!-- MODAL LOGIN PRO -->
<style>
  /* Reutiliza el estilo pro del modal (si ya lo tienes, puedes omitir este bloque) */
  .modal-pro .modal-content{
    border: 0;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 25px 70px rgba(2,6,23,.22);
  }
  .modal-pro .hero{
    padding: 1.15rem 1.25rem;
    color:#fff;
    background: #20427F !important;
  }
  .modal-pro .hero h5{ margin:0; font-weight: 900; letter-spacing: .2px; }
  .modal-pro .hero p{ margin:.35rem 0 0; opacity:.95; font-weight: 600; }

  .modal-pro .modal-body{ padding: 1.15rem 1.25rem; }
  .modal-pro .modal-footer{
    border-top: 1px solid rgba(2,6,23,.06);
    padding: .9rem 1.25rem 1.1rem;
  }

  /* Inputs pro */
  .form-pro .input-wrap{
    position: relative;
  }
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
    font-weight: 700;
    color: #0f172a;
    background: #fff;
    transition: box-shadow .2s ease, border-color .2s ease;
  }
  .form-pro .form-control:focus{
    border-color: rgba(13,110,253,.45);
    box-shadow: 0 0 0 .25rem rgba(13,110,253,.12);
  }

  .btn-login-primary{
    border: 0 !important;
    border-radius: 14px;
    padding: .9rem 1rem;
    font-weight: 900;
    color:#fff !important;
    background: #20427F !important;
    box-shadow: 0 14px 30px rgba(13,110,253,.22);
  }
  .btn-login-primary:hover{ filter: brightness(1.03); }

  .btn-login-secondary{
    border-radius: 14px;
    font-weight: 900;
    padding: .9rem 1rem;
    background: rgba(2,6,23,.06) !important;
    border: 1px solid rgba(2,6,23,.10) !important;
    color:#0f172a !important;
  }
  .btn-login-secondary:hover{
    background: rgba(13,110,253,.08) !important;
    border-color: rgba(13,110,253,.25) !important;
    color: #0d6efd !important;
  }

  .login-mini-links a{
    text-decoration: none;
    font-weight: 800;
  }
  .login-mini-links a:hover{ text-decoration: underline; }
</style>

<div class="modal fade modal-pro" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <!-- Header pro -->
      <div class="hero">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div class="d-flex align-items-center gap-3">
            <img src="assets/img/admin/estadistica3.png" alt="Logo" style="height:46px;width:auto;border-radius:12px;background:rgba(255,255,255,.18);padding:6px;">
            <div>
              <h5><i class="fa fa-unlock-alt me-2"></i>Inicia sesión</h5>
              <p>Ingresa para votar, ver resultados y participar.</p>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form id="loginForm" class="form-pro" autocomplete="off" method="POST" action="index.php">
          <input type="hidden" name="op" value="pms_usrlogin">

          <div class="mb-3">
            <label class="form-label fw-bold">Usuario o correo</label>
            <div class="input-wrap">
              <i class="fa fa-user"></i>
              <input
                type="text"
                id="nickname"
                name="nickname"
                class="form-control"
                placeholder="Ej: correo@dominio.com"
                required
              >
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label fw-bold">Contraseña</label>
            <div class="input-wrap">
              <i class="fa fa-lock"></i>
              <input
                type="password"
                id="hashpass"
                name="hashpass"
                class="form-control"
                placeholder="Ingresa tu contraseña"
                required
              >
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-between mt-2 login-mini-links">
            <a href="#" class="small text-muted">¿Olvidaste tu contraseña?</a>
            <a href="registro.php" target="_blank" class="small">Crear cuenta</a>
          </div>

          <button type="submit" class="btn btn-login-primary w-100 mt-3">
            <i class="fa fa-sign-in me-2"></i>Iniciar sesión
          </button>
        </form>
      </div>

      <!-- Footer -->
      <div class="modal-footer d-flex gap-2">
        <button type="button" class="btn btn-login-secondary w-50" data-bs-dismiss="modal">
          Ahora no
        </button>
        <a href="registro.php" target="_blank" class="btn btn-login-primary w-50">
          <i class="fa fa-user-plus me-2"></i>Registrarme
        </a>
      </div>

    </div>
  </div>
</div>
