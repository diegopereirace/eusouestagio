# Implementation Plan: Benefícios Para Estudantes

**Branch**: `feature-para-estudantes` (scaffolding Spec Kit — `.specify/scripts` ausente neste repo; setup a partir de `.specify/feature.json` → `specs/022-beneficios-estudantes`)  
**Date**: 2026-09-29  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/022-beneficios-estudantes/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar o segundo bloco de `/para-estudantes`: seção **Benefícios Estudantes** (`beneficios_estudantes` + paragraph `card_icon_text_p`) na região `content_full`, só nessa rota, abaixo do hero da feature `021`. Cabeçalho centralizado (título max ~404px + subtítulo max ~624px) + grid Bootstrap 1/2/4 (`col-12` / `col-md-6` / `col-lg-3`) de cards (~258×310, ícone ≤64px, shadow/borda sutil), tipografia Poppins `#0F172A`, CSS sob `.block-beneficios-estudantes`, seed de 4 cards + assets, hook idempotente `custom_configs_update_11035`, `drush cex` → `config/sync`, PRD §3.6 cirúrgico. **Não** reutilizar a instância PE `diferenciais_quem_somos` nem o paragraph legado `icone_titulo_descricao`. Zero storages novos; sem `core/`/`vendor/`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3 do tema  
**Primary Dependencies**: Drupal core (Block Content, Image, Field, Text); Paragraphs + Entity Reference Revisions (já no projeto); tema `default` (Bootstrap Barrio 5). **Nenhuma dependência Composer/npm nova**.  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md / lg+ — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: library `beneficios_estudantes` só na página do bloco; omit empty; ícones lazy; sem JS novo  
**Constraints**: sem `core/`/`vendor/`; zero field storages novos; reutilizar `field_text_simple`, `field_text_simple_long`, `field_image`, `field_itens_lista`; deploy `cim` → `updb` → (2ª `cim`) → `cr`; pt-BR; isolamento CSS; não alterar hero `021`, View `vagas`, PE/QS/home  
**Scale/Scope**: 1 block type, 1 paragraph type, ~12 YAMLs de config, 2 Twig, 1 CSS/library, 1 hook `11035`, 4 assets seed, PRD §3.6

**Estado atual verificado (2026-09-29):**

- Último hook: `custom_configs_update_11034` → próximo livre **`11035`**.
- Storages reutilizáveis **existem**: `block_content.field_text_simple`, `field_text_simple_long`, `field_itens_lista` (**cardinality já `-1`** — sem ajuste nesta feature); `paragraph.field_image`, `field_text_simple`, `field_text_simple_long`.
- Bundles `beneficios_estudantes` / `card_icon_text_p` **ainda não existem**.
- Paragraph legado `icone_titulo_descricao` e block type `diferenciais_quem_somos` (instâncias QS UUID `d5e6…` e PE UUID `f6a7…`) **permanecem** — não reutilizar.
- Em `/para-estudantes`: hero na região `banner` (`block_para_estudantes`); filtros expostos em `highlighted`; **nenhum** bloco exclusivo em `content_full` nessa rota ainda — o novo placement entra como primeiro conteúdo full útil (weight baixo, ex. `0` ou `1`).
- Padrão visual mais próximo: `diferenciais_quem_somos` (013) — cabeçalho + grid; diferença: cards com descrição + chrome de card + colunas `col-12/md-6/lg-3`.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | zero storage novo; só instances + bundles |
| Distinção PE / legado | PASS | tipo/instância próprios; não toca `diferenciais_quem_somos` nem `icone_titulo_descricao` |
| Deploy `cim` → `updb` → `cr` (+ 2ª `cim`) + hook idempotente | PASS | `11035` + `drush cex` estrutural |
| Clean URLs | PASS | visibility `/para-estudantes` |
| Performance / CSS isolado | PASS | library `beneficios_estudantes`; wrapper `.block-beneficios-estudantes` |
| Contrib first / custom mínimo | PASS | sem módulo novo; Paragraphs já no projeto |
| Sem dump / Entity API | PASS | seed via `BlockContent::create()` / entityTypeManager |
| Convivência hero 021 / vagas / outras rotas | PASS | placement só `/para-estudantes`; CSS escopado |

**Post-design**: gates mantidos; paragraph novo `card_icon_text_p` (em vez de reusar `icone_titulo_descricao` / `diferencial_item_p`) justificado em research R1. Sem violação injustificada.

## Design Decisions

1. **Paragraph** `card_icon_text_p` (“Card Ícone e Texto”): `field_image` + `field_text_simple` + `field_text_simple_long`.
2. **Block type** `beneficios_estudantes`: `field_text_simple` (título), `field_text_simple_long` (subtítulo), `field_itens_lista` → `card_icon_text_p` (ilimitado; seed 4).
3. **Mapeamentos canônicos**: pedido `field_text_simple_small` → `field_text_simple`; pedido `field_cards_lista` → instance de `field_itens_lista` (storage já `-1`).
4. **Twig bloco**: `block--block-beneficios-estudantes.html.twig`; classe raiz `block-beneficios-estudantes`; markup FR-009–016 (`.container.py-5` / `py-lg-5`, H2 + subtítulo centrados, `.row.mt-5.justify-content-center`).
5. **Twig paragraph**: `paragraph--card-icon-text-p.html.twig`; colunas `.col-12.col-md-6.col-lg-3`; card `.text-center.d-flex.flex-column.align-items-center.h-100` + `min-height: 310px`; ícone `.img-fluid` ≤64px; omit empty.
6. **Library** `default/beneficios_estudantes` → `assets/css/beneficios-estudantes.css`; título `max-width: 404px`; subtítulo `max-width: 624px`; cor `#0F172A`; Poppins.
7. **UUID fixo** do `block_content`: `a8b9c0d1-e2f3-4456-a789-0bcdef123456`.
8. **Placement** `block.block.default_beneficiosestudantes`: tema `default`, região `content_full`, weight `0` (primeiro útil nessa rota), `request_path` = `/para-estudantes`, `label_display: '0'`, plugin UUID alinhado ao seed.
9. **Hook `11035`**: ensure types/fields/displays (defensivo); seed bloco + 4 paragraphs + ícones de `modules/custom/custom_configs/assets/beneficios-estudantes/` **somente se ausentes/vazios**; ensure placement; **nunca** sobrescrever editorial; **nunca** alterar PE/QS/home/`021`.
10. **Permissões**: após criar bundle, ajustar roles que já editam block content; exportar via `cex`.
11. **PRD** §3.6 (e §3.1.1 / rota estudantes se aplicável): documentar block type + placement + `11035` + convivência com hero `021` e View `vagas`.
12. **Fora**: hero 021, View `vagas`, reuso de instância PE, Layout Builder, storages paralelos.

## Project Structure

### Documentation (this feature)

```text
specs/022-beneficios-estudantes/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── beneficios-estudantes-render.md
│   └── deploy-beneficios-estudantes.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  paragraphs.paragraphs_type.card_icon_text_p.yml
  block_content.type.beneficios_estudantes.yml
  field.field.paragraph.card_icon_text_p.field_image.yml
  field.field.paragraph.card_icon_text_p.field_text_simple.yml
  field.field.paragraph.card_icon_text_p.field_text_simple_long.yml
  field.field.block_content.beneficios_estudantes.field_text_simple.yml
  field.field.block_content.beneficios_estudantes.field_text_simple_long.yml
  field.field.block_content.beneficios_estudantes.field_itens_lista.yml
  core.entity_form_display.paragraph.card_icon_text_p.default.yml
  core.entity_view_display.paragraph.card_icon_text_p.default.yml
  core.entity_form_display.block_content.beneficios_estudantes.default.yml
  core.entity_view_display.block_content.beneficios_estudantes.default.yml
  block.block.default_beneficiosestudantes.yml
  user.role.*.yml                                               # permissões do bundle

themes/custom/default/
  templates/block/block--block-beneficios-estudantes.html.twig
  templates/paragraph/paragraph--card-icon-text-p.html.twig
  assets/css/beneficios-estudantes.css
  default.libraries.yml                                         # + beneficios_estudantes

modules/custom/custom_configs/
  custom_configs.install                                        # custom_configs_update_11035 + helpers
  assets/beneficios-estudantes/                                 # icon-1…4.png (ou svg)

PRD.md                                                          # §3.6 cirúrgico (+ rota /para-estudantes se necessário)
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Config: tipos, instances, displays, placement → `drush cex`
3. Twig bloco + paragraph + library/CSS (grid, cards 310px, ícones 64px, max-widths 404/624)
4. Hook `11035` + assets seed
5. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Paragraph novo `card_icon_text_p` apesar de `icone_titulo_descricao` / `diferencial_item_p` similares | Briefing exige machine name `card_icon_text_p`; evita acoplar legado/user fields e templates da home | Reusar legado — frágil e fora do machine name pedido |
| Tipo/instância próprios vs. 2º placement de `diferenciais_quem_somos` (PE) | PE usa `diferencial_simples_p` (só ícone+rótulo); layout e copy distintos; SC-008 | Reusar instância PE — misturaria editoriais e CSS |
| Weight `0` em `content_full` | Hoje não há outro bloco exclusivo dessa rota na região; hero está em `banner` | Weight alto “por precaução” — desnecessário |
