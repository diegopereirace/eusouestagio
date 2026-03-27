(function (Drupal, once) {
  'use strict';

  function getWrapper(input) {
    return input.closest('.js-form-item') || input.closest('[class*="col-"]') || input.parentElement;
  }

  function getErrorId(input) {
    return (input.name || input.id || 'field').replace(/[^a-z0-9_-]/gi, '-') + '-error-js';
  }

  function setInvalidState(input, message) {
    var wrapper = getWrapper(input);
    var errorId = getErrorId(input);
    var existingError = document.getElementById(errorId);

    input.classList.add('is-invalid');
    input.setAttribute('aria-invalid', 'true');

    if (input.type === 'checkbox' && wrapper) {
      wrapper.style.border = '1px solid #dc3545';
      wrapper.style.borderRadius = '6px';
      wrapper.style.padding = '8px';
    }

    if (!existingError && wrapper) {
      var error = document.createElement('div');
      error.id = errorId;
      error.className = 'invalid-feedback d-block';
      error.textContent = message;
      wrapper.appendChild(error);
    }
    else if (existingError) {
      existingError.textContent = message;
    }
  }

  function clearInvalidState(input) {
    if (!input) {
      return;
    }

    var wrapper = getWrapper(input);
    var existingError = document.getElementById(getErrorId(input));

    input.classList.remove('is-invalid');
    input.removeAttribute('aria-invalid');

    if (input.type === 'checkbox' && wrapper) {
      wrapper.style.border = '';
      wrapper.style.borderRadius = '';
      wrapper.style.padding = '';
    }

    if (existingError) {
      existingError.remove();
    }
  }

  function digitsOnly(value) {
    return (value || '').replace(/\D/g, '');
  }

  function isInputVisible(input) {
    if (!input) {
      return false;
    }

    if (input.disabled || input.type === 'hidden') {
      return false;
    }

    if (input.offsetParent === null && input.getClientRects().length === 0) {
      return false;
    }

    return true;
  }

  function validateField(input, message, testFn) {
    if (!isInputVisible(input)) {
      clearInvalidState(input);
      return true;
    }

    var isValid = testFn(input);
    if (!isValid) {
      setInvalidState(input, message);
      return false;
    }

    clearInvalidState(input);
    return true;
  }

  function bindRealtimeValidation(input, validator) {
    if (!input) {
      return;
    }

    var eventName = input.tagName === 'SELECT' || input.type === 'checkbox' || input.type === 'radio' ? 'change' : 'blur';
    input.addEventListener(eventName, validator);

    if (input.type !== 'checkbox' && input.type !== 'radio') {
      input.addEventListener('input', function () {
        if (input.classList.contains('is-invalid')) {
          validator();
        }
      });
    }
  }

  function focusFirstInvalid(form) {
    var firstInvalid = form.querySelector('.is-invalid, [aria-invalid="true"]');
    if (firstInvalid && typeof firstInvalid.focus === 'function') {
      firstInvalid.focus();
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function initEmpresaValidation(form) {
    var tipo = form.querySelector('select[name="field_tipo_entidade"]');
    var cpf = form.querySelector('input[name="field_cpf_empresa"]');
    var cnpj = form.querySelector('input[name="field_cnpj"]');
    var termo = form.querySelector('input[name="field_termo"]');
    var pass = form.querySelector('input[name="pass"]');
    var passConfirm = form.querySelector('input[name="pass_confirm"]');

    var rules = [
      {
        input: form.querySelector('input[name="name"]'),
        message: 'Informe o nome de usuário.',
        validate: function (input) {
          return input.value.trim().length > 0;
        }
      },
      {
        input: form.querySelector('input[name="mail"]'),
        message: 'Informe um e-mail válido.',
        validate: function (input) {
          return input.value.trim().length > 0 && input.checkValidity();
        }
      },
      {
        input: pass,
        message: 'Informe uma senha com no mínimo 8 caracteres.',
        validate: function (input) {
          return input.value.length >= 8;
        }
      },
      {
        input: passConfirm,
        message: 'Confirme a senha informada.',
        validate: function (input) {
          return input.value.length > 0 && pass && input.value === pass.value;
        }
      },
      {
        input: tipo,
        message: 'Selecione o tipo de entidade.',
        validate: function (input) {
          return input.value !== '';
        }
      },
      {
        input: form.querySelector('input[name="field_razao_social"]'),
        message: 'Informe a razão social.',
        validate: function (input) {
          return input.value.trim().length > 0;
        }
      },
      {
        input: form.querySelector('input[name="field_responsavel_nome"]'),
        message: 'Informe o nome do responsável pelo cadastro.',
        validate: function (input) {
          return input.value.trim().length > 0;
        }
      }
    ];

    function validateDocumento() {
      clearInvalidState(cpf);
      clearInvalidState(cnpj);

      if (!tipo || tipo.value === '') {
        return true;
      }

      if (tipo.value === 'fisica') {
        return validateField(cpf, 'Informe um CPF válido.', function (input) {
          return digitsOnly(input.value).length === 11;
        });
      }

      if (tipo.value === 'juridica') {
        return validateField(cnpj, 'Informe um CNPJ válido.', function (input) {
          return digitsOnly(input.value).length === 14;
        });
      }

      return true;
    }

    function validateTermo() {
      return validateField(termo, 'Você precisa aceitar a Política de Privacidade.', function (input) {
        return !!input.checked;
      });
    }

    rules.forEach(function (rule) {
      bindRealtimeValidation(rule.input, function () {
        validateField(rule.input, rule.message, rule.validate);
      });
    });

    if (tipo) {
      tipo.addEventListener('change', function () {
        clearInvalidState(cpf);
        clearInvalidState(cnpj);
        validateField(tipo, 'Selecione o tipo de entidade.', function (input) {
          return input.value !== '';
        });
        validateDocumento();
      });
    }

    [cpf, cnpj].forEach(function (input) {
      bindRealtimeValidation(input, validateDocumento);
    });

    if (termo) {
      bindRealtimeValidation(termo, validateTermo);
    }

    form.addEventListener('submit', function (event) {
      var isValid = true;

      rules.forEach(function (rule) {
        if (!validateField(rule.input, rule.message, rule.validate)) {
          isValid = false;
        }
      });

      if (!validateDocumento()) {
        isValid = false;
      }

      if (!validateTermo()) {
        isValid = false;
      }

      if (!isValid) {
        event.preventDefault();
        event.stopPropagation();
        focusFirstInvalid(form);
      }
    });
  }

  function initCandidatoValidation(form) {
    var emailInput = form.querySelector('input[name="mail"]');
    var passInput = form.querySelector('input[name="pass"]');
    var passConfirmInput = form.querySelector('input[name="pass_confirm"]');
    var cpfInput = form.querySelector('input[name="field_cpf"]');
    var termoInput = form.querySelector('input[name="field_termo"]');

    var baseRules = [
      {
        input: form.querySelector('input[name="name"]'),
        message: 'Informe o nome de usuário.',
        validate: function (input) {
          return input.value.trim().length >= 3;
        }
      },
      {
        input: emailInput,
        message: 'Informe um e-mail válido.',
        validate: function (input) {
          return input.value.trim().length > 0 && input.checkValidity();
        }
      },
      {
        input: passInput,
        message: 'A senha deve ter no mínimo 8 caracteres.',
        validate: function (input) {
          return input.value.length >= 8;
        }
      },
      {
        input: passConfirmInput,
        message: 'A confirmação de senha não confere.',
        validate: function (input) {
          return input.value.length > 0 && passInput && input.value === passInput.value;
        }
      },
      {
        input: cpfInput,
        message: 'O CPF deve conter 11 dígitos.',
        validate: function (input) {
          return digitsOnly(input.value).length === 11;
        }
      },
      {
        input: termoInput,
        message: 'Você precisa aceitar o termo para concluir o cadastro.',
        validate: function (input) {
          return !!input.checked;
        }
      }
    ];

    var requiredInputs = Array.prototype.slice.call(form.querySelectorAll('[required]')).filter(function (input) {
      return baseRules.every(function (rule) {
        return rule.input !== input;
      });
    });

    function requiredMessage(input) {
      var label = input.id ? form.querySelector('label[for="' + input.id + '"]') : null;
      var labelText = label ? label.textContent.replace('*', '').trim() : 'campo';
      return 'Preencha o campo ' + labelText + '.';
    }

    function validateRequiredField(input) {
      return validateField(input, requiredMessage(input), function (currentInput) {
        if (currentInput.type === 'checkbox' || currentInput.type === 'radio') {
          return currentInput.checked;
        }
        return currentInput.value.trim().length > 0;
      });
    }

    baseRules.forEach(function (rule) {
      bindRealtimeValidation(rule.input, function () {
        validateField(rule.input, rule.message, rule.validate);
      });
    });

    requiredInputs.forEach(function (input) {
      bindRealtimeValidation(input, function () {
        validateRequiredField(input);
      });
    });

    form.addEventListener('submit', function (event) {
      var isValid = true;

      baseRules.forEach(function (rule) {
        if (!validateField(rule.input, rule.message, rule.validate)) {
          isValid = false;
        }
      });

      requiredInputs.forEach(function (input) {
        if (!validateRequiredField(input)) {
          isValid = false;
        }
      });

      if (!isValid) {
        event.preventDefault();
        event.stopPropagation();
        focusFirstInvalid(form);
      }
    });
  }

  Drupal.behaviors.customConfigsUsersRegistrationValidation = {
    attach: function (context) {
      once('custom-configs-users-empresa-validation', 'form[data-drupal-selector="custom-configs-users-empresa-registration-form"]', context).forEach(function (form) {
        initEmpresaValidation(form);
      });

      once('custom-configs-users-candidato-validation', 'form[data-drupal-selector="custom-configs-users-candidato-registration-form"]', context).forEach(function (form) {
        initCandidatoValidation(form);
      });
    }
  };
})(Drupal, once);
