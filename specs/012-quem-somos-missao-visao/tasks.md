# Tasks: Quem Somos — Seção Missão e Visão (no Node)

**Input**: Artefatos de design em `specs/012-quem-somos-missao-visao/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/quem-somos-missao-visao-render.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/012-quem-somos-missao-visao`; template alinhado a `specs/011-bloco-missao-visao/tasks.md` / `specs/010-layout-sobre-nos/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar Twig legado, CSS da 011, displays/fields do bundle e último hook antes de criar config/tema/`11015`.

- [X] T001 Confirmar artefatos SDD e feature ativa (`specs/012-quem-somos-missao-visao/`, `.specify/feature.json`)
- [X] T002 [P] Inventariar markup atual (Sobre nós + 2ª seção legado `*_2`) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T003 [P] Inventariar CSS/library de referência da 011 e libraries do tema em `themes/custom/default/assets/css/block-missao-visao.css` e `themes/custom/default/default.libraries.yml`
- [X] T004 [P] Inventariar form/view displays, Field Groups e instances legado em `config/sync/core.entity_form_display.node.quem_somos.default.yml`, `config/sync/core.entity_view_display.node.quem_somos.default.yml` e `config/sync/field.field.node.quem_somos.*`
- [X] T005 Confirmar último hook `custom_configs_update_11014` → próximo livre `11015` em `modules/custom/custom_configs/custom_configs.install`; inventariar storages reutilizáveis (`field.storage.node.field_imagem_desktop.yml`, `field.storage.node.field_text_simple_long.yml`, `field.storage.node.field_text_simple_long_2.yml`), instances de referência (`field.field.node.banners.field_imagem_desktop.yml`, `field.field.node.para_empresas.field_text_simple_long.yml`, `field.field.node.para_empresas.field_text_simple_long_2.yml`), placement `config/sync/block.block.default_missaovisao.yml` e asset `modules/custom/custom_configs/assets/missao-visao/fundo-missao-visao.jpg`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar remoção do legado Anexo 2, anexação dos três fields + Field Group, displays e disable do bloco 011 — pré-requisito de todas as user stories. Zero storage novo; sem Paragraphs.

- [X] T006 Confirmar reuso (sem criar storages paralelos) de `field_imagem_desktop`, `field_text_simple_long` e `field_text_simple_long_2` em `config/sync/field.storage.node.field_imagem_desktop.yml`, `config/sync/field.storage.node.field_text_simple_long.yml` e `config/sync/field.storage.node.field_text_simple_long_2.yml`
- [X] T007 [P] Criar field instances do bundle `quem_somos` (rótulos editoriais adequados: imagem de fundo / texto Missão / texto Visão) em `config/sync/field.field.node.quem_somos.field_imagem_desktop.yml`, `config/sync/field.field.node.quem_somos.field_text_simple_long.yml` e `config/sync/field.field.node.quem_somos.field_text_simple_long_2.yml`
- [X] T008 Atualizar form display: remover `group_segundo_bloco` e widgets `*_2`; adicionar `group_missao_visao` (rótulo “Seção Missão e Visão”, children dos três campos novos, weight após `group_primeiro_bloco`) em `config/sync/core.entity_form_display.node.quem_somos.default.yml`
- [X] T009 Atualizar view display: remover formatters `*_2`; incluir os três campos novos (visíveis ou acessíveis ao Twig) em `config/sync/core.entity_view_display.node.quem_somos.default.yml`
- [X] T010 [P] Remover YAMLs das field instances legado em `config/sync/field.field.node.quem_somos.field_titulo_2.yml`, `config/sync/field.field.node.quem_somos.field_text_long_formatted_2.yml` e `config/sync/field.field.node.quem_somos.field_imagem_2.yml`
- [X] T011 Remover storages órfãos legado (somente se sem outras instances) em `config/sync/field.storage.node.field_titulo_2.yml`, `config/sync/field.storage.node.field_text_long_formatted_2.yml` e `config/sync/field.storage.node.field_imagem_2.yml`
- [X] T012 Desabilitar placement do bloco 011 (`status: false`) em `config/sync/block.block.default_missaovisao.yml`

**Checkpoint**: após esta fase, US1–US5 podem avançar; Twig/CSS/hook consomem os YAMLs acima (seed editorial vem em US5).

---

## Phase 3: User Story 1 — Visitante vê Missão e Visão no novo layout (Priority: P1)

**Goal**: viewport ≥768px — faixa full-width com fundo, overlay escuro, duas colunas “Nossa Missão” | “Nossa Visão”, tipografia Poppins branca, divisória sutil; **sem** layout Anexo 2.  
**Independent Test Criteria**: abrir `/quem-somos` ≥768px e comparar estrutura/overlay/colunas/tipografia com Anexo 1 (`contracts/quem-somos-missao-visao-render.md`); confirmar ausência do layout Anexo 2.

- [X] T013 [US1] Registrar library `quem_somos_missao_visao` apontando para o CSS dedicado em `themes/custom/default/default.libraries.yml`
- [X] T014 [P] [US1] Criar CSS base encapsulado (tokens `--qs-mv-overlay`; `min-height: 380px`; `background-size/position`; overlay `::before`; tipografia Poppins; fallback fundo escuro; breakout full-bleed) em `themes/custom/default/assets/css/quem-somos-missao-visao.css`
- [X] T015 [US1] Atualizar Twig do Node: restringir `.container` à seção Sobre nós; remover markup da 2ª seção legado (`*_2`); renderizar `<section class="quem-somos-missao-visao">` irmã fora do container interno; títulos H2 fixos “Nossa Missão”/“Nossa Visão”; grid `.container`/`.row`/`.col-md-6`; attach library; preservar `attributes`/`content_attributes`/`title_*` em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T016 [US1] Implementar fallbacks no Twig (ambos textos vazios + sem imagem → omitir seção; só Missão ou só Visão → uma coluna; sem imagem → sem `background-image` inline) em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T017 [P] [US1] Estilizar desktop (divisória no 1º `.col-md-6` / `.quem-somos-missao-visao__col--first` em `md+`; contraste texto branco sobre overlay; full-bleed seguro) sob `.quem-somos-missao-visao` em `themes/custom/default/assets/css/quem-somos-missao-visao.css`
- [X] T018 [US1] Validar SC-001/SC-002 e cenários US1 em viewport ≥768px conforme `specs/012-quem-somos-missao-visao/quickstart.md` e `specs/012-quem-somos-missao-visao/contracts/quem-somos-missao-visao-render.md`

---

## Phase 4: User Story 2 — Visitante mobile vê empilhamento legível (Priority: P1)

**Goal**: viewport &lt;768px — Missão e Visão empilhados, legíveis, sem overflow-x; divisória desktop ausente.  
**Independent Test Criteria**: `/quem-somos` ≤767.98px — empilhamento, legibilidade e ausência de scroll horizontal da seção.

- [X] T019 [US2] Ajustar CSS mobile (empilhamento `col-md-6`; desligar borda/divisória abaixo de `md`; conter overflow do breakout) sob `.quem-somos-missao-visao` em `themes/custom/default/assets/css/quem-somos-missao-visao.css`
- [X] T020 [US2] Confirmar classes Bootstrap responsivas e markup sem forçar duas colunas no mobile em `themes/custom/default/templates/content/node--quem-somos.html.twig`
- [X] T021 [US2] Validar SC-003 / cenários US2 em viewport ≤767.98px conforme `specs/012-quem-somos-missao-visao/quickstart.md`

---

## Phase 5: User Story 4 — Sem duplicidade com o bloco legado da feature 011 (Priority: P1)

**Goal**: exatamente uma faixa Missão/Visão em `/quem-somos` (a do Node); bloco `default_missaovisao` não renderiza; seção não aparece em outras rotas.  
**Independent Test Criteria**: contar faixas em `/quem-somos`; amostrar home e internas após `cim`/`cr`.

- [X] T022 [US4] Confirmar YAML do placement com `status: false` (tipos/conteúdo 011 podem permanecer) em `config/sync/block.block.default_missaovisao.yml`
- [X] T023 [US4] Validar SC-005 (uma faixa no Node; bloco ausente na rota; home/internas sem a seção) conforme `specs/012-quem-somos-missao-visao/quickstart.md`

---

## Phase 6: User Story 3 — Editor gerencia a seção no formulário do Node (Priority: P1)

**Goal**: form do Node expõe `group_missao_visao` (imagem + Missão + Visão); sem “Segundo Bloco”/`*_2`; alterações refletem em `/quem-somos` após cache.  
**Independent Test Criteria**: editar o Node no painel, salvar e recarregar `/quem-somos`; confirmar ausência do legado no form.

- [X] T024 [US3] Confirmar form display com `group_missao_visao` (rótulo “Seção Missão e Visão”) e widgets dos três campos; ausência de `group_segundo_bloco`/`*_2` em `config/sync/core.entity_form_display.node.quem_somos.default.yml`
- [X] T025 [US3] Confirmar que alterações editoriais de textos/imagem aparecem no Twig público via fields do Node em `themes/custom/default/templates/content/node--quem-somos.html.twig` e rota `/quem-somos`
- [X] T026 [US3] Validar SC-004 e cenários US3 (edição &lt;5 min; sem legado no form) conforme `specs/012-quem-somos-missao-visao/quickstart.md`

---

## Phase 7: User Story 5 — Deploy reproduz estrutura em outro ambiente (Priority: P1)

**Goal**: `custom_configs_update_11015` idempotente remove legado, anexa fields/grupo/displays, seed condicional e disable do bloco; fluxo `cim` → `updb` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; segunda `updb` sem duplicatas; conteúdo editorial preservado.

- [X] T027 [US5] Implementar `custom_configs_update_11015` idempotente (remover legado instances/grupo; ensure três fields + `group_missao_visao` + displays; seed textos Assumptions + imagem asset/`public://missao-visao/` **somente se vazios**; migração opcional strip tags de `field_text_long_formatted_2` → Missão se ainda existir e Missão vazia; **nunca** sobrescrever editorial; garantir `default_missaovisao` desabilitado) em `modules/custom/custom_configs/custom_configs.install`
- [X] T028 [P] [US5] Extrair/reusar helpers privados de ensure/seed no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T029 [US5] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/012-quem-somos-missao-visao/data-model.md` sob `config/sync/` (instances novas, form/view displays, `block.block.default_missaovisao.yml`; remoções legado; storages órfãos; `user.role.*` só se cex mostrar diff)
- [X] T030 [US5] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cr` e reexecutar `updb` (SC-006/SC-007) conforme `specs/012-quem-somos-missao-visao/quickstart.md`

---

## Phase 8: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, isolamento CSS (banner 009 + Sobre nós 010 intactos) e aceite final SC-001–SC-009.

- [X] T031 [P] Atualizar §3.1.0 (campos Node + remoção `*_2` + Twig/library/`11015`) e §3.6 (bloco `missao_visao` aposentado na rota; seção no Node) em `PRD.md`
- [X] T032 Executar validação final completa (SC-001–SC-009; sem imagem; só um texto; omitir seção vazia; isolamento home/banner/Sobre nós; sem overflow mobile; uma faixa) com `specs/012-quem-somos-missao-visao/quickstart.md` e `specs/012-quem-somos-missao-visao/checklists/requirements.md`
- [X] T033 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US4 (Phase 5) → US3 (Phase 6) → US5 (Phase 7) → Polish (Phase 8)
- US2 depende do markup/CSS base de US1 (T015–T017)
- US4 valida sobretudo o Foundational (T012); pode rodar em paralelo com US1 após Phase 2 (aceite completo após seed US5)
- US3 depende de form display (T008) + Twig com fallbacks (T016); ideal após seed mínimo (US5) ou conteúdo manual de teste
- US5 (hook/cex) independente do polish visual fino, mas precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T015)
- Polish após US1–US5

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1)
                    ├→ US2 (P1)
                    ├→ US4 (P1) ──┐
                    ├→ US3 (P1) ──┤
                    └→ US5 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002, T003, T004 em paralelo após T001; T005 após T001
- **Foundational**: T006 → T007; T008/T009 após T007; T010 ∥ T012 após T008; T011 após T010
- **US1**: T014 (CSS base) em paralelo com início de T015 após T013; T017 após classes estáveis em T015
- **US2 ∥ US4**: T019–T020 com T022 após T015 (US4 YAML já em T012)
- **US3**: após T008 + T016; melhor com seed de US5 ou Node de teste
- **US5**: T027 após Phase 2 (+ asset inventariado em T005); T028 junto a T027; T029–T030 após hook
- **Polish**: T031 em paralelo com preparo de T032

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop Missão|Visão no Node) — valor principal da feature
2. **Mobile + sem duplicidade**: US2 + US4 (disable bloco 011)
3. **Editorial + deploy**: US3 (form) + US5 (`11015` + cim/updb/cr + cex)
4. **Fechamento**: Polish (PRD §3.1.0/§3.6 + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T033`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US4 → US3 → US5 (todas P1 na spec; ordem reflete dependências de implementação)
