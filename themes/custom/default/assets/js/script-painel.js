/**
 * @file
 * Comportamento do botão "Salvar Vaga" na página de detalhe da vaga.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  Drupal.behaviors.scriptPainel = {
    attach: function (context) {
      once('salvar-vaga', '.js-salvar-vaga', context).forEach(function (btn) {
        btn.addEventListener('click', function () {
          var button = this;
          var nodeId = button.getAttribute('data-node-id');
          var originalText = button.textContent.trim();

          // Desabilita o botão e exibe spinner.
          button.disabled = true;
          button.innerHTML =
            '<span class="salvar-vaga-spinner"></span> ' +
            Drupal.t('Salvando…');

          // Chamada AJAX real ao endpoint de toggle.
          fetch(Drupal.url('session/token'))
            .then(function (res) { return res.text(); })
            .then(function (csrfToken) {
              return fetch(Drupal.url('painel/estudante/vagas-salvas/toggle'), {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'X-CSRF-Token': csrfToken,
                },
                body: 'nid=' + encodeURIComponent(nodeId),
              });
            })
            .then(function (response) {
              return response.json();
            })
            .then(function (data) {
              button.disabled = false;

              if (data.status === 'saved') {
                button.textContent = Drupal.t('Remover dos Salvos');
                button.classList.add('ui-btn--saved');
              }
              else if (data.status === 'removed') {
                button.textContent = Drupal.t('Salvar Vaga');
                button.classList.remove('ui-btn--saved');
              }
              else {
                button.textContent = originalText;
              }
            })
            .catch(function () {
              button.disabled = false;
              button.textContent = originalText;
            });
        });
      });

      // ── Botão "Candidatar-se" ──────────────────────────────────────────
      once('candidatar-vaga', '.js-candidatar-vaga', context).forEach(function (btn) {
        btn.addEventListener('click', function () {
          var button = this;
          var nodeId = button.getAttribute('data-node-id');

          // Trava o botão e exibe spinner (mockup de loading).
          button.disabled = true;
          button.innerHTML =
            '<span class="salvar-vaga-spinner"></span> ' +
            Drupal.t('Enviando…');

          fetch(Drupal.url('session/token'))
            .then(function (res) { return res.text(); })
            .then(function (csrfToken) {
              return fetch(Drupal.url('painel/estudante/vagas/candidatar'), {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'X-CSRF-Token': csrfToken,
                },
                body: 'nid=' + encodeURIComponent(nodeId),
              });
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (data.status === 'candidatado' || data.status === 'already') {
                // Substitui o botão por texto informativo.
                var label = document.createElement('p');
                label.className = 'vaga-ja-aplicada';
                label.textContent = Drupal.t('Vaga já aplicada');
                button.parentNode.replaceChild(label, button);
              } else {
                button.disabled = false;
                button.textContent = Drupal.t('Candidatar-se');
              }
            })
            .catch(function () {
              button.disabled = false;
              button.textContent = Drupal.t('Candidatar-se');
            });
        });
      });
    },
  };
})(Drupal, drupalSettings, once);
