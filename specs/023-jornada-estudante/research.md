# Research: Jornada do Estudante

**Data**: 2026-09-30 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **reuso de storages**, **Clean URL `/para-estudantes`**, e **isolamento** frente ao hero `021`, aos benefícios `022` e à View `vagas`.

---

## R1 — Machine names novos (não reusar benefícios / PE / legado)

**Decision**: block type `jornada_estudante`; paragraph `passo_jornada_p` (sufixo `_p` obrigatório). Não reutilizar `beneficios_estudantes` / `card_icon_text_p` / `diferenciais_quem_somos` / `diferencial_simples_p` / `icone_titulo_descricao` nem Twig/CSS dessas seções.

**Rationale**: FR-001 / FR-003; Input do usuário; Section 03 é jornada numerada (sem ícone), distinta dos cards com ícone da `022`. Evita colisão editorial e regressão visual (SC-008).

**Alternatives considered**:
- Reusar `card_icon_text_p` omitindo ícone — rejeitado (machine name pedido ≠; badge/número exigem markup próprio).
- Segundo placement da instância benefícios — rejeitado (campos/layout/copy diferentes).

---

## R2 — Reuso de storages (canônicos do projeto)

**Decision**:
- Título da seção e título do passo: `field_text_simple` (pedido verbal `field_text_simple_small` → canônico).
- Descrição do passo: `field_text_simple_long`.
- Lista: storage existente `field_itens_lista` (ERR → paragraph); **nova instance** no bundle `jornada_estudante` com handler só `passo_jornada_p`.

**Rationale**: FR-002 / FR-004; `estagio-fluxo-dev.mdc`; padrão 004–022.

**Alternatives considered**: criar `field_text_simple_small` ou storage de lista novo — viola reuso / YAGNI.

---

## R3 — Cardinalidade “4” sem mutar storage compartilhado

**Decision**: **não alterar** `field.storage.block_content.field_itens_lista` (`cardinality: -1`). Enforcement do máximo de **4** passos no bundle `jornada_estudante` via:
1. `hook_form_alter` (ou equivalente no `custom_configs`) — ocultar/desabilitar “Add more” quando já houver 4 itens;
2. validação no submit (rejeitar >4) para cobrir bypass de UI.

**Rationale**: Cardinalidade no Drupal vive no **storage**, não na instance. Mutar o storage para `4` quebraria benefícios (`022`), diferenciais QS (`013`) e demais consumidores. Seed e copy Figma usam exatamente 4; FR-004 / US3 exigem impedir o 5º.

**Alternatives considered**:
- Storage novo `field_jornada_passos` cardinality 4 — rejeitado (anti-reuso).
- Aceitar ilimitado como em `022` — rejeitado (aceitação US3 exige bloqueio do 5º).
- Mutar storage para 4 — rejeitado (regressão em outros bundles).

---

## R4 — Badge numerada + último laranja (apresentação, não dado)

**Decision**:
- Número da etapa = `loop.index` no Twig do **bloco** (ou CSS `counter` como fallback equivalente).
- Estilo “último” = `loop.last` e/ou classe `.je-badge--last` / `:last-child` → fundo `#FD7B1A`.
- Badges 1…N-1 → fundo navy `#023C62` (já presente no tema).
- **Sem** field de número, cor ou ícone no paragraph/bloco.

**Rationale**: FR-005 / FR-015 / FR-016; Assumptions (último = último item da lista, não índice hardcoded “4”). Token laranja `#FD7B1A` é padrão 009–022; `#FF8C78` descartado em favor do token já adotado. Bloco controla o loop para ter `loop.index`/`loop.last` sem preprocess.

**Alternatives considered**:
- Campo numérico/cor no CMS — fora de escopo.
- CSS-only counters sem `loop.index` — aceitável como reforço, mas spec pede loop Twig; implementar loop no bloco.
- Hardcode “passo 4 = laranja” — rejeitado (edge case &lt;4 passos: o último da lista deve ser laranja).

---

## R5 — Layout Bootstrap (grid 1 / 2 / 4) + tokens Figma

**Decision**: markup Twig com utilitários Bootstrap 5:
- wrapper: container `max-width: 1280px`; paddings ~`64px` / `40px` (Top/Bottom / Left/Right)
- título: `h2` centrado, Poppins bold, `#000000` ou `#0F172A`, altura visual ~38px
- grid: `.row.justify-content-center.g-4.mt-4`
- card column: `.col-12.col-md-6.col-lg-3`
- card: `.text-center.d-flex.flex-column.align-items-center`; fundo `#FFFFFF`; `border-radius: 16px`; padding ~`24px`; sombra sutil; `min-height` ~`202px`
- badge: `48×48`, `border-radius: 50%`, número branco ~18px bold, centralizado flex

**Rationale**: FR-009–017; SC-001/002; alinhado ao tema Barrio e ao grid de `022` (mesmas colunas); chrome de card menor (202 vs 310) e badge no lugar do ícone.

**Alternatives considered**: CSS Grid custom — YAGNI; Layout Builder — fora de escopo.

---

## R6 — Templates e naming (Barrio suggestions)

**Decision**:
- Bloco: `themes/custom/default/templates/block/block--block-jornada-estudante.html.twig`.
- Paragraph: `paragraph--passo-jornada-p.html.twig`.
- Classe raiz obrigatória: `block-jornada-estudante`.
- Classes BEM locais: ex. `je-header`, `je-header__title`, `je-grid`, `je-card`, `je-badge`, `je-badge--last`, `je-card__title`, `je-card__text`.

**Rationale**: FR-009; isolation vs. `.block-beneficios-estudantes` / `.be-*`.

**Alternatives considered**: suggestion só por ID de placement — frágil.

---

## R7 — CSS em library dedicada

**Decision**: library `default/jornada_estudante` → `assets/css/jornada-estudante.css`; attach no Twig do bloco. Todos os seletores sob `.block-jornada-estudante`. Tokens locais para navy/laranja; **não** alterar `--brand-orange` / `--brand-navy` globais.

**Rationale**: FR-009; SC-008; espelha 021/022.

**Alternatives considered**: estilos só em `style.css` — dificulta isolamento.

---

## R8 — Seed + UUID + placement (sem assets de imagem)

**Decision**:
- UUID fixo do `block_content`: `b9c0d1e2-f3a4-4567-b890-1cdef0123456`.
- Placement config ID: `default_jornadaestudante` (região `content_full`, weight **`1`**, pages `/para-estudantes`).
- Seed título: “Sua jornada até o sucesso”.
- Seed 4 passos (copy Figma / Assumptions da spec):
  1. Crie seu perfil — Mostre suas habilidades e formação de forma clara e atrativa.
  2. Descubra oportunidades — Receba recomendações inteligentes baseadas no seu perfil.
  3. Candidate-se — Com apenas um clique, envie seu perfil para as melhores vagas.
  4. Comece sua carreira — Acompanhe seus processos e celebre suas conquistas.
- **Sem** pasta de assets de ícone (diferente de `022`).
- Idempotência: UUID existe → não duplica; preenche lista **somente** se vazia/ausente; **nunca** sobrescreve editorial.

**Rationale**: FR-018–020; weight `1` imediatamente após `default_beneficiosestudantes` (weight `0` verificado em `config/sync`).

**Alternatives considered**: weight `0` — colidiria com benefícios; weight alto — desnecessário.

---

## R9 — Hook `custom_configs_update_11037`

**Decision**: próximo livre após `11036` → `custom_configs_update_11037`. Ensure defensivo de tipos/fields/displays; seed; ensure placement weight `1`; mensagem Drush created/skipped; sem sobrescrever editorial; sem tocar 021/022/vagas/PE/QS/home.

**Rationale**: FR-019; `drupal-deploy-configs.mdc`. Destino: `cim` → `updb` → `cim` → `cr`. Verificado: `11035` = benefícios; `11036` = desativa formulário exposto vagas.

**Alternatives considered**: só `cex` sem hook — rejeitado (seed/UUID não vivos só no sync de forma confiável).

---

## R10 — Deploy, PRD e convivência

**Decision**:
- Estrutura via admin/API + `drush cex` → `config/sync`.
- Destino: `cim` → `updb` (`11037`) → `cim` → `cr`.
- PRD cirúrgico §3.6: block type `jornada_estudante`, paragraph `passo_jornada_p`, placement `content_full` weight `1` `/para-estudantes`, hook `11037`; convivência hero `021` + benefícios `022` + View `vagas`.
- CSS só sob `.block-jornada-estudante`; amostragem regressão: hero estudantes, benefícios estudantes, “Como funciona” home.

**Rationale**: FR-021 / FR-022; SC-004 / SC-008.

**Alternatives considered**: só `updb` sem `cex` — rejeitado.
