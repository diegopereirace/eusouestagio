(function (Drupal, drupalSettings, once) {
  'use strict';

  const CONSENT_COOKIE_NAME = 'lgpd_consent';
  const CONSENT_REJECTED = 'rejected';
  const CONSENT_ACCEPTED = 'accepted';

  function setFunctionalCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = '; expires=' + date.toUTCString();
    document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
  }

  function getCookie(name) {
    const nameEQ = name + '=';
    const cookies = document.cookie.split(';');

    for (let index = 0; index < cookies.length; index += 1) {
      let cookieItem = cookies[index];
      while (cookieItem.charAt(0) === ' ') {
        cookieItem = cookieItem.substring(1, cookieItem.length);
      }
      if (cookieItem.indexOf(nameEQ) === 0) {
        return decodeURIComponent(cookieItem.substring(nameEQ.length, cookieItem.length));
      }
    }

    return null;
  }

  function pushConsentDenied() {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: 'lgpd_consent_update',
      analytics_storage: 'denied',
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
    });
  }

  function hasDecision() {
    const consentValue = getCookie(CONSENT_COOKIE_NAME);
    return consentValue === CONSENT_REJECTED || consentValue === CONSENT_ACCEPTED;
  }

  function hideBanner() {
    const banner = document.querySelector('#lgpd-cookie-banner');
    if (!banner) {
      return;
    }

    banner.classList.remove('is-visible');
    banner.setAttribute('hidden', 'hidden');
  }

  function showBanner() {
    const banner = document.querySelector('#lgpd-cookie-banner');
    if (!banner) {
      return;
    }
    banner.removeAttribute('hidden');
    requestAnimationFrame(() => {
      banner.classList.add('is-visible');
    });
  }

  function writeAcceptedConsent() {
    setFunctionalCookie(CONSENT_COOKIE_NAME, CONSENT_ACCEPTED, 365);
    hideBanner();
  }

  function writeRejectedConsent() {
    setFunctionalCookie(CONSENT_COOKIE_NAME, CONSENT_REJECTED, 365);
    pushConsentDenied();
    hideBanner();
  }

  function ensureBannerMarkup() {
    if (document.querySelector('#lgpd-cookie-banner')) {
      return;
    }

    const bannerElement = document.createElement('div');
    bannerElement.id = 'lgpd-cookie-banner';
    bannerElement.className = 'lgpd-cookie-banner';
    bannerElement.setAttribute('hidden', 'hidden');
    bannerElement.innerHTML = [
      '<div class="lgpd-cookie-banner__content">',
      '  <p class="lgpd-cookie-banner__text">Utilizamos cookies para melhorar sua experiência de navegação. Você pode aceitar ou rejeitar cookies não essenciais.</p>',
      '  <div class="lgpd-cookie-banner__actions">',
      '    <button type="button" class="lgpd-cookie-banner__btn lgpd-cookie-banner__btn--ghost" id="btn-rejeitar-cookies">Negar</button>',
      '    <button type="button" class="lgpd-cookie-banner__btn" id="btn-aceitar-cookies">Aceitar</button>',
      '  </div>',
      '</div>',
    ].join('');

    document.body.appendChild(bannerElement);
  }

  function blockEmbedsWithoutConsent(context) {
    const consentValue = getCookie(CONSENT_COOKIE_NAME);
    const mustBlock = !consentValue || consentValue === CONSENT_REJECTED;

    if (!mustBlock) {
      return;
    }

    once('lgpd-embed-consent-block', '.embed-requires-consent', context).forEach((iframeElement) => {
      const currentSrc = iframeElement.getAttribute('src');
      if (currentSrc) {
        iframeElement.setAttribute('data-src', currentSrc);
        iframeElement.removeAttribute('src');
      }

      iframeElement.setAttribute('data-consent-blocked', 'true');

      const parentContainer = iframeElement.parentElement;
      if (!parentContainer) {
        return;
      }

      if (!parentContainer.querySelector('.embed-consent-warning')) {
        const warningElement = document.createElement('div');
        warningElement.className = 'embed-consent-warning';
        warningElement.textContent = 'Para visualizar este conteúdo, aceite os cookies no banner de consentimento.';
        parentContainer.insertBefore(warningElement, iframeElement);
      }
    });
  }

  function enforceDeniedConsentOnLoad() {
    if (getCookie(CONSENT_COOKIE_NAME) === CONSENT_REJECTED) {
      pushConsentDenied();
      hideBanner();
    }
  }

  Drupal.behaviors.defaultLgpdBanner = {
    attach(context) {
      ensureBannerMarkup();

      once('lgpd-cookie-accept-handler', '#btn-aceitar-cookies', context).forEach((acceptButton) => {
        acceptButton.addEventListener('click', (event) => {
          event.preventDefault();
          writeAcceptedConsent();
        });
      });

      once('lgpd-cookie-reject-handler', '#btn-rejeitar-cookies', context).forEach((rejectButton) => {
        rejectButton.addEventListener('click', (event) => {
          event.preventDefault();
          writeRejectedConsent();
        });
      });

      enforceDeniedConsentOnLoad();
      blockEmbedsWithoutConsent(context);

      if (!hasDecision()) {
        showBanner();
      }
    },
  };
})(Drupal, drupalSettings, once);
