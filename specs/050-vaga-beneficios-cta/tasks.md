# Tasks: Refatoração da Vaga Interna (Benefícios + CTA)

**Feature**: `specs/050-vaga-beneficios-cta`  
**Branch**: `050-vaga-beneficios-cta`  
**Plan**: [plan.md](plan.md) | **Spec**: [spec.md](spec.md) | **Deploy**: [contracts/deploy-vaga-beneficios-cta.md](contracts/deploy-vaga-beneficios-cta.md)  
**Render**: [contracts/vaga-beneficios-taxonomy-render.md](contracts/vaga-beneficios-taxonomy-render.md) | **CTA**: [contracts/cta-vagas-candidatura.md](contracts/cta-vagas-candidatura.md)  
**Hook**: `custom_configs_update_11071` (último existente: `11070`)

## Implementation Strategy

MVP primeiro, entrega incremental por user story (todas P1 no spec):

1. **US3 (catálogo)** — vocabulário `beneficios` + `field_image` no termo + seed dos 7 WebP. Sem catálogo, checkboxes e detalhe não têm valor.
2. **US1 (empresa)** — ocultar `field_empresa_u` no form display; manter `field_vaga_empresa` canônico.
3. **US2 (seleção + detalhe)** — `field_vaga_beneficios_t` (checkboxes, required), ocultar paragraphs, Twig lê taxonomia, migração título→termo.
4. **US4 (CTA)** — visibility `user_role: candidato` + botão `js-candidatar-vaga` + preprocess + `default_block_access` deixa de forbidden pós-candidatura.
5. **US5 (deploy)** — montar `11071` idempotente chamando ensures/seed/migrate; `drush updb` → `cex` → `cr` na origem; receita destino `cim` → `updb` → `cim` → `cr`.

Sem PHPUnit (padrão do projeto). Validação manual via [quickstart.md](quickstart.md).

---

## Phase 1: Setup

- [X] T001 Confirmar identidade da feature em `.specify/feature.json` (`feature_directory: specs/050-vaga-beneficios-cta`) e artefatos presentes em `specs/050-vaga-beneficios-cta/` (spec, plan, research, data-model, contracts, quickstart, checklists)
- [X] T002 Confirmar pasta `modules/custom/custom_configs/assets/beneficios/` (já usada na 036 com PNG) e preparar destino dos 7 WebP versionados
- [X] T003 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-01.webp` (Plano de Carreira — converter/espelhar de `beneficios-01.png`)
- [X] T004 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-02.webp` (Vale Transporte — de `beneficios-02.png`)
- [X] T005 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-03.webp` (Vale Refeição — de `beneficios-03.png`)
- [X] T006 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-04.webp` (Horário Flexível — de `beneficios-04.png`)
- [X] T007 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-05.webp` (Assistência Médica — asset novo)
- [X] T008 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-06.webp` (Auxílio Academia / Gympass — asset novo)
- [X] T009 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-07.webp` (Day Off no Aniversário — asset novo)

---

## Phase 2: Foundational

Pré-requisitos bloqueantes. Nenhuma user story de implementação começa antes destes checks.

- [X] T010 Confirmar em `modules/custom/custom_configs/custom_configs.install` que o último hook é `custom_configs_update_11070` e que o próximo número livre é `11071` (sem colisão)
- [X] T011 Confirmar helper `_custom_configs_seed_file_from_module_asset()` em `modules/custom/custom_configs/custom_configs.install` reutilizável para WebP → `public://beneficios/`
- [X] T012 Confirmar em `config/sync/core.entity_form_display.node.vagas.default.yml` que `field_empresa_u` e `field_vaga_empresa` ainda estão visíveis e que `field_vaga_beneficios` (paragraphs) está no form
- [X] T013 Confirmar ausência de `taxonomy.vocabulary.beneficios.yml`, `field.storage.node.field_vaga_beneficios_t.yml` e `field.storage.taxonomy_term.field_image.yml` em `config/sync/`
- [X] T014 Confirmar em `themes/custom/default/templates/content/node--vagas--full.html.twig` o loop atual de `field_vaga_beneficios` (paragraphs) na seção `.vaga-detalhe__beneficios` (~linhas 135–165)
- [X] T015 Confirmar em `themes/custom/default/default.theme` que `default_block_access()` retorna `forbidden()` para `default_ctav1vagas` quando o candidato já se candidatou
- [X] T016 Confirmar em `themes/custom/default/templates/block/block--default-ctav1vagas.html.twig` que o botão usa `field_link` (cadastro) e **não** tem `js-candidatar-vaga`
- [X] T017 Confirmar em `config/sync/block.block.default_ctav1vagas.yml` visibility só `entity_bundle:node` = `vagas` (sem `user_role: candidato`) e UUID placement `e5f6a7b8-c9d0-4123-d456-789abcdef012`
- [X] T018 Confirmar contrato sidebar em `themes/custom/default/templates/content/includes/vaga-sidebar-acoes.html.twig` (`js-candidatar-vaga` + rota `custom_panel.vagas_candidatar`) como referência do CTA

---

## Phase 3: User Story 3 — Catálogo seedado com 7 benefícios e ícones (P1)

**Goal**: Após o deploy, o vocabulário `beneficios` contém os sete nomes da spec, cada um com ícone WebP; reexecução não duplica nem sobrescreve editorial divergente.

**Independent Test**: Listar `/admin/structure/taxonomy/manage/beneficios/overview` → 7 termos com ícone; reexecutar seed → zero duplicação; asset ausente → falha explícita naquele termo.

- [X] T019 [US3] Implementar `_custom_configs_ensure_beneficios_vocabulary()` em `modules/custom/custom_configs/custom_configs.install`: criar/garantir vocab `beneficios` (label Benefícios); no-op se existir
- [X] T020 [US3] Implementar `_custom_configs_ensure_taxonomy_term_field_image()` em `modules/custom/custom_configs/custom_configs.install`: criar/garantir `field.storage.taxonomy_term.field_image` + instance `taxonomy_term.beneficios.field_image` (image, WebP) + form/view displays do termo
- [X] T021 [US3] Definir UUIDs fixos dos 7 termos em constantes/array em `modules/custom/custom_configs/custom_configs.install` (conforme [data-model.md](data-model.md)) e mapear nome → asset `beneficios-0N.webp`
- [X] T022 [US3] Implementar `_custom_configs_seed_beneficios_terms()` em `modules/custom/custom_configs/custom_configs.install`: para cada termo, lookup por UUID (preferencial) ou nome normalizado; criar se ausente; anexar `field_image` via `_custom_configs_seed_file_from_module_asset('beneficios/beneficios-0N.webp', 'public://beneficios/beneficios-0N.webp', …)` com `alt` = nome; se termo já existe com ícone → não sobrescrever; se asset ilegível → mensagem `t()` clara e **não** criar termo sem ícone
- [X] T023 [US3] Garantir idempotência do seed em `modules/custom/custom_configs/custom_configs.install`: segunda execução → zero termos novos, zero arquivos duplicados (reuse por URI), nomes/ícones editoriais preservados
- [X] T024 [P] [US3] Documentar na implementação os 7 nomes exactos: Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível, Assistência Médica, Auxílio Academia / Gympass, Day Off no Aniversário (FR-007 / SC-002)

---

## Phase 4: User Story 1 — Moderador edita vaga sem duplicidade de Empresa (P1)

**Goal**: No formulário da vaga, apenas `field_vaga_empresa` aparece como “Empresa”; `field_empresa_u` fica hidden (storage intacto).

**Independent Test**: Abrir edição de vaga → empresa pública visível, legado ausente; salvar e confirmar header/cartão do detalhe inalterados.

- [X] T025 [US1] Implementar `_custom_configs_ensure_vaga_empresa_form_display()` em `modules/custom/custom_configs/custom_configs.install`: no form display `node.vagas.default`, mover `field_empresa_u` para region `hidden`; garantir `field_vaga_empresa` editável/visível
- [X] T026 [US1] Confirmar que storage/instance de `field_empresa_u` **não** são removidos (painel/candidaturas/`custom_panel` intactos) — apenas ocultação no form da vaga
- [X] T027 [US1] Validar no admin: formulário de vaga mostra só empresa pública; legado ausente; detalhe público (header/cartão) continua lendo `field_vaga_empresa` em `themes/custom/default/templates/content/node--vagas--full.html.twig` e `themes/custom/default/templates/content/includes/vaga-sobre-empresa.html.twig`

---

## Phase 5: User Story 2 — Moderador seleciona benefícios fixos por checkbox (P1)

**Goal**: Edição por checkboxes do catálogo; paragraphs legado ocultos; detalhe público renderiza termos (ícone + nome) no grid existente; migração fill-if-empty por título normalizado.

**Independent Test**: Marcar 3 benefícios, salvar, abrir detalhe → ícone + rótulo corretos; salvar sem benefício → bloqueio; paragraphs não aparecem no form nem no full.

- [X] T028 [US2] Implementar `_custom_configs_ensure_field_vaga_beneficios_t()` em `modules/custom/custom_configs/custom_configs.install`: storage `field.storage.node.field_vaga_beneficios_t` (entity_reference → taxonomy_term, unlimited) + instance `node.vagas.field_vaga_beneficios_t` com `required: true`, handler bundle `beneficios` only
- [X] T029 [US2] No form display `node.vagas.default` via ensure em `modules/custom/custom_configs/custom_configs.install`: widget `options_buttons` (checkboxes) para `field_vaga_beneficios_t`; region `hidden` para `field_vaga_beneficios` (paragraphs)
- [X] T030 [US2] No view display `node.vagas.default` via ensure em `modules/custom/custom_configs/custom_configs.install`: ocultar `field_vaga_beneficios` (paragraphs); configurar formatter adequado de `field_vaga_beneficios_t` (Twig custom lê o campo mesmo se hidden no display, se o template acessar a entity — preferir manter campo disponível à entity)
- [X] T031 [US2] Implementar `_custom_configs_migrate_vaga_beneficios_to_taxonomy()` em `modules/custom/custom_configs/custom_configs.install`: para cada nó `vagas`, se `field_vaga_beneficios_t` vazio, ler títulos de paragraphs `field_vaga_beneficios` → `field_text_simple`, normalizar (`mb_strtolower(trim())`, colapsar whitespace), mapear aos 7 nomes canônicos, setar termos; sem match → ignorar (não criar termo); **não** apagar paragraphs; **não** sobrescrever seleção já preenchida
- [X] T032 [US2] Em `themes/custom/default/templates/content/node--vagas--full.html.twig`, substituir o loop de `field_vaga_beneficios` pelo de `field_vaga_beneficios_t` conforme [contracts/vaga-beneficios-taxonomy-render.md](contracts/vaga-beneficios-taxonomy-render.md): ícone do `term.field_image`, título = `term.label`, grade `col-6 col-lg-3`, seção só se houver termos; **não** iterar paragraphs
- [X] T033 [P] [US2] Preferir reuso de `.vaga-detalhe__beneficios` / `__beneficio-card` / `__beneficio-icon` / `__beneficio-title` em `themes/custom/default/assets/css/components/vagas-detalhe.css` — só ajustar CSS se o markup da taxonomia exigir
- [X] T034 [US2] Validar form: checkboxes dos ≥7 termos; sem editor paragraphs; salvar sem seleção → bloqueio (FR-009 / SC-004)
- [X] T035 [US2] Validar detalhe: vaga com “Vale Refeição” + “Horário Flexível” → só esses cards; mobile 2 cols / desktop 4 cols

---

## Phase 6: User Story 4 — Candidato vê CTA e candidata-se pela mesma ação da sidebar (P1)

**Goal**: CTA “Pronto para o próximo passo?” só para role `candidato` em páginas `vagas`; botão dispara a mesma candidatura da sidebar; já candidatada → botão disabled (bloco permanece).

**Independent Test**: Logar como candidato → CTA visível e candidatura via botão; anônimo/empresa/admin sem candidato → CTA ausente; já candidatada → “Candidatura Enviada” disabled.

- [X] T036 [US4] Em `themes/custom/default/default.theme`, implementar preprocess do bloco `default_ctav1vagas`: injetar `vaga_nid`, `vaga_candidatada` (via `_default_user_has_candidatura_vaga()` ou equivalente), `vaga_apply_url` (`Url::fromRoute('custom_panel.vagas_candidatar', {node})`) a partir do node da rota; anexar library `default/script-painel` se necessário
- [X] T037 [US4] Atualizar `themes/custom/default/templates/block/block--default-ctav1vagas.html.twig` conforme [contracts/cta-vagas-candidatura.md](contracts/cta-vagas-candidatura.md): botão com `js-candidatar-vaga` + `data-node-id` + `data-apply-url` + `data-method="POST"`; se `vaga_candidatada` → `<button disabled>Candidatura Enviada</button>`; **não** navegar para `/cadastro/candidato`
- [X] T038 [US4] Em `themes/custom/default/default.theme`, alterar `default_block_access()`: **remover** `AccessResult::forbidden()` quando já há candidatura para `default_ctav1vagas` (Twig cobre o estado); manter demais regras de segurança intactas
- [X] T039 [US4] Garantir visibility do placement: adicionar `user_role` → `candidato` em `block.block.default_ctav1vagas` (via Entity API no hook e/ou admin + cex) mantendo `entity_bundle:node` = `vagas` — ver `config/sync/block.block.default_ctav1vagas.yml`
- [X] T040 [P] [US4] Só se necessário: estilo de botão disabled em `themes/custom/default/assets/css/cta-v1-vagas.css` (preferir reuso)
- [X] T041 [US4] Validar audiência: candidato vê CTA; anônimo / empresa / admin sem role candidato **não** veem; candidato com outro papel simultâneo **vê**
- [X] T042 [US4] Validar ação: clique “Candidatar-se Agora” = mesmo POST/feedback da sidebar; já candidatada = disabled sem reenvio

---

## Phase 7: User Story 5 — Operação ativa tudo sem admin manual (P1)

**Goal**: Um único `custom_configs_update_11071` idempotente + `drush cex` na origem; destino ativa tudo com `cim` → `updb` → `cim` → `cr`, zero passos manuais no admin.

**Independent Test**: Rodar receita em ambiente com vagas existentes; auditar form, catálogo, detalhe e CTA; reexecutar → zero duplicação; `config/sync` versionado.

- [X] T043 [US5] Implementar `custom_configs_update_11071()` em `modules/custom/custom_configs/custom_configs.install` chamando na ordem: ensure vocab + `field_image` termo → ensure `field_vaga_beneficios_t` + displays (form/view vaga: hide `field_empresa_u` + paragraphs; checkboxes) → seed 7 termos → migrate paragraphs→termos → ensure visibility CTA `user_role: candidato`; concatenar mensagens `t()`; **não** reabrir hooks antigos
- [X] T043b [US2/US5] `custom_configs_update_11072()`: ocultar também `field_text_simple_multiple_2` (texto livre “Benefícios”); único campo visível = `field_vaga_beneficios_t` (weight 6); migrate texto livre→taxonomia fill-if-empty; `drush cex`
- [X] T044 [US5] Garantir idempotência completa do `11071` em `modules/custom/custom_configs/custom_configs.install`: segunda passagem → no-op seguro (termos, files, migração fill-if-empty, displays, placement)
- [X] T045 [US5] Executar na origem `drush updb -y` (ou `docker compose exec drupal drush updb -y`) e validar mensagem do `11071`
- [X] T046 [US5] Executar na origem `drush cex -y` e versionar diffs reais em `config/sync/` (vocab, storages, instances, displays termo/vaga, `block.block.default_ctav1vagas.yml` com role) — **não** editar YAML à mão para inventar config
- [X] T047 [US5] Executar na origem `drush cr`
- [X] T048 [US5] Auditar pós-deploy mínimo: form sem `field_empresa_u` + checkboxes; vocab 7 termos; detalhe taxonomia; CTA só candidato; reexecutar `updb` → zero duplicação
- [X] T049 [P] [US5] Revisar `specs/050-vaga-beneficios-cta/contracts/deploy-vaga-beneficios-cta.md` e `specs/050-vaga-beneficios-cta/quickstart.md` alinhados à receita destino `cim` → `updb` → `cim` → `cr` e pré-requisito `settings.php` / `config_sync_directory`

---

## Phase 8: Polish & Cross-Cutting Concerns

- [X] T050 Atualizar cirurgicamente `PRD.md` §3.1 (campos vaga: `field_vaga_beneficios_t`; empresa legado oculto; paragraphs ocultos), §3.4 (vocab `beneficios`), §3.6 (CTA vagas: role `candidato` + ação candidatura)
- [X] T051 Executar validação completa do [quickstart.md](quickstart.md) (cenários US1–US5 + regressões) e registrar resultado
- [X] T052 Verificar idempotência ponta a ponta: `drush updb -y` já atualizado → no-op; `drush cex -y` → sem diff inesperado
- [X] T053 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT): Tasks de `050-vaga-beneficios-cta` aponta para `specs/050-vaga-beneficios-cta/tasks.md`
- [X] T054 Commitar em pt-BR com Conventional Commits **somente quando o usuário pedir**, sugerido: `feat: refatora benefícios da vaga para taxonomia e CTA de candidatura`
- [X] T055 Fix review: `field_empresa_u` auto-fill role empresa com form hidden (entity builder + presave + submit)
- [X] T056 Fix review: sync JS CTA↔sidebar em `.js-candidatar-vaga`; cache CTA via `hook_block_build_alter`
- [X] T057 Fix review: migrate reporta nids vazios + `11075`; `verify_050_beneficios_cta.php`; vocab UUID fixo; markup `div`; FR-014 no quickstart

---

## Dependencies

```text
Phase 1 (Setup: WebP assets)
    │
    ▼
Phase 2 (Foundational checks)
    │
    ▼
Phase 3 (US3: vocab + field_image + seed 7 termos)
    │
    ├─► Phase 4 (US1: hide field_empresa_u)          [paralelo após ensures de display]
    │
    └─► Phase 5 (US2: field_vaga_beneficios_t + Twig + migrate)
              │
              ├─► Phase 6 (US4: CTA role + Twig/JS + block_access)  [paralelo a US2 em arquivos de tema distintos]
              │
              └─► Phase 7 (US5: hook 11071 + updb + cex + cr)
                        │
                        ▼
              Phase 8 (Polish: PRD + quickstart + specify-rules)
```

- **US3 → US2**: checkboxes e migração dependem do vocab/termos seedados.
- **US3 → US1**: US1 só precisa do ensure de form display (pode avançar em paralelo após T019–T020 se o ensure de display da vaga for compartilhado).
- **US2 ∥ US4** (arquivos): Twig do node vs Twig/theme do CTA — paralelizáveis após Phase 2; teste de ponta a ponta do CTA não depende da taxonomia.
- **US1–US4 → US5**: o hook `11071` consolida ensures; `cex` só após estrutura estável na origem.
- **US5 → Polish**: PRD/quickstart após deploy validado.

---

## Parallel Execution Examples

### Dentro de Phase 1 (Setup)

T003–T009 são paralelizáveis (7 arquivos WebP independentes após T002).

### Dentro de Phase 2 (Foundational)

T010–T018 são checks de leitura — paralelizáveis.

### Dentro de Phase 3 (US3)

T019 → T020 → T021 → T022 → T023 sequenciais (mesmo `custom_configs.install`). T024 paralelo à documentação após T021.

### Entre US1 e US2 (após US3)

T025–T027 (US1) podem avançar em paralelo a T028–T031 (ensures/migrate US2) se coordenarem edits no mesmo `custom_configs.install`. T032 (Twig node) é independente de US1.

### Entre US2 e US4

T032–T035 (Twig/CSS/validação benefícios) ∥ T036–T042 (CTA theme/Twig/access) — arquivos distintos (`node--vagas--full.html.twig` vs `block--default-ctav1vagas.html.twig` / `default.theme`).

### Dentro de Phase 7 (US5)

T043 → T044 sequenciais; T045 → T046 → T047 sequenciais (`updb` → `cex` → `cr`); T048 e T049 paralelizáveis após T047.

---

## Implementation Strategy (MVP)

1. Entregar **US3** (catálogo + assets) — base de dados.
2. Entregar **US1 + US2** (form limpo + checkboxes + detalhe taxonomia + migração) — valor editorial/público.
3. Entregar **US4** (CTA candidato) — fecha o funil.
4. Fechar **US5** (hook único + cex) e Polish (PRD).

**Suggested MVP scope**: Phase 1–5 (US3 + US1 + US2) já entrega edição e detalhe coerentes; US4/US5 completam a feature.

---

## Done When

- [X] tasks.md gerado com todas as phases, task IDs e file paths
- [X] Extension hooks despachados ou ignorados conforme regras (não há `.specify/extensions.yml` — skipped)
- [X] Completion report entregue ao usuário com contagem de tasks, breakdown por user story, escopo MVP e validação de formato
