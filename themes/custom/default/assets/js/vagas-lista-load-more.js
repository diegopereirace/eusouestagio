/**
 * @file
 * Load more estilo Figma em /vagas (View page_1).
 * Preserva rows já carregadas e anexa a próxima página via AJAX da View.
 */
(function (Drupal, $) {
  'use strict';

  // display_id page_1 → classe view-display-id-page_1 (underscore).
  const VIEW_SEL = '.view-id-vagas.view-display-id-page_1';

  /** @type {string|null} */
  let preservedRowsHtml = null;

  /**
   * @param {Element|null} viewEl
   */
  function enhancePager(viewEl) {
    if (!viewEl) {
      return;
    }

    const wrap = viewEl.querySelector('.vagas-lista-pager');
    const next = viewEl.querySelector('.vagas-lista-pager a[rel="next"]');

    if (!wrap) {
      return;
    }

    if (!next) {
      wrap.hidden = true;
      wrap.setAttribute('aria-hidden', 'true');
      return;
    }

    wrap.hidden = false;
    wrap.removeAttribute('aria-hidden');

    viewEl.querySelectorAll('.vagas-lista-pager .pagination .page-item').forEach((li) => {
      li.hidden = !li.querySelector('a[rel="next"]');
    });

    if (!next.classList.contains('vagas-lista-load-more__btn')) {
      next.classList.add('vagas-lista-load-more__btn');
      next.setAttribute('aria-label', Drupal.t('Carregar mais vagas'));
      next.innerHTML =
        '<i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>' +
        '<span>' +
        Drupal.t('Carregar mais vagas') +
        '</span>';
    }
  }

  function enhanceAllPagers() {
    document.querySelectorAll(VIEW_SEL).forEach((viewEl) => {
      enhancePager(viewEl);
    });
  }

  /**
   * Restaura cards da página anterior após o replace AJAX da View.
   */
  function restorePreservedRows() {
    if (!preservedRowsHtml) {
      return false;
    }

    const list = document.querySelector(`${VIEW_SEL} .vagas-lista`);
    if (!list) {
      return false;
    }

    const html = preservedRowsHtml;
    preservedRowsHtml = null;
    list.insertAdjacentHTML('afterbegin', html);

    const viewEl = list.closest('.view');
    viewEl?.classList.remove('is-loading-more');
    enhancePager(viewEl);
    return true;
  }

  // Capture phase: Drupal.ajax em geral faz stopPropagation no bubble.
  document.addEventListener(
    'click',
    (event) => {
      const next = event.target.closest?.(`${VIEW_SEL} .vagas-lista-pager a[rel="next"]`);
      if (!next) {
        return;
      }
      const viewEl = next.closest(VIEW_SEL);
      const list = viewEl ? viewEl.querySelector('.vagas-lista') : null;
      if (list) {
        preservedRowsHtml = list.innerHTML;
        viewEl.classList.add('is-loading-more');
      }
    },
    true,
  );

  function patchAjaxInsert() {
    if (Drupal.vagasListaLoadMorePatched || !Drupal.AjaxCommands?.prototype?.insert) {
      return;
    }
    Drupal.vagasListaLoadMorePatched = true;
    const originalInsert = Drupal.AjaxCommands.prototype.insert;
    Drupal.AjaxCommands.prototype.insert = function (ajax, response, status) {
      originalInsert.call(this, ajax, response, status);
      if (!preservedRowsHtml) {
        enhanceAllPagers();
        return;
      }
      window.setTimeout(() => {
        restorePreservedRows();
        enhanceAllPagers();
      }, 0);
    };
  }

  $(document).on('ajaxError.vagasLoadMore', () => {
    preservedRowsHtml = null;
    document.querySelectorAll(`${VIEW_SEL}.is-loading-more`).forEach((el) => {
      el.classList.remove('is-loading-more');
    });
  });

  Drupal.behaviors.vagasListaLoadMore = {
    // Sem once no root da View: após AJAX o context costuma SER o .view.
    attach() {
      patchAjaxInsert();
      enhanceAllPagers();
      restorePreservedRows();
    },
  };
})(Drupal, jQuery);
