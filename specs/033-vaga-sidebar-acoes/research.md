# Research: Sidebar — Ações e Resumo

**Feature**: `033-vaga-sidebar-acoes`  
**Date**: 2026-10-18

## R1 — Rota de candidatura

**Decision**: usar `custom_panel.vagas_candidatar`.

**Evidence**: `modules/custom/custom_panel/custom_panel.routing.yml` define `path: '/vaga/apply/{node}'`, controller `VagasAplicadasController::apply`, `methods: [POST]`, `_role: candidato`, parâmetro `node: \d+`. `custom_candidaturas.routing.yml` declara que as rotas de candidatura ficam no `custom_panel`. Não há outra rota de formulário HTML de candidatura.

**Alternatives considered**: criar página GET de formulário — fora do escopo e duplicaria o fluxo que já cria o node `candidatura`. Navegar com `<a>` em GET — a rota responde método não permitido.

**Binding**: Twig `path('custom_panel.vagas_candidatar', {'node': node.id})` no `href` e em `data-apply-url`. O JS existente (`.js-candidatar-vaga`) passa a `preventDefault` em `<a>` e envia POST com o token CSRF, lendo `data-apply-url`.

## R2 — Rota de favoritos

**Decision**: `custom_panel.vagas_salvas_toggle`, POST, corpo `nid=<id>`.

**Evidence**: path `/painel/estudante/vagas-salvas/toggle`, `VagasSalvasController::toggle`, role `candidato`. O JS em `script-painel.js` já faz esse POST. O markup ganha `data-toggle-url`, `data-node-id`, `data-nid` e `data-method="POST"` para o JS não depender de URL hardcoded.

## R3 — Campos do resumo

**Decision**:

| Linha | Fonte | Novo? |
|-------|--------|-------|
| Período | `field_vaga_periodo` string 255 | sim |
| Bolsa Auxílio | `field_text_simple` | não |
| Modelo | `field_regime_t` | não |
| Vagas | `field_vaga_quantidade` integer unsigned | sim |

**Alternatives considered**: reutilizar `field_horarios` para período — rejeitado: é lista de turno (Manhã/Tarde/Noite), não duração “6 meses +”. Reutilizar `field_text_simple` para período — rejeitado: já é a bolsa no mesmo bundle (uma instância por campo). Reutilizar `field_vaga_carga_horaria` — rejeitado: já é a pill “30h semanais”.

Displays: form `string_textfield` e `number`; view default oculta os dois (o Twig full lê a entity, mesmo padrão de `field_vaga_carga_horaria`).

## R4 — Seed portável

**Decision**: não criar nodes novos. Reutilizar UUIDs da 032 e o helper `_custom_configs_seed_vaga_engenheiro_civil()`. `custom_configs_update_11050` garante os campos, chama os seeds e depois `_custom_configs_align_vaga_resumo_seed()`.

Alinhamento de localização/bolsa só quando o trio ainda é exatamente o seed 032 (`São Paulo` + `SP` + `R$ 1.800,00`). Período e quantidade preenchem só se vazios. Reexecução = no-op.

Valores-alvo do layout: Curitiba, PR, Presencial, 30h semanais, R$ 1.500/mês, 6 meses +, quantidade 2.

`field_estados` é `list_string`. A sigla `PR` não existia (o valor `PA` estava rotulado “Paraná”). O hook garante `PR` = Paraná antes de gravar o seed; o header já imprime a sigla (`Curitiba, PR`). Nodes antigos com valor `PA` não são reescritos.

## R5 — Componentização Twig

**Decision**: extrair `vaga-sidebar-acoes.html.twig` e `vaga-sidebar-resumo.html.twig` em `templates/content/includes/`, incluídos com `only`. O template full deixa de concentrar o markup da sidebar. Compartilhar continua no include já existente `vaga-share-popover.html.twig`.

## R6 — CSS sem SCSS

**Decision**: estender `vagas-detalhe.css` (library `default/vagas_detalhe`). O tema custom não tem arquivos `.scss`. Classe nova `.vaga-detalhe__btn-candidatar` (80px, `#FD7B1A` via `--vd-orange`, negrito, `w-100`). `--vd-resumo-bg` passa a `#f4f6f9`. Botões outline reutilizam `.ui-btn--outline`. Utilitários Bootstrap no markup (`.d-flex.gap-3.mt-3`, `.justify-content-between`).

## R7 — Skills externas

**Decision**: não instalar skill de `mattpocock/skills` nesta feature. A busca `npx skills find "clean code twig" --owner mattpocock` devolveu `code-review` e `git-guardrails-claude-code`, sem orientação de Twig, Drupal ou tipagem PHP. Componentização Twig e assinaturas tipadas nos helpers novos cobrem o pedido de código limpo. `code-review` fica disponível se for pedida uma revisão depois.
