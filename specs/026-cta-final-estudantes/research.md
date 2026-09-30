# Research: Bloco CTA Final — Para Estudantes

**Data**: 2026-09-30 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **reuso do `cta_v1`**, **isolamento visual absoluto** (sem regressão em Quem Somos / Para Empresas), e **deploy repetível** (`cim` → `updb` → `cim` → `cr`).

---

## R1 — Reuso do tipo `cta_v1` (sem estrutura nova)

**Decision**: criar apenas nova **instância** + placement + apresentação isolada. Não criar block type, field storage nem field instance.

**Rationale**: FR-001 / FR-002; Assumptions; tipo e campos já estáveis desde `015` + reuso PE `019`/`11024`.

**Alternatives considered**:
- Novo block type “CTA Escuro” — rejeitado (Fora / YAGNI / duplicaria campos).
- Paragraph no Node shell — rejeitado (spec exige Custom Block + placement em `content_full`).

---

## R2 — Instância distinta com UUID próprio

**Decision**:
- UUID `block_content`: `e1f2a3b4-c5d6-4789-d012-3ef012345678`
- Distinto de Quem Somos (`e6f7a8b9-…`) e Para Empresas (`a7b8c9d0-…`)
- `info` admin: “CTA v1 Para Estudantes”

**Rationale**: FR-004 / US5; edição independente; padrão de UUIDs da série estudantes (`a8b9…` benefícios → `d0e1…` shell).

**Alternatives considered**: reusar instância de Quem Somos com segundo placement — rejeitado (copy e visual diferentes; edição cruzada).

---

## R3 — Placement ID, weight e visibility

**Decision**:
- Config ID: `default_ctav1paraestudantes` (pedido verbal `ctav1_para_estudantes` → padrão `default_*` do tema)
- UUID placement: `f2a3b4c5-d6e7-4890-e123-4f0123456789`
- Região `content_full`, weight **`4`**, pages `/para-estudantes`, `label_display: '0'`

**Rationale**: FR-015 / FR-016; vizinho atual `default_views_block__vagas_block_3` weight `3` (verificado em `config/sync`). Ordem: benefícios `0` → jornada `1` → perfil `2` → vagas `3` → **CTA `4`**.

**Alternatives considered**: weight `3` — colide com `block_3`; weight negativo — ordem ambígua com placements legados.

---

## R4 — Isolamento via suggestion Twig do placement (não alterar Twig global)

**Decision**: template `block--default-ctav1paraestudantes.html.twig`. **Não** modificar `block--block-cta-v1.html.twig` para adicionar condicionais de rota/estilo.

**Rationale**: FR-006; Barrio já resolve suggestion por ID de bloco (ex.: `block--default-footer.html.twig`); prioridade natural sobre suggestion do bundle; zero risco de side-effect nas outras instâncias.

**Alternatives considered**:
- `hook_preprocess_block` + classes no Twig global — rejeitado (ainda acopla lógica ao template compartilhado).
- View mode dedicado — rejeitado (config/overhead YAGNI).

---

## R5 — CSS em library dedicada (não tocar `cta-v1.css`)

**Decision**:
- Library `default/cta_v1_para_estudantes` → `assets/css/cta-v1-para-estudantes.css`
- Escopo: `.block-cta-v1--para-estudantes` e/ou `#block-default-ctav1paraestudantes`
- Attach **somente** no Twig da suggestion
- Arquivo `cta-v1.css` permanece intocado

**Rationale**: FR-006 / SC-004; mesma estratégia de libraries isoladas do tema; facilita remoção futura.

**Alternatives considered**:
- Overrides com ID dentro de `cta-v1.css` — rejeitado (arquivo compartilhado; risco de regressão em review/diff).
- Reusar library `cta_v1` + !important — rejeitado (vazamento / especificidade frágil).

---

## R6 — Tokens Figma da variante escura

**Decision**: tokens exclusivos desta instância (ver plan / contract). Full-bleed via padding negativo ou seção edge-to-edge no wrapper do bloco; conteúdo interno em `.container`.

**Rationale**: FR-007–014; botão secundário outline branco **só** neste escopo (sobrescreve visualmente o secundário branco-sólido do CTA claro **sem** alterar o CSS global).

**Alternatives considered**: card contido como QS — rejeitado (Figma PE pede faixa full-bleed escura).

---

## R7 — Markup / classes BEM

**Decision**: manter família `cta-v1__*` no markup interno (paridade estrutural com o Twig global) e adicionar classe raiz `.block-cta-v1--para-estudantes` (e opcionalmente `.block-cta-v1` só se útil para utilitários compartilhados **sem** carregar `cta-v1.css`). Preferência: **não** attach da library clara nesta instância — evita herdar fundo `#D3E4FE` / secundário branco.

**Rationale**: omit empty e estrutura de botões já validados em `015`; CSS escuro redefine aparência no escopo.

**Alternatives considered**: markup totalmente novo (`.cta-final-pe__*`) — rejeitado (drift desnecessário).

---

## R8 — Seed + idempotência (espelhar helper PE empresas)

**Decision**: helper `_custom_configs_seed_cta_v1_para_estudantes()` no padrão de `_custom_configs_seed_cta_v1_para_empresas()`:
- loadByProperties UUID → se existe, preencher **somente** campos vazios
- se ausente, `BlockContent::create()` com seed completo
- ensure placement `default_ctav1paraestudantes` (criar/atualizar weight/visibility se ausente; não duplicar)

**Rationale**: FR-017 / FR-018; SC-008; código já comprovado em produção para PE empresas.

**Alternatives considered**: seed forçado sempre — viola preservação editorial; `default_content` — dependência nova.

---

## R9 — Hook `custom_configs_update_11043`

**Decision**: update idempotente que:
1. Verifica bundle `cta_v1` presente (senão mensagem “rode cim antes”)
2. Seed da instância
3. Ensure placement (plugin UUID, região, weight ≥4, pages `/para-estudantes`)
4. **Não** altera 021–025, CTAs QS/PE, View `vagas`, Node shell

**Rationale**: FR-017; `11042` já usado (título `/vagas`); próximo livre = `11043`.

---

## R10 — Ordem de deploy

**Decision**: origem `drush cex`; destino `git pull` → `cim -y` → `updb -y` → `cim -y` → `cr`.

**Rationale**: FR-019; placement exportado referencia UUID do conteúdo seedado pelo hook — 2ª `cim` alinhada a `drupal-deploy-configs.mdc` e feature `025`.

---

## R11 — Fallbacks Twig

**Decision**: mesmos edge cases do CTA global (omit title/body/botão; omit actions; omit faixa se tudo vazio).

**Rationale**: Edge Cases + SC-009; reaproveitar lógica do Twig `015`.

---

## R12 — PRD §3.6

**Decision**: atualização cirúrgica:
- Linha da composição `/para-estudantes`: acrescentar CTA w4 após `block_3`
- Bullet `cta_v1`: mencionar 3ª instância (estudantes), placement, UUID, hook `11043`, library isolada
- Não reescrever seções de Quem Somos / Para Empresas além da menção na convivência do bullet

**Rationale**: FR-020; Guardião do Escopo.

---

## R13 — Permissões

**Decision**: bundle `cta_v1` já tem permissões nas roles (015/019). **Não** alterar roles nesta feature salvo se `drush cex` revelar diff inesperado — não é objetivo.

**Rationale**: US5 já coberta pelo bundle existente; YAGNI.
