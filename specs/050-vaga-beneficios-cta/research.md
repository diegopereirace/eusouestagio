# Research: 050-vaga-beneficios-cta

**Date**: 2026-11-02  
**Spec**: [spec.md](spec.md)  
**Baseline verificado no código** (main, 2026-11-02).

## R1 — Campo empresa legado vs público

**Decision**: No form display `node.vagas.default`, ocultar `field_empresa_u` (region `hidden`); manter `field_vaga_empresa` visível e editável. Não apagar storage nem instances.

**Rationale**: Header/cartão já leem só `field_vaga_empresa` (`node--vagas--full.html.twig`, `vaga-sobre-empresa.html.twig`, `default_preprocess_node__vagas__full`). `field_empresa_u` permanece usado por painel/candidaturas/`custom_panel` (role empresa força o UID logado). Spec FR-001/FR-002 = só ocultação editorial.

**Alternatives considered**:
- Remover storage de `field_empresa_u` — rejeitado (quebra painel e Views legadas).
- Renomear labels e manter ambos — rejeitado (continua confusão).

## R2 — Modelo de benefícios: taxonomia + checkboxes

**Decision**:
- Vocabulário novo `beneficios` (vid).
- Campo de ícone no termo: `field_image` em `taxonomy_term.beneficios` — **novo** `field.storage.taxonomy_term.field_image` (não existe storage de image em taxonomy_term hoje; `field_icone_fa` em `curso` é Font Awesome, inadequado para WebP).
- Campo na vaga: `field_vaga_beneficios_t` (entity reference → `taxonomy_term:beneficios`, cardinality unlimited, `required: true`, widget `options_buttons` / checkboxes).
- Campo legado `field_vaga_beneficios` (ERR → `beneficio_vaga_p`): oculto no form e no view display; Twig deixa de iterá-lo; storage permanece para rollback.

**Rationale**: Spec exige catálogo reutilizável com ícone WebP + seleção múltipla. Padrão de machine names do projeto: `field_*_t` para taxonomias na vaga (`field_cursos_t`, `field_regime_t`, `field_tecnologias_t`). Reuso de `field_vaga_beneficios` é impossível sem mudar o tipo de storage (ERR ≠ taxonomy).

**Alternatives considered**:
- Manter paragraphs e só padronizar títulos — rejeitado (spec FR-003–FR-006).
- Converter `field_vaga_beneficios` in-place — rejeitado (Field API não troca tipo de storage com dados).
- Vocabulário + `field_icone_fa` — rejeitado (spec exige WebP, não FA).

## R3 — Seed dos 7 termos + assets WebP

**Decision**:
- Termos (UUID fixos, ordem seed): Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível, Assistência Médica, Auxílio Academia / Gympass, Day Off no Aniversário.
- Assets: `modules/custom/custom_configs/assets/beneficios/beneficios-0{1..7}.webp` → `public://beneficios/` via `_custom_configs_seed_file_from_module_asset()`.
- Reaproveitar arte dos 4 PNG Figma (036) convertendo para WebP na implementação; criar 3 WebP novos (médica, academia, aniversário).
- Idempotência: se termo já existe (por UUID ou nome normalizado) e já tem ícone, não sobrescrever; só preencher ausência. Asset ilegível → abortar aquele termo com mensagem explícita (não criar termo sem ícone).

**Rationale**: Spec FR-007 + edge case de asset ausente; helper e pasta `assets/beneficios/` já estabelecidos em 036.

**Alternatives considered**:
- Manter PNG — rejeitado (FR-004/FR-007 exigem WebP).
- Commit em `sites/default/files` — rejeitado (regra do projeto).

## R4 — Migração paragraphs → termos

**Decision**: No `hook_update_11071`, para cada nó `vagas`:
1. Ler títulos de `field_vaga_beneficios` (paragraphs `field_text_simple`).
2. Normalizar (trim, case-fold, colapsar espaços; tratar `/` e sinônimos mínimos só se idênticos aos 7 nomes canônicos).
3. Mapear aos termos seedados quando houver match; setar `field_vaga_beneficios_t` **somente se estiver vazio** (não sobrescrever seleção editorial já migrada).
4. Títulos sem match → ignorados (sem criar termo novo).
5. Não apagar paragraphs.

**Rationale**: Edge cases da spec; padrão fill-if-empty do `custom_configs`.

**Alternatives considered**:
- Migration API / migrate_plus — rejeitado (overhead; padrão do projeto é `hook_update_N`).
- Apagar paragraphs após mapear — rejeitado (rollback).

## R5 — CTA: audiência candidato + ação = candidatura

**Decision**:
1. **Visibility config** em `block.block.default_ctav1vagas`: manter `entity_bundle:node` → `vagas`; adicionar `user_role` → `candidato` (padrão já usado em `default_painelcandidato` / `default_paraempresas`).
2. **Twig** `block--default-ctav1vagas.html.twig`: botão primário deixa de usar `field_link` → `/cadastro/candidato`; passa a renderizar o mesmo contrato da sidebar (`js-candidatar-vaga` + `data-node-id` + `data-apply-url` + rota `custom_panel.vagas_candidatar`). Anexar `default/script-painel`.
3. **Preprocess** do bloco: injetar `vaga_node_id`, `vaga_candidatada`, URL de apply a partir do `node` da rota.
4. **Estado já candidatada**: remover o `AccessResult::forbidden()` de `default_block_access()` para esse caso; o Twig mostra botão desabilitado “Candidatura Enviada” (alinhado à sidebar). Visibility por role já cobre anônimo/empresa/admin sem papel candidato.
5. Seed do `field_link` da instância CTA pode permanecer como fallback de título “Candidatar-se Agora”; a URI deixa de ser a fonte da ação no Twig de vagas.

**Rationale**: FR-010–FR-012; rota/JS já existem (033); `user_role` é exportável via `drush cex`; ocultar bloco inteiro pós-candidatura diverge do “estado alinhado à sidebar”.

**Alternatives considered**:
- Só alterar `field_link` para `/vaga/apply/{nid}` — rejeitado (rota é POST-only; GET não candidata).
- Manter hide-when-applied em `hook_block_access` — aceitável como “equivalente”, mas pior alinhamento visual com sidebar; preferir disabled no Twig.
- Novo bloco/plugin custom — rejeitado (reuso de `cta_v1` + placement existente).

## R6 — Deploy / hook number

**Decision**: Um hook `custom_configs_update_11071` idempotente com helpers `_custom_configs_ensure_*` / `_custom_configs_seed_beneficios_vocab` / `_custom_configs_migrate_vaga_beneficios_to_taxonomy`. Origem: alterar via Entity API/admin → `drush cex`. Destino: `cim` → `updb` → `cim` → `cr`.

**Rationale**: Último hook = `11070`; receita normativa; feature cria vocab+fields+displays+seed+migração+visibility.

**Alternatives considered**: Vários hooks 11071–11073 — possível, mas um único com helpers espelha 11047/11056 e reduz risco de ordem parcial.

## R7 — PRD

**Decision**: Na implementação, atualização cirúrgica de §3.1 (campos `vagas`: ocultar legado empresa na edição; `field_vaga_beneficios_t`; paragraphs legado oculto), §3.4 (vocabulário `beneficios`), §3.6 (bullet `cta_v1` instância vagas: role `candidato` + ação candidatura).

**Rationale**: `estagio-prd-guardian.mdc` — mudança estrutural.
