# Data Model: Contato como Node

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-24

## Entidades

### 1. `contato` (node type) — novo

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Formulário | `webform` | webform | 1 | não* | storage **reutilizado** (`webform_node`) |
| Imagem lateral | `field_imagem` | image | 1 | não* | storage **reutilizado** |
| E-mail | `field_email` | email | 1 | não* | storage **novo** em `node` |
| Telefone/WhatsApp | `field_phone_wpp` | string (255) | 1 | não* | storage **novo** em `node` |
| Título coluna direita | `field_text_simple` | string (255) | 1 | não* | storage **reutilizado** |
| Descrição coluna direita | `field_text_simple_long` | string_long | 1 | não* | storage **reutilizado** |

- Label admin: “Contato”.
- Machine name: `contato`.
- Título do Node (core): “Contato” (admin/listagens); H2 visual do layout é hardcoded no Twig.
- \*Publicamente, campos vazios são omitidos no Twig (sem fatal).

### 2. Storages novos em `node`

#### `field_email`

| Aspecto | Valor |
|---------|--------|
| Config | `field.storage.node.field_email` |
| Type | `email` |
| Cardinality | `1` |
| Motivo | equivalente só em `block_content` |

#### `field_phone_wpp`

| Aspecto | Valor |
|---------|--------|
| Config | `field.storage.node.field_phone_wpp` |
| Type | `string` |
| max_length | `255` |
| Cardinality | `1` |
| Motivo | equivalente só em `block_content` |

### 3. Displays

| Display | Modo | Campos visíveis / editáveis |
|---------|------|-----------------------------|
| Form | `default` | title, webform, field_imagem, field_email, field_phone_wpp, field_text_simple, field_text_simple_long |
| View | `default` e/ou `full` | mesmos campos (formatters: webform form; image; plain/email; string) |

Path alias gerenciado via Path (não field path no modelo além do seed).

### 4. Instância seed (node `contato`)

| Aspecto | Valor |
|---------|--------|
| UUID | `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1` |
| Bundle | `contato` |
| Title | Contato |
| Status | published |
| Alias | `/contato` |
| `webform` | `contato` (webform id; status open) |
| `field_imagem` | `img-contato.png` via asset `custom_configs/assets/contato/` → `public://` |
| `field_email` | `contato@eusouestagio.com` |
| `field_phone_wpp` | `(61) 99999-9999` |
| `field_text_simple` | `Time de especialistas` |
| `field_text_simple_long` | `Nossa equipe responderá sua solicitação em até 24 horas úteis.` |

### 5. `contato` (webform) — inalterado nesta feature

| Aspecto | Valor |
|---------|--------|
| id | `contato` |
| UUID | `38532b7b-3193-4b6a-b666-e1e68693dd94` |
| Papel | referenciado pelo Node; elementos/displays da 017 preservados |

### 6. Legado a limpar (não são entidades ativas pós-update)

| Item | Ação no `11023` |
|------|-----------------|
| `block_content` ID 16 | delete se existir |
| `block_content` UUID `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6` | delete se ainda existir |
| Placement `default_layoutcontato` | desabilitar e/ou remover |
| Node `page` UUID `f3a4b5c6-d7e8-4f90-a123-c4d5e6f7a8b9` | remover alias `/contato` se apontar para ele; **não** apagar o node |

## Relacionamentos

```text
node:contato (/contato)  UUID a4b5c6d7-…
  ├── webform              → webform:contato
  ├── field_imagem         → file (img-contato.png)
  ├── field_email          → "contato@eusouestagio.com"
  ├── field_phone_wpp      → "(61) 99999-9999" → wa.me/5561999999999
  ├── field_text_simple    → "Time de especialistas"
  └── field_text_simple_long → "…24 horas úteis."

(legado, pós-update sem render em /contato)
block_content:layout_contato / placement default_layoutcontato → removidos/desativados
node:page (shell 017) → sem alias /contato
```

## Validação / regras

| Regra | Comportamento |
|-------|----------------|
| Reexecução do hook | no-op seguro; não duplica Node; não sobrescreve editorial divergente |
| Imagem vazia | painel sem `<img>` quebrada |
| Webform vazio | omite área do form |
| E-mail / WhatsApp vazios | omite caixa correspondente |
| Textos direita vazios | omite título/descrição |
| Alias conflitante | reassocia ao seed Contato |

## Estado / permissões

- Criar/editar Node Contato: papéis editoriais existentes (ajustar `user.role.*` no cex se o bundle exigir permissão explícita — padrão do projeto para novos types).
- Visita anônima: view published node em `/contato`.
