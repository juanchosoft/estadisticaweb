$(document).on("ready", init);
var q;
let selectedCandidates = [];
let opcionesGrilla = [];
const modal = document.getElementById("participantsModal");

function init() {
  q = {};
  // Asocia el evento 'change' y 'input' a la función de filtrado
  $("#tbl_cargo_publico_id").on("change", GRILLA.filterAndShowData);
  $("#codigo_departamento").on("input", GRILLA.filterAndShowData);
  $("#codigo_municipio").on("input", GRILLA.filterAndShowData);
  GRILLA.handleSondeParaCargoPublicoChange(); // Llama a esta función en la carga inicial
}

var return_page = "grilla.php";
var GRILLA = {
  showGrilla: function (itemJson, grillaId) {
    // Verificar si ya votó por esta grilla
    const fila = $(`tr[data-grilla-id="${grillaId}"]`);
    const yaVotado = fila.attr('data-ya-votado') === 'true';

    if (yaVotado) {
      Swal.fire({
        icon: 'warning',
        title: 'Ya votaste en esta grilla',
        text: 'No puedes votar nuevamente en este estudio electoral.',
        confirmButtonText: 'Entendido'
      });
      return;
    }

    // Validar si hay preguntas configuradas antes de continuar
    $.ajax({
      url: 'admin/ajax/rqst.php',
      type: 'POST',
      dataType: 'json',
      data: {
        op: 'grillavalidarpreguntas',
        grilla_id: grillaId
      },
      success: function(response) {
        if (response.output && response.output.valid && response.output.response.tiene_preguntas) {
          // Si hay preguntas, proceder con el envío normal
          GRILLA.enviarFormularioGrilla(itemJson, grillaId);
        } else {
          // No hay preguntas configuradas
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontraron preguntas configuradas en el sistema. Por favor, configure las preguntas desde el panel de administración.',
            confirmButtonText: 'Entendido'
          });
        }
      },
      error: function() {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Ha ocurrido un error al validar las preguntas. Por favor, intente nuevamente.',
          confirmButtonText: 'Entendido'
        });
      }
    });
  },

  enviarFormularioGrilla: function(itemJson, grillaId) {
    // 1. Crear el formulario dinámicamente
    const form = document.createElement("form");
    form.method = "POST"; // Método POST para enviar la data
    form.action = "candidato.php"; // URL de destino

    // Crear un nombre único para la ventana
    const windowName = "grilla_window_" + grillaId;
    form.target = windowName; // Abrir con nombre específico

    // 2. Crear el input oculto para enviar la cadena JSON
    const input = document.createElement("input");
    input.type = "hidden";
    // Usamos una clave descriptiva, por ejemplo: 'registro_data'
    input.name = "registro_data";
    input.value = itemJson;

    // 3. Adjuntar el input al formulario
    form.appendChild(input);

    // 4. Adjuntar el formulario al cuerpo del documento y enviarlo
    document.body.appendChild(form);

    console.log("Enviando datos JSON completos a candidato.php por POST.");

    // Abrir ventana con nombre específico
    const ventanaVotacion = window.open("", windowName);

    form.submit();

    // 5. Limpiar el DOM
    setTimeout(() => {
      document.body.removeChild(form);
    }, 50);

    // 6. Monitorear cuando se cierra la ventana y recargar la página
    const checkWindowClosed = setInterval(() => {
      if (ventanaVotacion && ventanaVotacion.closed) {
        clearInterval(checkWindowClosed);
        console.log("Ventana de votación cerrada, recargando página...");
        // Recargar la página principal para actualizar el estado
        setTimeout(() => {
          window.location.reload();
        }, 500);
      }
    }, 1000);
  },

  showResultados: function (itemJson) {
    // Crear el formulario dinámicamente para enviar a la página de resultados
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "resultados_grilla.php";
    form.target = "_blank"; // Abrir en una nueva pestaña

    // Crear el input oculto para enviar la cadena JSON
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "registro_data";
    input.value = itemJson;

    // Adjuntar el input al formulario
    form.appendChild(input);

    // Adjuntar el formulario al cuerpo del documento y enviarlo
    document.body.appendChild(form);

    console.log("Abriendo vista de resultados en tiempo real...");

    form.submit();

    // Limpiar el DOM
    setTimeout(() => {
      document.body.removeChild(form);
    }, 50);
  },

  editData: function (id) {
    q = {};
    q.op = "grillaget";
    q.id = id;
    UTIL.callAjaxRqstPOST(q, this.editdataHandler);
  },

  editdataHandler: function (data) {
    UTIL.cursorNormal();
    if (data.output.valid) {
      var res = data.output.response[0];
      $("#idGrilla").val(res.id);
      $("#grilla").val(res.grilla);
      $("#descripcion_grilla").val(res.descripcion_grilla);
      $("#tbl_ficha_tecnica_encuesta_id").val(res.tbl_ficha_tecnica_encuesta_id);
      $("#tipo_inferenciales").val(res.tipo_inferenciales);
      $("#aplica_cargos_publicos").val(res.aplica_cargos_publicos);
      $("#tbl_departamento_id").val(res.codigo_departamento);
      $("#tbl_municipio_id").val(res.codigo_municipio);
      $("#tbl_cargo_publico_id").val(res.tbl_cargo_publico_id);
      $("#dtcreate").val(res.dtcreate);
      $("#habilitado").prop('checked', res.habilitado === 'si');

      DEPARTAMENTO.getMunicipiosByDepartamentoIdV2SeteraCodigoMunicipio(
        res.codigo_departamento,
        res.codigo_municipio
      );

      $("#spanEncuesta").text(
        " Editar Información de Grillas N° " + res.id + " - " + res.grilla
      );
      $("#spanModulo").text("");
      UTIL.scrollToTop("formgrilla");

      // Informacion de los candidatos que tiene el grilla
      selectedCandidates = res.candidatos;

      // Manejar cambio en el select "Grilla para cargo público"
      GRILLA.handleSondeParaCargoPublicoChange();
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },
  emptyCells: function () {
    $("#idGrilla").val("");
    $("#grilla").val("");
    $("#descripcion_grilla").val("");
    $("#tbl_ficha_tecnica_encuesta_id").val("");
    $("#aplica_cargos_publicos").val("no");
    $("#habilitado").prop('checked', true); // Por defecto habilitado
    $("#spanEncuesta").text("");
    $("#spanModulo").text("Ingreso de Información de Grillas");
    selectedCandidates = [];
    $("#departamento-field").addClass("d-none");
    $("#municipio-field").addClass("d-none");
    $(".cargo-publico-fields").addClass("d-none");
    $(".table-candidatos").addClass("d-none");

    // Informacion de opciones
    opcionesGrilla = [];
    $(".opciones-preguntas").addClass("d-none");

    GRILLA.handleSondeParaCargoPublicoChange();
  },
  validateData: function () {
    var bValid = true;
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
    if ($("#grilla").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if ($("#tipo_inferenciales").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if ($("#aplica_cargos_publicos").val() === "") {
      bValid = false;
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }

    if (bValid) {
      GRILLA.savedata();
    }
  },
  savedata: function () {
    q = {};
    q.op = "grillasave";
    q.id = $("#idGrilla").val();
    q.grilla = $("#grilla").val();
    q.descripcion_grilla = $("#descripcion_grilla").val();
    q.tbl_ficha_tecnica_encuesta_id = $("#tbl_ficha_tecnica_encuesta_id").val();
    q.tipo_inferenciales = $("#tipo_inferenciales").val();
    q.aplica_cargos_publicos = $("#aplica_cargos_publicos").val();
    q.codigo_departamento = $("#tbl_departamento_id").val();
    q.codigo_municipio = $("#tbl_municipio_id").val();
    q.tbl_cargo_publico_id = $("#tbl_cargo_publico_id").val();
    q.habilitado = $("#habilitado").is(':checked') ? 'si' : 'no';
    q.candidatos = getSelectedCandidatesFromTable();
    q.opciones = OPCIONES.getOpciones();

    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
          UTIL.mostrarMensajeExitoso("Información guardada correctamente");
          setTimeout(function () {
            window.location = return_page;
          }, 1500);
        } else {
          UTIL.mostrarMensajeError(data.output.response.content);
        }
      },
      error: function () {
        UTIL.cursorNormal();
        UTIL.mostrarMensajeError(
          "Ha ocurrido un error en la operación ejecutada"
        );
      },
    });
  },
  deleteData: function (id) {
    if (!confirm("¿Está seguro de que desea eliminar este registro?")) {
      return;
    }
    q = {};
    q.op = "grilladelete";
    q.id = id;
    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
          UTIL.mostrarMensajeExitoso("Registro eliminado correctamente.");
          setTimeout(function () {
            window.location.reload();
          }, 1500);
        } else {
          UTIL.mostrarMensajeError(
            data.output.response.content || "No se pudo eliminar el registro."
          );
        }
      },
      error: function () {
        UTIL.cursorNormal();
        UTIL.mostrarMensajeError(
          "Ha ocurrido un error en la operación ejecutada"
        );
      },
    });
  },
  handleCargoPublicoChange: function (thisElement) {
    const cargoPublicoId = thisElement
      ? $(thisElement).val()
      : $("#tbl_cargo_publico_id").val();

    const departamentoFieldContainer = $(".departamento-municipio-fields").eq(
      0
    );
    const municipioFieldContainer = $(".departamento-municipio-fields").eq(1);

    const presidenteId = "1";
    const senadorId = "2";
    const representanteCamaraId = "3";
    const gobernadorId = "4";
    const diputadoId = "5";
    const alcaldeId = "6";
    const concejalId = "7";

    departamentoFieldContainer.addClass("d-none");
    municipioFieldContainer.addClass("d-none");

    // Eliminar el atributo 'required' si el campo se oculta
    $("#codigo_departamento").prop("required", false);
    $("#codigo_municipio").prop("required", false);

    switch (cargoPublicoId) {
      case presidenteId:
      case senadorId:
        // Ya están ocultos por defecto departamento y municipio
        break;
      case gobernadorId:
      case representanteCamaraId:
      case diputadoId:
        // Mostrar solo departamento
        departamentoFieldContainer.removeClass("d-none");
        $("#codigo_departamento").prop("required", true); // Hacerlo requerido si es visible
        break;
      case alcaldeId:
      case concejalId:
        // Mostrar ambos departamento y municipio
        departamentoFieldContainer.removeClass("d-none");
        municipioFieldContainer.removeClass("d-none");
        $("#codigo_departamento").prop("required", true); // Hacerlo requerido si es visible
        $("#codigo_municipio").prop("required", true); // Hacerlo requerido si es visible
        break;
      default:
        // Por defecto, o si no hay selección válida, ocultar todo
        departamentoFieldContainer.addClass("d-none");
        municipioFieldContainer.addClass("d-none");
        break;
    }
    if ($("#aplica_cargos_publicos").val() == "si") {
      $(".table-candidatos").removeClass("d-none");
      GRILLA.getParticipantesByCargoPublicoId(cargoPublicoId);
    }
  },
  handleSondeParaCargoPublicoChange: function (thisElement) {
    const grillaParaCargoPublicoId = thisElement
      ? $(thisElement).val()
      : $("#aplica_cargos_publicos").val();
    if (grillaParaCargoPublicoId === "si") {
      $(".cargo-publico-fields").removeClass("d-none");
      $("#departamento-field").removeClass("d-none");
      $("#municipio-field").removeClass("d-none");
      $(".table-candidatos").removeClass("d-none");
      GRILLA.handleCargoPublicoChange();
    } else {
      $("#departamento-field").addClass("d-none");
      $("#municipio-field").addClass("d-none");
      $(".cargo-publico-fields").addClass("d-none");
      $(".table-candidatos").addClass("d-none");
    }
  },
  getParticipantesByCargoPublicoId: function (cargoPublicoId) {
    q = {};
    q.op = "participanteget";
    q.cargoPublicoId = cargoPublicoId;
    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "GET",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        q = {};
        UTIL.cursorNormal();
        if (data.output.valid) {
          showDataParticipantes(data.output.response);
        } else {
        }
      },
    });
  },
  // Nuevo método para manejar el filtrado del lado del cliente
  filterAndShowData: function () {
    const cargoPublicoId = $("#tbl_cargo_publico_id").val();

    // Ocultar/mostrar campos de filtro
    const departamentoFieldContainer = $("#departamento-field");
    const municipioFieldContainer = $("#municipio-field");
    departamentoFieldContainer.addClass("d-none");
    municipioFieldContainer.addClass("d-none");

    switch (cargoPublicoId) {
      case "4": // Gobernador
      case "3": // Representante a la Cámara
      case "5": // Diputado
        departamentoFieldContainer.removeClass("d-none");
        break;
      case "6": // Alcalde
      case "7": // Concejal
        departamentoFieldContainer.removeClass("d-none");
        municipioFieldContainer.removeClass("d-none");
        break;
    }

    // Aquí llamamos a la función que obtendrá los datos y los renderizará
    if ($("#aplica_cargos_publicos").val() == "si") {
      GRILLA.getParticipantesByCargoPublicoId(cargoPublicoId);
    }
  },

  verMisRespuestas: function(grillaId) {
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalVerRespuestas'));
    modal.show();

    // Mostrar spinner
    $('#contenedor-respuestas').html(`
      <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2">Cargando tus respuestas...</p>
      </div>
    `);

    // Hacer petición AJAX
    $.ajax({
      url: 'admin/ajax/rqst.php',
      type: 'POST',
      dataType: 'json',
      data: {
        op: 'grillarespuestavotante',
        grilla_id: grillaId
      },
      success: function(response) {
        console.log('Respuesta:', response);

        if (response.output && response.output.valid) {
          const data = response.output.response;
          const fechaRespuesta = data.fecha_respuesta;
          const respuestas = data.respuestas;
          const subpreguntas = data.subpreguntas || [];

          let html = `
            <div class="alert alert-info">
              <i class="fas fa-calendar-alt me-2"></i>
              <strong>Fecha de respuesta:</strong> ${GRILLA.formatearFecha(fechaRespuesta)}
            </div>
          `;

          if (respuestas.length === 0 && subpreguntas.length === 0) {
            html += '<div class="alert alert-warning">No se encontraron respuestas.</div>';
          } else {
            respuestas.forEach((candidatoData, index) => {
              html += `
                <div class="card mb-3">
                  <div class="card-header" style="background-color: #f8f9fa;">
                    <div class="d-flex align-items-center">
                      ${candidatoData.foto
                        ? `<img src="assets/img/admin/${candidatoData.foto}" alt="${candidatoData.candidato}" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">`
                        : '<i class="fas fa-user-circle fa-3x me-3 text-secondary"></i>'
                      }
                      <h5 class="mb-0">${candidatoData.candidato}</h5>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-sm table-striped">
                        <thead>
                          <tr>
                            <th style="width: 50%;">Pregunta</th>
                            <th style="width: 50%;">Respuesta</th>
                          </tr>
                        </thead>
                        <tbody>
              `;

              candidatoData.respuestas.forEach(respuesta => {
                // Determinar si es subpregunta para aplicar estilo diferente
                const esSubpregunta = respuesta.tipo_pregunta === 'subpregunta';
                const classIndentacion = esSubpregunta ? 'ps-4' : '';
                const iconoPregunta = esSubpregunta ? '<i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2"></i>' : '';
                const colorBadge = esSubpregunta ? 'bg-secondary' : 'bg-primary';

                html += `
                  <tr${esSubpregunta ? ' style="background-color: #f8f9fa;"' : ''}>
                    <td class="fw-bold ${classIndentacion}">${iconoPregunta}${respuesta.pregunta}</td>
                    <td>
                      <span class="badge ${colorBadge}">${respuesta.respuesta}</span>
                    </td>
                  </tr>
                `;
              });

              html += `
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              `;
            });

            // Mostrar subpreguntas globales si existen
            if (subpreguntas.length > 0) {
              html += `
                <div class="card mb-3 border-secondary">
                  <div class="card-header" style="background-color: #6c757d; color: white;">
                    <h5 class="mb-0"><i class="fas fa-poll me-2"></i>Preferencia Electoral</h5>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-sm table-striped">
                        <thead>
                          <tr>
                            <th style="width: 60%;">Pregunta</th>
                            <th style="width: 40%;">Candidato Seleccionado</th>
                          </tr>
                        </thead>
                        <tbody>
              `;

              subpreguntas.forEach(subpregunta => {
                html += `
                  <tr style="background-color: #f8f9fa;">
                    <td class="fw-bold ps-4">
                      <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2"></i>
                      ${subpregunta.pregunta}
                    </td>
                    <td>
                      <span class="badge bg-success">${subpregunta.respuesta}</span>
                    </td>
                  </tr>
                `;
              });

              html += `
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              `;
            }
          }

          $('#contenedor-respuestas').html(html);
        } else {
          $('#contenedor-respuestas').html(`
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Error al cargar las respuestas.
            </div>
          `);
        }
      },
      error: function(xhr, status, error) {
        console.error('Error:', error);
        $('#contenedor-respuestas').html(`
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Error de conexión. Por favor, intenta nuevamente.
          </div>
        `);
      }
    });
  },

  formatearFecha: function(fechaStr) {
    const fecha = new Date(fechaStr);
    const opciones = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    };
    return fecha.toLocaleDateString('es-ES', opciones);
  }
};
// Función para actualizar la visualización de los IDs seleccionados
function updateSelectedCandidatesDisplay() {
  const displayElement = document.getElementById("selected-ids");
  if (displayElement) {
    displayElement.textContent = JSON.stringify(selectedCandidates, null, 2);
  }
}

// Manejador del evento onchange para los checkboxes
// 'el' es el elemento checkbox que disparó el evento
function handleCheckboxChange(el) {
  const candidateId = parseInt(el.value);
  if (el.checked) {
    if (!selectedCandidates.includes(candidateId)) {
      selectedCandidates.push(candidateId);
    }
  } else {
    selectedCandidates = selectedCandidates.filter((id) => id !== candidateId);
  }
  updateSelectedCandidatesDisplay();
}

// Función principal para renderizar la tabla con la data de participante
function showDataParticipantes(data) {
  if (data.length === 0) {
    data = selectedCandidates;
  }

  const tbody = document.querySelector("#candidatosTable tbody");
  if (!tbody) {
    console.error("Elemento <tbody> con ID 'candidatosTable' no encontrado.");
    return;
  }

  if (data.length === 0) {
    return;
  }

  const cargoId = $("#tbl_cargo_publico_id").val();
  const departamentoId = $("#tbl_departamento_id").val();
  const municipioId = $("#tbl_municipio_id").val();
  let cargoPublicoId = $("#tbl_cargo_publico_id").val();

  let filteredData = data;

  let validarDepartamento = false;
  let validarMunicipio = false;

  switch (cargoPublicoId) {
    case "4": // Gobernador
    case "3": // Representante a la Cámara
    case "5": // Diputado
      departamentoFieldContainer.removeClass("d-none");
      break;
    case "6": // Alcalde
    case "7": // Concejal
      validarDepartamento = true;
      validarMunicipio = true;
      break;
  }

  if (cargoId) {
    filteredData = filteredData.filter(
      (item) =>
        item.habilitado &&
        parseInt(item.tbl_cargo_publico_id) === parseInt(cargoId)
    );
  }

  if (departamentoId && validarDepartamento) {
    filteredData = filteredData.filter(
      (item) =>
        item.codigo_departamento &&
        item.habilitado &&
        item.codigo_departamento.toString() === departamentoId.toString()
    );
  }

  if (municipioId && validarMunicipio) {
    filteredData = filteredData.filter(
      (item) =>
        item.codigo_municipio &&
        item.habilitado &&
        item.codigo_municipio.toString() === municipioId.toString()
    );
  }

  let tableRows = "";
  const isEditing = $("#idGrilla").val() > 0;

  if (filteredData.length === 0) {
    tableRows = `<tr><td colspan="9" class="py-4 text-center text-gray-500">No se encontraron candidatos para los criterios seleccionados.</td></tr>`;
  } else {
    filteredData.forEach((item) => {
      const shouldBeChecked =
        isEditing &&
        selectedCandidates.some(
          (candidate) => parseInt(candidate.id) === parseInt(item.id)
        );

      tableRows += `
        <tr>
          <td class="py-3 px-4">
            <input
              onchange="handleCheckboxChange(this)"
              type="checkbox"
              value="${item.id}"
              ${shouldBeChecked ? "checked" : ""}
              class="form-checkbox h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300"
            >
          </td>
          <td class="py-3 px-4">
            ${
              item.foto
                ? `<img width="60" height="60" src="assets/img/admin/${item.foto}" alt="Foto de ${item.nombre_completo}" class="h-8 w-8 rounded-full object-cover">`
                : "N/A"
            }
          </td>
          <td class="py-3 px-4">${item.nombre_completo}</td>
          <td class="py-3 px-4">${item.cargo_publico}</td>
          <td class="py-3 px-4">${item.nombres_partidos}</td>
          <td class="py-3 px-4">${item.nombre_municipio || "N/A"}</td>
          <td class="py-3 px-4">${item.nombre_departamento || "N/A"}</td>
        </tr>
      `;
    });
  }

  tbody.innerHTML = tableRows;
  updateSelectedCandidatesDisplay();
}

function showParticipantsModal(candidatosDataString, grilla) {
  try {
    const candidatos = candidatosDataString;
    const tableBody = document.querySelector("#candidatosModalTable .list");
    tableBody.innerHTML = ""; // Limpia las filas anteriores

    candidatos.forEach((item) => {
      const row = document.createElement("tr");
      row.innerHTML = `
              <td class="py-3 px-4 border border-gray-300">
                  ${
                    item.foto
                      ? `<img width="60" height="60" src="assets/img/admin/${item.foto}" alt="Foto de ${item.nombre_completo}" class="h-12 w-12 rounded-full object-cover">`
                      : "N/A"
                  }
              </td>
              <td class="py-3 px-4 border border-gray-300">${
                item.nombre_completo
              }</td>
              <td class="py-3 px-4 border border-gray-300">${
                item.cargo_publico
              }</td>
              <td class="py-3 px-4 border border-gray-300">${
                item.nombres_partidos
              }</td>
              <td class="py-3 px-4 border border-gray-300">${
                item.nombre_municipio || "N/A"
              }</td>
              <td class="py-3 px-4 border border-gray-300">${
                item.nombre_departamento || "N/A"
              }</td>
          `;
      tableBody.appendChild(row);
    });

    $("#participantsModal").modal("show");
    $("#grilla-title").text(grilla);
  } catch (error) {
    console.error("Error al analizar los datos de los candidatos:", error);
  }
}

function hideParticipantsModal() {
  $("#participantsModal").modal("hide");
}

window.onclick = function (event) {
  if (event.target === modal) {
    hideParticipantsModal();
  }
};

function getSelectedCandidatesFromTable() {
  const checkboxes = document.querySelectorAll(
    '#table-container input[type="checkbox"]:checked'
  );
  const selectedIds = Array.from(checkboxes).map((checkbox) =>
    parseInt(checkbox.value)
  );
  return selectedIds;
}
