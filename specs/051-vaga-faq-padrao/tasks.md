# Tasks: FAQ Padrão Único em Todas as Vagas

**Feature**: `specs/051-vaga-faq-padrao`  
**Branch**: `051-vaga-faq-padrao`  
**Plan**: [plan.md](plan.md) | **Spec**: [spec.md](spec.md) | **Deploy**: [contracts/deploy-faq-padrao.md](contracts/deploy-faq-padrao.md)  
**Render**: [contracts/vaga-faq-padrao-render.md](contracts/vaga-faq-padrao-render.md) | **Data model**: [data-model.md](data-model.md)  
**Hook**: `custom_configs_update_11074` (último existente: `11073`)  
**UUID FAQ padrão**: `c3d4e5f6-a7b8-4901-b234-56789abcdef0`

## Implementation Strategy

MVP primeiro, entrega incremental por user story (todas P1 no spec):

1. **US1 (render global)** — helper `custom_configs_load_faq_padrao()` + preprocess full monta `faq_paras` + Twig deixa de ler `field_vaga_faq`. Valor de negócio principal.
2. **US3 (omitir se inválido)** — mesmos filtros/access do pipeline US1; tarefas de validação dos estados unpublished / zero itens válidos / sem view access.
3. **US2 (form sem seletor)** — `_custom_configs_ensure_vaga_faq_form_hidden()` move `field_vaga_faq` para `region: hidden`; storage permanece.
4. **US4 (deploy)** — `custom_configs_update_11074` idempotente (ensure structure + seed + hide form) + `drush updb` → `cex` → `cr` na origem; destino `cim` → `updb` → `cim` → `cr`.

Sem PHPUnit (padrão do projeto). Validação manual via [quickstart.md](quickstart.md). Accordion/CSS 038 **intocados**.

---

## Phase 1: Setup

- [X] T001 Confirmar identidade da feature em `.specify/feature.json` (`feature_directory: specs/051-vaga-faq-padrao`) e artefatos presentes em `specs/051-vaga-faq-padrao/` (spec, plan, research, data-model, contracts, quickstart, checklists)
- [X] T002 [P] Confirmar predecessor 038 intacto: include `themes/custom/default/templates/content/includes/vaga-faq-accordion.html.twig` e CSS Accordion em `themes/custom/default/assets/css/` (sem redesign nesta feature)
- [X] T003 [P] Confirmar seed canônico UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` e helper `_custom_configs_seed_faq_collection_example()` em `modules/custom/custom_configs/custom_configs.install`

---

## Phase 2: Foundational

Pré-requisitos bloqueantes. Nenhuma user story de implementação começa antes destes checks.

- [X] T004 Confirmar em `modules/custom/custom_configs/custom_configs.install` que o último hook é `custom_configs_update_11073` e que o próximo número livre é `11074` (sem colisão)
- [X] T005 Confirmar helpers reutilizáveis `_custom_configs_ensure_faq_structure()`, `_custom_configs_ensure_faq_item_p()` e `_custom_configs_seed_faq_collection_example()` em `modules/custom/custom_configs/custom_configs.install`
- [X] T006 Confirmar em `themes/custom/default/templates/content/node--vagas--full.html.twig` (~linhas 177–198) que a seção FAQ ainda monta `faq_paras` a partir de `node.field_vaga_faq.entity` e inclui `vaga-faq-accordion.html.twig`
- [X] T007 Confirmar em `themes/custom/default/default.theme` que `default_preprocess_node__vagas__full()` hoje só injeta `show_empresa_card` + fallbacks de ícone benefícios — **sem** FAQ
- [X] T008 Confirmar em `config/sync/core.entity_form_display.node.vagas.default.yml` que `field_vaga_faq` está em `region: content` (autocomplete, weight 23, label “FAQ da vaga”)
- [X] T009 Confirmar ausência de `custom_configs_load_faq_padrao()` (ou equivalente) em `modules/custom/custom_configs/custom_configs.module`

---

## Phase 3: User Story 1 — Visitante vê o mesmo FAQ em qualquer vaga (P1)

**Goal**: Detalhe público de toda vaga obtém “Dúvidas Frequentes” exclusivamente da coleção FAQ padrão (UUID canônico); Accordion 038 reutilizado; valores legados em `field_vaga_faq` não influenciam o HTML.

**Independent Test**: Abrir ≥2 vagas publicadas distintas (com ou sem FAQ legado diferente) e comparar Accordion — perguntas, respostas e ordem idênticas ao FAQ padrão.

- [X] T010 [US1] Implementar `custom_configs_load_faq_padrao(): ?\Drupal\node\NodeInterface` em `modules/custom/custom_configs/custom_configs.module`: carregar node `faq` pelo UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` (via `entity.repository` / loadByProperties+uuid); retornar `NULL` se ausente
- [X] T011 [US1] Em `themes/custom/default/default.theme`, estender `default_preprocess_node__vagas__full()` conforme [contracts/vaga-faq-padrao-render.md](contracts/vaga-faq-padrao-render.md): chamar `custom_configs_load_faq_padrao()`; se node FAQ publicado + `access('view')`, filtrar `field_faq_itens` para paragraphs com `field_pergunta` **e** `field_resposta` não vazios após `trim`; injetar `faq_paras` (lista) e opcionalmente `faq_padrao`; anexar cache tags do node FAQ ao render do vaga
- [X] T012 [US1] Em `themes/custom/default/templates/content/node--vagas--full.html.twig`, remover a montagem local de `faq_paras` a partir de `node.field_vaga_faq`; usar só `faq_paras` do preprocess; manter include `@default/content/includes/vaga-faq-accordion.html.twig` e posição (após “Sobre a Empresa”, antes do CTA) inalterados
- [X] T013 [US1] Garantir que o Twig **não** inspeciona `node.field_vaga_faq` em nenhum ponto de `themes/custom/default/templates/content/node--vagas--full.html.twig` (FR-002)
- [X] T014 [US1] Validar SC-001/SC-002: abrir ≥3 vagas publicadas (incluindo uma com `field_vaga_faq` legado diferente, se existir) e confirmar Accordion idêntico ao FAQ padrão; expandir item → comportamento 038 (cards isolados)

---

## Phase 4: User Story 3 — Seção some só quando o FAQ padrão é inválido (P1)

**Goal**: Com FAQ padrão unpublished, inexistente ou sem itens válidos, **nenhuma** vaga exibe a seção; ao voltar a ser válido, todas voltam a exibir o mesmo conteúdo.

**Independent Test**: Despublicar (ou esvaziar) o FAQ padrão → abrir qualquer vaga → seção ausente; republicar com ≥1 item válido → seção reaparece em todas.

> **Nota**: a lógica de omitir já é entregue pelo pipeline US1 (T010–T012). Esta fase valida FR-004/FR-009/SC-005 e edge cases do filtro.

- [X] T015 [US3] Validar FAQ unpublished: despublicar o node FAQ UUID canônico; abrir qualquer vaga publicada → seção “Dúvidas Frequentes” ausente em 100% das amostradas
- [X] T016 [US3] Validar zero itens válidos: com FAQ publicado, remover/esvaziar pergunta ou resposta de todos os itens (ou deixar só itens com um dos campos vazios); abrir vaga → seção omitida
- [X] T017 [US3] Validar recuperação: republicar FAQ com ≥1 item válido (pergunta + resposta); `drush cr` se necessário; recarregar qualquer vaga → seção reaparece com conteúdo atualizado (SC-004/SC-005)
- [X] T018 [P] [US3] Validar item parcial: FAQ com mix de itens válidos e inválidos → só válidos no Accordion; ordem dos válidos preservada

---

## Phase 5: User Story 2 — Editor não escolhe FAQ por vaga (P1)

**Goal**: Formulário criar/editar Vagas não exibe “FAQ da vaga”; editor altera dúvidas só no conteúdo do FAQ padrão; mudanças refletem em todas as vagas sem editar cada anúncio.

**Independent Test**: Abrir form de vaga → seletor ausente; editar FAQ padrão → mudança no detalhe de qualquer vaga.

- [X] T019 [US2] Implementar `_custom_configs_ensure_vaga_faq_form_hidden(): string` em `modules/custom/custom_configs/custom_configs.install`: carregar form display `node.vagas.default`; mover `field_vaga_faq` para `region: hidden` (remover widget de content se necessário); no-op se já hidden; mensagem `t()` clara
- [X] T020 [US2] Confirmar que storage/instance de `field_vaga_faq` **não** são removidos em `modules/custom/custom_configs/custom_configs.install` / `config/sync` — apenas ocultação no form (R3)
- [X] T021 [US2] Validar no admin: criar/editar vaga → campo “FAQ da vaga” ausente (SC-003); editar node FAQ padrão (pergunta/resposta), publicar → detalhe de qualquer vaga reflete sem editar a vaga (FR-006/FR-007 / SC-004)
- [X] T022 [US2] Validar legado: vaga com `field_vaga_faq` apontando para outra coleção → público continua mostrando só o FAQ padrão (FR-002)

---

## Phase 6: User Story 4 — Deploy automatizado ativa o FAQ padrão (P1)

**Goal**: Um único `custom_configs_update_11074` idempotente + `drush cex` na origem; destino ativa FAQ padrão + form sem seletor com `cim` → `updb` → `cim` → `cr`, zero passos manuais no admin.

**Independent Test**: Aplicar receita em ambiente com vagas sem FAQ (ou FAQs distintos); auditar form + detalhe; reexecutar → zero duplicatas; `config/sync` versionado.

- [X] T023 [US4] Implementar `custom_configs_update_11074(): string` em `modules/custom/custom_configs/custom_configs.install` na ordem: `_custom_configs_ensure_faq_structure()` → `_custom_configs_ensure_faq_item_p()` → `_custom_configs_seed_faq_collection_example()` → `_custom_configs_ensure_vaga_faq_form_hidden()`; concatenar mensagens `t()`; **não** limpar `field_vaga_faq` nas vagas; **não** reabrir hooks antigos
- [X] T024 [US4] Garantir idempotência do hook `11074` em `modules/custom/custom_configs/custom_configs.install`: coleção existente → no-op / só preenche ausências (fill-if-empty); form já hidden → no-op; 0 duplicatas da coleção; 0 sobrescritas de editorial divergente (FR-012 / SC-006)
- [X] T025 [US4] Executar `drush updb -y` na origem (container Drupal) para rodar `custom_configs_update_11074` e validar mensagem de retorno
- [X] T026 [US4] Executar `drush cex -y` na origem para consolidar `config/sync/core.entity_form_display.node.vagas.default.yml` com `field_vaga_faq` em `region: hidden`
- [X] T027 [US4] Executar `drush cr` na origem para limpar cache Twig / registry / entity
- [X] T028 [US4] Auditar `config/sync/core.entity_form_display.node.vagas.default.yml` pós-`cex`: `field_vaga_faq` em `hidden` (ou ausente da region content); dependencies preservadas
- [X] T029 [US4] Revisar `specs/051-vaga-faq-padrao/contracts/deploy-faq-padrao.md` alinhado à receita destino `cim` → `updb` → `cim` → `cr` e pré-requisito `settings.php` → `config/sync`
- [X] T030 [US4] Validar pós-deploy: ≥2 vagas exibem o mesmo Accordion; form sem “FAQ da vaga”; `drush updb -y` de novo → no-op sem duplicar FAQ

---

## Phase 7: Polish & Cross-Cutting Concerns

- [X] T031 Atualizar cirurgicamente `PRD.md` §3.1: CT `faq` / `field_vaga_faq` — FAQ das vagas passa a ser **único/padrão** (coleção seed UUID canônico), não “associável por vaga”; formulário sem seletor; detalhe full menciona fonte global 051 (FR-014)
- [X] T032 Confirmar zero alteração em markup/CSS do Accordion: `themes/custom/default/templates/content/includes/vaga-faq-accordion.html.twig` e CSS 038 intocados (FR-008 / R6)
- [X] T033 Executar validação completa do [quickstart.md](quickstart.md) (cenários 1–5: mesmo FAQ, form, inválido, idempotência, regressões Benefícios/sidebar/CTA 050) e registrar resultado
- [X] T034 Verificar idempotência ponta a ponta: reexecutar `drush updb -y` → mensagem de no-op; `drush cex -y` → sem diff relevante em `config/sync` para form display FAQ
- [ ] T035 Commitar em pt-BR com Conventional Commits (quando solicitado): `feat: unifica FAQ padrão em todas as vagas` englobando `custom_configs.module` + `custom_configs.install` + `default.theme` + `node--vagas--full.html.twig` + `config/sync/core.entity_form_display.node.vagas.default.yml` + `PRD.md` + specs

---

## Dependencies

Ordem de conclusão das user stories:

```text
Phase 1 (Setup) ──┐
                  ├─► Phase 3 (US1: helper + preprocess + Twig) ──► Phase 4 (US3: validação omitir)
Phase 2 (Foundational) ┘                                              │
                                                                      │
Phase 5 (US2: hide form) ─────────────────────────────────────────────┤
                                                                      │
                                                                      ▼
                                                         Phase 6 (US4: hook 11074 + cex)
                                                                      │
                                                                      ▼
                                                         Phase 7 (Polish: PRD + quickstart + commit)
```

- **US1 → US3**: validação de omitir depende do pipeline de render já apontando para o FAQ padrão.
- **US2 ⊥ US1** em arquivos distintos (`custom_configs.install` form hide vs `custom_configs.module` + tema), mas o MVP público é US1; US2 pode seguir em paralelo após Foundational.
- **US4** agrega ensures de US2 + seed e deve rodar depois (ou encapsular) T019; o hook chama o mesmo ensure de hide.
- **Polish** após US1–US4 implementados e `cex` feito.

## Parallel Execution Examples

### Dentro de Phase 1 (Setup)

T002 e T003 são paralelizáveis — checks em caminhos distintos (tema vs install).

### Dentro de Phase 2 (Foundational)

T006, T007, T008, T009 são paralelizáveis — inspeções read-only em arquivos diferentes.

### US1 vs US2 (após Foundational)

- Agente A: T010 → T011 → T012 → T013 → T014 (módulo + tema)
- Agente B: T019 → T020 (install ensure hide) — **não** conflitar com T023 até mergear o ensure no hook

### Dentro de Phase 4 (US3)

T015, T016, T018 são validações manuais sequenciais no CMS (mesmo FAQ node); T017 depois da recuperação.

### Dentro de Phase 6 (US4)

T023 → T024 sequenciais (mesmo arquivo). T025 → T026 → T027 sequenciais (updb → cex → cr). T028 e T029 paralelizáveis após T026.

## Implementation Strategy (MVP)

1. Entregar **US1** (T010–T014) + `drush cr` → visitante já vê FAQ único.
2. Validar **US3** (T015–T018) no mesmo pipeline.
3. Entregar **US2** (T019–T022) + embutir no **US4** hook `11074` + `cex`.
4. Polish PRD + quickstart + commit.

Suggested MVP scope: **User Story 1** (render global). Sem US1, as demais stories não entregam valor público.

## Done When

- [X] tasks.md gerado com todas as phases, task IDs e file paths
- [X] Extension hooks despachados ou ignorados conforme regras (não há `.specify/extensions.yml` — skipped)
- [X] Completion report entregue ao usuário com contagem de tasks, breakdown por user story, escopo MVP e validação de formato
- [X] Implementação validada (helper + preprocess + Twig + hook `11074` + cex + PRD); T035 commit pendente de solicitação explícita
