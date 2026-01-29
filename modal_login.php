<?php
/* ==========================================================
   =====================  LOGIN START  ======================
   login.php
   - Modo 1: ?only_modal=1 => imprime SOLO el modal (para fetch)
   - Modo 2: normal => página standalone que auto-abre el modal
   - Modo 3: INCLUDE_MODAL_ONLY => solo renderiza modal (para include)
   - Bootstrap 5.0 compatible (sin getOrCreateInstance)
   - NO rompe backend: dispara submit y tu JS externo decide
   =====================  LOGIN END  =========================
========================================================== */

/** ====== RENDER MODAL (reusable) ====== */
function renderLoginModal($logoLogin, $standalone = false){
  $logoSafe = htmlspecialchars((string)$logoLogin, ENT_QUOTES, 'UTF-8');
  ?>
  <style>
    :root{
      --nav:#0E2A52;
      --nav2:#143B73;
      --sky:#00c2ff;

      --ink:#0f172a;
      --muted:#64748b;

      --radius-xl: 24px;
      --radius-lg: 18px;

      --shadow-strong: 0 44px 160px rgba(2,6,23,.35);
      --shadow-mid: 0 18px 70px rgba(2,6,23,.16);
    }

    .login-modal .modal-dialog{ max-width: 520px; }

    .login-modal .modal-content{
      border: 0;
      border-radius: var(--radius-xl);
      overflow: hidden;
      background: #fff;
      box-shadow: var(--shadow-strong);
      position: relative;
    }

    /* ===== HEADER WOW (azul premium) ===== */
    .login-modal .modal-header{
      border:0;
      padding: 16px 18px 12px;
      background: linear-gradient(135deg, var(--nav), var(--nav2));
      position: relative;
      color:#fff;
    }
    .login-modal .modal-header::after{
      content:"";
      position:absolute;
      inset:-2px;
      background:
        radial-gradient(240px 120px at 18% 20%, rgba(255,255,255,.18), transparent 62%),
        radial-gradient(260px 140px at 88% 10%, rgba(0,194,255,.18), transparent 62%),
        radial-gradient(260px 140px at 60% 92%, rgba(124,58,237,.12), transparent 62%);
      pointer-events:none;
      opacity:.95;
    }

    .login-head{
      position: relative;
      z-index: 2;
      display:flex;
      gap:12px;
      align-items:center;
      width:100%;
      min-width:0;
    }

    .login-logo{
      width: 46px; height: 46px;
      border-radius: 16px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.22);
      box-shadow: 0 16px 50px rgba(2,6,23,.18);
      display:grid; place-items:center;
      overflow:hidden;
      flex: 0 0 auto;
    }
    .login-logo img{ width:78%; height:78%; object-fit:contain; display:block; }

    .login-titles b{
      display:block;
      font-weight: 950;
      letter-spacing: -.02em;
      color:#fff;
      font-size: 16px;
      line-height: 1.1;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
    }
    .login-titles small{
      display:block;
      font-weight: 800;
      color: rgba(255,255,255,.90);
      margin-top: 3px;
      font-size: 12px;
    }

    .login-modal .btn-close{
      position: relative;
      z-index: 2;
      filter: invert(1);
      opacity:.85;
      border-radius: 12px;
    }
    .login-modal .btn-close:hover{ opacity:1; }

    /* ===== BODY ===== */
    .login-modal .modal-body{
      padding: 14px 18px 16px;
      background:
        radial-gradient(980px 420px at 10% 0%, rgba(14,42,82,.10), transparent 55%),
        radial-gradient(780px 380px at 90% 10%, rgba(0,194,255,.12), transparent 60%),
        linear-gradient(180deg, rgba(255,255,255,.995), rgba(255,255,255,.94));
    }

    .login-panel{
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(255,255,255,.90);
      border-radius: var(--radius-lg);
      padding: 14px;
      box-shadow: var(--shadow-mid);
    }

    .login-field{ margin-bottom: 12px; }
    .login-label{
      font-weight: 950;
      font-size: 13px;
      color: #111827;
      margin-bottom: 7px;
    }

    .login-input-wrap{ position:relative; }

    .login-ic{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(100,116,139,.95);
      pointer-events:none;
      font-size: 16px;
      z-index: 2;
    }

    .login-input{
      height: 54px;
      width: 100%;
      border-radius: 16px;
      border: 1px solid rgba(15,23,42,.14);
      background: #fff;
      padding: 0 14px 0 46px; /* ✅ limpio */
      font-size: 16px;
      font-weight: 800;
      transition: border-color .14s ease, box-shadow .14s ease, transform .14s ease;
      outline: none;
    }
    .login-input:focus{
      border-color: rgba(14,42,82,.55);
      box-shadow: 0 0 0 6px rgba(0,194,255,.14);
      transform: translateY(-1px);
    }

    /* ✅ Password: deja espacio al ojo */
    .login-input.is-password{ padding-right: 54px !important; }

    .login-eye{
      position:absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      border: 0;
      background: rgba(15,23,42,.06);
      width: 42px;
      height: 42px;
      border-radius: 14px;
      color: rgba(100,116,139,.95);
      cursor:pointer;
      transition: transform .12s ease, background .12s ease, color .12s ease;
      display:grid;
      place-items:center;
      z-index: 3;
    }
    .login-eye:hover{
      background: rgba(0,194,255,.14);
      color: rgba(14,42,82,.98);
      transform: translateY(-50%) scale(1.03);
    }

    .login-actions{ display:grid; gap:10px; margin-top: 12px; }

    .login-btn{
      height: 54px;
      border-radius: 16px;
      border: 0;
      color: #fff;
      font-weight: 950;
      letter-spacing: .2px;
      background: linear-gradient(135deg, #021b5a, #0B3EDC);
      box-shadow: 0 22px 80px rgba(11,62,220,.28);
      transition: transform .14s ease, box-shadow .14s ease, filter .14s ease;
      display:flex;
      align-items:center;
      justify-content:center;
      gap: 10px;
      position: relative;
      overflow: hidden;
      user-select:none;
      -webkit-tap-highlight-color: transparent;
      width: 100%;
    }
    .login-btn::after{
      content:"";
      position:absolute;
      inset:-2px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.26), transparent);
      transform: translateX(-130%) skewX(-18deg);
      animation: loginShine 2.6s ease-in-out infinite;
      pointer-events:none;
      opacity: .9;
    }
    @keyframes loginShine{
      0%{ transform: translateX(-130%) skewX(-18deg); opacity:0; }
      18%{ opacity:1; }
      45%{ transform: translateX(230%) skewX(-18deg); opacity:0; }
      100%{ opacity:0; }
    }
    .login-btn:hover{
      transform: translateY(-2px);
      box-shadow: 0 32px 110px rgba(11,62,220,.36);
      filter: saturate(1.10);
      color:#fff;
    }
    .login-btn[disabled]{ opacity:.72; cursor:not-allowed; transform:none; filter:none; }

    .login-mini{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap: 10px;
      margin-top: 12px;
      color: rgba(71,85,105,.95);
      font-weight: 800;
      font-size: 13px;
    }
    .login-mini a{
      text-decoration:none;
      font-weight: 950;
      color: var(--nav);
    }
    .login-mini a:hover{ text-decoration: underline; }

    .login-footer{ border:0; padding: 0 18px 16px; }
    .login-tip{
      display:flex;
      gap: 10px;
      align-items:flex-start;
      padding: 12px 12px;
      border-radius: 16px;
      background: rgba(248,250,252,.92);
      border: 1px dashed rgba(14,42,82,.22);
      color: rgba(71,85,105,.96);
      font-weight: 800;
      font-size: 13px;
    }
    .login-tip i{ margin-top: 2px; color: rgba(14,42,82,.92); }

    @media (max-width: 520px){
      .login-modal .modal-header{ padding: 14px 14px 10px; }
      .login-modal .modal-body{ padding: 12px 14px 14px; }
      .login-footer{ padding: 0 14px 14px; }
      .login-panel{ padding: 12px; }
      .login-modal .modal-dialog{ margin: 12px; }
    }
  </style>

  <div class="modal fade login-modal" id="loginModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div class="login-head">
            <div class="login-logo">
              <img src="<?= $logoSafe; ?>" alt="Logo">
            </div>
            <div class="login-titles">
              <b>Iniciar sesión</b>
              <small>Accede para continuar</small>
            </div>
          </div>

          <?php if($standalone): ?>
            <button type="button" class="btn-close" aria-label="Cerrar" id="btnLoginCloseStandalone"></button>
          <?php else: ?>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          <?php endif; ?>
        </div>

        <div class="modal-body">
          <div class="login-panel">
            <form id="formLoginVotantes" autocomplete="on">
              <div class="login-field">
                <div class="login-label">Correo o Usuario</div>
                <div class="login-input-wrap">
                  <i class="fa-solid fa-user login-ic"></i>
                  <input type="text"
                         class="login-input"
                         id="login_user"
                         name="login_user"
                         placeholder="Escribe tu usuario o correo"
                         autocomplete="username"
                         required>
                </div>
              </div>

              <div class="login-field">
                <div class="login-label">Contraseña</div>
                <div class="login-input-wrap">
                  <i class="fa-solid fa-lock login-ic"></i>
                  <input type="password"
                         class="login-input is-password"
                         id="login_password"
                         name="login_password"
                         placeholder="Escribe tu contraseña"
                         autocomplete="current-password"
                         required>
                  <!-- ✅ Botón para quitar puntos y mostrar texto -->
                  <button type="button"
                          class="login-eye"
                          id="btnLoginTogglePw"
                          aria-label="Ver contraseña"
                          aria-pressed="false">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>

              <div class="login-actions">
                <button type="button" class="login-btn" id="btnLoginSubmit">
                  <span class="login-btn-ic"><i class="fa-solid fa-arrow-right-to-bracket"></i></span>
                  <span class="login-btn-txt">Entrar</span>
                </button>
              </div>

              <div class="login-mini">
                <span><i class="fa-solid fa-shield-halved me-1"></i> Acceso seguro</span>
                <a href="registro.php">Volver al registro</a>
              </div>
            </form>
          </div>
        </div>

        <div class="login-footer">
          <div class="login-tip">
            <i class="fa-solid fa-circle-info"></i>
            <div>Ingresa con tu usuario/correo y contraseña para continuar.</div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script>
    // ✅ Toggle password (quita puntos / muestra texto)
    (function(){
      const btn = document.getElementById('btnLoginTogglePw');
      const input = document.getElementById('login_password');
      if(!btn || !input) return;

      btn.addEventListener('click', function(){
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        btn.setAttribute('aria-pressed', isPass ? 'true' : 'false');

        const icon = btn.querySelector('i');
        if(icon){
          icon.classList.toggle('fa-eye', !isPass);
          icon.classList.toggle('fa-eye-slash', isPass);
        }
        input.focus();
      });
    })();

    // Submit safe (no inventa backend)
    (function(){
      const btn = document.getElementById('btnLoginSubmit');
      const form = document.getElementById('formLoginVotantes');
      if(!btn || !form) return;

      form.addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
          e.preventDefault();
          btn.click();
        }
      });

      btn.addEventListener('click', function(){
        btn.setAttribute('disabled','disabled');
        const txt = btn.querySelector('.login-btn-txt');
        const ic  = btn.querySelector('.login-btn-ic i');
        if(txt) txt.textContent = 'Validando...';
        if(ic){
          ic.classList.remove('fa-arrow-right-to-bracket');
          ic.classList.add('fa-spinner','fa-spin');
        }

        const ev = new Event('submit', { bubbles:true, cancelable:true });
        form.dispatchEvent(ev);

        setTimeout(function(){
          btn.removeAttribute('disabled');
          if(txt) txt.textContent = 'Entrar';
          if(ic){
            ic.classList.remove('fa-spinner','fa-spin');
            ic.classList.add('fa-arrow-right-to-bracket');
          }
        }, 900);
      });
    })();

    <?php if($standalone): ?>
    // Standalone close => back
    (function(){
      const btn = document.getElementById('btnLoginCloseStandalone');
      if(btn){
        btn.addEventListener('click', function(){
          window.history.back();
        });
      }
    })();
    <?php endif; ?>
  </script>
  <?php
}

/* ==========================================================
   MODE: include (cuando se incluye desde otro archivo)
========================================================== */
if (defined('INCLUDE_MODAL_ONLY')) {
  // Si el archivo padre ya definió logo, úsalo; si no, cae al default.
  $logoModal = $logo_configuracion ?? $logo ?? $logoSistema ?? 'assets/img/admin/estadistica3.png';
  renderLoginModal($logoModal, false);
  return;
}

/* ==========================================================
   Cargar dependencias SOLO si NO es include mode
========================================================== */
require './admin/include/generic_classes.php';
include './admin/include/generic_info_configuracion.php';

/** Logo dinámico */
$logoLogin = $logo_configuracion ?? $logo ?? $logoSistema ?? 'assets/img/admin/estadistica3.png';

/* ==========================================================
   MODE: only_modal (para fetch desde registro.php)
========================================================== */
if (isset($_GET['only_modal']) && $_GET['only_modal'] == '1') {
  renderLoginModal($logoLogin, false);
  exit;
}

/* ==========================================================
   MODE: página normal (standalone)
========================================================== */
include './admin/include/head.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    html, body{ height:100%; }
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:
        radial-gradient(1200px 600px at 18% -10%, rgba(19,43,82,.22), transparent 60%),
        radial-gradient(900px 450px at 90% 10%, rgba(0,194,255,.14), transparent 55%),
        linear-gradient(#fff, #fff);
      overflow-x:hidden;
    }
    .login-page-bg{
      position: fixed;
      inset: 0;
      pointer-events:none;
      background:
        radial-gradient(800px 380px at 15% 20%, rgba(0,194,255,.16), transparent 60%),
        radial-gradient(900px 420px at 90% 10%, rgba(124,58,237,.10), transparent 60%),
        radial-gradient(900px 420px at 70% 90%, rgba(251,113,133,.10), transparent 60%);
      filter: saturate(1.08);
    }
  </style>
</head>
<body>

  <div class="login-page-bg"></div>

  <?php renderLoginModal($logoLogin, true); ?>

  <!-- libs (standalone solo): por si head.php no los trae -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Auto-open modal en standalone
    (function(){
      const el = document.getElementById('loginModal');
      if(!el || typeof bootstrap === "undefined" || !bootstrap.Modal) return;

      const modal = new bootstrap.Modal(el, { backdrop:'static', keyboard:false });
      modal.show();

      el.addEventListener('shown.bs.modal', function(){
        const u = document.getElementById('login_user');
        if(u) u.focus();
      });

      el.addEventListener('hidden.bs.modal', function(){
        window.history.back();
      });
    })();
  </script>
</body>
</html>
