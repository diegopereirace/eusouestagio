# Research: Refatorar UI — Nossa Metodologia

**Data**: 2026-11-01 | **Feature**: [spec.md](spec.md)

## R1 — Library CSS dedicada vs continuar em `style.css`

**Decision**: Criar `default/nossa_metodologia` → `assets/css/nossa-metodologia.css` e `attach_library` no Twig do bloco; remover o bloco legado de regras NM em `style.css` (~3245–3452).

**Rationale**: Padrão já usado por `nossos_diferenciais` (042), `diferenciais_quem_somos` (013) e outros; isola regressões; atende FR-015 (manutenibilidade).

**Alternatives considered**: Manter tudo em `style.css` (já inchado; risco de conflito com ND vizinho); SCSS parcial sem build (inútil sem compilador).

## R2 — Pipeline SCSS

**Decision**: CSS plano. Não introduzir Sass/build nesta feature.

**Rationale**: Zero `.scss` em `themes/custom/default`. Spec assume migração SCSS só “se o pipeline já comportar”; skill `mattpocock` não cobre SCSS fiel ao Figma.

**Alternatives considered**: Adotar `mindrally/skills@scss-best-practices` + Vite/Mix — fora de escopo e bloqueia deploy simples.

## R3 — Como aplicar max-width 770px / 1160px

**Decision**: Aplicar `max-width` + `margin-inline: auto` (ou `mx-auto`) nos wrappers `.nm-etapas` e `.nm-passos` (ou um wrapper interno se o field Drupal precisar), **somente** em `@media (min-width: 992px)`. Base Mobile First = largura 100% do `.container`.

**Rationale**: FR-005–009 / FR-013; SC-003. Hoje `.nm-etapas__item` e `.field__item` usam `flex: 1 1 0` + `max-width: none` e ocupam os 1280px do container — causa principal do gap vs Figma.

**Alternatives considered**: `max-width` no `.container` da seção (quebraria ritmo da Home); `transform: scale` (acessibilidade/zoom ruins); media query desktop-first com `max-width` base (contrário a FR-013).

## R4 — Tipografia e cor do título

**Decision**: Título `#000000` / 600 / 30px / lh 38 / ls −0.3px. Remover `var(--brand-navy)` e weight 700 atuais. Subtítulo permanece alinhado ao título; tipografia Poppins coerente (peso/cor atuais do subtítulo podem ser refinados sem mudar o alinhamento).

**Rationale**: FR-010/011/012; mesmo delta que 042 corrigiu em ND; Poppins 400–700 já no tema cobre 600.

**Alternatives considered**: Manter navy da marca no H2 (rejeitado pelo aceite visual); `clamp()` frouxo sem tokens Figma (piora SC-001/002).

## R5 — Alinhamento das setas verdes

**Decision**: Na linha desktop, flex com alinhamento que coloque o eixo das setas no centro vertical dos círculos (`.nm-passo__icon` 96×96). Preferir `align-items: center` no eixo dos ícones + seta posicionada nesse eixo; evitar âncora no topo do bloco (rótulo embaixo não deve “puxar” a seta). Em &lt;992px com wrap: ocultar setas horizontais. Em coluna mobile (≤767.98px): reorientar 90° abaixo do item (comportamento atual preservado).

**Rationale**: FR-007; User Story 2 cenário 3; CSS `::after` já é a fonte das setas (sem field CMS).

**Alternatives considered**: Markup `<svg>` entre paragraphs no Twig (mais frágil com wrappers Drupal); PNGs de seta no tema (peso + a11y); manter `top: 48px` fixo sem validar centro do círculo (risco residual se altura do ícone mudar).

## R6 — Utilitários Bootstrap vs CSS custom

**Decision**: Manter `text-center text-md-end` no header (já no Twig). Usar utilitários (`mx-auto`, `d-flex`, `justify-content-between`, `align-items-center`) quando expressarem o mesmo resultado; tokens tipográficos e max-widths estritas ficam no CSS da library (FR-015).

**Rationale**: Spec Assumptions + FR-015; evita aninhamento profundo tipo `.block-nossa-metodologia .nm-passos .field__items .field__item…`.

**Alternatives considered**: Reescrever 100% em utilitários Bootstrap (não cobre tipografia Figma nem setas SVG); tudo em CSS aninhado profundo (viola FR-015).

## R7 — Deploy sem `cim`/`updb`

**Decision**: Destino = `git pull` + `drush cr`. Receita longa do projeto (`cim`/`updb`) é no-op seguro para esta feature.

**Rationale**: Nada estrutural; FR-017/018; mesma entidade UUID Home + PE.

**Alternatives considered**: Hook/`cex` só por hábito — desnecessário e viola “zero YAML novo obrigatório”.

## R8 — Superfície Twig

**Decision**: Alteração mínima no Twig do bloco: `attach_library('default/nossa_metodologia')` + classes utilitárias leves se ajudarem (`mx-auto` nos wrappers). Paragraph `metodologia-passo-p` permanece salvo alteração de classe pontual.

**Rationale**: Conteúdo e markup `nm-*` já existem na 005; gap é CSS + library.

**Alternatives considered**: Reescrever DOM inteiro (risco de regressão editorial); mudar campos/image styles (fora de escopo).
