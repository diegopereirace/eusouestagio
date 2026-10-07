# Tasks: Cabeçalho do Detalhe da Vaga — Perfil Empresa

**Input**: Artefatos de design em `specs/032-vaga-header-empresa/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/vaga-header-empresa-render.md`, `contracts/deploy-vaga-header-empresa.md`, `quickstart.md`  
**Branch**: `feature-vaga` (ou branch ativa alinhada à feature)  
**Predecessor**: `031-vagas-detalhe-refino` (layout duas colunas; header ainda lê `field_empresa_u`)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/032-vaga-header-empresa` (via `.specify/feature.json`); template alinhado a `specs/031-vagas-detalhe-refino/tasks.md`  
**Hook**: `custom_configs_update_11049` (último existente: `11048`)

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar estado pós-031 (header via user, pills com `field_horarios`, CT `empresa` ausente, campos novos ausentes, hook livre `11049`) antes de implementar.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/vaga-header-empresa-render.md`, `contracts/deploy-vaga-header-empresa.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/032-vaga-header-empresa/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar bloco header atual (logo/`user_picture`, `field_empresa_u`, pills `field_regime_t` / `field_horarios` / `field_text_simple` / `vaga_postado_ha`, localização) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T003 [P] Inventariar CSS do header (`.vaga-detalhe__header*`, logo atual ~72px, pills/badges) em `themes/custom/default/assets/css/components/vagas-detalhe.css` e library `default/vagas_detalhe` em `themes/custom/default/default.libraries.yml`
- [X] T004 [P] Inventariar preprocess `default_preprocess_node__vagas` e helper `formatTimeDiffSince()` / variável `vaga_postado_ha` em `themes/custom/default/default.theme`
- [X] T005 [P] Confirmar estado estrutural: ausência de `node.type.empresa`, `field_vaga_empresa`, `field_vaga_carga_horaria`; existência de storage `node.field_imagem`, `field_empresa_u`, `field_cidade`, `field_estados`, `field_regime_t`, `field_text_simple`, role `empresa` — sob `config/sync/`
- [X] T006 Confirmar último hook `custom_configs_update_11048` → próximo livre desta feature **`11049`** em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 [P] Inventariar superfícies intocáveis (Twigs Views `views-view-field--vagas--*--nothing.html.twig`, `vagas-lista-vertical.css`, Hero 027, seção “Sobre a Empresa” / sidebar / FAQ / CTA 031) sob `themes/custom/default/`

**Checkpoint**: inventário alinhado a plan/research/data-model; hook alvo = `11049`; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar isolamento listagem/cards/seções 031, convivência com `field_empresa_u`, ausência de rota pública de empresa, e alvos fixos (UUIDs / asset / hook). Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Congelar escopo negativo da listagem/cards/Hero: **não** editar Twigs `views-view-field--vagas--page-1|block-1|block-2|block-3--nothing.html.twig`, CSS `vagas-lista-vertical.css`, library `vagas_lista_vertical`, Hero 027, nem regras `.item-vaga--destaque` / `.item-vaga--lista` sob `themes/custom/default/`
- [X] T009 [P] Congelar escopo negativo de produto: **não** remover/desativar `field_empresa_u`; **não** migrar em massa user→node; **não** criar rota pública `/empresa/*` nem botão “Ver Empresa”; **não** redesenhar Sobre/Requisitos/Benefícios/FAQ/sidebar/CTA — ver `specs/032-vaga-header-empresa/research.md` (R5/R11) e escopo Fora da `spec.md`
- [X] T010 [P] Congelar split Twig: header lê só `field_vaga_empresa` (node); seção “Sobre a Empresa” continua em `field_empresa_u` até feature futura — documentar intenção em `specs/032-vaga-header-empresa/contracts/vaga-header-empresa-render.md` (sem mutar YAML ainda)
- [X] T011 Documentar alvos fixos a implementar (CT `empresa` + instance `field_imagem`; storages `field_vaga_empresa` / `field_vaga_carga_horaria`; empresa seed UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`; vaga seed UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`; asset `modules/custom/custom_configs/assets/vaga-header-empresa/logo-ecoconstrutora.png`; hook `11049`) em `specs/032-vaga-header-empresa/data-model.md` / `contracts/` — sem criar YAML ainda

**Checkpoint**: restrições claras; pronto para ensures/Twig/CSS sem risco de regressão `/vagas`/cards/seções 031.

---

## Phase 3: User Story 2 — Editor vincula Empresa à Vaga e preenche carga horária (Priority: P1)

**Goal**: CT `empresa` (title + `field_imagem`); na vaga, `field_vaga_empresa` + `field_vaga_carga_horaria` com form/view displays; editor cria empresa e vincula.  
**Independent Test Criteria**: criar empresa de teste + associar a vaga + preencher carga → reabrir detalhe e confirmar dados persistidos (SC-003 parcial / quickstart C).  
**Nota de ordem**: estrutura de dados antes do header visual (US1) — plan phases 2–3.

- [X] T012 [US2] Implementar helper `_custom_configs_ensure_node_type_empresa()` (CT `empresa` label “Empresa”; instance `field_imagem` sobre storage existente; form/view displays default title+imagem; reexecução = no-op) em `modules/custom/custom_configs/custom_configs.install` conforme `data-model.md` / research R1–R2
- [X] T013 [P] [US2] Implementar helper `_custom_configs_ensure_field_vaga_empresa()` (storage novo ER `node.field_vaga_empresa` → bundle `empresa` card. 1 + instance no bundle `vagas` + form/view displays; label “Empresa da vaga”) em `modules/custom/custom_configs/custom_configs.install` conforme research R3
- [X] T014 [P] [US2] Implementar helper `_custom_configs_ensure_field_vaga_carga_horaria()` (storage novo `string` `node.field_vaga_carga_horaria` card. 1 + instance no bundle `vagas` + form/view displays; label “Carga horária”) em `modules/custom/custom_configs/custom_configs.install` conforme research R4
- [X] T015 [US2] Garantir permissões mínimas de criar/editar/visualizar node `empresa` para papéis editoriais já usados no projeto (sem abrir edição anônima) nos helpers T012–T014 ou ensure de role em `modules/custom/custom_configs/custom_configs.install`
- [X] T016 [US2] Validar SC-002 parcial / cenários US2 (criar Empresa com logo; vincular na vaga; persistir carga “30h semanais”; regime/bolsa/cidade/estado existentes alimentam pills sem campos novos) conforme `specs/032-vaga-header-empresa/quickstart.md` seção C (após ensure local + `drush cr`)

**Checkpoint**: modelo editorial Empresa↔Vaga operacional; `field_empresa_u` intacto no form.

---

## Phase 4: User Story 1 — Visitante identifica a vaga pelo header do Figma (Priority: P1) 🎯 MVP

**Goal**: card header branco arredondado; logo 96×96 do node empresa; h1; nome + badge verificado + pin/local; quatro pills (regime, carga, bolsa, postado); omit empty; mobile sem overflow.  
**Independent Test Criteria**: abrir vaga com `field_vaga_empresa` + pills preenchidos e comparar frame Figma (SC-001 / SC-002 / SC-003 / SC-006 / quickstart B).  
**Depende de**: ensures US2 (T012–T014) para dados reais; seed US4 fecha o caminho zero-manual.

- [X] T017 [US1] Refatorar `<header class="vaga-detalhe__header">` no Twig full: ler `empresa_node` de `field_vaga_empresa.entity` (publicado/acessível); logo de `field_imagem`; h1 = título da vaga; nome + ícone verificado (`fa-circle-check` ou equivalente) + `•` + pin + cidade/estado; pills regime (`field_regime_t`), carga (`field_vaga_carga_horaria`), bolsa (`field_text_simple`), `vaga_postado_ha`; **remover** `field_horarios` e `field_empresa_u`/`user_picture` do header; manter “Sobre a Empresa” no legado — em `themes/custom/default/templates/content/node--vagas--full.html.twig` conforme `contracts/vaga-header-empresa-render.md`
- [X] T018 [P] [US1] Ajustar CSS do card header (fundo `#fff`, border `#E5E7EB` ou token próximo, `border-radius: 16px`, padding generoso; logo 96×96 `object-fit: cover` radius 12px; subtítulo inline; pills azul muito claro `border-radius: 999px` unificadas no header; mobile ≤576px sem overflow-x) sob `.vaga-detalhe__header*` em `themes/custom/default/assets/css/components/vagas-detalhe.css`
- [X] T019 [US1] Tratar edge cases no Twig (sem empresa → omitir logo/nome/verificado; sem logo → omitir img sem quebrar layout; local vazio → omitir pin/`•` órfão; pill vazia → omitir só ela; empresa unpublished → tratar como ausência) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T020 [US1] Validar SC-001 / SC-002 / SC-003 / SC-006 / cenários US1 (card Figma; quatro pills; mobile; alt do logo) conforme `specs/032-vaga-header-empresa/quickstart.md` seção B (após `drush cr`)

**Checkpoint**: visitante vê header alinhado ao Figma — MVP visual da feature.

---

## Phase 5: User Story 3 — Visitante lê tempo relativo desde a postagem (Priority: P2)

**Goal**: `vaga_postado_ha` em pt-BR a partir de `created`: &lt;1h → “menos de 1 hora”; &lt;24h → hora(s); ≥1d → dia(s); sem negativos.  
**Independent Test Criteria**: comparar pill com `created` conhecido / ajustado (quickstart D / FR-008).

- [X] T021 [US3] Substituir/envolver `formatTimeDiffSince()` por helper pt-BR no preprocess `default_preprocess_node__vagas` (&lt;1h / hora(s) / dia(s); string consumível pela pill “Postado há …”) em `themes/custom/default/default.theme` conforme research R7
- [X] T022 [US3] Confirmar Twig da quarta pill usando `vaga_postado_ha` com `|t` / placeholders corretos (sem hardcode de data) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T023 [US3] Validar cenários US3 (&lt;1h; horas; dias; pluralização; sem negativo) conforme `specs/032-vaga-header-empresa/quickstart.md` seção D

**Checkpoint**: pill “Postado há…” estável em pt-BR.

---

## Phase 6: User Story 4 — Deploy automatizado leva estrutura e mock para o destino (Priority: P1)

**Goal**: `custom_configs_update_11049` idempotente (ensures + asset + seeds EcoConstrutora / Engenheiro Civil); origem `drush cex`; destino `cim`→`updb`→`cim`→`cr`.  
**Independent Test Criteria**: receita deploy + 2ª `updb` sem duplicar seeds; header completo na vaga seed (SC-004 / SC-005 / quickstart A+E / contrato deploy).

- [X] T024 [US4] Adicionar asset placeholder de logo versionado em `modules/custom/custom_configs/assets/vaga-header-empresa/logo-ecoconstrutora.png` (nunca `sites/default/files` no Git)
- [X] T025 [US4] Implementar helper `_custom_configs_seed_empresa_ecoconstrutora()` (UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`; title “EcoConstrutora”; copiar asset → `public://`; load por UUID; não sobrescrever editorial divergente; reexecução = no-op) em `modules/custom/custom_configs/custom_configs.install` conforme `data-model.md` / research R9
- [X] T026 [US4] Implementar helper `_custom_configs_seed_vaga_engenheiro_civil()` (UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`; title “Engenheiro Civil”; `field_vaga_empresa` → seed; carga `30h semanais`; regime/bolsa/cidade/estado preenchidos; publicado; preencher só vazios se existir; sem duplicar) em `modules/custom/custom_configs/custom_configs.install`
- [X] T027 [US4] Implementar `custom_configs_update_11049` (chamar ensures T012–T015 + seeds T025–T026; **não** migrar/`remover` `field_empresa_u`; **não** alterar View listagem / Hero / cards laranja / seções 031; retornar mensagem Drush; idempotente) em `modules/custom/custom_configs/custom_configs.install`
- [X] T028 [US4] Exportar configs estruturais com `drush cex -y` — versionar tipicamente `node.type.empresa.yml`, `field.field.node.empresa.field_imagem.yml`, displays empresa, `field.storage.node.field_vaga_empresa.yml`, `field.storage.node.field_vaga_carga_horaria.yml`, instances/displays `vagas`; **não** inventar YAML à mão; revisar diff para **não** regredir listagem/Hero/`field_empresa_u`
- [X] T029 [US4] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (idempotência SC-005) conforme `specs/032-vaga-header-empresa/quickstart.md` seções A e E e `contracts/deploy-vaga-header-empresa.md`
- [X] T030 [US4] Validar gates pós-deploy (CT empresa; fields vaga; 1× EcoConstrutora + 1× Engenheiro Civil; header completo; legado `field_empresa_u` presente; SC-004) conforme contrato de deploy e quickstart

**Checkpoint**: destino reproduz estrutura + seed sem admin manual; hook reentrante seguro.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: PRD §3.1 cirúrgico, specify-rules, isolamento listagem/cards/seções 031, aceite final SC-001–SC-006.

- [X] T031 [P] Atualizar `PRD.md` §3.1 de forma cirúrgica (CT `empresa` vs role `empresa`; `field_imagem` no CT; `field_vaga_empresa` + `field_vaga_carga_horaria` em `vagas`; convivência com `field_empresa_u`; menção hook **`11049`**) — manter bullets listagem `/vagas` e cards laranja intactos
- [X] T032 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para feature `032-vaga-header-empresa` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T033 [P] Amostrar regressão: `/vagas` + Hero 027; cards laranja Home/PE; seções Sobre Empresa / sidebar / FAQ / CTA do detalhe intactos; header **não** lê `field_empresa_u` — quickstart F
- [X] T034 Executar validação final completa (SC-001–SC-006; checklist deploy; edges sem empresa/sem logo/pill vazia/empresa unpublished) com `specs/032-vaga-header-empresa/quickstart.md` e `checklists/requirements.md`
- [X] T035 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US2 (Phase 3) → US1 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US2 entrega CT + fields — bloqueia aceite completo de US1/US4 com dados reais
- US1 (MVP visual) depende dos ensures US2 para logo/nome/carga; pode codificar Twig/CSS em paralelo após T012–T014 definidos, mas validação B exige dados
- US3 pode avançar em paralelo a US1 no preprocess após Foundational; validação da pill fecha com header US1
- US4 (hook + seed + cex + deploy) agrega helpers T012–T015 + T025–T026; desbloqueia SC-004/SC-005 em destino limpo
- Polish após US1–US4

## Dependency Graph

```text
Phase1 → Phase2 → US2 (P1) CT empresa + fields vaga ─────────────┐
                    ├→ US1 (P1) Twig/CSS header Figma (MVP) ──────┤
                    ├→ US3 (P2) preprocess “Postado há…” pt-BR ───┤
                    └→ US4 (P1) hook 11049 + seed + cex + deploy ─┴→ Polish
                         ↑
                         └── usa helpers T012–T015, T025–T026
```

## Parallel Opportunities

| Após | Paralelo seguro |
|------|-----------------|
| T001 | T002–T005, T007 |
| Phase 2 | T008–T011 (T008 sequencial com clareza de escopo; T009–T010 [P]) |
| T012 iniciado | T013 ∥ T014 |
| T017 iniciado | T018 ∥ (T021 preprocess US3) |
| Após US4 | T031 ∥ T032 ∥ T033 |

```text
# Exemplo US2
T012 → (T013 ∥ T014) → T015 → T016

# Exemplo US1 (após ensures US2)
T017 → (T018 ∥ T019) → T020
# T019 pode ser feito no mesmo PR que T017 se preferir um único pass no Twig

# Exemplo US3
T021 → T022 → T023   # ou T021 ∥ T017 após Foundational

# Exemplo US4
T024 → (T025 ∥ T026) → T027 → T028 → T029 → T030
```

## Implementation Strategy

1. **MVP**: Phase 1–2 + US2 (modelo) + US1 (header Figma) — visitante já vê o valor visual com empresa criada manualmente ou via ensure local.
2. **Incremento P2**: US3 — pill de tempo pt-BR correta.
3. **Incremento deploy**: US4 — seed + `11049` + `cex` + receita destino.
4. **Polish**: PRD + specify-rules + regressão + aceite SC-001–SC-006.

## Format Validation

- Todas as tarefas usam `- [ ]`, ID `T00N`, paths de arquivo e labels `[USn]` nas fases de user story
- Marcador `[P]` só em tarefas paralelizáveis (arquivos distintos / sem dependência de incompletas)
- Sem tarefas de teste automatizado (não solicitadas na spec)
