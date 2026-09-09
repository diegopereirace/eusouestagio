/**
 * @file
 * Inicializa o carrossel do banner (BigPipe/Ajax + prefers-reduced-motion).
 */
(function (Drupal, once, bootstrap) {
  'use strict';

  Drupal.behaviors.bannerCarousel = {
    attach: function (context) {
      once('banner-carousel', '.banner-carousel:not(.banner-carousel--single)', context).forEach(function (el) {
        if (!bootstrap || !bootstrap.Carousel) {
          return;
        }
        var carousel = bootstrap.Carousel.getOrCreateInstance(el);
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
          carousel.pause();
        }
      });
    }
  };
})(Drupal, once, window.bootstrap);
