# Tasks: Sidebar do Detalhe da Vaga — Ações e Resumo

**Input**: Artefatos em `specs/033-vaga-sidebar-acoes/`  
**Pré-requisitos**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, contracts, `quickstart.md`  
**Predecessor**: `032-vaga-header-empresa`  
**Hook**: `custom_configs_update_11050` (último anterior: `11049`)  
**Testes automatizados**: não solicitados — validação via quickstart e página da vaga seed

## Phase 1: Setup

- [x] T001 Confirmar artefatos SDD em `specs/033-vaga-sidebar-acoes/` e feature ativa em `.specify/feature.json` + `.cursor/rules/specify-rules.mdc`
- [x] T002 Inventariar sidebar atual em `themes/custom/default/templates/content/node--vagas--full.html.twig` (período via `field_horarios`; quantidade fixa “Não informado”)
- [x] T003 Confirmar rota `custom_panel.vagas_candidatar` (`POST /vaga/apply/{node}`) e `custom_panel.vagas_salvas_toggle` em `modules/custom/custom_panel/custom_panel.routing.yml`
- [x] T004 Confirmar ausência de `field_vaga_periodo` / `field_vaga_quantidade` e presença de `field_text_simple` / `field_regime_t`
- [x] T005 Confirmar hook livre `11050` em `modules/custom/custom_configs/custom_configs.install`

## Phase 2: Foundational

- [x] T006 Congelar fora de escopo: Match, Perfil, header, coluna principal, listagem, cards, rotas novas
- [x] T007 Documentar UUIDs seed 032 e a regra de não sobrescrever editorial divergente em `data-model.md`

## Phase 3: User Story 3 e 4 — campos, displays e seed

- [x] T008 [US3] Implementar ensures tipados de `field_vaga_periodo` (string) e `field_vaga_quantidade` (integer) + form/view displays em `custom_configs.install`
- [x] T009 [US4] Garantir sigla `PR` em `field_estados` (mapa sigla => rótulo) sem remover chaves em uso
- [x] T010 [US4] Reutilizar seeds EcoConstrutora / Engenheiro Civil e alinhar o trio legado 032 + período/quantidade vazios
- [x] T011 [US4] `custom_configs_update_11050` idempotente chamando ensures + seeds + align
- [x] T012 [US4] `drush updb -y` e `drush cex -y` na origem

## Phase 4: User Stories 1 e 2 — Twig, CSS, JS

- [x] T013 [US2] Parcial `vaga-sidebar-acoes.html.twig` com `path('custom_panel.vagas_candidatar')` e `data-*` do toggle
- [x] T014 [US1] Parcial `vaga-sidebar-resumo.html.twig` (período, bolsa, modelo, quantidade `%02d`)
- [x] T015 [US1] Incluir os parciais em `node--vagas--full.html.twig` sem Match/Perfil
- [x] T016 [US2] CSS `.vaga-detalhe__btn-candidatar` (80px, `#FD7B1A`) e `--vd-resumo-bg: #f4f6f9`
- [x] T017 [US2] JS lê `data-apply-url` / `data-toggle-url` e cancela o GET do link de candidatura
- [x] T018 [US3] Atualizar `PRD.md` §3.1.1 (campos e sidebar 033)

## Phase 5: Verificação

- [x] T019 Conferir vaga seed (Curitiba, PR, bolsa, período, “02”) e sidebar no browser
- [x] T020 `drush cr` após Twig/CSS/JS
