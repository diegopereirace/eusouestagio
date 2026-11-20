# Contrato: formulários protegidos

A verificação entra só nestes form ids, via ponto CAPTCHA ativo com tipo `default`.

| Form id | Rota pública | Quem vê |
|---------|----------------|---------|
| `user_login_form` | `/user/login` | Visitante |
| `user_pass` | `/user/password` | Visitante |
| `user_register_form` | `/user/register` | Visitante |
| `custom_configs_users_candidato_registration_form` | `/cadastro/candidato` | Visitante |
| `custom_configs_users_empresa_registration_form` | `/cadastro/empresa` | Visitante |
| `webform_submission_contato_form` | página de contato (id base) | Visitante e autenticado sem `skip CAPTCHA` |

Fora do contrato: `search_form`, `search_block_form`, filtros expostos de Views, forms `node_*_form`, painel autenticado.

Comportamento do elemento:

- Caminho feliz: campo oculto com token v3 e selo `.grecaptcha-badge`. Sem `.g-recaptcha` até a reserva.
- Reserva: elemento `.g-recaptcha` (checkbox) dentro de `.captcha`, antes do botão de envio.
- Falha: mensagem da config v3, em português.
