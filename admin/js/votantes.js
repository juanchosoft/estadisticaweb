$(document).on("ready", init);
var q;

function init() {
    q = {};
}

var return_page = "votantes.php";
var VOTANTES = {
  editData: function (id) {
    q = {};
    q.op = "votantesget";
    q.id = id;
    UTIL.callAjaxRqstPOST(q, this.editdataHandler);
  },

  editdataHandler: function (data) {
    UTIL.cursorNormal();
    if (data.output.valid) {
      var res = data.output.response[0];
      $("#idVotantes").val(res.id);
      $("#nombre_completo").val(res.nombre_completo);
      $("#ideologia").val(res.ideologia);
      $("#rango_edad").val(res.rango_edad);
      $("#nivel_ingresos").val(res.nivel_ingresos);
      $("#email").val(res.email);
      $("#username").val(res.username);
      $("#password2").val(res.password);
      $("#genero").val(res.genero);
      $("#tbl_departamento_id").val(res.codigo_departamento);

      $("#tbl_departamento_id").trigger("change");
      DEPARTAMENTO.getMunicipios();

      setTimeout(() => {
        $("#tbl_municipio_id").val(res.codigo_municipio);
      }, 500);

      $("#comuna").val(res.comuna);
      $("#barrio").val(res.barrio);

      $("#nivel_educacion").val(res.nivel_educacion);
      $("#ocupacion").val(res.ocupacion);
      $("#estado").val(res.estado);

      $("#spanEncuesta").text(" Editar Ingreso de Votante N° " + res.id);
      $("#spanModulo").text("");
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },

    validateData: function () {
    var bValid = true;
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
    
    // Validar todos los campos obligatorios
    if ($("#nombre_completo").val() === "") {
        bValid = false;
    }
    if ($("#email").val() === "") {
        bValid = false;
    }
    if ($("#username").val() === "") {
        bValid = false;
    }
    if ($("#password").val() === "" && $("#idVotantes").val() === "") {
        bValid = false; // Password solo obligatorio para nuevos registros
    }
    if ($("#ideologia").val() === "") {
        bValid = false;
    }
    if ($("#rango_edad").val() === "") {
        bValid = false;
    }
    if ($("#nivel_ingresos").val() === "") {
        bValid = false;
    }
    if ($("#genero").val() === "") {
        bValid = false;
    }
    if ($("#tbl_departamento_id").val() === "") {
        bValid = false;
    }
    if ($("#tbl_municipio_id").val() === "") {
        bValid = false;
    }
    if ($("#ocupacion").val() === "") {
        bValid = false;
    }
    if ($("#estado").val() === "") {
        bValid = false;
    }
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
    q.id = $("#idVotantes").val();
    q.dtcreate = $("#dtcreate").val();
    q.tbl_usuario_id = $("#tbl_usuario_id").val();
    q.nombre_completo = $("#nombre_completo").val();
    q.ideologia = $("#ideologia").val();
    q.rango_edad = $("#rango_edad").val();
    q.nivel_ingresos = $("#nivel_ingresos").val();
    q.email = $("#email").val();
    q.username = $("#username").val();
    q.password = $("#password").val();
    q.genero = $("#genero").val();
    q.codigo_departamento = $("#tbl_departamento_id").val();
    q.codigo_municipio = $("#tbl_municipio_id").val();
    q.comuna = $("#comuna").val();
    q.barrio = $("#barrio").val();
    q.nivel_educacion = $("#nivel_educacion").val();
    q.ocupacion = $("#ocupacion").val();
    q.estado = $("#estado").val();
    q.email_verificado = $("#email_verificado").val();
    q.ultimo_acceso = $("#ultimo_acceso").val();
    q.intentos_login = $("#intentos_login").val();
    q.cuenta_bloqueada_hasta = $("#cuenta_bloqueada_hasta").val();
    q.dtupdate = $("#dtupdate").val();
    q.ip_registro = $("#ip_registro").val();
    q.user_agent = $("#user_agent").val();
    if (q.password !== "") {
      q.password = hex_md5(q.password);
    }

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
            window.location = "index.php";
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
  },
};