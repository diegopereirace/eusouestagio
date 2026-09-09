/**
 * @file
 * Autocomplete for hero course field (core/drupal.autocomplete).
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.heroSearchAutocomplete = {
    attach(context) {
      once('hero-search-ac', 'input[data-autocomplete-path]', context).forEach((input) => {
        const path = input.getAttribute('data-autocomplete-path');
        if (!path || !Drupal.autocomplete) {
          return;
        }
        // Drupal.autocomplete expects path with ?q= appended by jQuery UI.
        input.setAttribute('data-autocomplete-path', path);
        // Reuse core autocomplete: source hits /api/cursos/autocomplete?q=
        jQuery(input).autocomplete({
          source(request, response) {
            jQuery.getJSON(path, { q: request.term }, (data) => {
              response((data || []).map((item) => ({
                value: item.value,
                label: item.label,
              })));
            });
          },
          minLength: 2,
          select(event, ui) {
            if (ui.item) {
              input.value = ui.item.value;
            }
          },
        });
      });
    },
  };
})(Drupal, once);
