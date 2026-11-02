/**
 * @file
 * Normaliza query string do hero /vagas (minúsculas, sem acento).
 */
(function (Drupal, once) {
  'use strict';

  function toQueryValue(text) {
    return String(text || '')
      .trim()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase();
  }

  function syncKeywordFilled(form) {
    const title = form.querySelector('#vagas-hero-title');
    form.classList.toggle(
      'is-keyword-filled',
      !!(title && String(title.value || '').trim()),
    );
  }

  Drupal.behaviors.vagasHeroSearchQuery = {
    attach(context) {
      once('vagas-hero-search-query', 'form.vagas-hero-search__form', context).forEach((form) => {
        const title = form.querySelector('#vagas-hero-title');
        if (title) {
          title.addEventListener('input', () => {
            syncKeywordFilled(form);
          });
          title.addEventListener('change', () => {
            syncKeywordFilled(form);
          });
        }
        syncKeywordFilled(form);

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
      });
    },
  };
})(Drupal, once);
