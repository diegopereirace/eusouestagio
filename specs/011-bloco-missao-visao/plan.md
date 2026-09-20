# Implementation Plan: Bloco Missão e Visão

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-16  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/011-bloco-missao-visao/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar o bloco institucional **Missão e Visão** em `/quem-somos`: faixa full-width com imagem de fundo, overlay escuro e dois textos lado a lado (Missão | Visão). Modelagem Custom Block `missao_visao` + Paragraph `missao_visao_item_p`; reuso de `field_text_simple`, `field_text_simple_long` e `field_image`; novo storage `field_itens_lista` (ERR → paragraph, cardinality 2); seed + placement idempotentes via `custom_configs_update_11014`; Twig/CSS isolados sob `.block-missao-visao`; `drush cex` → `config/sync`; PRD §3.6 cirúrgico.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Block Content, Image, Text, Field); Paragraphs + Entity Reference Revisions (já instalados); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; conteúdo seed via `hook_update_N`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (desktop ≥768px / mobile &lt;768px — breakpoint Bootstrap `md`)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada; imagem de fundo única; sem JS novo; lazy não aplicável a `background-image` (faixa abaixo do fold — aceitável)  
**Constraints**: sem alteração em `core/`/`vendor/`; sem storage paralelo de título; deploy `cim` → `updb` → `cr`; pt-BR; banner 009 e layout Sobre nós 010 intactos  
**Scale/Scope**: 1 block type, 1 paragraph type, 1 storage ERR novo, instances/displays, 1 Twig bloco (+ Twig paragraph se necessário), 1 CSS/library, 1 hook `11014` + asset, placement, PRD §3.6

**Estado atual verificado (2026-09-16):**

- Último hook: `custom_configs_update_11013` → próximo livre **`11014`**.
- Storages reutilizáveis existem: `block_content.field_image`, `paragraph.field_text_simple`, `paragraph.field_text_simple_long`.
- **Não** existe `field_itens_lista` em `block_content` (criar). Listas irmãs: `field_diferenciais_lista`, `field_o_que_fazemos_itens`, `field_como_funciona_itens`, `field_metodologia_passos` — cada uma específica ao bundle (não reutilizáveis aqui).
- Block types existentes: `cto`, `nossa_metodologia`, `nossos_diferenciais`, `destaque`, `como_funciona_bt`, `footer`, `o_que_fazemos_bt`, `basic`, `whatsapp` — **sem** `missao_visao`.
- Região `content_full` usada por blocos da home (`default_nossametodologia`, `default_oquefazemos`, `default_comofunciona`, vagas); **nenhum** placement atual limita path a `/quem-somos` nessa região.
- Suggestion Twig Barrio canônica para bundles: `block--block-{bundle}.html.twig` (ex.: `block--block-nossos-diferenciais.html.twig`).
- Tokens tipográficos: Poppins já no tema; overlay azul/escuro será variável local sob `.block-missao-visao`.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | título/descrição/imagem reusam; só `field_itens_lista` é novo (sem equivalente) |
| Deploy `cim` → `updb` → `cr` + seed idempotente | PASS | `11014` + `drush cex` estrutural |
| Clean URLs | PASS | visibilidade `/quem-somos` |
| Performance / CSS isolado | PASS | library + seletores sob `.block-missao-visao` |
| Contrib first / custom mínimo | PASS | sem módulo novo; Paragraphs já no projeto |

**Post-design**: gates mantidos; sem violação injustificada. Storage novo `field_itens_lista` justificado (cardinality 2 + target bundle exclusivo; padrão das features 004–007).

## Design Decisions

1. **Bundles**: `missao_visao` (block_content) + `missao_visao_item_p` (paragraph) — machine names da spec.
2. **Fields do item**: `field_text_simple` (título) + `field_text_simple_long` (descrição) no paragraph — reuso canônico; **não** criar `field_text_simple_small`.
3. **Fields do bloco**: `field_image` (fundo) reutilizado; `field_itens_lista` (ERR → `missao_visao_item_p`, cardinality **2**) — storage novo, alinhado ao padrão `field_*_lista` / `field_*_itens`.
4. **Twig do bloco**: `block--block-missao-visao.html.twig` (suggestion Barrio); classe wrapper `.block-missao-visao`; iterar itens no Twig (ou via field render + paragraph suggestion se o markup de coluna exigir).
5. **Fundo**: URL da mídia → `style="background-image: url(...)"` no wrapper; CSS `background-size: cover; background-position: center`; fallback `background-color` escuro se sem imagem.
6. **Overlay**: `::before` no wrapper (azul/escuro translúcido); conteúdo em `position: relative; z-index` acima do overlay.
7. **Grid**: `.container` + `.row` + `.col-md-6` + `.text-center` + `.text-white` + padding `.px-4` / `.px-lg-5`; divisória `border-end` só no 1º item em `md+` (remover no mobile via media query).
8. **Library**: `default/missao_visao` → `assets/css/block-missao-visao.css`; attach no Twig do bloco.
9. **Placement**: `block.block.default_missaovisao` — tema `default`, região `content_full`, weight `0`, pages `/quem-somos`, `label_display: '0'`, plugin `block_content:<UUID>`.
10. **UUID fixo**: `b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e` (bloco + dependency do placement).
11. **Hook `11014`**: garantir tipos/fields/displays (defensivo pós-`cim`); seed bloco + 2 paragraphs + imagem asset **somente se ausentes**; não sobrescrever conteúdo editorial; garantir placement/visibilidade se config ainda não importada.
12. **Asset**: `modules/custom/custom_configs/assets/missao-visao/fundo-missao-visao.jpg` (ou `.webp`/`.png`) → `public://missao-visao/`.
13. **PRD**: acrescentar bullet em §3.6 descrevendo `missao_visao`, fields, placement e `11014`.
14. **Fora**: não tocar banner 009, Twig/CSS Sobre nós 010, nem fields `*_2` do node `quem_somos`.

## Project Structure

### Documentation (this feature)

```text
specs/011-bloco-missao-visao/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/missao-visao-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  block_content.type.missao_visao.yml
  paragraphs.paragraphs_type.missao_visao_item_p.yml
  field.storage.block_content.field_itens_lista.yml
  field.field.block_content.missao_visao.field_image.yml
  field.field.block_content.missao_visao.field_itens_lista.yml
  field.field.paragraph.missao_visao_item_p.field_text_simple.yml
  field.field.paragraph.missao_visao_item_p.field_text_simple_long.yml
  core.entity_form_display.block_content.missao_visao.default.yml
  core.entity_view_display.block_content.missao_visao.default.yml
  core.entity_form_display.paragraph.missao_visao_item_p.default.yml
  core.entity_view_display.paragraph.missao_visao_item_p.default.yml
  block.block.default_missaovisao.yml
  # + user.role.* se permissões de paragraph/block mudarem (cex)

themes/custom/default/
  templates/block/block--block-missao-visao.html.twig
  templates/paragraph/paragraph--missao-visao-item-p.html.twig   # se markup de coluna exigir
  assets/css/block-missao-visao.css
  default.libraries.yml                                         # + missao_visao

modules/custom/custom_configs/
  custom_configs.install                                        # custom_configs_update_11014 + helpers
  assets/missao-visao/fundo-missao-visao.jpg                    # seed

PRD.md                                                          # §3.6 + bullet Missão/Visão
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Config: types, storages/instances, displays, placement → `drush cex`
3. Twig + library/CSS (overlay, grid, tipografia, mobile)
4. Hook `11014` + asset seed
5. PRD cirúrgico + validação quickstart (`cim` → `updb` → `cr`)

## Complexity Tracking

Nenhuma violação de gate a justificar. Único storage novo (`field_itens_lista`) é necessário e documentado na spec.
