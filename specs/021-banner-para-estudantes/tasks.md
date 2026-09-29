# Tasks: Banner (Hero) Para Estudantes

**Input**: Artefatos de design em `specs/021-banner-para-estudantes/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/banner-para-estudantes-render.md`, `contracts/deploy-banner-para-estudantes.md`, `quickstart.md`  
**Branch**: `feature-para-estudantes` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/021-banner-para-estudantes` (via `.specify/feature.json`); template alinhado a `specs/019-para-empresas-page/tasks.md` + `specs/009-banner-quem-somos/tasks.md` (arquitetura = 019; markup bipartido = 009)

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar View `banners`, allowed values, placement legado `block_1` (só `/para-estudantes`), Twig/preprocess Quem Somos (bipartido), hook `11032` e assets de banner antes de alterar config/tema/seed.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/banner-para-estudantes-render.md`, `contracts/deploy-banner-para-estudantes.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/021-banner-para-estudantes/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar allowed values de `field_local_exibicao` (`home` \| `internas` \| `quem_somos` \| `para_empresas`) e ausência de `para_estudantes` em `config/sync/field.storage.node.field_local_exibicao.yml`
- [X] T003 [P] Inventariar displays da View `banners` (`block_home` / `block_1` / `block_quem_somos` / `block_para_empresas`) e pages/status de `default_views_block__banners_block_1` (só `/para-estudantes`) em `config/sync/views.view.banners.yml` e `config/sync/block.block.default_views_block__banners_block_1.yml`
- [X] T004 [P] Inventariar padrão Twig/library/preprocess do banner Quem Somos (bipartido + `banner_media`) em `themes/custom/default/templates/views/views-view--banners--block-quem-somos.html.twig`, `themes/custom/default/templates/views/views-view-unformatted--banners--block-quem-somos.html.twig`, `themes/custom/default/default.libraries.yml`, `themes/custom/default/default.theme`
- [X] T005 [P] Inventariar contraste PE (carrossel full-bleed — **não** reutilizar markup) e âncora `#main-content` / rota `/cadastro/candidato` em `themes/custom/default/templates/views/views-view--banners--block-para-empresas.html.twig`, `themes/custom/default/templates/layout/page.html.twig` (ou equivalente com `id="main-content"`)
- [X] T006 Confirmar último hook `custom_configs_update_11032` → próximo livre `11033` e helpers reutilizáveis de seed/placement/allowed value de banners em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` já cobre FR-012–FR-014 (`hook_update_N` + `cex` + fluxo `cim`→`updb`→`cim`→`cr`); anotar lacuna só se houver gap factual vs. `specs/021-banner-para-estudantes/spec.md` — **sem lacuna; T035 = no-op**

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar allowed value `para_estudantes`, display View `block_para_estudantes` (pager `some`/1), placement do hero e desativação do legado `block_1` — pré-requisito de todas as user stories. **Zero** field storage novo. **Não** alterar placements home / Quem Somos / Para Empresas / Contato.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T008 Acrescentar allowed value `para_estudantes` / rótulo “Para Estudantes” em `config/sync/field.storage.node.field_local_exibicao.yml`
- [X] T009 Criar display Block `block_para_estudantes` (filtros `status=1`, `type=banners`, `field_local_exibicao=para_estudantes`; pager `some` `items_per_page: 1`; sorts herdados `field_peso` ASC + `created` DESC; css_class `css-banners-para-estudantes` ou equivalente; sem empty area) em `config/sync/views.view.banners.yml`
- [X] T010 [P] Criar placement do display na região `banner`, tema `default`, pages só `/para-estudantes`, weight `0`, `label_display: '0'`, plugin `views_block:banners-block_para_estudantes` em `config/sync/block.block.default_views_block__banners_block_para_estudantes.yml`
- [X] T011 [P] Desativar placement legado (`status: false`; **não** esvaziar `pages`) em `config/sync/block.block.default_views_block__banners_block_1.yml`
- [X] T012 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — allowed value + display + placements versionados; **sem** alterar home/QS/PE/Contato; seed de conteúdo ainda ausente (hook US4)

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS do hero nem seed; legado desativado no sync.

---

## Phase 3: User Story 1 — Visitante vê o hero de estudantes alinhado ao Figma (Priority: P1) 🎯 MVP

**Goal**: viewport ≥992px — hero duas colunas (copy/CTAs | ilustração), container ≤1280px, paddings `64px 40px 48px 40px`; badge/h1/lead Figma; mobile empilha texto acima da imagem; sem carrossel e sem banner legado de internas; DOM alinhado ao contrato.  
**Independent Test Criteria**: abrir `/para-estudantes` ≥992px e comparar hero com Figma/`contracts/banner-para-estudantes-render.md`; viewport estreita; ausência do legado (SC-001, SC-002, SC-003).

- [X] T013 [US1] Registrar library `banner_para_estudantes` → `assets/css/banner-para-estudantes.css` em `themes/custom/default/default.libraries.yml`
- [X] T014 [P] [US1] Criar CSS encapsulado (tokens locais sob `.hero-estudantes-wrapper`; `max-width: 1280px`; padding `64px 40px 48px 40px`; CTA `#FD7B1A`; **não** mutar `--brand-orange` global; sem vazar para home/QS/PE/Contato) em `themes/custom/default/assets/css/banner-para-estudantes.css`
- [X] T015 [US1] Criar template do display que anexa a library e omite markup quando zero rows em `themes/custom/default/templates/views/views-view--banners--block-para-estudantes.html.twig`
- [X] T016 [US1] Implementar layout duas colunas + copy fixa (badge “Plataforma exclusiva para estudantes”, `h1` “Seu futuro profissional começa aqui.”, subtítulo Figma, CTAs) + coluna mídia (`.img-fluid`, `.text-center` / `.text-lg-end`) sob `.hero-estudantes-wrapper` > `.row.align-items-center` > `.col-12.col-lg-6` em `themes/custom/default/templates/views/views-view-unformatted--banners--block-para-estudantes.html.twig`
- [X] T017 [P] [US1] Adicionar preprocess `banner_media` (espelho Quem Somos: URI crua desktop/mobile + alt; omit se sem imagem) em `themes/custom/default/default.theme` (`default_preprocess_views_view_unformatted__banners__block_para_estudantes`)
- [X] T018 [P] [US1] Estilizar desktop (≥992px: duas colunas, paddings Figma) e mobile (&lt;992px: copy acima da mídia; sem overflow-x do hero) sob `.hero-estudantes-wrapper` em `themes/custom/default/assets/css/banner-para-estudantes.css`
- [X] T019 [US1] Validar SC-001/SC-002/SC-003 e cenários US1 (desktop, paddings, copy, mobile, ausência do legado) conforme `specs/021-banner-para-estudantes/quickstart.md` seção B e `specs/021-banner-para-estudantes/contracts/banner-para-estudantes-render.md` (conteúdo de teste manual ou seed US4)

**Checkpoint**: visitante vê o hero Figma (MVP); CTAs e deploy completo ainda nas fases seguintes.

---

## Phase 4: User Story 2 — Visitante usa os CTAs do hero (Priority: P1)

**Goal**: “Encontrar minha vaga” → `/para-estudantes#main-content`; “Criar meu perfil” → `/cadastro/candidato`; estilos primário/secundário do contrato.  
**Independent Test Criteria**: clicar cada CTA seedado e verificar destino (SC-004).

- [X] T020 [US2] Confirmar `href`s canônicos e classes dos CTAs (`banner-para-estudantes__btn--primary` / `--secondary`; grupo `d-flex gap-3 flex-wrap`) em `themes/custom/default/templates/views/views-view-unformatted--banners--block-para-estudantes.html.twig`
- [X] T021 [P] [US2] Estilizar botões do hero (primário `#FD7B1A`; secundário outline navy/tokens do tema; tocáveis no mobile) sob `.hero-estudantes-wrapper` em `themes/custom/default/assets/css/banner-para-estudantes.css`
- [X] T022 [US2] Validar SC-004 / cenários US2 conforme `specs/021-banner-para-estudantes/quickstart.md` seção C e `specs/021-banner-para-estudantes/contracts/banner-para-estudantes-render.md`

**Checkpoint**: conversão da jornada do estudante com rotas limpas.

---

## Phase 5: User Story 3 — Editor gerencia o hero sem código (Priority: P2)

**Goal**: editor altera imagem (e metadados do nó) do banner com local “Para Estudantes”; mudança reflete em `/para-estudantes` sem deploy; copy/CTAs permanecem no Twig (research R2); segundo banner com mesmo local **não** quebra layout (pager 1).  
**Independent Test Criteria**: editar nó banner no painel; publicar segundo banner; recarregar página pública (SC-005).

- [X] T023 [US3] Confirmar que nodes `banners` com `field_local_exibicao=para_estudantes` alimentam só a mídia (copy/CTAs fixos no Twig) em `themes/custom/default/templates/views/views-view-unformatted--banners--block-para-estudantes.html.twig` e `config/sync/views.view.banners.yml`
- [X] T024 [US3] Confirmar omissão total do wrapper hero com zero banners publicados e omissão da coluna `__media` sem imagem em `themes/custom/default/templates/views/views-view--banners--block-para-estudantes.html.twig` e `themes/custom/default/templates/views/views-view-unformatted--banners--block-para-estudantes.html.twig`
- [X] T025 [US3] Confirmar pager `some`/`items_per_page: 1` (segundo banner publicado não quebra layout bipartido) em `config/sync/views.view.banners.yml`
- [X] T026 [US3] Validar SC-005 / cenários US3 conforme `specs/021-banner-para-estudantes/quickstart.md` seção D (após seed US4)

**Checkpoint**: conteúdo visual editável sem código; copy Twig estável.

---

## Phase 6: User Story 4 — Deploy reproduz o hero sem painel manual (Priority: P1)

**Goal**: `custom_configs_update_11033` idempotente — ensure allowed value + display/placement (defensivo); seed 1 banner (UUID `c3d4e5f6-a7b8-4901-c234-567890abcdef`) + asset → `public://`; desativar `block_1`; **nunca** duplicar nem sobrescrever editorial divergente; fluxo `cim` → `updb` → `cim` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; 2ª `updb` sem duplicatas (SC-006, SC-007).

- [X] T027 [P] [US4] Versionar asset PNG seed da ilustração Figma em `modules/custom/custom_configs/assets/banner-para-estudantes/hero.png`
- [X] T028 [US4] Implementar `custom_configs_update_11033` idempotente (ensure allowed value `para_estudantes`; ensure display `block_para_estudantes` defensivo; seed nó UUID `c3d4e5f6-a7b8-4901-c234-567890abcdef` `local=para_estudantes` peso `0` + copy asset → `public://` só se campo vazio; ensure placement região `banner` / pages `/para-estudantes`; force `default_views_block__banners_block_1.status = false`; **nunca** sobrescrever editorial divergente; **nunca** duplicar; ausência de PNG → nó sem imagem sem falhar o update) em `modules/custom/custom_configs/custom_configs.install`
- [X] T029 [P] [US4] Extrair/reusar helpers privados (ensure allowed value / seed banner / ensure placement / disable `block_1`) no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T030 [US4] Exportar/confirmar configs estruturais com `drush cex -y` para `config/sync/field.storage.node.field_local_exibicao.yml`, `config/sync/views.view.banners.yml`, `config/sync/block.block.default_views_block__banners_block_para_estudantes.yml` e `config/sync/block.block.default_views_block__banners_block_1.yml`
- [X] T031 [US4] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-006/SC-007) conforme `specs/021-banner-para-estudantes/quickstart.md` seções A e E e `specs/021-banner-para-estudantes/contracts/deploy-banner-para-estudantes.md`
- [X] T032 [US4] Validar isolamento pós-seed (hero só em `/para-estudantes`; `block_1` ausente; home/QS/PE/Contato OK; zero duplicatas) conforme `specs/021-banner-para-estudantes/quickstart.md` seções E e F

**Checkpoint**: deploy 100% automatizado; `/para-estudantes` reproduzível sem admin manual.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, specify-rules, reforço opcional da regra de deploy, isolamento visual e aceite final SC-001–SC-008.

- [X] T033 [P] Atualizar §3.1.1b (`para_estudantes` na lista) e §3.6 (display `block_para_estudantes`, placement, convivência com `block_1` desativado, hook `11033`) em `PRD.md`
- [X] T034 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `021-banner-para-estudantes` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T035 [P] Reforçar `.cursor/rules/drupal-deploy-configs.mdc` **somente se** T007 identificar lacuna vs. FR-012–FR-014; caso contrário, no-op documentado
- [X] T036 Executar validação final completa (SC-001–SC-008; checklist aceite seções B–F) com `specs/021-banner-para-estudantes/quickstart.md` e `specs/021-banner-para-estudantes/checklists/requirements.md`
- [X] T037 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US2 depende do markup/CTAs do hero US1 (T016)
- US3 depende do Twig empty-omit + pager Foundational/US1 (T009, T015–T016); aceite completo após seed US4
- US4 (hook/cex/assets) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T015–T017)
- Polish após US1–US4

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US3 (P2)
                    └→ US4 (P1) ──→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002–T005 ∥ T007 após T001; T006 após T001
- **Foundational**: T010 ∥ T011 após T009; T012 por último
- **US1**: T014 (CSS) ∥ início T015 após T013; T017 ∥ T016; T018 após markup; T019 por último
- **US2**: T020–T021 após T016; T022 após seed US4 (ou com conteúdo de teste)
- **US2 ∥ US3 (parcial)**: após T015–T016; US3 melhor com seed US4
- **US4**: T027 ∥ início T028; T029 com T028; T030–T032 após hook
- **Polish**: T033 ∥ T034 ∥ T035; depois T036–T037

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (hero desktop/mobile Figma em `/para-estudantes`) — valor principal
2. **CTAs**: US2 (links canônicos `#main-content` + `/cadastro/candidato`)
3. **Editorial + deploy**: US3 (pager/omit/imagem) + US4 (`11033` + asset + cim/updb/cim/cr + cex)
4. **Fechamento**: Polish (PRD §3.1.1b/§3.6 + specify-rules + aceite SC-001–SC-008)

## Format Validation

- Todas as tarefas usam `- [X]`, ID sequencial (`T001`…`T037`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias: US1–US2 e US4 (P1) → US3 (P2); US1 = MVP
