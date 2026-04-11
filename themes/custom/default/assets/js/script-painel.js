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

      // ── Painel Moderador: Atualizar Status de Candidatura ─────────────
      once('salvar-status-candidatura', '.js-salvar-status', context).forEach(function (btn) {
        btn.addEventListener('click', function () {
          var button    = this;
          var nid       = button.getAttribute('data-nid');
          var row       = button.closest('tr');
          var select    = row.querySelector('.js-status-select');
          var newStatus = select.value;
          var table     = button.closest('table');
          var updateUrl = table.getAttribute('data-update-url');
          var badge     = row.querySelector('.candidatura-status-badge');

          button.disabled = true;
          button.textContent = Drupal.t('Salvando…');

          fetch(Drupal.url('session/token'))
            .then(function (res) { return res.text(); })
            .then(function (csrfToken) {
              return fetch(updateUrl, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'X-CSRF-Token': csrfToken,
                },
                body: 'nid=' + encodeURIComponent(nid) + '&status=' + encodeURIComponent(newStatus),
              });
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              button.disabled = false;
              button.textContent = Drupal.t('Salvar');
              if (data.status === 'ok' && badge) {
                badge.className = badge.className
                  .replace(/candidatura-status--\S+/g, '')
                  .trim();
                badge.classList.add('candidatura-status--' + newStatus);
                badge.textContent = select.options[select.selectedIndex].text.trim();
              }
            })
            .catch(function () {
              button.disabled = false;
              button.textContent = Drupal.t('Salvar');
            });
        });
      });
    },
  };
})(Drupal, drupalSettings, once);
