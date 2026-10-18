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
          var labelEl = button.querySelector('.js-salvar-vaga-label');
          var originalText = labelEl
            ? labelEl.textContent.trim()
            : button.textContent.trim();
          var wasSaved = button.classList.contains('ui-btn--saved');

          function renderSalvarLabel(text, saved) {
            button.innerHTML =
              '<i class="fa-regular fa-bookmark" aria-hidden="true"></i>' +
              '<span class="js-salvar-vaga-label">' + text + '</span>';
            button.classList.toggle('ui-btn--saved', !!saved);
          }

          // Desabilita o botão e exibe spinner.
          button.disabled = true;
          button.innerHTML =
            '<span class="salvar-vaga-spinner"></span> ' +
            Drupal.t('Salvando…');

          var toggleUrl = button.getAttribute('data-toggle-url') || Drupal.url('painel/estudante/vagas-salvas/toggle');
          var toggleMethod = button.getAttribute('data-method') || 'POST';

          // Chamada AJAX real ao endpoint de toggle.
          fetch(Drupal.url('session/token'))
            .then(function (res) { return res.text(); })
            .then(function (csrfToken) {
              return fetch(toggleUrl, {
                method: toggleMethod,
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
                renderSalvarLabel(Drupal.t('Remover'), true);
                showActionModal(data.message || Drupal.t('Vaga salva com sucesso.'));
              }
              else if (data.status === 'removed') {
                renderSalvarLabel(Drupal.t('Salvar'), false);
                showActionModal(data.message || Drupal.t('Vaga removida dos salvos.'));
              }
              else {
                renderSalvarLabel(originalText, wasSaved);
                showActionModal(data.message || Drupal.t('Nao foi possivel concluir a operacao.'), 'warning');
              }
            })
            .catch(function (error) {
              button.disabled = false;
              renderSalvarLabel(originalText, wasSaved);
              showActionModal(
                (error && (error.message || error.error)) || Drupal.t('Ocorreu um erro ao salvar a vaga.'),
                'error'
              );
            });
        });
      });

      // ── Botão "Candidatar-se" ──────────────────────────────────────────
      once('candidatar-vaga', '.js-candidatar-vaga', context).forEach(function (btn) {
        btn.addEventListener('click', function (event) {
          var button = this;
          if (button.tagName === 'A') {
            event.preventDefault();
          }
          if (button.getAttribute('aria-disabled') === 'true' || button.disabled) {
            return;
          }

          var nodeId = button.getAttribute('data-node-id');
          var applyUrl = button.getAttribute('data-apply-url') || button.getAttribute('href') || Drupal.url('vaga/apply/' + nodeId);
          var applyMethod = button.getAttribute('data-method') || 'POST';
          var originalHtml = button.innerHTML;

          button.setAttribute('aria-disabled', 'true');
          if (button.tagName === 'BUTTON') {
            button.disabled = true;
          }
          button.innerHTML =
            '<span class="salvar-vaga-spinner"></span> ' +
            Drupal.t('Enviando…');

          fetch(Drupal.url('session/token'))
            .then(function (res) { return res.text(); })
            .then(function (csrfToken) {
              return fetch(applyUrl, {
                method: applyMethod,
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'X-CSRF-Token': csrfToken,
                },
              });
            })
            .then(function (response) {
              return response.json().then(function (data) {
                // 409 = já candidatado: UI estabiliza em "Candidatura Enviada".
                if (response.status === 409 || data.status === 'already') {
                  return Object.assign({}, data, {
                    success: true,
                    status: 'already',
                  });
                }
                if (!response.ok) {
                  throw data;
                }
                return data;
              });
            })
            .then(function (data) {
              if (data.success || data.status === 'candidatado' || data.status === 'already') {
                button.setAttribute('aria-disabled', 'true');
                if (button.tagName === 'BUTTON') {
                  button.disabled = true;
                }
                else {
                  button.removeAttribute('href');
                }
                button.textContent = Drupal.t('Candidatura Enviada');
                showActionModal(data.message || Drupal.t('Sua candidatura foi registrada.'));
              } else {
                button.removeAttribute('aria-disabled');
                if (button.tagName === 'BUTTON') {
                  button.disabled = false;
                }
                button.innerHTML = originalHtml;
                showActionModal(data.message || Drupal.t('Nao foi possivel registrar sua candidatura.'), 'warning');
              }
            })
            .catch(function (error) {
              button.removeAttribute('aria-disabled');
              if (button.tagName === 'BUTTON') {
                button.disabled = false;
              }
              button.innerHTML = originalHtml;
              showActionModal(
                (error && (error.message || error.error)) || Drupal.t('Ocorreu um erro ao enviar sua candidatura.'),
                'error'
              );
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
