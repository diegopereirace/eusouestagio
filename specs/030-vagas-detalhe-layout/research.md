# Research: Detalhe da Vaga — Layout Duas Colunas

**Data**: 2026-10-04 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **isolamento da listagem `/vagas` e cards laranja**, **reuso de storages paragraph**, **fluxos de candidatura existentes**, e **deploy repetível** (`cim` → `updb` → `cim` → `cr`).

---

## R1 — Template `node--vagas--full.html.twig`

**Decision**: novo template suggestion `node--vagas--full.html.twig` com raiz `.vaga-detalhe`; view mode `full` apenas.

**Rationale**: Spec Assumptions; o arquivo atual `node--vagas.html.twig` cobre qualquer view mode — `--full` evita efeitos colaterais se teaser/outro modo passar a usar o mesmo hook; listagem usa Twigs de View, não este arquivo.

**Alternatives considered**:
- Só reescrever `node--vagas.html.twig` — aceitável, mas menos preciso.
- View mode custom `detalhe_figma` — rejeitado (YAGNI; `full` já é o canônico).

---

## R2 — Library CSS dedicada

**Decision**: library `default/vagas_detalhe` → `assets/css/components/vagas-detalhe.css`; escopo `.vaga-detalhe`.

**Rationale**: FR-021; estilos atuais em `style.css` misturam detalhe legado; library dedicada segue padrão 028/025 e reduz regressão.

**Alternatives considered**: ampliar blocos `.vaga-*` em `style.css` — rejeitado (arquivo monolítico; risco de afetar classes ainda usadas).

---

## R3 — Content Type `faq` + `field_resposta`

**Decision**: criar CT `faq`; storage novo `node.field_resposta` tipo `text_long`; pergunta = `title`.

**Rationale**: FR-001–003; nenhum CT FAQ no sync; machine name da resposta é contrato. `text_long` permite formatação leve via text format do site.

**Alternatives considered**:
- Reusar `field_text_simple_long` (string_long) — rejeitado (nome ≠ FR; sem formato).
- Reusar instance de `field_text_long_formatted` no faq — rejeitado (machine name divergente do FR).
- Paragraph FAQ — rejeitado (spec pede CT referenciável).

---

## R4 — Storages novos em `vagas` para multi-string e ER/ERR

**Decision**:
- `field_vaga_etapas_processo` / `field_vaga_requisitos` — storages `string` cardinality `-1` novos;
- `field_vaga_faq` — ER → `node` (handler só `faq`);
- `field_vaga_beneficios` — ERR → `paragraph` cardinality `-1`.

**Rationale**: `field_text_simple_multiple` e `_2` já estão no bundle; Drupal não permite segunda instance do mesmo storage no mesmo bundle. ERR `field_numeros_lista` tem cardinality 4 e propósito “Impact Numbers”.

**Alternatives considered**: esvaziar/renomear legados — rejeitado (FR-028 mantém legados para compatibilidade).

---

## R5 — Paragraph `beneficio_vaga_p` (não `card_icon_text_p`)

**Decision**: tipo novo com instances de `field_image` + `field_text_simple` (storages paragraph existentes).

**Rationale**: Spec Assumptions + padrão “paragraph por seção”; `card_icon_text_p` exige também `field_text_simple_long` e CSS da feature 022.

**Alternatives considered**: reusar `card_icon_text_p` omitindo descrição — rejeitado (colisões editorial/CSS; machine name pedido ≠).

---

## R6 — Preferência campos novos + migração opcional

**Decision**: página full lê só campos novos; hook `11047` copia legado → novo **somente se** o novo estiver vazio e o legado tiver valores (requisitos item a item; benefícios → paragraphs só com título, sem ícone).

**Rationale**: FR-028; evita sobrescrever editorial; Twig sem dual-path permanente.

**Alternatives considered**: Twig com fallback legado permanente — rejeitado (dois caminhos de dados; dívida).

---

## R7 — “Ver Empresa” omitido

**Decision**: card “Sobre a Empresa” mostra logo/nome/`field_sobre_empresa` truncado; **sem** botão “Ver Empresa”.

**Rationale**: inventário — não há rota pública de empresa; `/painel/empresa/perfil` é área autenticada de role empresa. FR-016 permite omitir com fallback documentado.

**Alternatives considered**:
- Link para painel empresa — rejeitado (403/confuso).
- Criar página pública de empresa — fora de escopo.

---

## R8 — Perfil % estático

**Decision**: barra com 75% (ou `$variables['vaga_perfil_completo']` se preprocess setar); link “Completar agora” → `/painel/estudante/perfil` (anônimo → login com destination).

**Rationale**: FR-019 / Assumptions; nenhum serviço de completude no custom.

**Alternatives considered**: calcular % por campos do candidato — rejeitado (fora do escopo; sem especificação de pesos).

---

## R9 — Reuso de ações de candidatura / salvar / share

**Decision**: manter classes `js-candidatar-vaga`, `js-salvar-vaga`, `data-node-id`, library `script-painel` e markup de share (WhatsApp/Facebook/LinkedIn) já no Twig atual; CTA final dispara o mesmo fluxo.

**Rationale**: Escopo → Fora proíbe fluxo novo; preprocess já calcula estados.

**Alternatives considered**: endpoints novos — rejeitado (YAGNI).

---

## R10 — Accordion Bootstrap 5 nativo

**Decision**: markup Accordion BS5 do tema; IDs únicos `vaga-faq-{vaga_nid}-{faq_nid}`; sem JS custom.

**Rationale**: FR-017; Barrio/Bootstrap 5.3 já no tema; acessibilidade teclado nativa.

**Alternatives considered**: details/summary puro — rejeitado (spec pede Accordion BS5); library accordion nova — YAGNI.

---

## R11 — Stepper CSS-only

**Decision**: lista/flex de passos numerados; último círculo `#FD7B1A`; conector via pseudo-elemento/borda; mobile wrap ou scroll-x discreto.

**Rationale**: FR-015; sem JS; dados = valores de `field_vaga_etapas_processo`.

**Alternatives considered**: paragraph de etapas — rejeitado (spec = texto multi); lib de stepper — YAGNI.

---

## R12 — Hook `11047` e seeds

**Decision**: `custom_configs_update_11047` com helpers ensure (CT/fields/displays/paragraph) + seed FAQ por UUID + migrate legado opcional. **Não** auto-associar FAQs a todas as vagas.

**Rationale**: FR-024; `11045` ocupado; `11046` reservado 029; padrão depoimento (`11032` / `_ensure_*`). Associação global = risco editorial.

**Alternatives considered**: só `cex` sem hook — rejeitado (destino sem admin; regra deploy). Associar FAQs a todas as vagas — rejeitado (YAGNI; Assumptions).

---

## R13 — Resumo “Vagas disponíveis”

**Decision**: linha com valor “Não informado” (sem campo estrutural).

**Rationale**: FR-022 / Assumptions.

**Alternatives considered**: omitir linha — aceitável como alternativa de UI, mas spec lista o par; default = “Não informado”.

---

## Resolução de clarificações

Nenhum `NEEDS CLARIFICATION` restante no Technical Context após inventário. Pontos abertos da checklist (“Ver Empresa”, associação FAQ) resolvidos em R7 e R12.
