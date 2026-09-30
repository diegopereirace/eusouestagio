# Implementation Plan: Jornada do Estudante

**Branch**: `feature-para-estudantes` (scaffolding Spec Kit — `.specify/scripts` ausente neste repo; setup a partir de `.specify/feature.json` → `specs/023-jornada-estudante`)  
**Date**: 2026-09-30  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/023-jornada-estudante/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar a terceira seção de `/para-estudantes`: bloco **Jornada do Estudante** (`jornada_estudante` + paragraph `passo_jornada_p`) na região `content_full`, só nessa rota, **imediatamente após** Benefícios Estudantes (`default_beneficiosestudantes`, weight `0` → jornada weight `1`). Título centralizado “Sua jornada até o sucesso” + grid Bootstrap 1/2/4 de cards com badge numerada (48px; navy `#023C62` nos passos não finais; laranja `#FD7B1A` no último via `loop.last` / `:last-child`), tipografia Poppins, CSS sob `.block-jornada-estudante`, seed de 4 passos (sem assets de imagem), hook idempotente `custom_configs_update_11037`, `drush cex` → `config/sync`, PRD §3.6 cirúrgico. Zero storages novos; número/cor **não** persistem no banco. Sem alterar hero `021`, benefícios `022`, View `vagas`, nem `core/`/`vendor/`.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3 do tema  
**Primary Dependencies**: Drupal core (Block Content, Field, Text); Paragraphs + Entity Reference Revisions (já no projeto); tema `default` (Bootstrap Barrio 5). **Nenhuma dependência Composer/npm nova**.  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md / lg+ — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: library `jornada_estudante` só na página do bloco; omit empty; sem JS novo; sem imagens seed  
**Constraints**: sem `core/`/`vendor/`; zero field storages novos; reutilizar `field_text_simple`, `field_text_simple_long`, `field_itens_lista`; max 4 passos sem mutar cardinality do storage compartilhado (`-1`); deploy `cim` → `updb` → (2ª `cim`) → `cr`; pt-BR; isolamento CSS; não alterar hero `021`, benefícios `022`, View `vagas`, PE/QS/home  
**Scale/Scope**: 1 block type, 1 paragraph type, ~10 YAMLs de config, 2 Twig, 1 CSS/library, 1 hook `11037`, PRD §3.6

**Estado atual verificado (2026-09-30):**

- Último hook: `custom_configs_update_11036` → próximo livre **`11037`**.
- Storages reutilizáveis **existem**: `block_content.field_text_simple`, `field_text_simple_long`, `field_itens_lista` (**cardinality storage `-1`** — não alterar); `paragraph.field_text_simple`, `field_text_simple_long`.
- Bundles `jornada_estudante` / `passo_jornada_p` **ainda não existem**.
- Em `/para-estudantes`: hero em `banner` (`block_para_estudantes`); `default_beneficiosestudantes` em `content_full` weight **`0`**; formulário exposto de vagas desativado (`11036`). Jornada entra com weight **`1`**.
- Tokens de marca já no tema: navy `#023C62` (ex. `style.css`); laranja Figma `#FD7B1A` (padrão 009–022; **não** mutar `--brand-orange` global).
- Padrão estrutural mais próximo: `022-beneficios-estudantes` (bloco + paragraphs + grid `col-12/md-6/lg-3`); diferença: sem ícone/imagem; badge numérica + destaque do último passo.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | zero storage novo; só instances + bundles |
| Cardinalidade 4 sem mutar storage `-1` | PASS | limite no form/validação do bundle (research R3) |
| Deploy `cim` → `updb` → `cr` (+ 2ª `cim`) + hook idempotente | PASS | `11037` + `drush cex` estrutural |
| Clean URLs | PASS | visibility `/para-estudantes` |
| Performance / CSS isolado | PASS | library `jornada_estudante`; wrapper `.block-jornada-estudante` |
| Contrib first / custom mínimo | PASS | sem módulo novo; Paragraphs já no projeto |
| Sem dump / Entity API | PASS | seed via `BlockContent::create()` / entityTypeManager |
| Convivência hero 021 / benefícios 022 / vagas | PASS | placement weight `1` só `/para-estudantes`; CSS escopado |

**Post-design**: gates mantidos. Limite de 4 passos via form/validação (não via alteração do storage compartilhado) justificado em research R3. Sem violação injustificada.

## Design Decisions

1. **Paragraph** `passo_jornada_p` (“Passo da Jornada”): `field_text_simple` (título) + `field_text_simple_long` (descrição). Sem campo numérico, cor ou ícone.
2. **Block type** `jornada_estudante`: `field_text_simple` (título da seção) + `field_itens_lista` → `passo_jornada_p` (handler só esse bundle).
3. **Mapeamentos canônicos**: pedido `field_text_simple_small` → `field_text_simple`; lista = instance de `field_itens_lista` (storage permanece `-1`).
4. **Max 4 passos**: storage compartilhado **inalterado**; enforcement no form display do bundle (`hook_form_alter` e/ou validação) — esconde “Add more” com ≥4 e rejeita submit com >4 (FR-004 / US3).
5. **Twig bloco**: `block--block-jornada-estudante.html.twig`; classe raiz `block-jornada-estudante`; loop Twig com `loop.index` / `loop.last` para badge e classe do último passo; markup FR-009–017 (container max 1280px, paddings ~64/40, `.row.justify-content-center.g-4.mt-4`).
6. **Twig paragraph**: `paragraph--passo-jornada-p.html.twig` — colunas `.col-12.col-md-6.col-lg-3` + card; badge pode ser renderizada no bloco (contexto de loop) ou no paragraph se o bloco passar índice — preferência: **bloco controla o loop e a badge** (research R4).
7. **Library** `default/jornada_estudante` → `assets/css/jornada-estudante.css`; tokens locais (badge navy/laranja, raio 16px, min-height ~202px, Poppins); **não** mutar `--brand-orange` global.
8. **UUID fixo** do `block_content`: `b9c0d1e2-f3a4-4567-b890-1cdef0123456`.
9. **Placement** `block.block.default_jornadaestudante`: tema `default`, região `content_full`, weight **`1`**, `request_path` = `/para-estudantes`, `label_display: '0'`, plugin UUID alinhado ao seed.
10. **Hook `11037`**: ensure types/fields/displays (defensivo); seed bloco + 4 paragraphs **somente se ausentes/vazios**; ensure placement weight `1`; **nunca** sobrescrever editorial; **nunca** alterar 021/022/vagas/PE/QS/home.
11. **Permissões**: após criar bundle, ajustar roles que já editam block content; exportar via `cex`.
12. **PRD** §3.6 (rota `/para-estudantes`): documentar block type + placement weight `1` + `11037` + convivência com hero `021` e benefícios `022`.
13. **Fora**: hero 021, benefícios 022, View `vagas`, campo numérico/cor no CMS, storages paralelos, assets de imagem.

## Project Structure

### Documentation (this feature)

```text
specs/023-jornada-estudante/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── jornada-estudante-render.md
│   └── deploy-jornada-estudante.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  paragraphs.paragraphs_type.passo_jornada_p.yml
  block_content.type.jornada_estudante.yml
  field.field.paragraph.passo_jornada_p.field_text_simple.yml
  field.field.paragraph.passo_jornada_p.field_text_simple_long.yml
  field.field.block_content.jornada_estudante.field_text_simple.yml
  field.field.block_content.jornada_estudante.field_itens_lista.yml
  core.entity_form_display.paragraph.passo_jornada_p.default.yml
  core.entity_view_display.paragraph.passo_jornada_p.default.yml
  core.entity_form_display.block_content.jornada_estudante.default.yml
  core.entity_view_display.block_content.jornada_estudante.default.yml
  block.block.default_jornadaestudante.yml
  user.role.*.yml                                               # permissões do bundle

themes/custom/default/
  templates/block/block--block-jornada-estudante.html.twig
  templates/paragraph/paragraph--passo-jornada-p.html.twig
  assets/css/jornada-estudante.css
  default.libraries.yml                                         # + jornada_estudante

modules/custom/custom_configs/
  custom_configs.install                                        # custom_configs_update_11037 + helpers
  custom_configs.module                                         # form alter / validação max 4 (se necessário)

PRD.md                                                          # §3.6 cirúrgico (+ rota /para-estudantes)
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Config: tipos, instances, displays, placement → `drush cex`
3. Twig bloco + paragraph + library/CSS (grid, badge 48px, último laranja, tokens 64/40/16/202)
4. Hook `11037` + limite form max 4
5. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Max 4 no form/validação, storage `field_itens_lista` permanece `-1` | Storage é compartilhado com outros bundles; mutar cardinality quebraria 013/022/etc. | Storage novo `field_jornada_passos` cardinality 4 — viola reuso; ou setar storage=4 — regressão |
| Badge/número no Twig do bloco (loop) vs. campo no banco | FR-005 / Assumptions; YAGNI | Campo numérico/cor editável — fora de escopo |
| Weight `1` fixo após benefícios `0` | Ordem Hero → Benefícios → Jornada (US4) | Weight alto “por precaução” — desnecessário |
