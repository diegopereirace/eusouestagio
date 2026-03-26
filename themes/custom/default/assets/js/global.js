(function (Drupal) {

  'use strict';

  var CONSENT_KEY = 'default_lgpd_cookie_consent';

  function setCookie(name, value, days) {
    var expires = '';
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
  }

  function getCookie(name) {
    var nameEQ = name + '=';
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) === ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
  }

  function hasDecision() {
    try {
      if (window.localStorage) {
        var decision = window.localStorage.getItem(CONSENT_KEY);
        if (decision === 'accepted' || decision === 'denied') {
          return true;
        }
      }
    }
    catch (e) {}

    var cookieDecision = getCookie(CONSENT_KEY);
    return cookieDecision === 'accepted' || cookieDecision === 'denied';
  }

  function persistDecision(decision) {
    if (decision !== 'accepted' && decision !== 'denied') {
      return;
    }

    try {
      if (window.localStorage) {
        window.localStorage.setItem(CONSENT_KEY, decision);
      }
    }
    catch (e) {}

    setCookie(CONSENT_KEY, decision, 365);
  }

  function hasConsent() {
    try {
      if (window.localStorage && window.localStorage.getItem(CONSENT_KEY) === 'accepted') {
        return true;
      }
    }
    catch (e) {}

    return getCookie(CONSENT_KEY) === 'accepted';
  }

  function removeBanner() {
    var banner = document.getElementById('lgpd-cookie-banner');
    if (banner) {
      banner.classList.remove('is-visible');
      setTimeout(function () {
        if (banner && banner.parentNode) {
          banner.parentNode.removeChild(banner);
        }
      }, 200);
    }
  }

  function createBanner() {
    if (document.getElementById('lgpd-cookie-banner')) {
      return;
    }

    var banner = document.createElement('div');
    banner.id = 'lgpd-cookie-banner';
    banner.className = 'lgpd-cookie-banner';
    banner.innerHTML = [
      '<div class="lgpd-cookie-banner__content">',
      '  <p class="lgpd-cookie-banner__text">Utilizamos cookies para melhorar sua experiência de navegação. Ao clicar em "Aceitar", você concorda com o uso de cookies.</p>',
      '  <div class="lgpd-cookie-banner__actions">',
      '    <button type="button" class="lgpd-cookie-banner__btn lgpd-cookie-banner__btn--ghost" id="lgpd-cookie-deny">Negar</button>',
      '    <button type="button" class="lgpd-cookie-banner__btn" id="lgpd-cookie-accept">Aceitar</button>',
      '  </div>',
      '</div>'
    ].join('');

    document.body.appendChild(banner);

    var acceptButton = document.getElementById('lgpd-cookie-accept');
    if (acceptButton) {
      acceptButton.addEventListener('click', function () {
        persistDecision('accepted');
        removeBanner();
      });
    }

    var denyButton = document.getElementById('lgpd-cookie-deny');
    if (denyButton) {
      denyButton.addEventListener('click', function () {
        persistDecision('denied');
        removeBanner();
      });
    }

    requestAnimationFrame(function () {
      banner.classList.add('is-visible');
    });
  }

  Drupal.behaviors.bootstrap_barrio_subtheme = {
    attach: function () {
      if (!hasDecision()) {
        createBanner();
      }
    }
  };

})(Drupal);
