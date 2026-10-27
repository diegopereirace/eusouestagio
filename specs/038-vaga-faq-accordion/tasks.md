# Tasks: Bloco "Dúvidas Frequentes" (FAQ Accordion) no Detalhe da Vaga

**Feature**: `specs/038-vaga-faq-accordion`  
**Branch**: `feature-vaga` (identidade da feature: `038-vaga-faq-accordion`)  
**Plan**: [plan.md](plan.md) | **Spec**: [spec.md](spec.md) | **Deploy**: [contracts/deploy-faq-accordion.md](contracts/deploy-faq-accordion.md) | **Render**: [contracts/vaga-faq-accordion-render.md](contracts/vaga-faq-accordion-render.md)  
**Hook**: `custom_configs_update_11059` (último existente: `11058`)

## Implementation Strategy

MVP primeiro, entrega incremental por user story (todas P1):

1. **US4 (deploy)** — núcleo automatizado: ensures FAQ existentes + seed coleção + `_custom_configs_link_vaga_engenheiro_civil_faq()` + `hook_update_11059` + `drush cex` na origem. Entrega o vínculo Engenheiro Civil → FAQ seed sem depender de Twig novo.
2. **US1 (visitante Accordion)** — include Twig + chamada no full + CSS de cards isolados (sem `data-bs-parent`; filtro pergunta **e** resposta). Depende do vínculo de US4 para a vaga seed mostrar os 2 itens.
3. **US2 (sem órfão)** — validar omissão da seção quando FAQ ausente/unpublished/sem itens válidos. A lógica de filtro entra com US1; esta fase fecha os cenários de regressão.
4. **US3 (editor)** — validar fluxo CMS já existente (CT `faq` / `field_vaga_faq`) e omissão de item inválido no Accordion (após US1).

Sem suite PHPUnit (padrão do projeto). Validação manual via [quickstart.md](quickstart.md). **Sem View** `faq_vaga` / bloco Views.

## Phase 1: Setup

- [X] T001 Confirmar identidade da feature em `.specify/feature.json` (`feature_directory: specs/038-vaga-faq-accordion`) e artefatos presentes em `specs/038-vaga-faq-accordion/` (spec, plan, research, data-model, contracts, quickstart, checklists)
- [X] T002 [P] Confirmar branch de trabalho alinhada ao plan (`feature-vaga`) e decisão R1 em `specs/038-vaga-faq-accordion/research.md`: **sem** View `faq_vaga` / `block_faq*` — renderização nativa de `field_vaga_faq`

## Phase 2: Foundational

Pré-requisitos bloqueantes. Nenhuma user story começa antes destes checks.

- [X] T003 Confirmar em `modules/custom/custom_configs/custom_configs.install` que o último hook é `custom_configs_update_11058` e que o próximo número livre é `11059` (sem colisão)
- [X] T004 Confirmar helpers FAQ existentes em `modules/custom/custom_configs/custom_configs.install`: `_custom_configs_ensure_faq_structure()`, `_custom_configs_ensure_faq_item_p()`, `_custom_configs_seed_faq_collection_example()` — e ausência de `_custom_configs_link_vaga_engenheiro_civil_faq()`
- [X] T005 Confirmar em `config/sync` a estrutura FAQ já exportada: CT `faq`, paragraph `faq_item_p`, `field_faq_itens`, `field_vaga_faq` (card. 1) e **ausência** de View `faq_vaga` / `block_faq*`
- [X] T006 Confirmar UUIDs canônicos: coleção FAQ `c3d4e5f6-a7b8-4901-b234-56789abcdef0` e vaga Engenheiro Civil `a7b8c9d0-e1f2-4345-f678-9abcdef01234`; e que `_custom_configs_seed_vaga_engenheiro_civil()` **não** preenche `field_vaga_faq` em `modules/custom/custom_configs/custom_configs.install`
- [X] T007 Confirmar em `themes/custom/default/templates/content/node--vagas--full.html.twig` o bloco FAQ inline (~175–213): após include “Sobre a Empresa”, filtro atual `pergunta or resposta`, presença de `data-bs-parent`, e que `themes/custom/default/templates/content/includes/vaga-faq-accordion.html.twig` **ainda não existe**
- [X] T008 Confirmar em `themes/custom/default/assets/css/components/vagas-detalhe.css` o bloco “FAQ accordion” (~315–334): override parcial (navy + `#f7fafc` no ativo) e itens ainda visualmente agrupados — alvo da reescrita para cards isolados

## Phase 3: User Story 4 — Deploy automatizado vincula seed e preserva editorial (P1)

**Goal**: Após a receita de deploy, a estrutura FAQ está garantida (ensure no-op se presente), a coleção de exemplo existe, a vaga Engenheiro Civil referencia essa coleção quando `field_vaga_faq` estava vazio, e reexecução é idempotente — zero passos manuais no admin.

**Independent Test**: Em origem, `drush updb -y` → mensagem do `11059`; abrir Engenheiro Civil e ver `field_vaga_faq` apontando para a coleção seed (se estava vazio); reexecutar vínculo com FAQ editorial preenchido → não sobrescreve; segunda passagem de `updb` → “No pending updates.”; sem View nova em `config/sync`.

- [X] T009 [US4] Implementar `_custom_configs_link_vaga_engenheiro_civil_faq()` em `modules/custom/custom_configs/custom_configs.install`: carregar vaga UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`; se ausente, reportar e não criar vaga; se sem `field_vaga_faq` ou campo já preenchido → no-op (preservar editorial); se vazio e coleção UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` existir → gravar a referência; retornar `t()` criado vs. preservado vs. ausente
- [X] T010 [US4] Implementar `custom_configs_update_11059()` em `modules/custom/custom_configs/custom_configs.install` chamando na ordem: ensures FAQ existentes (`_custom_configs_ensure_faq_structure` / `_custom_configs_ensure_faq_item_p` conforme padrão 031) → `_custom_configs_seed_faq_collection_example()` → `_custom_configs_link_vaga_engenheiro_civil_faq()`; concatenar mensagens `t()` (padrão dos hooks 11057/11058); **não** reabrir updates antigos; **não** criar View
- [X] T011 [US4] Garantir idempotência do `11059` em `modules/custom/custom_configs/custom_configs.install`: ensures presentes → no-op; coleção seed presente → não duplicar node/itens; vínculo editorial preenchido → não sobrescrever; segunda passagem de `updb` → “No pending updates.”
- [X] T012 [US4] Executar na origem `docker compose exec drupal drush updb -y` e validar a mensagem do `11059` (ensures, coleção seed, vínculo criado ou preservado)
- [X] T013 [US4] Executar na origem `docker compose exec drupal drush cex -y` — esperado no-op estrutural; versionar só se houver diff real; **não** editar YAML à mão; **não** inventar View `faq_vaga`
- [X] T014 [US4] Executar na origem `docker compose exec drupal drush cr`
- [X] T015 [P] [US4] Revisar `specs/038-vaga-faq-accordion/contracts/deploy-faq-accordion.md` e alinhar com a receita destino `cim` → `updb` → `cim` → `cr` e pré-requisito de `settings.php` / `config_sync_directory`

## Phase 4: User Story 1 — Visitante tira dúvidas sem sair da vaga (P1)

**Goal**: No detalhe público da vaga com FAQ válido, a coluna principal mostra “Dúvidas Frequentes” em Accordion de cards isolados (após “Sobre a Empresa”, antes do CTA), com painéis independentes e IDs únicos.

**Independent Test**: Abrir a vaga seed Engenheiro Civil; confirmar seção após “Sobre a Empresa” / antes do CTA; expandir/colapsar os dois itens de exemplo; desktop e mobile.

- [X] T016 [US1] Criar `themes/custom/default/templates/content/includes/vaga-faq-accordion.html.twig` conforme [contracts/vaga-faq-accordion-render.md](contracts/vaga-faq-accordion-render.md): `<section class="vaga-detalhe__faq">`; `h2.vaga-detalhe__section-title` com `{{ 'Dúvidas Frequentes'|t }}`; `.accordion` com `id="vaga-faq-{{ node.id }}"` (sem `accordion-flush`); por item válido `.accordion-item` + `h3.accordion-header` + botão **sem** `data-bs-parent`; IDs `vaga-faq-{nid}-{pid|delta}`; corpo com resposta no formato HTML permitido
- [X] T017 [US1] Em `themes/custom/default/templates/content/node--vagas--full.html.twig`, substituir o Accordion inline (~175–213) por montagem de `faq_paras` filtrados (pergunta **e** resposta não vazias após trim) + `{% include '@default/content/includes/vaga-faq-accordion.html.twig' with { node: node, faq_paras: faq_paras } only %}` **depois** do include “Sobre a Empresa” e **antes** do fechamento da `.col-lg-8`; omitir include se lista filtrada vazia
- [X] T018 [US1] Reescrever o bloco FAQ accordion em `themes/custom/default/assets/css/components/vagas-detalhe.css` (~315–334) sob `.vaga-detalhe__faq`: `.accordion-item` fundo `#fff`, borda `1px solid` fina (`var(--vd-border)`), `border-radius: 10px`, `margin-bottom: 1rem`; botão sempre branco (incluindo `:not(.collapsed)`), texto navy `font-weight: 600`, **sem** fundo `#f7fafc`/azul BS5 ativo; seta escura (override `--bs-accordion-btn-icon` / filtro); título via `.vaga-detalhe__section-title` já navy
- [X] T019 [US1] Validar desktop (≥992px): Engenheiro Civil — “Dúvidas Frequentes” com as perguntas “Qual a duração do estágio?” e “Existe auxílio home office?”, cards separados, seta escura à direita, acima do CTA “Pronto para o próximo passo?”
- [X] T020 [US1] Validar mobile (≤991px): cartões na largura da coluna; toque confortável; pergunta longa quebra linha sem empurrar a seta
- [X] T021 [US1] Validar painéis independentes: abrir o segundo item sem forçar o fechamento do primeiro; IDs de collapse únicos; sem `data-bs-parent` no markup
- [X] T022 [P] [US1] Validar edge cases de apresentação: empresa/cartão 037 ausente → FAQ após última seção de conteúdo da coluna; muitos itens (≥8) utilizáveis sem overflow horizontal

## Phase 5: User Story 2 — Visitante não vê FAQ vazio ou órfão (P1)

**Goal**: Vaga sem FAQ válido (campo vazio, coleção unpublished ou zero itens com pergunta **e** resposta) não renderiza a seção — zero markup órfão e sem espaço quebrado.

**Independent Test**: Abrir (ou simular) vaga sem `field_vaga_faq` e confirmar ausência de `.vaga-detalhe__faq`; com FAQ válido, confirmar presença.

> **Nota**: o filtro pergunta **e** resposta e a omissão do include são introduzidos em T017 (US1). Esta fase valida FR-006 / SC-002.

- [X] T023 [US2] Validar vaga sem `field_vaga_faq`: detalhe full **não** contém `.vaga-detalhe__faq` / include do Accordion
- [X] T024 [US2] Validar coleção unpublished ou `field_faq_itens` vazio: seção omitida para visitante anônimo
- [X] T025 [US2] Validar itens só com pergunta ou só com resposta (após trim vazio no outro lado): item omitido; se zero válidos restantes, seção omitida — confirmar em `themes/custom/default/templates/content/node--vagas--full.html.twig` e/ou `themes/custom/default/templates/content/includes/vaga-faq-accordion.html.twig` que o critério é **AND**, não `or`

## Phase 6: User Story 3 — Editor gerencia perguntas da vaga (P1)

**Goal**: Editor cria/edita coleção FAQ e associa via “FAQ da vaga”; o Accordion público reflete a alteração; itens incompletos não quebram a seção.

**Independent Test**: Editar a coleção FAQ associada à Engenheiro Civil (ou criar nova), salvar e recarregar o detalhe em &lt; 2 min no fluxo CMS.

> **Nota**: CT `faq`, paragraphs e `field_vaga_faq` já existem (030/031). Esta fase valida operabilidade + FR-003/SC-005; não recria storage.

- [X] T026 [US3] Validar no admin o formulário do CT `faq`: adicionar/remover itens `faq_item_p` com `field_pergunta` + `field_resposta` (campos required no CMS)
- [X] T027 [US3] Validar no formulário da vaga o campo “FAQ da vaga” (`field_vaga_faq`, card. 1): associar coleção, publicar e confirmar Accordion na página pública listando os itens
- [X] T028 [US3] Após US1: se o CMS permitir salvar item incompleto (legado), confirmar omissão no Accordion sem quebrar a seção; caso a validação do form impeça salvar, documentar no resultado do quickstart

## Phase 7: Polish & Cross-Cutting Concerns

- [X] T029 Atualizar cirurgicamente `PRD.md` no parágrafo do detalhe full (§3.1 área vagas): FAQ em cards Accordion isolados + vínculo seed Engenheiro Civil / hook `11059`; **sem** inventar View `faq_vaga`
- [X] T030 Executar validação completa do [quickstart.md](quickstart.md) (8 passos de conferência) e registrar resultado
- [X] T031 Verificar idempotência ponta a ponta: com vínculo FAQ editorial ≠ seed na Engenheiro Civil, reexecutar helper/`updb` conforme contrato → vínculo preservado; `drush cex -y` sem diff inesperado / sem View nova
- [X] T032 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT): Tasks de `038-vaga-faq-accordion` aponta para `specs/038-vaga-faq-accordion/tasks.md` (não mais “gerar com `/speckit-tasks`”)
- [ ] T033 Commitar em pt-BR com Conventional Commits (somente quando o usuário pedir), sugerido: `feat: alinha FAQ accordion do detalhe da vaga ao layout` englobando `modules/custom/custom_configs/custom_configs.install` + include Twig + `node--vagas--full.html.twig` + `vagas-detalhe.css` + `PRD.md`

## Dependencies

```text
Phase 1 (Setup)
    │
    ▼
Phase 2 (Foundational checks)
    │
    ▼
Phase 3 (US4: hook 11059 + cex) ──┬─► Phase 4 (US1: Twig include + CSS + validação visual)
                                  │         │
                                  │         ├─► Phase 5 (US2: validação omissão / sem órfão)
                                  │         │
                                  │         └─► Phase 6 (US3: validação CMS; T028 após US1)
                                  │
                                  └──────────────────────────────────────┐
                                                                         ▼
                                                          Phase 7 (Polish: PRD + quickstart + specify-rules)
```

- **US4 → US1**: Engenheiro Civil só mostra os 2 itens de exemplo após o vínculo seed.
- **US1 → US2**: filtro AND + omissão do include são entregues em US1; US2 valida ausência.
- **US1 → T028 (US3)**: omissão de item inválido só é verificável com o Twig do Accordion.
- **US3 ⊥ US2** (após US4/US1): validação CMS vs. validação de ausência são independentes em arquivo.
- **Sem View** em nenhuma fase (FR-009 / R1).

## Parallel Execution Examples

### Dentro de Phase 1 (Setup)

T001 e T002 são paralelizáveis (feature.json/artefatos vs. branch/decisão R1).

### Dentro de Phase 2 (Foundational)

T003–T008 são checks de leitura em arquivos distintos — paralelizáveis (exceto se um único agente for mais rápido em sequência).

### Dentro de Phase 3 (US4)

T009 → T010 → T011 são sequenciais (mesmo `custom_configs.install`).  
T012 → T013 → T014 são sequenciais (`updb` → `cex` → `cr`).  
T015 é paralelizável após T011 (revisão de contrato).

### Dentro de Phase 4 (US1)

T016 (include) e T018 (CSS) são paralelizáveis entre si; T017 depende de T016 existir.  
T019–T022 são validações após T017+T018+`drush cr`.

### Entre US2 e US3 (após US1)

T023–T025 (omissão) podem rodar em paralelo a T026–T027 (CMS), desde que US4 e US1 estejam feitos; T028 espera US1.

## Implementation Strategy (MVP)

1. Entregar **US4** (hook + vínculo) — Engenheiro Civil ligada à coleção seed no destino.
2. Entregar **US1** (Twig + CSS) — Accordion em cards isolados visível ao visitante.
3. Fechar **US2** + **US3** (validações) e Polish.

MVP sugerido para demo: **US4 + US1** (FAQ completo na Engenheiro Civil após `updb`/`cr`).

## Done When

- [X] `tasks.md` gerado com todas as phases, task IDs e file paths
- [X] Extension hooks despachados ou ignorados conforme regras (não há `.specify/extensions.yml` — skipped)
- [X] Completion report entregue com contagem, breakdown por story, escopo MVP e validação de formato
