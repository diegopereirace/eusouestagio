# Implementation Plan: Sidebar do Detalhe da Vaga — Ações e Resumo

**Branch**: branch ativa do repositório (scaffolding Spec Kit — `.specify/scripts` — ausente; setup via `.specify/feature.json` → `specs/033-vaga-sidebar-acoes`)  
**Date**: 2026-10-18  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/033-vaga-sidebar-acoes/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)  
**Predecessor**: `032-vaga-header-empresa`

## Summary

Refatorar só a sidebar do detalhe full (`node--vagas--full.html.twig`): cartão de Ações (Candidatar-se → rota `custom_panel.vagas_candidatar`; Salvar → `custom_panel.vagas_salvas_toggle` via AJAX; Compartilhar ao lado) e cartão Resumo (período, bolsa, modelo, quantidade). Campos novos `field_vaga_periodo` (string) e `field_vaga_quantidade` (integer) no bundle `vagas`, displays, hook idempotente `custom_configs_update_11050` que enriquece os seeds EcoConstrutora / Engenheiro Civil, `drush cex`, PRD §3.1.1 cirúrgico. Match, Perfil, header e coluna principal permanecem.

## Technical Context

**Language/Version**: PHP 8.3+ (assinaturas tipadas nos helpers novos; o `.install` legado não recebe `declare(strict_types=1)` para não alterar o arquivo inteiro), Drupal 11, Twig 3, CSS3, Bootstrap 5 (Barrio)  
**Primary Dependencies**: Drupal core Field API; tema `default`; módulos `custom_configs` e `custom_panel`. Nenhuma dependência Composer nova.  
**Storage**: PostgreSQL; fields/displays em `config/sync`; seeds via Entity API no hook `11050`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem PHPUnit dedicado  
**Target Platform**: detalhe full da vaga (mobile e desktop)  
**Project Type**: tema + módulo custom  
**Performance Goals**: reutilizar library `vagas_detalhe` e `script-painel`; sem request extra no primeiro paint  
**Constraints**: sem `core/`/`vendor/`; sem rotas novas; não sobrescrever editorial divergente; deploy `cim` → `updb` → `cim` → `cr`; pt-BR  
**Scale/Scope**: 2 fields, 1 hook `11050`, 2 includes Twig, CSS do botão/resumo, ajuste pontual do JS de candidatura/favorito, PRD cirúrgico

**Estado atual verificado (2026-10-18):**

- Template full já tem sidebar (ações + resumo). Período lê `field_horarios`. Quantidade está fixa em “Não informado”.
- Candidatar-se é `<button class="js-candidatar-vaga">` e o JS faz POST em `/vaga/apply/{id}`.
- Rota exata: `custom_panel.vagas_candidatar` → `/vaga/apply/{node}` → `VagasAplicadasController::apply`, `methods: [POST]`, `_role: candidato`.
- Favorito: `custom_panel.vagas_salvas_toggle` → `/painel/estudante/vagas-salvas/toggle`, POST, corpo `nid`.
- `field_text_simple` (bolsa) e `field_regime_t` (modelo) existem. `field_vaga_periodo` e `field_vaga_quantidade` não existem.
- Seeds 032: empresa UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`, vaga UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234` (cidade São Paulo, bolsa “R$ 1.800,00” no helper atual).
- Último hook: `custom_configs_update_11049`. Esta feature: **`11050`**.
- Tema sem arquivos SCSS; estilo em `themes/custom/default/assets/css/components/vagas-detalhe.css` (`--vd-orange: #fd7b1a` já definido).

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc`, `drupal-deploy-configs.mdc`, `estagio-fluxo-dev.mdc`.

| Gate | Resultado |
|------|-----------|
| Spec antes do código | spec.md + checklist antes da implementação |
| Sem `core/` / `vendor/` | só tema, `custom_configs.install`, JS do tema, `config/sync` via cex, PRD |
| Reuso de campos | bolsa e modelo reutilizam storage existente; período e quantidade não têm equivalente no bundle |
| Deploy automatizado | `hook_update_11050` idempotente + `drush cex` |
| Não sobrescrever editorial | preenche vazio; só alinha o trio exato do seed 032 |
| URLs via rota nomeada | `path()` com os nomes encontrados |
| PRD cirúrgico | §3.1.1 apenas |

**Pós-design**: gates mantidos. O `<a href="{{ path('custom_panel.vagas_candidatar') }}">` não navega por GET: o JS faz `preventDefault` e POST, porque a rota declara `methods: [POST]`.

## Project Structure

### Documentation

```text
specs/033-vaga-sidebar-acoes/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── vaga-sidebar-render.md
│   └── deploy-vaga-sidebar.md
├── checklists/requirements.md
└── tasks.md
```

### Source Code

```text
modules/custom/custom_configs/custom_configs.install   # 11050 + ensures + align seed
themes/custom/default/templates/content/node--vagas--full.html.twig
themes/custom/default/templates/content/includes/vaga-sidebar-acoes.html.twig
themes/custom/default/templates/content/includes/vaga-sidebar-resumo.html.twig
themes/custom/default/assets/css/components/vagas-detalhe.css
themes/custom/default/assets/js/script-painel.js
config/sync/field.storage.node.field_vaga_periodo.yml          # via cex
config/sync/field.storage.node.field_vaga_quantidade.yml       # via cex
config/sync/field.field.node.vagas.field_vaga_*.yml            # via cex
config/sync/core.entity_form_display.node.vagas.default.yml    # via cex
config/sync/core.entity_view_display.node.vagas.default.yml    # via cex
PRD.md
```

**Structure Decision**: includes Twig novos só para os dois cartões; o template full inclui-os. Sem módulo novo e sem rota nova.

## Phase 0 — Research

Ver [research.md](research.md). Decisões fechadas: rota POST existente, campos novos, seed por UUID, CSS no arquivo já versionado (sem SCSS).

## Phase 1 — Design

- [data-model.md](data-model.md)
- [contracts/vaga-sidebar-render.md](contracts/vaga-sidebar-render.md)
- [contracts/deploy-vaga-sidebar.md](contracts/deploy-vaga-sidebar.md)
- [quickstart.md](quickstart.md)
- Contexto do agente: `.specify/feature.json` + bloco SPECKIT em `.cursor/rules/specify-rules.mdc` (script `.specify/extensions/agent-context` ausente neste repo).

## Complexity Tracking

Sem violações que exijam exceção. O `preventDefault` no link de candidatura existe para cumprir o contrato POST da rota já publicada, não para criar endpoint novo.
