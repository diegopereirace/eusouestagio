# Data Model: Refino Figma Contato + token de seção

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-04

Nenhuma entidade nova. Esta feature **reusa** o modelo da 018 e só consome fields existentes para apresentação. O “token de título de seção” é contrato de CSS, não entidade Drupal.

## Entidades existentes (tocadas só na UI)

### 1. Node `contato`

| Campo | Uso na UI | Mudança de dados |
|-------|-----------|------------------|
| UUID seed | `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1` | nenhuma |
| Alias | `/contato` | nenhuma |
| `webform` → webform `contato` | coluna esquerda | nenhuma (só CSS) |
| `field_email` | card E-mail + `mailto:` | leitura; omit se vazio |
| `field_phone_wpp` | card WhatsApp + `wa.me` | leitura; DDI 55 se 10–11 dígitos |
| `field_imagem` | painel | leitura; omit `<img>` se vazio; **sem** replace de arquivo (057) |
| `field_text_simple` | título do painel | leitura; omit se vazio |
| `field_text_simple_long` | texto do painel | leitura; omit se vazio |
| `title` (node) | oculto via `default_page_title` | nenhuma |

### 2. Webform `contato`

Inalterado estruturalmente. Submit label **Enviar Mensagem**. Máscaras via library `default/masks` (já anexada no alter).

### 3. Injeção runtime (não é field)

`custom_configs_webform_submission_form_alter()` monta `#actions.contato_shortcuts` a partir do Node da rota. Não persiste config.

### 4. Token tipográfico (não persistido)

| Propriedade | Valor |
|-------------|--------|
| Família | Inter |
| Peso | 600 |
| Tamanho | 30px |
| Line-height | 38px |
| Letter-spacing | −0.3px |
| Cor default | `#000000` |
| Classe canônica | `.ese-section-title` |

Aplicado a headers de seção públicos (ver [contracts/section-title-token.md](contracts/section-title-token.md)); sem storage Drupal.

## Relacionamentos

```text
alias /contato → node:contato
  ├── webform field → webform.contato
  │     └── form-actions
  │           ├── submit
  │           └── shortcuts (alter ← field_email / field_phone_wpp)
  ├── field_imagem / field_text_simple / field_text_simple_long → painel
  └── H2 "Envie sua mensagem" → .ese-section-title (token CSS)

blocos públicos (nd/be/oe/…) → __header__title → mesmo token (aliases CSS)
```

## Validação

| Regra | Origem |
|-------|--------|
| Sem CT/field/storage novos | Spec Fora |
| Omit empty nos cards | FR-015 |
| Não reescrever bytes de `field_imagem` | 057 / FR assets |
| Webform envio intacto | FR-014 |
| Token exatamente Inter 600/30/38/−0.3/#000 | FR-016 |

## State transitions

Nenhuma. Sem status editorial novo; sem hook de migração de conteúdo esperado.
