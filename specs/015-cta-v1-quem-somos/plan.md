# Implementation Plan: Bloco CTA v1 — Quem Somos

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-22  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/015-cta-v1-quem-somos/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar o bloco gerenciável **CTA v1** (`cta_v1`) na região `content_full` de `/quem-somos`, **logo após** Impact in Numbers (`default_impactnumbersquemsomos`, weight `11`): título + subtítulo + dois botões (primário/secundário), Twig com utilitários Bootstrap 5, CSS sob `.block-cta-v1` com tokens Figma (`#D3E4FE`, radius `32px`, padding `64px`, gap `24px`, primário `#FD7B1A`), seed tipográfico + placement weight `12` via `custom_configs_update_11019` idempotente, `drush cex` → `config/sync`, PRD §3.6 cirúrgico. Sem alterar banner 009, Sobre nós 010, Missão/Visão 012, Diferenciais 013 nem Impact in Numbers 014.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Block Content, Field, Link, System visibility); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N` + Entity API  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md+ — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada; sem JS novo; omit empty no Twig  
**Constraints**: sem `core/`/`vendor/`; reutilizar storages `field_text_simple`, `field_text_simple_long`, `field_link`; criar só `field_link_2` se inexistente; **não** criar `field_text_simple_small` nem `field_link_secundario`; deploy `cim` → `updb` → `cr`; pt-BR; features 009–014 intactas  
**Scale/Scope**: 1 block type, 1 field storage novo (`field_link_2`) + 3 instances reutilizando storages existentes, displays, 1 Twig, 1 CSS/library, 1 placement, 1 hook `11019`, PRD cirúrgico

**Estado atual verificado (2026-09-22):**

- Último hook: `custom_configs_update_11018` → próximo livre **`11019`**.
- Storages `block_content` reutilizáveis **existem**: `field_text_simple`, `field_text_simple_long`, `field_link`.
- Storage **`field_link_2` ausente** → criar (link, cardinality 1; padrão `*_2` do projeto, ex. `field_text_simple_2`).
- Bundle `cta_v1` **não existe**.
- Placement Impact in Numbers: `default_impactnumbersquemsomos`, região `content_full`, weight **`11`**, pages `/quem-somos`.
- Diferenciais Quem Somos: weight **`10`** no mesmo fluxo.
- Bloco `cto` (home) reusa `field_link` + Twig `block--block-cto.html.twig` — **não** reutilizar visual/CSS do CTO (tokens e layout diferentes; Figma `#FD7B1A` ≠ `.ui-btn--primary` `#e55a24`).
- Poppins já no tema; reforço tipográfico no escopo do novo bloco.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages | PASS | textos + `field_link` reutilizados; só `field_link_2` novo (justificado R3) |
| Deploy `cim` → `updb` → `cr` + hook idempotente | PASS | `11019` + `drush cex` estrutural |
| Clean URLs | PASS | seed `/vagas`, `/cadastro/candidato`; rota `/quem-somos` inalterada |
| Performance / CSS isolado | PASS | library + seletores sob `.block-cta-v1` |
| Contrib first / custom mínimo | PASS | sem módulo novo; Block Content + Link core |
| Sem dump / Entity API | PASS | seed via `BlockContent::create()` / entityTypeManager |
| Convivência 009–014 | PASS | só adiciona placement após weight 11; não altera blocos/fields alheios |

**Post-design**: gates mantidos; criação de storage `field_link_2` justificada em research R3 (segundo link no mesmo bundle; padrão `*_2`). Sem violação injustificada.

## Design Decisions

1. **Block type** `cta_v1` (“CTA v1”): `field_text_simple` (título), `field_text_simple_long` (subtítulo), `field_link` (botão primário), `field_link_2` (botão secundário).
2. **Não** criar `field_text_simple_small` nem `field_link_secundario`.
3. **Twig**: `block--block-cta-v1.html.twig` (padrão `block--block-*`); classe raiz `.block-cta-v1`; card interno `.cta-v1` (max-width `1200px`, centralizado).
4. **Markup botões**: equivalente a `.d-grid.gap-2.d-md-flex.justify-content-md-center.gap-md-3` (empilha mobile / lado a lado md+); gap textos↔botões `24px`.
5. **CSS próprio** sob `.block-cta-v1` (não reusar `.ui-btn--primary` global — cor diferente do Figma). Tokens: fundo `#D3E4FE`, radius `32px`, padding `64px`, título `#0F172A` Poppins semibold/bold, subtítulo `#45464D`, primário bg `#FD7B1A` texto branco, secundário bg `#FFFFFF` texto escuro sem borda forte.
6. **Library** `default/cta_v1` → `assets/css/cta-v1.css`; attach no Twig do bloco.
7. **UUID** bloco seed: `e6f7a8b9-c0d1-4e2f-9a3b-4c5d6e7f8091`; placement ID `default_ctav1quemsomos` (UUID placement `f7a8b9c0-d1e2-4f3a-a0b5-192a3b4c5d6e`); região `content_full`, weight **`12`**, visibility `request_path` = `/quem-somos`.
8. **Hook `11019`**: ensure block type + storages/instances + displays; seed se ausente/campos vazios; ensure placement; **nunca** sobrescrever editorial divergente; **nunca** tocar 009–014.
9. **Seed**: título `Seu próximo estágio começa aqui.`; subtítulo `Junte-se a milhares de estudantes e encontre a oportunidade que vai mudar sua carreira.`; primário `Buscar vagas` → `/vagas`; secundário `Cadastrar gratuitamente` → `/cadastro/candidato`.
10. **PRD** §3.6 (+ menção breve em §3.1.0 se necessário): documentar `cta_v1`, campos, placement weight 12, UUID, hook `11019`, library; atualizar linha de deploy layout v2.
11. **Fora**: Layout Builder; exibição fora de `/quem-somos`; alteração do bloco CTO da home.

## Project Structure

### Documentation (this feature)

```text
specs/015-cta-v1-quem-somos/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/cta-v1-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  block_content.type.cta_v1.yml                                 # criar
  field.storage.block_content.field_link_2.yml                  # criar (link, card 1)
  field.field.block_content.cta_v1.field_text_simple.yml
  field.field.block_content.cta_v1.field_text_simple_long.yml
  field.field.block_content.cta_v1.field_link.yml
  field.field.block_content.cta_v1.field_link_2.yml
  core.entity_form_display.block_content.cta_v1.default.yml
  core.entity_view_display.block_content.cta_v1.default.yml
  block.block.default_ctav1quemsomos.yml                        # content_full weight 12
  user.role.*.yml                                               # permissões create/edit/delete do bundle (se necessário)

themes/custom/default/
  templates/block/block--block-cta-v1.html.twig
  assets/css/cta-v1.css
  default.libraries.yml                                         # + cta_v1

modules/custom/custom_configs/
  custom_configs.install                                        # custom_configs_update_11019 + helpers

PRD.md                                                          # §3.6 cirúrgico (+ deploy layout v2)
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Config: block type, `field_link_2`, instances, displays, placement → `drush cex`
3. Twig + library/CSS (tokens Figma, Bootstrap gap/stack, omit empty)
4. Hook `11019` (ensure + seed + placement)
5. PRD §3.6 + validação quickstart (`cim` → `updb` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Novo storage `field_link_2` | Spec exige segundo botão link; `field_link` já ocupa o primário; padrão `*_2` do projeto | `field_link_secundario` — fora do padrão; reusar só `field_link` multi — conflita com CTO/cardinality 1 e UX de “dois botões nomeados” |
| CSS próprio vs `.ui-btn--primary` | Figma exige `#FD7B1A`; global usa `#e55a24` — reuso quebraria SC-001 ou alteraria home/CTO | Estender `.ui-btn--primary` global — risco de regressão visual |
