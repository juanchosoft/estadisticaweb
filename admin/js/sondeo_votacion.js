const SONDEO = {
  selectedCandidateId: null,
  selectedOpcionValue: null,

  resetState() {
    this.selectedCandidateId = null;
    this.selectedOpcionValue = null;

    $('#submitVoteBtn').prop('disabled', true);
    $('#submitOpcionesVoteBtn').prop('disabled', true);

    $('#candidatosModalBody').empty();
    $('#opcionesContainer').empty();
    $('#opcionesQuestion').text('');
  },

  // ✅ Base64 -> JSON seguro
  decodeB64ToJson(b64) {
    try {
      if (!b64) return [];
      const jsonStr = decodeURIComponent(escape(atob(b64)));
      return JSON.parse(jsonStr);
    } catch (e) {
      console.error('Error decodeB64ToJson:', e);
      return [];
    }
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

    const btnMap = {
      candidatoModal: '#submitVoteBtn',
      opcionesModal: '#submitOpcionesVoteBtn',
    };
    const btn = $(btnMap[modalId] || '#submitVoteBtn');
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Procesando...');

    // cerrar modal
    const modalEl = document.getElementById(modalId);
    const modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
    if (modalInstance) modalInstance.hide();

    $.ajax({
      url: 'admin/ajax/rqst.php',
      type: 'POST',
      dataType: 'json',
      data: { op: 'sondeovotar', sondeo_id: sondeoId, tipo: tipo, valor: valor },
      success: function (r) {
        Swal.fire({
          icon: r.status === 'success' ? 'success' : 'warning',
          title: r.status === 'success' ? 'Éxito' : 'Atención',
          text: r.message,
          confirmButtonColor: '#13357b'
        }).then(() => {
          if (r.status === 'success') window.location.href = 'resultado.php';
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
        btn.prop('disabled', false).html(originalHtml);
      }
    });
  },

  // ✅ Render candidatos en CARDS con foto
  renderCandidatosCards(candidatos) {
    const wrap = $('#candidatosModalBody').empty();

    if (!Array.isArray(candidatos) || candidatos.length === 0) {
      wrap.html('<div class="text-center text-muted py-4">No hay candidatos disponibles.</div>');
      return;
    }

    candidatos.forEach((c) => {
      const foto = c.foto_url ? c.foto_url : 'assets/img/user_default.png';
      const nombre = c.nombre_completo || 'Candidato';
      const cargo = c.cargo_publico || 'Cargo no disponible';
      const partidos = c.nombres_partidos || 'Sin partido';
      const muni = c.nombre_municipio || '—';
      const dep = c.nombre_departamento || '—';

      wrap.append(`
        <div class="cand-card" data-id="${c.id}">
          <div class="cand-check"><i class="fas fa-check"></i></div>
          <img class="cand-photo" src="${foto}" alt="${nombre}" onerror="this.src='assets/img/user_default.png'">
          <div style="min-width:0; flex:1;">
            <p class="cand-name line-clamp-2">${nombre}</p>
            <p class="cand-sub line-clamp-2">${cargo}</p>

            <div class="cand-tags">
              <span class="tag">${partidos}</span>
              <span class="tag gray">${muni}</span>
              <span class="tag gray">${dep}</span>
            </div>
          </div>
        </div>
      `);
    });

    $('#submitVoteBtn').prop('disabled', true);
  },

  // ✅ Render opciones en CARDS
  renderOpcionesCards(opciones) {
    const wrap = $('#opcionesContainer').empty();

    if (!Array.isArray(opciones) || opciones.length === 0) {
      wrap.html('<div class="text-center text-muted py-4">No hay opciones disponibles.</div>');
      return;
    }

    opciones.forEach((o, i) => {
      const texto = o.opcion || `Opción ${i + 1}`;
      wrap.append(`
        <div class="opt-item" data-id="${o.id}">
          <div class="opt-left">
            <div class="opt-dot">${i + 1}</div>
            <div style="min-width:0;">
              <p class="opt-txt line-clamp-2">${texto}</p>
              <p class="opt-mini line-clamp-2">Toca para seleccionar</p>
            </div>
          </div>
          <div class="cand-check" style="position:static;"><i class="fas fa-check"></i></div>
        </div>
      `);
    });

    $('#submitOpcionesVoteBtn').prop('disabled', true);
  },

  openSondeo(card) {
    this.resetState();

    // ✅ si está votado, no abrir
    const contestado = String(card.data('contestado')) === 'true';
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
    const name = card.data('sondeo-name') || 'Sondeo';

    const tipo = (card.data('tipo-sondeo') || '').toString();
    const tipoOriginal = (card.data('tipo-sondeo-original') || '').toString();

    // ✅ Base64 (seguro)
    const candB64 = card.attr('data-candidatos-b64') || '';
    const opcB64  = card.attr('data-opciones-b64') || '';

    const candidatos = this.decodeB64ToJson(candB64);
    const opciones   = this.decodeB64ToJson(opcB64);

    // ✅ detectar tipo real
    const hasOpc = Array.isArray(opciones) && opciones.length > 0;
    const hasCand = Array.isArray(candidatos) && candidatos.length > 0;

    let tipoReal = tipo;
    if (hasOpc && tipoOriginal !== 'No Aplica' && tipoOriginal !== 'Si/No') tipoReal = 'opciones';
    else if (tipoOriginal === 'Si/No' || tipo === 'si_no') tipoReal = 'si_no';
    else if (hasCand) tipoReal = 'candidatos';
    else if (hasOpc) tipoReal = 'opciones';

    // guardamos ids en el modal
    $('#candidatoModal').data('sondeo-id', id).data('pregunta-id', pregunta);
    $('#opcionesModal').data('sondeo-id', id).data('pregunta-id', pregunta);

    if (tipoReal === 'opciones') {
      $('#opcionesQuestion').text(name);
      this.renderOpcionesCards(opciones);

      const modal = new bootstrap.Modal(document.getElementById('opcionesModal'), { backdrop: 'static' });
      modal.show();
      return;
    }

    if (tipoReal === 'candidatos') {
      $('#voteModalTitle').html('<i class="fas fa-hand-point-up me-2"></i>Selecciona tu candidato');
      this.renderCandidatosCards(candidatos);

      const modal = new bootstrap.Modal(document.getElementById('candidatoModal'), { backdrop: 'static' });
      modal.show();
      return;
    }

    Swal.fire('Error', 'No hay datos disponibles para votar.', 'error');
  },

  bindEvents() {
    const self = this;

    // ✅ CLICK en tarjeta (AHORA .sondeo-card)
    $(document).on('click', '.sondeo-card', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const $card = $(this);
      if ($card.hasClass('is-disabled')) return;

      self.openSondeo($card);
    });

    // ✅ SELECCIÓN candidato (cards)
    $(document).on('click', '#candidatosModalBody .cand-card', function () {
      $('#candidatosModalBody .cand-card').removeClass('selected');
      $(this).addClass('selected');

      self.selectedCandidateId = $(this).data('id');
      $('#submitVoteBtn').prop('disabled', !self.selectedCandidateId);
    });

    // ✅ SELECCIÓN opción (cards)
    $(document).on('click', '#opcionesContainer .opt-item', function () {
      $('#opcionesContainer .opt-item').removeClass('selected');
      $(this).addClass('selected');

      self.selectedOpcionValue = $(this).data('id');
      $('#submitOpcionesVoteBtn').prop('disabled', !self.selectedOpcionValue);
    });

    // ✅ CONFIRMAR candidato
    $('#submitVoteBtn').on('click', function () {
      self.enviar(
        $('#candidatoModal').data('sondeo-id'),
        'candidatos',
        self.selectedCandidateId,
        'candidatoModal'
      );
    });

    // ✅ CONFIRMAR opción
    $('#submitOpcionesVoteBtn').on('click', function () {
      self.enviar(
        $('#opcionesModal').data('sondeo-id'),
        'opciones',
        self.selectedOpcionValue,
        'opcionesModal'
      );
    });

    // ✅ cuando cierre modal, limpiar selección
    $('#candidatoModal, #opcionesModal').on('hidden.bs.modal', function(){
      self.resetState();
    });
  },

  init() {
    this.bindEvents();
  }
};

$(document).ready(function () {
  SONDEO.init();
});
