# Data Model: Página de Contato

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-23

## Entidades

### 1. `contato` (webform) — atualizar in-place

| Elemento | Tipo | Obrigatório | Placeholder / notas |
|----------|------|-------------|---------------------|
| `nome_completo` | textfield | sim | “Seu nome”; flexbox linha 1 |
| `email` | email | sim | “seu@email.com”; flexbox linha 1 |
| `telefone` | tel | sim | “(00) 00000-0000”; classe `mask-phone`; flexbox linha 2 |
| `categoria` | select | sim | empty “Estudante”; opções Estudante, Empresa, Outro; flexbox linha 2 |
| `assunto` | textfield | sim | “Como podemos ajudar?”; full width |
| `mensagem` | textarea | sim | “Escreva sua mensagem aqui...”; full width |
| `actions` | webform_actions | — | `#submit__label`: **Enviar Mensagem** |

| Aspecto | Valor |
|---------|--------|
| id | `contato` |
| UUID | `38532b7b-3193-4b6a-b666-e1e68693dd94` (inalterado) |
| title | Contato |
| confirmation | title `Mensagem enviada!`; message sucesso atual |
| access create | anonymous + authenticated |
| page dedicada | **não** em `/contato` (ver research R5) |

Elementos legados (`tipo`, `nome`, `e_mail`, `whatsapp`) **removidos** da definição ativa; submissions históricas permanecem no storage administrativo sem migração.

### 2. `layout_contato` (block_content) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Formulário | `field_formulario_contato` | webform | 1 | não* | storage **novo** |
| Imagem destaque | `field_image` | image | storage `-1` / UX 1 | não* | storage **reutilizado** |

- Label admin: “Layout de Contato”.
- Machine name: `layout_contato`.
- \*Publicamente, campos vazios são omitidos no Twig (sem fatal).

### 3. Storage novo `field_formulario_contato`

| Aspecto | Valor |
|---------|--------|
| Config | `field.storage.block_content.field_formulario_contato` |
| Entity type | `block_content` |
| Type | `webform` |
| Settings | `target_type: webform` |
| Cardinality | `1` |
| Motivo | referência gerenciável ao webform; `node.webform` não reutilizável |

### 4. Página shell `/contato` (node `page`)

| Aspecto | Valor |
|---------|--------|
| Bundle | `page` |
| UUID | `f3a4b5c6-d7e8-4f90-a123-c4d5e6f7a8b9` |
| Alias | `/contato` |
| Body | vazio/mínimo |
| Título Drupal | oculto na rota (bloco `default_page_title`) |

### 5. Placement (config Block)

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_layoutcontato` |
| UUID placement | `e2f3a4b5-c6d7-4e8f-9012-b3c4d5e6f7a8` |
| Theme | `default` |
| Region | `content_full` |
| Weight | `0` (único conteúdo principal da rota; ajustar só se colidir) |
| Plugin | `block_content:{UUID do conteúdo}` |
| Visibility | `request_path` = `/contato` (negate false) |
| Label display | `0` (oculto) |

### 6. Instância seed (`block_content`)

| Aspecto | Valor |
|---------|--------|
| UUID | `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6` |
| Bundle | `layout_contato` |
| Webform | `contato` |
| Imagem | `img-contato.png` via asset `custom_configs/assets/contato/` → `public://` |

## Relacionamentos

```text
node:page (/contato)
  └── (shell; body vazio)

block_content:layout_contato
  ├── field_formulario_contato → webform:contato
  └── field_image              → file (img-contato.png)

block.block.default_layoutcontato
  → plugin block_content:d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6
  → content_full / /contato
```

## Seed (conteúdo / atalhos Twig)

| Item | Valor |
|------|--------|
| Webform ref | `contato` |
| Imagem | `img-contato.png` (alt descritivo, ex. “Time de especialistas”) |
| E-mail (Twig) | `contato@eusouestagio.com` → `mailto:contato@eusouestagio.com` |
| WhatsApp (Twig) | `(61) 99999-9999` → `https://wa.me/5561999999999` |
| H2 | `Envie sua mensagem` (Twig, não field) |

**Idempotência**: UUID fixo → não duplicar; popular **somente** campos/instância/alias vazios; **não** sobrescrever editorial divergente do seed.

## Displays

| Entidade | Form | View |
|----------|------|------|
| `layout_contato` | webform ref + image | ambos visíveis para Twig |
| webform `contato` | n/a (UI Webform) | render via field formatter webform |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Imagem vazia | painel `#E5EEFF` sem `<img>` quebrada (ou omitir img) |
| Webform não referenciado | omitir área do formulário; página estável |
| E-mail inválido / obrigatório vazio | validação nativa Webform; envio bloqueado |
| Reexecução hook | no-op seguro; sem bloco/página duplicados |
| Página ≠ `/contato` | bloco não aparece |
| Submissions legadas | legíveis no admin; novos envios com novos keys |

## Config YAML a versionar (checklist)

- [x] `webform.webform.contato.yml`
- [x] `block_content.type.layout_contato.yml`
- [x] `field.storage.block_content.field_formulario_contato.yml`
- [x] `field.field.block_content.layout_contato.field_formulario_contato.yml`
- [x] `field.field.block_content.layout_contato.field_image.yml`
- [x] `core.entity_form_display.block_content.layout_contato.default.yml`
- [x] `core.entity_view_display.block_content.layout_contato.default.yml`
- [x] `block.block.default_layoutcontato.yml`
- [x] permissões em `user.role.*` (se o export as alterar)

> Página e path alias são **conteúdo** (Entity API / hook), não YAML de config — documentados no hook `11021`.
