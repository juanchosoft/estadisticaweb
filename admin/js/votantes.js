$(document).on("ready", init);

var q;
var return_page = "votantes.php";

function init() {
  q = {};
}

var VOTANTES = {

  // =========================
  // EDITAR
  // =========================
  editData: function (id) {
    q = {};
    q.op = "votantesget";
    q.id = id;
    UTIL.callAjaxRqstPOST(q, this.editdataHandler);
  },

  editdataHandler: function (data) {
    UTIL.cursorNormal();

    if (!data || !data.output || !data.output.valid) {
      UTIL.mostrarMensajeError((data?.output?.response?.content) || "No se pudo cargar la información.");
      return;
    }

    var res = data.output.response[0] || {};

    $("#idVotantes").val(res.id || "");
    $("#nombre_completo").val(res.nombre_completo || "");
    $("#ideologia").val(res.ideologia || "");
    $("#rango_edad").val(res.rango_edad || "");
    $("#nivel_ingresos").val(res.nivel_ingresos || "");
    $("#email").val(res.email || "");
    $("#username").val(res.username || "");

    // ✅ NO rellenar password con lo que venga de BD (normalmente viene hash)
    $("#password").val("");

    $("#genero").val(res.genero || "");
    $("#tbl_departamento_id").val(res.codigo_departamento || "");

    $("#tbl_departamento_id").trigger("change");
    DEPARTAMENTO.getMunicipios();

    setTimeout(() => {
      $("#tbl_municipio_id").val(res.codigo_municipio || "");
    }, 500);

    $("#comuna").val(res.comuna || "");
    $("#barrio").val(res.barrio || "");

    $("#nivel_educacion").val(res.nivel_educacion || "");
    $("#ocupacion").val(res.ocupacion || "");
    $("#estado").val(res.estado || "");

    $("#spanEncuesta").text(" Editar Ingreso de Votante N° " + (res.id || ""));
    $("#spanModulo").text("");
  },

  // =========================
  // VALIDAR
  // =========================
  validateData: function () {
    var bValid = true;
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";

    var id = ($("#idVotantes").val() || "").trim();
    var isNew = (id === "");

    // Obligatorios según tu lógica de formulario
    if (($("#nombre_completo").val() || "").trim() === "") bValid = false;

    // ⚠️ En tu PHP email NO es obligatorio. Si lo quieres obligatorio, deja esta línea.
    // Si NO lo quieres obligatorio, comenta esta línea.
    if (($("#email").val() || "").trim() === "") bValid = false;

    if (($("#username").val() || "").trim() === "") bValid = false;

    // Password obligatorio solo para nuevos
    if (isNew && (($("#password").val() || "").trim() === "")) bValid = false;

    if (($("#ideologia").val() || "").trim() === "") bValid = false;
    if (($("#rango_edad").val() || "").trim() === "") bValid = false;
    if (($("#nivel_ingresos").val() || "").trim() === "") bValid = false;
    if (($("#genero").val() || "").trim() === "") bValid = false;
    if (($("#tbl_departamento_id").val() || "").trim() === "") bValid = false;
    if (($("#tbl_municipio_id").val() || "").trim() === "") bValid = false;

    // Ocupación: en tu PHP NO es obligatorio. Si lo quieres obligatorio, deja esto.
    if (($("#ocupacion").val() || "").trim() === "") bValid = false;

    if (($("#estado").val() || "").trim() === "") bValid = false;

    if (!$("#politica").is(":checked")) {
      bValid = false;
      msj = "Debe aceptar la política de privacidad";
    }

    if (!bValid) {
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }

    VOTANTES.savedata();
  },

  // =========================
  // GUARDAR
  // =========================
  savedata: function () {
    q = {};
    q.op = "votantessave";

    // helpers
    function v(id) { return (($(id).val() ?? "") + "").trim(); }

    q.id                 = v("#idVotantes");
    q.dtcreate           = v("#dtcreate");
    q.tbl_usuario_id      = v("#tbl_usuario_id");
    q.nombre_completo     = v("#nombre_completo");
    q.ideologia           = v("#ideologia");
    q.rango_edad          = v("#rango_edad");
    q.nivel_ingresos      = v("#nivel_ingresos");
    q.email               = v("#email");
    q.username            = v("#username");

    // ✅ IMPORTANTE: enviar password en claro.
    // El backend (PHP) lo hashea con Util::make_hash_pass().
    var pass = v("#password");
    if (pass !== "") {
      q.password = pass;
    } else {
      // Si está vacío NO lo envíes (para que al editar no lo borre)
      q.password = "";
    }

    q.genero              = v("#genero");
    q.codigo_departamento = v("#tbl_departamento_id");
    q.codigo_municipio    = v("#tbl_municipio_id");
    q.comuna              = v("#comuna");
    q.barrio              = v("#barrio");
    q.nivel_educacion     = v("#nivel_educacion");
    q.ocupacion           = v("#ocupacion");
    q.estado              = v("#estado");

    // estos no son necesarios, pero si existen no hacen daño
    q.email_verificado       = v("#email_verificado");
    q.ultimo_acceso          = v("#ultimo_acceso");
    q.intentos_login         = v("#intentos_login");
    q.cuenta_bloqueada_hasta = v("#cuenta_bloqueada_hasta");
    q.dtupdate               = v("#dtupdate");
    q.ip_registro            = v("#ip_registro");
    q.user_agent             = v("#user_agent");

    UTIL.cursorBusy();

    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();

        if (data && data.output && data.output.valid) {
          UTIL.mostrarMensajeExitoso("Información guardada correctamente");
          setTimeout(function () {
            window.location = "index.php";
          }, 1200);
        } else {
          UTIL.mostrarMensajeError((data?.output?.response?.content) || "No se pudo guardar la información.");
        }
      },
      error: function (xhr) {
        UTIL.cursorNormal();
        UTIL.mostrarMensajeError("Ha ocurrido un error en la operación ejecutada");
        // Si quieres ver el error real:
        // console.log(xhr.responseText);
      }
    });
  },

  // =========================
  // LIMPIAR
  // =========================
  emptyCells: function () {
    $("#idVotantes").val("");
    $("#spanEncuesta").text("");
    $("#ideologia").val("");
    $("#rango_edad").val("");
    $("#nivel_ingresos").val("");
    $("#email").val("");
    $("#username").val("");
    $("#password").val("");
    $("#genero").val("");
    $("#nivel_educacion").val("");
    $("#ocupacion").val("");
    $("#estado").val("");
    $("#nombre_completo").val("");
    $("#spanModulo").text("Ingreso y listado de Votantes");
  }
};
