# Tasks: Bloco "Sobre a Empresa" no Detalhe da Vaga

**Feature**: `specs/037-vaga-sobre-empresa`  
**Branch**: `feature-vaga` (identidade da feature: `037-vaga-sobre-empresa`)  
**Plan**: [plan.md](plan.md) | **Spec**: [spec.md](spec.md) | **Deploy**: [contracts/deploy-sobre-empresa.md](contracts/deploy-sobre-empresa.md) | **Render**: [contracts/vaga-sobre-empresa-render.md](contracts/vaga-sobre-empresa-render.md)  
**Hook**: `custom_configs_update_11057` (último existente: `11056`)

## Implementation Strategy

MVP primeiro, entrega incremental por user story (todas P1):

1. **US4 (deploy)** — núcleo automatizado: helpers + `hook_update_11057` (instância `node.empresa.field_text_simple`, displays, seed EcoConstrutora) + `drush cex` na origem. Entrega valor de operação sem depender de Twig.
2. **US3 (editor)** — formulário/CMS: o campo e os displays vêm do hook; a fase valida edição ≤255, persistência e que o subtítulo vazio não quebra o cartão (após US1).
3. **US1 (cartão público)** — include Twig + chamada no detalhe full + CSS no lugar do bloco morto. Depende do campo/seed de US4 para o subtítulo Figma na vaga seed.
4. **US2 (sem órfão)** — gate `empresa_node` + `access('view')` no Twig pai; validação de ausência do markup. A lógica de omitir entra junto com US1; esta fase fecha os cenários de regressão.

Sem suite PHPUnit (padrão do projeto). Validação manual via [quickstart.md](quickstart.md).

## Phase 1: Setup

- [X] T001 Confirmar identidade da feature em `.specify/feature.json` (`feature_directory: specs/037-vaga-sobre-empresa`) e artefatos presentes em `specs/037-vaga-sobre-empresa/` (spec, plan, research, data-model, contracts, quickstart)
- [X] T002 [P] Confirmar branch de trabalho alinhada ao plan (`feature-vaga`) e que não há storage `field.storage.node.field_sobre_empresa_resumo` a criar — reuso de `field.storage.node.field_text_simple` conforme [research.md](research.md) R1

## Phase 2: Foundational

Pré-requisitos bloqueantes. Nenhuma user story começa antes destes checks.

- [X] T003 Confirmar em `modules/custom/custom_configs/custom_configs.install` que o último hook é `custom_configs_update_11056` e que o próximo número livre é `11057` (sem colisão)
- [X] T004 Confirmar em `config/sync/field.storage.node.field_text_simple.yml` que o storage existe (`type: string`, `max_length: 255`) e que **não** existe `config/sync/field.field.node.empresa.field_text_simple.yml`
- [X] T005 Confirmar em `config/sync/core.entity_form_display.node.empresa.default.yml` e `config/sync/core.entity_view_display.node.empresa.default.yml` os pesos atuais de `field_imagem`, `field_text_long_formatted`, `field_cidade`, `field_estados` (base para empurrar pesos na 1ª inserção do resumo)
- [X] T006 Confirmar em `themes/custom/default/templates/content/node--vagas--full.html.twig` a resolução de `empresa_node` (publicado via `field_vaga_empresa`), a ordem Benefícios → FAQ (linhas ~136–181) e o comentário “Sem bloco Sobre a Empresa”
- [X] T007 Confirmar em `themes/custom/default/assets/css/components/vagas-detalhe.css` o CSS morto do cartão (~241–269: `.vaga-detalhe__empresa-card*`, line-clamp, logo 56px) e que `.vaga-detalhe__empresa` já entra no grupo de `margin-bottom: 2rem` (~162)
- [X] T008 Confirmar UUID seed EcoConstrutora `f6a7b8c9-d0e1-4234-e567-89abcdef0123` e padrão de seed-só-se-vazio em `_custom_configs_align_empresa_pagina_seed()` / `_custom_configs_seed_empresa_ecoconstrutora()` em `modules/custom/custom_configs/custom_configs.install` (modelo para o helper de resumo)
- [X] T009 Confirmar que `themes/custom/default/templates/content/node--empresa--full.html.twig` é markup próprio (não imprime `{{ content.field_* }}`) — o resumo curto **não** deve ser adicionado a esse template

## Phase 3: User Story 4 — Deploy automatizado leva campo, displays e seed (P1)

**Goal**: Após a receita de deploy, o campo “Resumo curto” existe no CT `empresa`, está nos displays CMS, a EcoConstrutora recebe o texto do Figma se estava vazio, e reexecução é idempotente — zero passos manuais no admin.

**Independent Test**: Em origem, `drush updb -y` → mensagem do `11057`; abrir formulário da EcoConstrutora e ver “Resumo curto” com o texto Figma (se estava vazio); `drush cex -y` gera os 3 YAML; reexecutar `updb` → no-op / sem sobrescrever editorial; não existe `field.storage.node.field_sobre_empresa_resumo`.

- [X] T010 [US4] Implementar `_custom_configs_ensure_empresa_resumo_field()` em `modules/custom/custom_configs/custom_configs.install`: `FieldConfig::loadByName('node', 'empresa', 'field_text_simple')`; se ausente, `FieldConfig::create` no storage `field.storage.node.field_text_simple` com rótulo “Resumo curto”, `required: false`, `translatable: false`, descrição clara de que é subtítulo do cartão (não a descrição longa); retornar `t()` criado vs. já existente
- [X] T011 [US4] Implementar `_custom_configs_ensure_empresa_resumo_displays()` em `modules/custom/custom_configs/custom_configs.install`: no form display `node.empresa.default`, inserir `field_text_simple` com widget `string_textfield` weight `2` **só na 1ª inserção**; se `field_text_long_formatted` / `field_cidade` / `field_estados` ainda estiverem em 2/3/4, subir para 3/4/5; no view display default, formatter `string`, label above, weight após o logo com a mesma regra de empurrar; reexecução não regrava pesos se o componente já existe
- [X] T012 [US4] Implementar `_custom_configs_seed_empresa_resumo()` em `modules/custom/custom_configs/custom_configs.install`: carregar node UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`; se ausente, reportar e não criar empresa; se `hasField('field_text_simple')` e `isEmpty()`, gravar exatamente `Líder regional em construções sustentáveis e infraestrutura urbana de alto impacto.`; se não vazio, preservar; não varrer outras empresas
- [X] T013 [US4] Implementar `custom_configs_update_11057()` em `modules/custom/custom_configs/custom_configs.install` chamando na ordem: ensure field → ensure displays → seed resumo; concatenar mensagens `t()` (padrão dos hooks 11052/11056); **não** reabrir `custom_configs_update_11052`
- [X] T014 [US4] Garantir idempotência do `11057` em `modules/custom/custom_configs/custom_configs.install`: instância existente → no-op; componente de display presente → não reordenar; resumo editorial divergente → não sobrescrever; segunda passagem de `updb` → “No pending updates.”
- [X] T015 [US4] Executar na origem `docker compose exec drupal drush updb -y` e validar a mensagem do `11057` (instância criada/já existente, displays, seed preenchido ou preservado)
- [X] T016 [US4] Executar na origem `docker compose exec drupal drush cex -y` e confirmar export de `config/sync/field.field.node.empresa.field_text_simple.yml`, `config/sync/core.entity_form_display.node.empresa.default.yml`, `config/sync/core.entity_view_display.node.empresa.default.yml` (sem editar YAML à mão)
- [X] T017 [US4] Executar na origem `docker compose exec drupal drush cr`
- [X] T018 [US4] Auditar `config/sync/field.field.node.empresa.field_text_simple.yml` pós-`cex`: `required: false`, dependência de `field.storage.node.field_text_simple`, rótulo “Resumo curto”; confirmar ausência de `field.storage.node.field_sobre_empresa_resumo`
- [X] T019 [P] [US4] Revisar `specs/037-vaga-sobre-empresa/contracts/deploy-sobre-empresa.md` e alinhar com a receita destino `cim` → `updb` → `cim` → `cr` e pré-requisito de `settings.php` / `config_sync_directory`

## Phase 4: User Story 3 — Editor gerencia o resumo curto da empresa (P1)

**Goal**: Editor encontra “Resumo curto” no formulário da Empresa, salva até 255 caracteres e o subtítulo do cartão no detalhe da vaga reflete o valor; com resumo vazio o cartão permanece sem a linha de subtítulo.

**Independent Test**: Editar EcoConstrutora (ou empresa de teste), alterar o resumo, salvar e recarregar o detalhe de uma vaga vinculada; esvaziar o resumo e confirmar omissão só do subtítulo (após US1).

> **Nota**: a criação do campo e dos displays é entregue pelo hook `11057` (US4). Esta fase valida o fluxo CMS e a regra FR-005 no front (depende do Twig de US1 para o cenário de subtítulo omitido).

- [X] T020 [US3] Validar no admin o formulário `node.empresa.default`: campo “Resumo curto” visível (weight ~2, entre logo e descrição longa na 1ª inserção), aceita ≤255 caracteres e recusa o 256º via widget `string_textfield`
- [X] T021 [US3] Validar persistência: salvar um resumo distinto do Figma na EcoConstrutora e confirmar valor gravado em `field_text_simple` do bundle `empresa` (sem afetar `node.vagas.field_text_simple` / salário)
- [X] T022 [US3] Validar que o view display default da Empresa lista `field_text_simple` (formatter `string`) e que `themes/custom/default/templates/content/node--empresa--full.html.twig` **não** passa a imprimir o resumo (página 034 intacta)
- [X] T023 [US3] Após US1: esvaziar o resumo, salvar, recarregar a vaga vinculada e confirmar cartão presente **sem** linha de subtítulo e sem placeholder genérico (FR-005)

## Phase 5: User Story 1 — Visitante conhece a empresa sem sair da vaga (P1)

**Goal**: No detalhe público da vaga com empresa publicada, a coluna principal mostra o cartão (logo + “Sobre a {nome}” + subtítulo + “Ver Empresa”) acima do FAQ, alinhado ao Figma, com navegação para a página canônica da empresa.

**Independent Test**: Abrir a vaga seed Engenheiro Civil (UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`); confirmar cartão acima de “Dúvidas Frequentes”, textos do Figma, logo 80×80, clique em “Ver Empresa” → `/empresa/ecoconstrutora` (ou alias vigente); desktop linha / mobile empilhado.

- [X] T024 [US1] Criar `themes/custom/default/templates/content/includes/vaga-sobre-empresa.html.twig` conforme [contracts/vaga-sobre-empresa-render.md](contracts/vaga-sobre-empresa-render.md): `<section class="vaga-detalhe__empresa">` → `.vaga-detalhe__empresa-card`; logo só se `field_imagem` tiver arquivo (80×80, `alt` = nome, `loading="lazy"`); título `h2` via `'Sobre a @nome'|t`; subtítulo só se `field_text_simple` trim não vazio; CTA `<a class="vaga-detalhe__empresa-cta">` para `url('entity.node.canonical', { node: empresa_node.id })` com texto `{{ 'Ver Empresa'|t }}`; **não** ler `field_empresa_u`
- [X] T025 [US1] Em `themes/custom/default/templates/content/node--vagas--full.html.twig`, incluir o cartão **depois** de Benefícios e **antes** do FAQ: `{% if show_empresa_card and empresa_node %}{% include ... %}` (flag via `default_preprocess_node__vagas__full` — sandbox Twig bloqueia `Node::access()`); atualizar o comentário do arquivo (remover “Sem bloco Sobre a Empresa”)
- [X] T026 [US1] Substituir o CSS morto em `themes/custom/default/assets/css/components/vagas-detalhe.css` (bloco ~241–269): fundo `#EFF4FF`, borda `#D5E3F7`, radius 12px, padding ~1.25rem 1.5rem; título `var(--vd-navy)`; subtítulo `#45464D`; logo 80×80 `object-fit: cover` + radius 12px; flex linha no desktop (logo | textos `min-width:0` + `overflow-wrap:break-word` | botão `white-space:nowrap`); coluna centralizada abaixo de `991.98px`; **sem** `line-clamp` / ellipsis; botão `.vaga-detalhe__empresa-cta` outline navy radius 12px (hover preenche navy); **não** alterar `.vaga-detalhe__logo` (112px) nem `.vaga-detalhe__empresa-nome` do header
- [X] T027 [US1] Validar desktop (≥992px): cartão completo na vaga Engenheiro Civil — logo, “Sobre a EcoConstrutora”, texto Figma, “Ver Empresa” na mesma linha, acima do FAQ
- [X] T028 [US1] Validar mobile (≤991px): empilhamento logo → textos → botão, sem scroll horizontal; título longo e resumo de 255 caracteres quebram linha
- [X] T029 [US1] Validar CTA: clique em “Ver Empresa” leva à página pública canônica da EcoConstrutora; descrição longa da página 034 permanece (não é substituída pelo resumo)
- [X] T030 [P] [US1] Validar edge cases de apresentação: empresa sem logo → cartão sem coluna de imagem; FAQ ausente → cartão após Benefícios / fim da coluna principal; sem truncamento visual forçado no subtítulo

## Phase 6: User Story 2 — Visitante não vê bloco órfão sem empresa (P1)

**Goal**: Vaga sem empresa válida/publicada/acessível não renderiza o cartão — zero markup órfão e sem espaço vazio quebrado.

**Independent Test**: Abrir (ou simular) vaga sem `field_vaga_empresa` e confirmar ausência de `.vaga-detalhe__empresa`; com empresa publicada e acessível, confirmar presença.

> **Nota**: o gate `{% if empresa_node and empresa_node.access('view') %}` é introduzido em T025 (US1). Esta fase valida FR-002 / SC-002.

- [X] T031 [US2] Validar vaga sem `field_vaga_empresa`: detalhe full **não** contém `.vaga-detalhe__empresa` / include do cartão
- [X] T032 [US2] Validar vaga cuja referência aponta para empresa não publicada: `empresa_node` permanece nulo (mesma regra do header) e o cartão não aparece
- [X] T033 [US2] Confirmar em `themes/custom/default/templates/content/includes/vaga-sobre-empresa.html.twig` e na chamada em `node--vagas--full.html.twig` que **não** há fallback para `field_empresa_u` (FR-014)

## Phase 7: Polish & Cross-Cutting Concerns

- [X] T034 Atualizar cirurgicamente `PRD.md`: linha do CT `empresa` em §3.1 (campo “Resumo curto” / `field_text_simple`) e o parágrafo do detalhe full que hoje diz que não há bloco “Sobre a Empresa”
- [X] T035 Executar validação completa do [quickstart.md](quickstart.md) (8 passos de conferência) e registrar resultado
- [X] T036 Verificar idempotência ponta a ponta: após editar o resumo da EcoConstrutora para texto ≠ Figma, reexecutar fluxo de seed/`updb` conforme contrato → texto editorial preservado; `drush cex -y` sem diff inesperado nos 3 YAML
- [X] T037 [P] Atualizar `.cursor/rules/specify-rules.mdc` (bloco SPECKIT): Tasks de `037-vaga-sobre-empresa` aponta para `specs/037-vaga-sobre-empresa/tasks.md` (não mais “pendente”)
- [ ] T038 Commitar em pt-BR com Conventional Commits (somente quando o usuário pedir), sugerido: `feat: adiciona cartão Sobre a Empresa no detalhe da vaga` englobando `modules/custom/custom_configs/custom_configs.install` + include Twig + `node--vagas--full.html.twig` + `vagas-detalhe.css` + 3 YAML de `config/sync` + `PRD.md`

## Dependencies

```text
Phase 1 (Setup)
    │
    ▼
Phase 2 (Foundational checks)
    │
    ▼
Phase 3 (US4: hook 11057 + cex) ──┬─► Phase 4 (US3: validação CMS; T023 após US1)
                                  │
                                  └─► Phase 5 (US1: Twig + CSS + validação visual)
                                            │
                                            ▼
                                      Phase 6 (US2: validação gate / sem órfão)
                                            │
                                            ▼
                                      Phase 7 (Polish: PRD + quickstart + specify-rules)
```

- **US4 → US3**: campo e displays existem só após o hook/`cim`.
- **US4 → US1**: subtítulo Figma na vaga seed depende do seed; o markup do cartão lê `field_text_simple`.
- **US1 → US2**: gate e include são entregues em US1; US2 valida omissão.
- **US1 → T023 (US3)**: omissão do subtítulo vazio só é verificável com o Twig do cartão.
- **US3 ⊥ US2** (após US4/US1): validação CMS vs. validação de ausência são independentes em arquivo.

## Parallel Execution Examples

### Dentro de Phase 1 (Setup)

T001 e T002 são paralelizáveis (feature.json/artefatos vs. branch/regra de storage).

### Dentro de Phase 2 (Foundational)

T003–T009 são checks de leitura em arquivos distintos — paralelizáveis (exceto se um único agente for mais rápido em sequência).

### Dentro de Phase 3 (US4)

T010 → T011 → T012 → T013 → T014 são sequenciais (mesmo `custom_configs.install`).  
T015 → T016 → T017 são sequenciais (`updb` → `cex` → `cr`).  
T018 e T019 são paralelizáveis após T016.

### Dentro de Phase 5 (US1)

T024 (include) e T026 (CSS) são paralelizáveis entre si; T025 depende de T024 existir.  
T027–T030 são validações sequenciais após T025+T026+`drush cr`.

### Entre US3 e US2 (após US1)

T020–T022 (CMS) podem rodar em paralelo a T031–T033 (gate), desde que US4 e US1 estejam feitos; T023 espera US1.

## Implementation Strategy (MVP)

1. Entregar **US4** (hook + `cex`) — campo, displays e seed prontos para destino.
2. Entregar **US1** (Twig + CSS) — valor visível ao visitante na vaga seed.
3. Fechar **US2** + restante de **US3** (validações) e Polish.

MVP sugerido para demo: **US4 + US1** (cartão completo na Engenheiro Civil após `updb`/`cr`).

## Done When

- [X] `tasks.md` gerado com todas as phases, task IDs e file paths
- [X] Extension hooks despachados ou ignorados conforme regras (não há `.specify/extensions.yml` — skipped)
- [X] Completion report entregue com contagem, breakdown por story, escopo MVP e validação de formato
