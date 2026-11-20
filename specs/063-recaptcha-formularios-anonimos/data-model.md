# Data model: reCAPTCHA nos formulários anônimos

## Política (`captcha.settings`)

| Campo | Valor |
|-------|--------|
| `enable_globally` | `0` |
| `default_challenge` | `recaptcha_v3/formulario_publico` |

## Ação v3 (`recaptcha_v3.recaptcha_v3_action.formulario_publico`)

| Campo | Valor |
|-------|--------|
| `id` | `formulario_publico` |
| `threshold` | `0.5` |
| `challenge` | `default` |

## Reserva (`recaptcha_v3.settings`)

| Campo | Valor |
|-------|--------|
| `default_challenge` | `recaptcha/reCAPTCHA` |
| `error_message` | Não foi possível confirmar que você é humano. Resolva a verificação e tente novamente. |
| `hide_badge` | `false` |
| `verify_hostname` | `true` |
| `cacheable` | `false` |
| `secret_key` no sync | vazio (override de ambiente) |

## Widget v2 (`recaptcha.settings`)

| Campo | Valor |
|-------|--------|
| `widget.theme` | `light` |
| `widget.size` | vazio (normal) |
| `widget.type` | `image` |
| `secret_key` no sync | vazio (override de ambiente) |

## Pontos

Todos `status: true`, `captchaType: default`.

| Id do ponto | Formulário |
|-------------|------------|
| `user_login_form` | Login |
| `user_pass` | Recuperar senha |
| `user_register_form` | Registro padrão |
| `custom_configs_users_candidato_registration_form` | Cadastro estudante |
| `custom_configs_users_empresa_registration_form` | Cadastro empresa |
| `webform_submission_contato_form` | Contato (página e node) |

## Papel

`moderador` ganha `skip CAPTCHA`. `administrator` já ignora por `is_admin: true`.
