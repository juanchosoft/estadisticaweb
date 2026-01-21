const SONDEO = {
    selectedCandidateId: null,
    selectedSiNoValue: null,
    selectedOpcionValue: null,

    resetState() {
        this.selectedCandidateId = null;
        this.selectedSiNoValue = null;
        this.selectedOpcionValue = null;
        $('input[name="si_no_vote"]').prop('checked', false);
        $('#submitSiNoVoteBtn').prop('disabled', true);
        $('input[name="opcion_vote"]').prop('checked', false);
        $('#submitOpcionesVoteBtn').prop('disabled', true);
        $('#submitVoteBtn').prop('disabled', true);
    },

    getSubmitButtonId(modalId) {
        const map = {
            candidatoModal: '#submitVoteBtn',
            siNoModal: '#submitSiNoVoteBtn',
            opcionesModal: '#submitOpcionesVoteBtn'
        };
        return map[modalId] || '#submitVoteBtn';
    },

    enviar(sondeoId, tipo, valor, modalId) {
        if (!sondeoId || !tipo || !valor) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debes seleccionar una opción antes de confirmar',
                confirmButtonColor: '#13357b'
            });
            return;
        }

        const btn = $(this.getSubmitButtonId(modalId));
        const original = btn.text();
        btn.prop('disabled', true).text('Procesando');

        const modalEl = document.getElementById(modalId);
        const modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
        if (modalInstance) modalInstance.hide();

        $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'sondeovotar', sondeo_id: sondeoId, tipo: tipo, valor: valor },
            success: function(r) {
                Swal.fire({
                    icon: r.status === 'success' ? 'success' : 'warning',
                    title: r.status === 'success' ? 'Éxito' : 'Atención',
                    text: r.message,
                    confirmButtonColor: '#13357b'
                }).then(() => {
                    if (r.status === 'success') location.reload();
                });
            },
            error() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión',
                    confirmButtonColor: '#13357b'
                });
            },
            complete() {
                btn.prop('disabled', false).text(original);
            }
        });
    },

    selectCandidate(id) {
        this.selectedCandidateId = id;
        $('#submitVoteBtn').prop('disabled', !id);
    },

    selectSiNo(value) {
        this.selectedSiNoValue = value;
        $('#submitSiNoVoteBtn').prop('disabled', !value);
    },

    selectOpcion(value) {
        this.selectedOpcionValue = value;
        $('#submitOpcionesVoteBtn').prop('disabled', !value);
        $('.opcion-radio-item').removeClass('active');
        if (value) {
            $(`input[name="opcion_vote"][value="${value}"]`)
                .closest('.form-check-card')
                .find('.opcion-radio-item')
                .addClass('active');
        }
    },

    loadCandidatos(candidatosJson, name) {
        const tbody = $('#candidatosModalBody').empty();
        let candidatos;

        try { candidatos = JSON.parse(candidatosJson); }
        catch { candidatos = []; }

        $('#voteModalTitle').text('Selecciona tu Candidato');
        $('#candidatoQuestion').text(name);

        if (!candidatos.length) {
            tbody.append('<tr><td colspan="7" class="text-center">Sin candidatos.</td></tr>');
            return;
        }

        candidatos.forEach(c => {
            tbody.append(`
    <tr data-id="${c.id}">
        <td class="text-center">
            <input type="radio" name="candidato_vote" value="${c.id}">
        </td>
        <td>
            <img src="${c.foto_url}" style="width:55px;height:55px;border-radius:50%">
        </td>
        <td>${c.nombre_completo || ''}</td>
        <td>${c.cargo_publico || ''}</td>
        <td>${c.nombres_partidos || ''}</td>
        <td>${c.nombre_municipio || ''}</td>
        <td>${c.nombre_departamento || ''}</td>
    </tr>
`);

        });

        $('#submitVoteBtn').prop('disabled', true);
    },

    loadOpciones(opcionesJson, name) {
        const container = $('#opcionesContainer').empty();
        let opciones;

        try { opciones = JSON.parse(opcionesJson); }
        catch { opciones = []; }

        $('#opcionesQuestion').text(name);

        if (!opciones.length) {
            container.append('<div>Sin opciones.</div>');
            return;
        }

        opciones.forEach((o, i) => {
            container.append(`
                <div class="form-check-card mb-2">
                    <input class="d-none" type="radio" name="opcion_vote" id="opcion-${i}" value="${o.id}">
                    <label class="opcion-radio-item btn btn-outline-primary w-100 p-3" for="opcion-${i}">
                        <span class="me-2">${i + 1}</span> ${o.opcion}
                    </label>
                </div>
            `);
        });

        $('#submitOpcionesVoteBtn').prop('disabled', true);
    },

    detectarTipo(tipo, original, opcionesJson, candidatosJson) {
        const hasOpc = opcionesJson && opcionesJson !== '[]';
        const hasCand = candidatosJson && candidatosJson !== '[]';

        if (hasOpc && original !== 'No Aplica' && original !== 'Si/No') return 'opciones';
        if (original === 'Si/No' || tipo === 'si_no') return 'si_no';
        if (hasCand) return 'candidatos';
        if (hasOpc) return 'opciones';
        return tipo;
    },

    openSondeo(card) {
        this.resetState();

        const contestado = card.data('contestado');
        if (contestado) {
            Swal.fire({
                icon: 'info',
                title: 'Sondeo ya contestado',
                text: 'Ya has participado en este sondeo',
                confirmButtonColor: '#6c757d'
            });
            return;
        }

        const id = card.data('sondeo-id');
        const pregunta = card.data('pregunta-id');
        const name = card.data('sondeo-name');
        const tipo = card.data('tipo-sondeo');
        const tipoOriginal = card.data('tipo-sondeo-original');
        const candidatos = card.attr('data-candidatos');
        const opciones = card.attr('data-opciones');

        const tipoReal = this.detectarTipo(tipo, tipoOriginal, opciones, candidatos);

        if (tipoReal === 'opciones') {
            $('#opcionesModal')
                .data('sondeo-id', id)
                .data('pregunta-id', pregunta);
            this.loadOpciones(opciones, name);
            new bootstrap.Modal(document.getElementById('opcionesModal')).show();
            return;
        }

        if (tipoReal === 'si_no') {
            $('#siNoModal')
                .data('sondeo-id', id)
                .data('pregunta-id', pregunta);
            $('#siNoQuestion').text(name);
            $('input[name="si_no_vote"]').prop('checked', false);
            $('#submitSiNoVoteBtn').prop('disabled', true);
            this.selectedSiNoValue = null;
            new bootstrap.Modal(document.getElementById('siNoModal')).show();
            return;
        }

        if (tipoReal === 'candidatos') {
            $('#candidatoModal')
                .data('sondeo-id', id)
                .data('pregunta-id', pregunta);
            this.loadCandidatos(candidatos, name);
            new bootstrap.Modal(document.getElementById('candidatoModal')).show();
            return;
        }

        Swal.fire('Error', 'No hay datos disponibles.', 'error');
    },

    bindEvents() {
        const self = this;

        $(document).on('click', '.sondeo-card-compact', function(e) {
            e.preventDefault();
            e.stopPropagation();
            self.openSondeo($(this));
        });

        $(document).on('click', '#candidatosModalBody tr', function() {
            const id = $(this).data('id');
            $(`input[name="candidato_vote"][value="${id}"]`).prop('checked', true);
            self.selectCandidate(id);
        });

        $(document).on('change', 'input[name="si_no_vote"]', function() {
            const val = $(this).val();
            self.selectSiNo(val);
        });

        $(document).on('click', 'label[for="siOption"], label[for="noOption"]', function() {
            setTimeout(function() {
                const val = $('input[name="si_no_vote"]:checked').val();
                if (val) self.selectSiNo(val);
            }, 10);
        });

        $(document).on('change', 'input[name="opcion_vote"]', function() {
            self.selectOpcion($(this).val());
        });

        $('#submitVoteBtn').on('click', function() {
            self.enviar(
                $('#candidatoModal').data('sondeo-id'),
                'candidatos',
                self.selectedCandidateId,
                'candidatoModal'
            );
        });

        $('#submitSiNoVoteBtn').on('click', function() {
            self.enviar(
                $('#siNoModal').data('sondeo-id'),
                'si_no',
                self.selectedSiNoValue,
                'siNoModal'
            );
        });

        $('#submitOpcionesVoteBtn').on('click', function() {
            self.enviar(
                $('#opcionesModal').data('sondeo-id'),
                'opciones',
                self.selectedOpcionValue,
                'opcionesModal'
            );
        });
    },

    init() {
        this.bindEvents();
    }
};

$(document).ready(function() {
    SONDEO.init();
});
