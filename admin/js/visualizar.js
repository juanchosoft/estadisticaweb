const CODIGOS_DEPARTAMENTOS = {
    "AMAZONAS": "91",
    "ANTIOQUIA": "05",
    "ARAUCA": "81",
    "ATLÁNTICO": "08",
    "BOLÍVAR": "13",
    "BOYACÁ": "15",
    "CALDAS": "17",
    "CAQUETÁ": "18",
    "CASANARE": "85",
    "CAUCA": "19",
    "CESAR": "20",
    "CHOCÓ": "27",
    "CÓRDOBA": "23",
    "CUNDINAMARCA": "25",
    "GUAINÍA": "94",
    "GUAVIARE": "95",
    "HUILA": "41",
    "LA GUAJIRA": "44",
    "MAGDALENA": "47",
    "META": "50",
    "NARIÑO": "52",
    "NORTE DE SANTANDER": "54",
    "PUTUMAYO": "86",
    "QUINDÍO": "63",
    "RISARALDA": "66",
    "SAN ANDRÉS": "88",
    "SANTANDER": "68",
    "SUCRE": "70",
    "TOLIMA": "73",
    "VALLE DEL CAUCA": "76",
    "VAUPÉS": "97",
    "VICHADA": "99"
};

function normalizarTexto(str) {
    if (!str) return "";
    return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/ñ/gi, "n").trim();
}

const VISUALIZAR = {
    codigoDepartamentoActual: null,
    codigoMunicipioActual: null,
    departamentoActual: "Colombia",
    tipoConsultaActual: null,
    opcionActivaWeb: null,
    timeouts: [100, 500, 1000, 2000],
    graficoGeneral: null,
    ctxGrafico: null,
    preguntasIndex: {},

    init() {
        // Obtener la opción activa de la web
        const opcionInput = document.getElementById("opcionActivaWeb");
        this.opcionActivaWeb = opcionInput ? opcionInput.value : "";

        // Configurar tipo de consulta según opción activa
        if (this.opcionActivaWeb === "sondeo") {
            this.tipoConsultaActual = "sondeo";
        } else if (this.opcionActivaWeb === "cuestionario") {
            this.tipoConsultaActual = "encuesta";
        }

        this.aplicarReintentosMapa();
        this.bindEventosGlobales();
        this.inicializarGrafico();
    },

    inicializarGrafico() {
        const canvas = document.getElementById("graficoDatosGenerales");
        if (!canvas) return;

        this.ctxGrafico = canvas.getContext("2d");

        this.graficoGeneral = new Chart(this.ctxGrafico, {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Votos",
                    data: [],
                    backgroundColor: "rgba(13,110,253,0.5)",
                    borderColor: "rgb(13,110,253)",
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    },

    aplicarReintentosMapa() {
        this.timeouts.forEach(t => {
            setTimeout(() => this.hacerMapaClickeable(), t);
        });
    },

    hacerMapaClickeable() {
        const svg = $("#mapaContainer svg");
        if (svg.length === 0) return;

        const paths = $("#mapaContainer svg path");
        paths.each(function () {
            const p = $(this);
            p.addClass("mapaClick")
                .css({ cursor: "pointer", "pointer-events": "all" })
                .removeAttr("href")
                .removeAttr("data-url");

            const g = p.closest("g");
            const d =
                p.attr("id") ||
                p.attr("data-name") ||
                p.attr("name") ||
                g.attr("id") ||
                g.attr("data-name") ||
                g.find("text").first().text().trim();
            if (d) p.attr("data-departamento", d);
        });

        $("#mapaContainer svg g").each(function () {
            const g = $(this);
            const d = g.attr("id") || g.attr("data-name");
            if (d)
                g.addClass("mapaClick")
                    .attr("data-departamento", d)
                    .css({ cursor: "pointer", "pointer-events": "all" });
        });
    },

    bindEventosGlobales() {
        const self = this;

        // Evento para el select de departamentos
        $("#selectorDepartamento").on("change", function () {
            const codigo = $(this).val();
            const nombre = $(this).find("option:selected").text();

            if (!codigo) {
                $("#encuestaListaContainer").addClass("d-none");
                $("#preguntaListaContainer").addClass("d-none");
                self.limpiarGrafico();
                return;
            }

            self.codigoDepartamentoActual = codigo;
            self.departamentoActual = nombre.toUpperCase().trim();
            self.codigoMunicipioActual = null;

            // Cargar consultas según la opción activa
            if (self.opcionActivaWeb === "sondeo") {
                self.tipoConsultaActual = "sondeo";
                // Para sondeo: cargar y mostrar automáticamente el primer sondeo
                self.obtenerYMostrarSondeoAutomatico();
            } else if (self.opcionActivaWeb === "cuestionario") {
                self.tipoConsultaActual = "encuesta";
                self.obtenerEncuestas();
            }
        });

        // Evento para encuestas
        $("#selectorEncuesta").on("change", function () {
            const encuestaId = $(this).val();
            if (!encuestaId) {
                $("#selectorPreguntaContainer").addClass("d-none").hide();
                $("#resultadosContent").html('<div class="text-center p-4"><p class="text-muted">Seleccione una encuesta</p></div>');
                return;
            }
            self.obtenerPreguntasEncuesta(encuestaId);
        });

        // Evento para preguntas de encuesta
        $("#selectorPregunta").on("change", function () {
            const preguntaId = $(this).val();
            const encuestaId = $("#selectorEncuesta").val();

            if (!preguntaId || !encuestaId) {
                $("#resultadosContent").html('<div class="text-center p-4"><p class="text-muted">Seleccione una pregunta</p></div>');
                return;
            }

            self.obtenerResultadosEncuestaPregunta(encuestaId, preguntaId);
        });

        // Evento para sondeos
        $("#selectorConsulta").on("change", function () {
            const id = $(this).val();
            if (id) self.obtenerResultadosConsulta(id);
        });

        // Click en mapa también selecciona el departamento en el select
        $(document).on("click", ".mapaClick", function (e) {
            self.clickMapa($(this), e);
        });
    },

    clickMapa(elemento, e) {
        e.preventDefault();
        e.stopPropagation();

        const d = elemento.attr("data-departamento") || "Colombia";
        this.departamentoActual = d.toUpperCase().trim();
        const nombreNormalizado = normalizarTexto(this.departamentoActual).toUpperCase();

        console.log("Click mapa - Departamento:", d, "Normalizado:", nombreNormalizado);

        this.codigoDepartamentoActual = null;

        for (const dep in CODIGOS_DEPARTAMENTOS) {
            if (normalizarTexto(dep).toUpperCase() === nombreNormalizado) {
                this.codigoDepartamentoActual = CODIGOS_DEPARTAMENTOS[dep];
                console.log("Código encontrado:", this.codigoDepartamentoActual);
                break;
            }
        }

        // Actualizar el select de departamentos
        if (this.codigoDepartamentoActual) {
            $("#selectorDepartamento").val(this.codigoDepartamentoActual).trigger("change");
        } else {
            console.warn("No se encontró código para:", nombreNormalizado);
        }
    },

    calcularPosicionCard(x, y) {
        const cw = 450;
        const ch = 500;
        const ww = $(window).width();
        const wh = $(window).height();

        let fx = x + 15;
        let fy = y - 15;

        if (fx + cw > ww) fx = x - cw - 15;
        if (fy + ch > wh) fy = wh - ch - 15;
        if (fy < 0) fy = 15;
        if (fx < 0) fx = 15;

        return { x: fx, y: fy };
    },

    obtenerEncuestas() {
        const self = this;
        $.ajax({
            url: "./admin/ajax/rqst.php",
            type: "GET",
            dataType: "json",
            data: {
                op: "listar_encuestas",
                codigo_departamento: this.codigoDepartamentoActual
            },
            success: r => {
                const arr = (r && r.output && r.output.valid) ? (r.output.response || []) : [];

                if (arr.length === 0) {
                    $("#encuestaListaContainer").addClass("d-none");
                    self.limpiarGrafico();
                    $("#textoGraficoInfo").text("No hay encuestas disponibles para este departamento.");
                    return;
                }

                // Mostrar lista elegante de encuestas
                let html = '';
                arr.forEach((e, index) => {
                    const nombre = e.realizada_por || "Encuesta " + e.id;
                    html += `
                        <div class="opcion-item" data-encuesta-id="${e.id}">
                            <span class="numero">${index + 1}</span>
                            <span class="texto">${nombre}</span>
                        </div>
                    `;
                });

                $("#encuestaLista").html(html);
                $("#encuestaListaContainer").removeClass("d-none");
                $("#preguntaListaContainer").addClass("d-none");

                // Evento click en cada encuesta
                $(".opcion-item[data-encuesta-id]").on("click", function() {
                    $(".opcion-item[data-encuesta-id]").removeClass("selected");
                    $(this).addClass("selected");
                    const encuestaId = $(this).data("encuesta-id");
                    self.obtenerPreguntasEncuesta(encuestaId);
                });
            },
            error: () => {
                $("#encuestaListaContainer").addClass("d-none");
                self.limpiarGrafico();
                $("#textoGraficoInfo").text("Error al cargar encuestas.");
            }
        });
    },

    obtenerPreguntasEncuesta(encuestaId) {
        const self = this;
        $.ajax({
            url: "./admin/ajax/rqst.php",
            type: "GET",
            dataType: "json",
            data: {
                op: "listar_preguntas",
                encuesta_id: encuestaId
            },
            success: r => {
                const arr = (r && r.output && r.output.valid) ? (r.output.response || []) : [];

                self.preguntasIndex = {};

                if (arr.length === 0) {
                    $("#preguntaListaContainer").addClass("d-none");
                    self.limpiarGrafico();
                    $("#textoGraficoInfo").text("Esta encuesta no tiene preguntas configuradas.");
                    return;
                }

                // Mostrar lista elegante de preguntas
                let html = '';
                arr.forEach((p, index) => {
                    self.preguntasIndex[p.pregunta_id] = p.texto_pregunta;
                    html += `
                        <div class="opcion-item" data-pregunta-id="${p.pregunta_id}" data-encuesta-id="${encuestaId}">
                            <span class="numero">${index + 1}</span>
                            <span class="texto">${p.texto_pregunta}</span>
                        </div>
                    `;
                });

                $("#preguntaLista").html(html);
                $("#preguntaListaContainer").removeClass("d-none");

                // Evento click en cada pregunta
                $(".opcion-item[data-pregunta-id]").on("click", function() {
                    $(".opcion-item[data-pregunta-id]").removeClass("selected");
                    $(this).addClass("selected");
                    const preguntaId = $(this).data("pregunta-id");
                    const encId = $(this).data("encuesta-id");
                    self.obtenerResultadosEncuestaPregunta(encId, preguntaId);
                });
            },
            error: () => {
                $("#preguntaListaContainer").addClass("d-none");
                self.limpiarGrafico();
                $("#textoGraficoInfo").text("Error al cargar preguntas.");
            }
        });
    },

   obtenerResultadosEncuestaPregunta(encuestaId, preguntaId) {
    const self = this;

    $.ajax({
        url: "./admin/ajax/rqst.php",
        type: "GET",
        dataType: "json",
        data: {
            op: "listar_respuestas",
            encuesta_id: encuestaId,
            pregunta_id: preguntaId,
            codigo_departamento: this.codigoDepartamentoActual,
            codigo_municipio: this.codigoMunicipioActual
        },
        success: rRes => {
            const respuestas = (rRes && rRes.output && rRes.output.valid)
                ? (rRes.output.response || [])
                : [];

            $.ajax({
                url: "./admin/ajax/rqst.php",
                type: "GET",
                dataType: "json",
                data: {
                    op: "contar_respuestas",
                    encuesta_id: encuestaId,
                    pregunta_id: preguntaId,
                    codigo_departamento: this.codigoDepartamentoActual,
                    codigo_municipio: this.codigoMunicipioActual
                },
                success: rCount => {
                    const conteos = (rCount && rCount.output && rCount.output.valid)
                        ? (rCount.output.response || [])
                        : [];

                    self.mostrarResultadosEncuestaPregunta(
                        encuestaId,
                        preguntaId,
                        respuestas,
                        conteos
                    );
                },
                error: () => {
                    self.mostrarErrorResultados("Error al contar respuestas de la encuesta.");
                }
            });
        },
        error: () => {
            self.mostrarErrorResultados("Error al obtener respuestas de la encuesta.");
        }
    });
}
,

   mostrarResultadosEncuestaPregunta(encuestaId, preguntaId, respuestas, conteos) {
    const textoPregunta = this.preguntasIndex[preguntaId] || 'Pregunta seleccionada';

    const totalVotos = conteos.reduce((acc, c) => {
        const n = parseInt(c.cantidad || 0, 10);
        return acc + (isNaN(n) ? 0 : n);
    }, 0);

    let html = '';
    html += `
        <div class="p-3 border-bottom bg-light">
            <h6 class="fw-bold mb-2">${textoPregunta}</h6>
            <p class="small mb-0 text-muted">
                Cantidad total de respuestas: <strong>${totalVotos}</strong>
            </p>
        </div>
    `;

    if (conteos.length > 0) {
        html += '<div class="p-2"><strong>Distribución de respuestas:</strong></div>';

        conteos.forEach(c => {
            const texto = c.texto_opcion || ('Opción ' + c.opcion_id);
            const votos = parseInt(c.cantidad || 0, 10);
            const porcentaje = totalVotos > 0 ? ((votos * 100) / totalVotos).toFixed(1) : 0;

            html += `
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <span>${texto}</span>
                    <span class="badge bg-primary">${votos} voto(s) - ${porcentaje}%</span>
                </div>
            `;
        });
    } else {
        html += `
            <div class="p-4 text-center text-muted">
                No hay respuestas registradas para esta pregunta.
            </div>
        `;
    }

    $('#resultadosContent').html(html);

    const conteoGrafico = {};
    conteos.forEach(c => {
        const texto = c.texto_opcion || ('Opción ' + c.opcion_id);
        conteoGrafico[normalizarTexto(texto)] = parseInt(c.cantidad || 0, 10);
    });

    this.tipoConsultaActual = 'encuesta';
    this.actualizarTextoGrafico(textoPregunta);
    this.pintarGraficoOpciones(conteoGrafico);
}
,

    limpiarGrafico() {
        if (!this.graficoGeneral) return;
        this.graficoGeneral.data.labels = [];
        this.graficoGeneral.data.datasets[0].data = [];
        this.graficoGeneral.update();
        $("#textoGraficoInfo").text("Selecciona un departamento y una consulta para comenzar.");
    },

    obtenerYMostrarSondeoAutomatico() {
        const depClick = this.codigoDepartamentoActual;
        const depUser = window.USER_DEP || null;
        const self = this;

        console.log("obtenerYMostrarSondeoAutomatico - depClick:", depClick, "depUser:", depUser);

        $.ajax({
            url: "./admin/ajax/rqst.php",
            type: "GET",
            dataType: "json",
            data: {
                op: "consultasondeo",
                departamento_click: depClick,
                departamento_usuario: depUser,
                codigo_municipio: this.codigoMunicipioActual
            },
            success: r => {
                console.log("Respuesta consultasondeo:", r);
                if (r.success) {
                    const items = r.data || [];
                    console.log("Sondeos encontrados:", items.length, items);
                    // Si hay al menos un sondeo, obtener sus resultados automáticamente
                    if (items.length > 0) {
                        const primerSondeo = items[0];
                        console.log("Cargando sondeo:", primerSondeo.id, primerSondeo.sondeo);
                        self.obtenerResultadosConsulta(primerSondeo.id);
                    } else {
                        self.limpiarGrafico();
                        $("#textoGraficoInfo").text("No hay sondeos disponibles para este departamento.");
                    }
                } else {
                    self.limpiarGrafico();
                    $("#textoGraficoInfo").text(r.message || "Error al cargar sondeos.");
                }
            },
            error: (xhr, status, error) => {
                console.error("Error AJAX consultasondeo:", status, error);
                self.limpiarGrafico();
                $("#textoGraficoInfo").text("Error de conexión al cargar sondeos.");
            }
        });
    },

    obtenerListaConsultas() {
        const depClick = this.codigoDepartamentoActual;
        const depUser = window.USER_DEP || null;

        $.ajax({
            url: "./admin/ajax/rqst.php",
            type: "GET",
            dataType: "json",
            data: {
                op: "consultasondeo",
                departamento_click: depClick,
                departamento_usuario: depUser,
                codigo_municipio: this.codigoMunicipioActual
            },
            success: r => {
                if (r.success) this.mostrarListaConsultas(r);
                else this.mostrarErrorLista(r.message);
            },
            error: () => this.mostrarErrorLista("Error de conexión")
        });
    },

    mostrarListaConsultas(data) {
        const items = data.data || [];
        let html = '<option value="">Seleccione una pregunta...</option>';

        items.forEach(i => {
            const nombre = i.sondeo || i.nombre || "Sin nombre";
            const texto = nombre.length > 40 ? nombre.substring(0, 40) + "..." : nombre;
            html += `<option value="${i.id}" title="${nombre.replace(/"/g, "&quot;")}">${texto}</option>`;
        });

        $("#selectorConsulta").html(html);

        if (items.length === 0) $("#selectorConsulta").html('<option value="">No hay consultas disponibles</option>');
    },

    mostrarErrorLista(msg) {
        $("#selectorConsulta").html('<option value="">Error</option>');
        $("#resultadosContent").html('<div class="p-4 text-center text-muted">' + msg + "</div>");
    },

    obtenerResultadosConsulta(id) {
        $.ajax({
            url: "./admin/ajax/rqst.php",
            type: "GET",
            dataType: "json",
            data: {
                op: "consultar_respuestas_sondeo",
                id_sondeo: id,
                departamento_click: this.codigoDepartamentoActual,
                sin_fotos: "true"  // No enviar fotos para reducir tamaño de respuesta
            },
            success: r => {
                if (r.success) this.mostrarResultadosConsulta(r);
                else this.mostrarErrorResultados(r.message);
            },
            error: () => this.mostrarErrorResultados("Error de conexión")
        });
    },

    mostrarErrorResultados(msg) {
        $("#resultadosContent").html('<div class="p-4 text-center text-muted">' + msg + "</div>");
    },

    mostrarResultadosConsulta(data) {
        let html = "";
        const s = data.sondeo;
        const opciones = data.opciones || [];
        const respuestasOpc = data.respuestas_opciones || [];
        const candidatos = data.candidatos || [];
        const respuestasCand = data.respuestas_candidatos || [];

        html += '<div class="p-3 border-bottom bg-light">';
        html += '<h6 class="fw-bold mb-2">' + normalizarTexto(s.sondeo) + "</h6>";
        html += '<p class="small text-muted mb-0">' + normalizarTexto(s.descripcion_sondeo || "") + "</p>";
        html += "</div>";

        if (opciones.length > 0) {
            html += '<div class="p-2"><strong>Resultados:</strong></div>';

            opciones.forEach(op => {
                const t = normalizarTexto(op.opcion);
                const r = respuestasOpc.find(x => x.opcion_id == op.id);
                const votos = r ? r.total : 0;

                html += `
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <span>${t}</span>
                        <span class="badge bg-primary">${votos} votos</span>
                    </div>
                `;
            });

            $("#resultadosContent").html(html);

            const conteo = {};
            opciones.forEach(op => {
                const r = respuestasOpc.find(x => x.opcion_id == op.id);
                conteo[normalizarTexto(op.opcion)] = r ? r.total : 0;
            });

            this.tipoConsultaActual = "sondeo";
            this.actualizarTextoGrafico(s.sondeo);
            this.pintarGraficoOpciones(conteo);
            return;
        }

        if (candidatos.length > 0) {
            html += '<div class="p-2"><strong>Resultados por candidatos:</strong></div>';

            candidatos.forEach(c => {
                const nombre = normalizarTexto(c.nombre_completo);
                const r = respuestasCand.find(x => x.candidato_id == c.id);
                const votos = r ? r.total : 0;
                const foto = c.foto_base64 || "img/user_default.png";

                html += `
                    <div class="p-3 border-bottom d-flex align-items-center">
                        <img src="${foto}" style="width:55px;height:55px;border-radius:50%;object-fit:cover;margin-right:10px">
                        <div class="flex-grow-1"><strong>${nombre}</strong></div>
                        <span class="badge bg-success">${votos} votos</span>
                    </div>
                `;
            });

            $("#resultadosContent").html(html);

            const conteo = {};
            candidatos.forEach(c => {
                const r = respuestasCand.find(x => x.candidato_id == c.id);
                conteo[normalizarTexto(c.nombre_completo)] = r ? r.total : 0;
            });

            this.tipoConsultaActual = "sondeo";
            this.actualizarTextoGrafico(s.sondeo);
            this.pintarGraficoOpciones(conteo);
            return;
        }

        $("#resultadosContent").html('<div class="p-4 text-center text-muted">No hay información disponible.</div>');
    },

    actualizarTextoGrafico(nombre) {
        const tipo = this.tipoConsultaActual;
        const departamento = this.departamentoActual;
        const tipoCompleto = tipo === "sondeo" ? "sondeo" : tipo === "encuesta" ? "encuesta" : "consulta";

        const texto = "Resultado del " + tipoCompleto + " del departamento de " + departamento + " sobre: " + nombre;

        $("#textoGraficoInfo").text(texto);
    },

    pintarGraficoOpciones(conteo) {
        if (!this.graficoGeneral) return;

        const labels = Object.keys(conteo);
        const datos = Object.values(conteo);

        this.graficoGeneral.data.labels = labels;
        this.graficoGeneral.data.datasets[0].data = datos;
        this.graficoGeneral.update();
    }
};

$(document).ready(() => VISUALIZAR.init());
