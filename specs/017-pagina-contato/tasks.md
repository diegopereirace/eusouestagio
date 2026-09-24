# Tasks: Página de Contato

**Input**: Artefatos de design em `specs/017-pagina-contato/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/layout-contato-render.md`, `quickstart.md`  
**Branch**: `feature-contato` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/017-pagina-contato` (via `.specify/feature.json`); template alinhado a `specs/015-cta-v1-quem-somos/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar webform `contato`, storage `field_image`, máscara telefone, page title `/contato`, último hook e padrão Twig/library antes de criar config/tema/`11021`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/layout-contato-render.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/017-pagina-contato/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar webform legado `contato` (UUID `38532b7b-3193-4b6a-b666-e1e68693dd94`; elementos `tipo`/`nome`/`e_mail`/`whatsapp`/`mensagem`; confirmação; access create; `settings.page`) em `config/sync/webform.webform.contato.yml`
- [X] T003 [P] Confirmar reuso de `field_image` em `block_content` e ausência de `field_formulario_contato` / bundle `layout_contato` sob `config/sync/field.storage.block_content.field_image.yml` e `config/sync/block_content.type.*`
- [X] T004 [P] Confirmar máscara telefone (`custom_configs_webform_submission_form_alter` + library `default/masks` + classe `mask-phone`) em `modules/custom/custom_configs/custom_configs.module` e `themes/custom/default/default.libraries.yml`
- [X] T005 [P] Confirmar `default_page_title` com negate em `/contato` e remoção de `/contato` de `banners-block_1` (hook `11020`) em `config/sync/block.block.default_page_title.yml` e `modules/custom/custom_configs/custom_configs.install`
- [X] T006 Confirmar último hook `custom_configs_update_11020` → próximo livre `11021` em `modules/custom/custom_configs/custom_configs.install`; inventariar padrão Twig `block--block-*.html.twig` e library em `themes/custom/default/templates/block/` e `themes/custom/default/default.libraries.yml`

**Checkpoint**: inventário alinhado a plan/research; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar webform `contato` atualizado, block type `layout_contato`, storage novo `field_formulario_contato`, instances (`field_formulario_contato` + `field_image`), form/view displays e placement `default_layoutcontato` — pré-requisito de todas as user stories. **Não** criar `field_imagem_destaque`; **não** mapear `page_submit_path` do webform para `/contato`; **não** tocar home/Quem Somos/rodapé.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T007 Confirmar reuso de `field_image` (sem storage paralelo) e necessidade só de `field_formulario_contato` novo conforme `specs/017-pagina-contato/research.md` (R3/R4) sob `config/sync/field.storage.block_content.*`
- [X] T008 Atualizar webform `contato` in-place (elementos `nome_completo`/`email`/`telefone`+`mask-phone`/`categoria`/`assunto`/`mensagem`; `webform_flexbox` linhas 1–2; `#submit__label: Enviar Mensagem`; preservar UUID/confirmação/access create; **não** apontar `page_submit_path` para `/contato`) em `config/sync/webform.webform.contato.yml`
- [X] T009 [P] Criar tipo de bloco “Layout de Contato” em `config/sync/block_content.type.layout_contato.yml`
- [X] T010 Criar field storage `field_formulario_contato` (tipo `webform`, `target_type: webform`, cardinality **1**, entity type `block_content`) em `config/sync/field.storage.block_content.field_formulario_contato.yml`
- [X] T011 [P] Criar field instances do bloco em `config/sync/field.field.block_content.layout_contato.field_formulario_contato.yml` e `config/sync/field.field.block_content.layout_contato.field_image.yml`
- [X] T012 Criar form display do bloco (webform ref + image; UX imagem limitada a 1) em `config/sync/core.entity_form_display.block_content.layout_contato.default.yml`
- [X] T013 [P] Criar view display do bloco (ambos os campos visíveis para Twig) em `config/sync/core.entity_view_display.block_content.layout_contato.default.yml`
- [X] T014 Criar placement `default_layoutcontato` (tema `default`, região `content_full`, weight `0`, `label_display: '0'`, `request_path` = `/contato`, UUID placement `e2f3a4b5-c6d7-4e8f-9012-b3c4d5e6f7a8`, plugin `block_content:d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6`) em `config/sync/block.block.default_layoutcontato.yml`
- [X] T015 Incluir permissões create/edit/delete do bundle `layout_contato` nas roles que já gerenciam block content e preparar diffs em `config/sync/user.role.*.yml` (export final via `cex` em US6)
- [X] T016 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — webform Contato com 6 campos; tipo **Layout de Contato** em Estrutura → Tipos de bloco; storage `field_formulario_contato` presente; **sem** alterar home/Quem Somos/rodapé

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS custom; seed/página/asset ainda ausentes (hook US6).

---

## Phase 3: User Story 1 — Visitante vê a página de contato no layout do Figma (Priority: P1) 🎯 MVP

**Goal**: viewport ≥992px — duas colunas (form+atalhos | painel `#E5EEFF` + ilustração); H2 “Envie sua mensagem” Poppins negrito; classes `.block-layout-contato` / `.layout-contato`; DOM alinhado ao contrato.  
**Independent Test Criteria**: abrir `/contato` ≥992px com bloco publicado e comparar estrutura/cores/composição com `contracts/layout-contato-render.md` (SC-001).

- [X] T017 [US1] Registrar library `layout_contato` → `assets/css/layout-contato.css` em `themes/custom/default/default.libraries.yml`
- [X] T018 [P] [US1] Criar CSS encapsulado (painel `#E5EEFF`; submit `#FD7B1A`; inputs radius ≈8px; H2 Poppins negrito; **somente** seletores sob `.block-layout-contato`) em `themes/custom/default/assets/css/layout-contato.css`
- [X] T019 [US1] Implementar Twig do bloco (raiz `.block-layout-contato`; inner `.layout-contato.container.py-5`; `.row` → `.col-12.col-lg-7` | `.col-12.col-lg-5`; H2 “Envie sua mensagem”; render webform + painel com `.img-fluid`; attach `default/layout_contato`; preservar `attributes`/`content_attributes`/`title_*`; DOM alinhado a `contracts/layout-contato-render.md`) em `themes/custom/default/templates/block/block--block-layout-contato.html.twig`
- [X] T020 [US1] Implementar fallbacks no Twig (omitir área do webform se `field_formulario_contato` vazio; painel `#E5EEFF` sem `<img>` quebrada se `field_image` vazio; sem fatal) em `themes/custom/default/templates/block/block--block-layout-contato.html.twig`
- [X] T021 [US1] Validar SC-001 e cenários US1 (desktop duas colunas; título; painel) conforme `specs/017-pagina-contato/quickstart.md` seção B e `specs/017-pagina-contato/contracts/layout-contato-render.md` (conteúdo de teste manual ou seed US6)

**Checkpoint**: visitante desktop vê o layout Figma (MVP).

---

## Phase 4: User Story 2 — Visitante preenche e envia o formulário (Priority: P1)

**Goal**: campos/placeholders Figma; flexbox Nome|E-mail e Telefone|Categoria no desktop; submit “Enviar Mensagem”; envio válido → confirmação; inválido → erro sem limpar campos válidos; máscara telefone preservada.  
**Independent Test Criteria**: preencher e submeter em `/contato` (SC-003, SC-004).

- [X] T022 [US2] Revisar/confirmar elementos, placeholders, `webform_flexbox`, `categoria` (empty “Estudante”; opções Estudante/Empresa/Outro), `actions` e obrigatoriedade dos seis campos em `config/sync/webform.webform.contato.yml`
- [X] T023 [US2] Confirmar classe `mask-phone` no elemento `telefone` e que `custom_configs_webform_submission_form_alter` continua anexando `default/masks` ao webform `contato` em `config/sync/webform.webform.contato.yml` e `modules/custom/custom_configs/custom_configs.module`
- [X] T024 [US2] Estilizar botão submit “Enviar Mensagem” (fundo `#FD7B1A`, contraste legível) **somente** sob `.block-layout-contato` em `themes/custom/default/assets/css/layout-contato.css`
- [X] T025 [US2] Validar SC-003/SC-004 e cenários US2 (placeholders; pares lado a lado; envio válido/inválido) conforme `specs/017-pagina-contato/quickstart.md` seção C

**Checkpoint**: formulário funcional no layout.

---

## Phase 5: User Story 3 — Visitante usa atalhos de E-mail e WhatsApp (Priority: P1)

**Goal**: faixa abaixo do form com E-mail (`mailto:contato@eusouestagio.com`) e WhatsApp (`https://wa.me/5561999999999`), ícones/rótulos, usável no mobile.  
**Independent Test Criteria**: inspecionar e acionar os dois links em `/contato` (SC-005).

- [X] T026 [US3] Implementar faixa de atalhos (`.layout-contato__shortcuts.d-flex.gap-4.mt-4`; links hardcoded seed Figma; ícones envelope/WhatsApp) em `themes/custom/default/templates/block/block--block-layout-contato.html.twig`
- [X] T027 [P] [US3] Ajustar CSS dos atalhos (legibilidade; wrap/empilhamento mobile sem overflow) sob `.block-layout-contato` em `themes/custom/default/assets/css/layout-contato.css`
- [X] T028 [US3] Validar SC-005 / cenários US3 conforme `specs/017-pagina-contato/quickstart.md` seção D e `specs/017-pagina-contato/contracts/layout-contato-render.md`

**Checkpoint**: canais alternativos ao formulário funcionam.

---

## Phase 6: User Story 4 — Visitante mobile vê coluna empilhada (Priority: P1)

**Goal**: viewport ≤575.98px — form acima da imagem; inputs/select usáveis; botão clicável; sem scroll horizontal do bloco.  
**Independent Test Criteria**: `/contato` ≤575.98px (SC-002).

- [X] T029 [US4] Confirmar/ajustar CSS mobile (empilhamento Bootstrap `col-12`; sem overflow-x; tipografia/paddings legíveis) sob `.block-layout-contato` em `themes/custom/default/assets/css/layout-contato.css`
- [X] T030 [US4] Confirmar classes Bootstrap das colunas (`.col-12.col-lg-7` / `.col-12.col-lg-5`) e ordem DOM (form antes do painel) no Twig em `themes/custom/default/templates/block/block--block-layout-contato.html.twig`
- [X] T031 [US4] Validar SC-002 / cenários US4 conforme `specs/017-pagina-contato/quickstart.md` seção E

**Checkpoint**: experiência mobile estável.

---

## Phase 7: User Story 5 — Editor gerencia webform e imagem sem código (Priority: P2)

**Goal**: editor autenticado altera referência do webform e/ou imagem de destaque; mudanças refletem em `/contato` sem deploy (&lt; 3 min).  
**Independent Test Criteria**: editar bloco no painel, salvar, recarregar `/contato` (SC-006).

- [X] T032 [US5] Revisar form display (widgets webform + image; labels claros; UX 1 imagem) em `config/sync/core.entity_form_display.block_content.layout_contato.default.yml`
- [X] T033 [US5] Confirmar permissões do bundle `layout_contato` nas roles exportadas sob `config/sync/user.role.*.yml` (alinhado a T015)
- [X] T034 [US5] Confirmar que webform e imagem vêm dos fields (não hardcoded como única fonte); atalhos Twig permanecem seed desta fase em `themes/custom/default/templates/block/block--block-layout-contato.html.twig`
- [X] T035 [US5] Validar SC-006 / cenários US5 conforme `specs/017-pagina-contato/quickstart.md` seção F

**Checkpoint**: conteúdo visual/form associado editável sem código.

---

## Phase 8: User Story 6 — Deploy reproduz estrutura, seed e placement (Priority: P1)

**Goal**: asset `img-contato.png` + `custom_configs_update_11021` idempotente (ensure webform, block type/fields/displays, asset→`public://`, seed bloco UUID fixo, placement `/contato`, página+alias `/contato`); fluxo `cim` → `updb` → `cim` → `cr`; `cex` versiona estrutural; **nunca** sobrescrever editorial divergente nem duplicar.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; 2ª `updb` sem duplicatas; `/contato` completo (SC-007, SC-008).

- [X] T036 [P] [US6] Versionar asset seed `img-contato.png` (arte Figma; alt descritivo no seed) em `modules/custom/custom_configs/assets/contato/img-contato.png`
- [X] T037 [US6] Implementar `custom_configs_update_11021` idempotente (ensure webform `contato` atualizado; ensure `layout_contato` + storage `field_formulario_contato` + instances + displays; copiar asset → `public://`; seed `BlockContent` UUID `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6` com webform `contato` + imagem **somente se** ausente/campos vazios; ensure placement `default_layoutcontato` + visibility `/contato`; ensure Node `page` UUID `f3a4b5c6-d7e8-4f90-a123-c4d5e6f7a8b9` + alias `/contato`; **nunca** sobrescrever editorial divergente; **nunca** duplicar bloco/página) em `modules/custom/custom_configs/custom_configs.install`
- [X] T038 [P] [US6] Extrair/reusar helpers privados de ensure/seed/placement/asset/página no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T039 [US6] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/017-pagina-contato/data-model.md` sob `config/sync/` (webform, block type, storage, instances, displays, placement, `user.role.*`)
- [X] T040 [US6] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-007/SC-008) conforme `specs/017-pagina-contato/quickstart.md` seções A e G
- [X] T041 [US6] Validar fallbacks e isolamento pós-seed (campos vazios omitidos; bloco só em `/contato`; sem banner legado; SC-009 amostragem home/Quem Somos/rodapé) conforme `specs/017-pagina-contato/quickstart.md` seção G

**Checkpoint**: deploy 100% automatizado; `/contato` reproduzível.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, isolamento visual e aceite final SC-001–SC-009.

- [X] T042 [P] Atualizar §3.6 (+ §10 se listar rotas públicas) documentando `layout_contato`, webform `contato`, placement `default_layoutcontato`, rota `/contato`, hook `11021`, library `layout_contato` em `PRD.md`
- [X] T043 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `017-pagina-contato` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T044 Executar validação final completa (SC-001–SC-009; checklist aceite seções B–H) com `specs/017-pagina-contato/quickstart.md` e `specs/017-pagina-contato/checklists/requirements.md`
- [X] T045 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Phase 10: Hotfix — Formulário duplicado em `/contato`

**Objetivo**: em ambientes onde o alias `/contato` já apontava para um Node bundle `webform` (do módulo `webform_node`), o hook `11021` respeitava o alias herdado e nunca criava a página `page` seed — resultando em dois formulários renderizados (webform_node inline + bloco `layout_contato`). Corrigir de forma idempotente e deployável via fluxo padrão.

- [X] T046 Reforçar `_custom_configs_ensure_contato_page()` em `modules/custom/custom_configs/custom_configs.install` para detectar aliases `/contato` apontando para node com UUID diferente do seed, remover o alias legado sem apagar o node, criar/reutilizar a página seed (UUID `f3a4b5c6-d7e8-4f90-a123-c4d5e6f7a8b9`) e recriar o alias apontando para ela
- [X] T047 Adicionar `custom_configs_update_11022()` em `modules/custom/custom_configs/custom_configs.install` disparando `_custom_configs_ensure_contato_page()` no `drush updb` — reutiliza a idempotência da helper, é no-op quando o alias já pertence à página seed
- [X] T048 Atualizar `PRD.md` §3.6 (bloco `layout_contato`) referenciando `custom_configs_update_11022` como migração do alias legado
- [X] T049 Rodar `docker compose exec -T drupal vendor/bin/drush updb -y && drush cr` local, confirmar via `/contato` que apenas o bloco `layout_contato` é renderizado (1 form) e que o painel `#E5EEFF` + imagem seed aparecem no breakpoint apropriado

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US2 depende do webform foundational (T008) + CSS submit (T018/T024); aceite completo após seed US6 ou conteúdo manual
- US3 depende do Twig base US1 (T019); CSS atalhos pode paralelizar com ajustes US2
- US4 depende do markup/CSS de US1 (T018–T019)
- US5 depende de form display (T012) + Twig com fields (T019–T020); ideal após seed US6
- US6 (hook/asset/cex) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T019)
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
- **Foundational**: T008 após T007; T009 ∥ T010 após T007; T011 após T009+T010; T012/T013 após T011; T014 após UUID/plugin definido; T015 ∥ T014; T016 por último
- **US1**: T018 (CSS) em paralelo com início de T019 após T017; T020 após markup estável; T021 por último
- **US2**: T022–T023 após T008; T024 após T018; T025 por último
- **US3**: T026–T027 após T019; T028 por último
- **US4**: T029–T030 após T018–T019; T031 por último
- **US2 ∥ US3 ∥ US4**: após T019 (e T008 para US2)
- **US5**: após T012 + T019; melhor com seed US6
- **US6**: T036 ∥ início T037; T038 junto a T037; T039–T041 após hook
- **Polish**: T042 ∥ T043; depois T044–T045

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (layout desktop Figma em `/contato`) — valor principal
2. **Form + atalhos + mobile**: US2 + US3 + US4
3. **Editorial + deploy**: US5 (form/perms) + US6 (asset + `11021` + cim/updb/cim/cr + cex)
4. **Fechamento**: Polish (PRD + specify-rules + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T045`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1–US4 e US6 (P1) → US5 (P2); US1 = MVP
