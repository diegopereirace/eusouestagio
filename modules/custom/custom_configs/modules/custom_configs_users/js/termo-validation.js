(function (Drupal, once) {
  function getWrapper(input) {
    return input.closest('.form-item') || input.parentElement;
  }

  function setInvalidState(input) {
    var wrapper = getWrapper(input);
    var errorId = 'field-termo-error-js';
    var existingError = document.getElementById(errorId);

    input.classList.add('is-invalid');
    input.setAttribute('aria-invalid', 'true');

    if (wrapper) {
      wrapper.style.border = '1px solid #dc3545';
      wrapper.style.borderRadius = '6px';
      wrapper.style.padding = '8px';
    }

    if (!existingError && wrapper) {
      var error = document.createElement('div');
      error.id = errorId;
      error.className = 'invalid-feedback d-block';
      error.textContent = 'Campo Obrigatório';
      wrapper.appendChild(error);
    }
  }

  function clearInvalidState(input) {
    var wrapper = getWrapper(input);
    var existingError = document.getElementById('field-termo-error-js');

    input.classList.remove('is-invalid');
    input.removeAttribute('aria-invalid');

    if (wrapper) {
      wrapper.style.border = '';
      wrapper.style.borderRadius = '';
      wrapper.style.padding = '';
    }

    if (existingError) {
      existingError.remove();
    }
  }

  Drupal.behaviors.customConfigsUsersTermoValidation = {
    attach: function (context) {
      once('custom-configs-users-termo-submit', 'form[data-drupal-selector="custom-configs-users-candidato-registration-form"]', context).forEach(function (form) {
        var termo = form.querySelector('input[name="field_termo"]');
        if (!termo) {
          return;
        }

        termo.addEventListener('change', function () {
          if (termo.checked) {
            clearInvalidState(termo);
          }
        });

        form.addEventListener('submit', function (event) {
          if (!termo.checked) {
            event.preventDefault();
            event.stopPropagation();
            setInvalidState(termo);
            termo.focus();
            termo.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } else {
            clearInvalidState(termo);
          }
        });
      });
    }
  };
})(Drupal, once);
