# Implementation Plan: Listagem Vertical de Vagas (`/vagas`)

**Branch**: `feature-vagas-new` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente via `.specify/feature.json`)  
**Date**: 2026-10-04  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/028-vagas-lista-vertical/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Refatorar a listagem principal de `/vagas` (View `vagas` `page_1`): abandonar o grid de cards laranja e adotar **lista vertical de cards brancos detalhados** (Figma), com campo booleano **`field_vaga_destaque`**, ordenação destaques primeiro, **5 itens/página**, AJAX e badge “Destaque”. Implementação: Twig Custom Text de `page_1` + wrappers (sem view mode novo), CSS/library escopados, `custom_configs_update_11045` idempotente + `drush cex`; PRD §3.1 / §3.6 cirúrgico. Hero Search (`027`) e cards laranja de Home/`block_3`/similares **intocados**.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Node, Field, Views, Image); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova** (`views_infinite_scroll` fora).  
**Storage**: PostgreSQL; field storage/instance + View + form display em `config/sync`; seeds estruturais via Entity API no hook  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤575.98px / md+)  
**Project Type**: Drupal theme + custom modules (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS em library dedicada; markup leve; AJAX do Views (sem full reload); cache da View inalterado em essência  
**Constraints**: sem `core/`/`vendor/`; **não** alterar Twig/CSS dos cards laranja Home/`block_3`; sidebar filtros Figma / “Recomendado” / “Melhore currículo” fora; deploy `cim` → `updb` → `cim` → `cr`; pt-BR  
**Scale/Scope**: 1 field boolean + displays; View `page_1` (pager/sort/row_class); 3 Twigs de listagem; 1 library/CSS; 1 hook `11045`; PRD cirúrgico

**Estado atual verificado (2026-10-04):**

- Hook desta feature: `custom_configs_update_11045` (`11044` já usado pelo Hero Search `/vagas`).
- `page_1`: path `vagas`; `items_per_page: 12`; `use_ajax: true`; pager `full`; `row_class: col-12 col-md-6 col-lg-4`; sort herdado `created` DESC; Fields + Custom Text `nothing`.
- Card atual: `views-view-field--vagas--page-1--nothing.html.twig` → `.item-vaga--destaque` (laranja); wrappers `views-view--vagas--page-1.html.twig` (`.view-content.row.g-4`) + `views-view-unformatted--vagas--page-1.html.twig` (cols Bootstrap duplicadas).
- `field_vaga_destaque`: **inexistente**; nenhum boolean storage em `node` (só em `user`).
- Logo empresa: `field_empresa_u` → User → `user_picture` (não usado nos Twigs de card hoje); nome via `field_nome_fantasia`.
- `views_infinite_scroll`: ausente de `composer.json` / contrib.
- Placements “Recomendado para você” / “Melhore seu currículo” em `/vagas`: **não existem** (hook de limpeza = no-op seguro).
- Hero `027` em `highlighted` weight `-50` só `/vagas` — permanece acima da listagem.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storage | PASS | novo storage boolean em `node` justificado (não existe equivalente reutilizável no entity type) |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11045` (field + View + limpeza) + `drush cex` |
| Clean URLs | PASS | rota `/vagas` inalterada; CTA → canonical da vaga |
| Performance / CSS isolado | PASS | library dedicada; classes novas (não reutilizar `.item-vaga--destaque` na listagem) |
| Contrib first | PASS | Views + Field API; sem Composer novo |
| Sem dump / Entity API | PASS | Field/View via Entity API no hook |
| Isolamento Home / PE / similares | PASS | só Twigs/CSS de `page_1`; `block_1`/`block_2`/`block_3` intocados |

**Post-design**: gates mantidos. Storage boolean novo e CSS dedicado justificados em Complexity Tracking. Sem violação injustificada.

## Design Decisions

1. **Renderização**: manter Fields + Custom Text (`nothing`) em `page_1`; reescrever **apenas** o Twig `views-view-field--vagas--page-1--nothing.html.twig` com markup do card vertical branco. **Não** criar view mode `lista_vertical` / `teaser` (YAGNI; padrão 025).
2. **Classes novas**: raiz `.item-vaga--lista` (+ BEM `vaga-lista__*`); **não** reutilizar `.item-vaga--destaque` / `.vaga-destaque__*` na listagem (evita regressão visual se alguém alterar CSS compartilhado).
3. **CSS / library**: `default/vagas_lista_vertical` → `assets/css/components/vagas-lista-vertical.css`; attach no preprocess da View `page_1` (ou no wrapper Twig via `attach_library`). Escopo sob `.css-vagas-page`. **Não** alterar regras `:is(.css-vagas-home, .css-vagas-page) .item-vaga--destaque` de forma regressiva.
4. **Grid → lista**:
   - View `style.options.row_class` → `col-12` (ou vazio) em `page_1`;
   - `views-view-unformatted--vagas--page-1.html.twig` → uma coluna por row sem `col-md-6 col-lg-4`;
   - `views-view--vagas--page-1.html.twig` → `.view-content` sem `row g-4` de grid; container centralizado com `max-width` ~720px (lista).
5. **Campo `field_vaga_destaque`**: boolean, label “Destaque”, default `0`/`False`, cardinality 1; instance no bundle `vagas`; widget checkbox no form display `default`; opcionalmente hidden/omitido no view display default do node (card lê entity direto).
6. **Ordenação `page_1`**: sort `field_vaga_destaque` DESC + `created` DESC (override de sorts no display; `defaults.sorts = FALSE`).
7. **Pager**: `items_per_page: 5`; manter `type: full` + `use_ajax: true`. **Não** instalar `views_infinite_scroll`.
8. **Badge / borda**: se destaque → badge verde `#58A83C` (estrela FA + “Destaque”) absoluto no topo-direita + borda verde no card.
9. **Logo**: `field_empresa_u.entity.user_picture` (image style `thumbnail` ou equivalente ~64×64); ausente → omitir área ou placeholder discreto sem quebrar layout.
10. **Campos do card**:
    | UI | Fonte |
    |----|--------|
    | Título | `title` |
    | Empresa | `field_empresa_u` → `field_nome_fantasia` |
    | Local | `field_cidade` + `field_estados` |
    | Regime | `field_regime_t` term label |
    | Bolsa | `field_text_simple` |
    | Carga | `field_horarios` (label do list item) |
    | Tags | até 3 de `field_text_simple_multiple_2` (Benefícios); se vazio, labels de `field_cursos_t`; se >3 → `+N` |
    | Data | “Publicada há …” via `DateFormatter::formatTimeDiffSince(created)` (preprocess Twig var) |
    | CTA | destaque → “Candidatura Rápida” (sólido); senão “Ver Detalhes” (outline); ambos → canonical da vaga |
11. **Hook `11045`**:
    - `_custom_configs_ensure_field_vaga_destaque()` — storage + instance + form display;
    - `_custom_configs_ensure_vagas_page_1_lista_vertical()` — items_per_page 5, use_ajax true, sorts, row_class, css_class se necessário;
    - `_custom_configs_cleanup_vagas_out_of_scope_blocks()` — desabilitar/remover placements “Recomendado…” / “Melhore…” se existirem (no-op se ausentes);
    - **Não** tocar `block_1`/`block_2`/`block_3`, hero `027`, formulário exposto (permanece desativado).
12. **PRD**: §3.1 (`vagas` + campo Destaque) e §3.6 (View `page_1`: 5/página, lista vertical, sort destaque, hook `11045`) — cirúrgico.
13. **Fora**: sidebar filtros Figma; contador/ordenar/favoritar; infinite scroll Composer; redesign Home/PE/similares; alterar Hero Search.

## Project Structure

### Documentation (this feature)

```text
specs/028-vagas-lista-vertical/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── vagas-lista-vertical-render.md
│   └── deploy-vagas-lista-vertical.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/views/views-view-field--vagas--page-1--nothing.html.twig  # reescrever (card vertical)
  templates/views/views-view--vagas--page-1.html.twig                 # lista centralizada (sem grid row)
  templates/views/views-view-unformatted--vagas--page-1.html.twig     # sem cols 3-col
  assets/css/components/vagas-lista-vertical.css                      # novo
  default.libraries.yml                                               # + vagas_lista_vertical
  default.theme                                                       # preprocess: library + data relativa (+ attach)

modules/custom/custom_configs/
  custom_configs.install   # custom_configs_update_11045 + helpers

config/sync/
  field.storage.node.field_vaga_destaque.yml          # criar (cex)
  field.field.node.vagas.field_vaga_destaque.yml      # criar (cex)
  core.entity_form_display.node.vagas.default.yml     # + checkbox
  views.view.vagas.yml                                # page_1: 5, sorts, row_class
  # opcional: core.entity_view_display.node.vagas.default.yml se o campo for adicionado ao display

PRD.md   # §3.1 + §3.6 cirúrgico

# NÃO alterar (regressão proibida):
#   views-view-field--vagas--block-1--nothing.html.twig
#   views-view-field--vagas--block-3--nothing.html.twig
#   block_2 similares Twig
#   CSS :is(.css-vagas-home, .css-vagas-page) .item-vaga--destaque (exceto se remover escopo page_1 órfão — opcional/não obrigatório)
#   Hero Search 027 (plugin/Twig/CSS/placement)
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Field `field_vaga_destaque` + form display (origem) + ensure no hook
3. Twig card vertical + wrappers lista + preprocess (data relativa / library)
4. Library/CSS escopada (tokens Figma, badge, CTAs, responsivo)
5. Ensure View `page_1` (5, AJAX, sorts, row_class) + limpeza blocos no hook `11045`
6. Origem: `drush cex` → versionar field/View/displays
7. PRD §3.1/§3.6 + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Novo `field.storage.node.field_vaga_destaque` | Nenhum boolean storage em `node` reutilizável; booleans de `user` não são compartilháveis entre entity types | Reusar storage de user — impossível |
| CSS/library novos vs. reestilizar `.item-vaga--destaque` só em `.css-vagas-page` | Spec exige card branco distinto; Home/PE compartilham o seletor atual — risco alto de regressão | Overrides no mesmo BEM laranja — acoplamento e SC-006 |
| Manter Custom Text Twig (não view mode) | Padrão 025; menos config; escopo só `page_1` | View mode `lista_vertical` — mais YAML/displays sem ganho |
| Pager `full` AJAX sem `views_infinite_scroll` | Módulo ausente; FR-008 aceita fallback; YAGNI Composer | Nova dependência só por “Carregar mais” |
| Ensure View no hook + cex | Padrão 11040–11044; destino sem admin | Só cex — frágil; só hook — drift |
