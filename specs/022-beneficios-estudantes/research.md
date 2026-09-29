# Research: Benefícios Para Estudantes

**Data**: 2026-09-29 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **reuso de storages**, **Clean URL `/para-estudantes`**, e **isolamento** frente ao hero `021`, à View `vagas` e ao bloco PE “Benefícios para Empresas”.

---

## R1 — Machine names novos (não reusar PE / legado)

**Decision**: block type `beneficios_estudantes`; paragraph `card_icon_text_p`. Não reutilizar `diferenciais_quem_somos` / `diferencial_simples_p` / `diferencial_item_p` / `icone_titulo_descricao` nem Twig/CSS da PE ou da home.

**Rationale**: FR-001 / FR-003; Input do usuário; PE “Benefícios” é placement de `diferenciais_quem_somos` com `diferencial_simples_p` (ícone + rótulo, sem descrição longa por card). Spec exige card com ícone + título + texto e machine name `_p` dedicado. Evita regressão visual e colisão editorial (SC-008).

**Alternatives considered**:
- Segundo placement da instância PE UUID `f6a7…` — rejeitado (campos/layout/copy diferentes; contaminaria PE se o editor editar “o” bloco).
- Reusar `icone_titulo_descricao` — rejeitado (machine name pedido ≠; usado em fields de user; templates/CSS distintos).
- Reusar `diferencial_item_p` (home 004) — rejeitado (acopla home; machine name ≠).

---

## R2 — Reuso de storages (canônicos do projeto)

**Decision**:
- Títulos curtos (bloco e card): `field_text_simple` (pedido verbal `field_text_simple_small` → canônico).
- Subtítulo do bloco / texto do card: `field_text_simple_long`.
- Ícone do card: `field_image` (paragraph).
- Lista: storage existente `field_itens_lista` (ERR → paragraph); **nova instance** no bundle `beneficios_estudantes` com handler só `card_icon_text_p`.

**Rationale**: FR-002 / FR-004; `estagio-fluxo-dev.mdc`; padrão 004–021.

**Alternatives considered**: criar `field_text_simple_small` ou `field_cards_lista` — viola reuso / YAGNI.

---

## R3 — Cardinality de `field_itens_lista`

**Decision**: **não alterar** o storage. Já está `cardinality: -1` (desde 013). Apenas criar instance no novo bundle.

**Rationale**: verificado em `config/sync/field.storage.block_content.field_itens_lista.yml`; seed de 4 itens e FR “mais de 4 renderizam todos” já cobertos.

**Alternatives considered**: cardinality fixa 4 — rejeitado (edge case “mais de 4” da spec).

---

## R4 — Layout Bootstrap (grid 1 / 2 / 4) + chrome de card

**Decision**: markup Twig com utilitários Bootstrap 5:
- wrapper conteúdo: `.container.py-5` (ou `py-lg-5`)
- título: `h2` centrado, Poppins, `#0F172A`, CSS `max-width: 404px` + `.mx-auto`
- subtítulo: parágrafo centrado, `max-width: 624px` (CSS e/ou `.col-lg-8.mx-auto`)
- grid: `.row.mt-5.justify-content-center` (+ gutter vertical entre linhas)
- card column: `.col-12.col-md-6.col-lg-3`
- card body: `.text-center.d-flex.flex-column.align-items-center.h-100` + `min-height: 310px`; borda suave / `.shadow-sm` sob escopo do bloco
- ícone: `.img-fluid` + `max-height`/`max-width` ≈ 64px

**Rationale**: FR-009–016; SC-001/002; alinhado ao tema Barrio; zero JS. Distinto do grid QS (`col-6/md-4/lg-3` sem chrome de card).

**Alternatives considered**: CSS Grid custom — YAGNI; Layout Builder — fora de escopo; copiar markup PE/QS sem chrome — rejeitado (Figma pede cards ~258×310).

---

## R5 — Templates e naming (Barrio suggestions)

**Decision**:
- Bloco: `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig` (padrão `block--block-*` como 013/004).
- Paragraph: `paragraph--card-icon-text-p.html.twig`.
- Classe raiz obrigatória: `block-beneficios-estudantes`.
- Classes BEM locais: ex. `be-header`, `be-header__title`, `be-header__subtitle`, `be-grid`, `be-card`, `be-card__icon`, `be-card__title`, `be-card__text`.

**Rationale**: FR-008; isolation vs. `.block-diferenciais-quem-somos` / `.block-nossos-diferenciais`.

**Alternatives considered**: suggestion só por ID de placement — frágil.

---

## R6 — CSS em library dedicada

**Decision**: library `default/beneficios_estudantes` → `assets/css/beneficios-estudantes.css`; attach no Twig do bloco. Todos os seletores sob `.block-beneficios-estudantes`.

**Rationale**: FR-008; SC-008; espelha 013/021 (library isolada).

**Alternatives considered**: estilos só em `style.css` — dificulta isolamento.

---

## R7 — Seed + UUID + placement + assets

**Decision**:
- UUID fixo do `block_content`: `a8b9c0d1-e2f3-4456-a789-0bcdef123456`.
- Placement config ID: `default_beneficiosestudantes` (região `content_full`, weight `0`, pages `/para-estudantes`).
- Seed cabeçalho (Assumptions da spec):
  - Título: “Encontre oportunidades que combinam com você.”
  - Subtítulo: “Nossa plataforma utiliza inteligência para conectar você às vagas e empresas que fazem sentido para o seu perfil e momento.”
- Seed 4 cards (títulos fixos + textos Assumptions): Vagas alinhadas | Modelos de trabalho | Vários segmentos | Filtros Inteligentes.
- Ícones: `modules/custom/custom_configs/assets/beneficios-estudantes/icon-1.png`…`icon-4.png` → `public://` no hook (file_directory típico `paragraph/card-icon-text/…`).
- Idempotência: UUID existe → não duplica; preenche lista/ícones **somente** se vazios/ausentes; **nunca** sobrescreve editorial.

**Rationale**: FR-017–019; padrão 013/019/021. Weight `0` porque hoje não há outro bloco exclusivo dessa rota em `content_full` (filtros estão em `highlighted`; hero em `banner`).

**Alternatives considered**: weight alto “depois da View” — View `vagas` `page_1` renderiza no conteúdo da página, não como block em `content_full`; o bloco em `content_full` tipicamente aparece na ordem de regiões do page template — validar no implement se a ordem visual hero → benefícios → listagem exige ajuste de weight/região; se a listagem vier acima, documentar e corrigir weight no implement (não bloqueia o plan).

---

## R8 — Hook `custom_configs_update_11035`

**Decision**: próximo livre após `11034` → `custom_configs_update_11035`. Ensure defensivo de tipos/fields/displays; seed + assets; ensure placement; mensagem Drush created/skipped; sem sobrescrever editorial; sem tocar PE/QS/home/021.

**Rationale**: FR-018; `drupal-deploy-configs.mdc`. Destino: `cim` → `updb` → `cim` → `cr`.

**Alternatives considered**: só `cex` sem hook — rejeitado (seed/placement UUID não vivem só no sync de forma confiável entre ambientes).

---

## R9 — Deploy, PRD e convivência

**Decision**:
- Estrutura via admin/API + `drush cex` → `config/sync`.
- Destino: `cim` → `updb` (`11035`) → `cim` → `cr`.
- PRD cirúrgico §3.6: block type `beneficios_estudantes`, paragraph `card_icon_text_p`, placement `content_full` `/para-estudantes`, hook `11035`; menção de convivência com hero `021` e View `vagas`.
- CSS só sob `.block-beneficios-estudantes`; amostragem regressão: hero estudantes, PE benefícios, home diferenciais.

**Rationale**: FR-020 / FR-021; SC-004 / SC-008.

**Alternatives considered**: só `updb` sem `cex` — rejeitado.
