# Research: Diferenciais Quem Somos

**Data**: 2026-09-20 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cr`) e **isolamento** do bloco da home (`nossos_diferenciais`).

---

## R1 — Machine names distintos da home (004)

**Decision**: block type `diferenciais_quem_somos`; paragraph `diferencial_simples_p`. Não reutilizar `nossos_diferenciais` / `diferencial_item_p` nem os Twig/CSS da home.

**Rationale**: FR-003; Input do usuário; evita colisão de config, templates e regressão visual na home (SC-004 / SC-008).

**Alternatives considered**:
- Reusar `nossos_diferenciais` com segundo placement — rejeitado (layout e campos diferentes: home tem imagem + descrição por item).
- Extender `diferencial_item_p` (ícone + título + descrição) — rejeitado (item Quem Somos é só ícone + rótulo; machine name `_p` novo exigido).

---

## R2 — Reuso de storages de texto/imagem

**Decision**:
- Rótulos / título: `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente).
- Descrição do bloco: `field_text_simple_long`.
- Ícone do item: `field_image` (paragraph).
- Lista: storage existente `field_itens_lista` (ERR → paragraph).

**Rationale**: FR-002 / FR-004; padrão features `004`–`012`; proibição de storages paralelos.

**Alternatives considered**: criar `field_text_simple_small` ou `field_diferenciais_qs_lista` — viola reuso / YAGNI.

---

## R3 — Cardinality de `field_itens_lista` (2 → -1)

**Decision**: alterar `field.storage.block_content.field_itens_lista` de `cardinality: 2` para `-1` (ilimitada) e exportar via `drush cex`. Nova field instance no bundle `diferenciais_quem_somos` aponta handler só para `diferencial_simples_p`.

**Rationale**: no Drupal, cardinality é propriedade do **storage**, não da instance. Spec exige lista ilimitada **e** reuso do storage. Bundle `missao_visao` (011) já tem placement desabilitado (012); cardinality maior não quebra a rota pública.

**Alternatives considered**:
- Storage novo só para 013 — rejeitado (FR-004 / reuso).
- Manter cardinality 2 — rejeitado (impede seed de 8 itens e FR-004).
- Cardinality fixa 8 — rejeitado (spec: “mais de 8” deve renderizar todos).

---

## R4 — Layout Bootstrap grid (sem Layout Builder)

**Decision**: markup Twig com utilitários Bootstrap 5 equivalentes a:
- wrapper: `.container.py-5`
- título: `h2` centralizado Poppins Bold/SemiBold
- descrição: `.col-lg-8.mx-auto` centralizado
- grid: `.row.mt-5.justify-content-center`
- item: `.col-6.col-md-4.col-lg-3.mb-4` + flex column centrado

**Rationale**: FR-009–014; alinhado ao tema Barrio; zero JS.

**Alternatives considered**: CSS Grid custom — YAGNI; Layout Builder — fora de escopo.

---

## R5 — Templates e naming (Barrio suggestions)

**Decision**:
- Bloco: `themes/custom/default/templates/block/block--block-diferenciais-quem-somos.html.twig` (padrão `block--block-*` já usado em missao-visao / nossos-diferenciais).
- Paragraph: `paragraph--diferencial-simples-p.html.twig`.
- Classe raiz obrigatória: `block-diferenciais-quem-somos` (não reutilizar `.block-nossos-diferenciais`).

**Rationale**: FR-008; isolation visual da home; consistency com 011/004.

**Alternatives considered**: suggestion só por ID de placement — frágil se o ID mudar.

---

## R6 — CSS em library dedicada

**Decision**: library `default/diferenciais_quem_somos` → `assets/css/diferenciais-quem-somos.css`; attach no Twig do bloco. Ícones `max-width: 64px`; rótulos Poppins SemiBold (600). Todos os seletores sob `.block-diferenciais-quem-somos`.

**Rationale**: FR-008 / FR-015 / FR-016; espelha padrão 012 (library isolada) em vez de inchamento de `style.css` (004).

**Alternatives considered**: estilos só em `style.css` — funciona, mas dificulta isolamento e remoção futura.

---

## R7 — Seed + UUID + placement

**Decision**:
- UUID fixo do `block_content`: `d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80`.
- Placement config ID: `default_diferenciaisquemsomos` (região `content_full`, weight `10`, pages `/quem-somos`).
- Seed: título/descrição placeholder institucionais + **8** paragraphs (rótulos Assumptions) + ícones genéricos em `modules/custom/custom_configs/assets/diferenciais-quem-somos/` copiados para `public://` só se campos vazios.
- Idempotência: UUID existe → não duplica; não sobrescreve editorial divergente.

**Rationale**: FR-017–019; padrão CTO / metodologia / missao_visao; weight `10` abaixo do node em `page.content` e após placements desabilitados weight `0`.

**Alternatives considered**: weight `0` — ok, mas `10` deixa margem para faixas futuras acima; seed sem ícones (só placeholder) — possível, mas spec pede assets versionados quando necessário.

---

## R8 — Hook `custom_configs_update_11016`

**Decision**: update idempotente que:
1. Garante paragraph type + block type + field instances + form/view displays (rede de segurança pós-`cim`).
2. Eleva cardinality de `field_itens_lista` se ainda `2` (defensivo).
3. Cria/seeda bloco UUID fixo + 8 itens se ausentes.
4. Garante placement `default_diferenciaisquemsomos` + visibility `/quem-somos`.
5. **Não** altera `nossos_diferenciais`, banner, Sobre nós, Missão/Visão Node.

**Rationale**: FR-018; próximo número livre após `11015`.

**Alternatives considered**: só config sem seed — página institucional vazia no aceite; `default_content` — dependência nova, YAGNI.

---

## R9 — Ordem de deploy e regra Cursor

**Decision**: origem `drush cex`; destino `git pull` → `cim -y` → `updb -y` → `cr`. Regra `.cursor/rules/drupal-deploy-configs.mdc` já entregue na specify — **não** recriar; apenas referenciar no quickstart/PRD se necessário.

**Rationale**: FR-020 / FR-021; `estagio-fluxo-dev.mdc`.

---

## R10 — PRD §3.6

**Decision**: atualização cirúrgica em §3.6 documentando `diferenciais_quem_somos`, fields, paragraph, placement, UUID, `11016`, e convivência com home `nossos_diferenciais` (inalterado). Opcional: menção breve em §3.1.0 de que a página também exibe o bloco em `content_full`.

**Rationale**: FR-022; Guardião do Escopo.

---

## R11 — Fallbacks Twig

**Decision**: omitir H2/descrição/ícone/rótulo vazios; zero itens → só cabeçalho (se houver); sem erro Twig; não truncar lista >8.

**Rationale**: Edge Cases + SC-007.

---

## R12 — Permissões de role

**Decision**: após criar o bundle, incluir create/edit/delete de `diferenciais_quem_somos` nas roles que já gerenciam block content; exportar no mesmo PR.

**Rationale**: sem isso o `cim` sobe a estrutura mas o editor não edita no destino (padrão 004 R9).
