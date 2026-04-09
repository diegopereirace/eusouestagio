/**
 * @file
 * Comportamento do botão "Salvar Vaga" na página de detalhe da vaga.
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.scriptPainel
   = {
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

          // Simulação de chamada AJAX (placeholder).
          setTimeout(function () {
            // Restaura estado original.
            button.disabled = false;
            button.innerHTML = originalText;
          }, 1500);
        });
      });
    }
  };
})(Drupal, once);
