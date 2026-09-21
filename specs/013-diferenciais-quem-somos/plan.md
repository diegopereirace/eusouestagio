# Implementation Plan: Diferenciais Quem Somos

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-20  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/013-diferenciais-quem-somos/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar o bloco gerenciável **Diferenciais Quem Somos** (`diferenciais_quem_somos` + paragraph `diferencial_simples_p`) na região `content_full` de `/quem-somos`: cabeçalho centralizado (título + descrição) + grid Bootstrap de ícone/rótulo (2 / 3 / 4 colunas), tipografia Poppins SemiBold nos rótulos, CSS sob `.block-diferenciais-quem-somos`, seed de 8 itens + placement via `custom_configs_update_11016` idempotente, `drush cex` → `config/sync`, PRD §3.6 cirúrgico. Machine names **distintos** do bloco da home (`nossos_diferenciais` / `diferencial_item_p`).

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Block Content, Image, Field, Text); Paragraphs + Entity Reference Revisions (já no projeto); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed/placement via `hook_update_N`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md / lg+ — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada; ícones ≤64px; sem JS novo  
**Constraints**: sem `core/`/`vendor/`; reutilizar storages (`field_text_simple`, `field_text_simple_long`, `field_image`, `field_itens_lista`); não tocar home `nossos_diferenciais`; deploy `cim` → `updb` → `cr`; pt-BR; banner 009 / Sobre nós 010 / Missão-Visão Node 012 intactos  
**Scale/Scope**: 1 block type, 1 paragraph type, ~10 YAMLs de config (+ ajuste cardinality do storage lista), 2 Twig, 1 CSS/library, 1 hook `11016`, regra deploy já existente, PRD cirúrgico

**Estado atual verificado (2026-09-20):**

- Último hook: `custom_configs_update_11015` → próximo livre **`11016`**.
- Storages reutilizáveis **existem**: `block_content.field_text_simple`, `field_text_simple_long`; `paragraph.field_image`, `field_text_simple`; `block_content.field_itens_lista` (ERR → paragraph).
- **Atenção**: `field.storage.block_content.field_itens_lista` tem `cardinality: 2` (legado 011). Spec exige lista **ilimitada** → elevar storage para `-1` e exportar (ver research R3). Bundle `missao_visao` (placement já `status: false`) continua válido com cardinality maior.
- Bundles `diferenciais_quem_somos` / `diferencial_simples_p` **ainda não existem**.
- Bloco home `nossos_diferenciais` + `diferencial_item_p` + Twig/CSS `.block-nossos-diferenciais` **permanecem** (não reutilizar templates/CSS).
- Em `/quem-somos`, `content_full` só tem `default_missaovisao` (`status: false`, weight `0`). Novo placement ocupa a região pós-node.
- Regra Cursor `.cursor/rules/drupal-deploy-configs.mdc` **já existe** (FR-021 cumprido na specify).
- Poppins já no tema (`style.css`); reforço SemiBold no escopo do novo bloco.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | zero storage novo; só instances + ajuste cardinality lista |
| Distinção da home 004 | PASS | machine names / Twig / CSS novos |
| Deploy `cim` → `updb` → `cr` + hook idempotente | PASS | `11016` + `drush cex` estrutural |
| Clean URLs | PASS | rota `/quem-somos` inalterada; visibility por path |
| Performance / CSS isolado | PASS | library + seletores sob `.block-diferenciais-quem-somos` |
| Contrib first / custom mínimo | PASS | sem módulo novo; Paragraphs já no projeto |
| Sem dump / Entity API | PASS | seed via `BlockContent::create()` / entityTypeManager |

**Post-design**: gates mantidos; elevação de cardinality de `field_itens_lista` justificada em research R3 (única opção para reuso + lista ilimitada). Sem violação injustificada.

## Design Decisions

1. **Paragraph** `diferencial_simples_p` (“Item Diferencial Simples”): `field_image` + `field_text_simple` (sem descrição — distinto de `diferencial_item_p`).
2. **Block type** `diferenciais_quem_somos`: `field_text_simple` (título), `field_text_simple_long` (descrição), `field_itens_lista` → `diferencial_simples_p` (ilimitado).
3. **Cardinality** do storage `field_itens_lista`: `2` → `-1`; exportar YAML; instance nova no bundle 013 com handler só `diferencial_simples_p`.
4. **Twig bloco**: `block--block-diferenciais-quem-somos.html.twig`; classe raiz `block-diferenciais-quem-somos`; markup espelha FR-009–016 (`.container.py-5`, H2, `.col-lg-8.mx-auto`, `.row.mt-5.justify-content-center`, itens `.col-6.col-md-4.col-lg-3.mb-4`).
5. **Twig paragraph**: `paragraph--diferencial-simples-p.html.twig`; ícone `img-fluid` + rótulo `.mt-3`; omitir vazios.
6. **Library** `default/diferenciais_quem_somos` → `assets/css/diferenciais-quem-somos.css`; attach no Twig do bloco; `max-width: 64px` nos ícones; Poppins SemiBold nos rótulos.
7. **UUID fixo** do `block_content`: `d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80`.
8. **Placement** `block.block.default_diferenciaisquemsomos`: tema `default`, região `content_full`, weight `10`, `request_path` = `/quem-somos`, `label_display: '0'`, plugin UUID alinhado ao seed.
9. **Hook `11016`**: ensure types/fields/displays (defensivo); seed bloco + 8 paragraphs + ícones de `modules/custom/custom_configs/assets/diferenciais-quem-somos/` **somente se ausentes/vazios**; ensure placement; **nunca** sobrescrever editorial; **nunca** alterar `nossos_diferenciais`.
10. **Permissões**: após criar bundle, ajustar roles que já editam block content; exportar via `cex`.
11. **PRD** §3.6: documentar novo bloco + placement + `11016`; mencionar convivência com home `nossos_diferenciais`.
12. **Fora**: home 004, banner 009, Sobre nós 010, Missão/Visão Node 012, Layout Builder, storage paralelo `field_text_simple_small`.

## Project Structure

### Documentation (this feature)

```text
specs/013-diferenciais-quem-somos/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/diferenciais-quem-somos-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  paragraphs.paragraphs_type.diferencial_simples_p.yml          # criar
  block_content.type.diferenciais_quem_somos.yml                # criar
  field.storage.block_content.field_itens_lista.yml             # cardinality -1
  field.field.paragraph.diferencial_simples_p.field_image.yml
  field.field.paragraph.diferencial_simples_p.field_text_simple.yml
  field.field.block_content.diferenciais_quem_somos.field_text_simple.yml
  field.field.block_content.diferenciais_quem_somos.field_text_simple_long.yml
  field.field.block_content.diferenciais_quem_somos.field_itens_lista.yml
  core.entity_form_display.paragraph.diferencial_simples_p.default.yml
  core.entity_view_display.paragraph.diferencial_simples_p.default.yml
  core.entity_form_display.block_content.diferenciais_quem_somos.default.yml
  core.entity_view_display.block_content.diferenciais_quem_somos.default.yml
  block.block.default_diferenciaisquemsomos.yml
  user.role.*.yml                                               # permissões do bundle

themes/custom/default/
  templates/block/block--block-diferenciais-quem-somos.html.twig
  templates/paragraph/paragraph--diferencial-simples-p.html.twig
  assets/css/diferenciais-quem-somos.css
  default.libraries.yml                                         # + diferenciais_quem_somos

modules/custom/custom_configs/
  custom_configs.install                                        # custom_configs_update_11016 + helpers
  assets/diferenciais-quem-somos/                               # ícones seed (SVG/PNG)

PRD.md                                                          # §3.6 cirúrgico
# .cursor/rules/drupal-deploy-configs.mdc                       # já entregue (FR-021)
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Config: tipos, instances, displays, cardinality lista, placement → `drush cex`
3. Twig bloco + paragraph + library/CSS (grid, ícones 64px, Poppins SemiBold, omit empty)
4. Hook `11016` (ensure + seed 8 itens + assets + placement)
5. Roles + PRD §3.6 + validação quickstart (`cim` → `updb` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Alterar cardinality global de `field_itens_lista` (2 → -1) | Spec exige reuso do storage **e** lista ilimitada; cardinality vive só no storage | Storage paralelo `field_*_lista` — viola reuso; travar em 8 — contradiz FR-004 |
