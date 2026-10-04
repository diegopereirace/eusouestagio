# Research: Listagem Vertical de Vagas (`/vagas`)

**Data**: 2026-10-04 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **isolamento dos cards laranja** (Home / PE / similares), **reuso do padrão Fields + Custom Text**, **zero dependência Composer nova**, e **deploy repetível** (`cim` → `updb` → `cim` → `cr`).

---

## R1 — Custom Text Twig (não view mode)

**Decision**: manter `page_1` em modo Fields + campo Custom Text `nothing`; reescrever só `views-view-field--vagas--page-1--nothing.html.twig`.

**Rationale**: Assumptions da spec (opção b); mesmo padrão da feature `025`; evita criar view mode/`entity_view_display` só para listagem; escopo natural por suggestion de template.

**Alternatives considered**:
- View mode `lista_vertical` + row Content — rejeitado (mais YAML, displays, preprocess node; YAGNI).
- Display Content + teaser core — rejeitado (teaser inexistente; markup atual não usa entity view).

---

## R2 — Classes novas (não `.item-vaga--destaque`)

**Decision**: card com `.item-vaga.item-vaga--lista` e filhos `vaga-lista__*`.

**Rationale**: FR-016 / SC-006; CSS atual `:is(.css-vagas-home, .css-vagas-page) .item-vaga--destaque` é compartilhado — reutilizar o BEM laranja na listagem arrisca regressão na Home/PE ao estilizar o branco.

**Alternatives considered**: overrides sob `.css-vagas-page .item-vaga--destaque` — rejeitado (mesmo token `#FD761A` e estrutura incompatível com o Figma branco).

---

## R3 — Remoção do grid de 3 colunas

**Decision**:
1. `page_1` `style.options.row_class` → `col-12` (ou string vazia).
2. Unformatted Twig: remover `col-md-6 col-lg-4` (uma row = bloco full width da lista).
3. View Twig: trocar `.view-content.row.g-4` por container de lista (ex. `.vagas-lista` / stack vertical) com `max-width` ~560–720px centralizado.

**Rationale**: FR-007 / FR-010; hoje há **dupla** aplicação de cols (View `row_class` + unformatted Twig).

**Alternatives considered**: só CSS `flex-direction` mantendo cols — rejeitado (markup Bootstrap de grid permanece semanticamente errado).

---

## R4 — Field boolean novo em `node`

**Decision**: criar `field.storage.node.field_vaga_destaque` (type `boolean`, cardinality 1, default `0`) + instance no bundle `vagas` + checkbox no form display `default`.

**Rationale**: FR-001–003; inventário: nenhum boolean storage em `node` no `config/sync` (apenas `user.field_termo` / `user.field_possui_deficiencia` — **não** reutilizáveis entre entity types). Regra de reuso do projeto exige storage novo neste caso.

**Alternatives considered**:
- Campo em taxonomia/flag — rejeitado (FR exige no CT `vagas`).
- Reusar storage de user — impossível cross-entity.

---

## R5 — Ordenação Destaque + created

**Decision**: no display `page_1`, override de sorts: `field_vaga_destaque` DESC, depois `created` DESC; `defaults.sorts = FALSE`.

**Rationale**: FR-006 / US2; baseline herda só `created` DESC do default display.

**Alternatives considered**: sticky / weight editorial — rejeitado (fora do escopo; FR define boolean).

---

## R6 — 5 itens + pager full AJAX (sem infinite scroll)

**Decision**: `items_per_page: 5`; manter `pager.type: full` e `use_ajax: true`. **Não** adicionar `drupal/views_infinite_scroll`.

**Rationale**: FR-004 / FR-005 / FR-008; módulo ausente de `composer.json` e `modules/contrib`; Spec Assumptions e YAGNI.

**Alternatives considered**: instalar `views_infinite_scroll` para botão “Carregar mais” — rejeitado nesta entrega (dependência + aprovação produto); revisitável depois sem mudar o field/card.

---

## R7 — Logo via `user_picture`

**Decision**: no Twig, se `field_empresa_u.entity` tiver `user_picture` preenchido, renderizar imagem (~64×64, object-fit cover); senão omitir/placeholder discreto.

**Rationale**: FR-013; inventário confirma ER → User; único campo de imagem do perfil empresa no sync é `user_picture`; cards atuais só usam `field_nome_fantasia`.

**Alternatives considered**: criar `field_logo` em user — rejeitado (fora do escopo / YAGNI).

---

## R8 — Tags = Benefícios (fallback cursos)

**Decision**: pills de tag a partir de `field_text_simple_multiple_2` (Benefícios), máx. 3 + `+N`; se vazio, labels dos termos `field_cursos_t`; se ambos vazios, omitir seção.

**Rationale**: Assumptions da spec; Benefícios mapeia melhor ao “+N benefícios” do Figma; cursos já existem como multi ER.

**Alternatives considered**:
- Só `field_cursos_t` — possível, mas perde benefícios textuais.
- Nova taxonomia `tecnologias` — rejeitado (assumptions / YAGNI).
- `field_text_simple_multiple` (Requisitos) — rejeitado (semântica pior para “tags” do card).

---

## R9 — Carga horária = `field_horarios`

**Decision**: exibir label do valor de lista `field_horarios` quando preenchido; omitir seção se vazio.

**Rationale**: FR-013 / Assumptions; campo já no bundle; Twig atual de `page_1` não o usa.

---

## R10 — Data relativa via preprocess

**Decision**: em `default.theme` (preprocess do field/view `page_1`), calcular string “Publicada há X” com `date.formatter` → `formatTimeDiffSince($node->getCreatedTime())` e passar variável ao Twig.

**Rationale**: FR-014; Twig puro não tem interval formatter de primeira classe estável; preprocess é o padrão Drupal.

**Alternatives considered**: data absoluta `d/m/Y` — pior paridade Figma; lógica pesada no Twig — frágil.

---

## R11 — CTAs e links

**Decision**:
- Destaque: botão sólido escuro, rótulo “Candidatura Rápida”
- Padrão: botão outline, rótulo “Ver Detalhes”
- Ambos: `path('entity.node.canonical', {node: id})`

**Rationale**: FR-015 / US3; candidatura completa fora do escopo nesta entrega.

---

## R12 — CSS / library dedicada

**Decision**:
- Library `default/vagas_lista_vertical` → `vagas-lista-vertical.css`
- Attach só na renderização de `page_1`
- Tokens: fundo branco; badge `#58A83C`; borda destaque verde; container lista centralizado

**Rationale**: FR-011–016; SC-001 / isolamento.

**Alternatives considered**: regras novas dentro de `style.css` global sem library — aceitável legacy, mas library facilita escopo e cache; preferida.

---

## R13 — Limpeza de blocos fora de escopo

**Decision**: helper no hook procura placements com labels/copy “Recomendado para você” / “Melhore seu currículo” restritos a `/vagas` e desabilita/remove; se nenhum → mensagem no-op.

**Rationale**: FR-018–019; inventário 2026-10-04: **nenhum** placement correspondente em `config/sync`.

**Alternatives considered**: ignorar limpeza — rejeitado (FR-019 exige ensure).

---

## R14 — Hook `custom_configs_update_11045`

**Decision**: update idempotente que:
1. Ensure field storage/instance + form display checkbox
2. Ensure View `page_1` (5, AJAX, sorts, row_class lista)
3. Cleanup placements exclusos (no-op se ausentes)
4. Não toca hero `027`, `block_1`/`block_2`/`block_3`, formulário exposto desativado

**Rationale**: FR-020; próximo livre após `11044`; padrão Entity API 11040–11044.

---

## R15 — Ordem de deploy

**Decision**: origem `drush cex`; destino `git pull` → `cim -y` → `updb -y` → `cim -y` → `cr`.

**Rationale**: FR-022; 1ª `cim` traz storages/YAML; `updb` ensure; 2ª `cim` realinha View/displays se o hook ajustar config.

---

## R16 — PRD §3.1 / §3.6

**Decision**: atualização cirúrgica:
- §3.1 CT `vagas`: documentar `field_vaga_destaque`
- §3.6 View `page_1`: 5/página, lista vertical branca, sort destaque, hook `11045`; remover menção a grid 12 + cards laranja **nesta** rota
- Manter bullets Home `block_1` / PE `block_3` / Hero `027` intactos salvo referência cruzada mínima

**Rationale**: FR-023; Guardião do Escopo.
