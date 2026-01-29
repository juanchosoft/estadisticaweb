<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/include/generic_info_configuracion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recuperar contraseña</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    :root{
      --primary:#132b52;
      --sky:#0B3EDC;
      --ink:#0f172a;
      --muted:#64748b;
      --radius:22px;
      --shadow:0 44px 160px rgba(2,6,23,.20);
    }
    body{
      background: radial-gradient(1100px 600px at 10% -10%, rgba(11,62,220,.16), transparent 65%),
                  radial-gradient(900px 540px at 110% 0%, rgba(2,27,90,.18), transparent 60%),
                  #f6f8fc;
      min-height:100vh;
    }
    .wrap{
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px 14px;
    }
    .cardx{
      width:100%;
      max-width:540px;
      border:0;
      border-radius:var(--radius);
      overflow:hidden;
      box-shadow:var(--shadow);
      background:#fff;
    }
    .headx{
      padding:18px 18px 14px;
      color:#fff;
      background:linear-gradient(135deg,#021b5a,#0B3EDC);
    }
    .headx .t1{ font-weight:950; font-size:18px; margin:0; }
    .headx .t2{ font-weight:750; font-size:13px; opacity:.92; margin:4px 0 0; }
    .bodyx{ padding:16px 18px 18px; }
    .lbl{ font-weight:950; font-size:13px; color:#111827; margin-bottom:7px; }
    .inp{
      height:54px; width:100%;
      border-radius:16px;
      border:1px solid rgba(15,23,42,.14);
      background:#fff;
      padding:0 14px;
      font-size:16px;
      font-weight:800;
      outline:none;
    }
    .btnx{
      height:54px; width:100%;
      border-radius:16px;
      border:0;
      color:#fff;
      font-weight:950;
      background:linear-gradient(135deg,#021b5a,#0B3EDC);
      display:flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      cursor:pointer;
    }
    .muted{ color:var(--muted); font-weight:750; font-size:12px; margin-top:10px; }
    .row2{ display:flex; gap:10px; margin-top:12px; flex-wrap:wrap; }
    .btnlite{
      height:48px; flex:1; min-width:200px;
      border-radius:14px;
      border:1px solid rgba(15,23,42,.12);
      background:#fff;
      font-weight:950;
      color:var(--ink);
      cursor:pointer;
    }
    .alertx{
      display:none;
      margin-top:12px;
      padding:10px 12px;
      border-radius:14px;
      font-weight:850;
      font-size:13px;
    }
    .alertx.ok{ background:rgba(16,185,129,.12); color:#065f46; border:1px solid rgba(16,185,129,.25); }
    .alertx.bad{ background:rgba(239,68,68,.10); color:#7f1d1d; border:1px solid rgba(239,68,68,.25); }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="cardx">
      <div class="headx">
        <p class="t1"><i class="fa-solid fa-unlock-keyhole"></i> Recuperar contraseña</p>
        <p class="t2">Escribe tu <b>correo</b> o tu <b>usuario</b> registrado y te enviamos una contraseña temporal.</p>
      </div>

      <div class="bodyx">
        <form id="formForgot">
          <div>
            <div class="lbl">Correo o Usuario</div>
            <input type="text" id="login" name="login" class="inp" placeholder="ej: usuario@correo.com o username" required>
          </div>

          <div class="muted">
            Nota: si el registro existe, recibirás una contraseña temporal al correo registrado.
          </div>

          <div class="alertx ok" id="msgOk"></div>
          <div class="alertx bad" id="msgBad"></div>

          <div style="margin-top:14px;">
            <button type="submit" class="btnx" id="btnSend">
              <i class="fa-solid fa-paper-plane"></i> Enviar contraseña temporal
            </button>
          </div>

          <div class="row2">
            <button type="button" class="btnlite" onclick="window.location.href='index.php'">
              <i class="fa-solid fa-house"></i> Volver al inicio
            </button>
            <button type="button" class="btnlite" onclick="window.location.href='login.php'">
              <i class="fa-solid fa-arrow-right-to-bracket"></i> Ir a login
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
(() => {
  const form = document.getElementById('formForgot');
  const btn  = document.getElementById('btnSend');
  const ok   = document.getElementById('msgOk');
  const bad  = document.getElementById('msgBad');

  const show = (el, txt) => { el.textContent = txt; el.style.display='block'; };
  const hide = (el) => { el.style.display='none'; };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    hide(ok); hide(bad);

    const login = (document.getElementById('login').value || '').trim();
    if(!login){ show(bad, 'Por favor escribe tu correo o usuario.'); return; }

    btn.disabled = true;
    btn.style.opacity = '0.85';

    try{
      const fd = new FormData();
      fd.append('login', login);

      const res = await fetch('./admin/ajax/auth_forgot_password.php', { method:'POST', body: fd });
      const data = await res.json();

      if(data && data.ok){
        show(ok, data.msg || 'Si existe en el sistema, te enviamos una contraseña temporal.');
        form.reset();
      }else{
        show(bad, (data && data.msg) ? data.msg : 'No fue posible procesar la solicitud.');
      }
    }catch(err){
      show(bad, 'Error de red. Intenta nuevamente.');
    }finally{
      btn.disabled = false;
      btn.style.opacity = '1';
    }
  });
})();
</script>
</body>
</html>
