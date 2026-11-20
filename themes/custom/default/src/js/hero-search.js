/**
 * @file
 * Autocomplete + cross-filter for hero course/city fields.
 *
 * Probe (disable/hint) só após escolha confirmada no autocomplete.
 * Digitação parcial NÃO limpa/desabilita o outro campo.
 * Com constraint ativa: lista opções no foco; estado vazio explicativo se zero hits.
 */
(function (Drupal, once, $) {
  'use strict';

  const MIN_LENGTH = 2;
  const DELAY_MS = 80;
  const PROBE_DEBOUNCE_MS = 300;

  function escapeRegExp(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  function stripAccents(text) {
    return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  /**
   * Valor de query string: minúsculas e sem acento (Administração → administracao).
   */
  function toQueryValue(text) {
    return stripAccents(String(text || '').trim()).toLowerCase();
  }

  function appendHighlighted(parent, text, term) {
    const needle = stripAccents(term.trim()).toLowerCase();
    if (!needle) {
      parent.appendChild(document.createTextNode(text));
      return;
    }
    let plain = '';
    for (const ch of text) {
      plain += stripAccents(ch).toLowerCase().charAt(0) || ch;
    }
    const idx = plain.search(new RegExp(escapeRegExp(needle)));
    if (idx < 0) {
      parent.appendChild(document.createTextNode(text));
      return;
    }
    const chars = Array.from(text);
    const before = chars.slice(0, idx).join('');
    const match = chars.slice(idx, idx + needle.length).join('');
    const after = chars.slice(idx + needle.length).join('');
    if (before) {
      parent.appendChild(document.createTextNode(before));
    }
    const strong = document.createElement('strong');
    strong.textContent = match;
    parent.appendChild(strong);
    if (after) {
      parent.appendChild(document.createTextNode(after));
    }
  }

  function cacheKey(path, term, constraints) {
    return [
      path,
      term,
      constraints.cidade || '',
      constraints.estado || '',
      constraints.curso || '',
    ].join('|');
  }

  function setFieldDisabled(input, disabled, hint) {
    if (!input) {
      return;
    }
    const field = input.closest('.hero-search__field');
    input.disabled = !!disabled;
    if (field) {
      field.classList.toggle('is-disabled', !!disabled);
    }
    if (hint) {
      if (disabled) {
        hint.hidden = false;
        hint.removeAttribute('hidden');
      }
      else {
        hint.hidden = true;
        hint.setAttribute('hidden', 'hidden');
      }
    }
  }

  function clearField(input, estadoInput) {
    if (!input) {
      return;
    }
    input.value = '';
    if (estadoInput) {
      setEstadoValue(estadoInput, '');
    }
  }

  function setEstadoValue(estadoInput, uf) {
    if (!estadoInput) {
      return;
    }
    const value = String(uf || '').trim();
    estadoInput.value = value;
    if (value) {
      estadoInput.setAttribute('name', 'estado');
    }
    else {
      estadoInput.removeAttribute('name');
    }
  }

  function emptyStateMessage(isCidade, constraints) {
    if (isCidade) {
      return Drupal.t('Nenhuma cidade com vagas de @curso.', {
        '@curso': constraints.curso || '',
      });
    }
    return Drupal.t('Nenhum curso com vagas em @cidade.', {
      '@cidade': constraints.cidade || '',
    });
  }

  function hasConstraint(isCidade, constraints) {
    return isCidade ? !!constraints.curso : !!constraints.cidade;
  }

  /**
   * Constraint para filtrar sugestões: valor confirmado OU texto livre ≥2 chars.
   */
  function constraintValue(input, committed) {
    if (!input) {
      return '';
    }
    const value = input.value.trim();
    if (!value) {
      return '';
    }
    if (committed || value.length >= MIN_LENGTH) {
      return value;
    }
    return '';
  }

  Drupal.behaviors.heroSearchAutocomplete = {
    attach(context) {
      const forms = once('hero-search-form', 'form.hero-search__form', context);
      forms.forEach((form) => {
        const cursoInput = form.querySelector('input[name="cursos"][data-autocomplete-path]');
        const cidadeInput = form.querySelector('input[name="cidade"][data-autocomplete-path]');
        const estadoInput = form.querySelector('input[data-hero-estado]');
        const cursoHint = form.querySelector('[data-hero-hint="cursos"]');
        const cidadeHint = form.querySelector('[data-hero-hint="cidade"]');

        const shared = {
          cursoInput,
          cidadeInput,
          estadoInput,
          cursoHint,
          cidadeHint,
          committed: { cursos: false, cidade: false },
          probeTimers: { cursos: null, cidade: null },
          probeXhr: { cursos: null, cidade: null },
        };

        shared.probeOpposite = function probeOpposite(originName) {
          const isFromCidade = originName === 'cidade';
          const commitKey = isFromCidade ? 'cidade' : 'cursos';
          const origin = isFromCidade ? shared.cidadeInput : shared.cursoInput;
          const target = isFromCidade ? shared.cursoInput : shared.cidadeInput;
          const targetHint = isFromCidade ? shared.cursoHint : shared.cidadeHint;
          const targetEstado = isFromCidade ? null : shared.estadoInput;
          const probeKey = isFromCidade ? 'cursos' : 'cidade';

          if (!origin || !target) {
            return;
          }

          const originValue = origin.value.trim();
          if (!originValue || !shared.committed[commitKey]) {
            // Digitação parcial nunca desabilita o oposto.
            setFieldDisabled(target, false, targetHint);
            return;
          }

          const path = target.getAttribute('data-autocomplete-path');
          if (!path) {
            return;
          }

          const params = { q: '' };
          if (isFromCidade) {
            params.cidade = originValue;
            if (shared.estadoInput && shared.estadoInput.value) {
              params.estado = shared.estadoInput.value;
            }
          }
          else {
            params.curso = originValue;
          }

          if (shared.probeXhr[probeKey]) {
            shared.probeXhr[probeKey].abort();
          }

          shared.probeXhr[probeKey] = $.getJSON(path, params, (data) => {
            const items = data || [];
            if (!items.length) {
              clearField(target, targetEstado);
              if (targetEstado === shared.estadoInput) {
                shared.committed.cidade = false;
              }
              else {
                shared.committed.cursos = false;
              }
              setFieldDisabled(target, true, targetHint);
            }
            else {
              setFieldDisabled(target, false, targetHint);
            }
          }).fail((xhr, status) => {
            if (status !== 'abort') {
              setFieldDisabled(target, false, targetHint);
            }
          });
        };

        shared.scheduleProbe = function scheduleProbe(originName, immediate) {
          const key = originName === 'cidade' ? 'cidade' : 'cursos';
          if (shared.probeTimers[key]) {
            window.clearTimeout(shared.probeTimers[key]);
            shared.probeTimers[key] = null;
          }
          if (immediate) {
            shared.probeOpposite(originName);
            return;
          }
          shared.probeTimers[key] = window.setTimeout(() => {
            shared.probeTimers[key] = null;
            shared.probeOpposite(originName);
          }, PROBE_DEBOUNCE_MS);
        };

        // bfcache / valores pré-preenchidos: trata como confirmados.
        if (cidadeInput && cidadeInput.value.trim()) {
          shared.committed.cidade = true;
          shared.scheduleProbe('cidade', true);
        }
        else if (cursoInput && cursoInput.value.trim()) {
          shared.committed.cursos = true;
          shared.scheduleProbe('cursos', true);
        }

        form.addEventListener('submit', (event) => {
          event.preventDefault();
          const action = form.getAttribute('action') || '/vagas';
          const params = new URLSearchParams();
          form.querySelectorAll('input[name]').forEach((el) => {
            if (el.disabled) {
              return;
            }
            const name = el.getAttribute('name');
            const value = String(el.value || '').trim();
            if (!name || value === '') {
              return;
            }
            params.append(name, toQueryValue(value));
          });
          const query = params.toString();
          window.location.assign(query ? `${action}?${query}` : action);
        });

        form._heroSearchShared = shared;
      });

      once('hero-search-ac', 'input[data-autocomplete-path]', context).forEach((input) => {
        const path = input.getAttribute('data-autocomplete-path');
        // Não usar classe form-autocomplete: o core também inicializa e conflita.
        if (!path || typeof $.fn.autocomplete !== 'function') {
          return;
        }

        const form = input.closest('form');
        const shared = form && form._heroSearchShared ? form._heroSearchShared : null;
        const isCidade = input.getAttribute('name') === 'cidade';
        const isCurso = input.getAttribute('name') === 'cursos';
        const commitKey = isCidade ? 'cidade' : 'cursos';
        const field = input.closest('.hero-search__field') || input;
        const estadoInput = isCidade && form
          ? form.querySelector('input[data-hero-estado]')
          : null;
        let suppressCommitClear = false;

        const cache = new Map();
        let lastXhr = null;

        if (shared && (isCidade || isCurso)) {
          input.addEventListener('input', () => {
            if (suppressCommitClear) {
              return;
            }
            // Edição após select: deixa de ser "escolha confirmada".
            shared.committed[commitKey] = false;
            if (estadoInput) {
              setEstadoValue(estadoInput, '');
            }
            // Reabilita o oposto imediatamente (digitação parcial não pode travar).
            if (isCidade) {
              setFieldDisabled(shared.cursoInput, false, shared.cursoHint);
            }
            else {
              setFieldDisabled(shared.cidadeInput, false, shared.cidadeHint);
            }
            if (!input.value.trim()) {
              shared.scheduleProbe(commitKey, true);
            }
          });
        }

        function currentConstraints() {
          const constraints = { cidade: '', estado: '', curso: '' };
          if (!shared) {
            return constraints;
          }
          if (isCurso && shared.cidadeInput) {
            constraints.cidade = constraintValue(
              shared.cidadeInput,
              shared.committed.cidade,
            );
            if (shared.estadoInput && shared.committed.cidade) {
              constraints.estado = shared.estadoInput.value.trim();
            }
          }
          if (isCidade && shared.cursoInput) {
            constraints.curso = constraintValue(
              shared.cursoInput,
              shared.committed.cursos,
            );
          }
          return constraints;
        }

        $(input).autocomplete({
          delay: DELAY_MS,
          minLength: MIN_LENGTH,
          classes: { 'ui-autocomplete': 'hero-search-ac' },
          position: {
            my: 'left top+10',
            at: 'left bottom',
            of: field,
            collision: 'flip flip',
          },
          source(request, response) {
            const rawTerm = request.term;
            const term = rawTerm.trim().toLowerCase();
            const constraints = currentConstraints();
            const constrained = hasConstraint(isCidade, constraints);
            const key = cacheKey(path, term, constraints);
            if (cache.has(key)) {
              response(cache.get(key));
              return;
            }
            if (lastXhr) {
              lastXhr.abort();
            }
            const params = { q: rawTerm };
            if (constraints.cidade) {
              params.cidade = constraints.cidade;
            }
            if (constraints.estado) {
              params.estado = constraints.estado;
            }
            if (constraints.curso) {
              params.curso = constraints.curso;
            }
            lastXhr = $.getJSON(path, params, (data) => {
              let items = (data || []).map((item) => ({
                value: item.value,
                label: item.label,
                estado: item.estado || '',
                count: item.count || 0,
              }));
              if (!items.length && constrained) {
                items = [{
                  value: '',
                  label: emptyStateMessage(isCidade, constraints),
                  isEmpty: true,
                }];
              }
              cache.set(key, items);
              response(items);
            }).fail((xhr, status) => {
              if (status !== 'abort') {
                if (constrained) {
                  response([{
                    value: '',
                    label: emptyStateMessage(isCidade, constraints),
                    isEmpty: true,
                  }]);
                }
                else {
                  response([]);
                }
              }
            });
          },
          focus(event, ui) {
            // Não preenche o input ao navegar no item de estado vazio.
            if (ui.item && ui.item.isEmpty) {
              return false;
            }
          },
          select(event, ui) {
            if (!ui.item || ui.item.isEmpty) {
              return false;
            }
            if (shared) {
              shared.committed[commitKey] = true;
            }
            if (estadoInput) {
              suppressCommitClear = true;
              input.value = ui.item.value;
              setEstadoValue(estadoInput, ui.item.estado || '');
              window.setTimeout(() => {
                suppressCommitClear = false;
              }, 0);
              if (shared) {
                shared.scheduleProbe('cidade', true);
              }
              return false;
            }
            input.value = ui.item.value;
            if (shared && isCurso) {
              shared.scheduleProbe('cursos', true);
            }
          },
        });

        // Com constraint: abre no foco as opções disponíveis (minLength 0).
        input.addEventListener('focus', () => {
          const constraints = currentConstraints();
          const constrained = hasConstraint(isCidade, constraints);
          $(input).autocomplete('option', 'minLength', constrained ? 0 : MIN_LENGTH);
          if (constrained && !input.disabled) {
            $(input).autocomplete('search', input.value);
          }
        });

        const instance = $(input).autocomplete('instance');
        if (instance) {
          $(input).autocomplete('widget').addClass('hero-search-ac');
          instance._resizeMenu = function resizeMenu() {
            this.menu.element.outerWidth(Math.max(field.getBoundingClientRect().width, 220));
          };
          instance._renderItem = function renderItem(ul, item) {
            if (item.isEmpty) {
              const empty = document.createElement('div');
              empty.className = 'hero-search-ac__empty';
              empty.textContent = item.label;
              return $('<li class="ui-state-disabled hero-search-ac__empty-li">')
                .append(empty)
                .appendTo(ul);
            }

            const row = document.createElement('div');
            row.className = 'hero-search-ac__item';

            if (item.estado) {
              // Contrato 040/041: `{Cidade} - {UF} - ({N})` (N numérico).
              const main = document.createElement('span');
              main.className = 'hero-search-ac__main';
              appendHighlighted(main, item.value, this.term || '');
              row.appendChild(main);

              const sep = document.createElement('span');
              sep.className = 'hero-search-ac__sep';
              sep.textContent = ' - ';
              row.appendChild(sep);

              const meta = document.createElement('span');
              meta.className = 'hero-search-ac__meta';
              meta.textContent = `${item.estado} - (${item.count || 0})`;
              row.appendChild(meta);
            }
            else {
              const main = document.createElement('span');
              main.className = 'hero-search-ac__main';
              appendHighlighted(main, item.label || item.value, this.term || '');
              row.appendChild(main);
            }

            return $('<li>').append(row).appendTo(ul);
          };
        }
      });
    },
  };
})(Drupal, once, jQuery);
