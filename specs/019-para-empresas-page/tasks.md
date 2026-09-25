# Tasks: Página Para Empresas

**Input**: Artefatos de design em `specs/019-para-empresas-page/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/banner-para-empresas-render.md`, `contracts/composition-para-empresas.md`, `quickstart.md`  
**Branch**: `feature-para-empresas` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/019-para-empresas-page` (via `.specify/feature.json`); template alinhado a `specs/018-contato-node/tasks.md` + `specs/009-banner-quem-somos/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar View `banners`, allowed values, placements legado (`block_1`, `default_ctoparaempresas`), nó `para_empresas`, Twigs/libraries de banner (home/QS), blocos 004–006/013/015 e último hook antes de `11024`.

- [x] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/banner-para-empresas-render.md`, `contracts/composition-para-empresas.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/019-para-empresas-page/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [x] T002 [P] Inventariar allowed values de `field_local_exibicao` (`home` \| `internas` \| `quem_somos`) e ausência de `para_empresas` em `config/sync/field.storage.node.field_local_exibicao.yml`
- [x] T003 [P] Inventariar displays da View `banners` (`block_1`/`block_2`/`block_3`/`block_home`/`block_quem_somos`) e pages de `default_views_block__banners_block_1` (inclui `/para-empresas`) em `config/sync/views.view.banners.yml` e `config/sync/block.block.default_views_block__banners_block_1.yml`
- [x] T004 [P] Inventariar placement CTO legado `default_ctoparaempresas` (tipo `cto`, `content_full`, pages `/para-empresas`) e placements home 004–006 só `<front>` em `config/sync/block.block.default_ctoparaempresas.yml`, `config/sync/block.block.default_nossosdiferenciais.yml`, `config/sync/block.block.default_nossametodologia.yml`, `config/sync/block.block.default_oquefazemos.yml`
- [x] T005 [P] Inventariar padrão Twig/library/preprocess dos displays home e Quem Somos em `themes/custom/default/templates/views/views-view--banners--block-home.html.twig`, `themes/custom/default/templates/views/views-view--banners--block-quem-somos.html.twig`, `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig`, `themes/custom/default/default.libraries.yml`, `themes/custom/default/default.theme`
- [x] T006 [P] Inventariar Twig do nó `para_empresas`, Twig Benefícios (`id` hardcoded) e assets `diferenciais-quem-somos` em `themes/custom/default/templates/content/node--para-empresas.html.twig`, `themes/custom/default/templates/block/block--block-diferenciais-quem-somos.html.twig`, `modules/custom/custom_configs/assets/diferenciais-quem-somos/`
- [x] T007 Confirmar último hook `custom_configs_update_11023` → próximo livre `11024` e helpers reutilizáveis de seed/placement de banners/blocos em `modules/custom/custom_configs/custom_configs.install`
- [x] T008 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` já cobre FR-001/FR-002 (`hook_update_N` + `cex` + fluxo `cim`→`updb`→`cim`→`cr`); anotar lacuna só se houver gap factual vs. `specs/019-para-empresas-page/spec.md`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar allowed value `para_empresas`, display View `block_para_empresas`, placement do banner, remoção de `/para-empresas` do `block_1`, desativação do CTO legado e YAMLs dos 5 placements `content_full` — pré-requisito de todas as user stories. **Zero** field storage / block type / paragraph type novos (Benefícios = reuso `diferenciais_quem_somos`). **Não** alterar placements da home nem Quem Somos.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [x] T009 Acrescentar allowed value `para_empresas` / rótulo “Para Empresas” em `config/sync/field.storage.node.field_local_exibicao.yml`
- [x] T010 Criar display Block `block_para_empresas` (filtros `status=1`, `type=banners`, `field_local_exibicao=para_empresas`; pager `none`; sorts herdados `field_peso` ASC + `created` DESC; css_class dedicado; sem empty area) em `config/sync/views.view.banners.yml`
- [x] T011 [P] Criar placement do display na região `banner`, tema `default`, pages só `/para-empresas`, weight `0`, plugin `views_block:banners-block_para_empresas` em `config/sync/block.block.default_views_block__banners_block_para_empresas.yml`
- [x] T012 [P] Remover `/para-empresas` das pages do legado (permanece só `/para-estudantes`) em `config/sync/block.block.default_views_block__banners_block_1.yml`
- [x] T013 [P] Desativar placement CTO legado (`status: false`) em `config/sync/block.block.default_ctoparaempresas.yml`
- [x] T014 Criar placements `content_full` tema `default`, `label_display: '0'`, pages `/para-empresas`, weights 0–4 e UUIDs de conteúdo conforme `specs/019-para-empresas-page/data-model.md` em `config/sync/block.block.default_nossosdiferenciaisparaempresas.yml`, `config/sync/block.block.default_nossametodologiaparaempresas.yml`, `config/sync/block.block.default_oquefazemosparaempresas.yml`, `config/sync/block.block.default_beneficiosparaempresas.yml`, `config/sync/block.block.default_ctav1paraempresas.yml`
- [x] T015 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — allowed value + display + placements versionados; **sem** alterar home/Quem Somos; seeds de conteúdo ainda ausentes (hook US5)

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS do hero nem seeds; ordem de composição pronta após seed US5.

---

## Phase 3: User Story 1 — Visitante vê o hero corporativo em carrossel (Priority: P1) 🎯 MVP

**Goal**: viewport ≥992px — hero duas colunas (copy/CTA | carrossel de imagens), container interno ≤1200px, gap ~48px, padding-inline ~24px; 2 slides; mobile empilha copy acima da mídia; zero HTML legado “Olá, empresa!” / “Por que anunciar aqui?” no corpo do nó; DOM alinhado ao contrato.  
**Independent Test Criteria**: abrir `/para-empresas` ≥992px e comparar hero com Figma/`contracts/banner-para-empresas-render.md`; avançar carrossel; viewport estreita (SC-001, SC-002, SC-004, SC-005).

- [x] T016 [US1] Registrar library `banner_para_empresas` → `assets/css/banner-para-empresas.css` em `themes/custom/default/default.libraries.yml`
- [x] T017 [P] [US1] Criar CSS encapsulado (tokens locais sob `.banner-para-empresas-wrapper`; CTA primário `#FD7B1A`; inner `max-width: 1200px`; **não** mutar `--brand-orange` global; sem vazar para home/QS) em `themes/custom/default/assets/css/banner-para-empresas.css`
- [x] T018 [US1] Criar template do display que anexa a library e omite markup quando zero rows em `themes/custom/default/templates/views/views-view--banners--block-para-empresas.html.twig`
- [x] T019 [US1] Implementar layout duas colunas + copy fixa (tag “SOLUÇÕES CORPORATIVAS”, título “Encontre os melhores talentos para sua empresa.”, CTAs) + carrossel Bootstrap na coluna de mídia (`#banner-carousel-para-empresas`; controles/indicators só se multi; omit slide sem imagem; single sem controles quebrados) sob `.banner-para-empresas-wrapper` em `themes/custom/default/templates/views/views-view-unformatted--banners--block-para-empresas.html.twig`
- [x] T020 [P] [US1] Adicionar preprocess opcional montando slides (padrão home/QS) se necessário em `themes/custom/default/default.theme`
- [x] T021 [P] [US1] Estilizar desktop (≥992px: duas colunas, gap ~48px, padding ~24px) e mobile (&lt;992px: copy acima da mídia; sem overflow-x do hero) sob `.banner-para-empresas-wrapper` em `themes/custom/default/assets/css/banner-para-empresas.css`
- [x] T022 [US1] Atualizar Twig do nó para omit empty / shell mínimo (sem reintroduzir “Olá, empresa!” / grade `field_itens_p` vazia) em `themes/custom/default/templates/content/node--para-empresas.html.twig`
- [x] T023 [US1] Validar SC-001/SC-002/SC-004/SC-005 e cenários US1 (desktop, carrossel, mobile, ausência de HTML legado) conforme `specs/019-para-empresas-page/quickstart.md` seções B e E e `specs/019-para-empresas-page/contracts/banner-para-empresas-render.md` (conteúdo de teste manual ou seed US5)

**Checkpoint**: visitante vê o hero B2B Figma (MVP); composição completa ainda depende de US2 + seed US5.

---

## Phase 4: User Story 2 — Visitante percorre a composição institucional B2B (Priority: P1)

**Goal**: abaixo do banner, ordem Diferenciais → Metodologia → O Que Fazemos → Benefícios → CTA; exclusivos de `/para-empresas`; home e Quem Somos intocados; `id` da section Benefícios dinâmico (não forçar `#diferenciais-quem-somos`).  
**Independent Test Criteria**: rolar `/para-empresas` do hero ao rodapé; abrir `/` e `/quem-somos` (SC-003, SC-009).

- [x] T024 [US2] Ajustar Twig Benefícios para `id` da `<section>` derivado do título/label (slug) em vez de hardcode `diferenciais-quem-somos` em `themes/custom/default/templates/block/block--block-diferenciais-quem-somos.html.twig`
- [x] T025 [P] [US2] Confirmar YAMLs de placements PE (weights 0–4, pages `/para-empresas`, região `content_full`, plugins dos tipos corretos; home 004–006 e QS intocados) em `config/sync/block.block.default_nossosdiferenciaisparaempresas.yml`, `config/sync/block.block.default_nossametodologiaparaempresas.yml`, `config/sync/block.block.default_oquefazemosparaempresas.yml`, `config/sync/block.block.default_beneficiosparaempresas.yml`, `config/sync/block.block.default_ctav1paraempresas.yml`
- [x] T026 [US2] Confirmar exclusividade: `block_para_empresas` só `/para-empresas`; `block_1` sem `/para-empresas`; CTO `status: false` em `config/sync/block.block.default_views_block__banners_block_para_empresas.yml`, `config/sync/block.block.default_views_block__banners_block_1.yml`, `config/sync/block.block.default_ctoparaempresas.yml`
- [x] T027 [US2] Validar SC-003/SC-009 e cenários US2 (ordem; home intacta; QS/contato sem placements PE; CTO não renderiza) conforme `specs/019-para-empresas-page/quickstart.md` seções C e H e `specs/019-para-empresas-page/contracts/composition-para-empresas.md` (após seed US5)

**Checkpoint**: composição B2B estável e exclusiva da rota.

---

## Phase 5: User Story 3 — Visitante usa CTAs do hero e do bloco final (Priority: P1)

**Goal**: hero “Cadastrar Empresa” → `/cadastro/empresa`, “Contrate o Estágio Certo” → `/painel/empresa/vagas/nova`; CTA final `cta_v1` com título “Pronto para contratar os melhores talentos?” e botões utilizáveis (seed US5).  
**Independent Test Criteria**: clicar cada CTA seedado e verificar destino (SC-001 parcial / US3).

- [x] T028 [US3] Confirmar `href`s canônicos e classes dos CTAs do hero (`banner-para-empresas__btn--primary` / `--secondary`) em `themes/custom/default/templates/views/views-view-unformatted--banners--block-para-empresas.html.twig`
- [x] T029 [P] [US3] Estilizar botões do hero (primário `#FD7B1A`; secundário contraste legível; grupo `d-flex gap-3 flex-wrap`) sob `.banner-para-empresas-wrapper` em `themes/custom/default/assets/css/banner-para-empresas.css`
- [x] T030 [US3] Confirmar que a instância CTA PE reusa Twig/CSS existentes `.block-cta-v1` (015) sem alterar visual de Quem Somos além da convivência compartilhada do tipo — sem CSS novo obrigatório em `themes/custom/default/` (Twig/CSS `cta_v1` existentes)
- [x] T031 [US3] Validar cenários US3 (hero + CTA final título/botões) conforme `specs/019-para-empresas-page/quickstart.md` seção D e `specs/019-para-empresas-page/contracts/composition-para-empresas.md` / `banner-para-empresas-render.md` (após seed US5)

**Checkpoint**: conversão B2B com rotas limpas.

---

## Phase 6: User Story 4 — Editor gerencia conteúdo sem código (Priority: P2)

**Goal**: editor altera imagens dos 2 banners (`local=para_empresas`), itens dos blocos PE, Benefícios (ícone+rótulo) e CTA; mudanças refletem em `/para-empresas` sem deploy (&lt; 3 min). Copy do hero permanece no Twig (Assumption US4 / research R2).  
**Independent Test Criteria**: editar banner e CTA no painel; recarregar página pública (SC-006).

- [x] T032 [US4] Confirmar que nodes `banners` com `field_local_exibicao=para_empresas` alimentam só a mídia do carrossel (copy/CTAs fixos no Twig) em `themes/custom/default/templates/views/views-view-unformatted--banners--block-para-empresas.html.twig` e `config/sync/views.view.banners.yml`
- [x] T033 [US4] Confirmar omitência total do wrapper hero com zero banners publicados em `themes/custom/default/templates/views/views-view--banners--block-para-empresas.html.twig`
- [x] T034 [US4] Confirmar que Benefícios / CTA / blocos 004–006 PE usam fields dos tipos existentes (gerenciáveis no painel; sem hardcode como única fonte de itens) alinhado a `specs/019-para-empresas-page/data-model.md`
- [x] T035 [US4] Validar SC-006 / cenários US4 conforme `specs/019-para-empresas-page/quickstart.md` seção F (após seed US5)

**Checkpoint**: conteúdo B2B editável sem código.

---

## Phase 7: User Story 5 — Deploy reproduz a página sem painel manual (Priority: P1)

**Goal**: `custom_configs_update_11024` idempotente — limpar fields do nó `para_empresas`; ensure allowed value + display/placements (defensivo); seed 2 banners (UUIDs `a1b2c3d4-…` / `b2c3d4e5-…`) + assets → `public://`; seed 5 blocos (UUIDs data-model) **somente se** ausente/campos vazios; ensure placements; desativar CTO; **nunca** duplicar nem sobrescrever editorial divergente; fluxo `cim` → `updb` → `cim` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; 2ª `updb` sem duplicatas (SC-007, SC-008).

- [x] T036 [P] [US5] Versionar assets PNG seed dos 2 slides em `modules/custom/custom_configs/assets/banner-para-empresas/` (e ícones Benefícios só se o seed precisar de arte distinta de `modules/custom/custom_configs/assets/diferenciais-quem-somos/`)
- [x] T037 [US5] Implementar `custom_configs_update_11024` idempotente (limpar `field_titulo` / `field_text_simple` / `field_text_simple_long` / `field_text_simple_long_2` / `field_imagem` / `field_itens_p` do nó alias `/para-empresas` se preenchidos; ensure allowed value `para_empresas`; seed 2 banners `local=para_empresas` pesos 0/1 + copy assets → `public://`; seed blocos UUIDs `c3d4e5f6-…`, `d4e5f6a7-…`, `e5f6a7b8-…`, `f6a7b8c9-…` título “Benefícios para Empresas”, `a7b8c9d0-…` título “Pronto para contratar os melhores talentos?” + links `/cadastro/empresa` e `/painel/empresa/vagas/nova`; ensure/disable placements; **nunca** sobrescrever editorial divergente; **nunca** duplicar; **não** apagar nó/alias) em `modules/custom/custom_configs/custom_configs.install`
- [x] T038 [P] [US5] Extrair/reusar helpers privados (limpeza nó / ensure allowed value / seed banners / seed blocos PE / placements / disable CTO) no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [x] T039 [US5] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/019-para-empresas-page/plan.md` / `data-model.md` sob `config/sync/` (field storage, View, 6 placements novos/alterados, CTO)
- [x] T040 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-007/SC-008) conforme `specs/019-para-empresas-page/quickstart.md` seções A e G
- [x] T041 [US5] Validar limpeza e isolamento pós-seed (fields do nó vazios; 2 banners; 5 seções; sem duplicatas; home/QS/`/para-estudantes` OK) conforme `specs/019-para-empresas-page/quickstart.md` seções G e H

**Checkpoint**: deploy 100% automatizado; `/para-empresas` reproduzível sem admin manual.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, specify-rules, reforço opcional da regra de deploy, isolamento visual e aceite final SC-001–SC-009.

- [x] T042 [P] Atualizar §3.1.1b / §3.6 / UC-16 / rotas documentando composição `/para-empresas`, display `block_para_empresas`, valor `para_empresas`, hook `11024`, convivência `block_1`/CTO em `PRD.md`
- [x] T043 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `019-para-empresas-page` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [x] T044 [P] Reforçar `.cursor/rules/drupal-deploy-configs.mdc` **somente se** T008 identificar lacuna vs. FR-001/FR-002; caso contrário, no-op documentado
- [x] T045 Executar validação final completa (SC-001–SC-009; checklist aceite seções B–H) com `specs/019-para-empresas-page/quickstart.md` e `specs/019-para-empresas-page/checklists/requirements.md`
- [x] T046 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Phase 9: Shell Página básica (pós-composição)

**Objetivo**: `/para-empresas` como bundle `page`; remover tipo `para_empresas` e storage órfão `field_itens_p`; deploy automatizado via `11028`.

- [x] T047 Migrar alias `/para-empresas` para Node `page` (UUID `c9d0e1f2-…`), apagar nós/tipo/displays/fields e `field.storage.node.field_itens_p` em `custom_configs_update_11028`
- [x] T048 Remover YAMLs do tipo do `config/sync` e Twig `node--para-empresas.html.twig`
- [x] T049 Documentar exceção de ordem `updb` → `cim` → `cr` (quickstart 019, runbook 016, PRD §3.1.0b, `drupal-deploy-configs.mdc`)

---

## Phase 10: Subtexto opcional em `diferencial_simples_p` (Benefícios Figma)

**Objetivo**: campo Subtexto opcional no paragraph; seed 5 itens Figma no Benefícios PE; Quem Somos sem linha extra.

- [x] T050 Garantir instance `paragraph.diferencial_simples_p.field_text_simple_long` (label Subtexto, `required: false`) + form/view displays via ensure em `modules/custom/custom_configs/custom_configs.install`
- [x] T051 Implementar `custom_configs_update_11029`/`11030` (ensure campo + seed/normalização Benefícios PE 5 itens Figma; migrar seed legado ou híbrido; preencher subtexto vazio por rótulo)
- [x] T052 Twig omit-empty + CSS `.dqs-item__subtext` em `themes/custom/default/templates/paragraph/paragraph--diferencial-simples-p.html.twig` e `themes/custom/default/assets/css/diferenciais-quem-somos.css`
- [x] T053 `drush cex -y` + `updb` + `cr`; atualizar PRD §3.6 e quickstart 019

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende dos placements Foundational (T014) + Twig Benefícios (T024); aceite completo após seed US5
- US3 depende do markup/CTAs do hero US1 (T019) + seed CTA US5 para o bloco final
- US4 depende do Twig empty-omit US1 (T018–T019) + tipos existentes; ideal após seed US5
- US5 (hook/cex/assets) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T018–T022)
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US3 (P1)
                    ├→ US4 (P2)
                    └→ US5 (P1) ──→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002–T006 ∥ T008 após T001; T007 após T001
- **Foundational**: T011 ∥ T012 ∥ T013 após T010; T014 após UUIDs data-model; T015 por último
- **US1**: T017 (CSS) ∥ início T018 após T016; T020 ∥ T019; T021 após markup; T022 paralelo a Twig views; T023 por último
- **US2**: T024 ∥ T025–T026 após Phase 2; T027 após seed US5
- **US3**: T028–T029 após T019; T030 paralelo; T031 após seed US5
- **US2 ∥ US3**: após T019 (e T014 para US2)
- **US4**: após T018–T019; melhor com seed US5
- **US5**: T036 ∥ início T037; T038 com T037; T039–T041 após hook
- **Polish**: T042 ∥ T043 ∥ T044; depois T045–T046

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (hero desktop/mobile Figma em `/para-empresas`) — valor principal
2. **Composição + CTAs**: US2 + US3 (ordem das seções + links canônicos)
3. **Editorial + deploy**: US4 (editor) + US5 (`11024` limpeza + seeds + cim/updb/cim/cr + cex)
4. **Fechamento**: Polish (PRD + specify-rules + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T046`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1–US3 e US5 (P1) → US4 (P2); US1 = MVP
