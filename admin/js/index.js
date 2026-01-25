// Colores por defecto (se sobreescriben con los dinámicos del mapa)
let ColoresCandidatos = {
  1: "#1f77b4",
  2: "#ff7f0e",
  3: "#2ca02c",
  4: "#d62728"
};

// Usar colores dinámicos si están disponibles
if (typeof window.ColoresCandidatosDinamicos !== 'undefined' && window.ColoresCandidatosDinamicos) {
  ColoresCandidatos = window.ColoresCandidatosDinamicos;
}

const COLOR_TEMA = "#20427F";

// Paleta de colores para gráficos (usada cuando no hay colores específicos)
const PALETA_COLORES = [
    "#1f77b4", // azul
    "#ff7f0e", // naranja
    "#2ca02c", // verde
    "#d62728", // rojo
    "#9467bd", // morado
    "#8c564b", // marrón
    "#e377c2", // rosa
    "#7f7f7f", // gris
    "#bcbd22", // amarillo verdoso
    "#17becf"  // cian
];


$(document).ready(function () {

  let grafico = null;
  let graficoGeneral = null;

  /* =========================
     MAPA: pintar ganadores
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
            path.css("fill", "url(#rayas)"); // empate
          } else {
            const color = ColoresCandidatos[ganador.ganador] || "#999";
            path.css("fill", color);
          }
        });
      }
    });
  }

  /* =========================
     GRAFICO GENERAL PRO
  ========================= */
  function cargarGraficoGeneral() {
    const opcionActiva = window.OPCION_ACTIVA_WEB || 'sondeo';

    // Si es encuesta/cuestionario, usar endpoint diferente
    const endpoint = (opcionActiva === 'cuestionario') ? 'encuesta_general_index' : 'sondeo_general_index';

    $.ajax({
      url: "admin/ajax/rqst.php",
      type: "POST",
      dataType: "json",
      data: { op: endpoint },
      success: function (res) {
        console.log('cargarGraficoGeneral - respuesta:', res);
        if (!res || !res.success || !res.votos) {
          console.log('cargarGraficoGeneral - respuesta inválida o sin votos');
          return;
        }

        const canvas = document.getElementById('graficoGeneral');
        if (!canvas) return;

        if (graficoGeneral) graficoGeneral.destroy();

        function dividirEnTresLineas(nombre) {
          const palabras = (nombre || "").split(" ").filter(Boolean);
          if (palabras.length <= 1) return [palabras[0] || ""];
          if (palabras.length === 2) return [palabras[0], palabras[1]];

          let linea1 = palabras[0];
          let linea2 = palabras[1] + (palabras[2] ? " " + palabras[2] : "");
          let linea3 = palabras.slice(3).join(" ");

          const lineas = [linea1, linea2];
          if (linea3.trim() !== "") lineas.push(linea3);
          return lineas;
        }

        const labels = res.votos.map(v => dividirEnTresLineas(v.nombre_completo));
        const data = res.votos.map(v => Number(v.total || 0));

        // Verificar qué imágenes son válidas
        const imagenesValidas = res.votos.map(v => {
          const url = v.foto_url || '';
          return url.trim() !== '' &&
                 !url.includes('option_default') &&
                 !url.includes('default.png');
        });

        const imgs = res.votos.map((v, i) => {
          if (imagenesValidas[i]) {
            const img = new Image();
            img.src = v.foto_url;
            return img;
          }
          return null;
        });

        // Nombres para iniciales cuando no hay imagen
        const nombres = res.votos.map(v => v.nombre_completo || '?');

        // Paleta (puedes dejarlo así o mapear por candidato si viene ID)
        const coloresAsignados = res.votos.map((v, i) => {
          const id = v.candidato_id || v.id;
          return ColoresCandidatos[id] || PALETA_COLORES[i % PALETA_COLORES.length];
        });

        const fotoLabelPlugin = {
          id: 'fotoLabelPlugin',
          afterDraw(chart) {
            const ctx = chart.ctx;
            const yAxis = chart.scales.y;

            ctx.textBaseline = "middle";
            ctx.textAlign = "left";
            ctx.font = "12px system-ui, -apple-system, Segoe UI, Roboto, Arial";

            chart.data.labels.forEach((label, i) => {
              const y = yAxis.getPixelForTick(i);
              const img = imgs[i];
              const imgY = y - 15;

              if (img && imagenesValidas[i]) {
                // Foto válida
                try { ctx.drawImage(img, 5, imgY, 30, 30); } catch (e) {}
              } else {
                // Círculo con inicial
                const color = coloresAsignados[i] || "#1f77b4";
                ctx.beginPath();
                ctx.arc(20, y, 15, 0, 2 * Math.PI);
                ctx.fillStyle = color;
                ctx.fill();
                ctx.closePath();

                // Inicial
                ctx.fillStyle = "#fff";
                ctx.font = "bold 14px system-ui, -apple-system, Segoe UI, Roboto, Arial";
                ctx.textAlign = "center";
                const inicial = (nombres[i] || '?').charAt(0).toUpperCase();
                ctx.fillText(inicial, 20, y + 1);
                ctx.textAlign = "left";
                ctx.font = "12px system-ui, -apple-system, Segoe UI, Roboto, Arial";
              }

              // Texto multilínea
              label.forEach((line, lineIndex) => {
                ctx.fillStyle = "#0f172a";
                ctx.fillText(line, 40, y + (lineIndex * 12) - 6);
              });
            });
          }
        };

        graficoGeneral = new Chart(canvas, {
          type: 'bar',
          plugins: [fotoLabelPlugin],
          data: {
            labels: labels,
            datasets: [{
              label: "",
              data: data,
              backgroundColor: coloresAsignados,
              borderRadius: 10
            }]
          },
          options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: true } },
            scales: {
              x: { beginAtZero: true, grid: { color: "rgba(2,6,23,.08)" } },
              y: { ticks: { display: false }, grid: { display: false } }
            },
            layout: { padding: { left: 86 } }
          }
        });
      },
      error: function (xhr, status, error) {
        console.error('Error en AJAX cargarGraficoGeneral:', status, error, xhr.responseText);
      },
      complete: function() {
        console.log('cargarGraficoGeneral - petición completada');
      }
    });
  }

  /* =========================
     UI helpers (card)
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

  // Función para obtener color por ID o índice
  function obtenerColorPorIdOIndice(id, index) {
    return ColoresCandidatos[id] || PALETA_COLORES[index % PALETA_COLORES.length] || COLOR_TEMA;
  }

  /* =========================
     MAIN: init
  ========================= */
  cargarGraficoGeneral();

  // Espera a que el SVG esté montado (por include PHP)
  setTimeout(() => {
    pintarMapaSegunGanadores();
    MapaSondeo.hacerMapaClickeable();
  }, 250);

  const MapaSondeo = {

    departamentoActual: "",
    municipioActual: "",

    init() {
      this.eventos();
    },

    hacerMapaClickeable() {
      $('#mapaContainer svg path').each(function () {
        $(this).addClass('mapaClick').css('cursor', 'pointer');
      });
    },

    eventos() {
      // cerrar card
      $('#closeCard').on('click', function (e) {
        e.stopPropagation();
        $('#resultadosCard').addClass('d-none').removeClass('bottom-sheet');
      });

      // click afuera
      $(document).on('click', function (e) {
        if (!$(e.target).closest('#resultadosCard').length &&
            !$(e.target).closest('.mapaClick').length) {
          $('#resultadosCard').addClass('d-none').removeClass('bottom-sheet');
        }
      });

      // click dentro card
      $('#resultadosCard').on('click', function (e) {
        e.stopPropagation();
      });

      // click en mapa
      $(document).on('click', '.mapaClick', (e) => {
        this.manejarClickMapa(e);
      });

      // si cambia tamaño, reacomoda si está visible
      $(window).on('resize', () => {
        const card = $('#resultadosCard');
        if (!card.hasClass('d-none')) {
          if (isMobile()) {
            this.posicionarBottomSheet();
          }
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

      // badge
      $('#badgeElectoral').text((nombreReal || "RESULTADOS").toUpperCase());

      // mostrar card
      if (isMobile()) {
        this.posicionarBottomSheet();
      } else {
        this.posicionarCard(e.pageX, e.pageY);
      }

      // cargar
      this.obtenerSondeo(codigoDane);
    },

    posicionarBottomSheet() {
      const card = $('#resultadosCard');
      card
        .removeClass('d-none')
        .addClass('bottom-sheet')
        .css({
          top: "auto",
          left: "12px",
          right: "12px",
          bottom: "12px"
        });

      // animación suave
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

      $('#resultadosCard')
        .removeClass('d-none')
        .removeClass('bottom-sheet')
        .css({ top: finalY + "px", left: finalX + "px", right: "auto", bottom: "auto" });

      // animación suave
      const card = $('#resultadosCard')[0];
      card.style.transform = "scale(.98)";
      card.style.opacity = "0";
      requestAnimationFrame(() => {
        card.style.transition = "all .18s ease";
        card.style.transform = "scale(1)";
        card.style.opacity = "1";
      });
    },

    obtenerSondeo(departamento) {

      $('#resultadosContent').html(montarSpinner());

      const opcionActiva = window.OPCION_ACTIVA_WEB || 'sondeo';
      const endpoint = (opcionActiva === 'cuestionario') ? 'encuesta_mapa_index' : 'sondeo_presidencial_mapa';

      $.ajax({
        url: "admin/ajax/rqst.php",
        type: "POST",
        dataType: "json",
        data: {
          op: endpoint,
          departamento_click: departamento
        },
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

        // intenta detectar id del candidato para color individual
        const id = Number(v.id_candidato || v.candidato_id || v.tbl_candidato_id || 0);
        const color = obtenerColorPorIdOIndice(id, idx);

        // Verificar si hay una foto válida (no vacía y no un placeholder genérico)
        const tieneImagen = v.foto_url &&
                           v.foto_url.trim() !== '' &&
                           !v.foto_url.includes('option_default') &&
                           !v.foto_url.includes('default.png');

        // Si hay imagen, mostrarla; si no, mostrar un círculo con el color
        const imagenHtml = tieneImagen
          ? `<img src="${v.foto_url}" style="width:38px;height:38px;object-fit:cover;border-radius:999px;border:2px solid rgba(32,66,127,.18);">`
          : `<div style="width:38px;height:38px;border-radius:999px;background:${color};display:flex;align-items:center;justify-content:center;border:2px solid rgba(32,66,127,.18);">
               <span style="color:#fff;font-weight:700;font-size:0.9rem;">${(v.nombre_completo || '?').charAt(0).toUpperCase()}</span>
             </div>`;

        html += `
          <div class="p-2 border-bottom d-flex gap-2 align-items-center" style="background:${idx===0 ? "rgba(32,66,127,.04)" : "transparent"};">
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

      $('#resultadosContent').html(html);

      // pinta badge del card con color ganador
      $('#badgeElectoral')
        .css({ background: "rgba(32,66,127,.06)", borderColor: "rgba(32,66,127,.18)" });
    },

    actualizarGrafico(votos) {
      const ctx = document.getElementById('graficoVotos');
      if (!ctx) return;

      if (grafico) grafico.destroy();

      const labels = votos.map(v => v.nombre_completo);
      const data = votos.map(v => Number(v.total || 0));

      // colores por candidato si hay id, o por índice
      const bg = votos.map((v, idx) => {
        const id = Number(v.id_candidato || v.candidato_id || v.tbl_candidato_id || 0);
        return obtenerColorPorIdOIndice(id, idx);
      });

      grafico = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Votos',
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
      $('#resultadosContent').html(montarVacio());
    }
  };

  MapaSondeo.init();

  /* =========================
     CSS extra para bottom-sheet
     (se agrega aquí por si no quieres tocar CSS)
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
