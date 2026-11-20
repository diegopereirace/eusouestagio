# Tasks: Detalhe da Vaga — Layout Duas Colunas

**Input**: Artefatos de design em `specs/030-vagas-detalhe-layout/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/vagas-detalhe-render.md`, `contracts/deploy-vagas-detalhe.md`, `quickstart.md`  
**Branch**: `feature-vaga` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/030-vagas-detalhe-layout` (via `.specify/feature.json`); template alinhado a `specs/028-vagas-lista-vertical/tasks.md`  
**Hook**: `custom_configs_update_11047` (último existente: `11045`; `11046` reservado/opcional pela 029)

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig/CSS/preprocess atuais do detalhe, ausência de CT `faq` / campos novos / paragraph, e número do hook antes de implementar.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/vagas-detalhe-render.md`, `contracts/deploy-vagas-detalhe.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/030-vagas-detalhe-layout/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig atual do detalhe (grid 8/4 legado, listas requisitos/benefícios via `field_text_simple_multiple` / `_2`, sidebar `js-candidatar-vaga` / `js-salvar-vaga` / share; ausência de stepper/FAQ/CTA Figma/resumo/perfil) em `themes/custom/default/templates/content/node--vagas.html.twig`
- [X] T003 [P] Inventariar preprocess `default_preprocess_node__vagas` (`vaga_salva` / `vaga_candidatada`) e attach de `default/script-painel` em `themes/custom/default/default.theme` e no Twig atual
- [X] T004 [P] Confirmar ausência de CT `faq`, `field.storage.node.field_resposta`, `field_vaga_*` e paragraph `beneficio_vaga_p` sob `config/sync/node.type.*.yml`, `config/sync/field.storage.node.*`, `config/sync/paragraphs.paragraphs_type.*.yml`
- [X] T005 [P] Confirmar reuso disponível de storages paragraph `field_image` / `field_text_simple` e esgotamento no bundle `vagas` de `field_text_simple_multiple` / `_2` (justifica storages novos) em `config/sync/field.storage.paragraph.*` e `config/sync/field.field.node.vagas.*`
- [X] T006 Confirmar último hook `custom_configs_update_11045` → próximo livre desta feature **`11047`** (`11046` reservado 029) em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 [P] Inventariar superfícies intocáveis da listagem (`views-view-field--vagas--page-1|block-1|block-2|block-3--nothing.html.twig`, `vagas-lista-vertical.css`, library `vagas_lista_vertical`, Hero 027) sob `themes/custom/default/templates/views/` e `themes/custom/default/assets/css/components/`

**Checkpoint**: inventário alinhado a plan/research/data-model; hook alvo = `11047`; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar isolamento da listagem/cards laranja/Hero 027, exclusão de Match, omissão de “Ver Empresa”, perfil % estático e alvos fixos. Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Congelar escopo negativo da listagem/cards: **não** editar Twigs `views-view-field--vagas--page-1--nothing.html.twig`, `block-1`/`block-2`/`block-3`, CSS `vagas-lista-vertical.css`, library `vagas_lista_vertical`, nem regras `.item-vaga--destaque` / `.item-vaga--lista` sob `themes/custom/default/`
- [X] T009 [P] Congelar escopo negativo de produto: **não** implementar “Seu Match com a vaga” / “Por que combina com você?” (markup, CSS ou campos); **não** criar rota pública de empresa nem botão “Ver Empresa”; **não** inventar serviço de completude de perfil — ver `specs/030-vagas-detalhe-layout/research.md` (R7/R8) e `contracts/vagas-detalhe-render.md`
- [X] T010 [P] Congelar dependências existentes a reutilizar: classes `js-candidatar-vaga` / `js-salvar-vaga`, library `default/script-painel`, share WhatsApp/Facebook/LinkedIn, preprocess de estados — sem endpoints novos em `themes/custom/default/` e `modules/custom/custom_candidaturas/` / `custom_panel`
- [X] T011 Documentar alvos fixos a implementar (CT `faq` + `field_resposta`; paragraph `beneficio_vaga_p`; fields `field_vaga_faq` / `field_vaga_etapas_processo` / `field_vaga_requisitos` / `field_vaga_beneficios`; Twig `node--vagas--full.html.twig`; library `default/vagas_detalhe`; CSS `vagas-detalhe.css`; hook `11047` + migrate opcional; seeds UUID FAQ) em `specs/030-vagas-detalhe-layout/data-model.md` / `contracts/` — sem criar YAML ainda

**Checkpoint**: restrições claras; pronto para Twig/CSS isolados + ensures/hook sem risco de regressão `/vagas`/Home/PE.

---

## Phase 3: User Story 1 — Visitante vê o detalhe da vaga em duas colunas (Priority: P1) 🎯 MVP

**Goal**: template full Figma com container + row 8/4, raiz `.vaga-detalhe`, library/CSS dedicados e ausência total de Match — shell visual principal.  
**Independent Test Criteria**: abrir node `vagas` publicado desktop ≥992px — grid col-8/col-4; mobile empilhado; zero “Seu Match…” / “Por que combina…” (SC-001 / SC-005 / quickstart B).

- [X] T012 [US1] Criar `themes/custom/default/templates/content/node--vagas--full.html.twig` com shell Figma: `article` + classes Drupal + `.vaga-detalhe`; `.container.py-5` + `.row.g-4`; coluna `.col-12.col-lg-8` (placeholders de seções) + aside `.col-12.col-lg-4` (placeholders); `attach_library('default/script-painel')` + `attach_library('default/vagas_detalhe')`; **sem** Match — conforme `specs/030-vagas-detalhe-layout/contracts/vagas-detalhe-render.md`
- [X] T013 [US1] Registrar library `vagas_detalhe` → `assets/css/components/vagas-detalhe.css` (deps Bootstrap alinhadas ao tema) em `themes/custom/default/default.libraries.yml`
- [X] T014 [P] [US1] Criar CSS base encapsulado (tokens `#023C62` / `#FD7B1A` / fundo resumo `#F3F6F9`; layout colunas; gaps; **somente** sob `.vaga-detalhe` / `.node--type-vagas.node--view-mode-full`) em `themes/custom/default/assets/css/components/vagas-detalhe.css`
- [X] T015 [US1] Garantir attach da library no full (Twig e/ou preprocess) e que view modes ≠ `full` **não** usam o markup Figma; ajustar fallback mínimo de `themes/custom/default/templates/content/node--vagas.html.twig` sem afetar Views
- [X] T016 [US1] Validar SC-001 / SC-005 / cenários US1 (grid 8/4; mobile empilhado sem overflow-x; ausência Match) conforme `specs/030-vagas-detalhe-layout/quickstart.md` seção B (após `drush cr`)

**Checkpoint**: visitante vê shell duas colunas — MVP visual (seções de dados nas fases seguintes).

---

## Phase 4: User Story 2 — Visitante lê header, descrição, requisitos e benefícios (Priority: P1)

**Goal**: paragraph `beneficio_vaga_p` + campos `field_vaga_requisitos` / `field_vaga_beneficios`; header Figma; Sobre; requisitos com checks; benefícios em grid 2/4; seções omitidas se vazias.  
**Independent Test Criteria**: preencher campos novos + descrição → página alinhada ao Figma; esvaziar campo → seção some (SC-002 / quickstart C).

- [X] T017 [US2] Implementar helper `_custom_configs_ensure_beneficio_vaga_p()` (tipo paragraph + instances `field_image` / `field_text_simple` reutilizando storages existentes + form/view displays; reexecução = no-op) em `modules/custom/custom_configs/custom_configs.install`
- [X] T018 [P] [US2] Implementar helper `_custom_configs_ensure_field_vaga_requisitos_beneficios()` (storages novos `string`/`entity_reference_revisions` card. `-1` + instances no bundle `vagas` para `field_vaga_requisitos` e `field_vaga_beneficios` → `beneficio_vaga_p`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T019 [US2] Completar coluna principal no Twig full: header (logo, h1, empresa, local, badges regime/carga/bolsa/“Postado há…”), seção Sobre (`field_text_long_formatted`), Requisitos (`field_vaga_requisitos` + checks), Benefícios (grid paragraphs ícone+título); omitir seções vazias em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T020 [P] [US2] Criar Twig opcional do paragraph (ícone + título) em `themes/custom/default/templates/paragraphs/paragraph--beneficio-vaga-p.html.twig` **ou** render inline no node full — escolher um caminho e manter BEM `.vaga-detalhe__*`
- [X] T021 [P] [US2] Estilizar checks azuis de requisitos + grid de benefícios (2 cols mobile / até 4 desktop; cards brancos borda sutil) sob `.vaga-detalhe` em `themes/custom/default/assets/css/components/vagas-detalhe.css`
- [X] T022 [US2] Validar SC-002 / cenários US2 (header/sobre/requisitos/benefícios; omit empty; edges sem logo) conforme `specs/030-vagas-detalhe-layout/quickstart.md` seção C (após fields via US7/`updb` ou ensure local + `drush cr`)

**Checkpoint**: blocos informativos principais do Figma operacionais sobre dados preenchidos.

---

## Phase 5: User Story 3 — Visitante entende o processo de contratação (Priority: P1)

**Goal**: `field_vaga_etapas_processo` + stepper numerado; último círculo `#FD7B1A`; seção omitida se vazia.  
**Independent Test Criteria**: cadastrar ≥2 etapas → círculos 1…N e último laranja (SC-004 / quickstart D).

- [X] T023 [US3] Implementar helper `_custom_configs_ensure_field_vaga_etapas_processo()` (storage `string` card. `-1` + instance no bundle `vagas`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T024 [US3] Implementar seção “Processo de Contratação” no Twig full (N círculos numerados + labels + conector; omitir se vazio) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T025 [P] [US3] Estilizar stepper (último círculo `#FD7B1A`; linha conectora; mobile wrap/scroll-x discreto; 1 etapa ainda com destaque no único/último) em `themes/custom/default/assets/css/components/vagas-detalhe.css`
- [X] T026 [US3] Validar SC-004 / cenários US3 (N≥2 último laranja; vazio omitido; muitas etapas legíveis) conforme `specs/030-vagas-detalhe-layout/quickstart.md` seção D

**Checkpoint**: stepper visual e estrutural pronto.

---

## Phase 6: User Story 4 — Visitante consulta FAQ da vaga em accordion (Priority: P1)

**Goal**: CT `faq` + `field_resposta` + 2 seeds UUID + `field_vaga_faq` + Accordion Bootstrap 5; IDs únicos; seção omitida se vazia.  
**Independent Test Criteria**: associar 2 FAQs seed → expandir/colapsar; 2ª `updb` não duplica seeds (SC-003 / SC-008 / quickstart E).

- [X] T027 [US4] Implementar helper `_custom_configs_ensure_faq_structure()` (node type `faq` label “FAQ”; storage/instance `field_resposta` `text_long`; form/view displays title+resposta; sem menu) em `modules/custom/custom_configs/custom_configs.install`
- [X] T028 [US4] Implementar helper `_custom_configs_seed_faq_examples()` (2 nodes UUID `b1c2d3e4-f5a6-4789-a012-3456789abc01` / `…abc02`; perguntas Figma; respostas placeholder pt-BR; load por UUID; não sobrescrever editorial divergente) em `modules/custom/custom_configs/custom_configs.install` conforme `specs/030-vagas-detalhe-layout/data-model.md`
- [X] T029 [P] [US4] Implementar helper `_custom_configs_ensure_field_vaga_faq()` (storage ER → `node` bundle `faq` card. `-1` + instance no bundle `vagas`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T030 [US4] Implementar seção “Dúvidas Frequentes” com Accordion BS5 (pergunta=`title`; resposta=`field_resposta`; IDs `vaga-faq-{vaga_nid}-{faq_nid}`; omitir se vazio; respeitar access unpublished) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T031 [P] [US4] Ajustar CSS mínimo do accordion/seção FAQ sob `.vaga-detalhe__faq` em `themes/custom/default/assets/css/components/vagas-detalhe.css` (sem JS custom)
- [X] T032 [US4] Validar SC-003 / SC-008 / cenários US4 (accordion mouse+teclado; omit empty; seeds existem sem duplicata após re-`updb`) conforme `specs/030-vagas-detalhe-layout/quickstart.md` seção E

**Checkpoint**: FAQ estrutural + accordion público operacionais.

---

## Phase 7: User Story 5 — Visitante vê empresa, CTA final e age na sidebar (Priority: P1)

**Goal**: card empresa (sem “Ver Empresa”), CTA final `#023C62`, sidebar ações/resumo/perfil estático; fluxos candidatura/salvar/share intactos.  
**Independent Test Criteria**: comparar sidebar/CTA com fluxos atuais; card empresa truncado; resumo/perfil presentes (quickstart F).

- [X] T033 [US5] Implementar seção “Sobre a Empresa” (logo/nome/`field_sobre_empresa` truncado ~160–200 / line-clamp; **sem** botão “Ver Empresa”; omitir se sem `field_empresa_u`) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T034 [US5] Implementar CTA final fixo “Pronto para o próximo passo?” (fundo `#023C62`; botão laranja “Candidatar-se Agora” com mesmas classes/estado `js-candidatar-vaga` da sidebar) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T035 [US5] Portar/adaptar card de ações da sidebar (Candidatar-se largo + Salvar + Compartilhar; estados `vaga_candidatada` / `vaga_salva`; anônimo → login) a partir do markup atual de `themes/custom/default/templates/content/node--vagas.html.twig` para o full
- [X] T036 [US5] Implementar “Resumo da Vaga” (Período←`field_horarios`; Bolsa←`field_text_simple`; Modelo←`field_regime_t`; Vagas←“Não informado”; fundo `#F3F6F9`) e “Seu Perfil” (barra + % default 75 + “Completar agora” → `/painel/estudante/perfil` ou login+destination) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T037 [P] [US5] Expor variável opcional `vaga_perfil_completo` (default 75 se ausente) em `default_preprocess_node__vagas` em `themes/custom/default/default.theme` sem calcular completude real
- [X] T038 [P] [US5] Estilizar cards empresa/CTA/ações/resumo/perfil sob `.vaga-detalhe` em `themes/custom/default/assets/css/components/vagas-detalhe.css`
- [X] T039 [US5] Validar cenários US5 (empresa sem Ver Empresa; CTA; ações; resumo; perfil; anônimo) conforme `specs/030-vagas-detalhe-layout/quickstart.md` seção F

**Checkpoint**: funil de conversão + sidebar Figma fechados sem Match.

---

## Phase 8: User Story 6 — Editor gerencia FAQ, etapas, requisitos e benefícios (Priority: P1)

**Goal**: form/view displays de `vagas` (e FAQ) expõem os novos campos editáveis; alterações refletem na página pública.  
**Independent Test Criteria**: criar/associar FAQ; preencher etapas/requisitos/benefícios; salvar e reabrir (SC-006 / quickstart G editorial).

- [X] T040 [US6] Estender ensures para form display `default` de `node.vagas` (widgets: ER FAQ; text multi etapas/requisitos; paragraphs benefícios) e view display adequado (hidden no view se Twig lê entity) em `modules/custom/custom_configs/custom_configs.install`
- [X] T041 [US6] Confirmar form/view displays de `faq` (title + `field_resposta`) e displays de `beneficio_vaga_p` (ícone + título) nos helpers T017/T027 — completar gaps se necessário em `modules/custom/custom_configs/custom_configs.install`
- [X] T042 [US6] Validar SC-006 / cenários US6 (campos no form da vaga; FAQ + paragraphs refletem no full após salvar/`drush cr`) conforme `specs/030-vagas-detalhe-layout/quickstart.md` seção G (após estrutura via US7/`updb` ou ensure local)

**Checkpoint**: editorial opera os blocos Figma sem admin de estrutura.

---

## Phase 9: User Story 7 — Deploy automatizado sem passos manuais (Priority: P1)

**Goal**: `custom_configs_update_11047` idempotente (estrutura + seeds + migrate opcional); origem `drush cex`; destino `cim`→`updb`→`cim`→`cr`.  
**Independent Test Criteria**: fluxo de deploy + 2ª `updb` — SC-007 / SC-008 / quickstart A+G / contrato deploy.

- [X] T043 [US7] Implementar helper `_custom_configs_migrate_vaga_requisitos_beneficios_legado()` (se novos vazios e legados `field_text_simple_multiple` / `_2` tiverem valor → copiar itens/títulos; benefícios sem ícone; nunca apagar legado nem sobrescrever destino preenchido) em `modules/custom/custom_configs/custom_configs.install`
- [X] T044 [US7] Implementar `custom_configs_update_11047` (chamar ensures CT FAQ + seed + paragraph + 4 fields + displays + migrate; **não** auto-associar FAQs a todas as vagas; **não** alterar View `vagas` listagem / Hero 027 / cards laranja; retornar mensagem Drush; idempotente) em `modules/custom/custom_configs/custom_configs.install`
- [X] T045 [US7] Exportar configs estruturais com `drush cex -y` — versionar tipicamente `config/sync/node.type.faq.yml`, `field.storage.node.field_resposta.yml`, fields/displays FAQ, `paragraphs.paragraphs_type.beneficio_vaga_p.yml` + fields/displays, `field.storage.node.field_vaga_*.yml`, `field.field.node.vagas.field_vaga_*.yml`, `core.entity_form_display.node.vagas.default.yml`, `core.entity_view_display.node.vagas.default.yml` (+ full se houver); **não** inventar YAML à mão; revisar diff para **não** regredir listagem/Hero
- [X] T046 [US7] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (idempotência SC-008) conforme `specs/030-vagas-detalhe-layout/quickstart.md` seções A e G e `contracts/deploy-vagas-detalhe.md`
- [X] T047 [US7] Validar gates pós-deploy (CT FAQ + 2 seeds; paragraph; 4 fields no form; full Figma 8/4; stepper/accordion; ações intactas; listagem intacta; SC-007) conforme contrato de deploy e quickstart G

**Checkpoint**: destino reproduz estrutura + layout sem admin manual; hook reentrante seguro.

---

## Phase 10: Polish & Cross-Cutting Concerns

**Objetivo**: PRD §3.1 cirúrgico, specify-rules, isolamento listagem/cards, aceite final SC-001–SC-009.

- [X] T048 [P] Atualizar `PRD.md` §3.1 de forma cirúrgica (CT `faq` + `field_resposta`; paragraph `beneficio_vaga_p`; campos `field_vaga_*` em `vagas`; menção hook **`11047`** / detalhe full) — manter bullets listagem `/vagas` (028/029) e cards laranja intactos
- [X] T049 [P] Confirmar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) aponta feature `030-vagas-detalhe-layout` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T050 [P] Amostrar regressão: `/vagas` listagem vertical + Hero 027; Home/`block_3`/PE cards laranja `.item-vaga--destaque` intactos; zero Match no detalhe
- [X] T051 Executar validação final completa (SC-001–SC-009; checklist deploy; edges logo/empresa/etapas/FAQ unpublished/benefício sem ícone) com `specs/030-vagas-detalhe-layout/quickstart.md` e `checklists/requirements.md`
- [X] T052 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → US7 (Phase 9) → Polish (Phase 10)
- US2 depende do shell Twig/CSS de US1 (T012/T014) e dos helpers paragraph/fields (T017/T018); aceite completo de dados após US7/`updb`
- US3 depende do shell US1; field etapas (T023) paralelo ao CSS após Twig
- US4 depende do shell US1; CT/seed/field FAQ (T027–T029) antes do accordion Twig (T030); idempotência seeds fecha com US7
- US5 depende do shell US1 + ações existentes (T010); pode avançar em paralelo a US3/US4 no Twig após T012
- US6 estende displays dos ensures US2–US4; aceite editorial após estrutura existir
- US7 (hook + migrate + cex + deploy) precisa dos helpers T017/T018/T023/T027/T028/T029/T040/T043; desbloqueia aceite US2–US6 em destino limpo
- Polish após US1–US7

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) MVP shell 8/4 + library/CSS
                    ├→ US2 (P1) requisitos/benefícios + header/sobre ──┐
                    ├→ US3 (P1) etapas + stepper ─────────────────────┤
                    ├→ US4 (P1) CT faq + seeds + accordion ───────────┤
                    ├→ US5 (P1) empresa + CTA + sidebar ──────────────┤
                    ├→ US6 (P1) form displays editoriais ─────────────┤
                    └→ US7 (P1) hook 11047 + migrate + cex ───────────┴→ Polish
                         ↑
                         └── usa helpers T017/T018/T023/T027–T029/T040/T043
```

## Parallel Execution Opportunities

- **Setup**: T002–T005, T007 em paralelo após T001; T006 após T001
- **Foundational**: T008–T010 em paralelo; T011 após T008–T010
- **US1**: T013 paralelo a T012; T014 após T013; T015 após T012+T013; T016 por último
- **US2**: T017 e T018 em paralelo; T019 após T012; T020/T021 em paralelo após T019; T022 após fields (ideal pós-US7)
- **US3**: T023 paralelo a T017/T018; T024 após T012; T025 paralelo a T024; T026 por último
- **US4**: T027 → T028; T029 paralelo a T027; T030 após T012+T029; T031 paralelo a T030; T032 por último
- **US5**: T033–T036 em sequência no Twig após T012; T037/T038 paralelos; T039 por último
- **US6**: T040/T041 após ensures US2–US4; T042 após estrutura
- **US7**: T043 paralelo a finalização dos ensures; T044 após helpers → T045 → T046 → T047
- **Polish**: T048–T050 em paralelo; depois T051–T052

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (Twig `--full` shell 8/4 + library/CSS + sem Match) — valor visual principal
2. **Conteúdo Figma**: US2 (header/sobre/requisitos/benefícios) + US3 (stepper) + US4 (FAQ) + US5 (empresa/CTA/sidebar)
3. **Editorial + deploy**: US6 (displays) + US7 (`11047` + migrate + cex + `cim`→`updb`→`cim`→`cr`)
4. **Fechamento**: Polish (PRD §3.1 + specify-rules + isolamento + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T052`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1 → US2 → US3 → US4 → US5 → US6 → US7 (todas P1; deploy por último para fechar aceite)
