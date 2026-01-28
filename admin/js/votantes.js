$(document).on("ready", init);

var q;
var return_page = "votantes.php";

function init() {
  q = {};
}

var VOTANTES = {
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

  validateData: function () {
    var bValid = true;
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";

    var id = ($("#idVotantes").val() || "").trim();
    var isNew = (id === "");

    if (($("#email").val() || "").trim() === "") bValid = false;
    if (($("#username").val() || "").trim() === "") bValid = false;
    if (isNew && (($("#password").val() || "").trim() === "")) bValid = false;

    if (($("#ideologia").val() || "").trim() === "") bValid = false;
    if (($("#rango_edad").val() || "").trim() === "") bValid = false;
    if (($("#nivel_ingresos").val() || "").trim() === "") bValid = false;
    if (($("#genero").val() || "").trim() === "") bValid = false;
    if (($("#tbl_departamento_id").val() || "").trim() === "") bValid = false;
    if (($("#tbl_municipio_id").val() || "").trim() === "") bValid = false;
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
  savedata: function () {
    q = {};
    q.op = "votantessave";
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
    var pass = v("#password");
    if (pass !== "") {
      q.password = hex_md5(pass);
    } else {
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
          }, 1000);
        } else {
          UTIL.mostrarMensajeError((data?.output?.response?.content) || "No se pudo guardar la información.");
        }
      },
      error: function (xhr) {
        UTIL.cursorNormal();
        UTIL.mostrarMensajeError("Ha ocurrido un error en la operación ejecutada");
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
