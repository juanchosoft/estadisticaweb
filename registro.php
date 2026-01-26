<?php 
$_REQUEST["route_map"] = true;

include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Votantes.php';
include './admin/classes/Departamento.php';

include './admin/include/generic_info_configuracion.php';



// Informacion de departamentos
$departamentos = Departamento::getAll(null);
$isValidDep = $departamentos['output']['valid'] ?? false;
$departamentosResponse = $departamentos['output']['response'] ?? [];

$optionDep = "";
foreach ($departamentosResponse as $dep) {
  $optionDep .= "<option value='" . $dep['codigo_departamento'] . "'>" . $dep['codigo_departamento'] . " - " . $dep['departamento'] . "</option>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


  <!-- Bootstrap Icons / FontAwesome (si ya lo tienes en tu head.php, puedes quitar estas líneas) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<?php include './modal_login.php'; ?>
  <style>
    :root{
      --ink:#0f172a;
      --muted:#64748b;
      --line:rgba(15,23,42,.10);
      --card:#ffffff;
      --bg:#f6f8fc;
      --brand:#20427f;
      --brand2:#0f2d63;
      --radius:18px;
      --shadow: 0 18px 60px rgba(2,6,23,.08);
      --shadow2: 0 10px 25px rgba(2,6,23,.08);
    }

    body{
      background: radial-gradient(1200px 600px at 20% -10%, rgba(32,66,127,.14), transparent 60%),
                  radial-gradient(900px 450px at 90% 10%, rgba(14,165,233,.12), transparent 55%),
                  var(--bg);
      color: var(--ink);
    }

    /* Navbar */
    #mainNavbar{
      border-bottom: 1px solid rgba(15,23,42,.06);
      backdrop-filter: blur(10px);
    }
    #mainNavbar.scrolled{
      box-shadow: var(--shadow2);
    }
    .nav-link{
      color: #334155 !important;
      font-weight: 600;
    }
    .nav-link:hover{
      color: var(--brand) !important;
    }

    /* Shell */
    .page-wrap{
      max-width: 980px;
      margin: 0 auto;
      padding: 28px 14px 60px;
    }

    /* Card */
    .saas-card{
      background: var(--card);
      border: 1px solid rgba(15,23,42,.08);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    /* Header */
    .form-hero{
      padding: 22px 22px;
      background: linea-gradient(135deg, rgba(32,66,127,.10), rgba(14,165,233,.06));r
      border-bottom: 1px solid rgba(15,23,42,.08);
    }
    .form-hero .title{
      font-size: 1.25rem;
      font-weight: 900;
      margin: 0;
      letter-spacing: .2px;
    }
    .form-hero .subtitle{
      margin: 6px 0 0;
      color: var(--muted);
      font-weight: 600;
      font-size: .95rem;
      line-height: 1.35;
    }
    .pill{
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      border-radius: 999px;
      font-weight: 800;
      font-size: .85rem;
      border: 1px solid rgba(32,66,127,.18);
      background: rgba(32,66,127,.06);
      color: var(--brand);
    }

    /* Sections */
    .section{
      padding: 18px 22px 6px;
    }
    .section-title{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      margin: 0 0 10px;
    }
    .section-title h5{
      margin: 0;
      font-weight: 900;
      font-size: 1.03rem;
    }
    .section-title small{
      color: var(--muted);
      font-weight: 600;
    }
    .divider{
      border-top: 1px dashed rgba(15,23,42,.18);
      margin: 10px 0 0;
    }

    /* Form controls */
    .form-label{
      font-weight: 800;
      color: #1f2937;
    }
    .help{
      color: var(--muted);
      font-size: .85rem;
      font-weight: 600;
      margin-top: 4px;
    }
    .input-group-text{
      background: #f8fafc;
      border-color: rgba(15,23,42,.12);
      color: #334155;
    }
    .form-control, .form-select{
      border-color: rgba(15,23,42,.12);
    }
    .form-control:focus, .form-select:focus{
      border-color: rgba(32,66,127,.45);
      box-shadow: 0 0 0 .2rem rgba(32,66,127,.12);
    }

    /* Footer actions */
    .actions{
      padding: 18px 22px 24px;
      border-top: 1px solid rgba(15,23,42,.08);
      background: rgba(248,250,252,.6);
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .actions .left{
      color: var(--muted);
      font-weight: 700;
      font-size: .9rem;
      display:flex;
      gap: 10px;
      align-items:center;
    }
    .btn-soft{
      background: #eef2f7;
      border: 1px solid rgba(15,23,42,.10);
      font-weight: 800;
    }
    .btn-brand{
      background: var(--brand);
      border: 1px solid rgba(15,23,42,.18);
      color: #fff;
      font-weight: 900;
    }
    .btn-brand:hover{
      background: var(--brand2);
      color: #fff;
    }

    /* Responsive tweaks */
    @media (min-width: 768px){
      .form-hero{ padding: 26px 28px; }
      .section{ padding: 18px 28px 6px; }
      .actions{ padding: 18px 28px 24px; }
      .page-wrap{ padding-top: 36px; }
    }
    /* Botón Ingresar - Azul llamativo */
.btn-login1{
  background: linear-gradient(135deg, #021b5aff, #0B3EDC);
  color: #ffffff !important;
  font-weight: 800;
  border: none;
  border-radius: 14px;
  padding: 10px 20px;
  box-shadow: 0 8px 22px rgba(30,94,255,.35);
  transition: all .2s ease;
}

.btn-login1 i{
  color: #ffffff;
}

/* Hover */
.btn-login1:hover{
  background: linear-gradient(135deg, #0B3EDC, #0832B3);
  box-shadow: 0 12px 28px rgba(30,94,255,.45);
  transform: translateY(-1px);
  color: #ffffff;
}

/* Focus / active */
.btn-login1:focus,
.btn-login1:active{
  background: linear-gradient(135deg, #0832B3, #06268A);
  box-shadow: 0 0 0 0.2rem rgba(30,94,255,.35);
  color: #ffffff;
}

/* ====== SAAS PASSWORD FIELD (adaptado) ====== */
.password-field-saas{
  position: relative;
  border-radius: 14px;
}

.password-field-saas .pw-input{
  height: 48px;
  border-radius: 14px;
  padding-left: 46px;
  padding-right: 44px;
  border: 1px solid rgba(15,23,42,.14);
  background: #fff;
  font-size: 14px;
  transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.password-field-saas .pw-input:focus{
  border-color: rgba(37,99,235,.75);
  box-shadow: 0 0 0 4px rgba(37,99,235,.12);
  transform: translateY(-1px);
}

.password-field-saas .pw-icon-left{
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(100,116,139,.9);
  font-size: 14px;
  pointer-events:none;
  z-index: 2;
}

.password-field-saas .pw-glow{
  position:absolute;
  inset: -2px;
  border-radius: 16px;
  pointer-events:none;
  opacity: 0;
  transition: opacity .18s ease;
  background: radial-gradient(120px 60px at 30% 50%, rgba(37,99,235,.14), transparent 70%);
  z-index: 0;
}
.password-field-saas .pw-input:focus ~ .pw-glow{
  opacity: 1;
}

/* 👁️ ojo */
.password-field-saas .pw-toggle{
  position:absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  border: 0;
  background: transparent;
  color: rgba(100,116,139,.85);
  padding: 7px 8px;
  border-radius: 10px;
  cursor:pointer;
  transition: background .18s ease, color .18s ease, transform .18s ease, opacity .18s ease;
  opacity: 0;          
  pointer-events: none;
  z-index: 3;
}
.password-field-saas .pw-toggle:hover{
  background: rgba(37,99,235,.08);
  color: rgba(37,99,235,.95);
  transform: translateY(-50%) scale(1.03);
}
.password-field-saas .pw-toggle.active{
  color: rgba(37,99,235,.95);
}

/* ✅ aparece cuando hay texto (JS pone .has-value) */
.password-field-saas.has-value .pw-toggle{
  opacity: 1;
  pointer-events: auto;
}

/* ===== Meter ===== */
.pw-meter{
  height: 10px;
  border-radius: 999px;
  background: rgba(2,6,23,.06);
  overflow: hidden;
  border: 1px solid rgba(2,6,23,.08);
}
.pw-meter .pw-meter-bar{
  height: 100%;
  width: 0%;
  border-radius: 999px;
  transition: width .2s ease;
}

.pw-meter-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
  margin-top: 6px;
}
.pw-meter-row .pw-meter-text{
  font-size: 12px;
  color: rgba(100,116,139,1);
  font-weight: 700;
}
.pw-meter-row .pw-score{
  font-size: 12px;
  font-weight: 900;
  color: rgba(15,23,42,.9);
  white-space: nowrap;
}

/* ===== Rules ===== */
.pw-rules{
  list-style: none;
  padding-left: 0;
  margin-bottom: 0;
  display:grid;
  grid-template-columns: 1fr;
  gap: 6px;
}
@media (min-width: 768px){
  .pw-rules{ grid-template-columns: 1fr 1fr; }
}
.pw-rules li{
  display:flex;
  align-items:center;
  gap: 8px;
  font-size: 12px;
  color: rgba(100,116,139,1);
  font-weight: 700;
  padding: 8px 10px;
  border-radius: 12px;
  border: 1px solid rgba(2,6,23,.08);
  background: rgba(248,250,252,1);
}
.pw-rules li i{
  font-size: 10px;
  color: rgba(148,163,184,1);
}

.pw-rules li.ok{
  color: rgba(15,23,42,.95);
  border-color: rgba(34,197,94,.25);
  background: rgba(34,197,94,.08);
}
.pw-rules li.ok i{
  color: rgba(34,197,94,.95);
}

.pw-bar-weak  { background: linear-gradient(90deg, rgba(239,68,68,.9), rgba(239,68,68,.55)); }
.pw-bar-mid   { background: linear-gradient(90deg, rgba(245,158,11,.95), rgba(245,158,11,.55)); }
.pw-bar-good  { background: linear-gradient(90deg, rgba(59,130,246,.95), rgba(59,130,246,.55)); }
.pw-bar-strong{ background: linear-gradient(90deg, rgba(34,197,94,.95), rgba(34,197,94,.55)); }

  </style>
</head>
<script>
(function(){
  function initOne(wrap){
    if (!wrap || wrap.dataset.pwInit === '1') return;
    wrap.dataset.pwInit = '1';

    const input = wrap.querySelector('.js-pw-input');
    const btn   = wrap.querySelector('.js-pw-toggle');
    const icon  = btn ? btn.querySelector('i') : null;

    const bar   = wrap.parentElement.querySelector('.js-pw-bar');
    const text  = wrap.parentElement.querySelector('.js-pw-text');
    const score = wrap.parentElement.querySelector('.js-pw-score');
    const rules = wrap.parentElement.querySelector('.js-pw-rules');

    if(!input || !btn || !bar || !text || !score || !rules) return;

    function setRule(ruleName, ok){
      const li = rules.querySelector('[data-rule="'+ruleName+'"]');
      if(!li) return;
      li.classList.toggle('ok', !!ok);

      const i = li.querySelector('i');
      if(i){
        i.classList.toggle('fa-circle', !ok);
        i.classList.toggle('fa-check-circle', !!ok);
      }
    }

    function cleanBarClasses(){
      bar.classList.remove('pw-bar-weak','pw-bar-mid','pw-bar-good','pw-bar-strong');
    }

    function evaluatePassword(pw){
      pw = String(pw || '');

      const hasLen   = pw.length >= 8;
      const hasUpper = /[A-ZÁÉÍÓÚÑ]/.test(pw);
      const hasNum   = /\d/.test(pw);
      const hasSpec  = /[^A-Za-z0-9ÁÉÍÓÚÑáéíóúñ]/.test(pw);

      setRule('len', hasLen);
      setRule('upper', hasUpper);
      setRule('num', hasNum);
      setRule('spec', hasSpec);

      let s = 0;
      if (hasLen) s += 25;
      if (hasUpper) s += 20;
      if (hasNum) s += 20;
      if (hasSpec) s += 20;

      if (pw.length >= 12) s += 10;
      if (pw.length >= 16) s += 5;

      const lower = pw.toLowerCase();
      if (/^(1234|12345|123456|password|qwerty|admin)/.test(lower)) s = Math.max(10, s - 35);
      if (/(.)\1\1/.test(pw)) s = Math.max(10, s - 10);

      s = Math.max(0, Math.min(100, s));

      let label = 'Débil', cls = 'pw-bar-weak';
      if (s >= 35) { label = 'Media'; cls = 'pw-bar-mid'; }
      if (s >= 60) { label = 'Fuerte'; cls = 'pw-bar-good'; }
      if (s >= 80) { label = 'Brutal'; cls = 'pw-bar-strong'; }

      return { score: s, label, cls };
    }

    function updateUI(){
      const val = input.value || '';
      wrap.classList.toggle('has-value', val.trim().length > 0);

      if (!val){
        cleanBarClasses();
        bar.style.width = '0%';
        text.textContent = 'Escribe una contraseña para ver la fuerza';
        score.textContent = '';
        setRule('len', false); setRule('upper', false); setRule('num', false); setRule('spec', false);
        return;
      }

      const r = evaluatePassword(val);
      cleanBarClasses();
      bar.classList.add(r.cls);
      bar.style.width = r.score + '%';
      text.textContent = 'Fuerza: ' + r.label;
      score.textContent = r.score + '/100';
    }

    btn.addEventListener('click', function(){
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.classList.toggle('active', show);
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');

      if(icon){
        icon.classList.toggle('fa-eye', !show);
        icon.classList.toggle('fa-eye-slash', show);
      }
      input.focus();
    });

    input.addEventListener('input', updateUI);
    input.addEventListener('focus', updateUI);
    input.addEventListener('blur', updateUI);

    updateUI();
  }

  function initAll(){
    document.querySelectorAll('.js-pw-wrap').forEach(initOne);
  }

  // ✅ init normal
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  // ✅ fallback por si esto está dentro de un modal o contenido que se inyecta luego
  let tries = 0;
  const t = setInterval(function(){
    tries++;
    initAll();
    if (tries >= 10) clearInterval(t);
  }, 300);

})();
</script>

<body>

<?php include './admin/include/loading.php'; ?>

<?php include './admin/include/menu_registro.php'; ?>
<?php include './modal_login.php'; ?>

<!-- Content -->
<div class="page-wrap">
  <div class="saas-card">

    <!-- Hero -->
    <div class="form-hero">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
          <div class="pill"><i class="fa-solid fa-user-plus"></i> Nuevo registro</div>
          <h1 class="title mt-2">Crea tu cuenta en 2 minutos</h1>
          <p class="subtitle">
            Completa tus datos y confirma tu ubicación.  
            Los campos con <b>*</b> son obligatorios para poder guardar.
          </p>
        </div>
        <div class="text-end">
          <small class="text-muted fw-bold">¿Ya tienes cuenta?</small><br>          
           <button type="button" class="btn btn-login1" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="fa fa-user me-2"></i>Ingresa ya
            </button>        
        </div>
      </div>
    </div>

    <form id="formvotantes" class="m-0">
      <input type="hidden" name="op" id="op" />
      <input type="hidden" name="idVotantes" id="idVotantes" />
      <input type="hidden" id="estado" name="estado" value="activo">

      <!-- 1) Datos personales -->
      <div class="section">
        <div class="section-title">
          <h5><i class="fa-solid fa-id-card me-2"></i> Paso 1: Datos personales</h5>
          <small>Para identificarte y contactarte</small>
        </div>
        <div class="divider"></div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label">Nombre completo *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
              <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required
                     placeholder="Ej: Juan David Pérez">
            </div>
            <div class="help">Escríbelo tal como aparece en tu documento.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Correo electrónico *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
              <input type="email" class="form-control" id="email" name="email" required
                     placeholder="Ej: correo@dominio.com" onblur="VOTANTES.checkAvailability(this)">
            </div>
            <div class="help">Lo usamos para recuperar tu acceso si lo necesitas.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Nombre de usuario *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-user-tag"></i></span>
              <input type="text" class="form-control" id="username" name="username" required
                     placeholder="Ej: juanperez" onblur="VOTANTES.checkAvailability(this)">
            </div>
            <div class="help">Debe ser único. Evita espacios.</div>
          </div>

          <div class="col-md-6">
  <label class="form-label fw-semibold">Contraseña</label>

  <div class="password-field-saas js-pw-wrap">
    <i class="fa fa-lock pw-icon-left"></i>

    <input type="password"
            id="password"
           name="password"
           class="form-control pw-input js-pw-input"
           placeholder="Ingresa tu contraseña"
           autocomplete="current-password"
           required>

    <button type="button"
            class="pw-toggle js-pw-toggle"
            aria-label="Mostrar u ocultar contraseña"
            aria-pressed="false">
      <i class="fa fa-eye"></i>
    </button>

    <span class="pw-glow"></span>
  </div>

  <div class="pw-meter mt-2">
    <div class="pw-meter-bar js-pw-bar"></div>
  </div>

  <div class="pw-meter-row">
    <span class="pw-meter-text js-pw-text">Escribe una contraseña para ver la fuerza</span>
    <span class="pw-score js-pw-score"></span>
  </div>

  <ul class="pw-rules mt-2 js-pw-rules">
    <li data-rule="len"><i class="fa fa-circle"></i> Mínimo 8 caracteres</li>
    <li data-rule="upper"><i class="fa fa-circle"></i> Al menos 1 mayúscula</li>
    <li data-rule="num"><i class="fa fa-circle"></i> Al menos 1 número</li>
    <li data-rule="spec"><i class="fa fa-circle"></i> Al menos 1 símbolo (@#$%...)</li>
  </ul>
</div>



        </div>
      </div>

      <!-- 2) Ubicación -->
      <div class="section">
        <div class="section-title">
          <h5><i class="fa-solid fa-location-dot me-2"></i> Paso 2: Ubicación</h5>
          <small>Para organizar datos por territorio</small>
        </div>
        <div class="divider"></div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label">Departamento *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-map"></i></span>
              <select id="tbl_departamento_id" name="tbl_departamento_id" class="form-select" required>
                <option value="">Selecciona tu departamento</option>
                <?= $optionDep ?>
              </select>
            </div>
            <div class="help">Si no aparece, valida con soporte.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Municipio *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-location-crosshairs"></i></span>
              <select id="tbl_municipio_id" name="tbl_municipio_id" class="form-select" required>
                <option value="">Primero elige un departamento</option>
              </select>
            </div>
            <div class="help">Se carga automáticamente según el departamento.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Comuna</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-city"></i></span>
              <input type="text" class="form-control" id="comuna" name="comuna" placeholder="Ej: Comuna 3">
            </div>
            <div class="help">Opcional. Si no aplica, déjalo vacío.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Barrio</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-house"></i></span>
              <input type="text" class="form-control" id="barrio" name="barrio" placeholder="Ej: La Esperanza">
            </div>
            <div class="help">Opcional. Ayuda a ubicarte mejor.</div>
          </div>
        </div>
      </div>

      <!-- 3) Perfil demográfico -->
      <div class="section">
        <div class="section-title">
          <h5><i class="fa-solid fa-chart-pie me-2"></i> Paso 3: Perfil (para estadísticas)</h5>
          <small>Tu información se usa en reportes agregados</small>
        </div>
        <div class="divider"></div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label">Ideología política *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-scale-balanced"></i></span>
              <select class="form-select" id="ideologia" name="ideologia" required>
                <option value="">Selecciona una opción</option>
                <option value="izquierda">Izquierda</option>
                <option value="centro_izquierda">Centro izquierda</option>
                <option value="centro">Centro</option>
                <option value="centro_derecha">Centro derecha</option>
                <option value="derecha">Derecha</option>
                <option value="sin_definir">Sin definir</option>
              </select>
            </div>
            <div class="help">Si no estás seguro, elige “Sin definir”.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Rango de edad *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-hourglass-half"></i></span>
              <select class="form-select" id="rango_edad" name="rango_edad" required>
                <option value="">Selecciona tu grupo</option>
                <option value="18-25">18-25</option>
                <option value="26-35">26-35</option>
                <option value="36-45">36-45</option>
                <option value="46-55">46-55</option>
                <option value="56-65">56-65</option>
                <option value="66+">66+</option>
              </select>
            </div>
            <div class="help">Solo para análisis estadístico.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Nivel socioeconómico *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-wallet"></i></span>
              <select class="form-select" id="nivel_ingresos" name="nivel_ingresos" required>
                <option value="">Selecciona un nivel</option>
                <option value="menos_1_salario">Menos de 1 salario</option>
                <option value="1-2_salarios">1-2 salarios</option>
                <option value="3-5_salarios">3-5 salarios</option>
                <option value="6-10_salarios">6-10 salarios</option>
                <option value="mas_10_salarios">Más de 10 salarios</option>
              </select>
            </div>
            <div class="help">Esto es aproximado (no exacto).</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Género *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-venus-mars"></i></span>
              <select class="form-select" id="genero" name="genero" required>
                <option value="">Selecciona</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
                <option value="otro">Otro</option>
                <option value="prefiero_no_decir">Prefiero no decir</option>
              </select>
            </div>
            <div class="help">Tu privacidad es importante.</div>
          </div>
        </div>
      </div>

      <!-- 4) Educación y ocupación -->
      <div class="section">
        <div class="section-title">
          <h5><i class="fa-solid fa-briefcase me-2"></i> Paso 4: Educación y ocupación</h5>
          <small>Nos ayuda a entender mejor el contexto</small>
        </div>
        <div class="divider"></div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label">Nivel educativo</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-graduation-cap"></i></span>
              <select class="form-select" id="nivel_educacion" name="nivel_educacion">
                <option value="">Selecciona (opcional)</option>
                <option value="primaria_incompleta">Primaria incompleta</option>
                <option value="primaria_completa">Primaria completa</option>
                <option value="secundaria_incompleta">Secundaria incompleta</option>
                <option value="secundaria_completa">Secundaria completa</option>
                <option value="tecnico">Técnico</option>
                <option value="tecnologo">Tecnólogo</option>
                <option value="universitario_incompleto">Universitario incompleto</option>
                <option value="universitario_completo">Universitario completo</option>
                <option value="posgrado">Posgrado</option>
              </select>
            </div>
            <div class="help">Si no deseas indicarlo, déjalo vacío.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Ocupación *</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-briefcase"></i></span>
              <select class="form-select" id="ocupacion" name="ocupacion" required>
                <option value="">Selecciona</option>
                 <option value="Estudiante">Estudiante</option>
                <option value="Empleado">Empleado</option>          
                <option value="Empresario">Empresario</option>
                <option value="Comerciante">Comerciante</option>
                <option value="Independiente">Independiente</option>
              </select>
            </div>
            <div class="help">Elige la que mejor te describa hoy.</div>
          </div>
        </div>
      </div>

      <!-- Política -->
      <div class="section">
        <div class="section-title">
          <h5><i class="fa-solid fa-shield-halved me-2"></i> Privacidad</h5>
          <small>Tu información se maneja con cuidado</small>
        </div>
        <div class="divider"></div>

        <div class="row g-3 mt-2">
          <div class="col-12">
            <div class="d-flex align-items-start gap-2">
              <input class="form-check-input mt-1" style="width:20px;height:20px" type="checkbox" id="politica" required>
              <label class="form-check-label" for="politica" style="font-weight:700;">
                Acepto la
                <a href="politica.php" target="_blank" class="fw-bold" style="color:var(--brand); text-decoration:none;">
                  política de privacidad
                </a>
                y autorizo el tratamiento de datos.
              </label>
            </div>
            <div class="help ms-4">Sin esta aceptación no podemos completar el registro.</div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="actions">
        <div class="left">
          <i class="fa-solid fa-circle-info"></i>
          <span>¿Dudas? Completa los campos con * y luego guarda.</span>
        </div>

        <div class="d-flex gap-2">
          <button type="button" onclick="VOTANTES.emptyCells();" class="btn btn-soft px-4">
            <i class="fa-solid fa-xmark me-1"></i> Limpiar
          </button>

          <button type="button" class="btn btn-brand px-4" onclick="VOTANTES.validateData();">
            <i class="fa-solid fa-check me-1"></i> Crear mi cuenta
          </button>
        </div>
      </div>

    </form>

  </div>
</div>
<!-- MODAL LOGIN (colores iguales al navbar) -->
<div class="hero">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div>
     
           <div class="logo-wrap">
            <img 
              src="assets/img/admin/estadistica3.png" 
              alt="Estadística 360"
              class="logo-estadistica" >
          </div>
  
           
            <h5 class="blanco"><i class="fa fa-unlock-alt me-2 blanco"></i>Inicia sesión</h5>
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
<?php include './admin/include/footer.php'; ?>

<!-- Libs -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>

<!-- ✅ Evité duplicados -->
<script src="admin/js/lib/util.js"></script>
<script type="text/javascript" src="./admin/js/lib/data-md5.js"></script>

<script src="js/main.js"></script>
<script src="admin/js/departamentoDama.js"></script>
<script src="admin/js/votantes.js"></script>

<script>
  // ✅ Config por defecto: set departamento y carga municipios
  const departamento = $("#departamentoConfiguracionInput").val();
  if (departamento) {
    $("#tbl_departamento_id").val(departamento);
    DEPARTAMENTO.getMunicipios();
  }

  // Si cambian departamento manualmente, carga municipios también (por si tu js no lo hace)
  $("#tbl_departamento_id").on("change", function(){
    if (typeof DEPARTAMENTO !== "undefined" && typeof DEPARTAMENTO.getMunicipios === "function") {
      DEPARTAMENTO.getMunicipios();
    }
  });
</script>

<!-- Navbar scroll shadow + body margin-top por fixed-top -->
<script>
  document.addEventListener("scroll", function() {
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    if (window.scrollY > 10) nav.classList.add("scrolled");
    else nav.classList.remove("scrolled");
  });

  function aplicarMargenNavbar() {
    const nav = document.getElementById("mainNavbar");
    if (!nav) return;
    const altura = nav.offsetHeight;
    document.body.style.marginTop = altura + "px";
  }

  document.addEventListener("DOMContentLoaded", aplicarMargenNavbar);
  window.addEventListener("resize", aplicarMargenNavbar);
</script>

</body>
</html>
