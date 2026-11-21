# Quickstart: reCAPTCHA nos formulários anônimos

## Segredos (antes do import)

No `settings.php` ativo de cada ambiente, sem commitar o valor:

```php
$config['recaptcha.settings']['secret_key'] = 'SEGREDO_V2';
$config['recaptcha_v3.settings']['secret_key'] = 'SEGREDO_V3';
```

Em produção, antes do `cim`: `drush cget recaptcha.settings secret_key` e `drush cget recaptcha_v3.settings secret_key`. Se o banco tiver valor, copiar para o `settings.php` antes do import.

Docker local permanece com `$settings['disable_captcha'] = TRUE` até existirem segredos de teste. O `captcha.module` só limpa `$form['captcha']` na raiz; `custom_configs_form_alter()` remove o desafio aninhado (cadastros) para o reCAPTCHA de produção não carregar em `localhost`.

## Deploy

`git pull` → `drush cim -y` → `drush updb -y` → `drush cr`

## Checagem visual (CAPTCHA ligado)

Visitante anônimo, desktop e mobile (~390px):

1. `/user/login` — selo visível, sem checkbox no primeiro carregamento.
2. `/user/password` — igual.
3. `/cadastro/candidato` e `/cadastro/empresa` — um único bloco de verificação, dentro do cartão, antes do botão.
4. Página de contato — selo visível; com o aviso de cookies aberto, o selo não fica por baixo.
5. Listagem de vagas — sem selo e sem widget.

Envio com segredo válido conclui. Envio sem token, ou com pontuação abaixo de 0,5, mostra o checkbox e a mensagem em português.
