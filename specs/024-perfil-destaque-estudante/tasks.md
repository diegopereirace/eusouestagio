# Tasks: Perfil em Destaque (Estudante)

**Input**: Artefatos de design em `specs/024-perfil-destaque-estudante/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/perfil-destaque-estudante-render.md`, `contracts/deploy-perfil-destaque-estudante.md`, `quickstart.md`  
**Branch**: `feature-para-estudantes` (ou branch ativa alinhada à feature)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md`  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/024-perfil-destaque-estudante` (via `.specify/feature.json`); template alinhado a `specs/023-jornada-estudante/tasks.md`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar storages canônicos, bloco jornada `023` (padrão form max-cardinality + Twig/CSS a **não** reutilizar), benefícios `022`, hero `021`, placement `content_full` weight `1` em `/para-estudantes` e último hook `11037` antes de criar config/tema/`11038`.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/perfil-destaque-estudante-render.md`, `contracts/deploy-perfil-destaque-estudante.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/024-perfil-destaque-estudante/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar storages reutilizáveis (não recriar; **não** alterar cardinality `field_itens_lista` = `-1`) em `config/sync/field.storage.block_content.field_image.yml`, `config/sync/field.storage.block_content.field_text_simple.yml`, `config/sync/field.storage.block_content.field_itens_lista.yml`, `config/sync/field.storage.block_content.field_link.yml`, `config/sync/field.storage.paragraph.field_image.yml`, `config/sync/field.storage.paragraph.field_text_simple.yml` e `config/sync/field.storage.paragraph.field_text_simple_long.yml`
- [X] T003 [P] Inventariar padrão visual mais próximo (022 lista/ícone + `missao_visao` duas colunas; **não** reutilizar markup/CSS/classes `.be-*` / `.je-*` / `.block-jornada-estudante`) em `themes/custom/default/templates/block/block--block-jornada-estudante.html.twig`, `themes/custom/default/templates/block/block--block-beneficios-estudantes.html.twig`, `themes/custom/default/assets/css/jornada-estudante.css` e `themes/custom/default/default.libraries.yml`
- [X] T004 [P] Inventariar convivência hero `021` + benefícios `022` + jornada `023` + View `vagas` em `/para-estudantes` em `config/sync/block.block.default_views_block__banners_block_para_estudantes.yml`, `config/sync/block.block.default_beneficiosestudantes.yml` (weight `0`), `config/sync/block.block.default_jornadaestudante.yml` (weight `1`) e ausência de `perfil_destaque_estudante` / `default_perfildestaqueestudante`
- [X] T005 Confirmar último hook `custom_configs_update_11037` → próximo livre `11038` e helpers reutilizáveis de ensure/seed/placement/assets; confirmar form alter max-4 da jornada em `modules/custom/custom_configs/custom_configs.module` (padrão a estender para max-3 do perfil) em `modules/custom/custom_configs/custom_configs.install` e `modules/custom/custom_configs/custom_configs.module`
- [X] T006 [P] Confirmar que `.cursor/rules/drupal-deploy-configs.mdc` já cobre FR-019 (`hook_update_N` + `cex` + fluxo `cim`→`updb`→`cim`→`cr` + assets versionados); anotar lacuna só se houver gap factual vs. `specs/024-perfil-destaque-estudante/spec.md`

**Checkpoint**: inventário alinhado a plan/research/data-model; nenhuma alteração estrutural nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: versionar paragraph type `item_lista_icone_p`, block type `perfil_destaque_estudante`, field instances (reuso de storages; handler lista → `item_lista_icone_p`), form/view displays e placement `default_perfildestaqueestudante` weight `2` — pré-requisito de todas as user stories. **Zero** field storage novo. **Não** alterar cardinality de `field_itens_lista`. **Não** tocar hero `021`/benefícios `022`/jornada `023`/View `vagas`/PE/QS/home.

**⚠️ CRITICAL**: Nenhuma user story começa antes desta fase.

- [X] T007 Confirmar reuso (sem storage paralelo `field_text_simple_small` / `field_imagem` / lista nova; sem alterar cardinality de `field_itens_lista`) dos YAMLs listados em T002 sob `config/sync/field.storage.*`
- [X] T008 [P] Criar tipo de paragraph “Item de Lista com Ícone” em `config/sync/paragraphs.paragraphs_type.item_lista_icone_p.yml`
- [X] T009 [P] Criar tipo de bloco “Perfil em Destaque” em `config/sync/block_content.type.perfil_destaque_estudante.yml` (`revision` alinhado aos demais block types)
- [X] T010 [P] Criar field instances do paragraph em `config/sync/field.field.paragraph.item_lista_icone_p.field_image.yml`, `config/sync/field.field.paragraph.item_lista_icone_p.field_text_simple.yml` e `config/sync/field.field.paragraph.item_lista_icone_p.field_text_simple_long.yml`
- [X] T011 Criar field instances do bloco em `config/sync/field.field.block_content.perfil_destaque_estudante.field_image.yml`, `config/sync/field.field.block_content.perfil_destaque_estudante.field_text_simple.yml`, `config/sync/field.field.block_content.perfil_destaque_estudante.field_itens_lista.yml` (handler → somente `item_lista_icone_p`; storage permanece `-1` — max 3 só no form/validação US4) e `config/sync/field.field.block_content.perfil_destaque_estudante.field_link.yml`
- [X] T012 [P] Criar form + view displays do paragraph em `config/sync/core.entity_form_display.paragraph.item_lista_icone_p.default.yml` e `config/sync/core.entity_view_display.paragraph.item_lista_icone_p.default.yml`
- [X] T013 Criar form display do bloco (widget paragraphs; default type `item_lista_icone_p`; image + text + link) em `config/sync/core.entity_form_display.block_content.perfil_destaque_estudante.default.yml`
- [X] T014 [P] Criar view display do bloco (lista via `entity_reference_revisions_entity_view`; image + link) em `config/sync/core.entity_view_display.block_content.perfil_destaque_estudante.default.yml`
- [X] T015 Criar placement `default_perfildestaqueestudante` (tema `default`, região `content_full`, weight **`2`**, `label_display: '0'`, `request_path` = `/para-estudantes`, plugin UUID `c0d1e2f3-a4b5-4678-c901-2def01234567`) em `config/sync/block.block.default_perfildestaqueestudante.yml`
- [X] T016 Importar/validar estrutura local (`drush cim -y` ou UI + `drush cex -y`) — tipos em Estrutura → Tipos de bloco / Tipos de parágrafo; **sem** alterar hero `021`/benefícios `022`/jornada `023`/View `vagas`/PE/QS/home; seed de conteúdo ainda ausente (hook US6)

**Checkpoint**: estrutura sobe só com `cim`; front ainda sem Twig/CSS custom; seed ainda ausente; max 3 ainda não enforced no form.

---

## Phase 3: User Story 1 — Visitante vê o perfil em destaque em Para Estudantes (Priority: P1) 🎯 MVP

**Goal**: viewport ≥992px — duas colunas (ilustração esquerda; título + lista ícone/texto + CTA direita); classes raiz `section-perfil-destaque` / `block-perfil-destaque-estudante`; tokens 1280/64/40/20/208×44; Poppins; título item `#9D4300`; descrição `#45464D`; DOM alinhado ao contrato.  
**Independent Test Criteria**: abrir `/para-estudantes` ≥992px com bloco publicado e comparar estrutura (duas colunas + 3 itens + CTA) com Figma/`contracts/perfil-destaque-estudante-render.md` (SC-001).

- [X] T017 [US1] Registrar library `perfil_destaque_estudante` → `assets/css/perfil-destaque-estudante.css` em `themes/custom/default/default.libraries.yml`
- [X] T018 [P] [US1] Criar CSS encapsulado (container max `1280px`; paddings `64px`/`40px`; fundo `#FFFFFF`; ícone item `20×20`; título seção max-width `528px`; título item `#9D4300`; descrição `#45464D`; CTA ~`208×44` navy `#023C62` texto `#FFFFFF`; Poppins; **somente** seletores sob `.section-perfil-destaque` / `.block-perfil-destaque-estudante`; **não** mutar tokens globais do tema) em `themes/custom/default/assets/css/perfil-destaque-estudante.css`
- [X] T019 [US1] Implementar Twig do bloco (wrappers `.section-perfil-destaque` + `.block-perfil-destaque-estudante`; `.pd-container` + `.row.align-items-center.g-5` + `.col-12.col-lg-6` × 2; `.pd-media` ilustração `.img-fluid`; `.pd-content` + `h2.pd-title`; loop `field_itens_lista`; CTA `.pd-cta`; attach library; `id="perfil-destaque-estudante"`; preservar `attributes`/`content_attributes`/`title_*`; **não** reusar classes `.be-*` / `.je-*`) em `themes/custom/default/templates/block/block--block-perfil-destaque-estudante.html.twig`
- [X] T020 [P] [US1] Implementar Twig do paragraph (item `.pd-item.d-flex.gap-3.mb-4`; ícone `.pd-item__icon.flex-shrink-0` 20×20; `h3.pd-item__title`; `p.pd-item__text`; omitir ícone/título/descrição vazios) em `themes/custom/default/templates/paragraph/paragraph--item-lista-icone-p.html.twig`
- [X] T021 [US1] Implementar fallbacks no Twig do bloco (omitir `h2` vazio; ilustração ausente → coluna esquerda vazia/omitida; zero itens → só cabeçalho/ilustração/CTA; CTA vazio → omitir botão; sem fatal) em `themes/custom/default/templates/block/block--block-perfil-destaque-estudante.html.twig`
- [X] T022 [US1] Validar SC-001 e cenários US1 (desktop lg+: duas colunas + 3 itens ícone 20×20 + tokens 64/40/1280/208×44) conforme `specs/024-perfil-destaque-estudante/quickstart.md` seção B e `specs/024-perfil-destaque-estudante/contracts/perfil-destaque-estudante-render.md` (conteúdo de teste manual ou seed US6)

**Checkpoint**: visitante desktop vê a seção completa no layout de referência (MVP).

---

## Phase 4: User Story 2 — Visitante tablet/mobile vê colunas empilhadas (Priority: P1)

**Goal**: viewport &lt;992px — colunas empilham (`.col-12`; ilustração acima, conteúdo abaixo); ilustração `.img-fluid`; lista/CTA legíveis; sem scroll horizontal causado pelo bloco.  
**Independent Test Criteria**: abrir `/para-estudantes` em ≤575.98px e 768–991px (SC-002).

- [X] T023 [US2] Confirmar/ajustar CSS responsivo (sem overflow-x; tipografia legível; paddings/tokens preservados; ilustração sem estourar) sob `.section-perfil-destaque` / `.block-perfil-destaque-estudante` em `themes/custom/default/assets/css/perfil-destaque-estudante.css`
- [X] T024 [US2] Confirmar classes Bootstrap das colunas (`.col-12.col-lg-6` + `.row.align-items-center.g-5`) no Twig do bloco em `themes/custom/default/templates/block/block--block-perfil-destaque-estudante.html.twig`
- [X] T025 [US2] Validar SC-002 / cenários US2 (empilhamento &lt;lg; mobile `.img-fluid`; sem scroll horizontal) conforme `specs/024-perfil-destaque-estudante/quickstart.md` seção B itens 2–3

**Checkpoint**: colunas empilhadas em tablet/mobile sem regressão desktop.

---

## Phase 5: User Story 3 — Visitante aciona o CTA de completar perfil (Priority: P1)

**Goal**: botão “Completar meu perfil” renderiza `field_link` (~208×44 navy); rótulo = título do link; href = URI `/painel/estudante/perfil` (ou gate de login já vigente — fora de escopo ajustar auth).  
**Independent Test Criteria**: clicar no CTA em `/para-estudantes` e verificar destino `/painel/estudante/perfil` (SC-009).

- [X] T026 [US3] Confirmar markup do CTA (`.pd-cta`; rótulo/href de `field_link`; omitir se vazio) em `themes/custom/default/templates/block/block--block-perfil-destaque-estudante.html.twig`
- [X] T027 [P] [US3] Confirmar estilos do botão CTA (~`208×44`; fundo `#023C62`; texto `#FFFFFF`; raio alinhado ao design system) sob escopo da seção em `themes/custom/default/assets/css/perfil-destaque-estudante.css`
- [X] T028 [US3] Validar SC-009 / cenários US3 (clique → `/painel/estudante/perfil` ou gate de login vigente; rótulo = título do link) conforme `specs/024-perfil-destaque-estudante/quickstart.md` seção C

**Checkpoint**: CTA conversível e editável via CMS; sem hardcode de URL no Twig.

---

## Phase 6: User Story 5 — Bloco aparece só em Para Estudantes, após a Jornada (Priority: P1)

**Goal**: bloco só em `/para-estudantes` (`content_full` weight `2`); home / PE / QS sem `perfil_destaque_estudante`; ordem vertical hero (`021`) → benefícios (`022`, weight `0`) → jornada (`023`, weight `1`) → perfil (weight `2`); View `vagas` intacta.  
**Independent Test Criteria**: comparar `/para-estudantes`, `<front>`, `/para-empresas` e `/quem-somos`; confirmar ordem hero → benefícios → jornada → perfil (SC-004).

- [X] T029 [US5] Confirmar YAML do placement (path `/para-estudantes`, região `content_full`, weight **`2`**, UUID alinhado ao seed) em `config/sync/block.block.default_perfildestaqueestudante.yml`
- [X] T030 [P] [US5] Confirmar que configs/Twig/CSS do hero `021`, benefícios `022`, jornada `023` e home permanecem inalterados em `config/sync/block.block.default_views_block__banners_block_para_estudantes.yml`, `config/sync/block.block.default_beneficiosestudantes.yml`, `config/sync/block.block.default_jornadaestudante.yml`, `themes/custom/default/templates/block/block--block-jornada-estudante.html.twig` e escopo CSS (sem seletores em `.hero-estudantes-wrapper` / `.block-beneficios-estudantes` / `.be-*` / `.block-jornada-estudante` / `.je-*`)
- [X] T031 [US5] Validar SC-004 / cenários US5 (presença só em `/para-estudantes`; ordem hero → benefícios → jornada → perfil; ausência em home/PE/QS) conforme `specs/024-perfil-destaque-estudante/quickstart.md` seção E

**Checkpoint**: isolamento de rota e ordem de leitura garantidos.

---

## Phase 7: User Story 4 — Editor gerencia conteúdo sem código (Priority: P1)

**Goal**: editor com permissão edita título, ilustração, até 3 itens (ícone/título/descrição), ordem e CTA; tentativa de 4º item impedida (form alter + validação); mudanças refletem em `/para-estudantes` sem deploy de código.  
**Independent Test Criteria**: editar no painel, salvar, recarregar `/para-estudantes`; tentar 4º item e confirmar bloqueio (SC-003 / FR-004).

- [X] T032 [US4] Incluir permissões create/edit/delete do bundle `perfil_destaque_estudante` nas roles que já gerenciam block content e exportar diffs em `config/sync/user.role.*.yml` afetados
- [X] T033 [US4] Revisar form displays (widgets, labels, lista paragraphs default `item_lista_icone_p`, obrigatoriedade editorial do título do item se aplicável) em `config/sync/core.entity_form_display.block_content.perfil_destaque_estudante.default.yml` e `config/sync/core.entity_form_display.paragraph.item_lista_icone_p.default.yml`
- [X] T034 [US4] Estender limite max itens no form alter existente: bundle `perfil_destaque_estudante` max **3** (`hook_form_alter` ocultando/desabilitando “Add more” com ≥3 + validação de submit rejeitando >3; **sem** mutar storage `field_itens_lista`; preservar max 4 da jornada) em `modules/custom/custom_configs/custom_configs.module`
- [X] T035 [US4] Confirmar que nenhum copy institucional fica hardcoded como única fonte de verdade (placeholders Twig só para ausência; CTA via `field_link`) em `themes/custom/default/templates/block/block--block-perfil-destaque-estudante.html.twig` e `themes/custom/default/templates/paragraph/paragraph--item-lista-icone-p.html.twig`
- [X] T036 [US4] Validar SC-003 / cenários US4 (edição &lt;5 min; reorder; 4º item bloqueado; jornada ainda limita a 4) conforme `specs/024-perfil-destaque-estudante/quickstart.md` seção D

**Checkpoint**: conteúdo editorial gerenciável sem código; cardinalidade 3 enforced sem alterar storage.

---

## Phase 8: User Story 6 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

**Goal**: `custom_configs_update_11038` idempotente — ensure types/fields/displays; copiar assets seed; seed UUID `c0d1e2f3-a4b5-4678-c901-2def01234567` + ilustração + título + 3 itens + CTA; ensure placement weight `2`; **nunca** duplicar nem sobrescrever editorial divergente; fluxo `cim` → `updb` → `cim` → `cr`; `cex` versiona estrutural.  
**Independent Test Criteria**: ambiente desatualizado + fluxo padrão; 2ª `updb` sem duplicatas; editorial preservado (SC-005, SC-006, SC-007).

- [X] T037 [P] [US6] Versionar assets seed (1 ilustração + 3 ícones; **sem** commit de `sites/default/files`) em `modules/custom/custom_configs/assets/perfil-destaque-estudante/`
- [X] T038 [US6] Implementar `custom_configs_update_11038` idempotente (ensure `item_lista_icone_p` + fields + displays; ensure `perfil_destaque_estudante` + fields/handler lista → `item_lista_icone_p` + `field_link`/`field_image` + displays; copiar assets → `public://` se ausentes; seed `BlockContent` UUID `c0d1e2f3-a4b5-4678-c901-2def01234567` + ilustração + título “Seu perfil em destaque” + 3 `item_lista_icone_p` com copy das Assumptions + CTA “Completar meu perfil” → `/painel/estudante/perfil` **somente se vazios/ausentes**; ensure placement `default_perfildestaqueestudante` região `content_full` / pages `/para-estudantes` / weight **`2`**; mensagem Drush created/skipped; **nunca** sobrescrever editorial divergente; **nunca** alterar storage `field_itens_lista` nem hero `021`/benefícios `022`/jornada `023`/View `vagas`/PE/QS/home) em `modules/custom/custom_configs/custom_configs.install`
- [X] T039 [P] [US6] Extrair/reusar helpers privados (ensure type/fields/displays / copy assets / seed block+paragraphs / ensure placement) no mesmo arquivo se o padrão do módulo exigir, em `modules/custom/custom_configs/custom_configs.install`
- [X] T040 [US6] Exportar/confirmar configs estruturais com `drush cex -y` para os YAMLs listados em `specs/024-perfil-destaque-estudante/data-model.md` sob `config/sync/` (tipos, instances, displays, placement, `user.role.*`)
- [X] T041 [US6] Executar deploy local `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e reexecutar `updb` (SC-005/SC-006) conforme `specs/024-perfil-destaque-estudante/quickstart.md` seções A e F e `specs/024-perfil-destaque-estudante/contracts/deploy-perfil-destaque-estudante.md`
- [X] T042 [US6] Validar fallbacks pós-seed (título vazio omitido; ilustração ausente; zero/itens parciais; CTA vazio; SC-007) e isolamento pós-deploy conforme `specs/024-perfil-destaque-estudante/quickstart.md` seções E e F

**Checkpoint**: deploy 100% automatizado; `/para-estudantes` reproduzível sem admin manual.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: PRD cirúrgico, specify-rules, reforço opcional da regra de deploy, isolamento visual (hero 021 / benefícios 022 / jornada 023 / home) e aceite final SC-001–SC-009.

- [X] T043 [P] Atualizar §3.6 (rota `/para-estudantes`) documentando `perfil_destaque_estudante`, `item_lista_icone_p`, placement `content_full` weight `2` `/para-estudantes`, UUID `c0d1…`, hook `11038` e convivência com hero `021` + benefícios `022` + jornada `023` + View `vagas` em `PRD.md`
- [X] T044 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) para refletir feature `024-perfil-destaque-estudante` com caminhos `spec.md` / `plan.md` / `tasks.md`
- [X] T045 [P] Reforçar `.cursor/rules/drupal-deploy-configs.mdc` **somente se** T006 identificar lacuna vs. FR-019; caso contrário, no-op documentado
- [X] T046 Executar validação final completa (SC-001–SC-009; isolamento hero/benefícios/jornada/home “Como funciona”; checklist deploy) com `specs/024-perfil-destaque-estudante/quickstart.md` e `specs/024-perfil-destaque-estudante/checklists/requirements.md`
- [X] T047 Limpar cache Drupal após alterações Twig/CSS/config (`docker compose exec -T drupal vendor/bin/drush cr`)

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US5 (Phase 6) → US4 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US2 depende do markup/CSS base de US1 (T019–T021)
- US3 depende do CTA no Twig/CSS de US1 (T019/T018); fase curta de aceite
- US5 valida sobretudo o Foundational (T015); pode rodar em paralelo com US1 após Phase 2 (aceite completo após seed US6)
- US4 depende de form displays (T012/T013) + Twig com fallbacks (T021) + form alter max 3 (T034); ideal após seed mínimo (US6) ou conteúdo manual de teste
- US6 (hook/assets/cex) precisa dos YAMLs da Phase 2; validar pós-Twig mínimo (T019–T020)
- Polish após US1–US6

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP
                    ├→ US2 (P1)
                    ├→ US3 (P1) ──┐
                    ├→ US5 (P1) ──┤
                    ├→ US4 (P1) ──┤  (inclui form alter max 3)
                    └→ US6 (P1) ──┴→ Polish
```

## Parallel Execution Opportunities

- **Setup**: T002–T004 ∥ T006 após T001; T005 após T001
- **Foundational**: T008 ∥ T009 após T007; T010 após T008; T011 após T009; T012 ∥ após T010; T013/T014 após T011; T015 após UUID/plugin definido; T016 por último
- **US1**: T018 (CSS) em paralelo com início de T019 após T017; T020 ∥ T019 (arquivos distintos); T021 após markup estável; T022 por último
- **US2 ∥ US3 ∥ US5**: T023–T024 com T026–T027 e T029–T030 após T019/T020
- **US4**: após T013 + T021; T034 (form alter) paralelo a T032/T033; melhor com seed de US6
- **US6**: T037 (assets) paralelo ao início de T038; T039 junto a T038; T040–T042 após hook
- **Polish**: T043 ∥ T044 ∥ T045; depois T046–T047

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (desktop duas colunas + lista ícone + CTA em `/para-estudantes`) — valor principal da feature
2. **Mobile + CTA + isolamento de rota**: US2 + US3 + US5
3. **Editorial + deploy**: US4 (roles/form + max 3) + US6 (`11038` + assets + cim/updb/cim/cr + cex)
4. **Fechamento**: Polish (PRD §3.6 + specify-rules + aceite SC-001–SC-009)

## Format Validation

- Todas as tarefas usam `- [ ]`, ID sequencial (`T001`…`T047`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US3 → US5 → US4 → US6 (todas P1 na spec; ordem reflete dependências de implementação)
