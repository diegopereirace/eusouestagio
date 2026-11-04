/**
 * @file
 * Autocomplete vanilla do hero /vagas (3 inputs; sem jQuery UI).
 */
(function (Drupal, once) {
  'use strict';

  const MIN_LENGTH = 2;
  const DEBOUNCE_MS = 300;
  const CACHE_MAX = 50;
  const suggestionCache = new Map();

  function normalizeTerm(text) {
    return String(text || '')
      .trim()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase();
  }

  function crossFilterParams(form, activeInput) {
    const params = {};
    if (!form) {
      return params;
    }
    form.querySelectorAll('input[data-ac-kind]').forEach((el) => {
      if (el === activeInput) {
        return;
      }
      const kind = el.getAttribute('data-ac-kind');
      const value = String(el.value || '').trim();
      if (value.length < MIN_LENGTH) {
        return;
      }
      if (kind === 'vagas') {
        params.title = value;
      }
      else if (kind === 'cidades') {
        params.cidade = value;
      }
      else if (kind === 'cursos') {
        params.curso = value;
      }
    });
    return params;
  }

  function cacheKey(endpoint, term, extra) {
    const parts = [endpoint, normalizeTerm(term)];
    Object.keys(extra || {}).sort().forEach((key) => {
      parts.push(key, normalizeTerm(extra[key]));
    });
    return parts.join('|');
  }

  function cacheGet(key) {
    if (!suggestionCache.has(key)) {
      return null;
    }
    // Refresh FIFO order.
    const value = suggestionCache.get(key);
    suggestionCache.delete(key);
    suggestionCache.set(key, value);
    return value;
  }

  function cacheSet(key, value) {
    if (suggestionCache.has(key)) {
      suggestionCache.delete(key);
    }
    suggestionCache.set(key, value);
    while (suggestionCache.size > CACHE_MAX) {
      const oldest = suggestionCache.keys().next().value;
      suggestionCache.delete(oldest);
    }
  }

  function adaptItems(kind, raw) {
    if (!Array.isArray(raw)) {
      return [];
    }
    return raw.map((item) => {
      const value = String(item.value ?? '');
      const label = String(item.label ?? value);
      const out = { value, label };
      if (kind === 'vagas') {
        const type = item.type === 'empresa' ? 'empresa' : 'vaga';
        out.badge = type === 'empresa' ? 'Empresa' : 'Vaga';
        out.type = type;
      }
      return out;
    });
  }

  /**
   * Destaca o trecho casado (sem acento/caixa) com <mark> via nós DOM.
   */
  function appendHighlightedText(container, label, query) {
    const needle = normalizeTerm(query);
    if (!needle) {
      container.appendChild(document.createTextNode(label));
      return;
    }

    const normChars = [];
    const map = [];
    for (let i = 0; i < label.length; i += 1) {
      const ch = label[i];
      const n = normalizeTerm(ch);
      for (let j = 0; j < n.length; j += 1) {
        normChars.push(n[j]);
        map.push(i);
      }
    }
    const norm = normChars.join('');
    const start = norm.indexOf(needle);
    if (start < 0) {
      container.appendChild(document.createTextNode(label));
      return;
    }
    const end = start + needle.length - 1;
    const origStart = map[start];
    const origEnd = map[end] + 1;

    if (origStart > 0) {
      container.appendChild(document.createTextNode(label.slice(0, origStart)));
    }
    const mark = document.createElement('mark');
    mark.textContent = label.slice(origStart, origEnd);
    container.appendChild(mark);
    if (origEnd < label.length) {
      container.appendChild(document.createTextNode(label.slice(origEnd)));
    }
  }

  function createFieldController(input, form) {
    const endpoint = input.getAttribute('data-ac-endpoint') || '';
    const kind = input.getAttribute('data-ac-kind') || '';
    if (!endpoint || !kind) {
      return null;
    }

    const field = input.closest('.vagas-hero-search__field');
    if (!field) {
      return null;
    }

    const listId = `vagas-hero-ac-${input.id || kind}`;
    const list = document.createElement('ul');
    list.className = 'vagas-hero-search__suggestions';
    list.id = listId;
    list.setAttribute('role', 'listbox');
    list.hidden = true;

    const live = document.createElement('span');
    live.className = 'visually-hidden';
    live.setAttribute('role', 'status');
    live.setAttribute('aria-live', 'polite');

    field.appendChild(list);
    field.appendChild(live);

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', listId);
    input.setAttribute('aria-haspopup', 'listbox');

    const state = {
      timer: null,
      controller: null,
      token: 0,
      items: [],
      activeIndex: -1,
      open: false,
      suppressInput: false,
    };

    function setLive(message) {
      live.textContent = message;
    }

    function close() {
      state.open = false;
      state.activeIndex = -1;
      list.hidden = true;
      list.replaceChildren();
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
    }

    function setActive(index) {
      const options = list.querySelectorAll('[role="option"]');
      options.forEach((opt, i) => {
        const active = i === index;
        opt.classList.toggle('is-active', active);
        opt.setAttribute('aria-selected', active ? 'true' : 'false');
      });
      state.activeIndex = index;
      if (index >= 0 && options[index]) {
        input.setAttribute('aria-activedescendant', options[index].id);
        options[index].scrollIntoView({ block: 'nearest' });
      }
      else {
        input.removeAttribute('aria-activedescendant');
      }
    }

    function selectItem(item) {
      state.suppressInput = true;
      input.value = item.value;
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
      close();
      setLive('');
      window.setTimeout(() => {
        state.suppressInput = false;
      }, 0);
    }

    function renderEmpty() {
      const li = document.createElement('li');
      li.className = 'vagas-hero-search__suggestion vagas-hero-search__suggestion--empty';
      li.setAttribute('role', 'presentation');
      li.textContent = Drupal.t('Nenhum resultado encontrado');
      li.addEventListener('mousedown', (event) => {
        event.preventDefault();
      });
      list.appendChild(li);
      list.hidden = false;
      state.open = true;
      state.activeIndex = -1;
      input.setAttribute('aria-expanded', 'true');
      input.removeAttribute('aria-activedescendant');
      setLive(Drupal.t('Nenhum resultado encontrado'));
    }

    function render(items, query) {
      state.items = items;
      list.replaceChildren();
      if (!items.length) {
        renderEmpty();
        return;
      }

      items.forEach((item, index) => {
        const li = document.createElement('li');
        li.className = 'vagas-hero-search__suggestion';
        li.id = `${listId}-${index}`;
        li.setAttribute('role', 'option');
        li.setAttribute('aria-selected', 'false');

        const text = document.createElement('span');
        text.className = 'vagas-hero-search__suggestion-text';
        appendHighlightedText(text, item.label, query);
        li.appendChild(text);

        if (item.badge) {
          const badge = document.createElement('span');
          badge.className = 'vagas-hero-search__suggestion-badge';
          badge.textContent = item.badge;
          li.appendChild(badge);
        }

        li.addEventListener('mousedown', (event) => {
          event.preventDefault();
          selectItem(item);
        });

        list.appendChild(li);
      });

      list.hidden = false;
      state.open = true;
      state.activeIndex = -1;
      input.setAttribute('aria-expanded', 'true');
      input.removeAttribute('aria-activedescendant');
      setLive(`${items.length} sugestão${items.length === 1 ? '' : 'ões'} disponível${items.length === 1 ? '' : 'eis'}`);
    }

    function abortPending() {
      if (state.controller) {
        state.controller.abort();
        state.controller = null;
      }
    }

    function fetchSuggestions(term) {
      const filters = crossFilterParams(form, input);
      const key = cacheKey(endpoint, term, filters);
      const cached = cacheGet(key);
      if (cached) {
        render(cached, term);
        return;
      }

      abortPending();
      const token = ++state.token;
      const controller = new AbortController();
      state.controller = controller;

      const url = new URL(endpoint, window.location.origin);
      url.searchParams.set('q', term);
      Object.keys(filters).forEach((name) => {
        url.searchParams.set(name, filters[name]);
      });

      fetch(url.toString(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        signal: controller.signal,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error('bad status');
          }
          return response.json();
        })
        .then((data) => {
          if (token !== state.token) {
            return;
          }
          const items = adaptItems(kind, data);
          cacheSet(key, items);
          render(items, term);
        })
        .catch((error) => {
          if (error && error.name === 'AbortError') {
            return;
          }
          if (token !== state.token) {
            return;
          }
          close();
          setLive('');
        })
        .finally(() => {
          if (state.controller === controller) {
            state.controller = null;
          }
        });
    }

    function schedule() {
      if (state.suppressInput) {
        return;
      }
      window.clearTimeout(state.timer);
      const term = String(input.value || '').trim();
      if (term.length < MIN_LENGTH) {
        abortPending();
        close();
        setLive('');
        return;
      }
      state.timer = window.setTimeout(() => {
        fetchSuggestions(term);
      }, DEBOUNCE_MS);
    }

    input.addEventListener('input', schedule);

    input.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        if (state.open) {
          event.preventDefault();
          close();
        }
        return;
      }

      if (event.key === 'Tab') {
        close();
        return;
      }

      if (event.key === 'ArrowDown') {
        if (!state.open && state.items.length) {
          render(state.items, input.value);
        }
        if (state.open && state.items.length) {
          event.preventDefault();
          const next = state.activeIndex < state.items.length - 1
            ? state.activeIndex + 1
            : 0;
          setActive(next);
        }
        return;
      }

      if (event.key === 'ArrowUp') {
        if (state.open && state.items.length) {
          event.preventDefault();
          const next = state.activeIndex <= 0
            ? state.items.length - 1
            : state.activeIndex - 1;
          setActive(next);
        }
        return;
      }

      if (event.key === 'Enter') {
        if (state.open && state.activeIndex >= 0 && state.items[state.activeIndex]) {
          event.preventDefault();
          selectItem(state.items[state.activeIndex]);
        }
        // Sem item ativo / dropdown fechado → deixa o submit nativo / vagas-hero-search.js.
      }
    });

    input.addEventListener('blur', () => {
      window.setTimeout(() => {
        if (!field.contains(document.activeElement)) {
          close();
        }
      }, 150);
    });

    return {
      close,
      contains(node) {
        return field.contains(node);
      },
    };
  }

  Drupal.behaviors.vagasHeroAutocomplete = {
    attach(context) {
      once('vagas-hero-ac', 'form.vagas-hero-search__form', context).forEach((form) => {
        const controllers = [];
        form.querySelectorAll('input[data-ac-endpoint][data-ac-kind]').forEach((input) => {
          const ctrl = createFieldController(input, form);
          if (ctrl) {
            controllers.push(ctrl);
          }
        });

        function closeOthers(exceptNode) {
          controllers.forEach((c) => {
            if (!exceptNode || !c.contains(exceptNode)) {
              c.close();
            }
          });
        }

        form.addEventListener('focusin', (event) => {
          if (event.target instanceof Node) {
            closeOthers(event.target);
          }
        });

        document.addEventListener('mousedown', (event) => {
          const target = event.target;
          if (!(target instanceof Node)) {
            return;
          }
          closeOthers(target);
        });

        window.addEventListener('pageshow', (event) => {
          if (event.persisted) {
            controllers.forEach((c) => c.close());
          }
        });
      });
    },
  };
})(Drupal, once);
