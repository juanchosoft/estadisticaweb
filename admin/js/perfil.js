var PERFIL = {
    
    clearForm: function() {
        $('#formPerfilUpdate')[0].reset();
        $('#idVotantes_perfil').val('');
        $('#current_password_perfil').val('');
        $('#password_perfil').val('');
        $('#password_confirm_perfil').val('');
    },

    loadProfile: function(idVotante) {
        $.ajax({
            url: './admin/ajax/rqst.php',
            type: 'POST',
            data: {
                op: 'votantesget',
                id: idVotante
            },
            dataType: 'json',
            success: function(response) {
                if (response.output.valid) {
                    const data = response.output.response[0];
                    
                    $('#idVotantes_perfil').val(data.id);
                    $('#nombre_completo_perfil').val(data.nombre_completo);
                    $('#email_perfil').val(data.email);
                    $('#username_perfil').val(data.username);
                    
                    $('#current_password_perfil').val('');
                    $('#password_perfil').val('');
                    $('#password_confirm_perfil').val('');
                    
                    $('#perfilModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar la información del perfil'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar perfil:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al conectar con el servidor'
                });
            }
        });
    },

    validateAndUpdate: function() {
        if (typeof hex_md5 === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error del sistema',
                text: 'La función de encriptación hex_md5 no está disponible. Por favor recarga la página.'
            });
            return false;
        }

        const form = $('#formPerfilUpdate')[0];
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }

        const email = $('#email_perfil').val().trim();
        const username = $('#username_perfil').val().trim();
        const currentPassword = $('#current_password_perfil').val();
        const newPassword = $('#password_perfil').val();
        const confirmPassword = $('#password_confirm_perfil').val();

        if (!currentPassword) {
            Swal.fire({
                icon: 'warning',
                title: 'Contraseña requerida',
                text: 'Debes ingresar tu contraseña actual para guardar los cambios'
            });
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            Swal.fire({
                icon: 'warning',
                title: 'Email inválido',
                text: 'Por favor ingresa un correo electrónico válido'
            });
            return false;
        }

        if (newPassword && newPassword !== confirmPassword) {
            Swal.fire({
                icon: 'warning',
                title: 'Contraseñas no coinciden',
                text: 'La nueva contraseña y su confirmación deben ser iguales'
            });
            return false;
        }

        Swal.fire({
            title: '¿Confirmar actualización?',
            text: "Se actualizará tu información personal",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2b4eb9',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                PERFIL.updateProfile();
            }
        });
    },

    updateProfile: function() {
        if (typeof hex_md5 === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error del sistema',
                text: 'La función de encriptación hex_md5 no está disponible.'
            });
            return;
        }

        const formData = new FormData();
        
        formData.append('op', 'votantesactualizarperfil');
        formData.append('idVotantes', $('#idVotantes_perfil').val());
        formData.append('nombre_completo', $('#nombre_completo_perfil').val());
        formData.append('email', $('#email_perfil').val());
        formData.append('username', $('#username_perfil').val());
        
        const currentPassword = $('#current_password_perfil').val();
        const hashedCurrentPassword = hex_md5(currentPassword);
        formData.append('current_password', hashedCurrentPassword);
        
        const newPassword = $('#password_perfil').val();
        if (newPassword) {
            const hashedNewPassword = hex_md5(newPassword);
            formData.append('password', hashedNewPassword);
        }

        $.ajax({
            url: './admin/ajax/rqst.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                Swal.close(); 
                Swal.fire({
                    title: 'Actualizando...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                const isValid = (response.output && response.output.valid) || response.valid; 
                const responseData = response.output || response; 
                
                if (isValid) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: responseData.response,
                        confirmButtonColor: '#2b4eb9'
                    }).then(() => {
                        PERFIL.clearForm();
                        $('#perfilModal').modal('hide');
                        location.reload();
                    });
                } else {
                    const errorMsg = responseData.response ? responseData.response : 'Error desconocido al actualizar el perfil.';
                    
                    Swal.close(); 
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close(); 
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
            }
        });
    }
};

$(document).ready(function() {
    $('#perfilModal').on('hidden.bs.modal', function () {
        PERFIL.clearForm();
    });
});