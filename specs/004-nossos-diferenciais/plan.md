# Implementation Plan: Bloco Nossos Diferenciais

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente; setup manual)  
**Date**: 2026-09-11  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/004-nossos-diferenciais/spec.md` + pedido explícito: **facilitar deploy** — toda alteração estrutural de banco sobe para outros ambientes via Configuration Management (sem dump).

## Summary

Entregar o bloco gerenciável **Nossos Diferenciais** (`nossos_diferenciais` + paragraph `diferencial_item_p`) com layout Figma (duas colunas, geometria CSS, Poppins), **reutilizando storages** `field_text_simple` / `field_text_simple_long` / `field_image` e criando apenas `field_diferenciais_lista`.

**Estratégia de deploy (obrigatória):** estrutura 100% em `config/sync` versionado; ambientes destino recebem só `git pull` → `drush cim -y` → (`drush updb -y` se houver seed) → `drush cr`. **Proibido** dump/sql/manual de fields no painel de staging/prod.

## Technical Context

**Language/Version**: PHP 8.3+ (Drupal 11), Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Block Content, Image, Field), Paragraphs + Entity Reference Revisions (já no projeto), tema `default` (Bootstrap 5 / Barrio). **Zero dependência Composer nova.**  
**Storage**: PostgreSQL; schema de entidades via Configuration API (`config/sync`). Conteúdo editorial (textos/imagens do bloco) ≠ config — ver research R2.  
**Testing**: Sem PHPUnit nesta feature — validação manual + [quickstart.md](quickstart.md)  
**Target Platform**: Docker local + VPS (mesmo fluxo `cim`/`updb`/`cr`)  
**Project Type**: CMS Drupal SSR (webroot = raiz do repo)  
**Performance Goals**: CSS/Twig leves; imagem de destaque com `loading="lazy"` (não LCP hero); geometria só com CSS (sem assets extras)  
**Constraints**: Sem `core/`/`vendor/`; reutilizar field storages; Poppins já no tema (completar weight 300 se necessário); deploy sem dump  
**Scale/Scope**: 1 block type, 1 paragraph type, ~12 YAMLs de config, 2 Twig, 1 SCSS/CSS parcial, 1 `hook_update_N` opcional de seed idempotente

**Estado verificado (2026-09-11):**

- `config/sync` ativo; `settings.php.prod` já define `$settings['config_sync_directory'] = 'config/sync'`.
- Storages reutilizáveis existem: `field.storage.block_content.field_{text_simple,text_simple_long,image}` e equivalentes em `paragraph`.
- Paragraph semelhante `icone_titulo_descricao` **não** substitui `diferencial_item_p` (machine name obrigatório na spec).
- Poppins já importado em `themes/custom/default/assets/css/style.css` (weights 400–700); design pede 300 no subtítulo → ajustar import.
- Placements de `block_content` no sync apontam UUID da entidade de conteúdo (ex.: `block.block.default_cto.yml`) — conteúdo em si não viaja no `cim`.

## Constitution Check

*GATE: Must pass before Phase 0. Re-check after Phase 1.*

Sem `.specify/memory/constitution.md`; gates = regras do projeto + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD antes de codar | ✅ PASS | spec → plan → tasks |
| Nunca alterar `core/`/`vendor/` | ✅ PASS | só `config/sync/`, `themes/custom/default/`, `modules/custom/custom_configs/` |
| Config versionada / deploy sem dump | ✅ PASS | FR-006; fluxo R1–R3 |
| Performance | ✅ PASS | CSS decorativo; lazy na imagem de seção |
| Segurança | ✅ PASS | conteúdo público; sem endpoints novos |
| Clean URLs | ✅ PASS | sem rotas novas |
| Contrib first / custom mínimo | ✅ PASS | sem módulo novo; seed no `custom_configs` existente |
| PostgreSQL via Entity API | ✅ PASS | seed via Entity API, zero SQL cru |

**Resultado**: sem violações — Complexity Tracking vazio.

## Project Structure

### Documentation (this feature)

```text
specs/004-nossos-diferenciais/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md             # Fase 0
├── data-model.md           # Fase 1
├── contracts/
│   └── block-render.md     # contrato de apresentação
└── quickstart.md           # validação + receita de deploy
```

### Source Code (mudanças planejadas)

```text
config/sync/
├── block_content.type.nossos_diferenciais.yml
├── paragraphs.paragraphs_type.diferencial_item_p.yml
├── field.storage.block_content.field_diferenciais_lista.yml   # ÚNICO storage novo
├── field.field.block_content.nossos_diferenciais.field_text_simple.yml
├── field.field.block_content.nossos_diferenciais.field_text_simple_long.yml
├── field.field.block_content.nossos_diferenciais.field_image.yml
├── field.field.block_content.nossos_diferenciais.field_diferenciais_lista.yml
├── field.field.paragraph.diferencial_item_p.field_image.yml
├── field.field.paragraph.diferencial_item_p.field_text_simple.yml
├── field.field.paragraph.diferencial_item_p.field_text_simple_long.yml
├── core.entity_form_display.block_content.nossos_diferenciais.default.yml
├── core.entity_view_display.block_content.nossos_diferenciais.default.yml
├── core.entity_form_display.paragraph.diferencial_item_p.default.yml
├── core.entity_view_display.paragraph.diferencial_item_p.default.yml
├── (opcional) block.block.default_nossosdiferenciais.yml     # só se seed fixar UUID
└── user.role.*.yml  # permissões create/edit do novo bundle (export após ajuste)

themes/custom/default/
├── templates/block/block--nossos-diferenciais.html.twig
├── templates/paragraph/paragraph--diferencial-item-p.html.twig
├── assets/css/style.css          # Poppins 300 + escopo .block-nossos-diferenciais
└── (opcional) default.theme      # theme suggestion estável se o ID do bloco variar

modules/custom/custom_configs/
└── custom_configs.install        # hook_update_N idempotente: seed do bloco + 3 itens (R2)
```

**Structure Decision**: feature 100% no tema + config sync + update hook no módulo já usado para seeds (`custom_configs`). Sem módulo novo.

## Deploy (facilitado) — contrato operacional

### O que sobe automaticamente (estrutura)

| Artefato | Veículo | Comando destino |
|----------|---------|-----------------|
| Block type, paragraph type, fields, displays, roles | `config/sync/*.yml` no Git | `drush cim -y` |
| Twig / CSS | arquivos do tema no Git | deploy de código + `drush cr` |
| Seed editorial inicial (textos/itens de referência) | `custom_configs_update_N` idempotente | `drush updb -y` |

### O que **não** sobe no `cim`

- Arquivos de imagem (destaque/ícones) em `sites/default/files/` — seed usa placeholders Twig **ou** arquivos versionados em `modules/custom/custom_configs/assets/` copiados no update (preferir placeholder na v1; upload editorial depois).
- Edições posteriores de copy feitas só no painel de um ambiente — sincronizar conteúdo entre ambientes **não** é objetivo desta feature (só estrutura + seed inicial).

### Receita única (local → staging → prod)

```bash
# Origem (após criar/ajustar no UI ou YAML):
drush cex -y
git add config/sync themes/custom/default modules/custom/custom_configs
git commit -m "feat: adiciona bloco Nossos Diferenciais gerenciável"

# Destino (qualquer ambiente):
git pull
drush cim -y      # cria tipos/campos/displays — zero clique no Estrutura
drush updb -y     # seed idempotente do bloco de referência (se presente)
drush cr
```

**Ordem**: para esta feature (só tipos novos, sem migração destrutiva), `cim` **antes** de `updb` — o seed precisa dos fields já importados. Se o update detectar fields ausentes, retorna mensagem e não quebra o deploy.

**Proibido em staging/prod:** criar fields/tipos manualmente no UI; importar dump; SQL de schema.

## Phases

### Fase 0 — Research (concluída)

Decisões em [research.md](research.md): CM-only structure, seed vs conteúdo, template naming, cores nth-child, Poppins 300, permissões.

### Fase 1 — Design & Contracts (concluída)

- [data-model.md](data-model.md)
- [contracts/block-render.md](contracts/block-render.md)
- [quickstart.md](quickstart.md) — inclui checklist de deploy multi-ambiente

### Fase 2 — Tasks

Fora deste comando — `/speckit-tasks`. Ordem macro:

1. YAMLs de tipos/fields/displays em `config/sync` (reuso de storages).
2. Twig bloco + paragraph + CSS geometria/cores/Poppins.
3. `hook_update_N` seed idempotente + (opcional) placement.
4. Export permissões; `cex` limpo; validar quickstart; perguntar PRD (§3.6 blocos).

## Constitution Check (pós-design)

Revalidado após Phase 1: gates permanecem ✅; deploy CM-first reforça gate “sem dump”.
