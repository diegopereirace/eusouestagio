# Implementation Plan: reCAPTCHA nos formulários anônimos

**Branch**: `main` (workflow do projeto)  
**Date**: 2026-11-20  
**Spec**: [spec.md](spec.md)

## Summary

Usar o stack já instalado (`captcha` + `recaptcha` + `recaptcha_v3`). Desafio padrão `recaptcha_v3/formulario_publico` (limiar 0.5). Reserva `recaptcha/reCAPTCHA` (checkbox v2, tema claro, tamanho normal). Pontos CAPTCHA com tipo `default` nos seis formulários anônimos. Segredos só em `settings.php`. Sem `enable_globally`. Desinstalar `image_captcha`. Remover o elemento CAPTCHA hardcoded dos cadastros.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / Drupal 11 |
| Primary Dependencies | `drupal/captcha` 2.0.12, `drupal/recaptcha` 8.x-3.5, `drupal/recaptcha_v3` 2.0.5 |
| Storage | Config exportável em `config/sync`; segredos em `settings.php` |
| Testing | Browser anônimo nos cinco fluxos + ausência na busca de vagas ([quickstart.md](quickstart.md)) |
| Constraints | Sem `core/`/`vendor/`; sem `enable_globally`; sem segredo no Git; sem `hook_update_N` (config pura) |

## Constitution Check

| Gate | Status |
|------|--------|
| SDD — spec antes do código | PASS |
| Contrib first | PASS — nenhum módulo novo |
| Sem `core/` / `vendor/` | PASS |
| Config via `drush cex` | PASS |
| Seeds / 057 | N/A |
| PRD guardião | PASS — §1.4, §4.2, §5.2, §6.4 |
| Deploy | `git pull` → `cim` → `updb` → `cr`, com segredos já no `settings.php` antes do `cim` |

## Project Structure

```text
config/sync/captcha.settings.yml
config/sync/captcha.captcha_point.*.yml
config/sync/recaptcha_v3.settings.yml
config/sync/recaptcha_v3.recaptcha_v3_action.formulario_publico.yml
config/sync/user.role.moderador.yml
config/sync/core.extension.yml
modules/custom/custom_configs/modules/custom_configs_users/src/Form/*RegistrationForm.php
themes/custom/default/src/css/style.css
sites/default/settings.php          # local, gitignored
sites/default/settings.php.prod     # comentário sem segredo
```

## Implementation approach

- `captcha.settings:default_challenge` = `recaptcha_v3/formulario_publico`.
- Ação `formulario_publico`: threshold `0.5`, challenge `default`.
- `recaptcha_v3.settings:default_challenge` = `recaptcha/reCAPTCHA` (não `captcha/Math`).
- `error_message` em pt-BR. `hide_badge: false`. `verify_hostname: true`. `cacheable: false`.
- Pontos ativos, `captchaType: default`: `user_login_form`, `user_pass`, `user_register_form`, `custom_configs_users_candidato_registration_form`, `custom_configs_users_empresa_registration_form`, `webform_submission_contato_form` (id base; cobre o form embutido no node).
- Permissão `skip CAPTCHA` no papel `moderador`. `administrator` já é `is_admin`.
- Remover `#type => captcha` dos dois forms de cadastro para não duplicar o ponto.
- CSS: margem no `.captcha` e `.grecaptcha-badge` acima do banner LGPD quando `.is-visible`.
- Override documentado, sem valor real:

```php
$config['recaptcha.settings']['secret_key'] = '...';
$config['recaptcha_v3.settings']['secret_key'] = '...';
```

## Constitution Check (pós-desenho)

| Gate | Status |
|------|--------|
| Config nova inventada à mão | Aceito só como espelho do que o Drush gravaria; `drush cim` valida. Segredos não entram no YAML. |
| Cache de página | PASS — sem CAPTCHA em busca/filtros |
