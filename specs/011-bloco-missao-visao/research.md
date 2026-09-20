# Research: Bloco Missão e Visão

**Data**: 2026-09-16 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## R1 — Custom Block + Paragraphs vs. fields no node `quem_somos`

**Decision**: bloco `missao_visao` + paragraph `missao_visao_item_p`, placement em `content_full` só em `/quem-somos`.

**Rationale**: padrão das seções home (004–007); conteúdo editorial independente do node; seed/placement via Configuration Management + `hook_update_N`; não acopla Missão/Visão à 2ª seção do node (`*_2`).

**Alternatives considered**: campos extras no node `quem_somos` — rejeitado (mistura modelos; dificulta on/off e reuso); Layout Builder — fora de escopo.

## R2 — Storage `field_itens_lista` novo

**Decision**: criar `field.storage.block_content.field_itens_lista` (entity_reference_revisions → paragraph, cardinality **2**), instance só em `missao_visao` → `missao_visao_item_p`.

**Rationale**: FR-004; não há storage ERR reutilizável com cardinality 2 e propósito genérico. Irmãos (`field_diferenciais_lista` unlimited, `field_como_funciona_itens` card 7, etc.) estão acoplados a outros bundles/handlers — reutilizar quebraria targets ou cardinality.

**Alternatives considered**: reusar `field_diferenciais_lista` com handler restrito — rejeitado (storage shared + cardinality -1 permitiria >2 itens em outro contexto e conflitaria com handler_settings por instance, mas o storage continua unlimited); dois fields fixos `field_missao`/`field_visao` — rejeitado (pior UX de ordenação; foge do padrão paragraphs).

## R3 — Título canônico `field_text_simple` (não `field_text_simple_small`)

**Decision**: mapear pedido verbal `field_text_simple_small` → storage existente `paragraph.field_text_simple`.

**Rationale**: FR-002 / Assumptions; regra de reuso do projeto; mesmo padrão 004–007.

**Alternatives considered**: criar storage paralelo “small” — proibido pelas regras do repo.

## R4 — Fundo via CSS `background-image` + `::before`

**Decision**: Twig define estilo inline `background-image: url({{ file_url(uri) }})` no wrapper `.block-missao-visao`; CSS cuida de `cover`/`center`/`min-height: 380px`; overlay em `::before`.

**Rationale**: FR-009–011; overlay pseudo-elemento exige o fundo no mesmo box; `<img>` absoluto complica o overlay e o full-bleed.

**Alternatives considered**: `<img>` absoluto + overlay div — mais markup; Media background modules — YAGNI / contrib nova.

## R5 — Markup das colunas no Twig do bloco

**Decision**: o Twig do bloco itera `field_itens_lista` (via `#items` / entities carregadas) e renderiza cada coluna `col-md-6` com título + descrição; paragraph suggestion opcional só se o display field default atrapalhar o grid.

**Rationale**: controle fino de `border-end` no 1º item e de `loop.index`; evita wrappers `.field__item` do core que quebram o row Bootstrap.

**Alternatives considered**: `{{ content.field_itens_lista }}` puro — possível se paragraph Twig emitir a coluna; mais frágil com wrappers do field formatter.

## R6 — Suggestion Twig Barrio

**Decision**: arquivo `block--block-missao-visao.html.twig` (espelha `block--block-nossos-diferenciais.html.twig` etc.).

**Rationale**: convenção Barrio `block__block_{bundle}`; a formulação `block--block-content--missao-visao` da spec é equivalente conceitual — na implementação usar a suggestion que o tema já resolve para outros bundles.

**Alternatives considered**: preprocess forçando theme hook — YAGNI.

## R7 — Placement e weight

**Decision**: `default_missaovisao` em `content_full`, weight `0`, pages `/quem-somos` apenas.

**Rationale**: FR-016; região renderiza abaixo de `page.content` (Assumptions); nenhum outro bloco `content_full` compete nesse path hoje — weight `0` basta.

**Alternatives considered**: região `content` / `banner` — rejeitado (banner já tem 009; `content` misturaria com o node).

## R8 — Hook `custom_configs_update_11014`

**Decision**: update idempotente que:
1. Garante paragraph type + block type + storages/instances/displays (defensivo pós-`cim`).
2. Seeds bloco UUID `b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e` com 2 paragraphs (textos Assumptions) + imagem do asset **somente se** bloco/itens/imagem ausentes.
3. Não sobrescreve textos/imagem se o editor já alterou.
4. Garante placement/visibilidade `/quem-somos` se ainda ausente (preferência: YAML via `cex` + `cim` antes do `updb`).

**Rationale**: FR-017–019; padrão `11004`–`11013`.

**Alternatives considered**: só config sem seed — ambiente limpo fica sem conteúdo; criar tudo só no hook sem YAML — foge do Configuration Management.

## R9 — Library CSS dedicada

**Decision**: `missao_visao` em `default.libraries.yml` → `assets/css/block-missao-visao.css`; attach no Twig.

**Rationale**: padrão `banner_quem_somos` / `layout_sobre_nos`; evita poluir `style.css`; carrega só onde o bloco renderiza.

**Alternatives considered**: regras em `style.css` — rejeitado (arquivo grande; menor isolamento; SC-008).

## R10 — Overlay e tipografia

**Decision**: variável local `--mv-overlay` (ex.: `rgba(15, 23, 42, 0.72)` ou azul marca translúcido) sob `.block-missao-visao`; textos `.text-white`; Poppins herdada do tema; título Bold/SemiBold, corpo Regular.

**Rationale**: FR-011/013; não mutar tokens globais.

**Alternatives considered**: hardcode de cor em vários seletores — menos manutenível.

## R11 — PRD

**Decision**: atualização cirúrgica em §3.6 (novo bullet `missao_visao` + placement + `11014`); referência cruzada opcional em §3.1.0 Quem somos (“faixa Missão/Visão abaixo do node”).

**Rationale**: FR-020; Guardião do Escopo — mudança estrutural de block/paragraph/fields/placement.

## R12 — Convivência com a 2ª seção do node

**Decision**: não ocultar nem sincronizar `field_titulo_2` / `field_text_long_formatted_2` / `field_imagem_2`.

**Rationale**: fora de escopo (spec); ajuste editorial posterior se houver sobreposição semântica.

**Alternatives considered**: esvaziar `*_2` no hook — rejeitado (alteraria conteúdo editorial sem pedido).
