# Research: 051-vaga-faq-padrao

**Date**: 2026-11-02  
**Spec**: [spec.md](spec.md)  
**Baseline verificado no código** (main, 2026-11-02).

## R1 — Qual coleção é o FAQ padrão

**Decision**: A coleção seed canônica UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` (hoje titulada “FAQ — Exemplo”, helper `_custom_configs_seed_faq_collection_example()`) **é** o FAQ padrão das vagas. Não criar segunda coleção “oficial”.

**Rationale**: Spec Assumptions + FR-001/FR-010; UUID já usado por 030/038/`11060` no vínculo em massa. Reexecução do seed continua fill-if-empty (não sobrescreve editorial divergente).

**Alternatives considered**:
- Nova coleção “FAQ padrão das vagas” com outro UUID — rejeitado (duplica conteúdo; quebra seeds existentes).
- Designar via `state`/config editável no admin — rejeitado nesta entrega (overengineering; UUID fixo já é a convenção do projeto).

## R2 — Fonte de renderização (parar de ler `field_vaga_faq`)

**Decision**:
1. Expor helper público em `custom_configs.module` (ex.: `custom_configs_load_faq_padrao(): ?NodeInterface`) que carrega o node `faq` pelo UUID canônico.
2. Em `default_preprocess_node__vagas__full`, carregar o FAQ padrão, filtrar itens válidos (pergunta **e** resposta após trim), injetar `faq_paras` (e opcionalmente `faq_padrao`) nas variáveis Twig, e anexar cache tags/contexts do node FAQ.
3. Em `node--vagas--full.html.twig`, **deixar de ler** `node.field_vaga_faq`; usar só `faq_paras` do preprocess. Manter o include `vaga-faq-accordion.html.twig` inalterado.

**Rationale**: FR-002/FR-004 — valores legados por vaga não podem influenciar a página pública. Twig sandbox já força preprocess para lógica de acesso (padrão 037 `show_empresa_card`). Cache tags do FAQ garantem SC-004 (editar FAQ invalida caches das páginas de vaga que o referenciam).

**Alternatives considered**:
- Manter `field_vaga_faq` como fonte e só “sincronizar” todas as vagas para o seed — rejeitado (ainda permite divergência via API/admin oculto; viola fonte única).
- View/bloco global de FAQ — rejeitado (038 já excluiu Views; posição no detalhe permanece no Twig do node).

## R3 — Campo `field_vaga_faq` no formulário e no storage

**Decision**: Ocultar `field_vaga_faq` no form display `node.vagas.default` (`region: hidden`). **Não** apagar storage nem instance. Valores legados no banco podem permanecer (no-op na renderização). View display já tem o campo hidden (`true` em `config/sync`); consolidar via Entity API no hook + `drush cex`.

**Rationale**: FR-005; alinhado ao padrão 050 (`field_empresa_u` / `field_vaga_beneficios` ocultos). Spec permite remoção física do storage “se simplificar”, mas ocultar é mais seguro (rollback, Views/painel legados, zero risco de `cim` falhar por dependências).

**Alternatives considered**:
- Remover field storage/instance — rejeitado nesta entrega (risco de dependências e sem benefício para a página pública).
- `hook_form_alter` só — rejeitado (não exportável; deploy exige config versionada).

## R4 — Deploy / hook number

**Decision**: Um hook `custom_configs_update_11074` idempotente:
1. `_custom_configs_ensure_faq_structure()` / `_custom_configs_ensure_faq_item_p()` (reuso, no-op se ok);
2. `_custom_configs_seed_faq_collection_example()`;
3. Helper novo `_custom_configs_ensure_vaga_faq_form_hidden()` — move `field_vaga_faq` para `hidden` no form display (e garante view hidden se necessário).

Origem: `drush updb` → `drush cex`. Destino: `cim` → `updb` → `cim` → `cr`. Último hook existente: **`11073`**. Próximo: **`11074`**.

**Rationale**: FR-010–FR-013; receita normativa do runbook; não precisa backfill de `field_vaga_faq` (Twig não lê mais o campo).

**Alternatives considered**:
- Reexecutar vínculo em massa `11060` — desnecessário (campo deixa de ser fonte).
- Só alterar YAML à mão em `config/sync` — rejeitado (regra do projeto).

## R5 — PRD

**Decision**: Atualização cirúrgica na implementação: §3.1 (CT FAQ / `field_vaga_faq`) — FAQ das vagas passa a ser **único/padrão** (coleção seed), não “associável por vaga”; formulário sem seletor. Parágrafo do detalhe full (Accordion 038) menciona fonte global 051.

**Rationale**: FR-014 + `estagio-prd-guardian.mdc`.

## R6 — Visual / Accordion

**Decision**: Zero mudança de CSS/markup do Accordion além da troca de fonte de dados. Contrato visual 038 permanece.

**Rationale**: Escopo “Fora” da spec; FR-008.
