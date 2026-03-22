(function ($) {
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
})(jQuery);