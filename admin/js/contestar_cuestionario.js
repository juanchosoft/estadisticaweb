const CONTESTAR_CUESTIONARIO = {
    fichaTecnicaId: 0,

    init: function () {
        console.log('Iniciando módulo Contestar Cuestionario');

        // Obtener fichaTecnicaId desde el atributo data
        CONTESTAR_CUESTIONARIO.fichaTecnicaId =
            parseInt($('#cuestionario_container').data('ficha-tecnica-id')) || 0;

        // Verificar formulario
        if ($('#form_cuestionario').length === 0) {
            console.log('No hay formulario de cuestionario en esta página');
            return;
        }

        // Evento submit
        $('#form_cuestionario').on('submit', function (e) {
            e.preventDefault();
            CONTESTAR_CUESTIONARIO.enviarRespuestas();
        });

        // Track progress
        $('input[type="radio"], input[type="checkbox"], textarea.respuesta-texto')
            .on('change keyup', function () {
                CONTESTAR_CUESTIONARIO.actualizarProgreso();
            });

        CONTESTAR_CUESTIONARIO.actualizarProgreso();
    },

    actualizarProgreso: function () {
        const totalPreguntas = $('.pregunta-card').length;
        let contestadas = 0;

        $('.pregunta-card').each(function () {
            const tieneRadioCheck = $(this)
                .find('input[type="radio"]:checked, input[type="checkbox"]:checked')
                .length > 0;

            const textarea = $(this).find('textarea.respuesta-texto');
            const tieneTexto =
                textarea.length > 0 &&
                textarea.val() &&
                textarea.val().trim() !== '';

            if (tieneRadioCheck || tieneTexto) {
                contestadas++;
            }
        });

        const porcentaje =
            totalPreguntas > 0
                ? Math.round((contestadas / totalPreguntas) * 100)
                : 0;

        $('#progress_bar').css('width', porcentaje + '%');
    },

    enviarRespuestas: function () {
        // Validar si todas están contestadas
        const totalPreguntas = $('.pregunta-card').length;
        let contestadas = 0;

        $('.pregunta-card').each(function () {
            const tieneRadioCheck = $(this)
                .find('input[type="radio"]:checked, input[type="checkbox"]:checked')
                .length > 0;

            const textarea = $(this).find('textarea.respuesta-texto');
            const tieneTexto =
                textarea.length > 0 &&
                textarea.val() &&
                textarea.val().trim() !== '';

            if (tieneRadioCheck || tieneTexto) {
                contestadas++;
            }
        });

        if (contestadas < totalPreguntas) {
            Swal.fire({
                icon: 'warning',
                title: 'Preguntas sin responder',
                text: `Por favor, responde todas las preguntas. Faltan ${
                    totalPreguntas - contestadas
                } pregunta(s).`,
                confirmButtonText: 'Entendido',
            });
            return;
        }

        // Construir JSON
        const respuestas = {
            ficha_tecnica_id: CONTESTAR_CUESTIONARIO.fichaTecnicaId,
            preguntas: [],
        };

        $('.pregunta-card').each(function () {
            const preguntaId = $(this).data('pregunta-id');

            const respuestaPregunta = {
                pregunta_id: preguntaId,
                opciones: [],
                texto: '',
            };

            $(this)
                .find('input[type="radio"]:checked, input[type="checkbox"]:checked')
                .each(function () {
                    respuestaPregunta.opciones.push($(this).val());
                });

            const textoRespuesta = $(this).find('textarea.respuesta-texto').val();
            if (textoRespuesta) {
                respuestaPregunta.texto = textoRespuesta;
            }

            respuestas.preguntas.push(respuestaPregunta);
        });

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'respuestasave',
                data: JSON.stringify(respuestas),
            },
            success: function (response) {
                console.log('Response guardar:', response);

                if (response.output && response.output.valid) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Respuestas enviadas!',
                        text: 'Tus respuestas han sido guardadas correctamente.',
                        confirmButtonText: 'Ver resultados',
                        allowOutsideClick: false,
                    }).then(() => {
                        // Redirigir a la página de resultados
                        window.location.href = 'resultado.php';
                    });
                } else {
                    UTIL.mostrarMensajeError(response.output.response.content);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al guardar:', status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor.',
                    confirmButtonText: 'Entendido',
                });
            },
        });
    },
};

$(document).ready(function () {
    CONTESTAR_CUESTIONARIO.init();
});
