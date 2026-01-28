<?php
// index.php (Enterprise WOW SaaS)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Estadísticas 360 | Encuesta del Momento</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">

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
      flex-wrap:wrap;
      align-items:center;
    }
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

    /* ===== Modal (mini) ===== */
    .modal-overlay{
      position: fixed;
      inset: 0;
      background: rgba(2,6,23,.56);
      display:none;
      align-items:center;
      justify-content:center;
      padding: 18px;
      z-index: 1000;
    }
    .modal{
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
    .modal::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(460px 220px at 18% 0%, rgba(19,43,82,.18), transparent 55%),
        radial-gradient(420px 220px at 90% 30%, rgba(0,194,255,.14), transparent 60%);
      pointer-events:none;
    }
    .modal.open{
      transform: translateY(0) scale(1);
      opacity: 1;
    }
    .modal-header{
      position: relative;
      padding: 16px 16px 12px;
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
    }
    .modal-title{
      margin:0;
      font-size: 14px;
      font-weight: 900;
      color: var(--primary);
      letter-spacing: -.01em;
    }
    .modal-sub{
      margin: 6px 0 0;
      font-size: 12px;
      color: rgba(71,85,105,.98);
      font-weight: 600;
      max-width: 54ch;
    }
    .modal-close{
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
    .modal-close:hover{
      transform: translateY(-1px);
      background: rgba(15,23,42,.10);
    }
    .modal-body{
      position: relative;
      padding: 0 16px 14px;
    }
    .modal-box{
      border-radius: 18px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(255,255,255,.86);
      box-shadow: 0 14px 34px rgba(2,6,23,.08);
      padding: 12px;
    }
    .modal-row{
      display:flex;
      gap: 10px;
      align-items:flex-start;
      padding: 10px 8px;
      border-radius: 14px;
    }
    .modal-row + .modal-row{
      border-top: 1px dashed rgba(15,23,42,.10);
    }
    .ic{
      width: 34px; height: 34px;
      border-radius: 14px;
      background: rgba(19,43,82,.10);
      display:grid;
      place-items:center;
      color: var(--primary);
      flex: 0 0 auto;
      font-weight: 900;
    }
    .modal-row b{
      display:block;
      font-size: 13px;
      color: #0f172a;
      font-weight: 900;
    }
    .modal-row span{
      display:block;
      margin-top: 2px;
      font-size: 12px;
      color: rgba(71,85,105,.98);
      font-weight: 600;
    }
    .modal-actions{
      position: relative;
      display:flex;
      gap: 10px;
      flex-wrap: wrap;
      padding: 0 16px 16px;
    }
    .btn-modal{
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
      .btn{ width: 100%; }
      .btn-primary{ width: 100%; justify-content:center; }
      .btn-ghost{ width: 100%; }
      .stats{ grid-template-columns: 1fr; }
      .main-inner{ padding: 18px; }
    }

    @media (prefers-reduced-motion: reduce){
      .main, .side{ animation: none; opacity: 1; transform: none; }
      .btn-primary{ animation: none; }
      .btn-primary::after{ animation: none; }
      .bar > span{ animation: none; width: 60%; }
      .sk::after{ animation: none; }
      .pulse{ animation: none; }
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

/* La magia: tamaño responsivo + no se corta */
.map-img{
  width: 100%;
  height: auto;
  max-width: 420px;         /* tamaño bonito en desktop dentro del side */
  max-height: 360px;
  object-fit: contain;
  display:block;
  filter: drop-shadow(0 18px 34px rgba(2,6,23,.18));
}

/* Para que se vea más grande en PC */
@media (min-width: 1200px){
  .map-img{
    max-width: 520px;
    max-height: 420px;
  }
}

/* En móvil, que no quede gigante pero sí visible */
@media (max-width: 520px){
  .map-body{ padding: 12px; }
  .map-img{
    max-width: 100%;
    max-height: 320px;
  }
}

  </style>
</head>

<body>
  <div class="bg" aria-hidden="true"></div>
  <div class="grid" aria-hidden="true"></div>

  <header class="topbar">
    <div class="wrap">
      <div class="brand">
        <img src="assets/img/admin/estadistica3.png" alt="Estadísticas 360">
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

            <div class="cta-row">
              <!-- IMPORTANTE: abre el minimodal -->
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
                  <path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
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
                  <path d="M4 19h16M6 17V9m6 8V5m6 12v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
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
  <div class="modal-overlay" id="modalOverlay" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal" id="modalCard" aria-label="Confirmación para iniciar encuesta">
      <div class="modal-header">
        <div>
          <div class="modal-title">Antes de empezar 🚀</div>
          <div class="modal-sub">
            Vas a iniciar el proceso de registro y voto. Es rápido: <b>menos de 60 segundos</b>.
          </div>
        </div>
        <button class="modal-close" type="button" id="btnCloseModal" aria-label="Cerrar">
          ✕
        </button>
      </div>

      <div class="modal-body">
        <div class="modal-box">
          <div class="modal-row">
            <div class="ic">⏱</div>
            <div>
              <b>Tiempo</b>
              <span>Promedio: 1 minuto (campos cortos y claros).</span>
            </div>
          </div>
          <div class="modal-row">
            <div class="ic">🔒</div>
            <div>
              <b>Confidencialidad</b>
              <span>Tu información se maneja con cuidado y se usa para análisis agregado.</span>
            </div>
          </div>
          <div class="modal-row">
            <div class="ic">✅</div>
            <div>
              <b>Recomendación</b>
              <span>Ten a la mano tus datos básicos. ¡Y listo!</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn btn-primary btn-modal" type="button" id="btnGoRegistro">
          <span>Continuar y registrarme</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h12M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <button class="btn btn-outline btn-modal" type="button" id="btnCancelModal">
          Mejor después
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
        // focus accesible
        setTimeout(()=> closeBtn && closeBtn.focus(), 60);
        // bloquea scroll
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

    // ===== Parallax suave SOLO desktop (sin afectar móvil) =====
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
          const x = (e.clientX - r.left) / r.width;   // 0..1
          const y = (e.clientY - r.top) / r.height;   // 0..1
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
</body>
</html>
