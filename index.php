<?php
// index.php (Enterprise WOW SaaS)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- =========================================
       Básico
  ========================================== -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="theme-color" content="#132b52" />

  <!-- =========================================
       SEO
  ========================================== -->
  <title>Estadísticas 360 | Encuesta del Momento</title>
  <meta name="description" content="Estadísticas 360 presenta la Encuesta del Momento con resultados claros, visuales e interactivos, listos para analizar y compartir." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://estadisticas360.com/" />

  <!-- =========================================
       Open Graph (WhatsApp / Facebook)
  ========================================== -->
  <meta property="og:locale" content="es_CO" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Estadísticas 360 | Encuesta del Momento" />
  <meta property="og:description" content="Consulta y comparte resultados en tiempo real con visualizaciones claras y profesionales." />
  <meta property="og:url" content="https://estadisticas360.com/" />
  <meta property="og:site_name" content="Estadísticas 360" />
  <meta property="og:image" content="https://estadisticas360.com/assets/img/og/estadisticas360-og.jpg" />
  <meta property="og:image:secure_url" content="https://estadisticas360.com/assets/img/og/estadisticas360-og.jpg" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:alt" content="Estadísticas 360 - Encuesta del Momento" />

  <!-- =========================================
       Twitter Card
  ========================================== -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Estadísticas 360 | Encuesta del Momento" />
  <meta name="twitter:description" content="Resultados de encuestas en tiempo real con visualización profesional." />
  <meta name="twitter:image" content="https://estadisticas360.com/assets/img/og/estadisticas360-og.jpg" />

  <!-- =========================================
       CSS / Fuentes (no se elimina nada)
  ========================================== -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- Favicon -->
  <link rel="icon" href="/assets/img/admin/favicon.ico" />


  <style>
    :root{
      --primary:#132b52;
      --primary-2:#0b1a33;
      --primary-3:#1f3b72;

      --bg:#ffffff;
      --ink:#0f172a;
      --muted:#475569;

      --border: rgba(15,23,42,.10);
      --glass: rgba(255,255,255,.70);

      --radius-2xl: 30px;
      --radius-xl: 24px;
      --radius-lg: 18px;
      --radius-md: 14px;

      --shadow-soft: 0 14px 38px rgba(2, 6, 23, .10);
      --shadow-mid:  0 24px 70px rgba(2, 6, 23, .14);
      --shadow-strong: 0 34px 120px rgba(2, 6, 23, .18);

      --ring: 0 0 0 10px rgba(0,194,255,.10);
    }

    *{ box-sizing:border-box; }
    html,body{ height:100%; }
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: var(--bg);
      color: var(--ink);
      line-height: 1.45;
      overflow-x: hidden;
    }

    /* ===== Fondo enterprise (gradientes + grid) ===== */
    .bg{
      position: fixed;
      inset: 0;
      z-index: -3;
      background:
        radial-gradient(900px 520px at 18% 12%, rgba(19,43,82,.16), transparent 60%),
        radial-gradient(720px 420px at 78% 12%, rgba(46,88,168,.14), transparent 60%),
        radial-gradient(760px 520px at 45% 92%, rgba(124,58,237,.11), transparent 60%),
        linear-gradient(#fff, #fff);
    }
    .grid{
      position: fixed;
      inset: 0;
      z-index: -2;
      pointer-events:none;
      opacity: .08;
      background-image:
        linear-gradient(to right, rgba(15,23,42,.55) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(15,23,42,.55) 1px, transparent 1px);
      background-size: 52px 52px;
      mask-image: radial-gradient(closest-side at 50% 18%, rgba(0,0,0,.92), transparent 72%);
      -webkit-mask-image: radial-gradient(closest-side at 50% 18%, rgba(0,0,0,.92), transparent 72%);
    }

    /* ===== Topbar ===== */
    .topbar{
      background: linear-gradient(135deg, var(--primary), var(--primary-2));
      color:#fff;
      position: sticky;
      top:0;
      z-index:10;
      border-bottom: 1px solid rgba(255,255,255,.12);
      box-shadow: 0 14px 40px rgba(2,6,23,.18);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }
    .topbar .wrap{
      max-width: 1320px;
      margin: 0 auto;
      padding: 14px 18px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 14px;
    }

    .brand{
      display:flex;
      align-items:center;
      gap: 12px;
      min-width: 0;
    }
    .brand img{
      width: 46px;
      height: 46px;
      border-radius: 14px;
      background: rgba(255,255,255,.08);
      padding: 8px;
      box-shadow: 0 12px 26px rgba(0,0,0,.22);
      object-fit: contain;
    }
    .brand .title{
      display:flex;
      flex-direction:column;
      min-width:0;
    }
    .brand .title b{
      font-size: 14px;
      letter-spacing: .2px;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }
    .brand .title span{
      font-size: 12px;
      opacity: .86;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }

    .right{
      display:flex;
      gap: 10px;
      align-items:center;
      flex-wrap: wrap;
      justify-content:flex-end;
    }

    .pill{
      display:inline-flex;
      align-items:center;
      gap:10px;
      font-size: 12px;
      padding: 9px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.16);
      user-select:none;
      white-space:nowrap;
    }
    .dot{
      width:8px;height:8px;border-radius:999px;
      background: #34d399;
      box-shadow: 0 0 0 7px rgba(52,211,153,.14);
    }

    .badge-live{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding: 9px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 900;
      letter-spacing: .02em;
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.16);
      position: relative;
      overflow:hidden;
    }
    .badge-live::after{
      content:"";
      position:absolute;
      inset:-2px;
      background: radial-gradient(140px 60px at 20% 30%, rgba(255,255,255,.22), transparent 55%);
      pointer-events:none;
      opacity:.85;
    }
    .pulse{
      width: 10px; height:10px; border-radius: 999px;
      background: #fb7185;
      box-shadow: 0 0 0 0 rgba(251,113,133,.0);
      animation: livePulse 1.4s ease-in-out infinite;
      position: relative;
      z-index: 1;
    }
    @keyframes livePulse{
      0%{ box-shadow: 0 0 0 0 rgba(251,113,133,.0); }
      50%{ box-shadow: 0 0 0 10px rgba(251,113,133,.18); }
      100%{ box-shadow: 0 0 0 0 rgba(251,113,133,.0); }
    }

    /* ===== Page + Hero ===== */
    .page{
      max-width: 1320px;
      margin: 0 auto;
      padding: 26px 18px 46px;
    }

    .hero{
      display:grid;
      grid-template-columns: 1.2fr .8fr;
      gap: 18px;
      align-items: stretch;
      min-height: calc(100vh - 86px); /* grande en PC */
    }

    /* ===== Card principal WOW ===== */
    .main{
      background: rgba(255,255,255,.78);
      border: 1px solid var(--border);
      border-radius: var(--radius-2xl);
      box-shadow: var(--shadow-strong);
      overflow:hidden;
      position: relative;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);

      transform: translateY(14px);
      opacity: 0;
      animation: enter .62s ease forwards;
    }
    @keyframes enter{ to{ transform: translateY(0); opacity: 1; } }

    .main::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(980px 320px at 16% 0%, rgba(19,43,82,.16), transparent 58%),
        radial-gradient(620px 300px at 92% 12%, rgba(0,194,255,.14), transparent 60%),
        radial-gradient(560px 300px at 60% 74%, rgba(124,58,237,.12), transparent 62%);
      pointer-events:none;
    }

    .main-inner{
      position:relative;
      padding: 38px;
      display:grid;
      grid-template-columns: 1.1fr .9fr;
      gap: 18px;
      align-items: start;
    }

    .kicker{
      display:inline-flex;
      align-items:center;
      gap:10px;
      font-weight: 900;
      font-size: 12px;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: var(--primary);
      background: rgba(19,43,82,.06);
      border: 1px solid rgba(19,43,82,.12);
      padding: 10px 14px;
      border-radius: 999px;
      width: fit-content;
    }

    h1{
      margin: 16px 0 12px;
      font-size: clamp(30px, 3.6vw, 58px);
      line-height: 1.04;
      letter-spacing: -0.05em;
    }
    .lead{
      margin: 0;
      color: var(--muted);
      font-size: clamp(15px, 1.2vw, 18px);
      max-width: 70ch;
    }

    /* Trust bar */
    .trust{
      margin-top: 18px;
      display:flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items:center;
    }
    .trust .t{
      display:inline-flex;
      align-items:center;
      gap: 8px;
      padding: 10px 12px;
      border-radius: 999px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(255,255,255,.85);
      box-shadow: 0 14px 34px rgba(2,6,23,.06);
      font-size: 13px;
      color: #0f172a;
    }

    /* 60s timer */
    .timer{
      margin-top: 18px;
      padding: 14px 16px;
      border-radius: 18px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(255,255,255,.85);
      box-shadow: 0 16px 44px rgba(2,6,23,.08);
      display:flex;
      align-items:center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .timer b{
      color: var(--primary);
      font-size: 13px;
      letter-spacing: -.01em;
      font-weight: 900;
    }
    .bar{
      flex: 1 1 260px;
      height: 12px;
      border-radius: 999px;
      background: rgba(15,23,42,.08);
      overflow:hidden;
      position:relative;
    }
    .bar > span{
      position:absolute;
      inset:0;
      width: 40%;
      border-radius: 999px;
      background: linear-gradient(90deg, #2e58a8, #00c2ff, #7c3aed, #fb7185, #2e58a8);
      background-size: 220% 220%;
      animation: moveBar 2.6s ease-in-out infinite;
      box-shadow: var(--ring);
      opacity: .95;
    }
    @keyframes moveBar{
      0%{ transform: translateX(-35%); background-position: 0% 50%; }
      50%{ transform: translateX(60%); background-position: 100% 50%; }
      100%{ transform: translateX(-35%); background-position: 0% 50%; }
    }
    .timer small{
      color: rgba(71,85,105,.95);
      font-size: 12px;
      font-weight: 600;
    }

    /* CTA Row */
    .cta-row{
      margin-top: 18px;
      display:flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
    }

    /* Los botones (base) */
    .btn{
      appearance:none;
      border:0;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      padding: 16px 18px;
      border-radius: 18px;
      font-weight: 900;
      letter-spacing: .2px;
      text-decoration:none;
      transition: transform .14s ease, box-shadow .14s ease, filter .14s ease, border-color .14s ease;
      user-select:none;
      white-space:nowrap;
      width: fit-content;
      position: relative;
      isolation: isolate;
    }
    .btn:active{ transform: translateY(1px) scale(.995); }

    /* BOTÓN WOW ULTRA */
    .btn-primary{
      color:#fff;
      border: 1px solid rgba(255,255,255,.18);
      overflow:hidden;
      background: linear-gradient(115deg, #2e58a8, #00c2ff, #7c3aed, #fb7185, #2e58a8);
      background-size: 340% 340%;
      animation: gradientMove 3.9s ease infinite, pulseGlow 1.85s ease-in-out infinite, microBounce 2.8s ease-in-out infinite;
      box-shadow:
        0 30px 120px rgba(19,43,82,.40),
        0 0 0 12px rgba(0,194,255,.12);
      padding: 18px 22px;
      border-radius: 20px;
      font-size: 15px;
    }
    @keyframes gradientMove{
      0%{ background-position: 0% 50%; }
      50%{ background-position: 100% 50%; }
      100%{ background-position: 0% 50%; }
    }
    @keyframes pulseGlow{
      0%,100%{ filter: saturate(1.10) brightness(1.03); }
      50%{ filter: saturate(1.40) brightness(1.08); }
    }
    @keyframes microBounce{
      0%,100%{ transform: translateY(0); }
      50%{ transform: translateY(-2px); }
    }
    .btn-primary::after{
      content:"";
      position:absolute;
      top:-70%;
      left:-35%;
      width: 60%;
      height: 240%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.34), transparent);
      transform: rotate(22deg);
      animation: shine 2.7s ease-in-out infinite;
      pointer-events:none;
      z-index: 0;
    }
    @keyframes shine{
      0%{ transform: translateX(-120%) rotate(22deg); opacity:0; }
      18%{ opacity:1; }
      45%{ transform: translateX(300%) rotate(22deg); opacity:0; }
      100%{ opacity:0; }
    }
    .btn-primary::before{
      content:"";
      position:absolute;
      inset:-2px;
      border-radius: 22px;
      background:
        radial-gradient(160px 90px at 25% 25%, rgba(255,255,255,.28), transparent 60%),
        radial-gradient(220px 110px at 85% 70%, rgba(255,255,255,.18), transparent 62%);
      opacity:.9;
      z-index: 0;
      pointer-events:none;
    }
    .btn-primary span,
    .btn-primary svg{ position: relative; z-index: 1; }

    .btn-primary:hover{
      transform: translateY(-4px) scale(1.018);
      box-shadow:
        0 38px 150px rgba(19,43,82,.48),
        0 0 0 16px rgba(124,58,237,.13);
    }

    .btn-ghost{
      background:#fff;
      color: var(--primary);
      border: 1px solid rgba(19,43,82,.24);
      box-shadow: 0 12px 34px rgba(2,6,23,.08);
      font-weight: 900;
    }
    .btn-ghost:hover{
      transform: translateY(-2px);
      border-color: rgba(19,43,82,.40);
      box-shadow: 0 18px 52px rgba(2,6,23,.12);
    }

    .cta-mini{
      margin-top: 10px;
      font-size: 12px;
      color: rgba(71,85,105,.95);
      font-weight: 600;
    }
    .cta-mini b{ color: var(--primary); }

    /* ===== Columna derecha en card principal (bento) ===== */
    .bento{
      display:grid;
      grid-template-columns: 1fr;
      gap: 12px;
    }
    .tile{
      border-radius: 22px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(255,255,255,.88);
      box-shadow: 0 16px 48px rgba(2,6,23,.08);
      padding: 14px;
      position: relative;
      overflow:hidden;
    }
    .tile::before{
      content:"";
      position:absolute;
      inset:-2px;
      background: radial-gradient(260px 140px at 20% 0%, rgba(19,43,82,.12), transparent 62%);
      pointer-events:none;
    }
    .tile > *{ position: relative; }
    .tile h3{
      margin:0 0 8px;
      font-size: 13px;
      color: var(--primary);
      letter-spacing: -.01em;
      font-weight: 900;
      display:flex;
      align-items:center;
      gap: 8px;
    }
    .tile p{
      margin:0;
      font-size: 12px;
      color: rgba(71,85,105,.98);
      font-weight: 600;
    }

    .stats{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      margin-top: 10px;
    }
    .stat{
      border-radius: 18px;
      border: 1px solid rgba(15,23,42,.10);
      background: #fff;
      padding: 12px;
      box-shadow: 0 12px 30px rgba(2,6,23,.06);
    }
    .stat b{
      display:block;
      font-size: 16px;
      letter-spacing: -.02em;
      color: var(--primary);
      font-weight: 900;
    }
    .stat span{
      display:block;
      margin-top: 4px;
      font-size: 12px;
      color: rgba(71,85,105,.95);
      font-weight: 600;
    }

    .preview{
      margin-top: 10px;
      border-radius: 18px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(255,255,255,.92);
      padding: 12px;
      overflow:hidden;
    }
    .sk{
      height: 12px;
      border-radius: 999px;
      background: rgba(15,23,42,.08);
      overflow:hidden;
      position: relative;
      margin-top: 10px;
    }
    .sk:first-child{ margin-top:0; }
    .sk::after{
      content:"";
      position:absolute;
      inset:0;
      width: 45%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.52), transparent);
      transform: translateX(-60%);
      animation: shimmer 1.6s ease-in-out infinite;
    }
    @keyframes shimmer{
      0%{ transform: translateX(-60%); }
      100%{ transform: translateX(220%); }
    }
    .sk.w90{ width: 90%; }
    .sk.w78{ width: 78%; }
    .sk.w84{ width: 84%; }
    .sk.w66{ width: 66%; }

    /* ===== Side card derecha (pasos + nota) ===== */
    .side{
      background: rgba(255,255,255,.78);
      border: 1px solid var(--border);
      border-radius: var(--radius-2xl);
      box-shadow: var(--shadow-mid);
      overflow:hidden;
      position: relative;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);

      transform: translateY(18px);
      opacity: 0;
      animation: enter2 .72s ease .08s forwards;
    }
    @keyframes enter2{ to{ transform: translateY(0); opacity: 1; } }

    .side::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(520px 260px at 20% 0%, rgba(19,43,82,.14), transparent 55%),
        radial-gradient(520px 260px at 90% 30%, rgba(46,88,168,.12), transparent 55%);
      pointer-events:none;
    }

    .side-inner{
      position:relative;
      padding: 22px;
      display:flex;
      flex-direction:column;
      gap: 12px;
    }
    .side h2{
      margin:0;
      font-size: 16px;
      color: var(--primary);
      letter-spacing: -.01em;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      font-weight: 900;
    }
    .tag{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 7px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 900;
      color: #fff;
      background: linear-gradient(135deg, var(--primary), #2e58a8);
      box-shadow: 0 14px 36px rgba(19,43,82,.22);
    }

    .step{
      display:flex;
      gap: 10px;
      align-items:flex-start;
      padding: 12px;
      border-radius: 18px;
      background:#fff;
      border: 1px solid rgba(15,23,42,.08);
      box-shadow: 0 14px 38px rgba(2,6,23,.08);
      transition: transform .12s ease, box-shadow .12s ease;
    }
    .step:hover{
      transform: translateY(-2px);
      box-shadow: 0 20px 60px rgba(2,6,23,.12);
    }
    .badge{
      width: 34px;
      height: 34px;
      border-radius: 14px;
      display:grid;
      place-items:center;
      background: rgba(19,43,82,.10);
      color: var(--primary);
      font-weight: 900;
      flex: 0 0 auto;
    }
    .step b{
      display:block;
      font-size: 13px;
      margin-bottom: 2px;
      color: #0f172a;
      font-weight: 900;
    }
    .step span{
      display:block;
      font-size: 12px;
      color: var(--muted);
      font-weight: 600;
    }

    .note{
      font-size: 12px;
      color: rgba(71,85,105,.96);
      padding: 12px 14px;
      border-radius: 18px;
      border: 1px dashed rgba(19,43,82,.22);
      background: rgba(255,255,255,.70);
      font-weight: 600;
    }

    /* ===== Footer ===== */
    footer{
      max-width: 1320px;
      margin: 0 auto;
      padding: 0 18px 26px;
      color: #64748b;
      font-size: 12px;
      font-weight: 600;
    }

    /* ===== Mini Modal (custom - NO Bootstrap) ===== */
    .mini-modal-overlay{
      position: fixed;
      inset: 0;
      background: rgba(2,6,23,.56);
      display:none;
      align-items:center;
      justify-content:center;
      padding: 18px;
      z-index: 1000;
    }
    .mini-modal{
      width: min(560px, 100%);
      border-radius: 22px;
      background: rgba(255,255,255,.86);
      border: 1px solid rgba(255,255,255,.24);
      box-shadow: 0 34px 120px rgba(2,6,23,.35);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      overflow:hidden;
      transform: translateY(10px) scale(.98);
      opacity: 0;
      transition: all .16s ease;
      position: relative;
    }
    .mini-modal::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(460px 220px at 18% 0%, rgba(19,43,82,.18), transparent 55%),
        radial-gradient(420px 220px at 90% 30%, rgba(0,194,255,.14), transparent 60%);
      pointer-events:none;
    }
    .mini-modal.open{
      transform: translateY(0) scale(1);
      opacity: 1;
    }
    .mini-modal-header{
      position: relative;
      padding: 16px 16px 12px;
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
    }
    .mini-modal-title{
      margin:0;
      font-size: 14px;
      font-weight: 900;
      color: var(--primary);
      letter-spacing: -.01em;
    }
    .mini-modal-sub{
      margin: 6px 0 0;
      font-size: 12px;
      color: rgba(71,85,105,.98);
      font-weight: 600;
      max-width: 54ch;
    }
    .mini-modal-close{
      border:0;
      background: rgba(15,23,42,.06);
      color: var(--primary);
      width: 38px; height: 38px;
      border-radius: 12px;
      cursor:pointer;
      display:grid;
      place-items:center;
      transition: transform .12s ease, background .12s ease;
      position: relative;
      z-index: 2;
    }
    .mini-modal-close:hover{
      transform: translateY(-1px);
      background: rgba(15,23,42,.10);
    }
    .mini-modal-body{
      position: relative;
      padding: 0 16px 14px;
    }
    .mini-modal-box{
      border-radius: 18px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(255,255,255,.86);
      box-shadow: 0 14px 34px rgba(2,6,23,.08);
      padding: 12px;
    }
    .mini-modal-row{
      display:flex;
      gap: 10px;
      align-items:flex-start;
      padding: 10px 8px;
      border-radius: 14px;
    }
    .mini-modal-row + .mini-modal-row{
      border-top: 1px dashed rgba(15,23,42,.10);
    }
    .mini-modal-ic{
      width: 34px; height: 34px;
      border-radius: 14px;
      background: rgba(19,43,82,.10);
      display:grid;
      place-items:center;
      color: var(--primary);
      flex: 0 0 auto;
      font-weight: 900;
    }
    .mini-modal-row b{
      display:block;
      font-size: 13px;
      color: #0f172a;
      font-weight: 900;
    }
    .mini-modal-row span{
      display:block;
      margin-top: 2px;
      font-size: 12px;
      color: rgba(71,85,105,.98);
      font-weight: 600;
    }
    .mini-modal-actions{
      position: relative;
      display:flex;
      gap: 10px;
      flex-wrap: wrap;
      padding: 0 16px 16px;
    }
    .btn-mini-modal{
      flex: 1 1 220px;
      width: auto;
    }
    .btn-outline{
      background: rgba(255,255,255,.92);
      color: var(--primary);
      border: 1px solid rgba(19,43,82,.26);
      box-shadow: 0 12px 34px rgba(2,6,23,.08);
      font-weight: 900;
      padding: 14px 16px;
      border-radius: 18px;
    }
    .btn-outline:hover{
      transform: translateY(-2px);
      border-color: rgba(19,43,82,.40);
      box-shadow: 0 18px 52px rgba(2,6,23,.12);
    }

    /* ===== MAP CARD (Enterprise) ===== */
    .map-card{
      border-radius: 22px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(255,255,255,.86);
      box-shadow: 0 18px 60px rgba(2,6,23,.10);
      overflow: hidden;
      position: relative;
    }

    .map-card::before{
      content:"";
      position:absolute;
      inset:-2px;
      background:
        radial-gradient(320px 180px at 20% 0%, rgba(19,43,82,.16), transparent 60%),
        radial-gradient(320px 180px at 90% 40%, rgba(0,194,255,.12), transparent 62%),
        radial-gradient(320px 180px at 40% 90%, rgba(124,58,237,.10), transparent 60%);
      pointer-events:none;
    }

    .map-head{
      position: relative;
      padding: 12px 14px 10px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      border-bottom: 1px dashed rgba(15,23,42,.10);
      background: rgba(255,255,255,.75);
    }

    .map-head b{
      font-size: 13px;
      color: var(--primary);
      font-weight: 900;
      letter-spacing: -.01em;
    }

    .map-legend{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      font-size: 11px;
      color: rgba(71,85,105,.95);
      font-weight: 700;
    }

    .dot-lg{
      width: 10px;
      height: 10px;
      border-radius: 999px;
      display:inline-block;
      box-shadow: 0 0 0 6px rgba(0,0,0,.03);
    }
    .dot-green{ background:#22c55e; }
    .dot-blue{ background:#3b82f6; }
    .dot-red{ background:#ef4444; }

    .map-body{
      position: relative;
      padding: 14px;
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .map-img{
      width: 100%;
      height: auto;
      max-width: 420px;
      max-height: 360px;
      object-fit: contain;
      display:block;
      filter: drop-shadow(0 18px 34px rgba(2,6,23,.18));
    }

    @media (min-width: 1200px){
      .map-img{
        max-width: 520px;
        max-height: 420px;
      }
    }

    @media (max-width: 520px){
      .map-body{ padding: 12px; }
      .map-img{
        max-width: 100%;
        max-height: 320px;
      }
    }

    /* ===========================
       ===== LOGIN (CTA INLINE) =====
       (Arreglado para que NO rompa la fila)
       =========================== */
    .login-box{
      width: auto;               /* ✅ antes estaba 100% y rompía */
      display:flex;
      align-items:center;
      justify-content:flex-start;
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
      white-space: nowrap;
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

    /* ===== Responsive ===== */
    @media (max-width: 1080px){
      .hero{
        grid-template-columns: 1fr;
        min-height: auto;
      }
      .main-inner{
        grid-template-columns: 1fr;
      }
      .main-inner{ padding: 24px; }
    }
    @media (max-width: 520px){
      .brand img{ width: 42px; height: 42px; }

      /* móvil: todos full width */
      .btn{ width: 100%; }
      .btn-primary{ width: 100%; justify-content:center; }
      .btn-ghost{ width: 100%; }
      .login-box{ width: 100%; }
      .login-cta{
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
      }

      .stats{ grid-template-columns: 1fr; }
      .main-inner{ padding: 18px; }
    }

    @media (prefers-reduced-motion: reduce){
      .main, .side{ animation: none; opacity: 1; transform: none; }
      .btn-primary{ animation: none; }
      .btn-primary::after{ animation: none; }
      .bar > span{ animation: none; width: 60%; }
      .pulse{ animation: none; }
      .login-cta::after{ animation: none; }
    }
/* ===== HEADER MODAL (SaaS PRO) ===== */
.modal.show .modal-header{
  /* base */
  background: linear-gradient(135deg,
    rgba(19, 43, 82, .92),
    rgba(11, 26, 51, .92)
  ) !important;

  /* glass */
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);

  /* profundidad */
  border-bottom: 1px solid rgba(255,255,255,.16) !important;
  box-shadow: 0 10px 24px rgba(2, 6, 23, .22) !important;

  /* look */
  padding: 18px 20px !important;
  border-top-left-radius: 18px;
  border-top-right-radius: 18px;
  position: relative;
  overflow: hidden;
}

/* brillo suave “premium” */
.modal.show .modal-header::after{
  content:"";
  position:absolute;
  inset:-40% -20% auto -20%;
  height:120%;
  background: radial-gradient(420px 220px at 20% 20%, rgba(255,255,255,.22), transparent 60%);
  pointer-events:none;
  opacity:.85;
}


/* textos */
.modal.show .modal-header .modal-title,
.modal.show .modal-header small,
.modal.show .modal-header p,
.modal.show .modal-header span{
  color:#fff !important;
  text-shadow: 0 2px 10px rgba(0,0,0,.22);
}

/* botón cerrar más pro */
.modal.show .btn-close{
  filter: invert(1);
  opacity: .95;
  transform: scale(1.05);
}
.modal.show .btn-close:hover{
  opacity: 1;
  transform: scale(1.12);
}

/* opcional: para que TODO el modal se vea premium */
.modal.show .modal-content{
  border: 1px solid rgba(255,255,255,.16) !important;
  box-shadow: 0 28px 70px rgba(2, 6, 23, .35) !important;
  border-radius: 18px !important;
  overflow: hidden;
}
/* ================================
   MODALES PEQUEÑOS (SaaS Pro)
   - PC: pequeño (480px)
   - Tablet: medio (560px)
   - Móvil: casi full (92vw)
   ================================ */
.modal-saas .modal-dialog{
  width: calc(100% - 24px) !important;
  margin: 12px auto !important;
  max-width: 480px !important;      /* ✅ PC pequeño */
}

@media (min-width: 768px){
  .modal-saas .modal-dialog{
    max-width: 560px !important;    /* ✅ Tablet */
  }
}

@media (min-width: 1200px){
  .modal-saas .modal-dialog{
    max-width: 520px !important;    /* ✅ PC pro (un poquito más ancho) */
  }
}

/* Evita que algún CSS te lo expanda a 100% */
.modal-saas .modal-content{
  width: 100% !important;
}

/* Opcional: que no quede pegado arriba en pantallas bajas */
@media (max-height: 700px){
  .modal-saas .modal-dialog{
    margin: 10px auto !important;
  }
}
.brand--light{
  display:flex;
  align-items:center;
  gap:14px;
}

/* LOGO CLARO */
.brand-logo-light{
  width:58px;
  height:auto;
  object-fit:contain;
  display:block;

  /* CLAVE: lo hace claro y visible */
  filter:
    brightness(1.35)
    contrast(1.25)
    drop-shadow(0 1px 3px rgba(0,0,0,.45));
}

/* TEXTO */
.brand--light .title{
  display:flex;
  flex-direction:column;
  line-height:1.15;
}

.brand--light .title b{
  font-size:16px;
  font-weight:800;
  color:#ffffff;
  letter-spacing:.3px;
}

.brand--light .title span{
  font-size:12px;
  color:rgba(255,255,255,.8);
  margin-top:2px;
}

/* RESPONSIVE */
@media (max-width:768px){
  .brand-logo-light{
    width:50px;
  }

  .brand--light .title b{
    font-size:15px;
  }

  .brand--light .title span{
    font-size:11px;
  }
}

/* ================================
   TOPBAR BRAND (FIX LOGO + TEXTO)
   - No toca nada fuera del header
   ================================ */
.topbar .brand{
  gap: 12px;
  min-width: 0;
}

/* ✅ Override directo al selector que te está dañando */
.topbar .brand img.brand-logo-light{
  width: 54px !important;
  height: auto !important;

  /* ✅ elimina el "recuadro" que mete .brand img */
  padding: 0 !important;
  background: transparent !important;
  border-radius: 0 !important;
  box-shadow: none !important;

  /* ✅ mejora visibilidad sobre azul oscuro */
  filter: brightness(1.55) contrast(1.25)
          drop-shadow(0 2px 6px rgba(0,0,0,.55)) !important;

  display: block;
}

/* Textos: más pro y legibles */
.topbar .brand .title b{
  font-size: 15px !important;
  font-weight: 900 !important;
  letter-spacing: .2px;
}

.topbar .brand .title span{
  font-size: 12px !important;
  opacity: .88;
}

/* ===== Tablet ===== */
@media (max-width: 992px){
  .topbar .wrap{ padding: 12px 14px; }
  .topbar .brand img.brand-logo-light{ width: 50px !important; }
  .topbar .brand .title b{ font-size: 14px !important; }
  .topbar .brand .title span{ font-size: 11px !important; }
}

/* ===== Móvil ===== */
@media (max-width: 520px){
  .topbar .wrap{
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .topbar .right{
    width: 100%;
    justify-content: flex-start;
    gap: 8px;
  }

  .topbar .brand img.brand-logo-light{ width: 46px !important; }

  /* Evita que el texto se “coma” todo */
  .topbar .brand .title b,
  .topbar .brand .title span{
    max-width: 92vw;
  }
}



  </style>
</head>

<body>
  <div class="bg" aria-hidden="true"></div>
  <div class="grid" aria-hidden="true"></div>

  <header class="topbar">
  <div class="wrap">

    <div class="brand brand--light">
      <img src="assets/img/admin/estadistica3.png" alt="Estadísticas 360" class="brand-logo-light">

      <div class="title">
        <b>Estadísticas 360</b>
        <span>Encuesta del momento • Participación ciudadana</span>
      </div>
    </div>

    <div class="right">
      <div class="badge-live" title="Encuesta activa">
        <span class="pulse"></span>
        <span>Encuesta activa hoy</span>
      </div>
      <div class="pill" title="Tiempo estimado">
        <span class="dot"></span>
        <span>~60 segundos</span>
      </div>
    </div>

  </div>
</header>

  <main class="page">
    <section class="hero">
      <!-- MAIN -->
      <div class="main" id="heroCard">
        <div class="main-inner">
          <!-- LEFT (copy + CTA) -->
          <div>
            <div class="kicker">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 2l3 7 7 3-7 3-3 7-3-7-7-3 7-3 3-7z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
              Tu opinión mueve la información
            </div>

            <h1>Bienvenido a <span style="color:var(--primary)">Estadísticas 360</span></h1>

            <p class="lead">
              Participa en la encuesta del momento en <b>menos de 60 segundos</b>.
              Son pocos campos, todo súper claro, y tu respuesta ayuda a construir una lectura real de lo que está pasando <b>hoy</b>.
              Si decides votar, te registras y quedas al día con la información del momento.
            </p>

            <div class="trust" aria-label="Confianza">
              <div class="t">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M12 22s8-4 8-10V7l-8-3-8 3v5c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>
                Datos protegidos
              </div>
              <div class="t">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Rápido y sin enredos
              </div>
              <div class="t">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M4 19h16M6 17V9m6 8V5m6 12v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Resultados al día
              </div>
            </div>

            <div class="timer" aria-label="Tiempo estimado de encuesta">
              <b>Tiempo estimado</b>
              <div class="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="60">
                <span></span>
              </div>
              <small>Rápido • Claro • Diseñado para celular</small>
            </div>

             <!-- LOGIN MODAL -->
              <div class="login-box">
                <a href="#" class="login-cta" id="btnOpenLogin" role="button" aria-label="Ya tengo una cuenta, iniciar sesión">
                  <span class="login-cta-ic"><i class="fa-solid fa-right-to-bracket"></i></span>
                  <span class="login-cta-txt">
                    <b>Ya tengo una cuenta</b>
                    <small>Entrar para votar</small>
                  </span>
                  <span class="login-cta-go"><i class="fa-solid fa-arrow-right"></i></span>
                </a>
              </div>

            <div class="cta-row">
              <!-- MINI MODAL -->
              <button class="btn btn-primary" type="button" id="btnOpenModal">
                <span>Quiero votar y registrarme</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M5 12h12M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>

             

              <a class="btn btn-ghost" href="https://www.google.com/" target="_blank" rel="noopener noreferrer">
                No quiero participar
              </a>
            </div>

            <div class="cta-mini">
              ✅ Te toma <b>1 minuto</b> • ✅ Tu voto cuenta • ✅ Sin complicaciones
            </div>
          </div>

          <!-- RIGHT (bento tiles) -->
          <div class="bento">
            <div class="tile">
              <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M4 19h16M6 17V9m6 8V5m6 12v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Vista rápida (enterprise)
              </h3>
              <div class="stats">
                <div class="stat">
                  <b>60s</b>
                  <span>Tiempo promedio</span>
                </div>
                <div class="stat">
                  <b>3</b>
                  <span>Pasos simples</span>
                </div>
                <div class="stat">
                  <b>100%</b>
                  <span>Responsive</span>
                </div>
              </div>
            </div>

            <div class="tile">
              <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Encuesta del momento (preview)
              </h3>
              <p>Así se ve por dentro: campos cortos, navegación clara y finalización rápida.</p>
              <div class="preview" aria-hidden="true">
                <div class="sk w90"></div>
                <div class="sk w78"></div>
                <div class="sk w84"></div>
                <div class="sk w66"></div>
              </div>
            </div>

            <div class="tile">
              <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M12 22s8-4 8-10V7l-8-3-8 3v5c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>
                Confianza y claridad
              </h3>
              <p>Tu participación es anónima a nivel de resultados y se usa para análisis agregado.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- SIDE -->
      <aside class="side">
        <div class="side-inner">
          <h2>
            ¿Cómo funciona?
            <span class="tag">Simple</span>
          </h2>

          <div class="step">
            <div class="badge">1</div>
            <div>
              <b>Te registras</b>
              <span>Solo lo esencial para validar participación.</span>
            </div>
          </div>

          <div class="step">
            <div class="badge">2</div>
            <div>
              <b>Respondes</b>
              <span>Pocos campos, todo claro y rápido.</span>
            </div>
          </div>

          <div class="step">
            <div class="badge">3</div>
            <div>
              <b>Listo</b>
              <span>Tu respuesta suma a los resultados del momento.</span>
            </div>
          </div>

          <div class="map-card">
            <div class="map-head">
              <b>Mapa de Colombia</b>
              <div class="map-legend" aria-label="Leyenda">
                <span><i class="dot-lg dot-green"></i> Verde</span>
                <span><i class="dot-lg dot-blue"></i> Azul</span>
                <span><i class="dot-lg dot-red"></i> Rojo</span>
              </div>
            </div>

            <div class="map-body">
              <img src="assets/img/colombia.png" class="map-img" alt="Mapa de Colombia con departamentos resaltados">
            </div>
          </div>

        </div>
      </aside>
    </section>
  </main>

  <footer>
    © <?php echo date('Y'); ?> Estadísticas 360 — Plataforma de participación y análisis.
  </footer>

  <!-- MINI MODAL -->
  <div class="mini-modal-overlay" id="modalOverlay" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="mini-modal" id="modalCard" aria-label="Confirmación para iniciar encuesta">
      <div class="mini-modal-header">
        <div>
          <div class="mini-modal-title">Antes de empezar 🚀</div>
          <div class="mini-modal-sub">
            Vas a iniciar el proceso de registro y voto. Es rápido: <b>menos de 60 segundos</b>.
          </div>
        </div>
        <button class="mini-modal-close" type="button" id="btnCloseModal" aria-label="Cerrar">✕</button>
      </div>

      <div class="mini-modal-body">
        <div class="mini-modal-box">
          <div class="mini-modal-row">
            <div class="mini-modal-ic">⏱</div>
            <div>
              <b>Tiempo</b>
              <span>Promedio: 1 minuto (campos cortos y claros).</span>
            </div>
          </div>
          <div class="mini-modal-row">
            <div class="mini-modal-ic">🔒</div>
            <div>
              <b>Confidencialidad</b>
              <span>Tu información se maneja con cuidado y se usa para análisis agregado.</span>
            </div>
          </div>
          <div class="mini-modal-row">
            <div class="mini-modal-ic">✅</div>
            <div>
              <b>Recomendación</b>
              <span>Ten a la mano tus datos básicos. ¡Y listo!</span>
            </div>
          </div>
        </div>
      </div>

      <div class="mini-modal-actions">
        <button class="btn btn-primary btn-mini-modal" type="button" id="btnGoRegistro">
          <span>Continuar y registrarme</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h12M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <button class="btn btn-outline btn-mini-modal" type="button" id="btnCancelModal">
          <span>Cancelar</span>
        </button>
      </div>
    </div>
  </div>



  <script>
    // ===== Modal logic (mini modal) =====
    (function(){
      const overlay = document.getElementById('modalOverlay');
      const card    = document.getElementById('modalCard');
      const openBtn = document.getElementById('btnOpenModal');
      const closeBtn= document.getElementById('btnCloseModal');
      const cancelBtn = document.getElementById('btnCancelModal');
      const goBtn   = document.getElementById('btnGoRegistro');

      function open(){
        overlay.style.display = 'flex';
        overlay.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(()=> card.classList.add('open'));
        setTimeout(()=> closeBtn && closeBtn.focus(), 60);
        document.body.style.overflow = 'hidden';
      }

      function close(){
        card.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        setTimeout(()=>{
          overlay.style.display = 'none';
          document.body.style.overflow = '';
          openBtn && openBtn.focus();
        }, 160);
      }

      openBtn && openBtn.addEventListener('click', open);
      closeBtn && closeBtn.addEventListener('click', close);
      cancelBtn && cancelBtn.addEventListener('click', close);

      overlay && overlay.addEventListener('click', (e)=>{
        if(e.target === overlay) close();
      });

      document.addEventListener('keydown', (e)=>{
        if(e.key === 'Escape' && overlay.style.display === 'flex') close();
      });

      goBtn && goBtn.addEventListener('click', ()=>{
        window.location.href = 'registro.php';
      });
    })();

    // ===== Parallax suave SOLO desktop =====
    (function(){
      const card = document.getElementById('heroCard');
      if(!card) return;

      const isTouch = matchMedia('(hover: none)').matches;
      if (isTouch) return;

      let raf = null;
      window.addEventListener('mousemove', (e) => {
        if (raf) cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => {
          const r = card.getBoundingClientRect();
          const x = (e.clientX - r.left) / r.width;
          const y = (e.clientY - r.top) / r.height;
          const rx = (y - 0.5) * -2.2;
          const ry = (x - 0.5) *  2.2;
          card.style.transform = `perspective(1100px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(0)`;
        });
      });

      window.addEventListener('mouseleave', () => {
        card.style.transform = '';
      });
    })();

  </script>

<!-- ===== MODAL LOGIN ===== -->
<div class="modal fade modal-saas" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">

    <div class="modal-content" style="border-radius:24px; border:0; box-shadow:0 44px 160px rgba(2,6,23,.35);">
      <div class="modal-header" style="border:0; padding:18px 18px 10px; background:linear-gradient(135deg,#021b5a,#0B3EDC); border-top-left-radius:24px; border-top-right-radius:24px;">
        <div style="display:flex; gap:12px; align-items:center;">
          <div style="width:54px;height:54px;border-radius:18px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);display:grid;place-items:center;">
            <img src="assets/img/admin/estadistica3.png" alt="Logo" style="width:78%;height:78%;object-fit:contain;">
          </div>
          <div>
            <b style="font-weight:950;color:#fff;font-size:18px;">Iniciar sesión</b><br>
            <small style="font-weight:750;color:rgba(255,255,255,.90);">Accede para continuar</small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter:invert(1);opacity:.85;"></button>
      </div>

      <div class="modal-body" style="padding:14px 18px 16px;">
        <div style="border:1px solid rgba(15,23,42,.10);background:rgba(255,255,255,.90);border-radius:18px;padding:14px;">
          <form id="formLoginVotantes" autocomplete="on">
            <div style="margin-bottom:12px;">
              <div style="font-weight:950;font-size:13px;color:#111827;margin-bottom:7px;">Correo o Usuario</div>
              <input type="text" id="login_user" name="login_user" placeholder="Escribe tu usuario o correo" required
                style="height:54px;width:100%;border-radius:16px;border:1px solid rgba(15,23,42,.14);background:#fff;padding:0 14px;font-size:16px;font-weight:800;">
            </div>

            <div style="margin-bottom:12px; position: relative;">
              <div style="font-weight:950;font-size:13px;color:#111827;margin-bottom:7px;">Contraseña</div>
              <input type="password" id="login_password" name="login_password" placeholder="Escribe tu contraseña" required
                style="height:54px;width:100%;border-radius:16px;border:1px solid rgba(15,23,42,.14);background:#fff;padding:0 44px 0 14px;font-size:16px;font-weight:800;">

              <button type="button" id="togglePassword"
                style="position: absolute; right: 12px; top: 42px; background: none; border: none; cursor: pointer; color: #64748b; font-size: 18px; padding: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-eye" id="eyeIcon"></i>
              </button>
            </div>

            <button type="button" id="btnLoginSubmit"
              style="height:54px;width:100%;border-radius:16px;border:0;color:#fff;font-weight:950;background:linear-gradient(135deg,#021b5a,#0B3EDC);display:flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;">
              <i class="fa-solid fa-arrow-right-to-bracket"></i> Entrar
            </button>

          
              <!-- ✅ OLVIDASTE CONTRASEÑA (abre modal) -->
        <div style="margin-top:12px; text-align:center;">
          <a href="#"
            id="openForgotModal"
            style="font-weight:900; font-size:13px; color:#0B3EDC; text-decoration:none;">
            ¿Olvidaste tu contraseña?
          </a>
          <div style="margin-top:4px; font-size:12px; font-weight:700; color:#64748b;">
            Te enviamos una contraseña temporal a tu correo.
          </div>
        </div>



          </form>
        </div>
      </div>

    </div>
  </div>
</div>
<!-- ===== MODAL OLVIDÉ CONTRASEÑA (FUERA DEL LOGIN MODAL) ===== -->
<div class="modal fade modal-saas" id="forgotModal" tabindex="-1" aria-hidden="true">

 <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">

    <div class="modal-content" style="border-radius:24px; border:0; box-shadow:0 44px 160px rgba(2,6,23,.35); overflow:hidden;">
      
      <div class="modal-header" style="border:0; padding:18px 18px 10px; background:linear-gradient(135deg,#021b5a,#0B3EDC);">
        <div style="display:flex; gap:12px; align-items:center;">
          <div style="width:54px;height:54px;border-radius:18px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);display:grid;place-items:center;">
            <i class="fa-solid fa-unlock-keyhole" style="color:#fff;font-size:22px;"></i>
          </div>
          <div>
            <b style="font-weight:950;color:#fff;font-size:18px;">Recuperar contraseña</b><br>
            <small style="font-weight:750;color:rgba(255,255,255,.90);">Te enviamos una contraseña temporal</small>
          </div>
        </div>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" style="filter:invert(1);opacity:.85;"></button>
      </div>

      <div class="modal-body" style="padding:14px 18px 16px; background:#f6f8fc;">
        <div style="border:1px solid rgba(15,23,42,.10);background:#fff;border-radius:18px;padding:14px;">
          
          <form id="formForgotPassword" autocomplete="off">

            <div style="margin-bottom:12px;">
              <div style="font-weight:950;font-size:13px;color:#111827;margin-bottom:7px;">Correo o Usuario</div>
              <input type="text" id="forgot_login" name="forgot_login" placeholder="Escribe tu correo o username" required
                style="height:54px;width:100%;border-radius:16px;border:1px solid rgba(15,23,42,.14);background:#fff;padding:0 14px;font-size:16px;font-weight:800;">
              <div style="margin-top:6px;font-size:12px;font-weight:700;color:#64748b;">
                Si el registro existe, enviamos la contraseña temporal al correo asociado.
              </div>
            </div>

            <div id="forgot_ok" style="display:none;margin-bottom:10px;padding:10px 12px;border-radius:14px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);color:#065f46;font-weight:900;font-size:13px;"></div>
            <div id="forgot_bad" style="display:none;margin-bottom:10px;padding:10px 12px;border-radius:14px;background:rgba(239,68,68,.10);border:1px solid rgba(239,68,68,.25);color:#7f1d1d;font-weight:900;font-size:13px;"></div>

            <button type="submit" id="btnForgotSend"
              style="height:54px;width:100%;border-radius:16px;border:0;color:#fff;font-weight:950;background:linear-gradient(135deg,#021b5a,#0B3EDC);display:flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;">
              <i class="fa-solid fa-paper-plane"></i> Enviar contraseña temporal
            </button>

            <button type="button" id="btnBackToLogin"
              style="margin-top:10px;height:48px;width:100%;border-radius:16px;border:1px solid rgba(15,23,42,.14);color:#0f172a;font-weight:950;background:#fff;display:flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;">
              <i class="fa-solid fa-arrow-left"></i> Volver al login
            </button>

          </form>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- Libs (cárgalas 1 sola vez y en este orden) -->
<!-- Libs (1 sola vez y en este orden) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="./admin/js/lib/data-md5.js"></script>


<script>
document.addEventListener("DOMContentLoaded", () => {

  // =========================================================
  // 1) ABRIR MODAL LOGIN (Bootstrap)
  // =========================================================
  function getOrCreateModal(el, options = undefined){
    if (!el || typeof bootstrap === "undefined" || !bootstrap.Modal) return null;
    return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, options);
  }

  const loginModalEl  = document.getElementById("loginModal");
  const forgotModalEl = document.getElementById("forgotModal");

  function cleanupBackdrop() {
    // ✅ por si queda alguno colgado (pantalla oscura sin modal)
    document.querySelectorAll(".modal-backdrop").forEach(b => b.remove());
    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("padding-right");
  }

  function showLoginModal(){
    const instance = getOrCreateModal(loginModalEl, { backdrop:'static', keyboard:false });
    if (!instance) return;

    instance.show();
    loginModalEl.addEventListener('shown.bs.modal', function onShown(){
      loginModalEl.removeEventListener('shown.bs.modal', onShown);
      const u = document.getElementById('login_user');
      if (u) u.focus();
    });
  }

  // Click CTA "Ya tengo una cuenta"
  document.addEventListener('click', function(e){
    const btn = e.target.closest('#btnOpenLogin');
    if(!btn) return;
    e.preventDefault();
    showLoginModal();
  });

  // =========================================================
  // 2) TOGGLE PASSWORD (solo 1 vez)
  // =========================================================
  const pass = document.getElementById("login_password");
  const toggleBtn = document.getElementById("togglePassword");
  const eyeIcon = document.getElementById("eyeIcon");

  if (toggleBtn && pass) {
    toggleBtn.addEventListener("click", () => {
      const isPass = pass.type === "password";
      pass.type = isPass ? "text" : "password";
      if (eyeIcon) {
        eyeIcon.classList.toggle("fa-eye", !isPass);
        eyeIcon.classList.toggle("fa-eye-slash", isPass);
      }
    });
  }

  // =========================================================
  // 3) CAMBIO ENTRE MODALES: LOGIN <-> FORGOT (sin pantalla oscura)
  //    Requisito: #forgotModal debe estar FUERA del #loginModal
  // =========================================================
  const openForgot = document.getElementById("openForgotModal");
  const btnBack    = document.getElementById("btnBackToLogin");

  if (openForgot && loginModalEl && forgotModalEl) {
    openForgot.addEventListener("click", (e) => {
      e.preventDefault();

      const loginInstance = getOrCreateModal(loginModalEl);
      if (!loginInstance) return;

      loginModalEl.addEventListener("hidden.bs.modal", function handler(){
        loginModalEl.removeEventListener("hidden.bs.modal", handler);

        cleanupBackdrop();

        const forgotInstance = getOrCreateModal(forgotModalEl);
        if (!forgotInstance) return;

        forgotInstance.show();

        forgotModalEl.addEventListener("shown.bs.modal", function handler2(){
          forgotModalEl.removeEventListener("shown.bs.modal", handler2);
          const inp = document.getElementById("forgot_login");
          if (inp) inp.focus();
        });
      }, { once:true });

      loginInstance.hide();
    });
  }

  if (btnBack && loginModalEl && forgotModalEl) {
    btnBack.addEventListener("click", () => {
      const forgotInstance = getOrCreateModal(forgotModalEl);
      if (!forgotInstance) return;

      forgotModalEl.addEventListener("hidden.bs.modal", function handler(){
        forgotModalEl.removeEventListener("hidden.bs.modal", handler);

        cleanupBackdrop();

        const loginInstance = getOrCreateModal(loginModalEl, { backdrop:'static', keyboard:false });
        if (!loginInstance) return;

        loginInstance.show();

        loginModalEl.addEventListener("shown.bs.modal", function handler2(){
          loginModalEl.removeEventListener("shown.bs.modal", handler2);
          const u = document.getElementById("login_user");
          if (u) u.focus();
        });
      }, { once:true });

      forgotInstance.hide();
    });
  }

  // =========================================================
  // 4) AJAX RECUPERAR CONTRASEÑA (mantiene tu endpoint)
  // =========================================================
  const formForgot = document.getElementById("formForgotPassword");
  const btnForgotSend = document.getElementById("btnForgotSend");
  const ok = document.getElementById("forgot_ok");
  const bad = document.getElementById("forgot_bad");

  const showMsg = (el, txt) => { if(!el) return; el.textContent = txt; el.style.display = "block"; };
  const hideMsg = (el) => { if(!el) return; el.style.display = "none"; };

  if (formForgot) {
    formForgot.addEventListener("submit", async (e) => {
      e.preventDefault();
      hideMsg(ok); hideMsg(bad);

      const login = (document.getElementById("forgot_login")?.value || "").trim();
      if (!login) { showMsg(bad, "Escribe tu correo o usuario."); return; }

      if (btnForgotSend){
        btnForgotSend.disabled = true;
        btnForgotSend.style.opacity = "0.85";
      }

      try {
        const fd = new FormData();
        fd.append("login", login);

        const res = await fetch("./admin/ajax/auth_forgot_password.php", {
          method: "POST",
          body: fd
        });

        const data = await res.json();

        if (data && data.ok) {
          showMsg(ok, data.msg || "Si existe, te enviamos una contraseña temporal.");
          formForgot.reset();
        } else {
          showMsg(bad, (data && data.msg) ? data.msg : "No fue posible procesar la solicitud.");
        }
      } catch (err) {
        showMsg(bad, "Error de red. Intenta nuevamente.");
        console.error(err);
      } finally {
        if (btnForgotSend){
          btnForgotSend.disabled = false;
          btnForgotSend.style.opacity = "1";
        }
      }
    });
  }

  // =========================================================
  // 5) LOGIN SUBMIT (tu lógica tal cual)
  // =========================================================
  document.getElementById('btnLoginSubmit')?.addEventListener('click', async function(){
    const nickname = document.getElementById('login_user')?.value.trim() || '';
    const hashpass = document.getElementById('login_password')?.value.trim() || '';

    if(!nickname || !hashpass){
      Swal.fire('Error', 'Por favor completa todos los campos.', 'error');
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
        Swal.fire('Error', data.message || 'Error de inicio de sesión.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Entrar';
      }
    } catch(err){
      Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
      console.error(err);
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Entrar';
    }
  });

  // Enter key en login
  document.getElementById('formLoginVotantes')?.addEventListener('keydown', function(e){
    if(e.key === 'Enter'){
      e.preventDefault();
      document.getElementById('btnLoginSubmit')?.click();
    }
  });

});
</script>


</body>
</html>
