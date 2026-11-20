# Research: Refatorar UI — Nossos Diferenciais

**Data**: 2026-11-01 | **Feature**: [spec.md](spec.md)

## R1 — Library CSS dedicada vs continuar em `style.css`

**Decision**: Criar `default/nossos_diferenciais` → `assets/css/nossos-diferenciais.css` e `attach_library` no Twig do bloco; remover o bloco legado de regras ND em `style.css`.

**Rationale**: Padrão já usado por `diferenciais_quem_somos`, banners e carrosséis; isola regressões; atende FR-016 (manutenibilidade).

**Alternatives considered**: Manter tudo em `style.css` (já inchado; risco de conflito); SCSS parcial sem build (inútil sem compilador).

## R2 — Pipeline SCSS

**Decision**: CSS plano. Não introduzir Sass/build nesta feature.

**Rationale**: Zero `.scss` em `themes/custom/default`; header “SASS Starterkit” é legado. Spec assume migração SCSS só “se o pipeline já comportar”.

**Alternatives considered**: Adotar `mindrally/skills@scss-best-practices` + Vite/Laravel Mix — fora de escopo e bloqueia deploy simples.

## R3 — Fonte das fatias secundárias da colagem

**Decision (revisada pós-implementação)**: A imagem CMS (`field_image`) **já contém** a colagem completa (âncora + fatias). Sem `.nd-media__slice` e sem pasta `assets/images/nossos-diferenciais/`. Servir via `image_style('wide')` + `width`/`height`.

**Rationale**: O asset editorial seedado já embute as fatias; markup separado duplicaria a arte e exigiria PNGs que não existem no repo. FR-001/013/018 atualizados.

**Alternatives considered**: PNGs de fatia no tema (rejeitado — duplicata visual); novos fields de imagem (rejeitado pela spec); `hook_update_N` + `public://` (desnecessário).

## R4 — Formas decorativas (círculo verde / ponto navy)

**Decision**: Pseudo-elementos ou elementos vazios absolutos no `.nd-media` (SVG inline ou `border-radius` + `background`/`border`), `pointer-events: none`, `aria-hidden`, `z-index` atrás das fotos.

**Rationale**: FR-014; evita binários extras; dois `::before`/`::after` no mesmo nó são insuficientes se houver >2 camadas — preferir filhos `.nd-media__deco` quando precisar de >2 formas + slices.

**Alternatives considered**: Só os 2 pseudos atuais (insuficiente para colagem Figma); PNGs decorativos (mais peso, menos flexível).

## R5 — Tipografia do cabeçalho

**Decision**: Título `#000` / 600 / 30px / lh 38 / ls −0.3px; subtítulo 400 / 14px / lh 20 / max-width ~663px. Poppins já no tema (weights 400–700 cobrem 600 e 400).

**Rationale**: FR-007/008/004; corrige navy + 700 + subtítulo 300 “errado” vs Figma.

**Alternatives considered**: Manter navy da marca no H2 (rejeitado pelo aceite visual); `clamp()` frouxo sem tokens Figma (piora checklist SC-001/002).

## R6 — Mobile First e overflow

**Decision**: Estilos base = coluna única + colagem CMS + decos; em `min-width: 992px` ativar `col-lg-7`/`col-lg-5` e o ring cinza; em `max-width: 575.98px` ocultar o ring; `overflow: hidden` na seção.

**Rationale**: FR-015/016; User Story 3; evita scroll horizontal e sangria dos decos.

**Alternatives considered**: Desktop-first (contrário ao pedido); `overflow-x: clip` só no eixo X (sangria vertical); fatias `display: none` no markup (não aplicável — fatias no asset CMS).

## R8 — Cores cicláveis com `display: contents`

**Decision**: Selecionar `.nd-list .field__item:nth-child(3n+k) .nd-item__title`, não `.nd-item:nth-child`.

**Rationale**: Wrappers do field usam `display: contents` para o flex; no DOM cada `.nd-item` é quase sempre `:first-child` do próprio `.field__item`, o que quebrava o ciclo verde/navy/laranja (FR-009).

**Alternatives considered**: Remover `display: contents` e estilizar gaps nos wrappers (mais frágil com markup Drupal); classes por delta no Twig (acoplamento desnecessário).

## R7 — Deploy sem `cim`/`updb`

**Decision**: Destino = `git pull` + `drush cr`. Receita longa do projeto (`cim`/`updb`) é no-op seguro para esta feature.

**Rationale**: Nada estrutural; FR-019.

**Alternatives considered**: Hook só para copiar PNGs a `public://` — desnecessário com assets no tema.
