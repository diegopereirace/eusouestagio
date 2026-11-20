(function (Drupal, drupalSettings, once) {
    'use strict';

    Drupal.behaviors.blockBreakpoint = {
        attach: function (context) {
        var breakpoints = (drupalSettings.blockBreakpoint && drupalSettings.blockBreakpoint.breakpoints) || {};

        once('block-breakpoint', '[data-breakpoint]', context).forEach(function (el) {
            var id = el.getAttribute('data-breakpoint');
            var mq = breakpoints[id];

            if (!mq) {
            return;
            }

            if (!window.matchMedia(mq).matches) {
            el.remove();
            }
        });
        }
    };
})(Drupal, drupalSettings, once);
