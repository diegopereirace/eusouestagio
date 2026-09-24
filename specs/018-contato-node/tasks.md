# Tasks: Contato como Node

**Input**: Artefatos de design em `specs/018-contato-node/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/contato-node-render.md`, `quickstart.md`  
**Branch**: `feature-contato` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/018-contato-node` (via `.specify/feature.json`); template alinhado a `specs/017-pagina-contato/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar legado 017 (`layout_contato`, bloco ID 16, placement, página `page` + alias), storages canônicos em `node`, asset `img-contato.png`, último hook e padrões Twig/CSS de bloco Contato antes de criar Content Type / `11023`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/contato-node-render.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/018-contato-node/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar legado 017: block type `layout_contato`, placement `default_layoutcontato`, bloco seed UUID `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6`, página `page` UUID `f3a4b5c6-d7e8-4f90-a123-c4d5e6f7a8b9`, webform `contato` UUID `38532b7b-3193-4b6a-b666-e1e68693dd94` sob `config/sync/block_content.type.layout_contato.yml`, `config/sync/block.block.default_layoutcontato.yml`, `config/sync/webform.webform.contato.yml`
- [X] T003 [P] Confirmar storages reutilizáveis em `node` (`webform`, `field_imagem`, `field_text_simple`, `field_text_simple_long`) e ausência de `field_email` / `field_phone_wpp` / bundle `contato` em `node` sob `config/sync/field.storage.node.*` e `config/sync/node.type.*`
- [X] T004 [P] Confirmar equivalentes `field_email` / `field_phone_wpp` só em `block_content` (footer) e asset seed em `modules/custom/custom_configs/assets/contato/img-contato.png` + `config/sync/field.storage.block_content.field_email.yml`
- [X] T005 [P] Confirmar Twig/CSS/library atuais do bloco Contato e `default_page_title` com negate em `/contato` em `themes/custom/default/templates/block/block--block-layout-contato.html.twig`, `themes/custom/default/assets/css/layout-contato.css`, `themes/custom/default/default.libraries.yml`, `config/sync/block.block.default_page_title.yml`
- [X] T006 Confirmar último hook `custom_configs_update_11022` → próximo livre `11023` e helpers de alias/página Contato (`_custom_configs_ensure_contato_page` ou equivalentes) em `modules/custom/custom_configs/custom_configs.install`

**Checkpoint**: inventário alinhado a plan/research; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar Content Type `contato`, storages novos `field_email` + `field_phone_wpp` em `node`, instances (`webform`, `field_imagem`, `field_email`, `field_phone_wpp`, `field_text_simple`, `field_text_simple_long`), form/view displays e permissões do bundle — pré-requisito de todas as user stories. **Não** criar storages paralelos `field_formulario_contato` / `field_image` / `field_text_simple_small` em `node`; **não** alterar elementos do webform `contato`; **não** tocar home/Quem Somos/rodapé.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T007 Confirmar mapeamento canônico (pedidos verbais → storages `node`) conforme `specs/018-contato-node/research.md` (R2–R4) e `specs/018-contato-node/data-model.md` sob `config/sync/field.storage.node.*`
- [X] T008 [P] Criar Content Type “Contato” (machine name `contato`) em `config/sync/node.type.contato.yml`
- [X] T009 [P] Criar field storage `field_email` (tipo `email`, cardinality 1, entity type `node`) em `config/sync/field.storage.node.field_email.yml`
- [X] T010 [P] Criar field storage `field_phone_wpp` (tipo `string`, max_length 255, cardinality 1, entity type `node`) em `config/sync/field.storage.node.field_phone_wpp.yml`
- [X] T011 Criar field instances do bundle `contato` em `config/sync/field.field.node.contato.webform.yml`, `config/sync/field.field.node.contato.field_imagem.yml`, `config/sync/field.field.node.contato.field_email.yml`, `config/sync/field.field.node.contato.field_phone_wpp.yml`, `config/sync/field.field.node.contato.field_text_simple.yml`, `config/sync/field.field.node.contato.field_text_simple_long.yml`
- [X] T012 Criar form display do Node Contato (title + webform + imagem + e-mail + WhatsApp + textos; UX imagem limitada a 1) em `config/sync/core.entity_form_display.node.contato.default.yml`
- [X] T013 [P] Criar view display do Node Contato (formatter webform que renderiza o form, não só link; campos visíveis para Twig; modo `default` e/ou `full`) em `config/sync/core.entity_view_display.node.contato.default.yml`
- [X] T014 Incluir permissões create/edit/delete do bundle `contato` nas roles editoriais que já gerenciam nodes e preparar diffs em `config/sync/user.role.*.yml` (export final via `cex` em US6)
- [X] T015 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — Content Type **Contato** com 6 fields; storages `field_email`/`field_phone_wpp` em `node`; **sem** alterar webform `contato`, home, Quem Somos ou rodapé

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig node/CSS atualizado; limpeza legado/seed/alias ainda ausentes (hook US6).

---

## Phase 3: User Story 1 — Visitante vê a página de contato no layout do Figma via Node (Priority: P1) 🎯 MVP

**Goal**: viewport ≥992px — duas colunas (form+atalhos | painel `#E5EEFF` + ilustração + textos); H2 “Envie sua mensagem” Poppins negrito; classes `.node--contato` / `.layout-contato-node`; DOM alinhado ao contrato; zero bloco legado na mesma resposta.  
**Independent Test Criteria**: abrir `/contato` ≥992px com Node Contato publicado e comparar estrutura/cores/composição com `contracts/contato-node-render.md` (SC-001, SC-008).

- [X] T016 [US1] Reusar library `layout_contato` → `assets/css/layout-contato.css` (sem library nova) em `themes/custom/default/default.libraries.yml`
- [X] T017 [P] [US1] Atualizar CSS encapsulado (painel `#E5EEFF`; submit `#FD7B1A`; H2 Poppins negrito; **somente** seletores sob `.node--contato` / `.layout-contato-node`; dual-scope temporário opcional até updb) em `themes/custom/default/assets/css/layout-contato.css`
- [X] T018 [US1] Implementar Twig do node (raiz `.node--contato`; inner `.layout-contato-node.container.py-5`; `.row` → `.col-12.col-lg-7` | `.col-12.col-lg-5`; H2 “Envie sua mensagem”; render `webform` + painel com `field_imagem` `.img-fluid` + textos; attach `default/layout_contato`; preservar `attributes`; DOM alinhado a `contracts/contato-node-render.md`) em `themes/custom/default/templates/node/node--contato--full.html.twig`
- [X] T019 [US1] Implementar fallbacks no Twig (omitir webform/`<img>`/textos vazios; painel `#E5EEFF` sem imagem quebrada; sem fatal) em `themes/custom/default/templates/node/node--contato--full.html.twig`
- [X] T020 [US1] Validar SC-001/SC-008 e cenários US1 (desktop duas colunas; título; painel; ausência de `.block-layout-contato`) conforme `specs/018-contato-node/quickstart.md` seção B e `specs/018-contato-node/contracts/contato-node-render.md` (conteúdo de teste manual ou seed US6)

**Checkpoint**: visitante desktop vê o layout Figma via Node (MVP).

---

## Phase 4: User Story 2 — Visitante preenche e envia o formulário (Priority: P1)

**Goal**: webform `contato` (017) renderizado na coluna esquerda via field `webform`; envio válido → confirmação + submission; inválido → feedback; máscara telefone preservada; submit `#FD7B1A`.  
**Independent Test Criteria**: preencher e submeter em `/contato` (SC-003).

- [X] T021 [US2] Confirmar que o view display do Node Contato usa formatter que renderiza o formulário (não só link) e que o webform `contato` permanece inalterado em `config/sync/core.entity_view_display.node.contato.default.yml` e `config/sync/webform.webform.contato.yml`
- [X] T022 [US2] Confirmar render do webform na coluna esquerda do Twig (omit se vazio) em `themes/custom/default/templates/node/node--contato--full.html.twig`
- [X] T023 [P] [US2] Estilizar botão submit “Enviar Mensagem” (fundo `#FD7B1A`, contraste legível) **somente** sob `.node--contato` / `.layout-contato-node` em `themes/custom/default/assets/css/layout-contato.css`
- [X] T024 [US2] Validar SC-003 e cenários US2 (campos 017; envio válido/inválido) conforme `specs/018-contato-node/quickstart.md` seção C

**Checkpoint**: formulário funcional no layout do Node.

---

## Phase 5: User Story 3 — Visitante usa atalhos de E-mail e WhatsApp gerenciáveis (Priority: P1)

**Goal**: faixa abaixo do form com caixas a partir de `field_email` (`mailto:`) e `field_phone_wpp` (`wa.me` + DDI 55 se 10–11 dígitos); omit empty; usável no mobile.  
**Independent Test Criteria**: inspecionar/acionar links em `/contato`; alterar fields no Node e confirmar reflexão (SC-004).

- [X] T025 [US3] Implementar faixa de atalhos (`.layout-contato-node__shortcuts.d-flex.gap-4.mt-4.flex-wrap`; `mailto:` de `field_email`; `wa.me` de `field_phone_wpp` com strip de máscara + prepend `55` se 10–11 dígitos; omit se vazio; ícones envelope/WhatsApp; `target="_blank"` + `rel` no WhatsApp) em `themes/custom/default/templates/node/node--contato--full.html.twig`
- [X] T026 [P] [US3] Ajustar CSS dos atalhos (caixas bordadas; wrap/empilhamento mobile sem overflow) sob `.node--contato` / `.layout-contato-node` em `themes/custom/default/assets/css/layout-contato.css`
- [X] T027 [US3] Validar SC-004 / cenários US3 (seed `mailto:contato@eusouestagio.com` + `https://wa.me/5561999999999`; omit empty) conforme `specs/018-contato-node/quickstart.md` seção D e `specs/018-contato-node/contracts/contato-node-render.md`

**Checkpoint**: canais gerenciáveis pelo Node funcionam.

---

## Phase 6: User Story 4 — Visitante mobile vê colunas empilhadas (Priority: P1)

**Goal**: viewport ≤575.98px — formulário acima do painel; inputs/atalhos usáveis; sem scroll horizontal do layout do node.  
**Independent Test Criteria**: `/contato` ≤575.98px (SC-002).

- [X] T028 [US4] Confirmar/ajustar CSS mobile (empilhamento Bootstrap `col-12`; sem overflow-x; tipografia/paddings legíveis) sob `.node--contato` / `.layout-contato-node` em `themes/custom/default/assets/css/layout-contato.css`
- [X] T029 [US4] Confirmar classes Bootstrap das colunas (`.col-12.col-lg-7` / `.col-12.col-lg-5`) e ordem DOM (form antes do painel) no Twig em `themes/custom/default/templates/node/node--contato--full.html.twig`
- [X] T030 [US4] Validar SC-002 / cenários US4 conforme `specs/018-contato-node/quickstart.md` seção E

**Checkpoint**: experiência mobile estável.

---

## Phase 7: User Story 5 — Editor gerencia a página Contato sem código (Priority: P2)

**Goal**: editor autenticado edita webform, imagem, e-mail, WhatsApp e textos da coluna direita no Node Contato; mudanças refletem em `/contato` sem deploy (&lt; 3 min).  
**Independent Test Criteria**: editar Node no painel, salvar, recarregar `/contato` (SC-005).

- [X] T031 [US5] Revisar form display (widgets webform + image + email + string; labels claros; UX 1 imagem) em `config/sync/core.entity_form_display.node.contato.default.yml`
- [X] T032 [US5] Confirmar permissões do bundle `contato` nas roles exportadas sob `config/sync/user.role.*.yml` (alinhado a T014)
- [X] T033 [US5] Confirmar que webform, imagem, e-mail, WhatsApp e textos vêm dos fields do Node (não hardcoded como única fonte; H2 “Envie sua mensagem” permanece hardcoded) em `themes/custom/default/templates/node/node--contato--full.html.twig`
- [X] T034 [US5] Validar SC-005 / cenários US5 conforme `specs/018-contato-node/quickstart.md` seção F

**Checkpoint**: página Contato editável sem código.

---

## Phase 8: User Story 6 — Deploy limpa legado e provisiona Node Contato (Priority: P1)

**Goal**: `custom_configs_update_11023` idempotente — (1) delete bloco ID 16 + seed `layout_contato` UUID `d1e2f3a4-…` se existirem; (2) desabilitar/remover placement `default_layoutcontato`; (3) ensure Content Type + storages novos + instances + displays; (4) seed Node Contato UUID `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1` (asset → `public://`, valores Figma) só se ausente/campos vazios; (5) reassociar alias `/contato` ao Node Contato (remover alias da página `page` `f3a4b5c6-…` sem apagar o node); fluxo `cim` → `updb` → `cim` → `cr`; `cex` versiona estrutural; **nunca** sobrescrever editorial divergente nem duplicar.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; 2ª `updb` sem duplicatas; `/contato` = Node Contato com 1 form (SC-006, SC-007, SC-008).

- [X] T035 [US6] Implementar `custom_configs_update_11023` idempotente (delete ID 16 + UUID `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6` se existirem; desabilitar/remover `default_layoutcontato`; ensure type `contato` + storages `field_email`/`field_phone_wpp` + instances + displays; copiar asset → `public://`; seed Node UUID `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1` com webform `contato` + imagem + e-mail `contato@eusouestagio.com` + WhatsApp `(61) 99999-9999` + textos Figma **somente se** ausente/campos vazios; reassociar alias `/contato` ao Node Contato; **nunca** sobrescrever editorial divergente; **nunca** duplicar) em `modules/custom/custom_configs/custom_configs.install`
- [X] T036 [P] [US6] Extrair/reusar helpers privados de limpeza legado / ensure structure / seed Node / alias no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T037 [US6] Atualizar config do placement legado (`status: false` e/ou remoção) em `config/sync/block.block.default_layoutcontato.yml` para `/contato` não renderizar `layout_contato`
- [X] T038 [US6] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/018-contato-node/plan.md` / `data-model.md` sob `config/sync/` (node type, storages novos, instances, displays, placement, `user.role.*`)
- [X] T039 [US6] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-006/SC-007/SC-008) conforme `specs/018-contato-node/quickstart.md` seções A e G
- [X] T040 [US6] Validar limpeza e isolamento pós-seed (ID 16 ausente; um único form; placement inativo; fallbacks omit empty; SC-009 amostragem home/Quem Somos/rodapé) conforme `specs/018-contato-node/quickstart.md` seções G e H

**Checkpoint**: deploy 100% automatizado; `/contato` reproduzível via Node Contato.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, specify-rules, isolamento visual e aceite final SC-001–SC-009.

- [X] T041 [P] Atualizar §3.1/§3.6 + §10 documentando Content Type `contato`, fields canônicos, rota `/contato` via Node, aposentadoria do bloco/placement na página, hook `11023`, library `layout_contato` em `PRD.md`
- [X] T042 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `018-contato-node` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T043 Executar validação final completa (SC-001–SC-009; checklist aceite seções B–H) com `specs/018-contato-node/quickstart.md` e `specs/018-contato-node/checklists/requirements.md`
- [X] T044 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US2 depende do view display foundational (T013) + Twig base US1 (T018); aceite completo após seed US6 ou conteúdo manual
- US3 depende do Twig base US1 (T018) + fields `field_email`/`field_phone_wpp` (T009–T011)
- US4 depende do markup/CSS de US1 (T017–T018)
- US5 depende de form display (T012) + Twig com fields (T018–T019, T025); ideal após seed US6
- US6 (hook/cex/limpeza) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T018)
- Polish após US1–US6

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US3 (P1)
                    ├→ US4 (P1)
                    ├→ US5 (P2)
                    └→ US6 (P1) ──→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 em paralelo após T001; T006 após T001
- **Foundational**: T008 ∥ T009 ∥ T010 após T007; T011 após T008+T009+T010; T012/T013 após T011; T014 ∥ T013; T015 por último
- **US1**: T017 (CSS) em paralelo com início de T018 após T016; T019 após markup estável; T020 por último
- **US2**: T021–T022 após T013+T018; T023 após T017; T024 por último
- **US3**: T025–T026 após T018; T027 por último
- **US4**: T028–T029 após T017–T018; T030 por último
- **US2 ∥ US3 ∥ US4**: após T018 (e T013 para US2)
- **US5**: após T012 + T018 + T025; melhor com seed US6
- **US6**: T035–T036; T037 ∥ início T035; T038–T040 após hook
- **Polish**: T041 ∥ T042; depois T043–T044

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (layout desktop Figma via Node em `/contato`) — valor principal
2. **Form + atalhos + mobile**: US2 + US3 + US4
3. **Editorial + deploy**: US5 (form/perms) + US6 (`11023` limpeza + seed + alias + cim/updb/cim/cr + cex)
4. **Fechamento**: Polish (PRD + specify-rules + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T044`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1–US4 e US6 (P1) → US5 (P2); US1 = MVP
