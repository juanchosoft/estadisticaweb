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

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    :root{
      --primary:#132b52;
      --primary2:#0b1a33;
      --sky:#00c2ff;

      --bg:#ffffff;
      --soft:#f6f8fc;
      --ink:#0f172a;
      --muted:#64748b;
      --line: rgba(15,23,42,.10);

      --radius-xl: 26px;
      --radius-lg: 20px;
      --radius-md: 16px;

      --shadow-strong: 0 34px 120px rgba(2, 6, 23, .14);
      --shadow-mid: 0 18px 60px rgba(2, 6, 23, .10);
    }

    *{ box-sizing:border-box; }
    html, body{ height:100%; }
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color: var(--ink);
      background:
        radial-gradient(1200px 600px at 18% -10%, rgba(19,43,82,.16), transparent 60%),
        radial-gradient(900px 450px at 90% 10%, rgba(0,194,255,.10), transparent 55%),
        linear-gradient(#fff, #fff);
      overflow-x:hidden;
    }

    /* Navbar (si tu head.php ya lo trae, esto lo mejora sin romperlo) */
    #mainNavbar{
      border-bottom: 1px solid rgba(255,255,255,.12);
      background: linear-gradient(135deg, var(--primary), var(--primary2)) !important;
      box-shadow: 0 14px 40px rgba(2,6,23,.18);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      position: sticky;
      top: 0;
      z-index: 50;
    }
    #mainNavbar .nav-link{ color:#fff !important; font-weight:700; opacity:.95; }
    #mainNavbar .nav-link:hover{ opacity:1; }

    /* Page container */
    .wrap{
      max-width: 1040px;
      margin: 0 auto;
      padding: 18px 14px 70px;
      padding-bottom: calc(70px + env(safe-area-inset-bottom));
    }
    @media (max-width: 520px){
      .wrap{ padding: 14px 10px 60px; }
    }

    /* Big card */
    .card-pro{
      background: rgba(255,255,255,.88);
      border: 1px solid var(--line);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-strong);
      overflow: hidden;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }

    /* Hero */
    .hero{
      padding: 16px 14px 14px;
      background:
        radial-gradient(900px 260px at 12% 0%, rgba(19,43,82,.18), transparent 60%),
        radial-gradient(700px 240px at 90% 10%, rgba(0,194,255,.12), transparent 60%),
        linear-gradient(135deg, rgba(19,43,82,.06), rgba(0,194,255,.05));
      border-bottom: 1px solid rgba(15,23,42,.08);
    }

    .hero-top{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
      flex-wrap: wrap;
    }

    .pill{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding: 9px 12px;
      border-radius: 999px;
      background: rgba(19,43,82,.08);
      border: 1px solid rgba(19,43,82,.14);
      color: var(--primary);
      font-weight: 900;
      font-size: 13px;
    }

    .title{
      margin: 10px 0 6px;
      font-size: clamp(22px, 5.2vw, 34px);
      line-height: 1.08;
      letter-spacing: -0.03em;
      font-weight: 950;
    }

    .subtitle{
      margin:0;
      color: rgba(71,85,105,.98);
      font-weight: 650;
      font-size: clamp(14px, 3.8vw, 16px);
      max-width: 65ch;
    }

    /* ===========================
       ===== LOGIN (CTA) =====
       =========================== */
    /* ===== LOGIN START ===== */
    .login-box{
      width:100%;
      display:flex;
      justify-content:flex-end;
      align-items:flex-start;
      position: relative;
      z-index: 20;
    }

    .login-cta{
      text-decoration: none !important;
      user-select:none;
      -webkit-tap-highlight-color: transparent;

      display:flex !important;
      align-items:center !important;
      gap: 10px !important;

      padding: 12px 14px !important;
      border-radius: 18px !important;

      background: linear-gradient(135deg, #021b5a, #0B3EDC) !important;
      color:#fff !important;

      border: 1px solid rgba(255,255,255,.22) !important;
      box-shadow: 0 18px 55px rgba(11,62,220,.32) !important;

      position: relative;
      overflow:hidden;
      transform: translateZ(0);
      transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
      min-width: 260px;
      max-width: 380px;
    }

    .login-cta:hover{
      transform: translateY(-2px);
      box-shadow: 0 26px 80px rgba(11,62,220,.40) !important;
      filter: saturate(1.12);
    }

    .login-cta::after{
      content:"";
      position:absolute;
      inset:-2px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.28), transparent);
      transform: translateX(-130%) skewX(-18deg);
      animation: loginShine 2.6s ease-in-out infinite;
      pointer-events:none;
      opacity: .95;
    }
    @keyframes loginShine{
      0%{ transform: translateX(-130%) skewX(-18deg); opacity:0; }
      18%{ opacity:1; }
      45%{ transform: translateX(230%) skewX(-18deg); opacity:0; }
      100%{ opacity:0; }
    }

    .login-cta-ic{
      width: 42px; height: 42px;
      border-radius: 14px;
      display:grid;
      place-items:center;
      background: rgba(255,255,255,.16);
      box-shadow: inset 0 0 0 1px rgba(255,255,255,.18);
      flex: 0 0 auto;
    }
    .login-cta-ic i{ color:#fff !important; font-size: 16px; }

    .login-cta-txt{
      display:flex;
      flex-direction:column;
      line-height: 1.05;
      gap: 4px;
      flex: 1 1 auto;
      min-width: 0;
    }
    .login-cta-txt b{
      color:#fff !important;
      font-weight: 950 !important;
      font-size: 14px;
      letter-spacing: .1px;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
    }
    .login-cta-txt small{
      color: rgba(255,255,255,.88) !important;
      font-weight: 800;
      font-size: 12px;
    }

    .login-cta-go{
      width: 38px; height: 38px;
      border-radius: 14px;
      display:grid;
      place-items:center;
      background: rgba(255,255,255,.14);
      box-shadow: inset 0 0 0 1px rgba(255,255,255,.16);
      flex: 0 0 auto;
    }
    .login-cta-go i{ color:#fff !important; }

    @media (max-width: 520px){
      .login-box{ justify-content: stretch; }
      .login-cta{
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
        padding: 12px !important;
        border-radius: 20px !important;
      }
    }
    /* ===== LOGIN END ===== */

    /* Steps */
    .steps{
      display:grid;
      grid-template-columns: 1fr;
      gap: 10px;
      margin-top: 12px;
    }
    .step{
      display:flex;
      gap: 12px;
      align-items:flex-start;
      padding: 12px 12px;
      border-radius: var(--radius-lg);
      background: rgba(255,255,255,.90);
      border: 1px solid rgba(15,23,42,.08);
      box-shadow: 0 12px 34px rgba(2,6,23,.06);
    }
    .step .n{
      width: 34px; height: 34px;
      border-radius: 14px;
      display:grid;
      place-items:center;
      font-weight: 950;
      color: var(--primary);
      background: rgba(19,43,82,.10);
      flex: 0 0 auto;
    }
    .step b{ display:block; font-weight: 950; font-size: 14px; }
    .step span{ display:block; color: rgba(71,85,105,.98); font-weight: 650; font-size: 13px; margin-top: 2px; }

    @media (min-width: 900px){
      .hero{ padding: 22px 24px 20px; }
      .steps{ grid-template-columns: 1fr 1fr; }
    }

    /* Sections */
    .section{ padding: 18px 14px; }
    @media (min-width: 900px){
      .section{ padding: 18px 24px; }
    }

    .sec-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 10px;
    }
    .sec-head h3{
      margin:0;
      font-weight: 950;
      font-size: 16px;
      letter-spacing: -.01em;
      color: var(--primary);
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .sec-head small{
      color: rgba(71,85,105,.98);
      font-weight: 700;
    }

    /* Inputs - accesibles */
    .form-label{
      font-weight: 900;
      color: #111827;
      font-size: 14px;
      margin-bottom: 6px;
    }
    .help{
      margin-top: 6px;
      color: rgba(71,85,105,.98);
      font-weight: 650;
      font-size: 13px;
    }

    .input-wrap{ position: relative; }

    .form-control, .form-select{
      height: 56px;
      border-radius: 16px;
      border: 1px solid rgba(15,23,42,.14);
      background: #fff;
      padding-left: 48px;
      padding-right: 14px;
      font-size: 16px;
      font-weight: 700;
      transition: border-color .14s ease, box-shadow .14s ease, transform .14s ease;
    }
    .form-select{ padding-left: 48px; }

    .form-control:focus, .form-select:focus{
      border-color: rgba(19,43,82,.55);
      box-shadow: 0 0 0 6px rgba(0,194,255,.10);
      transform: translateY(-1px);
    }

    .input-ic{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(100,116,139,.95);
      pointer-events:none;
      font-size: 16px;
    }

    .req{ color: #ef4444; font-weight: 950; margin-left: 4px; }

    /* Password field with eye (registro) */
    .pw-wrap{ position: relative; }
    .pw-eye{
      position:absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      border: 0;
      background: rgba(15,23,42,.06);
      width: 44px;
      height: 44px;
      border-radius: 14px;
      color: rgba(100,116,139,.95);
      cursor:pointer;
      transition: transform .12s ease, background .12s ease, color .12s ease;
      display:grid;
      place-items:center;
    }
    .pw-eye:hover{
      background: rgba(0,194,255,.10);
      color: rgba(19,43,82,.95);
      transform: translateY(-50%) scale(1.03);
    }

    /* Actions */
    .actions{
      padding: 16px;
      border-top: 1px solid rgba(15,23,42,.08);
      background: rgba(248,250,252,.75);
      display:grid;
      gap: 10px;
    }
    @media (min-width: 900px){
      .actions{
        padding: 18px 24px;
        grid-template-columns: 1fr auto;
        align-items:center;
      }
    }

    .hint{
      display:flex;
      gap: 10px;
      align-items:flex-start;
      color: rgba(71,85,105,.98);
      font-weight: 750;
      font-size: 13px;
    }
    .hint i{
      margin-top: 2px;
      color: rgba(19,43,82,.85);
    }

    .btn-clear{
      height: 54px;
      border-radius: 16px;
      border: 1px solid rgba(15,23,42,.12);
      background: #eef2f7;
      font-weight: 950;
    }

    /* CTA Crear cuenta */
    .btn-create{
      height: 58px;
      width: 100%;
      border-radius: 18px;
      border: 0;
      color:#fff;
      font-weight: 950;
      font-size: 16px;
      letter-spacing: .2px;
      background: linear-gradient(115deg, #2e58a8, #00c2ff, #7c3aed, #fb7185, #2e58a8);
      background-size: 340% 340%;
      animation: ctaGlow 3.9s ease infinite, ctaBounce 2.8s ease-in-out infinite;
      box-shadow: 0 30px 120px rgba(19,43,82,.34), 0 0 0 12px rgba(0,194,255,.10);
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap: 10px;
      transition: transform .14s ease, box-shadow .14s ease, filter .14s ease;
    }
    @keyframes ctaGlow{
      0%{ background-position: 0% 50%; }
      50%{ background-position: 100% 50%; }
      100%{ background-position: 0% 50%; }
    }
    @keyframes ctaBounce{
      0%,100%{ transform: translateY(0); }
      50%{ transform: translateY(-2px); }
    }
    .btn-create:hover{
      transform: translateY(-3px) scale(1.01);
      box-shadow: 0 38px 150px rgba(19,43,82,.42), 0 0 0 16px rgba(124,58,237,.12);
      filter: saturate(1.10);
      color:#fff;
    }

    .btn-row{
      display:grid;
      grid-template-columns: 1fr;
      gap: 10px;
    }
    @media (min-width: 520px){
      .btn-row{
        grid-template-columns: auto 1fr;
        align-items:center;
      }
      .btn-clear{ width: 170px; }
    }

    /* Privacy checkbox big */
    .privacy{
      display:flex;
      gap: 12px;
      align-items:flex-start;
      padding: 12px 12px;
      border-radius: 18px;
      border: 1px dashed rgba(19,43,82,.22);
      background: rgba(255,255,255,.78);
    }
    .privacy input{
      width: 22px;
      height: 22px;
      margin-top: 2px;
    }
    .privacy label{
      font-weight: 800;
      color: rgba(15,23,42,.92);
      line-height: 1.35;
      font-size: 14px;
      margin: 0;
    }
    .privacy a{
      color: var(--primary);
      font-weight: 950;
      text-decoration: none;
    }
    .privacy a:hover{ text-decoration: underline; }

    /* Ajuste fino móvil */
    @media (max-width: 520px){
      .hero{ padding: 14px 12px 12px; }
      .section{ padding: 16px 12px; }
      .actions{ padding: 14px 12px; }
      .step{ padding: 11px 11px; }
    }
  </style>
</head>

<body>
<?php include './admin/include/loading.php'; ?>
<?php include './admin/include/menu_registro.php'; ?>

<div class="wrap">
  <div class="card-pro">

    <!-- HERO -->
    <div class="hero">
      <div class="hero-top">
        <div>
          <div class="pill"><i class="fa-solid fa-user-plus"></i> Crear cuenta</div>
          <h1 class="title">Regístrate para votar en la encuesta</h1>
          <p class="subtitle">
            Es fácil. Solo llena lo que dice <b>Obligatorio</b> y pulsa <b>Crear mi cuenta</b>.
            Diseñado para que sea cómodo en celular.
          </p>
        </div>

        <!-- ===== LOGIN START (CTA abre modal de login.php) ===== -->
        <div class="login-box">
          <a href="#" class="login-cta" id="btnOpenLogin" role="button" aria-label="Ya tengo una cuenta, iniciar sesión" data-bs-toggle="modal" data-bs-target="#loginModal">
            <span class="login-cta-ic"><i class="fa-solid fa-right-to-bracket"></i></span>
            <span class="login-cta-txt">
              <b>Ya tengo una cuenta</b>
              <small>Entrar para votar</small>
            </span>
            <span class="login-cta-go"><i class="fa-solid fa-arrow-right"></i></span>
          </a>
        </div>
        <!-- ===== LOGIN END ===== -->

      </div>

      <div class="steps">
        <div class="step">
          <div class="n">1</div>
          <div>
            <b>Escribe tu correo y usuario</b>
            <span>Te servirá para ingresar después.</span>
          </div>
        </div>
        <div class="step">
          <div class="n">2</div>
          <div>
            <b>Selecciona tu ubicación</b>
            <span>Departamento y municipio (obligatorio).</span>
          </div>
        </div>
      </div>
    </div>

    <form id="formvotantes" class="m-0">
      <input type="hidden" name="op" id="op" />
      <input type="hidden" name="idVotantes" id="idVotantes" />
      <input type="hidden" id="estado" name="estado" value="activo">

      <!-- PASO 1 -->
      <div class="section">
        <div class="sec-head">
          <h3><i class="fa-solid fa-id-card"></i> Paso 1 • Datos de acceso</h3>
          <small>Obligatorio</small>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Correo electrónico <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-envelope input-ic"></i>
              <input type="email" class="form-control" id="email" name="email" required
                     placeholder="Ej: correo@dominio.com"
                     onblur="VOTANTES.checkAvailability(this)">
            </div>
            <div class="help">Te ayuda a recuperar acceso si lo necesitas.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Usuario <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-user-tag input-ic"></i>
              <input type="text" class="form-control" id="username" name="username" required
                     placeholder="Ej: juanperez"
                     onblur="VOTANTES.checkAvailability(this)">
            </div>
            <div class="help">Sin espacios. Debe ser único.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Contraseña <span class="req">*</span></label>
            <div class="input-wrap pw-wrap">
              <i class="fa-solid fa-lock input-ic"></i>
              <input type="password" id="password" name="password" class="form-control" required
                     placeholder="Crea una contraseña">
              <button type="button" class="pw-eye" id="btnTogglePw" aria-label="Ver contraseña" aria-pressed="false">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
            <div class="help">Pulsa el ojo para ver lo que escribes.</div>
          </div>
        </div>
      </div>

      <!-- PASO 2 -->
      <div class="section">
        <div class="sec-head">
          <h3><i class="fa-solid fa-location-dot"></i> Paso 2 • Ubicación</h3>
          <small>Obligatorio</small>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Departamento <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-map input-ic"></i>
              <select id="tbl_departamento_id" name="tbl_departamento_id" class="form-select" required>
                <option value="">Selecciona tu departamento</option>
                <?= $optionDep ?>
              </select>
            </div>
            <div class="help">Primero elige el departamento.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Municipio <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-location-crosshairs input-ic"></i>
              <select id="tbl_municipio_id" name="tbl_municipio_id" class="form-select" required>
                <option value="">Primero elige un departamento</option>
              </select>
            </div>
            <div class="help">Se carga automáticamente.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Barrio <span style="color:rgba(71,85,105,.9); font-weight:800;">(Opcional)</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-house input-ic"></i>
              <input type="text" class="form-control" id="barrio" name="barrio" placeholder="Ej: La Esperanza">
            </div>
            <div class="help">Si no lo sabes, déjalo en blanco.</div>
          </div>
        </div>
      </div>

      <!-- PASO 3 -->
      <div class="section">
        <div class="sec-head">
          <h3><i class="fa-solid fa-chart-pie"></i> Paso 3 • Perfil (estadísticas)</h3>
          <small>Obligatorio</small>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Ideología política <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-scale-balanced input-ic"></i>
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

          <div class="col-12 col-md-6">
            <label class="form-label">Rango de edad <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-hourglass-half input-ic"></i>
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
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Nivel socioeconómico <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-wallet input-ic"></i>
              <select class="form-select" id="nivel_ingresos" name="nivel_ingresos" required>
                <option value="">Selecciona un nivel</option>
                <option value="menos_1_salario">Menos de 1 salario</option>
                <option value="1-2_salarios">1-2 salarios</option>
                <option value="3-5_salarios">3-5 salarios</option>
                <option value="6-10_salarios">6-10 salarios</option>
                <option value="mas_10_salarios">Más de 10 salarios</option>
              </select>
            </div>
            <div class="help">Aproximado (no exacto).</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Género <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-venus-mars input-ic"></i>
              <select class="form-select" id="genero" name="genero" required>
                <option value="">Selecciona</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
                <option value="otro">Otro</option>
                <option value="prefiero_no_decir">Prefiero no decir</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- PASO 4 -->
      <div class="section">
        <div class="sec-head">
          <h3><i class="fa-solid fa-briefcase"></i> Paso 4 • Educación y ocupación</h3>
          <small>Ocupación obligatoria</small>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Nivel educativo <span style="color:rgba(71,85,105,.9); font-weight:800;">(Opcional)</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-graduation-cap input-ic"></i>
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
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Ocupación <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-briefcase input-ic"></i>
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

      <!-- PRIVACIDAD -->
      <div class="section">
        <div class="sec-head">
          <h3><i class="fa-solid fa-shield-halved"></i> Privacidad</h3>
          <small>Obligatorio</small>
        </div>

        <div class="privacy">
          <input class="form-check-input" type="checkbox" id="politica" required>
          <label for="politica">
            Acepto la
            <a href="politica.php" target="_blank">política de privacidad</a>
            y autorizo el tratamiento de datos.
            <div class="help" style="margin-top:6px;">Sin esta aceptación no podemos completar el registro.</div>
          </label>
        </div>
      </div>

      <!-- ACTIONS -->
      <div class="actions">
        <div class="hint">
          <i class="fa-solid fa-circle-info"></i>
          <div>
            <div style="font-weight:950; color:rgba(15,23,42,.92);">Consejo rápido</div>
            <div>Llena los campos con <b>*</b> y luego pulsa <b>Crear mi cuenta</b>.</div>
          </div>
        </div>

        <div class="btn-row">
          <button type="button" onclick="VOTANTES.emptyCells();" class="btn btn-clear">
            <i class="fa-solid fa-eraser me-2"></i>Limpiar
          </button>

          <button type="button" class="btn-create" onclick="VOTANTES.validateData();">
            <i class="fa-solid fa-user-check"></i>
            Crear mi cuenta
          </button>
        </div>
      </div>

    </form>
  </div>
</div>

<?php include './admin/include/footer.php'; ?>

<!-- Modal Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px; border:0; box-shadow:0 44px 160px rgba(2,6,23,.35);">
      <div class="modal-header" style="border:0; padding:18px 18px 10px;">
        <div style="display:flex; gap:12px; align-items:center;">
          <div style="width:54px;height:54px;border-radius:18px;background:#fff;border:1px solid rgba(15,23,42,.10);display:grid;place-items:center;">
            <img src="<?= $logo_configuracion ?? 'assets/img/admin/estadistica3.png' ?>" alt="Logo" style="width:78%;height:78%;object-fit:contain;">
          </div>
          <div>
            <b style="font-weight:950;color:#0f172a;font-size:18px;">Iniciar sesiónrrrrrr</b><br>
            <small style="font-weight:750;color:rgba(71,85,105,.96);">Accede para continuar</small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" style="padding:6px 18px 16px;">
        <div style="border:1px solid rgba(15,23,42,.10);background:rgba(255,255,255,.84);border-radius:18px;padding:14px;">
          <form id="formLoginVotantes" autocomplete="on">
            <div style="margin-bottom:10px;">
              <div style="font-weight:900;font-size:13px;color:#111827;margin-bottom:6px;">Correo o Usuario</div>
              <input type="text" class="form-control" id="login_user" name="login_user" placeholder="Escribe tu usuario o correo" required style="height:56px;border-radius:16px;">
            </div>
            <div style="margin-bottom:10px;">
              <div style="font-weight:900;font-size:13px;color:#111827;margin-bottom:6px;">Contraseña</div>
              <input type="password" class="form-control" id="login_password" name="login_password" placeholder="Escribe tu contraseña" required style="height:56px;border-radius:16px;">
            </div>
            <button type="button" class="btn btn-primary w-100" id="btnLoginSubmit" style="height:56px;border-radius:16px;font-weight:950;background:linear-gradient(135deg,#021b5a,#0B3EDC);border:0;">
              <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Entrar
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Libs -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="admin/js/lib/util.js"></script>
<script type="text/javascript" src="./admin/js/lib/data-md5.js"></script>

<script src="js/main.js"></script>
<script src="admin/js/departamentoDama.js"></script>
<script src="admin/js/votantes.js"></script>

<script>
  // ==========================
  // ✅ MUNICIPIOS AUTOLOAD
  // ==========================
  const departamento = $("#departamentoConfiguracionInput").val();
  if (departamento) {
    $("#tbl_departamento_id").val(departamento);
    if (typeof DEPARTAMENTO !== "undefined" && typeof DEPARTAMENTO.getMunicipios === "function") {
      DEPARTAMENTO.getMunicipios();
    }
  }
  $("#tbl_departamento_id").on("change", function(){
    if (typeof DEPARTAMENTO !== "undefined" && typeof DEPARTAMENTO.getMunicipios === "function") {
      DEPARTAMENTO.getMunicipios();
    }
  });

  // ==========================
  // 👁️ Toggle password (registro)
  // ==========================
  (function(){
    const btn = document.getElementById('btnTogglePw');
    const input = document.getElementById('password');
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

  // ==========================
  // ===== LOGIN START (OPEN MODAL DESDE login.php) =====
  // Carga el modal remoto: login.php?only_modal=1
  // y lo abre con Bootstrap 5.0 (sin getOrCreateInstance)
  // ==========================
  (function(){
    function showLoginModal(){
      const el = document.getElementById('loginModal');
      if(!el || typeof bootstrap === "undefined" || !bootstrap.Modal) return;

      let instance = null;
      if (typeof bootstrap.Modal.getInstance === "function") {
        instance = bootstrap.Modal.getInstance(el);
      }
      if (!instance) {
        instance = new bootstrap.Modal(el, { backdrop:'static', keyboard:false });
      }
      instance.show();

      el.addEventListener('shown.bs.modal', function(){
        const u = document.getElementById('login_user');
        if(u) u.focus();
      }, { once:true });
    }

    async function loadModalIfNeeded(){
      // Si ya existe en DOM, solo abre
      if (document.getElementById('loginModal')) {
        showLoginModal();
        return;
      }

      const res = await fetch('modal_login.php?only_modal=1', { credentials: 'same-origin' });
      const html = await res.text();

      // Inserta el modal (solo una vez)
      const wrap = document.createElement('div');
      wrap.id = 'loginModalRemoteWrap';
      wrap.innerHTML = html;
      document.body.appendChild(wrap);

      // Ejecutar scripts que vienen en el HTML cargado
      const scripts = wrap.querySelectorAll('script');
      scripts.forEach(function(oldScript){
        const newScript = document.createElement('script');
        if(oldScript.src){
          newScript.src = oldScript.src;
        } else {
          newScript.textContent = oldScript.textContent;
        }
        document.body.appendChild(newScript);
      });

      showLoginModal();
    }

    document.addEventListener('click', function(e){
      const btn = e.target.closest('#btnOpenLogin');
      if(!btn) return;
      e.preventDefault();
      loadModalIfNeeded().catch(console.error);
    });
  })();
  // ===== LOGIN END =====

  // ===== LOGIN SUBMIT =====
  document.getElementById('btnLoginSubmit').addEventListener('click', async function(){
    const nickname = document.getElementById('login_user').value.trim();
    const hashpass = document.getElementById('login_password').value.trim();

    if(!nickname || !hashpass){
      UTIL.mostrarMensajeError('Por favor completa todos los campos.');
      return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Validando...';

    const formData = new FormData();
    formData.append('nickname', nickname);
    formData.append('hashpass', hashpass);

    try {
      const res = await fetch('login_process.php', { method: 'POST', body: formData });
      const data = await res.json();

      if(data.status === 'success'){
        window.location.href = data.redirect;
      } else {
        UTIL.mostrarMensajeError(data.message || 'Error de inicio de sesión.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Entrar';
      }
    } catch(err){
      UTIL.mostrarMensajeError('Error de conexión con el servidor.');
      console.error(err);
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Entrar';
    }
  });

  // Enter key en el form de login
  document.getElementById('formLoginVotantes').addEventListener('keydown', function(e){
    if(e.key === 'Enter'){
      e.preventDefault();
      document.getElementById('btnLoginSubmit').click();
    }
  });
</script>

</body>
</html>
