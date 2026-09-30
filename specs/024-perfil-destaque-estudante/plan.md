# Implementation Plan: Perfil em Destaque (Estudante)

**Branch**: `feature-para-estudantes` (scaffolding Spec Kit — `.specify/scripts` ausente neste repo; setup a partir de `.specify/feature.json` → `specs/024-perfil-destaque-estudante`)  
**Date**: 2026-09-30  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/024-perfil-destaque-estudante/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar a quarta seção de `/para-estudantes`: bloco **Perfil em Destaque** (`perfil_destaque_estudante` + paragraph `item_lista_icone_p`) na região `content_full`, só nessa rota, **imediatamente após** Jornada do Estudante (`default_jornadaestudante`, weight `1` → perfil weight `2`). Layout duas colunas (ilustração à esquerda; título + lista ícone/texto + CTA à direita), tipografia Poppins, CSS sob `.section-perfil-destaque` / `.block-perfil-destaque-estudante`, seed de 3 itens + ilustração + CTA “Completar meu perfil” → `/painel/estudante/perfil`, assets em `custom_configs/assets/perfil-destaque-estudante/`, hook idempotente `custom_configs_update_11038`, `drush cex` → `config/sync`, PRD §3.6 cirúrgico. Zero storages novos; max 3 itens via form/validação (storage `field_itens_lista` permanece `-1`). Sem alterar hero `021`, benefícios `022`, jornada `023`, View `vagas`, nem `core/`/`vendor/`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3 do tema  
**Primary Dependencies**: Drupal core (Block Content, Image, Field, Link, Text); Paragraphs + Entity Reference Revisions (já no projeto); tema `default` (Bootstrap Barrio 5). **Nenhuma dependência Composer/npm nova**.  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md / lg+ — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: library `perfil_destaque_estudante` só na página do bloco; omit empty; imagens lazy; sem JS novo  
**Constraints**: sem `core/`/`vendor/`; zero field storages novos; reutilizar `field_image`, `field_text_simple`, `field_text_simple_long`, `field_itens_lista`, `field_link`; max 3 itens sem mutar cardinality do storage compartilhado (`-1`); deploy `cim` → `updb` → (2ª `cim`) → `cr`; pt-BR; isolamento CSS; não alterar hero `021`, benefícios `022`, jornada `023`, View `vagas`, PE/QS/home  
**Scale/Scope**: 1 block type, 1 paragraph type, ~14 YAMLs de config, 2 Twig, 1 CSS/library, 1 hook `11038`, 4 assets seed (1 ilustração + 3 ícones), PRD §3.6

**Estado atual verificado (2026-09-30):**

- Último hook: `custom_configs_update_11037` → próximo livre **`11038`**.
- Storages reutilizáveis **existem**: `block_content.field_image`, `field_text_simple`, `field_itens_lista` (**cardinality storage `-1`** — não alterar), `field_link`; `paragraph.field_image`, `field_text_simple`, `field_text_simple_long`.
- Bundles `perfil_destaque_estudante` / `item_lista_icone_p` **ainda não existem**.
- Em `/para-estudantes`: hero em `banner` (`block_para_estudantes`); `default_beneficiosestudantes` weight **`0`**; `default_jornadaestudante` weight **`1`** (UUID `b9c0…`). Perfil entra com weight **`2`**.
- Padrão estrutural mais próximo: `022` (bloco + paragraphs com ícone) + CTA/`field_link` de blocos tipo `cto`; layout duas colunas alinhado a `missao_visao` (imagem + lista), com markup Bootstrap próprio desta Section 04.
- Limite de itens: mesmo trade-off da jornada (`023`) — enforcement no form/validação do bundle, **não** no storage.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | zero storage novo; só instances + bundles; canônicos `field_image` / `field_text_simple` / `field_link` |
| Cardinalidade 3 sem mutar storage `-1` | PASS | limite no form/validação do bundle (research R3) |
| Deploy `cim` → `updb` → `cr` (+ 2ª `cim`) + hook idempotente | PASS | `11038` + `drush cex` estrutural + assets versionados |
| Clean URLs | PASS | visibility `/para-estudantes`; CTA `/painel/estudante/perfil` |
| Performance / CSS isolado | PASS | library `perfil_destaque_estudante`; wrappers `.section-perfil-destaque` / `.block-perfil-destaque-estudante` |
| Contrib first / custom mínimo | PASS | sem módulo novo; Paragraphs já no projeto |
| Sem dump / Entity API | PASS | seed via `BlockContent::create()` / entityTypeManager |
| Convivência hero 021 / benefícios 022 / jornada 023 / vagas | PASS | placement weight `2` só `/para-estudantes`; CSS escopado |

**Post-design**: gates mantidos. Limite de 3 itens via form/validação (não via alteração do storage compartilhado) justificado em research R3. Sem violação injustificada.

## Design Decisions

1. **Paragraph** `item_lista_icone_p` (“Item de Lista com Ícone”): `field_image` (ícone) + `field_text_simple` (título) + `field_text_simple_long` (descrição).
2. **Block type** `perfil_destaque_estudante`: `field_image` (ilustração), `field_text_simple` (título), `field_itens_lista` → `item_lista_icone_p` (handler só esse bundle), `field_link` (CTA).
3. **Mapeamentos canônicos**: pedido `field_text_simple_small` → `field_text_simple`; pedido `field_imagem` → `field_image` em `block_content`; lista = instance de `field_itens_lista` (storage permanece `-1`).
4. **Max 3 itens**: storage compartilhado **inalterado**; enforcement no form display do bundle (`hook_form_alter` e/ou validação) — esconde “Add more” com ≥3 e rejeita submit com >3 (FR-004 / US4).
5. **Twig bloco**: `block--block-perfil-destaque-estudante.html.twig`; classes raiz `section-perfil-destaque` + `block-perfil-destaque-estudante`; markup FR-009–014 (`.row.align-items-center.g-5` + `.col-12.col-lg-6` × 2).
6. **Twig paragraph**: `paragraph--item-lista-icone-p.html.twig` — item flex `.d-flex.gap-3.mb-4`; ícone `20×20` `flex-shrink-0`; título destaque `#9D4300`; descrição `#45464D`.
7. **Library** `default/perfil_destaque_estudante` → `assets/css/perfil-destaque-estudante.css`; tokens locais (paddings 64/40, max-width 1280, título max 528px, CTA ~208×44 navy `#023C62`); **não** mutar tokens globais do tema.
8. **UUID fixo** do `block_content`: `c0d1e2f3-a4b5-4678-c901-2def01234567`.
9. **Placement** `block.block.default_perfildestaqueestudante`: tema `default`, região `content_full`, weight **`2`**, `request_path` = `/para-estudantes`, `label_display: '0'`, plugin UUID alinhado ao seed.
10. **Hook `11038`**: ensure types/fields/displays (defensivo); seed bloco + ilustração + 3 paragraphs + ícones de `modules/custom/custom_configs/assets/perfil-destaque-estudante/` **somente se ausentes/vazios**; ensure placement weight `2`; **nunca** sobrescrever editorial; **nunca** alterar 021/022/023/vagas/PE/QS/home.
11. **Permissões**: após criar bundle, ajustar roles que já editam block content; exportar via `cex`.
12. **PRD** §3.6 (rota `/para-estudantes`): documentar block type + placement weight `2` + `11038` + convivência com hero `021`, benefícios `022` e jornada `023`.
13. **Fora**: hero 021, benefícios 022, jornada 023, View `vagas`, formulário real de perfil, storages paralelos `field_text_simple_small` / `field_imagem`.

## Project Structure

### Documentation (this feature)

```text
specs/024-perfil-destaque-estudante/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── perfil-destaque-estudante-render.md
│   └── deploy-perfil-destaque-estudante.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  paragraphs.paragraphs_type.item_lista_icone_p.yml
  block_content.type.perfil_destaque_estudante.yml
  field.field.paragraph.item_lista_icone_p.field_image.yml
  field.field.paragraph.item_lista_icone_p.field_text_simple.yml
  field.field.paragraph.item_lista_icone_p.field_text_simple_long.yml
  field.field.block_content.perfil_destaque_estudante.field_image.yml
  field.field.block_content.perfil_destaque_estudante.field_text_simple.yml
  field.field.block_content.perfil_destaque_estudante.field_itens_lista.yml
  field.field.block_content.perfil_destaque_estudante.field_link.yml
  core.entity_form_display.paragraph.item_lista_icone_p.default.yml
  core.entity_view_display.paragraph.item_lista_icone_p.default.yml
  core.entity_form_display.block_content.perfil_destaque_estudante.default.yml
  core.entity_view_display.block_content.perfil_destaque_estudante.default.yml
  block.block.default_perfildestaqueestudante.yml
  user.role.*.yml                                               # permissões do bundle

themes/custom/default/
  templates/block/block--block-perfil-destaque-estudante.html.twig
  templates/paragraph/paragraph--item-lista-icone-p.html.twig
  assets/css/perfil-destaque-estudante.css
  default.libraries.yml                                         # + perfil_destaque_estudante

modules/custom/custom_configs/
  custom_configs.install                                        # custom_configs_update_11038 + helpers
  custom_configs.module                                         # form alter / validação max 3
  assets/perfil-destaque-estudante/                             # ilustração + 3 ícones

PRD.md                                                          # §3.6 cirúrgico (+ rota /para-estudantes)
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Config: tipos, instances, displays, placement → `drush cex`
3. Twig bloco + paragraph + library/CSS (duas colunas, tokens 64/40/20/208×44)
4. Hook `11038` + assets seed + limite form max 3
5. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Max 3 no form/validação, storage `field_itens_lista` permanece `-1` | Storage compartilhado com 013/022/023/etc.; mutar cardinality quebraria outros bundles | Storage novo cardinality 3 — viola reuso; ou setar storage=3 — regressão |
| Paragraph novo `item_lista_icone_p` vs. reusar `card_icon_text_p` | Machine name + layout de lista (não card grid) pedidos na spec; evita regressão visual 022 | Reusar `card_icon_text_p` — colisão editorial/CSS |
| Weight `2` fixo após jornada `1` | Ordem Hero → Benefícios → Jornada → Perfil (US5) | Weight alto “por precaução” — desnecessário |
