/* =========================================================
   Estadísticas 360 Web - Dashboard JS (Charts + Mapa)
   - Chart.js horizontal bar con fotos/iniciales
   - Modo sondeo / cuestionario
   - Mapa ganadores + card detalle
========================================================= */

/* =========================
   Colores base
========================= */
let ColoresCandidatos = {
  1: "#1f77b4",
  2: "#ff7f0e",
  3: "#2ca02c",
  4: "#d62728"
};

// Usar colores dinámicos si están disponibles
if (typeof window.ColoresCandidatosDinamicos !== "undefined" && window.ColoresCandidatosDinamicos) {
  ColoresCandidatos = window.ColoresCandidatosDinamicos;
}

const COLOR_TEMA = "#20427F";

// Paleta fallback
const PALETA_COLORES = [
  "#1f77b4",
  "#ff7f0e",
  "#2ca02c",
  "#d62728",
  "#9467bd",
  "#8c564b",
  "#e377c2",
  "#7f7f7f",
  "#bcbd22",
  "#17becf"
];

$(document).ready(function () {
  let grafico = null;         // graficoVotos
  let graficoGeneral = null;  // graficoGeneral
  let preguntaSeleccionada = 0;

  /* =========================
     Helpers UI / Util
  ========================= */
  function isMobile() {
    return window.matchMedia("(max-width: 575px)").matches;
  }

  function montarSpinner() {
    return `
      <div class="text-center p-4">
        <div class="spinner-border" style="color:${COLOR_TEMA};" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 mb-0 text-muted fw-bold">Cargando sondeo...</p>
      </div>
    `;
  }

  function montarVacio() {
    return `
      <div class="p-4 text-center">
        <div class="fw-bold" style="color:#0f172a;">Sin datos disponibles</div>
        <div class="text-muted fw-bold" style="font-size:.92rem;">Intenta con otro departamento.</div>
      </div>
    `;
  }

  function obtenerColorPorIdOIndice(id, index) {
    return ColoresCandidatos[id] || PALETA_COLORES[index % PALETA_COLORES.length] || COLOR_TEMA;
  }

  function dividirEnTresLineas(nombre) {
    const palabras = (nombre || "").split(" ").filter(Boolean);
    if (palabras.length <= 1) return [palabras[0] || ""];
    if (palabras.length === 2) return [palabras[0], palabras[1]];

    const linea1 = palabras[0];
    const linea2 = palabras[1] + (palabras[2] ? " " + palabras[2] : "");
    const linea3 = palabras.slice(3).join(" ");

    const lineas = [linea1, linea2];
    if (linea3.trim() !== "") lineas.push(linea3);
    return lineas;
  }

  // ✅ Calcula padding izquierdo sin matar el chart (limita por % del canvas)
  function calcularPaddingIzquierdo(ctx, labelsMultiLinea, canvasEl, font = "12px system-ui") {
    ctx.save();
    ctx.font = font;

    let maxW = 0;
    (labelsMultiLinea || []).forEach(lines => {
      (lines || []).forEach(line => {
        const w = ctx.measureText(String(line || "")).width;
        if (w > maxW) maxW = w;
      });
    });

    ctx.restore();

    // foto 30px + gap + texto + margen
    let pad = Math.ceil(maxW + 30 + 14 + 26);

    // ✅ NUNCA permitir que coma el ancho del chart
    const canvasW = (canvasEl?.clientWidth || canvasEl?.width || 700);
    const maxPad = Math.floor(canvasW * 0.45); // 45% desktop
    pad = Math.min(pad, maxPad);

    // mínimos sanos
    pad = Math.max(140, pad);

    // mobile: permite un poco más pero con control
    if (window.matchMedia("(max-width: 575px)").matches) {
      const maxPadMobile = Math.floor(canvasW * 0.52);
      pad = Math.min(pad, maxPadMobile);
      pad = Math.max(125, pad);
    }
    return pad;
  }

  /* =========================
     MODO CUESTIONARIO: Preguntas
  ========================= */
  function cargarPreguntasCuestionario() {
    const opcionActiva = window.OPCION_ACTIVA_WEB || "sondeo";
    if (opcionActiva !== "cuestionario") return;

    $.ajax({
      url: "admin/ajax/rqst.php",
      type: "POST",
      dataType: "json",
      data: { op: "encuesta_preguntas_activas" },
      success: function (res) {
        if (!res || !res.success) {
          $("#selectorPregunta").html('<option value="">Sin preguntas disponibles</option>');
          $("#fichaTecnicaNombre").text("No hay cuestionario activo");
          return;
        }

        if (res.ficha && res.ficha.nombre) {
          $("#fichaTecnicaNombre").text(res.ficha.nombre);
        }

        let options = "";
        if (res.preguntas && res.preguntas.length > 0) {
          res.preguntas.forEach((p, idx) => {
            const selected = idx === 0 ? "selected" : "";
            options += `<option value="${p.id}" ${selected}>${p.texto_pregunta}</option>`;
          });
          $("#selectorPregunta").html(options);

          preguntaSeleccionada = parseInt(res.preguntas[0].id) || 0;

          cargarGraficoGeneral(preguntaSeleccionada);
          actualizarColoresMapaCuestionario(preguntaSeleccionada);
        } else {
          $("#selectorPregunta").html('<option value="">Sin preguntas disponibles</option>');
        }
      },
      error: function () {
        $("#selectorPregunta").html('<option value="">Error al cargar</option>');
      }
    });
  }

  $(document).on("change", "#selectorPregunta", function () {
    preguntaSeleccionada = parseInt($(this).val()) || 0;
    cargarGraficoGeneral(preguntaSeleccionada);
    actualizarColoresMapaCuestionario(preguntaSeleccionada);
  });

  /* =========================
     MAPA: colores cuestionario
  ========================= */
  function actualizarColoresMapaCuestionario(preguntaId) {
    if (!preguntaId) return;

    $.ajax({
      url: "admin/ajax/rqst.php",
      type: "POST",
      dataType: "json",
      data: {
        op: "encuesta_colores_mapa",
        pregunta_id: preguntaId
      },
      success: function (res) {
        if (!res || !res.success) return;

        const colores = res.colores || {};
        const ganadores = res.ganadores || {};

        ColoresCandidatos = colores;

        $("#mapaContainer svg path.mapaClick").each(function () {
          const codigo = $(this).data("codigo");
          if (!codigo) return;

          const infoGanador = ganadores[codigo];
          if (!infoGanador) {
            $(this).css("fill", "#d9d9d9");
          } else if (infoGanador.empate === true) {
            $(this).css("fill", "url(#rayasAzules)");
          } else {
            const color = colores[infoGanador.ganador] || "#d9d9d9";
            $(this).css("fill", color);
          }
        });
      }
    });
  }

  /* =========================
     MAPA: pintar ganadores (sondeo)
  ========================= */
  function pintarMapaSegunGanadores() {
    const departamentos = [];
    $("#mapaContainer svg g").each(function () {
      const codigo = $(this).find("path").data("codigo");
      if (codigo) departamentos.push(codigo);
    });

    if (departamentos.length === 0) return;

    $.ajax({
      url: "admin/ajax/rqst.php",
      type: "POST",
      dataType: "json",
      data: {
        op: "mapa_colores_departamentos",
        departamentos: departamentos
      },
      success: function (res) {
        if (!res || !res.success) return;

        const info = res.data || {};

        $("#mapaContainer svg g").each(function () {
          const path = $(this).find("path");
          const codigo = path.data("codigo");
          if (!codigo) return;

          const ganador = info[codigo];
          if (!ganador) return;

          if (ganador.empate) {
            // Usar URL absoluta para compatibilidad con todos los navegadores
            const baseUrl = window.location.href.split('#')[0];
            path.attr("fill", "url(" + baseUrl + "#rayasAzules)");
          } else {
            const color = ColoresCandidatos[ganador.ganador] || "#d9d9d9";
            path.attr("fill", color);
          }
        });
      }
    });
  }

  /* =========================
     GRAFICO GENERAL (horizontal) - FIX labels
  ========================= */
  function cargarGraficoGeneral(preguntaId = 0) {
  const opcionActiva = window.OPCION_ACTIVA_WEB || "sondeo";
  const endpoint = (opcionActiva === "cuestionario") ? "encuesta_general_index" : "sondeo_general_index";

  const requestData = { op: endpoint };
  if (opcionActiva === "cuestionario" && preguntaId > 0) {
    requestData.pregunta_id = preguntaId;
  }

  $.ajax({
    url: "admin/ajax/rqst.php",
    type: "POST",
    dataType: "json",
    data: requestData,
    success: function (res) {
      if (!res || !res.success || !res.votos) return;

      const canvas = document.getElementById("graficoGeneral");
      if (!canvas) return;

      if (graficoGeneral) graficoGeneral.destroy();

      // ========= labels/data =========
      function dividirEnTresLineas(nombre) {
        const palabras = (nombre || "").split(" ").filter(Boolean);
        if (palabras.length <= 1) return [palabras[0] || ""];
        if (palabras.length === 2) return [palabras[0], palabras[1]];
        const linea1 = palabras[0];
        const linea2 = palabras[1] + (palabras[2] ? " " + palabras[2] : "");
        const linea3 = palabras.slice(3).join(" ");
        const lineas = [linea1, linea2];
        if (linea3.trim() !== "") lineas.push(linea3);
        return lineas;
      }

      const labels = res.votos.map(v => dividirEnTresLineas(v.nombre_completo));
      const data = res.votos.map(v => Number(v.total || 0));

      const imagenesValidas = res.votos.map(v => {
        const url = v.foto_url || "";
        return url.trim() !== "" && !url.includes("option_default") && !url.includes("default.png");
      });

      const imgs = res.votos.map((v, i) => {
        if (imagenesValidas[i]) {
          const img = new Image();
          img.src = v.foto_url;
          return img;
        }
        return null;
      });

      const nombres = res.votos.map(v => v.nombre_completo || "?");

      const coloresAsignados = res.votos.map((v, i) => {
        const id = v.candidato_id || v.id;
        return ColoresCandidatos[id] || PALETA_COLORES[i % PALETA_COLORES.length];
      });

      // ✅ Carril fijo para foto + texto (100% estable)
      const LANE_DESKTOP = 235;  // espacio a la izquierda
      const LANE_MOBILE  = 185;

      const LANE = window.matchMedia("(max-width: 575px)").matches ? LANE_MOBILE : LANE_DESKTOP;

      // ✅ Plugin: pinta SIEMPRE en el carril izquierdo (x fijo)
      const fotoLabelPlugin = {
        id: "fotoLabelPlugin",
        afterDraw(chart) {
          const ctx = chart.ctx;
          const yAxis = chart.scales.y;

          // coordenadas dentro del carril
          const imgX  = 14;   // foto
          const textX = 52;   // texto

          ctx.save();
          ctx.textBaseline = "middle";
          ctx.textAlign = "left";

          chart.data.labels.forEach((label, i) => {
            const y = yAxis.getPixelForTick(i);
            const img = imgs[i];
            const imgY = y - 15;

            // Foto / inicial
            if (img && imagenesValidas[i]) {
              try { ctx.drawImage(img, imgX, imgY, 30, 30); } catch (e) {}
            } else {
              const color = coloresAsignados[i] || "#1f77b4";
              ctx.beginPath();
              ctx.arc(imgX + 15, y, 15, 0, 2 * Math.PI);
              ctx.fillStyle = color;
              ctx.fill();
              ctx.closePath();

              ctx.fillStyle = "#fff";
              ctx.font = "400 12px system-ui, -apple-system, Segoe UI, Roboto, Arial";
              ctx.textAlign = "center";
              ctx.fillText((nombres[i] || "?").charAt(0).toUpperCase(), imgX + 15, y + 1);
              ctx.textAlign = "left";
            }

            // Texto multilínea (fijo, nunca encima de barras)
            ctx.fillStyle = "#0f172a";
            ctx.font = "00 12px system-ui, -apple-system, Segoe UI, Roboto, Arial";

            (label || []).forEach((line, lineIndex) => {
              ctx.fillText(String(line || ""), textX, y + (lineIndex * 12) - 6);
            });
          });

          ctx.restore();
        }
      };

      // ✅ Truco clave:
      // 1) Ocultamos ticks del eje Y (no queremos que Chart.js ponga texto)
      // 2) Reservamos carril con layout.padding.left = LANE
      graficoGeneral = new Chart(canvas, {
        type: "bar",
        plugins: [fotoLabelPlugin],
        data: {
          labels: labels,
          datasets: [{
            data: data,
            backgroundColor: coloresAsignados,
            borderRadius: 5,
            borderSkipped: false
          }]
        },
        options: {
          indexAxis: "y",
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: { enabled: true }
          },
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: "rgba(2,6,23,.08)" },
              ticks: { precision: 0 } // si quieres enteros
            },
            y: {
              ticks: { display: false },
              grid: { display: false }
            }
          },
          layout: { padding: { left: LANE, right: 14, top: 8, bottom: 8 } }
        }
      });
    },
    error: function (xhr, status, error) {
      console.error("Error en AJAX cargarGraficoGeneral:", status, error, xhr.responseText);
    }
  });
}

  

  /* =========================
     Card + Mapa + Grafico depto
  ========================= */
  const MapaSondeo = {
    departamentoActual: "",
    municipioActual: "",

    init() {
      this.eventos();
    },

    hacerMapaClickeable() {
      $("#mapaContainer svg path").each(function () {
        $(this).addClass("mapaClick").css("cursor", "pointer");
      });
    },

    eventos() {
      $("#closeCard").on("click", function (e) {
        e.stopPropagation();
        $("#resultadosCard").addClass("d-none").removeClass("bottom-sheet");
      });

      $(document).on("click", function (e) {
        if (!$(e.target).closest("#resultadosCard").length && !$(e.target).closest(".mapaClick").length) {
          $("#resultadosCard").addClass("d-none").removeClass("bottom-sheet");
        }
      });

      $("#resultadosCard").on("click", function (e) {
        e.stopPropagation();
      });

      $(document).on("click", ".mapaClick", (e) => {
        this.manejarClickMapa(e);
      });

      $(window).on("resize", () => {
        const card = $("#resultadosCard");
        if (!card.hasClass("d-none")) {
          if (isMobile()) this.posicionarBottomSheet();
        }
      });
    },

    manejarClickMapa(e) {
      e.preventDefault();
      e.stopPropagation();

      const path = $(e.target);
      const nombreReal = path.data("nombre");
      const codigoDane = path.data("codigo");

      this.departamentoActual = codigoDane;
      this.municipioActual = "";

      $("#badgeElectoral").text((nombreReal || "RESULTADOS").toUpperCase());

      if (isMobile()) this.posicionarBottomSheet();
      else this.posicionarCard(e.pageX, e.pageY);

      this.obtenerSondeo(codigoDane);
    },

    posicionarBottomSheet() {
      const card = $("#resultadosCard");
      card
        .removeClass("d-none")
        .addClass("bottom-sheet")
        .css({ top: "auto", left: "12px", right: "12px", bottom: "12px" });

      card[0].style.transform = "translateY(10px)";
      card[0].style.opacity = "0";
      requestAnimationFrame(() => {
        card[0].style.transition = "all .18s ease";
        card[0].style.transform = "translateY(0)";
        card[0].style.opacity = "1";
      });
    },

    posicionarCard(x, y) {
      const cardWidth = 360;
      const cardHeight = 520;
      const ww = $(window).width();
      const wh = $(window).height();

      let finalX = x + 15;
      let finalY = y - 15;

      if (finalX + cardWidth > ww) finalX = x - cardWidth - 15;
      if (finalY + cardHeight > wh) finalY = wh - cardHeight - 15;
      if (finalY < 0) finalY = 15;
      if (finalX < 0) finalX = 15;

      $("#resultadosCard")
        .removeClass("d-none")
        .removeClass("bottom-sheet")
        .css({ top: finalY + "px", left: finalX + "px", right: "auto", bottom: "auto" });

      const card = $("#resultadosCard")[0];
      card.style.transform = "scale(.98)";
      card.style.opacity = "0";
      requestAnimationFrame(() => {
        card.style.transition = "all .18s ease";
        card.style.transform = "scale(1)";
        card.style.opacity = "1";
      });
    },

    obtenerSondeo(departamento) {
      $("#resultadosContent").html(montarSpinner());

      const opcionActiva = window.OPCION_ACTIVA_WEB || "sondeo";
      const endpoint = (opcionActiva === "cuestionario") ? "encuesta_mapa_index" : "sondeo_presidencial_mapa";

      const dataRqst = { op: endpoint, departamento_click: departamento };
      if (opcionActiva === "cuestionario" && preguntaSeleccionada > 0) {
        dataRqst.pregunta_id = preguntaSeleccionada;
      }

      $.ajax({
        url: "admin/ajax/rqst.php",
        type: "POST",
        dataType: "json",
        data: dataRqst,
        success: (res) => {
          if (!res || !res.success || !res.votos || res.votos.length === 0) {
            this.mostrarSondeoVacio();
            this.actualizarGrafico([]);
            return;
          }

          this.mostrarSondeo(res.votos);
          this.actualizarGrafico(res.votos);
        },
        error: () => {
          this.mostrarSondeoVacio();
          this.actualizarGrafico([]);
        }
      });
    },

    mostrarSondeo(votos) {
      let total = votos.reduce((t, v) => t + Number(v.total || 0), 0);
      votos.sort((a, b) => Number(b.total || 0) - Number(a.total || 0));

      let html = "";
      votos.forEach((v, idx) => {
        const votosNum = Number(v.total || 0);
        const porcentaje = total > 0 ? ((votosNum / total) * 100).toFixed(1) : 0;

        const id = Number(v.id_candidato || v.candidato_id || v.tbl_candidato_id || 0);
        const color = obtenerColorPorIdOIndice(id, idx);

        const tieneImagen =
          v.foto_url && v.foto_url.trim() !== "" &&
          !v.foto_url.includes("option_default") &&
          !v.foto_url.includes("default.png");

        const imagenHtml = tieneImagen
          ? `<img src="${v.foto_url}" style="width:38px;height:38px;object-fit:cover;border-radius:999px;border:2px solid rgba(32,66,127,.18);">`
          : `<div style="width:38px;height:38px;border-radius:999px;background:${color};display:flex;align-items:center;justify-content:center;border:2px solid rgba(32,66,127,.18);">
               <span style="color:#fff;font-weight:700;font-size:0.9rem;">${(v.nombre_completo || "?").charAt(0).toUpperCase()}</span>
             </div>`;

        html += `
          <div class="p-2 border-bottom d-flex gap-2 align-items-center" style="background:${idx === 0 ? "rgba(32,66,127,.04)" : "transparent"};">
            ${imagenHtml}
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <strong style="color:#0f172a; font-size: 0.9rem;">${v.nombre_completo}</strong>
                ${idx === 0 ? `<span class="badge" style="background:${color}; color:#fff; font-weight:900; border-radius:999px; font-size: 0.7rem;">Líder</span>` : ``}
              </div>
              <div class="d-flex justify-content-between text-muted fw-bold" style="font-size:.8rem;">
                <span>${votosNum} votos</span>
                <span>${porcentaje}%</span>
              </div>
              <div class="progress mt-1" style="height:5px;border-radius:999px;background:rgba(2,6,23,.06);">
                <div class="progress-bar" style="width:${porcentaje}%; background:${color}; border-radius:999px;"></div>
              </div>
            </div>
          </div>
        `;
      });

      $("#resultadosContent").html(html);

      $("#badgeElectoral").css({
        background: "rgba(32,66,127,.06)",
        borderColor: "rgba(32,66,127,.18)"
      });
    },

    actualizarGrafico(votos) {
      const ctx = document.getElementById("graficoVotos");
      if (!ctx) return;

      if (grafico) grafico.destroy();

      const labels = votos.map(v => v.nombre_completo);
      const data = votos.map(v => Number(v.total || 0));

      const bg = votos.map((v, idx) => {
        const id = Number(v.id_candidato || v.candidato_id || v.tbl_candidato_id || 0);
        return obtenerColorPorIdOIndice(id, idx);
      });

      grafico = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [{
            label: "Votos",
            data: data,
            backgroundColor: bg,
            borderRadius: 10
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, grid: { color: "rgba(2,6,23,.08)" } },
            x: { ticks: { font: { weight: "700" } }, grid: { display: false } }
          }
        }
      });
    },

    mostrarSondeoVacio() {
      $("#resultadosContent").html(montarVacio());
    }
  };

  /* =========================
     INIT
  ========================= */
  const opcionActiva = window.OPCION_ACTIVA_WEB || "sondeo";

  if (opcionActiva === "cuestionario") cargarPreguntasCuestionario();
  else cargarGraficoGeneral();

  setTimeout(() => {
    if (opcionActiva !== "cuestionario") pintarMapaSegunGanadores();
    MapaSondeo.hacerMapaClickeable();
  }, 250);

  MapaSondeo.init();

  /* =========================
     CSS extra bottom-sheet
  ========================= */
  if (!document.getElementById("bottomSheetStyle")) {
    const style = document.createElement("style");
    style.id = "bottomSheetStyle";
    style.innerHTML = `
      #resultadosCard.bottom-sheet{
        position: fixed !important;
        width: auto !important;
        max-height: 72vh;
        overflow: hidden;
      }
      #resultadosCard.bottom-sheet .card-body{
        max-height: calc(72vh - 130px);
        overflow: auto;
      }
    `;
    document.head.appendChild(style);
  }
});
