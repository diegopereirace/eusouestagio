# Research: Bloco CTA v1 — Quem Somos

**Data**: 2026-09-22 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cr`), **reuso de storages** e **isolamento CSS** sob `.block-cta-v1`.

---

## R1 — Block type dedicado `cta_v1`

**Decision**: criar tipo de bloco `cta_v1` (“CTA v1”), distinto do bloco `cto` da home.

**Rationale**: FR-001; Input do usuário; layout/tokens diferentes (card azul claro vs imagem full-bleed CTO); evita colisão de Twig/CSS e regressão na home (SC-009).

**Alternatives considered**:
- Reusar bundle `cto` com segundo placement — rejeitado (campos/imagem e template incompatíveis com Figma CTA v1).
- Paragraph no Node Quem Somos — rejeitado (spec exige Custom Block Type + placement em `content_full`).

---

## R2 — Reuso de storages de texto e link primário

**Decision**:
- Título: `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente).
- Subtítulo: `field_text_simple_long`.
- Botão primário: `field_link` (já usado em `cto`).

**Rationale**: FR-002 / FR-003; Assumptions; padrão `004`–`014`; proibição de storages paralelos de texto.

**Alternatives considered**: criar `field_text_simple_small` — viola reuso / YAGNI.

---

## R3 — Storage novo `field_link_2` (botão secundário)

**Decision**: criar `field.storage.block_content.field_link_2` (tipo `link`, cardinality **1**) + instance no bundle `cta_v1`.

**Rationale**: FR-003. O storage `field_link` já cobre o primário (cardinality 1). Segundo botão nomeado no form exige segundo storage; o projeto já usa o padrão `*_2` (`field_text_simple_2` no footer). Spec descarta explicitamente `field_link_secundario`.

**Alternatives considered**:
- `field_link` com cardinality 2 — rejeitado (impacta bundle `cto` e semântica “primário/secundário” no form).
- `field_link_secundario` — rejeitado (Fora / Assumptions).
- Link hardcoded no Twig — rejeitado (FR-004: gerenciável no painel).

---

## R4 — Posição na página (weight 12)

**Decision**: placement `default_ctav1quemsomos` em `content_full`, weight **`12`**, visibility `request_path` = `/quem-somos`, imediatamente após `default_impactnumbersquemsomos` (weight `11`). Ordem atual Quem Somos em `content_full`: Diferenciais (`10`) → Impact Numbers (`11`) → **CTA v1 (`12`)**.

**Rationale**: FR-015; Assumptions; Input do usuário.

**Alternatives considered**: weight `11` — colide com Impact Numbers; weight `0` — ordem ambígua com placements legados.

---

## R5 — Templates e naming (Barrio suggestions)

**Decision**:
- Twig: `themes/custom/default/templates/block/block--block-cta-v1.html.twig` (padrão `block--block-*` de diferenciais / CTO).
- Classe raiz: `.block-cta-v1`; card: `.cta-v1` (ou BEM `.block-cta-v1__card` se preferir consistência interna — manter ao menos `.block-cta-v1` como escopo CSS obrigatório).

**Rationale**: FR-007; consistency com 013.

**Alternatives considered**: suggestion só por ID de placement — frágil; reusar `block--block-cto.html.twig` — regressão visual / tokens errados.

---

## R6 — Layout Bootstrap + tokens Figma

**Decision**: markup com utilitários Bootstrap 5 para empilhamento responsivo dos botões (equivalente a `.d-grid.gap-2.d-md-flex.justify-content-md-center.gap-md-3`); espaçamento textos↔botões `24px` (`.gap-4` / CSS no escopo). Card: max-width `1200px`, centralizado, **não** full-bleed.

**Tokens CSS (escopo `.block-cta-v1`)**:

| Token | Valor |
|-------|--------|
| Fundo card | `#D3E4FE` |
| Border-radius | `32px` |
| Padding | `64px` (todos os lados) |
| Max-width | `1200px` |
| Gap textos↔botões | `24px` |
| Título | Poppins semibold/bold, `#0F172A` |
| Subtítulo | Poppins, `#45464D` |
| Primário | bg `#FD7B1A`, texto branco |
| Secundário | bg `#FFFFFF`, texto escuro, borda ausente/sutil |

**Rationale**: FR-008–014; alinhado ao tema Barrio; zero JS.

**Alternatives considered**: CSS Grid custom — YAGNI; Layout Builder — fora de escopo.

---

## R7 — Não reutilizar `.ui-btn--primary` global

**Decision**: estilos de botão **somente** sob `.block-cta-v1` (classes locais, ex. `.cta-v1__btn--primary` / `__btn--secondary`). Não aplicar `.ui-btn--primary` do tema.

**Rationale**: em `style.css`, `.ui-btn--primary` usa `#e55a24`; Figma CTA exige `#FD7B1A`. Reuso quebraria SC-001 ou forçaria mudança global (regressão CTO/banner — SC-009).

**Alternatives considered**: alterar cor global do `.ui-btn--primary` — rejeitado (escopo e convivência).

---

## R8 — CSS em library dedicada

**Decision**: library `default/cta_v1` → `assets/css/cta-v1.css`; attach no Twig do bloco. Todos os seletores sob `.block-cta-v1`.

**Rationale**: FR-007; espelha libraries isoladas 012/013/014.

**Alternatives considered**: estilos só em `style.css` — dificulta isolamento e remoção futura.

---

## R9 — Seed + UUID + placement

**Decision**:
- UUID fixo do `block_content`: `e6f7a8b9-c0d1-4e2f-9a3b-4c5d6e7f8091`.
- Placement config ID: `default_ctav1quemsomos` (UUID `f7a8b9c0-d1e2-4f3a-a0b5-192a3b4c5d6e`).
- Seed tipográfico conforme Assumptions da spec (título, subtítulo, dois links).
- Idempotência: UUID existe → não duplica; preencher campos **somente** se vazios; **não** sobrescrever editorial divergente.

**Rationale**: FR-015 / FR-016; padrão 013/CTO/metodologia.

**Alternatives considered**: seed sempre forçado — viola preservação editorial; `default_content` — dependência nova, YAGNI.

---

## R10 — Hook `custom_configs_update_11019`

**Decision**: update idempotente que:
1. Garante block type + field storage `field_link_2` + instances dos quatro campos + form/view displays (rede de segurança pós-`cim`).
2. Cria/seeda a instância com UUID fixo se ausente; preenche campos vazios.
3. Garante placement `default_ctav1quemsomos` (weight 12, `/quem-somos`).
4. **Não** altera banner, Sobre nós, Missão/Visão, Diferenciais, Impact in Numbers nem bloco `cto`.

**Rationale**: FR-015; próximo número livre após `11018` (verificado em `custom_configs.install`).

**Alternatives considered**: só config sem seed — página sem CTA no aceite; configuração manual no admin — proibida.

---

## R11 — Ordem de deploy

**Decision**: origem `drush cex`; destino `git pull` → `cim -y` → `updb -y` → `cr`. Atualizar menção do layout v2 Quem Somos (PRD / regra deploy) para incluir `11019`.

**Rationale**: FR-017; `estagio-fluxo-dev.mdc` / `drupal-deploy-configs.mdc`.

---

## R12 — Fallbacks Twig

**Decision**:
- Título vazio → omitir título.
- Subtítulo vazio → omitir subtítulo.
- Botão sem URL → omitir esse botão.
- Ambos botões ausentes → omitir área de botões (sem wrapper vazio).
- Título + subtítulo + botões todos vazios → **não** renderizar o card/bloco (faixa vazia proibida).

**Rationale**: Edge Cases + SC-008.

---

## R13 — PRD §3.6

**Decision**: atualização cirúrgica documentando `cta_v1`, quatro campos, placement weight 12, UUID, hook `11019`, library `cta_v1`, e convivência com Impact Numbers (11) / Diferenciais (10). Ampliar a linha de deploy do layout v2 para incluir `11019`.

**Rationale**: FR-018; Guardião do Escopo.

---

## R14 — Permissões de roles

**Decision**: na implementação, incluir permissões create/edit/delete do bundle `cta_v1` nas roles que já gerenciam block content (mesmo padrão de 013) e exportar diffs de `user.role.*.yml` via `drush cex`.

**Rationale**: US4 / SC-004; editor precisa gerenciar sem código.

**Alternatives considered**: deixar permissões só no hook — incompleto sem export; esquecer roles — editor bloqueado no aceite.
