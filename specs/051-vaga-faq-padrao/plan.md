# Implementation Plan: FAQ Padrão Único em Todas as Vagas

**Branch**: `051-vaga-faq-padrao`  
**Date**: 2026-11-02  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessor**: `038-vaga-faq-accordion` (Accordion + CT `faq`); `036-vagas-beneficios-padrao` (precedente de conteúdo padrão + deploy idempotente)  
**Hook**: `custom_configs_update_11074`  
**Setup**: scaffolding Spec Kit (`.specify/scripts`) ausente neste repo — feature ativa via `.specify/feature.json` → `specs/051-vaga-faq-padrao`

## Summary

Trocar a fonte de dados da seção **Dúvidas Frequentes** no detalhe da vaga: de associação por vaga (`field_vaga_faq`) para **uma única coleção FAQ padrão** (seed UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0`). Três frentes:

1. **Render**: preprocess carrega o FAQ padrão; Twig full deixa de ler `field_vaga_faq`; Accordion 038 reutilizado.
2. **Editorial**: ocultar `field_vaga_faq` no form display de Vagas (`region: hidden`); storage permanece.
3. **Deploy**: hook `11074` idempotente (ensure structure + seed coleção + hide form) + `drush cex` na origem; destino `cim` → `updb` → `cim` → `cr`. PRD §3.1 cirúrgico na implementação.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / 8.4, Drupal 11, Twig 3 |
| Primary Dependencies | Node/Field/Paragraphs; `custom_configs`; tema `default`. **Sem** Composer novo |
| Storage | Reuso: CT `faq`, `faq_item_p`, `field_faq_itens`, `field_vaga_faq` (oculto no form; **não** fonte do público) |
| Testing | Manual via [quickstart.md](quickstart.md); sem PHPUnit dedicado |
| Target Platform | Form admin da vaga + detalhe público `/vaga/<alias>` |
| Performance | 1 load extra por detalhe (FAQ por UUID); cache tags do node FAQ |
| Constraints | Sem `core/`/`vendor/`; sem redesenhar Accordion; pt-BR; SDD; deploy automatizado |
| Scale/Scope | 1 hook `11074`, helper load FAQ, preprocess + Twig, form display, PRD |

**Estado atual verificado (2026-11-02):**

- Twig `node--vagas--full.html.twig` (~178–198): lê `node.field_vaga_faq` → filtra `field_faq_itens` → include Accordion.
- Include `vaga-faq-accordion.html.twig` e CSS 038 intactos.
- Form display: `field_vaga_faq` em `region: content` (autocomplete, weight 23), label “FAQ da vaga”.
- View display: `field_vaga_faq: true` (já hidden na renderização de campos).
- Seed coleção: UUID `c3d4e5f6-…def0`, helper `_custom_configs_seed_faq_collection_example()`; vínculo em massa histórico em `11060`.
- Preprocess full: só `show_empresa_card` + fallbacks de ícone benefícios — **sem** FAQ ainda.
- Último hook: **`11073`**. Próximo: **`11074`**.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só `custom_configs`, tema `default`, `config/sync` via cex |
| Reuso de storage | PASS | sem storage novo; reusa CT `faq` + paragraphs + seed UUID |
| Deploy automatizado | PASS | `11074` + `drush cex`; receita `cim`→`updb`→`cim`→`cr` |
| Seeds Entity API / idempotentes | PASS | reusa `_custom_configs_seed_faq_collection_example()` |
| Não sobrescrever editorial | PASS | seed fill-if-empty; não limpa `field_vaga_faq` legado |
| Clean URLs | PASS | sem rota nova |
| PRD guardião | A atualizar na implementação | §3.1 (FAQ associável → padrão único) |
| Reinício cache | A executar na implementação | `drush cr` |

**Post-design**: nenhuma violação injustificada. Trade-offs em [research.md](research.md) e Complexity Tracking.

## Architecture

```
Edição vaga (form default)
  └─ field_vaga_faq     ← HIDDEN (storage permanece; valores legados ignorados no público)

Detalhe público (/vaga/<alias>)
  preprocess full
    └─ custom_configs_load_faq_padrao()  → node faq UUID canônico
         └─ filtra itens válidos → faq_paras + cache tags
  Twig full
    └─ include vaga-faq-accordion (038) se faq_paras não vazio
```

## Design Decisions

1. FAQ padrão = seed UUID existente (R1); sem segunda coleção.
2. Preprocess + helper de módulo como fonte única de render (R2); Twig não lê `field_vaga_faq`.
3. Ocultação via form display exportado, não remoção de field (R3).
4. Hook único `11074` com ensures + seed + hide (R4).
5. Accordion/CSS 038 intocados (R6).
6. PRD cirúrgico na fase implement (R5).

## Project Structure

### Documentation (this feature)

```text
specs/051-vaga-faq-padrao/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/
│   ├── deploy-faq-padrao.md
│   └── vaga-faq-padrao-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_configs/
  custom_configs.module     # + custom_configs_load_faq_padrao() (+ UUID helper)
  custom_configs.install    # + custom_configs_update_11074 + ensure form hidden

themes/custom/default/
  default.theme             # preprocess full: faq_paras + cache tags
  templates/content/node--vagas--full.html.twig  # fonte = faq_paras do preprocess

config/sync/  (via drush cex)
  core.entity_form_display.node.vagas.default.yml  # field_vaga_faq → hidden

PRD.md   # §3.1 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: helper + preprocess + Twig + hook + cex + PRD + cr
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Manter storage `field_vaga_faq` sem uso público | Rollback seguro; evita quebra de deps; spec permite | Apagar field (risco sem ganho) |
| UUID hardcoded em helper (não state admin) | Convenção do projeto; FR-001 atendido | Config UI para “qual FAQ é padrão” |
| 1 load entity extra no detalhe | Necessário para fonte global; mitigado por cache tags | Continuar ER no node (viola FR-002) |
