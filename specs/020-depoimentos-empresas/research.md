# Research: Bloco de Depoimentos — Para Empresas

**Data**: 2026-09-26 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **reuso de field storages** em `node`, **Clean URL `/para-empresas`**, e **isolamento CSS/JS** sob wrappers da seção.

---

## R1 — Content type `depoimento` + reuso de storages

**Decision**: criar bundle `depoimento` (label “Depoimento”). Campos:

| Propósito | Storage reutilizado | Label da instance |
|-----------|---------------------|-------------------|
| Nome do autor | `title` (nativo) | Título → “Nome do autor” no form display (ou help text) |
| Cargo / empresa | `field_text_simple` | Cargo / Empresa |
| Texto do depoimento | `field_text_simple_long` | Texto do depoimento |
| Foto do autor | `field_imagem` | Foto do autor |

**Zero** field storages novos. Form/view displays do bundle cobrem os quatro campos. View mode público do card: `teaser` (ou row fields na View) — preferir display **Teaser** com os campos necessários para o Twig do card.

**Rationale**: FR-001–004; regra de reuso (`estagio-fluxo-dev.mdc`); storages já existem em `config/sync` (`string` / `string_long` / `image`).

**Alternatives considered**:
- Criar `field_cargo_empresa` / `field_texto_depoimento` / `field_image` — rejeitado (storages paralelos proibidos).
- Paragraph type para depoimento — rejeitado (editor espera content type listável; FR-001).

---

## R2 — View `depoimentos_carousel` + placement / weights

**Decision**:
- View `depoimentos_carousel`, display Block `block_depoimentos_empresas`.
- Filtros: `status=1`, `type=depoimento`; sort `created` DESC; pager `none` (lista completa no carrossel; volume editorial esperado baixo).
- Placement: plugin `views_block:depoimentos_carousel-block_depoimentos_empresas`, região `content_full`, tema `default`, pages só `/para-empresas`, `label_display: '0'`.
- **Weights**: Benefícios permanece **3**; Depoimentos **4**; CTA `default_ctav1paraempresas` sobe de **4 → 5** (ajuste idempotente no hook + `cex`).

**Rationale**: FR-005–009; ordem Benefícios → Depoimentos → CTA (SC-002); weights Drupal são inteiros.

**Alternatives considered**:
- Manter CTA em 4 e usar weight 3.5 — impossível.
- Inserir Depoimentos com weight 3 e Benefícios em 2 — muda ordem relativa das seções anteriores (risco desnecessário).

---

## R3 — Biblioteca do carrossel (center mode)

**Decision (atualizada 2026-09-26)**:
- **Banners** (home / PE): mantêm **Bootstrap Carousel** (já carregado pelo tema Barrio) — full-bleed, 1 slide, autoplay/setas. Não migrar para Swiper.
- **Depoimentos** (e futuros center-mode): motor próprio `default/carousel_center` (`assets/js/carousel-center.js`) — center + peeks + dots + **loop infinito** (clones) + autoplay off. Skin CSS permanece em `.depoimentos-empresas`.
- **Não** introduzir Swiper/Splide: Bootstrap já cobre heroes; center+loop cabe em JS leve (~3KB) anexado só na página que usa a library; zero Composer/npm.

**Rationale**: dois contratos de UX distintos; unificar tudo em Swiper reescreveria banners estáveis e adicionaria peso sem ganho nos heroes. Centralização inteligente = Bootstrap para full-bleed + `carousel_center` para multi-item.

**Alternatives considered**:
- Swiper vendored — só se o motor próprio falhar no aceite visual/infinito.
- Um único lib para todos — rejeitado (YAGNI + regressão nos banners).
- Bootstrap para depoimentos — rejeitado (sem center mode / peeks).

---

## R4 — Visual do card (Figma) + avatar

**Decision**:
- Wrapper `.depoimentos-empresas` / `.depoimento-card`; fundo `#FFFFFF`, `border-radius: 16px`, `padding: 32px`, largura alvo ~404px no desktop, `min-height` ~188px.
- Cabeçalho flex: avatar circular ~48×48 (`border-radius: 50%`, `object-fit: cover`) + nome (title) + cargo (`field_text_simple`).
- Corpo: `field_text_simple_long` em ~`#45464D` / tokens do tema; tipografia Poppins já do tema.
- Imagem: style existente **`thumbnail`** (100×100) + CSS para 48px — **não** criar image style novo na v1 (YAGNI).
- Sem foto: omitir `<img>` (sem broken image).

**Rationale**: FR-012–014; SC-001; reuso de `thumbnail` já no sync.

**Alternatives considered**: image style `depoimento_avatar` 48×48 — só se thumbnail+CSS falhar em nitidez/perf.

---

## R5 — Seed (≥4) + assets + hook `11032`

**Decision**:
- Hook `custom_configs_update_11032` idempotente: ensure tipo/fields/displays (defensivo se `cim` ainda não rodou); seed ≥4 nodes `depoimento` com UUIDs fixos; copiar avatares de `modules/custom/custom_configs/assets/depoimentos/` → `public://`; ensure placement + weight do bloco da View; ajustar weight do CTA PE para 5; **não** sobrescrever editorial divergente; **não** duplicar.
- Seeds exemplo (pt-BR, alinhados ao Figma + dois coerentes):

| # | Título (autor) | Cargo / Empresa | Nota |
|---|----------------|-----------------|------|
| 1 | Mariana Silva | Head de Talentos - TechCorp | Figma |
| 2 | Ricardo Gomes | CEO - Inovatech | Figma |
| 3 | Ana Paula Costa | Gerente de RH - Horizonte Soft | inventado coerente |
| 4 | Lucas Ferreira | Diretor de Operações - Nexo Digital | inventado coerente |

- Avatares: placeholders geométricos/ilustrativos versionados (sem fotos de pessoas reais sem direito).
- Ausência de asset PNG: criar nó sem imagem (degradação documentada); não falhar o update.

**Rationale**: FR-017–018; último hook verificado = `11031` → próximo **`11032`**.

**Alternatives considered**: seed só 2 itens do Figma — rejeitado (spec ≥4 para center mode demonstrável).

---

## R6 — Deploy, PRD e isolamento

**Decision**:
- Estrutura via admin/API + `drush cex` → `config/sync` (node type, field instances, displays, View, block placement, CTA weight).
- Destino: `cim` → `updb` (`11032`) → `cim` → `cr`.
- PRD cirúrgico: §3.1 (novo tipo), §3.1.0b / §3.6 (composição + View), hook `11032`.
- CSS/JS só sob `.depoimentos-empresas`; home, Quem Somos, Contato, banner/CTA PE inalterados visualmente.

**Rationale**: FR-015, FR-019–021; `drupal-deploy-configs.mdc`.

**Alternatives considered**: só `updb` sem `cex` — rejeitado (estrutura precisa estar no sync).
