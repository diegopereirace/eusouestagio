(function ($, Drupal, drupalSettings, once) {
    Drupal.behaviors.masks = {
        attach: function (context) {
            $(function () {
                var phone = $(".mask-phone", context);
                if (phone.length != 0) {
                    phone.each(function(index) {
                        phone.mask('(99)99999-9999', { 'translation': { 9: { pattern: /[0-9*]/ }, 0: { pattern: /[0]/ }, } });
                        
                        $(this).on("focusout", function(){
                            if ($(this).val() == '(') $(this).val('');
                            if ($(this).val() == '(0') $(this).val('');
                        });
                    });
                }
            });

            var cnpj = $(".mask-cnpj", context);
            if (cnpj.length != 0) {
                cnpj.each(function(index) {
                    cnpj.mask('99.999.999/9999-99', { 'translation': { 9: { pattern: /[0-9*]/ }, 0: { pattern: /[0]/ } } });
                });
            }

            var cpf = $(".mask-cpf", context);
            if (cpf.length != 0) {
                cpf.each(function(index) {
                    cpf.mask('999.999.999-99', { 'translation': { 9: { pattern: /[0-9*]/ }, 0: { pattern: /[0]/ } } });
                });
            } 

            var cep = $(".mask-cep", context);
            if (cep.length != 0) {
                cep.each(function(index) {
                    cep.mask('99999-999', { 'translation': { 9: { pattern: /[0-9*]/ }, 0: { pattern: /[0]/ } } });
                });
            }             

            once('cep-autofill', '.mask-cep', context).forEach(function (element) {
                $(element).on('blur', function () {
                    var value = String($(this).val() || '');
                    var cepDigits = value.replace(/\D/g, '');
                    if (cepDigits.length !== 8) {
                        return;
                    }

                    var apiSettings = (drupalSettings && drupalSettings.defaultMasks && drupalSettings.defaultMasks.cepApi) || {};
                    var lookupUrlBase = apiSettings.lookupUrl || '/api/cep';

                    fetch(lookupUrlBase + '/' + cepDigits, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        },
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('CEP não encontrado');
                            }
                            return response.json();
                        })
                        .then(function (data) {
                            if (!data || typeof data !== 'object') {
                                return;
                            }

                            var endereco = data.endereco || '';
                            var bairro = data.bairro || '';
                            var cidade = data.cidade || '';
                            var uf = (data.uf || '').toUpperCase();

                            var $endereco = $('input[name="field_endereco"], input[name^="field_endereco["]', context).first();
                            var $bairro = $('input[name="field_bairro"], input[name^="field_bairro["]', context).first();
                            var $cidade = $('input[name="field_cidade"], input[name^="field_cidade["]', context).first();
                            var $estado = $('select[name="field_estado"], select[name^="field_estado["]', context).first();

                            if ($endereco.length && endereco && !$endereco.val()) {
                                $endereco.val(endereco).trigger('change');
                            }
                            if ($bairro.length && bairro && !$bairro.val()) {
                                $bairro.val(bairro).trigger('change');
                            }
                            if ($cidade.length && cidade && !$cidade.val()) {
                                $cidade.val(cidade).trigger('change');
                            }

                            if ($estado.length && uf) {
                                var hasExactOption = $estado.find('option[value="' + uf + '"]').length > 0;
                                if (hasExactOption) {
                                    $estado.val(uf).trigger('change');
                                }
                                else {
                                    $estado.find('option').each(function () {
                                        if ($(this).text().trim().toUpperCase() === uf) {
                                            $estado.val($(this).val()).trigger('change');
                                            return false;
                                        }
                                    });
                                }
                            }
                        })
                        .catch(function () {
                            return;
                        });
                });
            });

            var money = $(".mask-money", context);
            if (money.length != 0) {
                money.mask('999999.99', {reverse: true});
            }

            var card_number = $(".card-number", context);
            if (card_number.length != 0) {
                card_number.each(function(index) {
                    card_number.mask('9999 9999 9999 9999', { 'translation': { 9: { pattern: /[0-9*]/ } } });
                });
            }

            var card_date = $(".card-date", context);
            if (card_date.length != 0) {
                card_date.each(function(index) {
                    card_date.mask('99/99', { 'translation': { 9: { pattern: /[0-9*]/ } } });
                });
            }

            var card_code = $(".card-code", context);
            if (card_code.length != 0) {
                card_code.each(function(index) {
                    card_code.mask('999', { 'translation': { 9: { pattern: /[0-9*]/ } } });
                });
            }

            var MaskBankAgency = function (val) {
                return val.replace(/\D/g, '').length === 5 ? '0000-0' : '000000';
            },
            agencyOptions = {
            onKeyPress: function(val, e, field, options) {
                field.mask(MaskBankAgency.apply({}, arguments), options);
                }
            };
        
            $('.mask-bank-agency').mask(MaskBankAgency, agencyOptions);
        }
    }
})(jQuery, Drupal, drupalSettings, once);