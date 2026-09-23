# Tasks: Deploy em Produção — Consolidação do Novo Layout (Home + Quem Somos)

**Input**: Artefatos de design em `specs/016-deploy-producao-layout/`  
**Pré-requisitos**: `plan.md` (obrigatório), `spec.md` (obrigatório), `research.md`, `data-model.md`, `contracts/deploy-runbook.md`, `quickstart.md`  
**Branch**: `dev`  
**Testes automatizados**: não solicitados — validação operacional via `quickstart.md` / SSH  
**Nota de setup**: `.specify/scripts` e `.specify/templates` ausentes neste repo; `FEATURE_DIR` = `specs/016-deploy-producao-layout` (via `.specify/feature.json`); template alinhado a `specs/015-cta-v1-quem-somos/tasks.md`  
**Natureza**: feature operacional (sem PHP/Twig/CSS novo). Deploy já executado em produção em 2026-09-23; tarefas consolidam o runbook normativo, ações permanentes e merge dos docs em `main`.

## Phase 1: Setup (Project Initialization)

**Objetivo**: confirmar baseline SDD, inventário do release consolidado e estado remoto/local antes de qualquer consolidação documental ou reexecução da esteira.

- [X] T001 Confirmar artefatos SDD completos (`spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/deploy-runbook.md`, `quickstart.md`, `checklists/requirements.md`) em `specs/016-deploy-producao-layout/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [X] T002 [P] Inventariar delta do release (`origin/main..dev` / commits docs pendentes) e confirmar Core 11.4.7 inalterado conforme `specs/016-deploy-producao-layout/plan.md` e `specs/016-deploy-producao-layout/data-model.md`
- [X] T003 [P] Confirmar referência versionada de `config_sync_directory` em `settings.php.prod` (deve apontar para `config/sync`) e contrastar com a regra em `.cursor/rules/drupal-deploy-configs.mdc`
- [X] T004 Confirmar invocação Drush normativa (`php vendor/bin/drush.php`) e workdir `/var/www/html` documentados em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md` e `specs/016-deploy-producao-layout/quickstart.md`

**Checkpoint**: artefatos e pré-condições inventariados; nenhuma alteração de produção nesta fase.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Objetivo**: tornar permanentes os pré-requisitos da esteira (ordem `cim` → `updb` → `cim` → `cr`, alinhamento de `settings.php`, Drush via `php`, proibições) para que US1–US4 sejam executáveis de forma repetível. **⚠️ CRITICAL**: nenhuma user story começa antes desta fase.

- [X] T005 Atualizar a receita normativa de destino para incluir a **2ª passada de `cim`** após `updb` (`cim` → `updb` → `cim` → `cr`) e o pré-requisito `settings.php` alinhado a `settings.php.prod` em `.cursor/rules/drupal-deploy-configs.mdc`
- [X] T006 [P] Garantir que o contrato liste pré-condições bloqueantes (SSH, workdir, `settings.php` → `config/sync`, tree limpo, `../backups/`, Drush invocável) em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T007 [P] Alinhar a seção "Plano de Release" / Assumptions da spec com a ordem validada e os 5 desvios (R2–R6) em `specs/016-deploy-producao-layout/spec.md` (já presentes — revisar consistência com T005–T006)
- [X] T008 Documentar exceção `custom_banners` (import cirúrgico Entity API → migração → hooks → `cim`) e higiene de órfãs via Entity API como referência permanente em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md` e `specs/016-deploy-producao-layout/research.md` (R3/R4)
- [X] T009 Sincronizar checklist "deploy fácil" e pré-voo com o contrato (backup + hash + settings + ordem + zero admin) em `specs/016-deploy-producao-layout/quickstart.md`

**Checkpoint**: regras e contrato refletem a ordem validada; próximos deploys têm a mesma receita.

---

## Phase 3: User Story 1 — Backup de segurança antes de qualquer mudança (Priority: P1) 🎯 MVP

**Goal**: portão FR-001/FR-002 — dump PostgreSQL 16 gzipado com timestamp em `../backups/` (fora de `web/`), verificado (existência, tamanho > 0, `gzip -t`) **antes** de qualquer alteração; FR-003 — hash de rollback registrado.  
**Independent Test Criteria**: após o dump, listar `../backups/` e confirmar arquivo recente, tamanho > 0 e gzip íntegro; sem isso a esteira não avança (SC-001).

- [X] T010 [US1] Confirmar/ajustar comando de backup normativo (`sql-dump` + `--gzip` + timestamp + destino `../backups/`) e criação do diretório em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md` (passo 0) e `specs/016-deploy-producao-layout/quickstart.md` seção A
- [X] T011 [P] [US1] Documentar gates de abort (dump falhou/vazio/gzip inválido ⇒ nada alterado) alinhados a FR-001/FR-002 e edge cases da spec em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T012 [US1] Documentar registro obrigatório de `git rev-parse HEAD` (alvo de rollback, FR-003) imediatamente após backup OK em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md` e `specs/016-deploy-producao-layout/quickstart.md` seção A
- [X] T013 [US1] Validar (ou revalidar em próximo deploy) o portão de backup via SSH conforme `specs/016-deploy-producao-layout/quickstart.md` seção A — arquivo existe, > 0, fora de `web/`, `gzip -t` OK; falha ⇒ abort

**Checkpoint**: backup verificado + hash anotado = único estado que autoriza a esteira (MVP do release).

---

## Phase 4: User Story 2 — Esteira de deploy 100% automatizada (Priority: P1)

**Goal**: sequência Git → Composer → `cim` → `updb` → `cim` → `cr` sem passo manual no admin (FR-004–FR-010); cada etapa com exit 0 antes da próxima; falha ⇒ parar e avaliar rollback.  
**Independent Test Criteria**: em destino desatualizado (ou próximo release), cada comando conclui exit 0 e o estado final (banco + config ativa) corresponde a `config/sync` + hooks executados (SC-002, SC-008).

- [X] T014 [US2] Congelar a sequência normativa completa (passos 2–7: fetch/checkout/merge/pull → `composer install --no-dev --optimize-autoloader` → `cim -y` → `updb -y` → `cim -y` → `cr`) com saídas esperadas em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T015 [P] [US2] Alinhar comandos da seção B do quickstart à mesma ordem (incluindo 2ª `cim`) em `specs/016-deploy-producao-layout/quickstart.md`
- [X] T016 [US2] Documentar proibições da esteira (admin UI, `core/`/`vendor/`, `.env`/`settings.php` pela esteira, SQL direto, forçar merge, `updb` em loop) em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T017 [US2] Documentar tratamento de falha por etapa (composer falhou ⇒ banco intacto; `updb` parcial ⇒ rollback; conflito merge ⇒ abortar e resolver na origem) conforme edge cases em `specs/016-deploy-producao-layout/spec.md` e `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T018 [US2] Consolidar código/docs do release: garantir `main` contém o conteúdo de `origin/dev` (merge dos commits desta spec, ex.: docs `f0fb6627`) conforme `specs/016-deploy-producao-layout/plan.md` Phase 5 — via Git em repositório local e, se aplicável, pull no servidor `/var/www/html`
- [X] T019 [US2] Validar idempotência pós-sucesso (`updb` = no-op; `cim` sem diffs) conforme `specs/016-deploy-producao-layout/quickstart.md` seção D (SC-008)

**Checkpoint**: esteira documentada e reproduzível; docs do release consolidados em `main` quando T018 concluir.

---

## Phase 5: User Story 3 — Visitante vê o novo layout sem erros (Priority: P1)

**Goal**: após a esteira, anônimo em `<front>` e `/quem-somos` obtém HTTP 200 ≤ 3 s, seletores do novo layout, full-width corretos, zero 500/WSOD e log sem erros novos (FR-011–FR-014, SC-003–SC-005).  
**Independent Test Criteria**: `curl` das duas rotas + grep de seletores + `watchdog:show` conforme `quickstart.md` seção C.

- [X] T020 [US3] Congelar contrato de verificação (`<front>`: `.container`, carrossel, hero search, blocos 004–008, rodapé; `/quem-somos`: banner → Sobre Nós → Missão/Visão → Diferenciais → Impact Numbers → `.cta-v1`/`.block-cta-v1`) em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T021 [P] [US3] Garantir comandos `curl`/`grep` e critérios mobile (sem scroll horizontal ≤575.98px) em `specs/016-deploy-producao-layout/quickstart.md` seção C
- [X] T022 [US3] Incluir gate de log (`php vendor/bin/drush.php watchdog:show --severity=Error --count=10`) sem erros novos pós-deploy em `specs/016-deploy-producao-layout/quickstart.md` e `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T023 [US3] Executar (ou revalidar) verificação anônima das rotas críticas e seletores conforme `specs/016-deploy-producao-layout/quickstart.md` seção C — HTTP 200 ≤ 3 s, HTML com elementos-chave, log limpo

**Checkpoint**: valor público do release confirmado nas duas rotas.

---

## Phase 6: User Story 4 — Rollback completo em caso de falha (Priority: P1)

**Goal**: procedimento documentado e acionável — `git reset --hard <hash>` + `composer install` + restore do dump + `cr` + verificação; RTO ≤ 15 min; RPO = início do deploy (FR-015, SC-007).  
**Independent Test Criteria**: seguir seção E do quickstart / contrato de rollback e obter rotas 200 com layout anterior (ensaio documentado ou execução real em falha).

- [X] T024 [US4] Congelar passos de rollback (código → composer → `gunzip -c … | sql-cli` → `cr` → verificar → reportar) com SLA RTO/RPO em `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T025 [P] [US4] Alinhar comandos da seção E do quickstart ao contrato (mesmos artefatos: hash registrado + dump do pré-voo) em `specs/016-deploy-producao-layout/quickstart.md`
- [X] T026 [US4] Documentar gatilhos de acionamento (falha irrecuperável pós-banco/config, verificação reprovada, 500 persistente) e o que o rollback **não** preserva (editorial da janela) em `specs/016-deploy-producao-layout/spec.md` e `specs/016-deploy-producao-layout/contracts/deploy-runbook.md`
- [X] T027 [US4] Confirmar prontidão operacional: dump do pré-voo + hash registrados disponíveis para o release atual (checklist itens 1–2 e 8) em `specs/016-deploy-producao-layout/quickstart.md`

**Checkpoint**: saída segura do release está documentada e os artefatos de rollback existem.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Objetivo**: fechar consolidação documental, checklist de aceite e referência permanente para próximos deploys.

- [X] T028 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT) se necessário para refletir feature `016-deploy-producao-layout` e caminhos `spec.md`/`plan.md`/`tasks.md`
- [X] T029 [P] Revisar consistência cruzada spec ↔ plan ↔ research ↔ data-model ↔ contract ↔ quickstart (ordem da esteira, desvios R2–R6, SC-001–SC-008) sob `specs/016-deploy-producao-layout/`
- [X] T030 Completar checklist de requirements / "deploy fácil" (itens 1–8) em `specs/016-deploy-producao-layout/checklists/requirements.md` e `specs/016-deploy-producao-layout/quickstart.md`
- [X] T031 Merge final dos commits de docs desta feature de `dev` → `main` (e pull no servidor se aplicável) conforme `specs/016-deploy-producao-layout/plan.md` Phase 5 — deixar `contracts/deploy-runbook.md` como referência normativa dos próximos deploys

---

## Dependencies & Execution Order

- Setup (Phase 1) → Foundational (Phase 2) → US1 (Phase 3) → US2 (Phase 4) → US3 (Phase 5) → US4 (Phase 6) → Polish (Phase 7)
- US1 é portão bloqueante de US2 (sem backup verificado + hash, esteira não inicia)
- US2 deve concluir (ou já ter concluído em 2026-09-23) antes da verificação US3
- US4 depende dos artefatos produzidos em US1 (dump + hash); documentação pode avançar em paralelo após T012
- Polish após US1–US4 documentados/validados; T031 por último

## Dependency Graph

```text
Phase1 → Phase2 → US1 (P1) 🎯 MVP (backup + hash)
                    └→ US2 (P1) esteira
                         └→ US3 (P1) verificação
                    US1 artefatos ──→ US4 (P1) rollback
                         └→ Polish (merge docs + checklist)
```

## Parallel Execution Opportunities

- **Setup**: T002 ∥ T003 após T001; T004 após T001
- **Foundational**: T006 ∥ T007 após início de T005; T008 ∥ T009 após contrato estável
- **US1**: T011 ∥ T012 após T010; T013 por último (execução)
- **US2**: T015 ∥ T016 após T014; T017 após T014; T018/T019 após documentação estável
- **US3**: T021 ∥ T022 após T020; T023 por último
- **US4**: T025 ∥ T026 após T024; T027 após artefatos US1
- **Polish**: T028 ∥ T029; depois T030 → T031

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 — portão de backup/hash (sem isso não há deploy seguro)
2. **Esteira**: US2 — sequência normativa + consolidação `dev` → `main`
3. **Aceite público**: US3 — rotas e seletores
4. **Saída segura**: US4 — rollback documentado e artefatos prontos
5. **Fechamento**: Polish — rules, checklist, merge final dos docs

## Format Validation

- Todas as tarefas usam `- [ ]`/`- [X]`, ID sequencial (`T001`…`T031`), caminho de arquivo e label `[USn]` nas fases de história
- Marcador `[P]` apenas onde arquivos/trabalhos distintos permitem paralelismo real
- Sem tasks de teste automatizado (não solicitadas na spec)
- Ordem de histórias por prioridade P1: US1 → US2 → US3 → US4 (todas P1; US1 é portão de US2; US4 consome artefatos de US1)
