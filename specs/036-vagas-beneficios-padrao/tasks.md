# Tasks: Benefícios Padrão em Todas as Vagas

**Feature**: `specs/036-vagas-beneficios-padrao`
**Branch**: `036-vagas-beneficios-padrao`
**Plan**: [plan.md](plan.md) | **Spec**: [spec.md](spec.md) | **Deploy**: [contracts/deploy-beneficios-padrao.md](contracts/deploy-beneficios-padrao.md)
**Hook**: `custom_configs_update_11056` (último livre: `11055`)

## Implementation Strategy

MVP primeiro, entrega incremental em 3 frentes independentes por user story:

1. **US3 (deploy em massa)** — núcleo automatizado: hook `11056` (backfill idempotente + flip `required` via Entity API) + assets versionados + `drush cex` na origem. É pré-requisito de US1 (backfill) e US2 (flip). Entrega valor de operação sem depender de UI.
2. **US1 (apresentação pública)** — ajuste cirúrgico em CSS existente (`vagas-detalhe.css`) + bump de `width/height` nos dois templates Twig. Independente do hook em termos de arquivo, mas o teste de ponta a ponta depende do backfill de US3.
3. **US2 (editor bloqueado)** — entregue pelo flip `required: TRUE` do hook de US3; as tarefas de US2 são de verificação/validação no admin e de garantia de que o YAML exportado reflete a obrigatoriedade.

Reuso total do modelo de dados (features 030/031): `paragraph beneficio_vaga_p` + `field_vaga_beneficios`. Nenhum storage novo.

## Phase 1: Setup

- [X] T001 Criar pasta `modules/custom/custom_configs/assets/beneficios/` para versionar os 4 ícones padrão do Figma
- [X] T002 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-01.png` (ícone mala de couro — Plano de Carreira)
- [X] T003 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-02.png` (ícone maleta azul — Vale Transporte)
- [X] T004 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-03.png` (ícone talheres — Vale Refeição)
- [X] T005 [P] Adicionar `modules/custom/custom_configs/assets/beneficios/beneficios-04.png` (ícone relógio — Horário Flexível)

## Phase 2: Foundational

Tarefas pré-requisito bloqueantes para todas as user stories. Reuso de helper existente (`_custom_configs_seed_file_from_module_asset` em `custom_configs.install` linhas 2463–2505) — nenhuma nova função utilitária global é necessária; o helper de backfill específico da feature fica encapsulado no próprio hook (ver T012).

- [X] T006 Confirmar em `modules/custom/custom_configs/custom_configs.install` que o último hook é `custom_configs_update_11055` e que o próximo número livre é `11056` (sem colisão)
- [X] T007 Confirmar em `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` que `required: false` e `handler.default:paragraph` com `target_bundles.beneficio_vaga_p: true` (estado atual esperado)
- [X] T008 Confirmar em `config/sync/paragraphs.paragraphs_type.beneficio_vaga_p.yml` e em `field.field.paragraph.beneficio_vaga_p.field_text_simple.yml` que o título é `required: true` (pré-condição do seed)
- [X] T009 Confirmar em `themes/custom/default/templates/content/node--vagas--full.html.twig` (linhas 134–165) que a seção `.vaga-detalhe__beneficios` já renderiza condicionalmente com `col-6 col-lg-3` e fallback `fa-gift` (base do ajuste de US1)
- [X] T010 Confirmar em `themes/custom/default/templates/paragraph/paragraph--beneficio-vaga-p.html.twig` que o markup espelha o template full da vaga (mesmo seletor `.vaga-detalhe__beneficio-card`)
- [X] T011 Confirmar em `themes/custom/default/assets/css/components/vagas-detalhe.css` (linhas 202–238) o estado atual dos seletores `.vaga-detalhe__beneficio-card / -icon / -icon img / -title` (base do bump de US1)

## Phase 3: User Story 3 — Operação aplica o padrão em massa no deploy (P1)

**Goal**: Em ambiente com vagas sem benefícios e assets dos quatro ícones presentes, executar a receita de deploy e, sem passos manuais no admin, todas as vagas vazias passam a ter o padrão, os ícones ficam disponíveis como arquivos gerenciados e a obrigatoriedade do campo entra em vigor — de forma idempotente.

**Independent Test**: Em ambiente de origem com pelo menos uma vaga sem benefícios e os 4 PNGs em `modules/custom/custom_configs/assets/beneficios/`, rodar `drush updb -y` e auditar: (a) contagem de vagas atualizadas na mensagem do hook; (b) abrir uma vaga antes vazia e ver 4 paragraphs `beneficio_vaga_p` anexados na ordem Figma; (c) `drush cex -y` e inspecionar `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` com `required: true`; (d) reexecutar `updb -y` e confirmar 0 atualizadas / 0 duplicações.

- [X] T012 [US3] Implementar `custom_configs_update_11056()` em `modules/custom/custom_configs/custom_configs.install` com a sequência: (a) checar legibilidade dos 4 assets `beneficios-01.png`…`beneficios-04.png` — se algum faltar, abortar com `t('Backfill benefícios: asset ausente: @path', [...])` e **não** flipar `required`; (b) iterar via `entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'vagas'])` e, para cada vaga com `field_vaga_beneficios` vazio (`isEmpty()`), criar 4 paragraphs `beneficio_vaga_p` na ordem Figma (Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível), cada um com `field_image` (managed file via `_custom_configs_seed_file_from_module_asset('beneficios/beneficios-0N.png', 'public://beneficios/beneficios-0N.png', 'paragraph', $paragraph->id())` + `alt`) e `field_text_simple` (título exato), anexar ao `field_vaga_beneficios` e `save()`; (c) após backfill bem-sucedido, carregar `field_config` de `node.vagas.field_vaga_beneficios` via `Drupal::entityTypeManager()->getStorage('field_config')->load('node.vagas.field_vaga_beneficios')`, setar `set('required', TRUE)` e `save()` (no-op se já true); (d) retornar `t('Backfill benefícios: @updated vagas atualizadas, @skipped já preenchidas, @total total.', [...])`
- [X] T013 [US3] Garantir idempotência do hook `11056` em `modules/custom/custom_configs/custom_configs.install`: `isEmpty()` falso → não recriar paragraphs; `required: TRUE` já true → `set()` + `save()` no-op; managed files reusados por URI via helper existente (não recriar `file.usage`)
- [X] T014 [US3] Tratar Edge Case de asset ausente no hook `11056` em `modules/custom/custom_configs/custom_configs.install`: abortar antes de qualquer escrita em DB (nenhum paragraph criado, nenhum flip), retornar mensagem `t()` clara com o caminho do asset faltante, deixar `required: false` (estado consistente)
- [X] T015 [US3] Tratar Edge Case de volume alto no hook `11056` em `modules/custom/custom_configs/custom_configs.install`: usar `loadByProperties` + `foreach` com `save()` por nó (mesmo padrão de `11054/11055`); não carregar todos os paragraphs em memória; reportar contagem (atualizadas / já ok / total) na mensagem de retorno
- [X] T016 [US3] Tratar Edge Case de vaga em rascunho/não publicada no hook `11056` em `modules/custom/custom_configs/custom_configs.install`: o `loadByProperties(['type' => 'vagas'])` retorna todas as vagas do tipo independentemente de `status`; o backfill aplica a todas com `field_vaga_beneficios` vazio (alinhado ao spec)
- [X] T017 [US3] Executar `drush updb -y` na origem (container Drupal) para rodar `custom_configs_update_11056` e validar a mensagem de retorno (atualizadas / já ok / total)
- [X] T018 [US3] Executar `drush cex -y` na origem para consolidar `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` com `required: true` (flip via Entity API refletido no YAML versionado)
- [X] T019 [US3] Executar `drush cr` na origem para limpar cache de field definitions / Twig / registry
- [X] T020 [US3] Auditar `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` pós-`cex`: confirmar `required: true` e `handler.default:paragraph` + `target_bundles.beneficio_vaga_p: true` preservados
- [X] T021 [US3] Revisar `specs/036-vagas-beneficios-padrao/contracts/deploy-beneficios-padrao.md` e alinhar com a ordem `cim` → `updb` → `cim` → `cr` no destino e o pré-requisito de `settings.php` alinhado a `settings.php.prod`

## Phase 4: User Story 1 — Visitante vê Benefícios em toda vaga publicada (P1)

**Goal**: Visitante abre o detalhe de qualquer vaga publicada e encontra a seção "Benefícios" com cards (ícone + texto) alinhados ao layout Figma, em vez de a seção desaparecer por falta de dados.

**Independent Test**: Após deploy de US3, abrir 3 vagas antes sem benefícios e confirmar seção "Benefícios" com 4 cards (ícone + título) na ordem Figma (Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível); abrir 1 vaga com benefícios customizados e confirmar conteúdo editorial preservado; em mobile (≤991px) grade 2×2, em desktop (≥992px) 4 colunas, ícone ~48px sem distorção e título semibold em `--vd-navy`.

- [X] T022 [P] [US1] Ajustar `.vaga-detalhe__beneficio-card` em `themes/custom/default/assets/css/components/vagas-detalhe.css`: bump de padding de `1rem 0.75rem` para `1.25rem 1rem`; manter `background: #fff`, `border: 1px solid var(--vd-border)`, `border-radius: 12px`, flex column centered, `gap: 0.5rem`, `height: 100%`, `min-height: 7rem`
- [X] T023 [P] [US1] Ajustar `.vaga-detalhe__beneficio-icon` em `themes/custom/default/assets/css/components/vagas-detalhe.css`: bump de `width/height` de `2.5rem` para `3rem` (container do ícone)
- [X] T024 [P] [US1] Ajustar `.vaga-detalhe__beneficio-icon img` em `themes/custom/default/assets/css/components/vagas-detalhe.css`: bump de `max-width/max-height` de `32px` para `48px`; manter `object-fit: contain`
- [X] T025 [P] [US1] Ajustar `.vaga-detalhe__beneficio-title` em `themes/custom/default/assets/css/components/vagas-detalhe.css`: trocar `color: var(--vd-text)` por `color: var(--vd-navy)`; manter `font-size: 0.9rem`, `font-weight: 600`, `margin: 0`
- [X] T026 [P] [US1] Ajustar atributos `width="48" height="48"` no `<img>` da seção benefícios em `themes/custom/default/templates/content/node--vagas--full.html.twig` (linha ~152) — bump de `32` para `48` como hint de layout (evita reflow; CSS continua controlando o tamanho real)
- [X] T027 [P] [US1] Ajustar atributos `width="48" height="48"` no `<img>` do ícone em `themes/custom/default/templates/paragraph/paragraph--beneficio-vaga-p.html.twig` (linha ~23) — mesmo bump para manter os dois templates em paridade
- [X] T028 [US1] Validar renderização pública em desktop (≥992px): abrir 3 vagas antes sem benefícios e confirmar seção "Benefícios" com 4 cards em 4 colunas, ícone ~48px sem distorção, título semibold em `--vd-navy`, card branco com borda sutil e cantos arredondados
- [X] T029 [US1] Validar renderização pública em mobile (≤991px): confirmar grade 2×2, ícone centralizado sem distorção, texto legível
- [X] T030 [US1] Validar preservação editorial: abrir 1 vaga que já tinha benefícios customizados e confirmar que o conteúdo editorial permanece inalterado (sem substituição pelo padrão Figma)
- [X] T031 [US1] Validar edge cases de renderização no Twig `themes/custom/default/templates/content/node--vagas--full.html.twig`: card com ícone ausente mas título presente renderiza com fallback `fa-gift`; card sem ícone nem título é omitido do grid (`if ben_title or ben_icon`); seção inteira omitida se `has_beneficios` falso

## Phase 5: User Story 2 — Editor não publica vaga sem benefícios (P1)

**Goal**: Editor cria/edita vaga e o sistema exige ao menos um benefício antes de salvar/publicar, evitando novas vagas sem seção Benefícios.

**Independent Test**: No admin, editar uma vaga removendo todos os benefícios e tentar salvar → confirmar bloqueio de validação (campo obrigatório); preencher pelo menos um benefício válido e salvar → confirmar sucesso e seção Benefícios no detalhe público.

> **Nota de implementação**: a obrigatoriedade do campo é entregue pelo flip `required: TRUE` no hook `11056` (T012, US3) e consolidada em `config/sync` por `drush cex` (T018, US3). As tarefas de US2 abaixo são de verificação/validação no admin e de garantia de que o YAML exportado reflete a obrigatoriedade — não há nova escrita de código nesta fase.

- [X] T032 [US2] Validar bloqueio de salvamento no admin: editar uma vaga existente, remover todos os paragraphs de `field_vaga_beneficios` e tentar salvar → confirmar que o Drupal impede o salvamento com mensagem de validação (campo obrigatório)
- [X] T033 [US2] Validar salvamento com benefício válido: na mesma vaga, preencher pelo menos 1 paragraph `beneficio_vaga_p` (ícone + título) e salvar → confirmar persistência e seção "Benefícios" no detalhe público
- [X] T034 [US2] Validar que o formulário de vaga exibe `field_vaga_beneficios` com indicador de obrigatório (asterisco / mensagem de validação) após o flip `required: TRUE` do hook `11056`
- [X] T035 [US2] Confirmar em `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` que `required: true` está versionado (pós `drush cex` de T018) — garantia de que a obrigatoriedade entra em vigor no destino via `cim` mesmo se o hook falhar

## Phase 6: Polish & Cross-Cutting Concerns

- [X] T036 Atualizar cirurgicamente `PRD.md` na seção de campos da vaga (§3.1 ou equivalente) para registrar que `field_vaga_beneficios` passou de opcional para obrigatório, com referência à feature 036
- [X] T037 Executar validação completa do [quickstart.md](quickstart.md) (6 passos de conferência + Edge Case de asset ausente) e registrar resultado
- [X] T038 Verificar idempotência ponta a ponta: reexecutar `drush updb -y` em ambiente já atualizado → mensagem de no-op (zero vagas atualizadas, zero duplicações); `drush cex -y` → sem diff em `config/sync`
- [ ] T039 Commitar em pt-BR com Conventional Commits, em um único commit `feat: adiciona benefícios padrão em todas as vagas` englobando `modules/custom/custom_configs/custom_configs.install` + `modules/custom/custom_configs/assets/beneficios/beneficios-0N.png` + `themes/custom/default/assets/css/components/vagas-detalhe.css` + `themes/custom/default/templates/content/node--vagas--full.html.twig` + `themes/custom/default/templates/paragraph/paragraph--beneficio-vaga-p.html.twig` + `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` + `PRD.md`

## Dependencies

Ordem de conclusão das user stories (respeita pré-requisitos técnicos):

```text
Phase 1 (Setup: assets PNG) ──┐
                              ├─► Phase 3 (US3: hook 11056 + cex) ──┬─► Phase 4 (US1: CSS/Twig bump + validação visual)
Phase 2 (Foundational: checks) ┘                                    └─► Phase 5 (US2: validação admin do required)
                                                                              │
                                                                              ▼
                                                                   Phase 6 (Polish: PRD + quickstart + commit)
```

- **US3 → US1**: o teste de ponta a ponta de US1 (ver 4 cards em vagas antes vazias) depende do backfill de US3.
- **US3 → US2**: o bloqueio de validação no admin depende do flip `required: TRUE` de US3 (T012) e da consolidação em `config/sync` (T018).
- **US1 ⊥ US2**: independentes entre si em termos de arquivo (US1 toca CSS/Twig; US2 só valida admin), mas ambas dependem de US3.

## Parallel Execution Examples

### Dentro de Phase 1 (Setup)

T002, T003, T004, T005 são paralelizáveis — 4 arquivos PNG independentes em diretórios já criados por T001.

### Dentro de Phase 4 (US1)

T022, T023, T024, T025, T026, T027 são paralelizáveis — 6 arquivos/seletores independentes (4 seletores CSS no mesmo arquivo + 2 templates Twig). **Atenção**: T022–T025 tocam o mesmo arquivo `vagas-detalhe.css`; se executadas em paralelo por agentes distintos, coordenar para evitar conflito de merge. T026 e T027 tocam templates distintos e são totalmente independentes.

### Dentro de Phase 3 (US3)

T012 é sequencial (núcleo do hook). T013–T016 são refinamentos do mesmo hook (mesmo arquivo `custom_configs.install`) — execututar em sequência após T012. T017 → T018 → T019 são sequenciais (updb → cex → cr). T020 e T021 são paralelizáveis após T018.

## Done When

- [X] tasks.md gerado com todas as phases, task IDs e file paths
- [X] Extension hooks despachados ou ignorados conforme regras (não há `.specify/extensions.yml` — skipped)
- [X] Completion report entregue ao usuário com contagem de tasks, breakdown por user story, escopo MVP e validação de formato

