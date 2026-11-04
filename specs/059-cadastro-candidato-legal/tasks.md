# Tasks: Cadastro de Candidato (UX) e Páginas Legais

**Input**: Artefatos de design em `specs/059-cadastro-candidato-legal/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/cadastro-candidato-render.md`, `contracts/legal-pages-seed.md`, `contracts/deploy-cadastro-candidato-legal.md`, `quickstart.md`  
**Branch sugerida**: `main` (workflow do projeto) ou `feat/cadastro-candidato-legal`  
**Predecessors**: `058-refino-figma-contato`, `035-refatorar-login`, `008-rodape-redesign`, `057-seed-arquivos-imutaveis`  
**Hook**: `custom_configs_update_11089` (seed Páginas Básicas legais + aliases + desligar banner View legado)  
**Testes automatizados**: não solicitados — validação manual via `quickstart.md` (sem PHPUnit)  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/059-cadastro-candidato-legal` (via `.specify/feature.json`); template alinhado a `specs/058-refino-figma-contato/tasks.md`  
**Config estrutural**: `field.field.user.user.field_termo` + `block.block.default_views_block__banners_block_2` via `drush cex`; destino `cim` → `updb` → `cim` → `cr`

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD e inventariar form/Twig/CSS/login/field_termo/banner legado antes de mutar código.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/cadastro-candidato-render.md`, `contracts/legal-pages-seed.md`, `contracts/deploy-cadastro-candidato-legal.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/059-cadastro-candidato-legal/` e feature ativa `059-cadastro-candidato-legal` em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar `CandidatoRegistrationForm` (seções, `field_termo`, CAPTCHA, submit/redirect atual para `custom_configs_users.candidato_perfil`) em `modules/custom/custom_configs/modules/custom_configs_users/src/Form/CandidatoRegistrationForm.php`
- [X] T003 [P] Inventariar rota `/cadastro/candidato` e form ID em `modules/custom/custom_configs/modules/custom_configs_users/custom_configs_users.routing.yml`
- [X] T004 [P] Inventariar padrão visual de card/Form API do login (035) em `themes/custom/default/templates/layout/page--user--login.html.twig` e library/alter relacionados em `themes/custom/default/` + `modules/custom/custom_panel/`
- [X] T005 [P] Inventariar description atual de `field_termo` (links `href="#"`) em `config/sync/field.field.user.user.field_termo.yml`
- [X] T006 [P] Inventariar placement do View banners `block_2` (região `banner`, pages `/cadastro/candidato`, `status: true`) em `config/sync/block.block.default_views_block__banners_block_2.yml`
- [X] T007 Inventariar último `hook_update_N` (`11088`) e helpers de seed de página (`_custom_configs_ensure_contato_page` e similares) para espelhar padrão de alias/body em `modules/custom/custom_configs/custom_configs.install`

**Checkpoint**: inventário alinhado a plan/research/data-model/contracts; nenhuma alteração de código nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: congelar escopo negativo e confirmar superfície de mudança (suggestion Twig, hook livre, UUIDs, sem CT novos) antes das user stories.

**⚠️ CRITICAL**: Nenhuma user story de implementação começa antes desta fase.

- [X] T008 Congelar escopo negativo: **não** criar content/block/paragraph types nem field storages novos; **não** editar `core/`/`vendor/`; **não** redesenhar `/cadastro/empresa` nem login; **não** alterar regras CPF/paragraphs/papéis/verify-mail; **não** seed File entity do banner (assets estáticos no tema; política 057); **não** mutar token global `--brand-orange`; **não** introduzir pipeline SCSS
- [X] T009 Confirmar suggestion nativa `page__cadastro__candidato` → `page--cadastro--candidato.html.twig`, rota anônima, e que o próximo hook livre é `11089` — anotar sob DevTools + `specs/059-cadastro-candidato-legal/data-model.md` / `plan.md`
- [X] T010 Confirmar UUIDs canônicos e aliases estritos (Termos `a1b2c3d4-e5f6-4789-a012-3456789abc01` → `/termos-de-uso`; Política `a1b2c3d4-e5f6-4789-a012-3456789abc02` → `/politica-de-privacidade`) e bundle `page` disponível conforme `specs/059-cadastro-candidato-legal/contracts/legal-pages-seed.md`

**Checkpoint**: escopo congelado; suggestion/hook/UUIDs confirmados; pronto para US1.

---

## Phase 3: User Story 1 — Visitante encontra Termos e Política publicados (Priority: P1) 🎯 MVP

**Goal**: Seed idempotente de Nodes `page` “Termos de Uso” e “Política de Privacidade” com aliases `/termos-de-uso` e `/politica-de-privacidade`; body placeholder só se vazio; reuso seguro em alias alheio; rodapé deixa de 404 (FR-001–FR-004 / SC-001 / SC-007).  
**Independent Test Criteria**: após `drush updb`, GET das duas URLs → 200 + título/corpo; links Institucionais do rodapé; 2º `updb` sem duplicata nem overwrite editorial (`quickstart.md` seção B / `contracts/legal-pages-seed.md`).

- [X] T011 [US1] Implementar helper idempotente `_custom_configs_ensure_legal_page($uuid, $title, $alias, $body_html)` (load UUID → reuso; senão alias de `page` publicado → reuso; senão criar; body só se vazio; alias `pt-br` faltante; mensagens created/reused/skipped) em `modules/custom/custom_configs/custom_configs.install` (research R4 / `contracts/legal-pages-seed.md`)
- [X] T012 [US1] Criar `custom_configs_update_11089` chamando o helper para Termos e Política com UUIDs/aliases/placeholders canônicos; pré-checar bundle `page` (mensagem + retorno seguro se ausente) em `modules/custom/custom_configs/custom_configs.install`
- [X] T013 [US1] Executar `docker compose exec drupal drush updb -y` e validar SC-001 / cenários US1 (200, títulos, HTML estruturado, rodapé, reexecução idempotente) conforme `specs/059-cadastro-candidato-legal/quickstart.md` seção B e `specs/059-cadastro-candidato-legal/contracts/legal-pages-seed.md`

**Checkpoint**: URLs legais respondem — MVP de conformidade/rodapé.

---

## Phase 4: User Story 2 — Candidato vê banner e formulário em card moderno (Priority: P1)

**Goal**: `/cadastro/candidato` com banner `<picture>` (desktop/mobile), overlay, H1 canônico e formulário em card centralizado; View `banners` `block_2` desligado nesta rota (FR-005–FR-007 / FR-013 / SC-003 / SC-004).  
**Independent Test Criteria**: desktop ≥992px e mobile ≤575px — banner + card; sem segundo banner; sem overflow-x (`quickstart.md` seção C / `contracts/cadastro-candidato-render.md`).

- [X] T014 [P] [US2] Adicionar placeholders de banner (desktop ~1920×400, mobile ~800×600; webp/jpg) em `themes/custom/default/assets/images/cadastro-candidato/` (`hero-desktop.webp|jpg`, `hero-mobile.webp|jpg`)
- [X] T015 [US2] Criar page suggestion com banner `<picture>` (eager/fetchpriority, overlay, H1 *“Crie sua conta e encontre seu estágio ideal”*) + card Bootstrap (`col-12 col-lg-6`, `shadow-sm`, `rounded-4`) envolvendo `page.highlighted` + `page.content` em `themes/custom/default/templates/layout/page--cadastro--candidato.html.twig` (research R1/R2)
- [X] T016 [P] [US2] Criar CSS escopado (banner, overlay, fallback navy, card, ocultar `default_page_title`, anti-overflow) em `themes/custom/default/assets/css/components/cadastro-candidato.css`
- [X] T017 [US2] Registrar library `cadastro_candidato` apontando o CSS novo em `themes/custom/default/default.libraries.yml`
- [X] T018 [US2] Desligar placement legado: `status: false` (ou visibility vazia) de `block.block.default_views_block__banners_block_2` via config + ensure no `custom_configs_update_11089` em `config/sync/block.block.default_views_block__banners_block_2.yml` e `modules/custom/custom_configs/custom_configs.install` (FR-013)
- [X] T019 [US2] Validar SC-003/SC-004 e cenários US2 (picture, card, sem View banner duplicado, 375/1440) conforme `specs/059-cadastro-candidato-legal/quickstart.md` seção C e `specs/059-cadastro-candidato-legal/contracts/cadastro-candidato-render.md`

**Checkpoint**: primeira impressão da jornada de inscrição alinhada ao login/marca.

---

## Phase 5: User Story 3 — Candidato preenche formulário legível e alinhado à marca (Priority: P1)

**Goal**: Classes Bootstrap grandes via Form API; labels negrito escuros; seções distinguíveis; submit laranja `#FD7B1A` full width; Twig só estrutura (FR-008–FR-010 / FR-012 / FR-014–FR-015).  
**Independent Test Criteria**: percorrer seções; inspecionar inputs/labels/botão; sem rewrite de templates de elemento do core (`quickstart.md` seção D / contract render).

- [X] T020 [US3] Anexar library `default/cadastro_candidato` via `#attached` no form (preferir `hook_form_custom_configs_users_candidato_registration_form_alter`) em `modules/custom/custom_configs/modules/custom_configs_users/custom_configs_users.module` e/ou `modules/custom/custom_configs/modules/custom_configs_users/src/Form/CandidatoRegistrationForm.php` (research R3)
- [X] T021 [US3] Aplicar classes Form API: inputs text/email/password `form-control form-control-lg`; labels `form-label fw-bold text-dark`; submit `btn btn-primary btn-lg w-100 mt-4`; reforçar agrupadores de seção existentes — sem tocar `EmpresaRegistrationForm` — nos mesmos arquivos do T020
- [X] T022 [US3] Forçar cor do submit `#FD7B1A` (hover levemente mais escuro) sob escopo `.page-cadastro-candidato` / card **sem** mutar `--brand-orange` global em `themes/custom/default/assets/css/components/cadastro-candidato.css` (research R7)
- [X] T023 [US3] Validar cenários US3 (seções, inputs lg, labels, botão laranja full width; Twig limpo) conforme `specs/059-cadastro-candidato-legal/quickstart.md` seção D e `specs/059-cadastro-candidato-legal/contracts/cadastro-candidato-render.md`

**Checkpoint**: formulário longo legível e com identidade de marca.

---

## Phase 6: User Story 4 — Candidato aceita Termos e Política com links reais (Priority: P1)

**Goal**: Checkbox `form-check` com links reais `/termos-de-uso` e `/politica-de-privacidade` em nova aba; validação de aceite preservada; empresa intocada (FR-011 / SC-002).  
**Independent Test Criteria**: clicar links do checkbox → URLs canônicas + `target="_blank"`; envio sem aceite bloqueado (`quickstart.md` seção D / field config).

- [X] T024 [US4] Atualizar description de `field.field.user.user.field_termo` com links `<a href="/termos-de-uso" …>` e Política + `target="_blank" rel="noopener noreferrer"` via FieldConfig/API e exportar com `drush cex` → `config/sync/field.field.user.user.field_termo.yml` (research R3)
- [X] T025 [US4] Garantir aparência `form-check` / `form-check-input` no checkbox de aceite via Form API (alter/`buildForm`) em `modules/custom/custom_configs/modules/custom_configs_users/custom_configs_users.module` e/ou `modules/custom/custom_configs/modules/custom_configs_users/src/Form/CandidatoRegistrationForm.php`; confirmar que `EmpresaRegistrationForm` continua com título hardcoded próprio
- [X] T026 [US4] Validar SC-002 e cenários US4 (links reais, nova aba, bloqueio sem aceite, visual checkbox) conforme `specs/059-cadastro-candidato-legal/quickstart.md` seção D

**Checkpoint**: aceite compliance com páginas reais (depende de US1 publicada).

---

## Phase 7: User Story 5 — Candidato conclui o cadastro sem regressão (Priority: P1)

**Goal**: Submit válido cria usuário; login automático → `/painel/estudante/perfil`; CAPTCHA utilizável; erros legíveis no card; fluxos verify/pending inalterados (FR-016–FR-017 / SC-005 / SC-006).  
**Independent Test Criteria**: cadastro válido → usuário + redirect painel; CAPTCHA staging ≥3 submits; erros no card (`quickstart.md` seção E / contract redirect).

- [X] T027 [US5] Trocar redirect de sucesso com `user_login_finalize` de `custom_configs_users.candidato_perfil` (`/meu-perfil`) para `custom_panel.painel_estudante_perfil` (`/painel/estudante/perfil`); manter ramos verify-mail/pending em `<front>` em `modules/custom/custom_configs/modules/custom_configs_users/src/Form/CandidatoRegistrationForm.php` (research R5)
- [X] T028 [US5] Ajustar espaçamento CSS do wrapper `.captcha` dentro do card (sem alterar ponto CAPTCHA/`disable_captcha`) em `themes/custom/default/assets/css/components/cadastro-candidato.css` (research R8)
- [X] T029 [US5] Validar SC-005/SC-006 e cenários US5 (criação, redirect painel, CAPTCHA, erros no card) conforme `specs/059-cadastro-candidato-legal/quickstart.md` seção E e `specs/059-cadastro-candidato-legal/contracts/cadastro-candidato-render.md`

**Checkpoint**: estética não compromete conversão nem anti-bot.

---

## Phase 8: User Story 6 — Editorial atualiza textos legais sem novo deploy de copy (Priority: P2)

**Goal**: Editor altera `body` no admin; URL/alias estáveis; reexecução do seed não sobrescreve (FR-004 / SC-007).  
**Independent Test Criteria**: editar parágrafo → URL pública atualiza; 2º `updb` preserva editorial (`quickstart.md` seção B itens 3–4).

- [X] T030 [US6] Confirmar editabilidade dos Nodes seed (permissões/admin) e que alias permanece após save editorial — smoke em `/admin/content` + URLs públicas; documentar resultado breve em `specs/059-cadastro-candidato-legal/quickstart.md` se gap encontrado
- [X] T031 [US6] Reexecutar `docker compose exec drupal drush updb -y` após alteração editorial e validar no-op de body (SC-007) conforme `specs/059-cadastro-candidato-legal/contracts/legal-pages-seed.md`

**Checkpoint**: seed é placeholder; valor de negócio na editabilidade.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Objetivo**: `drush cex`, PRD cirúrgico, regressões, receita destino, aceite SC-001–SC-008 (≤15 min).

- [X] T032 Executar na origem `docker compose exec drupal drush cex -y` e garantir sync versionado de `config/sync/field.field.user.user.field_termo.yml` + `config/sync/block.block.default_views_block__banners_block_2.yml` (e demais diffs estruturais reais) conforme `specs/059-cadastro-candidato-legal/contracts/deploy-cadastro-candidato-legal.md`
- [X] T033 [P] Atualizar `PRD.md` §10 de forma **cirúrgica** com rotas legais canônicas + nota de cadastro candidato (banner/card/redirect painel); sem expandir escopo
- [X] T034 [P] Confirmar bloco SPECKIT em `.cursor/rules/specify-rules.mdc` aponta para `059-cadastro-candidato-legal` com `spec.md` / `plan.md` / `tasks.md`
- [X] T035 [P] Smoke de regressão: `/cadastro/empresa`, `/user/login`, home e demais rotas — CSS de cadastro candidato não vaza — conforme `specs/059-cadastro-candidato-legal/quickstart.md` seção F
- [X] T036 Executar limpeza de cache (`docker compose exec drupal drush cr`) após Twig/CSS/libraries/hooks
- [X] T037 Documentar/validar receita destino `cim` → `updb` (`11089`) → `cim` → `cr` e gates pós-deploy em `specs/059-cadastro-candidato-legal/quickstart.md` / `contracts/deploy-cadastro-candidato-legal.md`
- [X] T038 Executar validação final completa (SC-001–SC-008; edges: alias conflitante, asset ausente/fallback, tablet, CAPTCHA off, usuário autenticado) com `specs/059-cadastro-candidato-legal/quickstart.md` e `specs/059-cadastro-candidato-legal/checklists/requirements.md`

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → US5 (Phase 7) → US6 (Phase 8) → Polish (Phase 9)
- US1 (páginas legais) é MVP e desbloqueia links reais do aceite (US4) e do rodapé
- US2 (banner/card) pode iniciar após Foundational em paralelo com finalização de validação US1 (Twig/CSS/assets distintos do `.install`)
- US3 (Form API classes) depende da library (T017) e idealmente do Twig/card (T015)
- US4 (aceite) depende de US1 publicada + pode compartilhar alter Form API com US3 — sequenciar após T020–T021 se um único agente
- US5 (redirect/CAPTCHA) pode paralelizar CSS do captcha com US3; redirect no Form é independente após inventário
- US6 valida comportamento já garantido por T011–T012; executar após US1 estável
- Polish após US1–US6

## Dependency Graph

```text
Phase1 → Phase2 (escopo + suggestion + UUIDs + hook 11089)
           ├→ US1 (P1) seed legal 11089 ── MVP
           │     └→ US4 (P1) field_termo links + form-check
           │           └→ US6 (P2) editorial + re-run updb
           ├→ US2 (P1) Twig picture + CSS + library + banner block off
           │     └→ US3 (P1) Form API classes + #FD7B1A + #attached
           │           └→ US5 (P1) redirect painel + captcha spacing
           └→ Polish (cex, PRD, regressão, cim/updb/cim/cr, SC-001–SC-008)
```

## Parallel Execution Opportunities

- **Setup**: T002–T006 em paralelo após T001; T007 após inventário form/config
- **Foundational**: T009 ∥ T010 após T008
- **US1**: T011 → T012 → T013 (sequencial no `.install`)
- **US2 ∥ validação US1**: T014 [P] ∥ T016 [P] após Foundational; T015 → T017; T018 pode ir no mesmo `11089` de US1 (coordenar merge no `.install`); T019 por último na história
- **US3**: T020 → T021; T022 [CSS] pode ∥ T021 se agentes distintos; T023 por último
- **US4**: após US1 (páginas) + preferível pós T020; T024 (cex field) ∥ T025 (form-check) com cuidado; T026 por último
- **US5**: T027 (PHP redirect) ∥ T028 (CSS captcha); T029 por último
- **US6**: T030 → T031 após US1
- **Polish**: T033 ∥ T034 ∥ T035 após T032; depois T036 → T037 → T038

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (seed Termos/Política + aliases) — elimina 404 do rodapé
2. **Shell visual**: US2 (banner `<picture>` + card + desligar View legado)
3. **Form marca**: US3 (classes Form API + botão `#FD7B1A`) → US4 (links reais do aceite)
4. **Conversão**: US5 (redirect painel + CAPTCHA)
5. **Editorial**: US6 (editabilidade + idempotência)
6. **Fechamento**: Polish — `cex`, PRD §10, regressões, receita destino, SC-001–SC-008

## Format Validation

- Todas as tarefas usam checkbox, ID sequencial (`T001`…`T038`), caminho de arquivo e label `[USn]` nas fases de história
- Setup, Foundational e Polish sem label de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec; validação manual no quickstart)
- Ordem de histórias: US1–US5 (P1) → US6 (P2) → Polish
