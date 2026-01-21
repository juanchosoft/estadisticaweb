// Botón atrás
function goBack() {
  if (document.referrer && document.referrer !== window.location.href) {
    history.back();
  } else {
    window.location.href = "dashboard.php";
  }
}

function redirectToEncuesta() {
  window.location.href = "encuesta.php";
}
function redirectToSondeo() {
  window.location.href = "sondeo.php";
}
function redirectToEstudio() {
  window.location.href = "grilla.php";
}

/**
 * ✅ Normaliza el SVG del mapa para que NO se vea mocho:
 * - quita width/height rígidos
 * - asegura viewBox
 * - preserva proporción y centra
 */
function normalizeMapaSvg() {
  const container = document.getElementById("mapaContainer");
  if (!container) return;

  const svg = container.querySelector("svg");
  if (!svg) return;

  svg.removeAttribute("width");
  svg.removeAttribute("height");

  if (!svg.getAttribute("viewBox")) {
    // intenta con bbox
    try {
      const box = svg.getBBox();
      if (box && box.width && box.height) {
        svg.setAttribute("viewBox", `${box.x} ${box.y} ${box.width} ${box.height}`);
      } else {
        svg.setAttribute("viewBox", "0 0 1000 800");
      }
    } catch (e) {
      svg.setAttribute("viewBox", "0 0 1000 800");
    }
  }

  svg.setAttribute("preserveAspectRatio", "xMidYMid meet");

  // fuerza responsive real dentro del contenedor
  svg.style.width = "100%";
  svg.style.height = "100%";
  svg.style.display = "block";
}

/**
 * Convierte un color CSS (rgb/rgba/hex) a "rgb(r,g,b)" en minúscula.
 */
function toRgbString(color) {
  if (!color) return "";

  // ya es rgb/rgba
  if (color.startsWith("rgb")) {
    // normaliza a rgb()
    const m = color.match(/rgba?\((\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i);
    if (!m) return color.toLowerCase();
    return `rgb(${m[1]}, ${m[2]}, ${m[3]})`.toLowerCase();
  }

  // hex #rrggbb
  if (color.startsWith("#")) {
    let hex = color.replace("#", "").trim();
    if (hex.length === 3) hex = hex.split("").map(c => c + c).join("");
    if (hex.length !== 6) return color.toLowerCase();

    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgb(${r}, ${g}, ${b})`.toLowerCase();
  }

  return color.toLowerCase();
}

document.addEventListener("DOMContentLoaded", function () {
  const spinner = document.getElementById("spinner");
  if (spinner) spinner.style.display = "none";

  // ✅ Gris neutro típico
  const COLOR_NEUTRO = "rgb(192, 192, 192)"; // #c0c0c0
  const COLOR_NEUTRO_ALT = "rgb(190, 190, 190)"; // por si varía un poco

  // 1) normaliza el svg apenas cargue
  setTimeout(normalizeMapaSvg, 80);
  setTimeout(normalizeMapaSvg, 400);

  // 2) listeners SOLO dentro del contenedor del mapa (no todos los svg del sitio)
  setTimeout(function () {
    const container = document.getElementById("mapaContainer");
    if (!container) {
      console.warn("No existe #mapaContainer");
      return;
    }

    const paths = container.querySelectorAll("svg path");

    paths.forEach((path) => {
      path.style.cursor = "pointer";

      path.addEventListener("click", function () {
        // color real calculado
        const fill = window.getComputedStyle(this).fill || "";
        const rgb = toRgbString(fill);

        // Evitar clic en departamentos grises (neutros)
        if (rgb === COLOR_NEUTRO || rgb === COLOR_NEUTRO_ALT) return;

        const modalEl = document.getElementById("alertModal");
        if (!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      });
    });

    console.log("Mapa listo (scoped) + validación color neutro");
  }, 300);

  // 3) re-normaliza al redimensionar (tablet / rotate)
  window.addEventListener("resize", () => setTimeout(normalizeMapaSvg, 80));
});
