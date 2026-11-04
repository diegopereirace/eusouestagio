/**
 * @file
 * Overlay de loading ao ordenar a lista de vagas (/vagas).
 * O select dispara submit GET; o overlay cobre o viewport até a navegação.
 * Segurança: timeout + pageshow (bfcache) evitam loading eterno.
 */
(function (Drupal, once) {
  'use strict';

  const VIEW_SEL = '.view-id-vagas.view-display-id-page_1';
  const OVERLAY_CLASS = 'vagas-lista-sort-overlay';
  const LOADING_CLASS = 'is-sort-loading';
  /** Tempo máximo com overlay se a navegação não concluir. */
  const SAFETY_MS = 20000;

  /** @type {number|null} */
  let safetyTimer = null;

  /**
   * @returns {Element|null}
   */
  function getHost() {
    return (
      document.querySelector(`${VIEW_SEL}.css-vagas-page`) ||
      document.querySelector(VIEW_SEL) ||
      document.querySelector('.css-vagas-page')
    );
  }

  /**
   * @returns {Element|null}
   */
  function getOverlay() {
    return document.body.querySelector(`:scope > .${OVERLAY_CLASS}`);
  }

  function clearSafetyTimer() {
    if (safetyTimer !== null) {
      window.clearTimeout(safetyTimer);
      safetyTimer = null;
    }
  }

  /**
   * @param {Element|null} host
   */
  function hideOverlay(host) {
    clearSafetyTimer();
    const el = host || getHost();
    if (el) {
      el.classList.remove(LOADING_CLASS);
      el.removeAttribute('aria-busy');
      const select = el.querySelector('#vagas-sort-order');
      if (select) {
        select.disabled = false;
      }
    }
    const overlay = getOverlay();
    if (overlay) {
      overlay.remove();
    }
  }

  /**
   * @param {Element} host
   */
  function showOverlay(host) {
    clearSafetyTimer();
    host.classList.add(LOADING_CLASS);
    host.setAttribute('aria-busy', 'true');

    let overlay = getOverlay();
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = OVERLAY_CLASS;
      overlay.setAttribute('role', 'status');
      overlay.setAttribute('aria-live', 'polite');
      overlay.innerHTML =
        '<div class="vagas-lista-sort-overlay__inner">' +
        '<i class="fa-solid fa-arrows-rotate vagas-lista-sort-overlay__spinner" aria-hidden="true"></i>' +
        '<span class="vagas-lista-sort-overlay__text">' +
        Drupal.t('Atualizando vagas…') +
        '</span>' +
        '</div>';
      // body: position:fixed real no viewport (sem containing-block de ancestors).
      document.body.appendChild(overlay);
    }

    const select = host.querySelector('#vagas-sort-order');
    if (select) {
      select.disabled = true;
    }

    safetyTimer = window.setTimeout(() => {
      hideOverlay(host);
    }, SAFETY_MS);
  }

  /**
   * @param {HTMLSelectElement} select
   */
  function onSortChange(select) {
    const form = select.form;
    const host = getHost();
    if (!form || !host) {
      if (form) {
        form.submit();
      }
      return;
    }

    showOverlay(host);

    try {
      // Reabilita o select só para o valor ir no GET (disabled não envia).
      select.disabled = false;
      form.submit();
      select.disabled = true;
    } catch (err) {
      hideOverlay(host);
    }
  }

  // Voltar via bfcache pode restaurar a página com overlay ainda no DOM.
  window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
      hideOverlay();
    }
  });

  Drupal.behaviors.vagasListaSortLoading = {
    attach(context) {
      once('vagas-lista-sort', '#vagas-sort-order', context).forEach((select) => {
        select.addEventListener('change', () => {
          onSortChange(/** @type {HTMLSelectElement} */ (select));
        });
      });
    },
  };
})(Drupal, once);
