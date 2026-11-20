# Tasks: Detalhe da Vaga — Layout Duas Colunas (Refino)

**Input**: Artefatos de design em `specs/031-vagas-detalhe-refino/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/vagas-detalhe-refino-render.md`, `contracts/deploy-vagas-detalhe-refino.md`, `quickstart.md`  
**Branch**: `feature-vaga` (ou branch ativa alinhada à feature)  
**Predecessor**: `030-vagas-detalhe-layout` (shell 8/4, FAQ 1:1, stepper/perfil/CTA inline — a refinar)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/031-vagas-detalhe-refino` (via `.specify/feature.json`); template alinhado a `specs/030-vagas-detalhe-layout/tasks.md`  
**Hook**: `custom_configs_update_11048` (último existente: `11047`)

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar estado pós-030 (Twig full com processo/perfil/CTA inline; FAQ 1:1; requisitos string multi; ausência de `faq_item_p` / CTA placement vagas) antes de implementar.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/vagas-detalhe-refino-render.md`, `contracts/deploy-vagas-detalhe-refino.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/031-vagas-detalhe-refino/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar Twig full atual (grid 8/4; seções processo/perfil/CTA inline `.vaga-detalhe__cta`; FAQ via `title`+`field_resposta` node; sidebar ações/resumo) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T003 [P] Inventariar CSS detalhe (regras `__processo` / `__cta` / `__perfil`; checks requisitos; grid benefícios) em `themes/custom/default/assets/css/components/vagas-detalhe.css` e library `vagas_detalhe` em `themes/custom/default/default.libraries.yml`
- [X] T004 [P] Inventariar preprocess `default_preprocess_node__vagas` (`vaga_salva` / `vaga_candidatada` / `vaga_perfil_completo` / `vaga_postado_ha`) em `themes/custom/default/default.theme`
- [X] T005 [P] Confirmar estado estrutural 030: CT `faq` + `field_resposta`; seeds UUID `…abc01`/`…abc02`; `field_vaga_faq` card. `-1`; `field_vaga_requisitos` storage `string` card. `-1`; `beneficio_vaga_p` + `field_vaga_beneficios`; ausência de `faq_item_p` / `field_faq_itens` sob `config/sync/`
- [X] T006 Confirmar último hook `custom_configs_update_11047` → próximo livre desta feature **`11048`** em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 [P] Inventariar padrão CTA escuro isolado (026) e placement por bundle (`default_vagasvoltar` / `entity_bundle:node`) em `themes/custom/default/templates/block/block--default-ctav1paraestudantes.html.twig`, `themes/custom/default/assets/css/cta-v1-para-estudantes.css` e `config/sync/block.block.default_vagasvoltar.yml` — referência; **não** mutar nesta fase
- [X] T008 [P] Inventariar superfícies intocáveis (Twigs Views `views-view-field--vagas--*--nothing.html.twig`, `vagas-lista-vertical.css`, Hero 027, Twig/CSS CTA claro global, CTA PE estudantes) sob `themes/custom/default/`

**Checkpoint**: inventário alinhado a plan/research/data-model; hook alvo = `11048`; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: travar isolamento listagem/cards/CTAs claros, exclusões de produto (Match/Processo/Perfil/CTA inline), omissão “Ver Empresa”, reuso benefícios/`field_text_simple`, e alvos fixos (UUIDs / hook). Pré-requisito de todas as user stories.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T009 Congelar escopo negativo da listagem/cards/Hero: **não** editar Twigs `views-view-field--vagas--page-1|block-1|block-2|block-3--nothing.html.twig`, CSS `vagas-lista-vertical.css`, library `vagas_lista_vertical`, Hero 027, nem regras `.item-vaga--destaque` / `.item-vaga--lista` sob `themes/custom/default/`
- [X] T010 [P] Congelar escopo negativo de CTAs: **não** editar `block--block-cta-v1.html.twig`, `cta-v1.css`, Twigs/CSS `default_ctav1paraestudantes*` / `cta-v1-para-estudantes.css`, placements QS/PE empresas sob `themes/custom/default/` e `config/sync/block.block.*`
- [X] T011 [P] Congelar escopo negativo de produto: **não** implementar Match / “Por que combina”; **não** criar rota pública de empresa nem botão “Ver Empresa”; **não** criar storage `field_text_simple_small` (usar `field_text_simple`); **não** deletar storage `field_vaga_etapas_processo` nesta feature — ver `specs/031-vagas-detalhe-refino/research.md` (R6/R7/R9)
- [X] T012 [P] Congelar dependências a reutilizar: classes `js-candidatar-vaga` / `js-salvar-vaga`, library `default/script-painel`, share existente, preprocess de estados candidatura/salva — sem endpoints novos em `themes/custom/default/` e `modules/custom/custom_candidaturas/` / `custom_panel`
- [X] T013 Documentar alvos fixos a implementar (paragraph `faq_item_p` + storages `paragraph.field_pergunta`/`field_resposta`; `field_faq_itens`; seed FAQ UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0`; `field_vaga_faq` card. 1; recreate `field_vaga_requisitos` text_long; CTA UUID `d4e5f6a7-b8c9-4012-c345-6789abcdef01`; placement `default_ctav1vagas` UUID `e5f6a7b8-c9d0-4123-d456-789abcdef012`; hook `11048`) em `specs/031-vagas-detalhe-refino/data-model.md` / `contracts/` — sem criar YAML ainda

**Checkpoint**: restrições claras; pronto para Twig/CSS refino + ensures/hook sem risco de regressão `/vagas`/Home/PE/CTAs claros.

---

## Phase 3: User Story 1 — Visitante vê o detalhe em duas colunas sem seções excluídas (Priority: P1) 🎯 MVP

**Goal**: manter grid 8/4; remover do DOM Processo, Seu Perfil, CTA inline e quaisquer resquícios Match; limpar CSS órfão dessas seções.  
**Independent Test Criteria**: abrir node `vagas` publicado desktop ≥992px — grid col-8/col-4; mobile empilhado; zero Match/Processo/“Por que combina”/Seu Perfil/CTA inline (SC-001 / SC-004 / quickstart B).

- [X] T014 [US1] Remover do Twig full as seções `vaga-detalhe__processo`, `vaga-detalhe__perfil`, `vaga-detalhe__cta` e o uso de `vaga_perfil_completo` / `field_vaga_etapas_processo` no markup; manter shell `.vaga-detalhe` + `.row` col-lg-8 / col-lg-4 em `themes/custom/default/templates/content/node--vagas--full.html.twig` conforme `contracts/vagas-detalhe-refino-render.md`
- [X] T015 [P] [US1] Remover regras CSS órfãs de `__processo` / `__cta` / `__perfil` (e variantes) em `themes/custom/default/assets/css/components/vagas-detalhe.css` sem tocar seletores de listagem/cards
- [X] T016 [P] [US1] Remover ou tornar no-op o default `vaga_perfil_completo` em `default_preprocess_node__vagas` se só servia ao card removido, em `themes/custom/default/default.theme` (preservar `vaga_salva` / `vaga_candidatada` / `vaga_postado_ha`)
- [X] T017 [US1] Validar SC-001 / SC-004 / cenários US1 (grid 8/4; mobile empilhado sem overflow-x; ausência total seções excluídas + CTA inline) conforme `specs/031-vagas-detalhe-refino/quickstart.md` seção B (após `drush cr`)

**Checkpoint**: visitante vê shell duas colunas limpo — MVP visual do refino.

---

## Phase 4: User Story 2 — Visitante lê header, descrição, requisitos, benefícios e empresa (Priority: P1)

**Goal**: header/sobre intactos; requisitos como Text long formatted com checks CSS; benefícios via `beneficio_vaga_p` (`field_image` + `field_text_simple`); card empresa sem “Ver Empresa”; seções omitidas se vazias.  
**Independent Test Criteria**: preencher campos + comparar Figma; esvaziar campo → seção some (SC-002 / quickstart C).

- [X] T018 [US2] Implementar helper `_custom_configs_migrate_recreate_field_vaga_requisitos_text_long()` (ler string multi → HTML `<ul><li>` escapado; remover instance+storage string; recreate storage `text_long` card. 1 + instance + displays; gravar migrado só se destino vazio; formato `basic_html` ou equivalente de `field_text_long_formatted`) em `modules/custom/custom_configs/custom_configs.install` conforme `data-model.md` / research R5
- [X] T019 [US2] Confirmar/ajustar Twig full: header (logo, h1, empresa, local, badges); Sobre (`field_text_long_formatted`); Requisitos renderizando HTML de `field_vaga_requisitos` com wrapper BEM para checks; Benefícios grid paragraphs; omit empty — em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T020 [US2] Confirmar card “Sobre a Empresa” (logo/nome/`field_sobre_empresa` truncado; **sem** botão/link “Ver Empresa”; omitir se sem `field_empresa_u`) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T021 [P] [US2] Ajustar CSS de requisitos (checks azuis em `ul`/`ol`/`p` dentro de `.vaga-detalhe__requisitos`; sem bullets padrão) e confirmar grid benefícios 2/4 sob `.vaga-detalhe` em `themes/custom/default/assets/css/components/vagas-detalhe.css`
- [X] T022 [P] [US2] Confirmar que `beneficio_vaga_p` continua com `field_image` + `field_text_simple` (pedido verbal `field_text_simple_small` → canônico; **sem** storage paralelo) em `config/sync/field.field.paragraph.beneficio_vaga_p.*` / helpers existentes — sem criar YAML novo de benefícios
- [X] T023 [US2] Validar SC-002 / cenários US2 (header/sobre/requisitos checks/benefícios/empresa sem Ver Empresa; omit empty) conforme `specs/031-vagas-detalhe-refino/quickstart.md` seção C (após `updb`/ensure local + `drush cr`)

**Checkpoint**: blocos informativos do Figma operacionais sem seções excluídas.

---

## Phase 5: User Story 3 — Visitante consulta FAQ da vaga em accordion (Priority: P1)

**Goal**: paragraph `faq_item_p` + `field_faq_itens` no CT `faq`; migração 1:1→coleção; seed 1×2; `field_vaga_faq` card. 1; accordion lê itens da coleção.  
**Independent Test Criteria**: associar coleção seed → expandir/colapsar; 2ª `updb` não duplica (SC-003 / SC-008 / quickstart D).

- [X] T024 [US3] Implementar helper `_custom_configs_ensure_faq_item_p()` (paragraph type `faq_item_p` + storages novos `paragraph.field_pergunta` string + `paragraph.field_resposta` text_long + instances + form/view displays; reexecução = no-op) em `modules/custom/custom_configs/custom_configs.install`
- [X] T025 [P] [US3] Implementar helper `_custom_configs_ensure_field_faq_itens()` (storage ERR `node.field_faq_itens` → `faq_item_p` card. `-1` + instance no bundle `faq` + displays; ocultar `field_resposta` legado no form `faq`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T026 [US3] Implementar helper `_custom_configs_migrate_faq_1to1_to_collection()` (nodes FAQ com title+`field_resposta` sem itens → 1× `faq_item_p`; fundir seeds 030 `…abc01`/`…abc02` na coleção seed sem duplicar pergunta) em `modules/custom/custom_configs/custom_configs.install` conforme research R4
- [X] T027 [US3] Implementar helper `_custom_configs_seed_faq_collection_example()` (1 node UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0`, title “FAQ — Exemplo”, 2 itens perguntas Figma; load por UUID; não sobrescrever editorial divergente) em `modules/custom/custom_configs/custom_configs.install` conforme `data-model.md`
- [X] T028 [P] [US3] Implementar helper `_custom_configs_ensure_field_vaga_faq_cardinality_1()` (consolidar N refs → 1 coleção quando necessário; ajustar cardinality storage/instance para **1**; handler só bundle `faq`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T029 [US3] Reescrever seção “Dúvidas Frequentes” no Twig full: ler `field_vaga_faq.entity.field_faq_itens`; Accordion BS5; pergunta=`field_pergunta`; resposta=`field_resposta`; IDs `vaga-faq-{vaga_nid}-{delta|pid}`; omitir se vazio/unpublished sem acesso — em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T030 [P] [US3] Ajustar CSS mínimo do accordion/seção FAQ sob `.vaga-detalhe__faq` em `themes/custom/default/assets/css/components/vagas-detalhe.css` (sem JS custom)
- [X] T031 [P] [US3] Criar Twig opcional do paragraph FAQ em `themes/custom/default/templates/paragraph/paragraph--faq-item-p.html.twig` **ou** render inline no node full — um caminho só, BEM `.vaga-detalhe__*`
- [X] T032 [US3] Validar SC-003 / SC-008 / cenários US3 (accordion mouse+teclado; omit empty; 1 seed / 2 itens sem duplicata) conforme `specs/031-vagas-detalhe-refino/quickstart.md` seção D

**Checkpoint**: FAQ coleção estrutural + accordion público operacionais.

---

## Phase 6: User Story 4 — Visitante age na sidebar e vê o resumo (Priority: P1)

**Goal**: sidebar com Candidatar-se / Salvar / Compartilhar (fluxos existentes) + Resumo da Vaga; sem card Seu Perfil.  
**Independent Test Criteria**: comparar sidebar com fluxos atuais; resumo com pares Período/Bolsa/Modelo/Vagas (quickstart E).

- [X] T033 [US4] Confirmar/ajustar card de ações da sidebar (Candidatar-se largo + Salvar + Compartilhar; estados `vaga_candidatada` / `vaga_salva`; anônimo → login; id âncora `#vaga-acoes` se útil ao CTA) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T034 [US4] Confirmar/ajustar “Resumo da Vaga” (título; Período←`field_horarios`; Bolsa←`field_text_simple`; Modelo←`field_regime_t`; Vagas←“Não informado”; fundo `#F3F6F9`; **sem** Seu Perfil) em `themes/custom/default/templates/content/node--vagas--full.html.twig`
- [X] T035 [P] [US4] Confirmar estilos de ações/resumo sob `.vaga-detalhe` (sem regras de perfil) em `themes/custom/default/assets/css/components/vagas-detalhe.css`
- [X] T036 [US4] Validar cenários US4 (ações; resumo; anônimo/candidato; estados salva/candidatada) conforme `specs/031-vagas-detalhe-refino/quickstart.md` seção E

**Checkpoint**: funil da sidebar fechado sem seções excluídas.

---

## Phase 7: User Story 5 — Visitante vê o CTA final isolado (Priority: P1)

**Goal**: instância `cta_v1` + placement `default_ctav1vagas` em `content_full` só em nodes `vagas`; Twig/CSS escuro `#023C62` isolados; CTAs claros intactos.  
**Independent Test Criteria**: vaga vs home/QS/PE — SC-005 / quickstart F.

- [X] T037 [US5] Registrar library `cta_v1_vagas` → `assets/css/cta-v1-vagas.css` (deps Bootstrap alinhadas a `cta_v1` / padrão 026) em `themes/custom/default/default.libraries.yml`
- [X] T038 [P] [US5] Criar CSS encapsulado (fundo `#023C62`; textos claros; botão primário alinhado ao Figma; **somente** sob `.block-cta-v1--vagas` / `#block-default-ctav1vagas`; **não** tocar `cta-v1.css` nem `cta-v1-para-estudantes.css`) em `themes/custom/default/assets/css/cta-v1-vagas.css`
- [X] T039 [US5] Implementar Twig suggestion do placement (classe raiz `block-cta-v1--vagas`; markup `cta-v1__*`; attach **só** `default/cta_v1_vagas`; **não** attach `default/cta_v1`; omit empty) em `themes/custom/default/templates/block/block--default-ctav1vagas.html.twig`
- [X] T040 [US5] Implementar helper `_custom_configs_seed_cta_v1_vagas()` (block_content UUID `d4e5f6a7-b8c9-4012-c345-6789abcdef01`; título “Pronto para o próximo passo?”; corpo convidativo; link “Candidatar-se Agora” → `internal:/cadastro/candidato`; campos vazios only na reexecução) em `modules/custom/custom_configs/custom_configs.install`
- [X] T041 [P] [US5] Implementar helper `_custom_configs_ensure_placement_ctav1vagas()` (placement id `default_ctav1vagas`; UUID `e5f6a7b8-c9d0-4123-d456-789abcdef012`; região `content_full`; weight `10`; `label_display: '0'`; visibility `entity_bundle:node` → `vagas`) em `modules/custom/custom_configs/custom_configs.install`
- [X] T042 [US5] Validar SC-005 / cenários US5 (CTA escuro só em vagas; landings intactas; copy seed) conforme `specs/031-vagas-detalhe-refino/quickstart.md` seção F (após seed/`updb` + `drush cr`)

**Checkpoint**: conversão final via bloco isolado — sem CTA inline no Twig do node.

---

## Phase 8: User Story 6 — Editor gerencia requisitos, benefícios e FAQ coleção (Priority: P1)

**Goal**: form displays de `vagas` / `faq` / paragraphs expõem campos editáveis; alterações refletem no full.  
**Independent Test Criteria**: editar FAQ coleção, requisitos, benefícios; reabrir página pública (SC-006 / quickstart G).

- [X] T043 [US6] Estender ensures para form display `default` de `node.vagas` (requisitos textarea formatado; benefícios paragraphs; FAQ autocomplete card. 1) e view display adequado em `modules/custom/custom_configs/custom_configs.install`
- [X] T044 [US6] Confirmar form/view displays de `faq` (title + `field_faq_itens`; `field_resposta` legado hidden) e de `faq_item_p` / `beneficio_vaga_p` nos helpers T024/T025/T022 — completar gaps em `modules/custom/custom_configs/custom_configs.install`
- [X] T045 [US6] Validar SC-006 / cenários US6 (campos no form; FAQ coleção + benefícios refletem no full após salvar/`drush cr`) conforme `specs/031-vagas-detalhe-refino/quickstart.md` seção G

**Checkpoint**: editorial opera o modelo coleção + requisitos text_long sem admin de estrutura.

---

## Phase 9: User Story 7 — Header global permanece claro e responsivo (Priority: P2)

**Goal**: smoke do Menu Principal (Header) em home + detalhe de vaga; corrigir só regressão introduzida pelo refino.  
**Independent Test Criteria**: desktop + mobile — SC-010 / quickstart H.

- [X] T046 [US7] Smoke-test Header (`block--default-top.html.twig` + menu `main`): desktop legível e mobile offcanvas/hamburger + sticky em home e detalhe de vaga — documentar resultado; **só** corrigir regressão factual em `themes/custom/default/templates/block/block--default-top.html.twig` / CSS do topo se o refino quebrar navegação
- [X] T047 [US7] Validar SC-010 / cenários US7 conforme `specs/031-vagas-detalhe-refino/quickstart.md` seção H

**Checkpoint**: navegação global sem regressão (sem redesign).

---

## Phase 10: User Story 8 — Deploy automatizado sem passos manuais (Priority: P1)

**Goal**: `custom_configs_update_11048` idempotente (FAQ coleção + migrate + requisitos recreate + CTA seed/placement); origem `drush cex`; destino `cim`→`updb`→`cim`→`cr`.  
**Independent Test Criteria**: fluxo deploy + 2ª `updb` — SC-007 / SC-008 / quickstart A+J / contrato deploy.

- [X] T048 [US8] Implementar `custom_configs_update_11048` (chamar ensures/migrates/seeds T018 + T024–T028 + T040–T041 + displays T043/T044; **não** alterar View listagem / Hero 027 / cards laranja / CTAs QS/PE/estudantes; **não** deletar `field_vaga_etapas_processo`; retornar mensagem Drush; idempotente) em `modules/custom/custom_configs/custom_configs.install`
- [X] T049 [US8] Exportar configs estruturais com `drush cex -y` — versionar tipicamente `paragraphs.paragraphs_type.faq_item_p.yml`, storages/fields/displays FAQ item + `field_faq_itens`, `field.storage.node.field_vaga_requisitos.yml` (text_long), `field_vaga_faq` card. 1, displays `faq`/`vagas`, `block.block.default_ctav1vagas.yml`; **não** inventar YAML à mão; revisar diff para **não** regredir listagem/Hero/CTAs claros
- [X] T050 [US8] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (idempotência SC-008) conforme `specs/031-vagas-detalhe-refino/quickstart.md` seções A e J e `contracts/deploy-vagas-detalhe-refino.md`
- [X] T051 [US8] Validar gates pós-deploy (FAQ coleção 1×2; `field_vaga_faq` card. 1; requisitos text_long; full sem seções excluídas; CTA só em vagas; ações intactas; listagem intacta; SC-007) conforme contrato de deploy e quickstart

**Checkpoint**: destino reproduz estrutura + layout refinado sem admin manual; hook reentrante seguro.

---

## Phase 11: Polish & Cross-Cutting Concerns

**Objetivo**: PRD §3.1/§3.6 cirúrgico, specify-rules, isolamento listagem/cards/CTAs, aceite final SC-001–SC-010.

- [X] T052 [P] Atualizar `PRD.md` §3.1 e §3.6 de forma cirúrgica (FAQ coleção + `faq_item_p` / `field_faq_itens`; `field_vaga_requisitos` text_long; `field_vaga_faq` card. 1; benefícios `field_text_simple` canônico; CTA vagas / placement / library; menção hook **`11048`**) — manter bullets listagem `/vagas` e cards laranja intactos
- [X] T053 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para feature `031-vagas-detalhe-refino` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T054 [P] Amostrar regressão: `/vagas` + Hero 027; Home/PE cards laranja; CTAs claros QS/PE + CTA escuro PE estudantes intactos; zero Match/Processo/Perfil/CTA inline no detalhe — quickstart I
- [X] T055 Executar validação final completa (SC-001–SC-010; checklist deploy; edges logo/empresa/FAQ unpublished/benefício sem ícone) com `specs/031-vagas-detalhe-refino/quickstart.md` e `checklists/requirements.md`
- [X] T056 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → US7 (Phase 9, P2) → US8 (Phase 10) → Polish (Phase 11)
- US1 é independente de schema novo — só Twig/CSS/preprocess cleanup
- US2 depende do shell limpo US1; helper recreate requisitos (T018) fecha com US8/`updb`; aceite completo pós-deploy
- US3 depende do shell US1; ensures FAQ (T024–T028) antes do accordion Twig (T029); idempotência fecha com US8
- US4 depende do shell US1 (perfil já removido); pode avançar em paralelo a US2/US3 no Twig após T014
- US5 independente de FAQ/requisitos no Twig do node; seed/placement fecham com US8; Twig/CSS CTA podem avançar após Foundational
- US6 estende displays dos ensures US2–US3/US5; aceite editorial após estrutura existir
- US7 (P2) é smoke; não bloqueia US8
- US8 (hook + cex + deploy) precisa dos helpers T018 + T024–T028 + T040–T041 + T043–T044; desbloqueia aceite completo em destino limpo
- Polish após US1–US8

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) MVP shell limpo 8/4 (sem processo/perfil/CTA inline)
                    ├→ US2 (P1) requisitos text_long + header/benefícios/empresa ──┐
                    ├→ US3 (P1) FAQ coleção + accordion ───────────────────────────┤
                    ├→ US4 (P1) sidebar ações + resumo ────────────────────────────┤
                    ├→ US5 (P1) CTA cta_v1 vagas isolado ──────────────────────────┤
                    ├→ US6 (P1) form displays editoriais ──────────────────────────┤
                    ├→ US7 (P2) Header smoke ──────────────────────────────────────┤
                    └→ US8 (P1) hook 11048 + cex + deploy ─────────────────────────┴→ Polish
                         ↑
                         └── usa helpers T018, T024–T028, T040–T041, T043–T044
```

## Parallel Execution Opportunities

- **Setup**: T002–T005, T007–T008 em paralelo após T001; T006 após T001
- **Foundational**: T009–T012 em paralelo; T013 após T009–T012
- **US1**: T014 → T015/T016 em paralelo → T017
- **US2**: T018 independente de Twig; T019/T020 após T014; T021/T022 paralelos; T023 após estrutura (ideal pós-US8)
- **US3**: T024 → T025 paralelo; T026 após T024+T025; T027 após T024; T028 paralelo a T026/T027; T029 após T014+T028; T030/T031 paralelos a T029; T032 por último
- **US4**: T033–T034 após T014; T035 paralelo; T036 por último
- **US5**: T037 → T038/T039; T040/T041 paralelos (PHP); T042 após seed
- **US6**: T043/T044 após ensures US2–US3; T045 após estrutura
- **US7**: T046 → T047 (pode rodar em paralelo a US5/US6)
- **US8**: T048 após helpers → T049 → T050 → T051
- **Polish**: T052–T054 em paralelo; depois T055–T056

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (remover seções excluídas + CTA inline; manter grid 8/4) — valor visual principal do refino
2. **Conteúdo + FAQ + sidebar**: US2 (requisitos text_long / benefícios / empresa) + US3 (FAQ coleção) + US4 (sidebar)
3. **CTA + editorial**: US5 (`cta_v1` vagas isolado) + US6 (displays) + US7 (Header smoke P2)
4. **Deploy**: US8 (`11048` + cex + `cim`→`updb`→`cim`→`cr`)
5. **Fechamento**: Polish (PRD §3.1/§3.6 + specify-rules + isolamento + aceite SC-001–SC-010)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T056`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1 → US2 → US3 → US4 → US5 → US6 → US7 (P2) → US8 (deploy por último para fechar aceite)
