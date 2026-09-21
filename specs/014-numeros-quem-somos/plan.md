# Implementation Plan: Números / Estatísticas Quem Somos (Impact in Numbers)

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente)  
**Date**: 2026-09-21  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/014-numeros-quem-somos/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Entregar a faixa **Impact in Numbers** no Node `quem_somos`: paragraph `numero_destaque_p` (destaque + subtexto), lista dedicada `field_numeros_lista` (cardinality **4**), seed tipográfico dos 4 pares do design, Twig no `node--quem-somos.html.twig` com grid Bootstrap 2/4 colunas, CSS sob `.section-impact-numbers` com tokens Figma, `custom_configs_update_11017` idempotente, `drush cex` → `config/sync`, PRD §3.1.0 cirúrgico. Sem alterar banner 009, Sobre nós 010, Missão/Visão 012 nem Diferenciais 013.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Node, Field, Text); Paragraphs + Entity Reference Revisions (já no projeto); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; estrutura em `config/sync`; seed via `hook_update_N`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md+ — breakpoints Bootstrap)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS encapsulado em library dedicada; sem JS novo; omit empty no Twig  
**Constraints**: sem `core/`/`vendor/`; reutilizar storages paragraph `field_text_simple` / `field_text_simple_long`; **não** reutilizar `field_itens_p` (cardinality 3); deploy `cim` → `updb` → `cr`; pt-BR; features 009–013 intactas  
**Scale/Scope**: 1 paragraph type, 1 field storage node + 1 instance, displays, 2 Twig (node + paragraph), 1 CSS/library, 1 hook `11017`, PRD cirúrgico

**Estado atual verificado (2026-09-21):**

- Último hook: `custom_configs_update_11016` → próximo livre **`11017`**.
- Storages paragraph reutilizáveis **existem**: `paragraph.field_text_simple`, `paragraph.field_text_simple_long`.
- Bundle `quem_somos` **não** tem `field_numeros_lista` nem paragraph `numero_destaque_p`.
- `field_itens_p` (node, cardinality **3**, usado em `para_empresas`) — **incompatível**; criar storage dedicado `field_numeros_lista` (cardinality 4).
- Twig ativo: `themes/custom/default/templates/content/node--quem-somos.html.twig` (Sobre nós + Missão/Visão); libraries `layout_sobre_nos` + `quem_somos_missao_visao`.
- Bloco 013 (`diferenciais_quem_somos`) em `content_full` — permanece; Impact in Numbers vive **no Node**, antes do bloco na ordem da página.
- Poppins já no tema; reforço tipográfico no escopo da nova seção.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storages de texto | PASS | zero storage paralelo de string; só instances no paragraph |
| Lista dedicada (não `field_itens_p`) | PASS | storage `field_numeros_lista` cardinality 4 — justificado (R3) |
| Deploy `cim` → `updb` → `cr` + hook idempotente | PASS | `11017` + `drush cex` estrutural |
| Clean URLs | PASS | rota `/quem-somos` inalterada |
| Performance / CSS isolado | PASS | library + seletores sob `.section-impact-numbers` |
| Contrib first / custom mínimo | PASS | sem módulo novo; Paragraphs já no projeto |
| Sem dump / Entity API | PASS | seed via entityTypeManager |
| Convivência 009–013 | PASS | só adiciona seção no Node; não altera blocos/fields alheios |

**Post-design**: gates mantidos; criação de storage ERR `field_numeros_lista` justificada em research R3 (única opção para cardinality 4 sem contaminar `field_itens_p`). Sem violação injustificada.

## Design Decisions

1. **Paragraph** `numero_destaque_p` (“Número Destaque”): `field_text_simple` (destaque) + `field_text_simple_long` (subtexto). Sem imagem.
2. **Campo Node** `field_numeros_lista`: Entity Reference Revisions → `numero_destaque_p`, **cardinality 4** (storage novo no entity type `node`).
3. **Não** reutilizar `field_itens_p` (cardinality 3 em `para_empresas`) nem elevar sua cardinality (escopo errado / risco colateral).
4. **Twig Node**: em `node--quem-somos.html.twig`, após Missão/Visão, renderizar `<section class="section-impact-numbers">` se houver ≥1 item utilizável; full-bleed via CSS breakout (mesmo padrão 012 — node dentro de `#main.container`).
5. **Twig paragraph**: `paragraph--numero-destaque-p.html.twig`; coluna `.col-6.col-md-3`; flex column centrado com gap 8px; omitir destaque/subtexto vazios; omitir item se ambos vazios.
6. **Markup grid**: container max-width 1280px + `.row` > itens `.col-6.col-md-3`; paddings da seção via CSS custom (`64px` / `40px`), não só utilitários genéricos.
7. **Library** `default/impact_numbers` → `assets/css/impact-numbers.css`; attach no Twig do node; tokens: fundo `#0F172A`, destaque `#FD7B1A` Poppins 700 (caixa ~56px), subtexto `#FFFFFF` opacity 0.8 uppercase (caixa ~20px).
8. **Hook `11017`**: ensure paragraph type + field storage/instance + displays (defensivo); localizar Node `quem_somos`; seed 4 paragraphs **somente se** lista vazia/ausente; **nunca** sobrescrever editorial divergente; **nunca** tocar fields/blocos 009–013.
9. **Field Group** (opcional no form): `group_numeros` no form display do Node para UX do editor — se Field Group já usado no bundle; manter padrão dos grupos existentes.
10. **PRD** §3.1.0: documentar `numero_destaque_p`, `field_numeros_lista`, tokens da seção, hook `11017`, library; mencionar convivência com bloco 013 em `content_full`.
11. **Fora**: título editorial da seção, Layout Builder, storages `field_text_simple_small*`, reuso `field_itens_p`, exibição fora de `/quem-somos`.

## Project Structure

### Documentation (this feature)

```text
specs/014-numeros-quem-somos/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/impact-numbers-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  paragraphs.paragraphs_type.numero_destaque_p.yml              # criar
  field.storage.node.field_numeros_lista.yml                    # criar (ERR, card 4)
  field.field.paragraph.numero_destaque_p.field_text_simple.yml
  field.field.paragraph.numero_destaque_p.field_text_simple_long.yml
  field.field.node.quem_somos.field_numeros_lista.yml
  core.entity_form_display.paragraph.numero_destaque_p.default.yml
  core.entity_view_display.paragraph.numero_destaque_p.default.yml
  core.entity_form_display.node.quem_somos.default.yml          # + lista (+ group opcional)
  core.entity_view_display.node.quem_somos.default.yml          # + lista

themes/custom/default/
  templates/content/node--quem-somos.html.twig                  # + seção Impact in Numbers
  templates/paragraph/paragraph--numero-destaque-p.html.twig
  assets/css/impact-numbers.css
  default.libraries.yml                                         # + impact_numbers

modules/custom/custom_configs/
  custom_configs.install                                        # custom_configs_update_11017 + helpers

PRD.md                                                          # §3.1.0 cirúrgico (+ menção deploy layout v2)
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Config: paragraph type, storage/instance lista, displays → `drush cex`
3. Twig node + paragraph + library/CSS (tokens Figma, grid, omit empty, full-bleed)
4. Hook `11017` (ensure + seed 4 itens se lista vazia)
5. PRD §3.1.0 + validação quickstart (`cim` → `updb` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Novo storage ERR `field_numeros_lista` | Spec exige cardinality **4** e proíbe reuso de `field_itens_p` (card 3, bundle `para_empresas`) | Elevar `field_itens_p` para 4 — contamina outro CT; card `-1` — afrouxa FR-003 |
| Full-bleed CSS breakout | Node renderiza dentro de `#main.container`; faixa escura precisa edge-to-edge (padrão 012) | Mover seção para bloco em `content_full` — fora do escopo (conteúdo no Node) |
