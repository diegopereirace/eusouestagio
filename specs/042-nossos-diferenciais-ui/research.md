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

**Decision**: Assets estáticos versionados em `themes/custom/default/assets/images/nossos-diferenciais/`, referenciados no Twig via path do tema; `aria-hidden="true"`. Sem campos CMS; sem cópia para `public://`.

**Rationale**: FR-001/014/018; editor só troca a âncora (`field_image`). Assets de tema sobem no Git e aparecem após `drush cr` (agregação/cache).

**Alternatives considered**: Novos fields de imagem (rejeitado pela spec); `custom_configs/assets` + `hook_update_N` (necessário só quando seed editorial precisa de `File` entity — não é o caso); CSS `background` puro sem PNG (pode não bater o Figma se houver fotos reais nas fatias).

## R4 — Formas decorativas (círculo verde / ponto navy)

**Decision**: Pseudo-elementos ou elementos vazios absolutos no `.nd-media` (SVG inline ou `border-radius` + `background`/`border`), `pointer-events: none`, `aria-hidden`, `z-index` atrás das fotos.

**Rationale**: FR-014; evita binários extras; dois `::before`/`::after` no mesmo nó são insuficientes se houver >2 camadas — preferir filhos `.nd-media__deco` quando precisar de >2 formas + slices.

**Alternatives considered**: Só os 2 pseudos atuais (insuficiente para colagem Figma); PNGs decorativos (mais peso, menos flexível).

## R5 — Tipografia do cabeçalho

**Decision**: Título `#000` / 600 / 30px / lh 38 / ls −0.3px; subtítulo 400 / 14px / lh 20 / max-width ~663px. Poppins já no tema (weights 400–700 cobrem 600 e 400).

**Rationale**: FR-007/008/004; corrige navy + 700 + subtítulo 300 “errado” vs Figma.

**Alternatives considered**: Manter navy da marca no H2 (rejeitado pelo aceite visual); `clamp()` frouxo sem tokens Figma (piora checklist SC-001/002).

## R6 — Mobile First e ocultação de fatias

**Decision**: Estilos base = coluna única + colagem simplificada (âncora + decos mínimas); em `min-width: 992px` ativar grid duas colunas e fatias; abaixo de breakpoint estreito (`max-width: 575.98px` ou similar) `display: none` nas fatias.

**Rationale**: FR-015/016; User Story 3; evita scroll horizontal.

**Alternatives considered**: Desktop-first com overrides mobile (contrário ao pedido); `transform: scale()` global na seção (pode desfocar texto).

## R7 — Deploy sem `cim`/`updb`

**Decision**: Destino = `git pull` + `drush cr`. Receita longa do projeto (`cim`/`updb`) é no-op seguro para esta feature.

**Rationale**: Nada estrutural; FR-019.

**Alternatives considered**: Hook só para copiar PNGs a `public://` — desnecessário com assets no tema.
