/**
 * @file
 * Comportamento do botão de candidatura.
 */
(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.candidatura = {
    attach: function (context, settings) {
      once('candidatura', '.js-candidatar-vaga', context).forEach(function (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          var $button = $(el);
          var nodeId = $button.data('node-id');

          $button.prop('disabled', true).text(Drupal.t('Enviando...'));

          $.ajax({
            url: '/vaga/apply/' + nodeId,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
              if (response.success) {
                $button.text(Drupal.t('Candidatura Enviada'));
              } else {
                $button.prop('disabled', false).text(Drupal.t('Candidatar-se'));
                alert(response.message || Drupal.t('Ocorreu um erro. Tente novamente.'));
              }
            },
            error: function (xhr) {
              $button.prop('disabled', false).text(Drupal.t('Candidatar-se'));
              var message = Drupal.t('Ocorreu um erro inesperado. Tente novamente mais tarde.');
              if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
              }
              alert(message);
            }
          });
        });
      });
    }
  };

})(jQuery, Drupal, once);
