# Research: Números / Estatísticas Quem Somos (Impact in Numbers)

**Data**: 2026-09-21 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cr`), **reuso de storages de texto** e **isolamento CSS** sob `.section-impact-numbers`.

---

## R1 — Paragraph `numero_destaque_p`

**Decision**: criar paragraph type `numero_destaque_p` (“Número Destaque”) com dois campos de texto: destaque + subtexto. Sufixo `_p` obrigatório (padrão do projeto).

**Rationale**: FR-001; Input do usuário; item atômico reutilizável e editável no Node.

**Alternatives considered**:
- Campos plain no Node (4× destaque + 4× subtexto) — rejeitado (má UX, sem ordem flexível, viola modelo paragraph das features recentes).
- Reusar `diferencial_simples_p` — rejeitado (tem imagem; machine name e semântica diferentes).

---

## R2 — Reuso de storages de texto no paragraph

**Decision**:
- Destaque: `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente).
- Subtexto: `field_text_simple_long` (alternativa canônica a `field_text_simple_small_2`; **não** criar storage paralelo).

**Rationale**: FR-002; Assumptions da spec; padrão `004`–`013`; `field_text_simple_long` já existe em `paragraph` e comporta rótulos curtos em caixa alta.

**Alternatives considered**: criar `field_text_simple_small` / `field_text_simple_small_2` — viola reuso / YAGNI.

---

## R3 — Lista dedicada `field_numeros_lista` (cardinality 4)

**Decision**: criar **novo** field storage `node.field_numeros_lista` (entity_reference_revisions → paragraph, cardinality **4**) + instance no bundle `quem_somos` com handler limitado a `numero_destaque_p`.

**Rationale**: FR-003. O storage existente `field_itens_p` tem cardinality **3** e instance em `para_empresas`. No Drupal, cardinality vive no **storage**; alterar para 4 afetaria outro content type; cardinality `-1` afrouxaria o limite de 4 itens.

**Alternatives considered**:
- Reusar `field_itens_p` elevando card para 4 — rejeitado (especificado Fora; risco em `para_empresas`).
- Reusar `field_itens_lista` (block_content) — rejeitado (entity type errado).
- Cardinality `-1` com validação Twig — rejeitado (FR-003 exige exatamente 4 no campo).

---

## R4 — Posição na página e convivência com 013

**Decision**: renderizar Impact in Numbers **no Twig do Node**, após a seção Missão/Visão e ainda dentro de `node__content`. O bloco Diferenciais Quem Somos (013) permanece em `content_full` e continua aparecendo **depois** do Node na página.

**Rationale**: Assumptions da spec; conteúdo editorial no Node; não compete com placement do bloco 013.

**Alternatives considered**: bloco custom em `content_full` — fora de escopo (FR-004 / Input: gerenciável no Node).

---

## R5 — Full-bleed da faixa escura

**Decision**: CSS breakout sob `.section-impact-numbers` (padrão 012 / Missão-Visão): escapar `#main.container` com técnica `100vw` / margens negativas (ou equivalente já usada em `quem-somos-missao-visao.css`), mantendo conteúdo interno max-width `1280px` centralizado.

**Rationale**: FR-008 / FR-009; fundo `#0F172A` edge-to-edge; node não vive em região full-width.

**Alternatives considered**: padding só no container interno sem full-bleed — rejeitado (não bate Figma); mover para região `content_full` — R4.

---

## R6 — Layout Bootstrap grid

**Decision**: markup Twig equivalente a:
- seção: `.section-impact-numbers` + paddings custom `64px` / `40px`
- inner: max-width `1280px` centralizado
- grid: `.row` > `.col-6.col-md-3`
- item: `.d-flex.flex-column.align-items-center` + gap `8px` (utilitário `.gap-2` = 0.5rem ≈ 8px **ou** CSS `gap: 8px` no escopo)

**Rationale**: FR-010 / FR-011; alinhado ao tema Barrio; zero JS.

**Alternatives considered**: CSS Grid custom — YAGNI; Layout Builder — fora de escopo.

---

## R7 — Templates e naming

**Decision**:
- Node: atualizar `node--quem-somos.html.twig` (arquivo ativo; suggestion `--full` só se o tema passar a exigir).
- Paragraph: `paragraph--numero-destaque-p.html.twig`.
- Classe raiz obrigatória: `section-impact-numbers`.

**Rationale**: FR-007; Assumptions template; consistency com 010/012.

**Alternatives considered**: template de field separado — overhead desnecessário; markup só via `{{ content.field_numeros_lista }}` sem override — perde controle de tokens/grid.

---

## R8 — CSS em library dedicada

**Decision**: library `default/impact_numbers` → `assets/css/impact-numbers.css`; attach no Twig do node quando a seção for renderizada (ou sempre no template Quem Somos, custo mínimo). Todos os seletores sob `.section-impact-numbers`.

**Tokens**:
| Token | Valor |
|-------|--------|
| Fundo | `#0F172A` |
| Padding V/H | `64px` / `40px` |
| Max-width conteúdo | `1280px` |
| Destaque | Poppins 700, `#FD7B1A`, caixa alvo `56px` |
| Subtexto | `#FFFFFF`, `opacity: 0.8`, `uppercase`, caixa alvo `20px` |
| Gap item | `8px` |

**Rationale**: FR-007–013; espelha libraries isoladas 012/013.

**Alternatives considered**: estilos só em `style.css` — dificulta isolamento e remoção futura.

---

## R9 — Seed tipográfico + idempotência

**Decision**: `custom_configs_update_11017` localiza o Node `quem_somos` (mesmo critério das features 010/012 — tipicamente por type + status ou nid conhecido via loadByProperties). Se `field_numeros_lista` estiver vazio/ausente, cria e anexa **exatamente 4** paragraphs:

| # | Destaque | Subtexto |
|---|----------|----------|
| 1 | `20k+` | `ESTUDANTES ATIVOS` |
| 2 | `1.2k+` | `EMPRESAS PARCEIRAS` |
| 3 | `8k+` | `ESTÁGIOS INICIADOS` |
| 4 | `95%` | `SATISFAÇÃO GLOBAL` |

Se a lista já tiver itens → **no-op** (não duplicar; não sobrescrever editorial).

**Rationale**: FR-014 / FR-015; SC-006.

**Alternatives considered**: UUID fixo de node — frágil se o nid/UUID diferir entre ambientes; seed sempre forçado — viola preservação editorial.

---

## R10 — Hook `custom_configs_update_11017`

**Decision**: update idempotente que:
1. Garante paragraph type + field storage/instance + form/view displays (rede de segurança pós-`cim`).
2. Anexa `field_numeros_lista` ao bundle `quem_somos` e atualiza displays (incl. Field Group opcional).
3. Seed dos 4 itens se lista vazia.
4. **Não** altera banner, Sobre nós, Missão/Visão Node, Diferenciais Quem Somos.

**Rationale**: FR-014; próximo número livre após `11016`.

**Alternatives considered**: só config sem seed — página sem números no aceite; `default_content` — dependência nova, YAGNI.

---

## R11 — Ordem de deploy

**Decision**: origem `drush cex`; destino `git pull` → `cim -y` → `updb -y` → `cr`. Atualizar menção do layout v2 Quem Somos na regra/PRD para incluir `11017` (cirúrgico).

**Rationale**: FR-016; `estagio-fluxo-dev.mdc` / `drupal-deploy-configs.mdc`.

---

## R12 — Fallbacks Twig

**Decision**:
- Lista vazia → omitir a seção inteira.
- Item com um campo vazio → omitir só o elemento vazio.
- Ambos vazios → não renderizar a célula do item.
- &lt; 4 itens → grid com colunas disponíveis; sem forçar placeholders.

**Rationale**: Edge Cases + SC-007.

---

## R13 — PRD §3.1.0

**Decision**: atualização cirúrgica documentando `numero_destaque_p`, `field_numeros_lista` (card 4), seção Impact in Numbers (tokens + library `impact_numbers`), hook `11017`, e convivência com bloco 013 em `content_full`. Ampliar a linha de deploy do layout v2 para incluir `11017`.

**Rationale**: FR-017; Guardião do Escopo.
