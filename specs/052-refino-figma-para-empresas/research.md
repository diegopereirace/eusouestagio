# Research: Refino Visual "Para Empresas" — Conformidade com Figma

**Data**: 2026-11-03 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **fidelidade ao Figma**, **paridade perceptível com a home no carrossel**, **CTAs condicionais por role** e **deploy repetível** (`cim` → `updb` → `cim` → `cr`), sem regressão das features `019` / `020` / `042` / `047` e sem alterar o visual da home.

---

## R1 — Paridade do carrossel PE com a home (sem absorver o CSS da home)

**Decision**: Manter a library CSS dedicada `banner_para_empresas` (layout full-bleed + CTAs overlay). Reutilizar o **comportamento** da home via:

1. Classe utilitária `banner-carousel` no elemento `.carousel` de PE (além das classes PE existentes), para o `Drupal.behaviors.bannerCarousel` em `assets/js/banner-carousel.js` inicializar Bootstrap + `prefers-reduced-motion`.
2. Dependency `default/banner_carousel` (ou ao menos o JS + Bootstrap) em `banner_para_empresas` em `default.libraries.yml`.
3. Atributos Bootstrap alinhados à home: `data-bs-pause="hover"`; intervalo pode permanecer `6000` (PE) vs `7000` (home) — diferença irrelevante para SC-001.
4. **Não** anexar `banner-carousel.css` ao PE (tokens/layout da home quebrariam o overlay de CTAs).

**Rationale**: FR-001 / SC-001 / SC-008. Home e PE já usam Bootstrap Carousel; o gap real é a ausência do behavior JS e dos atributos de pause/reduced-motion no PE. Reusar o CSS da home violaria o layout duas-colunas/overlay de PE.

**Alternatives considered**:
- Copiar `banner-carousel.js` para um `banner-para-empresas.js` — rejeitado (duplicação).
- Unificar Twig home+PE — rejeitado (markup/CTAs distintos; risco de regressão na home).
- Só CSS “parecer igual” sem JS — rejeitado (falha reduced-motion / BigPipe init).

---

## R2 — Breakpoint dos CTAs do banner: 768px (não 992px)

**Decision**: Em `banner-para-empresas.css`, mover o bloco que tira CTAs do overlay / empilha controles de `@media (max-width: 991.98px)` para `@media (max-width: 767.98px)`. Em `<768px`:

- Container `.banner-para-empresas__ctas` com `display: flex`, `gap` uniforme, `width: 100%`.
- Filhos `> a` com `flex: 1 1 0` (ou `width` igual) para larguras iguais (±2px).
- Se o conteúdo tipográfico for longo demais em viewports muito estreitos, permitir `flex-wrap: wrap` com cada botão `flex: 1 1 100%` (empilhados, largura plena igual).

Em `≥768px` preservar posição overlay atual.

**Rationale**: FR-002 / FR-003 / SC-002. Spec e restante do site usam 768px; o corte em 992px é legado inconsistente.

**Alternatives considered**: Manter 992px — rejeitado (viola pedido/Figma). Container query — overkill sem ganho.

---

## R3 — Seletores `body.node--type-para-empresas` → path atual

**Decision**: Substituir `body.node--type-para-empresas` por `body.path-para-empresas` (classe de path do Drupal para alias `/para-empresas`) **ou** confiar só em `.banner-para-empresas-wrapper` / `.block-banner-para-empresas` onde já isolam o escopo. Preferência: `body.path-para-empresas` nas poucas regras de região full-width do banner; resto permanece sob o wrapper.

**Rationale**: FR-005. Após `11028` a página é bundle `page`, não `para_empresas` — o seletor legado nunca casa.

**Alternatives considered**: `body.page-node-type-page` — rejeitado (atinge todas as páginas básicas). Hardcode no Twig de região — fora de escopo.

---

## R4 — Arte mobile / anti-placeholder

**Decision**: Não inventar Image Style nem asset mobile nesta feature se o editorial não entregar `field_imagem_mobile`. Manter fallback atual (mobile = desktop URI). Garantir que o Twig **nunca** injeta placeholder genérico `500×600`; slides sem `field_imagem_desktop` já são omitidos no preprocess. Se assets mobile dedicados forem versionados em `modules/custom/custom_configs/assets/banner-para-empresas/`, o hook `11076` pode copiá-los fill-if-empty — **opcional**, só se arquivos existirem na implementação.

**Rationale**: FR-004. Seed atual já usa PNGs reais; o risco de placeholder vem de conteúdo/CMS vazio, não do Twig.

**Alternatives considered**: Forçar Image Style `banner_carousel_mobile` — rejeitado (preprocess PE usa URI crua de propósito para alpha PNG, igual estudantes).

---

## R5 — Nossos Diferenciais: `#4E4E4E` + mobile sem decos absolutos

**Decision**: Em `nossos-diferenciais.css`:

- `.nd-header__subtitle` e `.nd-item__desc`: `#4a5d73` → `#4E4E4E`.
- Títulos cicláveis (verde/navy/laranja) **inalterados**.
- Em `@media (max-width: 767.98px)`: ocultar `.nd-media__deco-circle`, `.nd-media__deco-dot`, `.nd-media__deco-ring` (`display: none`) e reduzir/zerar padding especial do wrapper de mídia para a imagem fluir acima da lista sem overflow.
- Desktop (`≥768px` / layout em linha em `≥992px`): manter anel verde atrás (`z-index: 0`) e dot navy na frente (`z-index: 2`); alinhar topo mídia↔lista via grid/flex já existente (ajuste fino de `align-items: start` se medição Figma exigir ±4px).

**Rationale**: FR-006–009 / SC-003 / SC-004. Feature `042` já entregou camadas; lacuna real é cor + simplificação mobile.

**Alternatives considered**: Mudar só itens e não o subtítulo — rejeitado (Assumption da spec). Refatorar markup de decos — desnecessário.

---

## R6 — Depoimentos: bullets ativos (cor + sync)

**Decision**:

1. CSS: `.depoimentos-empresas__dot.is-active` passa de `var(--brand-navy)` para `var(--brand-orange)` (destaque Figma desta seção); inativos permanecem `#c5cbd3`.
2. JS `carousel-center.js`: manter contrato `data-carousel-dot` / `data-index` / `syncDots`; na implementação, validar clique N→slide N e sync pós-swipe. Correção de bug só se QA reproduzir dessincronia (ex.: `pointer-events`, `jumping` flag, ou `data-index` ausente) — sem reescrever o motor.
3. Não introduzir autoplay.

**Rationale**: FR-010–012 / SC-005. Wiring já existe (020); gap perceptível = cor do ativo + eventual bug de UX.

**Alternatives considered**: Trocar motor por Bootstrap Carousel — rejeitado (perde center-mode/peeks). Dots só CSS sem JS — já falhou no relato.

---

## R7 — CTA PE: override Twig + role `empresa` (servidor)

**Decision**: Criar suggestion `block--default-ctav1paraempresas.html.twig` (padrão de `block--default-ctav1paraestudantes.html.twig` / `block--default-ctav1vagas.html.twig`). Preprocess `default_preprocess_block__default_ctav1paraempresas` expõe `is_empresa` (mesmo critério do topo: autenticado + role `empresa`). Template:

| Perfil | Botão 1 | Botão 2 |
|--------|---------|---------|
| Anônimo / autenticado sem `empresa` | `<a>` cadastro (`field_link`) | **não renderiza** markup |
| Role `empresa` | `<button disabled>` (ou `<span role="button" aria-disabled>`) “Empresa Cadastrada”, classe fosca | `<a>` “Abrir Vaga” se `field_link_2` tiver URI |

CSS do estado desabilitado sob o override PE (não alterar `.block-cta-v1` de Quem Somos). Imagens do CTA: N/A hoje (bloco sem mídia); se no futuro houver `<img>`, `loading="lazy"`.

**Rationale**: FR-013–017 / SC-006. Ocultação só CSS vazaria markup/URL privilegiada. Template genérico `block--block-cta-v1.html.twig` serve QS e não deve ganhar lógica B2B.

**Alternatives considered**: Lógica no template genérico com `if` por `configuration.id` — rejeitado (acopla QS). Visibility de bloco Drupal por role — rejeitado (precisamos dos dois botões com estados diferentes no mesmo bloco).

---

## R8 — Hook `custom_configs_update_11076` (legado CTA)

**Decision**: Hook único idempotente que:

1. Carrega bloco UUID `a7b8c9d0-e1f2-4345-a678-90abcdef0123` (`default_ctav1paraempresas`).
2. Se `field_link_2.title` ∈ {`Falar com Especialista`, vazio com URI legada conhecida} **ou** URI ≠ `internal:/painel/empresa/vagas/nova` **somente** quando o título ainda é o legado conhecido → seta `title: Abrir Vaga`, `uri: internal:/painel/empresa/vagas/nova`.
3. Não sobrescreve título/URI se o editorial já divergiu de forma intencional (qualquer título ≠ legado e ≠ vazio).
4. Opcional: ensure fill-if-empty de assets mobile do banner **se** arquivos versionados existirem.

Último hook no repo: **`11075`** → próximo **`11076`**.

**Rationale**: FR-018–019 / SC-007. Origem já tem “Abrir Vaga”; destino pode ter legado.

**Alternatives considered**: Só documentar correção manual — rejeitado (`drupal-deploy-configs`). Sempre overwrite — rejeitado (quebra editorial).

---

## R9 — Deploy / cex / PRD

**Decision**: Sem novos content/block/paragraph types. `drush cex` na origem **somente se** displays/placements/visibility forem tocados (esperado: zero YAML estrutural, ou só se preprocess exigir config). Destino: `cim` → `updb` (`11076`) → `cim` → `cr`. PRD §3.1.0b / §3.6 cirúrgico: CTAs do `cta_v1` PE condicionados à role `empresa`; refinamentos banner/ND/depoimentos; hook `11076`.

**Rationale**: FR-020–022.

**Alternatives considered**: Skip PRD — rejeitado (spec + guardian para mudança de comportamento estrutural de CTAs).

---

## R10 — `mattpocock/skills`

**Decision**: Pacote **não instalado** (igual `047`). Plano e implementação usam convenções do repo + skills Speckit locais. Se o usuário instalar depois, pode reaplicar em `/speckit-implement`.

**Rationale**: Assumption da spec; evita bloquear o plano.

---

## Resolução de clarificações

Nenhum `[NEEDS CLARIFICATION]` na spec/checklist. Rota canônica de nova vaga confirmada no seed e no PRD: `/painel/empresa/vagas/nova`.
