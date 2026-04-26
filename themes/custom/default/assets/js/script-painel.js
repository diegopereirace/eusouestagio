/**
 * @file
 * Comportamento do botão "Salvar Vaga" na página de detalhe da vaga.
 */

(function (Drupal, drupalSettings, once) {
  'use strict';

  function cleanupModalState() {
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });

    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
  }

  function getActionModal() {
    var modalElement = document.getElementById('script-painel-feedback-modal');

    if (modalElement) {
      return modalElement;
    }

    modalElement = document.createElement('div');
    modalElement.className = 'modal fade app-feedback-modal';
    modalElement.id = 'script-painel-feedback-modal';
    modalElement.tabIndex = -1;
    modalElement.setAttribute('aria-labelledby', 'script-painel-feedback-modal-label');
    modalElement.setAttribute('aria-hidden', 'true');
    modalElement.innerHTML =
      '<div class="modal-dialog modal-dialog-centered" role="document">' +
        '<div class="modal-content app-feedback-modal__content">' +
          '<div class="modal-header app-feedback-modal__header">' +
            '<div class="app-feedback-modal__eyebrow">' + Drupal.t('Notificacao') + '</div>' +
            '<h5 class="modal-title app-feedback-modal__title" id="script-painel-feedback-modal-label"></h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' + Drupal.t('Close') + '"></button>' +
          '</div>' +
          '<div class="modal-body app-feedback-modal__body text-center"></div>' +
          '<div class="modal-footer app-feedback-modal__footer d-block text-center">' +
            '<button type="button" class="btn btn-primary app-feedback-modal__button" data-bs-dismiss="modal">' + Drupal.t('Close') + '</button>' +
          '</div>' +
        '</div>' +
      '</div>';

    document.body.appendChild(modalElement);

    modalElement.addEventListener('hidden.bs.modal', function () {
      cleanupModalState();
    });

    return modalElement;
  }

  function getModalMeta(type) {
    if (type === 'error') {
      return {
        title: Drupal.t('Erro'),
        className: 'app-feedback-modal__content--error',
      };
    }

    if (type === 'warning') {
      return {
        title: Drupal.t('Atencao'),
        className: 'app-feedback-modal__content--warning',
      };
    }

    return {
      title: Drupal.t('Sucesso'),
      className: 'app-feedback-modal__content--status',
    };
  }

  function showActionModal(message, type) {
    var modalElement;
    var modalBody;
    var modalTitle;
    var modalContent;
    var modal;
    var meta;

    if (!message || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
      return;
    }

    modalElement = getActionModal();
    modalBody = modalElement.querySelector('.modal-body');
    modalTitle = modalElement.querySelector('.modal-title');
    modalContent = modalElement.querySelector('.modal-content');
    meta = getModalMeta(type);

    modalTitle.textContent = meta.title;
    modalBody.textContent = message;
    modalContent.classList.remove(
      'app-feedback-modal__content--status',
      'app-feedback-modal__content--warning',
      'app-feedback-modal__content--error'
    );
    modalContent.classList.add(meta.className);

    cleanupModalState();
    modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
      backdrop: true,
      focus: true,
      keyboard: true,
    });
    modal.show();
  }

  function parseJsonResponse(response) {
    return response.json().then(function (data) {
      if (!response.ok) {
        throw data;
      }

      return data;
    });
  }

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
            .then(parseJsonResponse)
            .then(function (data) {
              button.disabled = false;

              if (data.status === 'saved') {
                button.textContent = Drupal.t('Remover dos Salvos');
                button.classList.add('ui-btn--saved');
                showActionModal(data.message || Drupal.t('Vaga salva com sucesso.'));
              }
              else if (data.status === 'removed') {
                button.textContent = Drupal.t('Salvar Vaga');
                button.classList.remove('ui-btn--saved');
                showActionModal(data.message || Drupal.t('Vaga removida dos salvos.'));
              }
              else {
                button.textContent = originalText;
                showActionModal(data.message || Drupal.t('Nao foi possivel concluir a operacao.'), 'warning');
              }
            })
            .catch(function (error) {
              button.disabled = false;
              button.textContent = originalText;
              showActionModal((error && error.error) || Drupal.t('Ocorreu um erro ao salvar a vaga.'), 'error');
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
            .then(parseJsonResponse)
            .then(function (data) {
              if (data.status === 'candidatado' || data.status === 'already') {
                // Substitui o botão por texto informativo.
                var label = document.createElement('p');
                label.className = 'vaga-ja-aplicada';
                label.textContent = Drupal.t('Vaga já aplicada');
                button.parentNode.replaceChild(label, button);
                showActionModal(data.message || Drupal.t('Sua candidatura foi registrada.'));
              } else {
                button.disabled = false;
                button.textContent = Drupal.t('Candidatar-se');
                showActionModal(data.message || Drupal.t('Nao foi possivel registrar sua candidatura.'), 'warning');
              }
            })
            .catch(function (error) {
              button.disabled = false;
              button.textContent = Drupal.t('Candidatar-se');
              showActionModal((error && error.error) || Drupal.t('Ocorreu um erro ao enviar sua candidatura.'), 'error');
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
            .then(parseJsonResponse)
            .then(function (data) {
              button.disabled = false;
              button.textContent = Drupal.t('Salvar');
              if (data.status === 'ok' && badge) {
                badge.className = badge.className
                  .replace(/candidatura-status--\S+/g, '')
                  .trim();
                badge.classList.add('candidatura-status--' + newStatus);
                badge.textContent = select.options[select.selectedIndex].text.trim();
                showActionModal(data.message || Drupal.t('Status da candidatura atualizado com sucesso.'));
              }
              else {
                showActionModal(data.message || Drupal.t('Nao foi possivel atualizar o status da candidatura.'), 'warning');
              }
            })
            .catch(function (error) {
              button.disabled = false;
              button.textContent = Drupal.t('Salvar');
              showActionModal((error && error.error) || Drupal.t('Ocorreu um erro ao atualizar o status da candidatura.'), 'error');
            });
        });
      });
    },
  };
})(Drupal, drupalSettings, once);
