/**
 * @file
 * Carrossel center-mode reutilizável (peeks laterais + loop infinito opcional).
 *
 * Markup:
 *   [data-carousel-center][data-count][data-loop="1"]
 *     [data-carousel-viewport]
 *       [data-carousel-track]
 *         [data-carousel-slide][data-index]
 *     [data-carousel-dot][data-index]
 *
 * Banners full-bleed: Bootstrap Carousel (já no tema).
 * Este motor: multi-item / center / loop — library default/carousel_center.
 */
(function (Drupal, once) {
  'use strict';

  var TRANSITION_MS = 350;

  /**
   * @param {HTMLElement} track
   * @param {HTMLElement} slide
   * @param {HTMLElement} viewport
   * @param {boolean} instant
   */
  function centerSlide(track, slide, viewport, instant) {
    if (!track || !slide || !viewport) {
      return;
    }
    if (instant) {
      track.style.transition = 'none';
    }
    var offset = slide.offsetLeft - (viewport.clientWidth - slide.offsetWidth) / 2;
    track.style.transform = 'translateX(' + (-offset) + 'px)';
    if (instant) {
      void track.offsetWidth;
      track.style.transition = '';
    }
  }

  /**
   * @param {HTMLElement[]} children
   * @param {HTMLElement} target
   */
  function setActiveClasses(children, target) {
    children.forEach(function (slide) {
      var isActive = slide === target;
      slide.classList.toggle('depoimento-card--active', isActive);
      slide.classList.toggle('depoimento-card--side', !isActive);
    });
  }

  /**
   * @param {NodeListOf<Element>|Element[]} dots
   * @param {number} index
   */
  function syncDots(dots, index) {
    dots.forEach(function (dot, idx) {
      var on = idx === index;
      dot.classList.toggle('is-active', on);
      if (on) {
        dot.setAttribute('aria-current', 'true');
      }
      else {
        dot.removeAttribute('aria-current');
      }
    });
  }

  /**
   * @param {HTMLElement} root
   */
  function initCarousel(root) {
    var track = root.querySelector('[data-carousel-track]');
    var viewport =
      root.querySelector('[data-carousel-viewport]') ||
      (track && track.parentElement);
    var realSlides = track
      ? Array.prototype.slice.call(track.querySelectorAll('[data-carousel-slide]'))
      : [];
    var n = realSlides.length;
    var loop = root.getAttribute('data-loop') === '1' && n > 1;
    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-carousel-dot]'));
    var jumping = false;
    var active = 0;

    if (!track || !viewport || n === 0) {
      return;
    }

    if (loop) {
      var cloneLast = realSlides[n - 1].cloneNode(true);
      var cloneFirst = realSlides[0].cloneNode(true);
      [cloneLast, cloneFirst].forEach(function (clone, idx) {
        clone.removeAttribute('data-carousel-slide');
        clone.removeAttribute('data-index');
        clone.setAttribute('data-carousel-clone', idx === 0 ? 'last' : 'first');
        clone.classList.add('is-clone');
        clone.classList.remove('depoimento-card--active');
        clone.classList.add('depoimento-card--side');
        clone.setAttribute('aria-hidden', 'true');
      });
      track.insertBefore(cloneLast, realSlides[0]);
      track.appendChild(cloneFirst);
    }

    /**
     * @returns {HTMLElement[]}
     */
    function children() {
      return Array.prototype.slice.call(track.children);
    }

    /**
     * @param {number} logical
     * @returns {number}
     */
    function domIndex(logical) {
      return loop ? logical + 1 : logical;
    }

    /**
     * @param {number} logical
     * @param {boolean} instant
     */
    function goTo(logical, instant) {
      var i = ((logical % n) + n) % n;
      active = i;
      root.dataset.activeIndex = String(i);
      var list = children();
      var target = list[domIndex(i)];
      setActiveClasses(list, target);
      syncDots(dots, i);
      centerSlide(track, target, viewport, !!instant);
    }

    /**
     * @param {'next'|'prev'} direction
     */
    function wrap(direction) {
      if (jumping) {
        return;
      }
      jumping = true;
      var list = children();
      var clone = direction === 'next' ? list[n + 1] : list[0];
      var logical = direction === 'next' ? 0 : n - 1;
      setActiveClasses(list, clone);
      syncDots(dots, logical);
      centerSlide(track, clone, viewport, false);
      active = logical;
      root.dataset.activeIndex = String(logical);
      window.setTimeout(function () {
        goTo(logical, true);
        jumping = false;
      }, TRANSITION_MS);
    }

    /**
     * @param {number} delta
     */
    function step(delta) {
      if (n < 2 || jumping) {
        return;
      }
      var next = active + delta;
      if (loop) {
        if (next >= n) {
          wrap('next');
        }
        else if (next < 0) {
          wrap('prev');
        }
        else {
          goTo(next, false);
        }
      }
      else {
        goTo(Math.max(0, Math.min(next, n - 1)), false);
      }
    }

    goTo(0, true);

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        if (jumping) {
          return;
        }
        var idx = parseInt(dot.getAttribute('data-index') || '0', 10);
        if (loop && active === n - 1 && idx === 0) {
          wrap('next');
          return;
        }
        if (loop && active === 0 && idx === n - 1) {
          wrap('prev');
          return;
        }
        goTo(idx, false);
      });
    });

    var startX = 0;
    var deltaX = 0;
    var tracking = false;

    root.addEventListener(
      'pointerdown',
      function (e) {
        if (e.pointerType === 'mouse' && e.button !== 0) {
          return;
        }
        tracking = true;
        startX = e.clientX;
        deltaX = 0;
        try {
          root.setPointerCapture(e.pointerId);
        }
        catch (err) {
          // Ignore.
        }
      },
      { passive: true }
    );

    root.addEventListener(
      'pointermove',
      function (e) {
        if (tracking) {
          deltaX = e.clientX - startX;
        }
      },
      { passive: true }
    );

    root.addEventListener('pointerup', function () {
      if (!tracking) {
        return;
      }
      tracking = false;
      if (deltaX <= -40) {
        step(1);
      }
      else if (deltaX >= 40) {
        step(-1);
      }
      deltaX = 0;
    });

    root.addEventListener('pointercancel', function () {
      tracking = false;
      deltaX = 0;
    });

    window.addEventListener(
      'resize',
      function () {
        if (!jumping) {
          goTo(active, true);
        }
      },
      { passive: true }
    );
  }

  Drupal.behaviors.carouselCenter = {
    attach: function (context) {
      once('carousel-center', '[data-carousel-center]', context).forEach(initCarousel);
    },
  };
})(Drupal, once);
