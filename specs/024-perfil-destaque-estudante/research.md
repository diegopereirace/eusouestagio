# Research: Perfil em Destaque (Estudante)

**Data**: 2026-09-30 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **reuso de storages**, **Clean URL `/para-estudantes`**, e **isolamento** frente ao hero `021`, benefícios `022`, jornada `023` e View `vagas`.

---

## R1 — Machine names novos (não reusar benefícios / jornada / PE)

**Decision**: block type `perfil_destaque_estudante`; paragraph `item_lista_icone_p` (sufixo `_p` obrigatório). Não reutilizar `beneficios_estudantes` / `card_icon_text_p` / `jornada_estudante` / `passo_jornada_p` / `diferenciais_quem_somos` / `diferencial_simples_p` / `icone_titulo_descricao` nem Twig/CSS dessas seções.

**Rationale**: FR-001 / FR-003; Input do usuário; Section 04 é lista com ícone + CTA em duas colunas, distinta do grid de cards (`022`) e da jornada numerada (`023`). Evita colisão editorial e regressão visual (SC-008).

**Alternatives considered**:
- Reusar `card_icon_text_p` — rejeitado (machine name pedido ≠; layout lista ≠ card grid; risco de CSS cruzado).
- Segundo placement de jornada/benefícios — rejeitado (campos/layout/copy diferentes).

---

## R2 — Reuso de storages (canônicos do projeto)

**Decision**:
- Ícone do item e ilustração do bloco: `field_image` (pedido verbal `field_imagem` no bloco → canônico `field_image` em `block_content`).
- Títulos (seção e item): `field_text_simple` (pedido verbal `field_text_simple_small` → canônico).
- Descrição do item: `field_text_simple_long`.
- Lista: storage existente `field_itens_lista` (ERR → paragraph); **nova instance** no bundle `perfil_destaque_estudante` com handler só `item_lista_icone_p`.
- CTA: storage existente `field_link` em `block_content`.

**Rationale**: FR-002 / FR-004; `estagio-fluxo-dev.mdc`; padrão 004–023. Storages verificados em `config/sync` (2026-09-30).

**Alternatives considered**: criar `field_text_simple_small`, `field_imagem` ou storage de lista novo — viola reuso / YAGNI.

---

## R3 — Cardinalidade “3” sem mutar storage compartilhado

**Decision**: **não alterar** `field.storage.block_content.field_itens_lista` (`cardinality: -1`). Enforcement do máximo de **3** itens no bundle `perfil_destaque_estudante` via:
1. `hook_form_alter` (ou equivalente no `custom_configs`) — ocultar/desabilitar “Add more” quando já houver 3 itens;
2. validação no submit (rejeitar >3) para cobrir bypass de UI.

**Rationale**: Cardinalidade no Drupal vive no **storage**, não na instance. Mutar o storage para `3` quebraria jornada (`023`, max 4 via form), benefícios (`022`), diferenciais QS (`013`) e demais consumidores. Spec US4 / FR-004 exigem impedir o 4º. Mesmo padrão documentado em `023` research R3.

**Alternatives considered**:
- Storage novo `field_perfil_itens` cardinality 3 — rejeitado (anti-reuso).
- Aceitar ilimitado — rejeitado (aceitação US4 exige bloqueio do 4º).
- Mutar storage para 3 — rejeitado (regressão em outros bundles, especialmente jornada max 4).

---

## R4 — Layout duas colunas Bootstrap + tokens Figma

**Decision**: markup Twig com utilitários Bootstrap 5:
- wrapper seção: `.section-perfil-destaque` / `.block-perfil-destaque-estudante`; fundo `#FFFFFF`
- container: `max-width: 1280px`; paddings Top/Bottom `64px`, Left/Right `40px`
- grid: `.row.align-items-center.g-5`
- colunas: `.col-12.col-lg-6` (esquerda: ilustração; direita: título + lista + CTA)
- ilustração: `.img-fluid`, centralizada na coluna
- título: `h2` Poppins; max-width texto `528px` no desktop
- item: `.d-flex.gap-3.mb-4`; ícone `20×20` `flex-shrink-0`; título `#9D4300` (ou tom exato do check Figma); descrição `#45464D`
- CTA: botão ~`208×44`, fundo navy `#023C62`, texto `#FFFFFF`

**Rationale**: FR-008–014; SC-001/002; alinhado ao tema Barrio. Empilhamento abaixo de `lg` = mobile/tablet (US2).

**Alternatives considered**: CSS Grid custom — YAGNI; Layout Builder — fora de escopo; reusar markup `missao_visao` — classes/tokens diferentes.

---

## R5 — Templates e naming (Barrio suggestions)

**Decision**:
- Bloco: `themes/custom/default/templates/block/block--block-perfil-destaque-estudante.html.twig`.
- Paragraph: `paragraph--item-lista-icone-p.html.twig`.
- Classes raiz: `section-perfil-destaque` e `block-perfil-destaque-estudante`.
- Classes BEM locais: ex. `pd-container`, `pd-media`, `pd-content`, `pd-title`, `pd-list`, `pd-item`, `pd-item__icon`, `pd-item__title`, `pd-item__text`, `pd-cta`.

**Rationale**: FR-008; isolation vs. `.block-beneficios-estudantes` / `.be-*` / `.block-jornada-estudante` / `.je-*`.

**Alternatives considered**: suggestion só por ID de placement — frágil.

---

## R6 — CSS em library dedicada

**Decision**: library `default/perfil_destaque_estudante` → `assets/css/perfil-destaque-estudante.css`; attach no Twig do bloco. Todos os seletores sob `.section-perfil-destaque` / `.block-perfil-destaque-estudante`. Tokens locais; **não** alterar `--brand-orange` / `--brand-navy` globais.

**Rationale**: FR-008; SC-008; espelha 021–023.

**Alternatives considered**: estilos só em `style.css` — dificulta isolamento.

---

## R7 — Seed + UUID + placement + assets

**Decision**:
- UUID fixo do `block_content`: `c0d1e2f3-a4b5-4678-c901-2def01234567`.
- Placement config ID: `default_perfildestaqueestudante` (região `content_full`, weight **`2`**, pages `/para-estudantes`).
- Seed título: “Seu perfil em destaque”.
- Seed CTA: título “Completar meu perfil”, URI `/painel/estudante/perfil`.
- Seed 3 itens (Assumptions da spec; se Figma divergir no implement, seguir Figma):
  1. Mostre suas habilidades — Vá além do currículo padrão e destaque competências, formação e diferenciais do seu perfil.
  2. Seja encontrado pelas empresas — Recrutadores buscam ativamente candidatos com perfil completo e atualizado.
  3. Receba oportunidades relevantes — Notificações personalizadas quando surgirem vagas alinhadas ao seu perfil.
- Assets: `modules/custom/custom_configs/assets/perfil-destaque-estudante/` (ilustração + 3 ícones); hook copia para `public://` se ausentes; **sem** commit de `sites/default/files`.
- Idempotência: UUID existe → não duplica; preenche campos/lista **somente** se vazios/ausentes; **nunca** sobrescreve editorial.

**Rationale**: FR-015–018; weight `2` imediatamente após `default_jornadaestudante` (weight `1` verificado em `config/sync`).

**Alternatives considered**: weight `1` — colidiria com jornada; weight alto — desnecessário.

---

## R8 — Hook `custom_configs_update_11038`

**Decision**: próximo livre após `11037` → `custom_configs_update_11038`. Ensure defensivo de tipos/fields/displays; seed com assets; ensure placement weight `2`; mensagem Drush created/skipped; sem sobrescrever editorial; sem tocar 021/022/023/vagas/PE/QS/home.

**Rationale**: FR-016 / FR-019; `drupal-deploy-configs.mdc`. Destino: `cim` → `updb` → `cim` → `cr`. Verificado: `11037` = jornada do estudante.

**Alternatives considered**: só `cex` sem hook — rejeitado (seed/UUID/assets não vivos só no sync de forma confiável).

---

## R9 — CTA e autenticação

**Decision**: `field_link` aponta para `/painel/estudante/perfil`. Comportamento de gate de login para anônimos é o fluxo **já existente** da plataforma — fora do escopo desta feature ajustar autenticação.

**Rationale**: FR-014; US3; Assumptions; SC-009.

**Alternatives considered**: hardcode URL no Twig — rejeitado (CTA deve ser editável no CMS via `field_link`).

---

## R10 — Deploy, PRD e convivência

**Decision**:
- Estrutura via admin/API + `drush cex` → `config/sync`.
- Destino: `cim` → `updb` (`11038`) → `cim` → `cr`.
- PRD cirúrgico §3.6: block type `perfil_destaque_estudante`, paragraph `item_lista_icone_p`, placement `content_full` weight `2` `/para-estudantes`, hook `11038`; convivência hero `021` + benefícios `022` + jornada `023` + View `vagas`.
- CSS só sob wrappers dedicados; amostragem regressão: hero estudantes, benefícios, jornada, “Como funciona” home.

**Rationale**: FR-019 / FR-020; SC-004 / SC-008.

**Alternatives considered**: só `updb` sem `cex` — rejeitado.
