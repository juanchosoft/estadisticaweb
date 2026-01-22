window.accion_visualizar = function () {
    window.location.href = "visualizar.php";
};

window.accion_encuestas = function () {
    window.location.href = "encuesta.php";
};

window.accion_sondeos = function () {
    window.location.href = "sondeo.php";
};

window.accion_estudio = function () {
    window.location.href = "grilla.php";
};


document.addEventListener('DOMContentLoaded', function () {
    const colorNeutro = window.MAPA_COLOR_NEUTRO?.toLowerCase() || "";

    function obtenerColorReal(element) {
        let color = null;

        if (element.hasAttribute("fill")) {
            color = element.getAttribute("fill");
        }

        if (!color && element.hasAttribute("style")) {
            const style = element.getAttribute("style");
            const match = style.match(/fill\s*:\s*([^;]+)/i);
            if (match) color = match[1];
        }

        if (!color) {
            const computed = window.getComputedStyle(element);
            if (computed && computed.fill) color = computed.fill;
        }

        return color ? color.trim().toLowerCase() : null;
    }

    $(document).on('click', '.mapaClick', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const url = $(this).data('url');
        if (!url || url === "#" || url === "") return false;

        const color = obtenerColorReal(this);
        if (!color) return false;
        if (color.includes(colorNeutro)) return false;

        const opcionActiva = $('#opcionActivaWeb').val();

        // Si hay una opción activa, ir directamente a la página correspondiente
        if (opcionActiva === 'sondeo') {
            window.location.href = "sondeo.php";
            return false;
        } else if (opcionActiva === 'estudio') {
            window.location.href = "grilla.php";
            return false;
        } else if (opcionActiva === 'cuestionario') {
            window.location.href = "encuesta.php";
            return false;
        }

        // Si no hay opción activa, mostrar el modal con todas las opciones
        Swal.fire({
            html: `
                <div style="padding:30px 25px; text-align:center;">

                    <h3 style="margin:0 0 10px 0; font-weight:700; color:#13357b;">
                        ¿Qué te gustaría realizar el día de hoy?
                    </h3>

                    <p style="margin:0 0 25px 0; color:#6c757d; font-size:15px;">
                        Selecciona una opción
                    </p>

                    <div style="display:flex; flex-direction:column; gap:14px;">

                        <button type="button"
                            onclick="accion_visualizar()"
                            style="
                                width:100%; padding:12px; border:none; border-radius:10px;
                                background:#13357b; color:#ffffff; font-size:15px; font-weight:600;
                                display:flex; align-items:center; justify-content:center;
                            ">
                            <i class="fas fa-chart-bar" style="margin-right:8px; color:#ffffff;"></i>
                            Visualizar datos de votación
                        </button>

                        <button type="button"
                            onclick="accion_encuestas()"
                            style="
                                width:100%; padding:12px; border:none; border-radius:10px;
                                background:#13357b; color:#ffffff; font-size:15px; font-weight:600;
                                display:flex; align-items:center; justify-content:center;
                            ">
                            <i class="fas fa-list-check" style="margin-right:8px; color:#ffffff;"></i>
                            Responder encuestas
                        </button>

                        <button type="button"
                            onclick="accion_sondeos()"
                            style="
                                width:100%; padding:12px; border:none; border-radius:10px;
                                background:#13357b; color:#ffffff; font-size:15px; font-weight:600;
                                display:flex; align-items:center; justify-content:center;
                            ">
                            <i class="fas fa-person-circle-question" style="margin-right:8px; color:#ffffff;"></i>
                            Responder sondeos
                        </button>

                        <button type="button"
                            onclick="accion_estudio()"
                            style="
                                width:100%; padding:12px; border:none; border-radius:10px;
                                background:#13357b; color:#ffffff; font-size:15px; font-weight:600;
                                display:flex; align-items:center; justify-content:center;
                            ">
                            <i class="fas fa-clipboard-list" style="margin-right:8px; color:#ffffff;"></i>
                            Responder estudio
                        </button>

                    </div>
                </div>
            `,
            showConfirmButton: false,
            width: "550px",
            padding: 0,
            background: "white",
            allowOutsideClick: true,
            customClass: { popup: "swal2-no-padding" }
        });


        return false;
    });
});
