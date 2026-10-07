/**
 * @file
 * Toggle do popover Compartilhar no detalhe da vaga.
 */
(function (Drupal, once) {
  'use strict';

  function closeShare(root, toggle, panel) {
    panel.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
    root.classList.remove('is-open');
  }

  function openShare(root, toggle, panel) {
    panel.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    root.classList.add('is-open');
  }

  Drupal.behaviors.vagaDetalheShare = {
    attach(context) {
      once('vaga-share', '.js-vaga-share', context).forEach((root) => {
        const toggle = root.querySelector('.js-vaga-share-toggle');
        const panel = root.querySelector('.js-vaga-share-panel');
        if (!toggle || !panel) {
          return;
        }

        toggle.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          if (panel.hidden) {
            openShare(root, toggle, panel);
          }
          else {
            closeShare(root, toggle, panel);
          }
        });

        panel.addEventListener('click', (event) => {
          event.stopPropagation();
        });

        document.addEventListener('click', () => {
          if (!panel.hidden) {
            closeShare(root, toggle, panel);
          }
        });

        document.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && !panel.hidden) {
            closeShare(root, toggle, panel);
            toggle.focus();
          }
        });
      });
    },
  };
})(Drupal, once);
