# Research: Detalhe da Vaga — Refino (031)

**Data**: 2026-10-07 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **reuso do shell 030**, **migração idempotente do FAQ**, **isolamento de CTAs claros**, e **deploy repetível** (`cim` → `updb` → `cim` → `cr`).

---

## R1 — Reuso do template `--full` (não criar view mode novo)

**Decision**: refatorar `node--vagas--full.html.twig` existente; manter raiz `.vaga-detalhe` e grid 8/4.

**Rationale**: Spec Assumptions; shell da 030 já isola o detalhe da listagem; YAGNI novo view mode.

**Alternatives considered**:
- View mode `detalhe_v2` — rejeitado (YAGNI).
- Reescrever só `node--vagas.html.twig` — rejeitado (`--full` já é o canônico).

---

## R2 — FAQ coleção (1 node × N itens) vs modelo 030 (N nodes)

**Decision**: Content Type `faq` passa a ser **coleção** via `field_faq_itens` → paragraph `faq_item_p` (`field_pergunta` + `field_resposta`); `field_vaga_faq` cardinality **1**.

**Rationale**: FR-001–004; glossário da skill de domínio; pedido explícito do produto. Modelo 030 (title=pergunta, ER ilimitado) não atende.

**Alternatives considered**:
- Manter N nodes FAQ e só mudar o Twig — rejeitado (viola FR coleção).
- Paragraphs direto no node `vagas` sem CT `faq` — rejeitado (FR exige CT referenciável + reuso de coleção entre vagas).

---

## R3 — Storages do paragraph FAQ

**Decision**: criar `paragraph.field_pergunta` (`string`) e `paragraph.field_resposta` (`text_long`).

**Rationale**: FR-002 machine names; field storages são por entity type — `node.field_resposta` da 030 não cobre paragraphs.

**Alternatives considered**:
- Reusar `field_text_simple` + `field_text_simple_long` — rejeitado (nomes ≠ FR; colisão semântica com benefícios).
- Nested paragraphs sem CT — rejeitado (ver R2).

---

## R4 — Migração FAQ 1:1 → coleção + seeds

**Decision**:
1. Para cada node `faq` legado com `title`+`field_resposta` e sem itens: criar 1 `faq_item_p` e anexar a `field_faq_itens`; ajustar `title` da coleção se ainda for a pergunta (prefixo/coleção).
2. Seed canônico: **1** coleção UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` com **2** itens (perguntas Figma).
3. Seeds 030 `…abc01`/`…abc02`: se existirem como 1:1, fundir itens na coleção seed (sem duplicar perguntas iguais); não criar novos nodes 1:1.
4. Vagas com múltiplas refs em `field_vaga_faq`: consolidar itens num único target antes de forçar card. 1.

**Rationale**: FR-003, FR-027, idempotência; evita perda editorial.

**Alternatives considered**: apagar FAQs 030 e só seedar do zero — rejeitado (perda). Twig dual-path 1:1+coleção permanente — rejeitado (dívida).

---

## R5 — Recreate `field_vaga_requisitos` como Text long

**Decision**: no `11048`, migrar valores string multi → HTML lista, deletar storage/instance string, recriar storage `text_long` card. 1 com o **mesmo** machine name, restaurar valor migrado se destino vazio.

**Rationale**: FR-005; Drupal não muda tipo de field storage in-place; manter machine name do contrato.

**Alternatives considered**:
- Novo campo `field_vaga_requisitos_html` — rejeitado (quebra FR/nome).
- Manter multi-string + CSS — rejeitado (viola FR-005).
- Reusar instance de `field_text_long_formatted` no bundle — impossível (já usado como “Sobre a Vaga”).

---

## R6 — Benefícios e `field_text_simple_small`

**Decision**: manter `beneficio_vaga_p` com `field_image` + `field_text_simple`. Pedido verbal `field_text_simple_small` → canônico `field_text_simple`.

**Rationale**: storage já existe; padrão 004–026; proibido storage paralelo.

**Alternatives considered**: criar `field_text_simple_small` — rejeitado (reuso/YAGNI).

---

## R7 — Remoção de Processo / Perfil / Match / CTA inline

**Decision**: remover markup/CSS dessas seções do detalhe; Match já ausente. Manter storage `field_vaga_etapas_processo` no schema (sem UI full). Remover uso de `vaga_perfil_completo` no Twig.

**Rationale**: FR-017; YAGNI purge de field etapas nesta feature (FR-027 foca migração sem perda).

**Alternatives considered**: deletar field etapas no mesmo hook — rejeitado (risco/escopo). Deixar markup oculto por CSS — rejeitado (ainda no DOM).

---

## R8 — CTA final via `cta_v1` + visibilidade por bundle

**Decision**:
- Nova instância `cta_v1` + placement `default_ctav1vagas` em `content_full` com `entity_bundle:node` = `vagas`.
- Isolamento visual: Twig suggestion do placement + library/CSS dedicada (padrão 026), fundo `#023C62`.
- Primário: link seed (cadastro/login) — candidatura real permanece na sidebar.

**Rationale**: FR-018/019; `content_full` após `page.content` em páginas que não são `/para-estudantes`; `entity_bundle` já usado em `default_vagasvoltar`.

**Alternatives considered**:
- Manter CTA inline no Twig — rejeitado (FR pede `cta_v1`).
- Visibility só `request_path` — inviável (paths canônicos de node variam).
- Alterar `cta-v1.css` global para escuro — rejeitado (regressão QS/PE).

---

## R9 — “Ver Empresa” omitido

**Decision**: card empresa sem botão/link; truncar descrição.

**Rationale**: inventário 030/031 — sem rota pública; Assumptions da spec permitem omitir.

**Alternatives considered**: criar rota pública — fora de escopo; link painel — 403.

---

## R10 — Header global (FR-020)

**Decision**: nenhuma alteração estrutural planejada; smoke test home + detalhe (desktop/mobile). Corrigir só se o refino introduzir regressão.

**Rationale**: menu já vem de `default_top` + `drupal_menu('main')`; pedido é alinhamento/regressão, não redesign.

**Alternatives considered**: redesign do menu nesta feature — rejeitado (fora do core do detalhe; P2).

---

## R11 — Número do hook

**Decision**: `custom_configs_update_11048` (sucessor de `11047`).

**Rationale**: último update no install é `11047`; sequência contínua.

**Alternatives considered**: reutilizar `11047` — rejeitado (já rodou em ambientes com modelo 030).

---

## R12 — Accordion FAQ (itens da coleção)

**Decision**: Bootstrap 5 Accordion; IDs `vaga-faq-{vaga_nid}-{delta}` (ou paragraph id); ler itens de `field_vaga_faq.entity.field_faq_itens`.

**Rationale**: padrão 030; FR-015; teclado nativo BS5.

**Alternatives considered**: details/summary — rejeitado (manter accordion do site); JS custom — YAGNI.
