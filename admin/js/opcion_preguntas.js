$(document).on("ready", initOpciones);

var q;
var optionCounter = 0;

function initOpciones() {
  console.log("INIT: Función init() ejecutada.");
  q = {};
  // Configura los listeners de los botones de opciones (añadir/eliminar)
  OPCIONES.setupOptionListeners();
  // Establece el estado inicial del mensaje "No hay opciones"
  OPCIONES.updateNoOptionsMessage();
}

var OPCIONES = {
  /**
   * Configura los eventos para los botones de añadir y eliminar opciones.
   */
  setupOptionListeners: function () {
    // Evento para el botón de añadir opción
    $("#addOptionBtn").on("click", OPCIONES.addOptionBtnClick);

    // Evento delegado para los botones de eliminar opción (funciona para botones dinámicos)
    $(document).on("click", ".removeOptionBtn", OPCIONES.removeOptionBtnClick);
    console.log(
      "SETUP_LISTENERS: Event listeners para añadir/eliminar opciones configurados."
    );
  },

  /**
   * Manejador de click para el botón 'Añadir Opción'.
   */
  addOptionBtnClick: function () {
    OPCIONES.addOptionField();
    OPCIONES.updateNoOptionsMessage();
  },

  /**
   * Manejador de click para los botones 'X' de eliminar opción.
   */
  removeOptionBtnClick: function (event) {
    const $closestInputGroup = $(event).closest(".input-group");

    if ($closestInputGroup.length > 0) {
      $closestInputGroup.remove();
    } else {
      console.warn(
        "REMOVE_OPTION_CLICK: No se encontró un '.input-group' padre para remover."
      );
    }

    OPCIONES.updateNoOptionsMessage();
  },

  /**
   * Actualiza la visibilidad del mensaje "No hay opciones de respuesta".
   */
  updateNoOptionsMessage: function () {
    const numOptions = $("#opcionesContainer .input-group").length;
    console.log("UPDATE_MESSAGE: Número de opciones actuales:", numOptions);

    if (numOptions === 0) {
      $("#noOptionsMessage").show();
    } else {
      $("#noOptionsMessage").hide();
    }
  },

  /**
   * Maneja el cambio en el tipo de pregunta para precargar opciones.
   */
  handleTipoPreguntaChange: function (input, dataBD = null) {
    const opcion = $("#" + input).val();

    // Limpiar todas las opciones existentes antes de precargar nuevas
    $("#opcionesContainer").empty();

    let defaultOptions = [];

    switch (opcion) {
      case "Grilla":
        defaultOptions = ["Texto 1", "Texto 2", "Texto 3"];
        break;
      case "Seleccion_Multiple_multiple_respuesta":
        defaultOptions = ["Respuesta 1", "Respuesta 2", "Respuesta 3"];
        $("#divLimiteRespuesta").show(); 
        break;
      case "Preguntas_Ordinales":
        defaultOptions = ["Opcion 1", "Opcion 2", "Opcion 3"];
        break;
      case "Seleccion_Multiple_unica_respuesta":
        defaultOptions = ["Buena", "Mala", "Regular"];
        break;
      case "Dicotomica":
        defaultOptions = ["Sí", "No"];
        break;
      case "Preguntas_Cardinales":
        defaultOptions = [
          "Muy Satisfecho",
          "Satisfecho",
          "Neutral",
          "Insatisfecho",
          "Muy Insatisfecho",
        ];
        break;
      default:
        // No hay opciones por defecto para otros tipos
        $("#divLimiteRespuesta").hide(); 
        break;
    }

    // SI dataBD trae informacion, es por que vienen de la base de datos, por lo que se precargan las opciones
    if (dataBD != null) {
      $.each(dataBD, function (index, text) {
        OPCIONES.addOptionField(text.opcion);
      });
    } else {
      // Añadir las opciones predefinidas
      $.each(defaultOptions, function (index, text) {
        OPCIONES.addOptionField(text);
      });
    }

    // Actualizar el mensaje de "No hay opciones" y la visibilidad del botón "Añadir Opción"
    OPCIONES.updateNoOptionsMessage();
  },
  /**
   * Añade un campo de entrada para una opción de respuesta.
   * @param {string} [initialValue=''] Valor inicial para el campo.
   * @param {number} [optionId=null] ID de la opción (usado para edición, no se guarda en el input visible).
   */
  addOptionField: function (initialValue = "", optionId = null) {
    console.log("ADD_OPTION_FIELD: Añadiendo nuevo campo de opción.");
    optionCounter++;
    const newOptionHtml = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" placeholder="Opción de respuesta" value="${initialValue}" required>
                <button onclick="OPCIONES.removeOptionBtnClick(this)" class="btn btn-outline-danger" type="button">X</button>
            </div>
        `;
    $("#opcionesContainer").append(newOptionHtml);
    OPCIONES.updateNoOptionsMessage();
  },

  clearForm: function (formId) {
    console.log("CLEARFORM: Limpiando formulario:", formId);
    UTIL.clearForm(formId);
    $("#opcionesContainer").empty(); // Limpiar opciones dinámicas
    $("#noOptionsMessage").show(); // Mostrar mensaje
    $("#tipo_pregunta").val("Multiple Choice").trigger("change"); // Reiniciar tipo de pregunta
  },

  /**
   * Muestra un modal con las opciones de respuesta de una pregunta.
   * @param {string} optionsJson Cadena JSON de las opciones.
   * @param {string} questionText Texto de la pregunta.
   */
  showOptionsModal: function (optionsJson, questionText) {
    console.log("SHOW_OPTIONS_MODAL: Abriendo modal para opciones.");
    try {
      const opciones = JSON.parse(optionsJson);
      const modalBodyList = $("#modalOptionsList");
      const modalQuestionText = $("#modalQuestionText");

      modalBodyList.empty();
      modalQuestionText.empty().text(questionText);

      if (opciones.length > 0) {
        $.each(opciones, function (index, opcion) {
          const optionHtml = `
                      <div class="col-md-6 col-xxl-12">
                          <div class="rounded-3 py-2 px-3 bg-body-emphasis d-flex align-items-center mb-3">
                              <span class="fas fa-check text-primary me-3 fs-9"></span>
                              <p class="mb-0 text-body-secondary">${OPCIONES.htmlspecialchars(
                                opcion.texto
                              )}</p>
                          </div>
                      </div>
                  `;
          modalBodyList.append(optionHtml);
        });
      } else {
        modalBodyList.append(
          '<p class="text-muted text-center">No hay opciones para esta pregunta.</p>'
        );
      }

      // Mostramos el modal
      const optionsModal = new bootstrap.Modal(
        document.getElementById("optionsModal")
      );
      optionsModal.show();
    } catch (e) {
      UTIL.mostrarMensajeError("Error al cargar las opciones de la pregunta.");
    }
  },
  htmlspecialchars: function (str) {
    if (typeof str !== "string") {
      return str;
    }
    var map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return str.replace(/[&<>"']/g, function (m) {
      return map[m];
    });
  },
  getOpciones: function () {
    let opciones = [];
    $('#opcionesContainer input[type="text"]').each(function () {
      const opcionTexto = $(this).val().trim();
      if (opcionTexto !== "") {
        opciones.push(opcionTexto);
      }
    });
    return opciones;
  },
};
