# Implementation Plan: Bloco "Dúvidas Frequentes" (FAQ Accordion) no Detalhe da Vaga

**Branch**: `feature-vaga`  
**Feature Directory**: `specs/038-vaga-faq-accordion`  
**Date**: 2026-10-27  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessor**: `030-vagas-detalhe-layout` / `031-vagas-detalhe-refino` (CT `faq`, `faq_item_p`, `field_vaga_faq`, Accordion inline); `037-vaga-sobre-empresa` (cartão imediatamente acima do FAQ)  
**Hook**: `custom_configs_update_11059`

## Summary

Alinhar a seção **Dúvidas Frequentes** do detalhe da vaga ao layout de referência (cards Accordion isolados), componentizar o Twig e garantir o vínculo seed Engenheiro Civil → coleção FAQ de exemplo — **sem** View/bloco Views e **sem** recriar a estrutura de dados 030/031.

Três frentes:

1. **Tema**: extrair o Accordion para `includes/vaga-faq-accordion.html.twig`; filtrar itens com pergunta **e** resposta; painéis independentes (sem `data-bs-parent`); CSS de cards flutuantes sob `.vaga-detalhe__faq`.
2. **Seed/vínculo** (`hook_update_11059`): reutilizar ensures + `_custom_configs_seed_faq_collection_example()`; helper novo liga a vaga UUID `a7b8c9d0-…1234` à coleção `c3d4e5f6-…def0` **somente** se `field_vaga_faq` estiver vazio.
3. **Governança**: `drush cex` na origem (provável no-op); PRD cirúrgico no parágrafo do detalhe full.

## Technical Context

**Language/Version**: PHP 8.4, Drupal 11.4+, Twig 3, CSS3 (Bootstrap 5.3 via `bootstrap_barrio`).  
**Primary Dependencies**: Drupal core (`node`, `field`, `text`, `paragraphs`); módulo custom `custom_configs`; tema `default`. **Nenhuma dependência Composer nova. Nenhuma View nova.**  
**Storage**: reuso integral — `node.field_vaga_faq` (ER card. 1), `node.field_faq_itens` (ERR → `faq_item_p`), `paragraph.field_pergunta` / `paragraph.field_resposta`. Sem storage/instance novos.  
**Testing**: validação manual via [quickstart.md](quickstart.md); sem suite PHPUnit dedicada (padrão do projeto para apresentação + seed).  
**Target Platform**: detalhe público da vaga (`/vaga/<alias>`), desktop e mobile.  
**Project Type**: módulo custom (`modules/custom/custom_configs`) + tema (`themes/custom/default`) + config já exportada (`config/sync`).  
**Performance Goals**: sem query extra além da ER já carregada no node; sem bloco/View.  
**Constraints**: sem `core/`/`vendor/`; sem View `faq_vaga`; sem passo manual no admin; sem sobrescrever vínculo/editorial divergente; pt-BR.

**Estado atual verificado (2026-10-27):**

- CT `faq`, paragraph `faq_item_p`, `field_faq_itens`, `field_vaga_faq` (cardinality 1) presentes em `config/sync` e nos helpers 030/031.
- Coleção seed UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` com as duas perguntas do layout — helper `_custom_configs_seed_faq_collection_example()`.
- Vaga Engenheiro Civil UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234` — seed **não** preenche `field_vaga_faq`.
- Twig full: FAQ inline após cartão empresa (037), ~linhas 175–213; usa `pergunta or resposta` e `data-bs-parent`.
- CSS `.vaga-detalhe__faq`: override parcial (navy + `#f7fafc` no ativo); itens ainda agrupados visualmente.
- Include `vaga-faq-accordion.html.twig` **ainda não existe**.
- View `faq_vaga` / `block_faq*` **ausentes** em `config/sync` (confirmado).
- Último hook: `custom_configs_update_11058`. Próximo livre: **`11059`**.
- Checkout git atual: `feature-vaga`. Identidade da feature: `038-vaga-faq-accordion` (`.specify/feature.json`).

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK, zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só `custom_configs` + tema `default` (+ `config/sync` se cex alterar algo) |
| Reuso de storage | PASS | nenhum storage novo; ensures só se ausente |
| Decisão View documentada | PASS | Escopo da spec + [research.md](research.md) R1 — nativo na coluna 8 |
| Deploy automatizado | PASS | hook `11059` + tema; receita `cim` → `updb` → `cim` → `cr` |
| Seeds via Entity API / idempotência | PASS | seed coleção existente + link só se vazio |
| Não sobrescrever editorial | PASS | FR-016; mesmo critério dos seeds 031/037 |
| Sem passo manual no admin | PASS | updb + cr; cex na origem |
| PRD guardião | A atualizar na implementação | parágrafo detalhe full (FAQ cards + seed/hook) |
| Reinício de cache | A executar na implementação | `drush cr` após updb/tema |

**Post-design**: nenhuma violação. Trade-off “sem View” está justificado no Escopo e em R1. Detalhe em [research.md](research.md).

## Design Decisions

1. **Sem View** nesta entrega (R1): fonte de verdade = `field_vaga_faq` no Twig da coluna principal.
2. **Include** `vaga-faq-accordion.html.twig` (R3): full só orquestra ordem e filtro mínimo.
3. **Item válido** = pergunta **e** resposta não vazias após trim (R4); seção some se a lista filtrada for vazia.
4. **Painéis independentes** — sem `data-bs-parent` (R5).
5. **CSS cards** sob `.vaga-detalhe__faq` (R6): radius ~10px, margem 1rem, botão sempre branco, seta escura; título via `.vaga-detalhe__section-title`.
6. **Hook `11059`** (R7): ensures FAQ existentes → seed coleção → `_custom_configs_link_vaga_engenheiro_civil_faq()`.
7. **cex** tipicamente no-op; PRD cirúrgico (R8).
8. **Fora**: View; mudar card. de `field_vaga_faq`; redesenhar header/sidebar/empresa/CTA; FAQ global do site.

## Project Structure

### Documentation (this feature)

```text
specs/038-vaga-faq-accordion/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/
│   ├── deploy-faq-accordion.md
│   └── vaga-faq-accordion-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_configs/
  custom_configs.install
    # + custom_configs_update_11059
    # + _custom_configs_link_vaga_engenheiro_civil_faq

themes/custom/default/
  templates/content/node--vagas--full.html.twig
    # substituir bloco FAQ inline por include
  templates/content/includes/vaga-faq-accordion.html.twig
  assets/css/components/vagas-detalhe.css
    # reescrever bloco FAQ accordion → cards isolados

config/sync/   # só via drush cex se houver diff real; sem View nova
PRD.md         # cirúrgico: parágrafo detalhe full (FAQ cards + 11059)
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Helper de vínculo + `hook_update_11059` em `custom_configs.install`
3. Include Twig + chamada no detalhe full
4. CSS dos cards Accordion
5. Origem: `drush updb -y` → `drush cex -y` → `drush cr`
6. Atualização cirúrgica do `PRD.md`
7. Validação via [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Não criar View apesar da preferência verbal inicial | Ordem Figma só é estável na coluna 8; CTA já é bloco pós-node; dados já no formulário da vaga | View + Contextual Filter + placement (quebra layout / complexidade) |
| Remover `data-bs-parent` | Cards isolados leem como unidades independentes | Manter parent (reforça bloco agrupado) |
| Hook só de vínculo + ensures, sem YAML novo esperado | Estrutura já em sync; gap = seed link + tema | Recriar fields no hook (risco de duplicata) |
