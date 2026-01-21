/**
 * Sistema de Gestión de Respuestas de Estudio de Votaciones - VERSIÓN DINÁMICA
 * Gestiona la lógica condicional entre preguntas de manera 100% dinámica desde BD
 *
 * CARACTERÍSTICAS:
 * - Soporta N preguntas principales
 * - Soporta M subpreguntas
 * - Lógica condicional configurable desde BD
 * - Escalable y mantenible
 */

const EstudioVotaciones = {
  // Almacén de respuestas del usuario (dinámico)
  respuestas: {},

  // Almacén de respuestas de subpreguntas (dinámico)
  subpreguntasRespuestas: {},

  // Datos de la grilla recibidos desde PHP
  grillaData: null,

  // Configuración de preguntas desde BD
  preguntasConfig: null,

  // Configuración de subpreguntas desde BD
  subpreguntasConfig: null,

  /**
   * Helper: Parsea opciones de respuesta (puede venir como string o array)
   * @param {string|array} opciones - Opciones en formato JSON string o array
   * @returns {array}
   */
  parseOpciones: function(opciones) {
    if (!opciones) return [];
    if (Array.isArray(opciones)) return opciones;
    if (typeof opciones === 'string') {
      try {
        return JSON.parse(opciones);
      } catch (e) {
        console.error('Error parseando opciones:', opciones, e);
        return [];
      }
    }
    return [];
  },

  /**
   * Inicializa el sistema
   * @param {Object} grillaData - Datos de la grilla desde PHP
   * @param {Array} preguntasConfig - Configuración de preguntas principales desde BD
   * @param {Array} subpreguntasConfig - Configuración de subpreguntas desde BD
   */
  init: function(grillaData, preguntasConfig, subpreguntasConfig) {
    this.grillaData = grillaData;
    this.preguntasConfig = preguntasConfig || [];
    this.subpreguntasConfig = subpreguntasConfig || [];

    // Verificar si el usuario ya votó hoy en esta grilla
    this.verificarVotoDuplicado();

    this.initializeRespuestas();
    this.bindEvents();
    this.applyInitialState();
  },

  /**
   * Verifica si el votante seleccionado ya votó hoy en esta grilla
   */
  verificarVotoDuplicado: function() {
    // Obtener votante seleccionado
    const votanteSelector = document.getElementById('votanteSelector');
    if (!votanteSelector) return;

    // Agregar listener para verificar cuando cambia el votante
    votanteSelector.addEventListener('change', () => {
      const votanteId = votanteSelector.value;
      if (!votanteId) return;

      const datosEnvio = {
        op: 'grillacandidatoverificarvotoduplicado',
        grilla_id: this.grillaData.id,
        votante_id: parseInt(votanteId)
      };

      UTIL.callAjaxRqstPOST(datosEnvio, (response) => {

        if (response && response.output && response.output.valid) {
          const yaVoto = response.output.response.ya_voto;

          if (yaVoto) {
            // El votante ya votó, mostrar mensaje y deshabilitar la interfaz
            UTIL.mostrarMensajeValidacion('Este votante ya registró su voto hoy en esta grilla. Por favor, seleccione otro votante.');

            // Deshabilitar todos los botones toggle
            const toggleButtons = document.querySelectorAll('.toggle-btn');
            toggleButtons.forEach(btn => {
              btn.disabled = true;
              btn.style.cursor = 'not-allowed';
              btn.style.opacity = '0.5';
            });

            // Deshabilitar botón de guardar
            const btnGuardar = document.getElementById('btnGuardarRespuestas');
            if (btnGuardar) {
              btnGuardar.disabled = true;
              btnGuardar.style.cursor = "not-allowed";
              btnGuardar.style.opacity = '0.5';
            }
          } else {
            // El votante NO ha votado, habilitar la interfaz
            const toggleButtons = document.querySelectorAll('.toggle-btn');
            toggleButtons.forEach(btn => {
              btn.disabled = false;
              btn.style.cursor = 'pointer';
              btn.style.opacity = '1';
            });

            const btnGuardar = document.getElementById('btnGuardarRespuestas');
            if (btnGuardar) {
              btnGuardar.disabled = false;
              btnGuardar.style.cursor = "pointer";
              btnGuardar.style.opacity = '1';
            }
          }
        }
      });
    });
  },

  /**
   * Inicializa la estructura de respuestas para todos los candidatos
   * DINÁMICO: Se adapta a las preguntas configuradas en BD
   */
  initializeRespuestas: function() {
    const rows = document.querySelectorAll('.tabla_grilla tbody tr[data-candidato-id]');

    rows.forEach(row => {
      const candidatoId = row.getAttribute('data-candidato-id');
      this.respuestas[candidatoId] = {};

      // Inicializar respuestas para cada pregunta configurada
      this.preguntasConfig.forEach((pregunta, index) => {
        const codigoPregunta = pregunta.codigo_pregunta;
        const opciones = this.parseOpciones(pregunta.opciones_respuesta);

        if (index === 0) {
          // Primera pregunta: valor por defecto es la segunda opción (generalmente "no")
          this.respuestas[candidatoId][codigoPregunta] = opciones[1] || opciones[0] || 'no';
        } else {
          // Preguntas siguientes: NO APLICA por defecto
          this.respuestas[candidatoId][codigoPregunta] = 'no_aplica';
        }
      });
    });

    // Inicializar subpreguntas con null
    this.subpreguntasConfig.forEach(subpregunta => {
      this.subpreguntasRespuestas[subpregunta.codigo_pregunta] = null;
    });
  },

  /**
   * Vincula eventos de click a todos los botones toggle
   */
  bindEvents: function() {
    const self = this;

    // Event delegation en la tabla
    const tbody = document.querySelector('.tabla_grilla tbody');
    if (!tbody) {
      console.error('No se encontró el tbody de la tabla');
      return;
    }

    tbody.addEventListener('click', function(e) {
      const btn = e.target.closest('.toggle-btn');
      if (!btn) return;

      const row = btn.closest('tr[data-candidato-id]');
      const td = btn.closest('td[data-pregunta]');

      if (!row || !td) return;

      const candidatoId = row.getAttribute('data-candidato-id');
      const codigoPregunta = td.getAttribute('data-pregunta');
      const valor = btn.getAttribute('data-value');

      self.handleToggleClick(candidatoId, codigoPregunta, valor, row, btn, td);
    });

    // Botón de guardar
    const btnGuardar = document.getElementById('btnGuardarRespuestas');
    if (btnGuardar) {
      btnGuardar.addEventListener('click', function() {
        self.guardarRespuestas();
      });
    }
  },

  /**
   * Maneja el click en un botón toggle
   * @param {string} candidatoId - ID del candidato
   * @param {string} codigoPregunta - Código de la pregunta
   * @param {string} valor - Valor seleccionado
   * @param {HTMLElement} row - Fila de la tabla
   * @param {HTMLElement} btn - Botón clickeado
   * @param {HTMLElement} td - Celda clickeada
   */
  handleToggleClick: function(candidatoId, codigoPregunta, valor, row, btn, td) {

    // Actualizar estado visual del toggle
    const toggleGroup = btn.parentElement;
    const buttons = toggleGroup.querySelectorAll('.toggle-btn');
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Actualizar respuestas
    this.respuestas[candidatoId][codigoPregunta] = valor;

    // Aplicar lógica condicional DINÁMICA
    this.applyConditionalLogicDynamic(candidatoId, codigoPregunta, valor, row, td);

  },

  /**
   * Aplica lógica condicional de manera DINÁMICA basada en la configuración de BD
   * @param {string} candidatoId - ID del candidato
   * @param {string} codigoPregunta - Código de la pregunta que cambió
   * @param {string} valor - Nuevo valor
   * @param {HTMLElement} row - Fila de la tabla
   * @param {HTMLElement} tdActual - Celda actual
   */
  applyConditionalLogicDynamic: function(candidatoId, codigoPregunta, valor, row, tdActual) {
    // Obtener configuración de la pregunta actual
    const preguntaConfig = this.preguntasConfig.find(p => p.codigo_pregunta === codigoPregunta);

    if (!preguntaConfig) {
      console.warn(`No se encontró configuración para pregunta: ${codigoPregunta}`);
      return;
    }

    const habilita = parseInt(preguntaConfig.habilita_subpreguntas) === 1;
    const condicion = preguntaConfig.condicion_habilitacion;
    const ordenActual = parseInt(preguntaConfig.orden);


    if (habilita) {
      // Esta pregunta puede habilitar las siguientes
      const cumpleCondicion = this.evaluarCondicion(candidatoId, condicion, valor, row);

      if (cumpleCondicion) {
        // Habilitar siguiente pregunta
        this.habilitarSiguientePregunta(row, ordenActual);
      } else {
        // Deshabilitar todas las siguientes preguntas
        this.deshabilitarSiguientesPreguntas(row, ordenActual, candidatoId);
      }
    }

    // Actualizar candidatos aprobados
    this.actualizarCandidatosAprobados();
  },

  /**
   * Evalúa si se cumple la condición para habilitar siguientes preguntas
   * @param {string} candidatoId - ID del candidato
   * @param {string} condicion - Condición a evaluar ('si', 'favorable', 'todas_si', null)
   * @param {string} valorActual - Valor actual de la pregunta
   * @param {HTMLElement} row - Fila de la tabla
   * @returns {boolean}
   */
  evaluarCondicion: function(candidatoId, condicion, valorActual, row) {
    if (!condicion) return false;

    switch(condicion) {
      case 'si':
        return valorActual === 'si';

      case 'favorable':
        return valorActual === 'favorable';

      case 'todas_si':
        // Verificar que todas las respuestas anteriores sean positivas
        return this.todasRespuestasPositivas(candidatoId);

      default:
        console.warn(`Condición no reconocida: ${condicion}`);
        return false;
    }
  },

  /**
   * Verifica si todas las respuestas del candidato son positivas
   * IMPORTANTE: Solo evalúa preguntas que NO estén en estado 'no_aplica'
   * @param {string} candidatoId - ID del candidato
   * @returns {boolean}
   */
  todasRespuestasPositivas: function(candidatoId) {
    const respuestasCandidato = this.respuestas[candidatoId];

    for (const pregunta of this.preguntasConfig) {
      const codigo = pregunta.codigo_pregunta;
      const respuesta = respuestasCandidato[codigo];

      // IMPORTANTE: Saltar preguntas que están en 'no_aplica' (deshabilitadas)
      if (respuesta === 'no_aplica') {
        continue; // No evaluamos esta pregunta
      }

      const opciones = this.parseOpciones(pregunta.opciones_respuesta);

      // Respuesta positiva es la primera opción o valores específicos
      const esPositiva = (
        respuesta === opciones[0] ||
        respuesta === 'si' ||
        respuesta === 'favorable'
      );

      if (!esPositiva) {
        return false;
      }
    }

    return true;
  },

  /**
   * Habilita TODAS las preguntas siguientes
   * @param {HTMLElement} row - Fila de la tabla
   * @param {number} ordenActual - Orden de la pregunta actual
   */
  habilitarSiguientePregunta: function(row, ordenActual) {
    const candidatoId = row.getAttribute('data-candidato-id');
    const tdsPreguntas = row.querySelectorAll('td[data-pregunta]');

    // Iterar sobre TODAS las preguntas siguientes
    tdsPreguntas.forEach(td => {
      const ordenPregunta = parseInt(td.getAttribute('data-orden'));

      // Solo habilitar preguntas con orden MAYOR al actual
      if (ordenPregunta > ordenActual) {
        const codigoPregunta = td.getAttribute('data-pregunta');
        const preguntaConfig = this.preguntasConfig.find(p => p.codigo_pregunta === codigoPregunta);

        if (preguntaConfig) {
          const opciones = this.parseOpciones(preguntaConfig.opciones_respuesta);
          // Segunda opción por defecto (generalmente "NO")
          const valorDefault = opciones[1] || opciones[0] || 'no';

          this.setAplicable(td, valorDefault);

          // Actualizar respuesta
          this.respuestas[candidatoId][codigoPregunta] = valorDefault;
        }
      }
    });
  },

  /**
   * Deshabilita todas las preguntas siguientes
   * @param {HTMLElement} row - Fila de la tabla
   * @param {number} ordenActual - Orden de la pregunta actual
   * @param {string} candidatoId - ID del candidato
   */
  deshabilitarSiguientesPreguntas: function(row, ordenActual, candidatoId) {
    const tdsPreguntas = row.querySelectorAll('td[data-pregunta]');

    tdsPreguntas.forEach(td => {
      const ordenPregunta = parseInt(td.getAttribute('data-orden'));

      if (ordenPregunta > ordenActual) {
        this.setNoAplica(td);

        // Actualizar respuesta
        const codigoPregunta = td.getAttribute('data-pregunta');
        this.respuestas[candidatoId][codigoPregunta] = 'no_aplica';
      }
    });
  },

  /**
   * Marca una celda como "NO APLICA"
   * @param {HTMLElement} td - Celda a modificar
   */
  setNoAplica: function(td) {
    if (!td) return;

    const toggle = td.querySelector('.toggle');
    if (toggle) {
      toggle.style.display = 'none';
    }

    // Agregar texto "NO APLICA" si no existe
    if (!td.querySelector('.no-aplica-text')) {
      const span = document.createElement('span');
      span.className = 'no-aplica-text';
      span.textContent = '--';
      td.appendChild(span);
    }

    td.classList.add('no-aplica');
  },

  /**
   * Reactiva una celda que estaba marcada como "NO APLICA"
   * @param {HTMLElement} td - Celda a modificar
   * @param {string} valorDefault - Valor por defecto a activar
   */
  setAplicable: function(td, valorDefault) {
    if (!td) return;

    const toggle = td.querySelector('.toggle');
    if (toggle) {
      toggle.style.display = 'flex';

      // Activar el botón por defecto
      const buttons = toggle.querySelectorAll('.toggle-btn');
      buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-value') === valorDefault) {
          btn.classList.add('active');
        }
      });
    }

    // Remover texto "NO APLICA"
    const noAplicaText = td.querySelector('.no-aplica-text');
    if (noAplicaText) {
      noAplicaText.remove();
    }

    td.classList.remove('no-aplica');
  },

  /**
   * Aplica el estado inicial de la interfaz
   */
  applyInitialState: function() {
    this.actualizarCandidatosAprobados();
  },

  /**
   * Obtiene la lista de candidatos aprobados (dinámico)
   * NUEVA LÓGICA: Solo verifica la pregunta que activa las subpreguntas (activa_seccion_subpreguntas = 1)
   * @returns {Array} Array de IDs de candidatos aprobados
   */
  getCandidatosAprobados: function() {
    const aprobados = [];

    // Buscar la pregunta que activa las subpreguntas
    const preguntaActivadora = this.preguntasConfig.find(p => parseInt(p.activa_seccion_subpreguntas) === 1);

    if (!preguntaActivadora) {
      console.warn('No hay pregunta configurada para activar subpreguntas');
      return aprobados;
    }

    const codigoPreguntaActivadora = preguntaActivadora.codigo_pregunta;

    // Iterar sobre cada candidato
    for (const candidatoId in this.respuestas) {
      const respuestaCandidato = this.respuestas[candidatoId][codigoPreguntaActivadora];

      // Si respondió SÍ a la pregunta activadora → Candidato aprobado para subpreguntas
      if (respuestaCandidato === 'si' || respuestaCandidato === 'favorable') {
        aprobados.push(candidatoId);
      }
    }
    return aprobados;
  },

  /**
   * Actualiza dinámicamente la lista de candidatos aprobados
   */
  actualizarCandidatosAprobados: function() {
    const container = document.getElementById('candidatosAprobadosContainer');
    const totalElement = document.getElementById('totalAprobados');
    const mensajeVacio = document.getElementById('mensajeVacio');

    if (!container || !totalElement) {
      console.warn('No se encontró el contenedor de candidatos aprobados');
      return;
    }

    const aprobados = this.getCandidatosAprobados();
    const totalAprobados = aprobados.length;

    totalElement.textContent = totalAprobados;
    container.innerHTML = '';

    if (totalAprobados === 0) {
      if (mensajeVacio) {
        container.appendChild(mensajeVacio.cloneNode(true));
      } else {
        container.innerHTML = `
          <div class="text-center text-muted py-4">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p class="mb-0">No hay candidatos aprobados aún</p>
          </div>
        `;
      }
    } else {
      const letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

      aprobados.forEach((candidatoId, index) => {
        const candidato = this.getCandidatoById(candidatoId);
        if (!candidato) return;

        const letra = letras[index] || `#${index + 1}`;
        const fotoUrl = candidato.foto
          ? 'assets/img/admin/' + candidato.foto
          : 'assets/img/candidato.png';

        const candidatoCard = document.createElement('div');
        candidatoCard.className = 'candidato-aprobado-item mb-2';
        candidatoCard.setAttribute('data-candidato-letra', letra);
        candidatoCard.innerHTML = `
          <div class="candidato-aprobado-card-compacto">
            <div class="candidato-aprobado-foto-container">
              <img src="${fotoUrl}" alt="${candidato.nombre_completo}" class="candidato-aprobado-foto-compacta">
              <div class="candidato-aprobado-letra-badge">
                ${letra}
              </div>
            </div>
            <div class="candidato-aprobado-info">
              <strong class="candidato-nombre-compacto">${letra}. ${candidato.nombre_completo}</strong>
              ${candidato.nombres_partidos ? `<small class="candidato-partido-compacto">${candidato.nombres_partidos}</small>` : ''}
              <div class="respuestas-compactas">
                ${this.preguntasConfig.map(() => '<span class="si-badge"><i class="fas fa-check"></i> SÍ</span>').join('')}
              </div>
            </div>
          </div>
        `;

        container.appendChild(candidatoCard);
      });

      // Renderizar subpreguntas dinámicamente
      this.renderizarSubpreguntasDinamicas(aprobados, letras);
    }
  },

  /**
   * Renderiza las subpreguntas de manera dinámica desde la configuración de BD
   * @param {Array} aprobados - Array de IDs de candidatos aprobados
   * @param {Array} letras - Array de letras del alfabeto
   */
  renderizarSubpreguntasDinamicas: function(aprobados, letras) {
    const container = document.getElementById('candidatosAprobadosContainer');
    if (!container || aprobados.length === 0 || this.subpreguntasConfig.length === 0) return;

    // LÓGICA DE CANDIDATO ÚNICO: Si solo hay 1 candidato aprobado, las subpreguntas son solo lectura
    const soloUnCandidato = aprobados.length === 1;

    const preguntasSection = document.createElement('div');
    preguntasSection.className = 'preguntas-adicionales mt-3 pt-3 border-top';

    let headerHTML = '<h6 class="text-center mb-3"><i class="fas fa-poll"></i> PREGUNTAS ADICIONALES</h6>';

    // Mostrar mensaje informativo si solo hay un candidato
    if (soloUnCandidato) {
      headerHTML += `
        <div class="alert alert-info py-2 mb-3" style="font-size: 12px;">
          <i class="fas fa-info-circle"></i>
          <strong>Solo hay un candidato aprobado.</strong> Las preguntas adicionales se muestran como referencia únicamente.
        </div>
      `;
    }

    preguntasSection.innerHTML = headerHTML;

    // Renderizar cada subpregunta configurada
    this.subpreguntasConfig.forEach((subpregunta, index) => {
      // LÓGICA CORRECTA: Número de subpreguntas ACTIVAS = Número de candidatos aprobados
      // Ejemplo: 2 candidatos → PA y PB activas, PC+ solo lectura
      const totalCandidatos = aprobados.length;
      const esActiva = index < totalCandidatos; // Solo las primeras N subpreguntas (N = candidatos)
      const soloLectura = !esActiva;

      const preguntaDiv = this.crearSubpreguntaDinamica(
        subpregunta,
        aprobados,
        letras,
        index,
        soloLectura  // true si index >= totalCandidatos
      );
      preguntasSection.appendChild(preguntaDiv);
    });

    container.appendChild(preguntasSection);
  },

  /**
   * Crea una subpregunta dinámica
   * @param {Object} subpregunta - Configuración de la subpregunta
   * @param {Array} aprobados - IDs de candidatos aprobados
   * @param {Array} letras - Array de letras
   * @param {number} index - Índice de la subpregunta
   * @param {boolean} soloLectura - Si true, los botones son solo lectura
   * @returns {HTMLElement}
   */
  crearSubpreguntaDinamica: function(subpregunta, aprobados, letras, index, soloLectura = false) {
    const preguntaDiv = document.createElement('div');
    preguntaDiv.className = 'pregunta-adicional-item mb-3';

    const codigo = subpregunta.codigo_pregunta;
    const texto = subpregunta.texto_pregunta;

    // Mensaje diferente si es solo lectura
    const mensajeInstruccion = soloLectura
      ? '<small class="text-muted"><i class="fas fa-lock"></i> Solo lectura - No es necesario votar</small>'
      : '<small class="text-muted">Seleccione su respuesta</small>';

    preguntaDiv.innerHTML = `
      <p class="pregunta-texto"><strong>${texto}</strong></p>
      ${mensajeInstruccion}
      <div class="opciones-candidatos mt-2">
        ${aprobados.map((candidatoId, idx) => {
          const candidato = this.getCandidatoById(candidatoId);
          const letra = letras[idx];

          // Si es solo lectura, mostrar el botón pero deshabilitado y pre-seleccionado
          const claseBoton = soloLectura ? 'btn btn-primary btn-sm btn-candidato-opcion mb-1 active' : 'btn btn-outline-primary btn-sm btn-candidato-opcion mb-1';
          const disabled = soloLectura ? 'disabled' : '';
          const estiloExtra = soloLectura ? 'cursor: not-allowed; opacity: 0.7;' : '';

          return `
            <button class="${claseBoton}"
                    data-subpregunta="${codigo}"
                    data-candidato="${candidatoId}"
                    data-letra="${letra}"
                    ${disabled}
                    style="${estiloExtra}">
              <i class="fas fa-user"></i> ${letra}. ${candidato.nombre_completo}
            </button>
          `;
        }).join('')}
      </div>
    `;

    // Si es solo lectura, pre-guardar la respuesta automáticamente con el único candidato
    if (soloLectura && aprobados.length === 1) {
      this.subpreguntasRespuestas[codigo] = parseInt(aprobados[0]);
    }

    // Solo agregar event listeners si NO es solo lectura
    if (!soloLectura) {
      const self = this;
      setTimeout(() => {
        const botones = preguntaDiv.querySelectorAll('.btn-candidato-opcion');
        botones.forEach(btn => {
          btn.addEventListener('click', () => {
            botones.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const codigoSubpregunta = btn.getAttribute('data-subpregunta');
            const candidatoId = btn.getAttribute('data-candidato');

            self.subpreguntasRespuestas[codigoSubpregunta] = parseInt(candidatoId);

            // IMPORTANTE: Actualizar opciones disponibles en subpreguntas siguientes
            // Ocultar candidato seleccionado en todas las subpreguntas posteriores
            self.actualizarOpcionesSubpreguntas(index);
          });
        });
      }, 100);
    }

    return preguntaDiv;
  },

  /**
   * Actualiza las opciones disponibles en subpreguntas siguientes
   * Oculta candidatos ya seleccionados en subpreguntas anteriores
   * @param {number} subpreguntaIndex - Índice de la subpregunta que acaba de cambiar
   */
  actualizarOpcionesSubpreguntas: function(subpreguntaIndex) {
    // Obtener todos los candidatos ya seleccionados en subpreguntas anteriores (incluyendo la actual)
    const candidatosSeleccionados = [];

    for (let i = 0; i <= subpreguntaIndex; i++) {
      if (this.subpreguntasConfig[i]) {
        const codigo = this.subpreguntasConfig[i].codigo_pregunta;
        const candidatoId = this.subpreguntasRespuestas[codigo];
        if (candidatoId) {
          candidatosSeleccionados.push(candidatoId);
        }
      }
    }

    // Actualizar cada subpregunta POSTERIOR
    for (let i = subpreguntaIndex + 1; i < this.subpreguntasConfig.length; i++) {
      const codigoSubpregunta = this.subpreguntasConfig[i].codigo_pregunta;

      // Buscar todos los botones de esta subpregunta
      const botones = document.querySelectorAll(`[data-subpregunta="${codigoSubpregunta}"]`);

      botones.forEach(btn => {
        const candidatoId = parseInt(btn.getAttribute('data-candidato'));

        // Si el candidato ya fue seleccionado en una subpregunta anterior
        if (candidatosSeleccionados.includes(candidatoId)) {
          // Ocultar el botón
          btn.style.display = 'none';

          // Si este botón estaba activo, desactivarlo
          if (btn.classList.contains('active')) {
            btn.classList.remove('active');
            // Limpiar la respuesta de esta subpregunta
            delete this.subpreguntasRespuestas[codigoSubpregunta];
          }
        } else {
          // Mostrar el botón (candidato disponible)
          btn.style.display = 'inline-block';
        }
      });
    }
  },

  /**
   * Obtiene un candidato por su ID
   * @param {string} candidatoId - ID del candidato
   * @returns {Object|null}
   */
  getCandidatoById: function(candidatoId) {
    if (!this.grillaData || !this.grillaData.candidatos) return null;
    return this.grillaData.candidatos.find(c => c.id == candidatoId) || null;
  },

  /**
   * Guarda las respuestas (implementación en siguiente archivo debido a longitud)
   */
  guardarRespuestas: function() {

    // Validar respuestas completas
    const aprobados = this.getCandidatosAprobados();
    if (aprobados.length > 0) {
      // Validar solo las primeras N subpreguntas (N = candidatos aprobados)
      const totalCandidatos = aprobados.length;

      for (let i = 0; i < Math.min(totalCandidatos, this.subpreguntasConfig.length); i++) {
        const subpregunta = this.subpreguntasConfig[i];
        if (this.subpreguntasRespuestas[subpregunta.codigo_pregunta] === null ||
            this.subpreguntasRespuestas[subpregunta.codigo_pregunta] === undefined) {
          UTIL.mostrarMensajeValidacion(
            `Por favor, complete las subpreguntas activas:\n\nFalta responder: ${subpregunta.texto_pregunta}`
          );
          return;
        }
      }
    }

    // Preparar datos para envío (JSON flexible)
    const datosEnvio = {
      op: 'grillacandidatoguardarrespuestas',
      grilla_id: this.grillaData.id,
      //votante_id: parseInt(votanteId),
      respuestas: JSON.stringify(this.respuestas),
      subpreguntas: JSON.stringify(this.subpreguntasRespuestas)
    };

    UTIL.cursorBusy();
    UTIL.callAjaxRqstPOST(datosEnvio, this.guardarRespuestasHandler.bind(this));
  },

  /**
   * Handler del guardado
   */
  guardarRespuestasHandler: function(response) {
    UTIL.cursorNormal();

    if (response && response.output && response.output.valid) {
      UTIL.mostrarMensajeExitoso('¡Todas las respuestas guardadas exitosamente!');

      setTimeout(() => {
        window.location.href = 'grilla.php';
      }, 1500);
    } else {
      const mensaje = response?.output?.response?.content || 'Error al guardar';
      UTIL.mostrarMensajeError(mensaje);
    }
  }
};

// Exportar
window.EstudioVotaciones = EstudioVotaciones;
