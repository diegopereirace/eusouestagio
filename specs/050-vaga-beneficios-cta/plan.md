# Implementation Plan: Refatoração da Vaga Interna (Benefícios + CTA)

**Branch**: `050-vaga-beneficios-cta`  
**Date**: 2026-11-02  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessor**: `030` / `031` / `036` (benefícios); `032` / `034` / `037` (empresa pública); `033` (candidatura); `031` (CTA vagas)  
**Hook**: `custom_configs_update_11071` (+ `11072` texto livre; `11075` re-report migrate)  
**Review fixes (2026-11-03)**: auto-fill `field_empresa_u` com form hidden; sync JS CTA↔sidebar; cache CTA em `hook_block_build_alter`; verify script  
**Setup**: scaffolding Spec Kit (`.specify/scripts`) ausente neste repo — feature ativa via `.specify/feature.json` → `specs/050-vaga-beneficios-cta`

## Summary

Três frentes no detalhe/edição da vaga (`node` bundle **`vagas`**):

1. **Empresa**: ocultar `field_empresa_u` no form display; manter `field_vaga_empresa` como único vínculo editorial do perfil público.
2. **Benefícios**: vocabulário `beneficios` (7 termos + ícone WebP) + campo `field_vaga_beneficios_t` (checkboxes, required); ocultar paragraphs `field_vaga_beneficios`; migrar títulos existentes → termos por match normalizado; Twig da seção Benefícios passa a ler a taxonomia (mesmo grid CSS).
3. **CTA final** (`default_ctav1vagas`): visibility `user_role: candidato` + bundle `vagas`; botão “Candidatar-se Agora” reutiliza `js-candidatar-vaga` / rota `custom_panel.vagas_candidatar`; estado já candidatada = botão desabilitado (não esconder o bloco).

Deploy 100% automatizado: hook idempotente + `drush cex` na origem; destino `cim` → `updb` → `cim` → `cr`. PRD §3.1 / §3.4 / §3.6 cirúrgico na implementação.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ (helpers tipados; `.install` legado sem `declare(strict_types=1)` no arquivo inteiro), Drupal 11, Twig 3, CSS3 |
| Primary Dependencies | Field/Taxonomy API; `custom_configs`; tema `default`; `custom_panel` (rota candidatura). **Sem** Composer novo |
| Storage | Vocab `beneficios`; `field.storage.taxonomy_term.field_image`; `field.storage.node.field_vaga_beneficios_t`; instances + displays; placement CTA com role |
| Testing | Manual via [quickstart.md](quickstart.md); sem PHPUnit dedicado |
| Target Platform | Form admin da vaga + detalhe público `/vaga/<alias>` (desktop/mobile) |
| Performance | Seed 7 termos + backfill por nó (dezenas–centenas); managed files reusados por URI |
| Constraints | Sem `core/`/`vendor/`; sem apagar storage legado; assets em módulo; pt-BR; SDD |
| Scale/Scope | 1 vocab, 2 storages novos, 1 hook `11071`, Twig node + CTA, preprocess/access CTA, 7 WebP, PRD |

**Estado atual verificado (2026-11-02):**

- Empresa: `field_empresa_u` + `field_vaga_empresa` ambos visíveis no form; público usa só `field_vaga_empresa`.
- Benefícios: `field_vaga_beneficios` → `beneficio_vaga_p` (`field_image` + `field_text_simple`), required; hooks `11056`/`11058`; assets PNG `assets/beneficios/beneficios-0{1..4}.png`; Twig loop em `node--vagas--full.html.twig`.
- CTA: placement `default_ctav1vagas` (UUID `e5f6a7b8-…`), instância `d4e5f6a7-…`, visibility só bundle `vagas`; link seed `internal:/cadastro/candidato`; `default_block_access` esconde se já candidatada; Twig sem `js-candidatar-vaga`.
- Candidatura: POST `custom_panel.vagas_candidatar` + `script-painel.js` + role `candidato`.
- Taxonomia com ícone WebP: inexistente (só `curso.field_icone_fa`).
- Último hook: **`11070`**. Próximo: **`11071`**.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só `custom_configs`, tema `default`, `config/sync` via cex |
| Reuso de storage | PASS com storage novo justificado | `field_image` em **taxonomy_term** não existe (só em node/paragraph); `field_vaga_beneficios` é ERR e não pode mudar de tipo — novo `field_vaga_beneficios_t` |
| Deploy automatizado | PASS | `11071` + `drush cex`; receita `cim`→`updb`→`cim`→`cr` |
| Seeds Entity API / idempotentes | PASS | termos por UUID; fill-if-empty; helper de file existente |
| Não sobrescrever editorial | PASS | migração só se `field_vaga_beneficios_t` vazio; ícone/nome de termo existentes preservados |
| Assets versionados | PASS | `assets/beneficios/*.webp` → `public://beneficios/` |
| Clean URLs / rotas nomeadas | PASS | `path('custom_panel.vagas_candidatar', {node})` |
| PRD guardião | A atualizar na implementação | §3.1, §3.4, §3.6 |
| Reinício cache | A executar na implementação | `drush cr` |

**Post-design**: nenhuma violação injustificada. Trade-offs documentados em [research.md](research.md) e Complexity Tracking abaixo.

## Architecture

```
Edição vaga (form default)
  ├─ field_vaga_empresa          ← único “Empresa” visível
  ├─ field_empresa_u             ← HIDDEN (storage permanece)
  ├─ field_vaga_beneficios_t     ← checkboxes → termos beneficios  [NOVO]
  └─ field_vaga_beneficios       ← HIDDEN (paragraphs legado)

Detalhe público
  ├─ Seção Benefícios ← field_vaga_beneficios_t (ícone termo + name)
  ├─ Sidebar ações    ← js-candidatar-vaga (inalterado)
  └─ Bloco CTA vagas  ← só role candidato; mesmo js-candidatar-vaga
```

## Design Decisions

1. Vocab `beneficios` + `field_vaga_beneficios_t` + `taxonomy_term.field_image` (ver research R2/R3).
2. Ocultação via form/view display exportados (`region: hidden`), não via remoção de field.
3. Migração por título normalizado (R4); paragraphs intocados.
4. CTA: `user_role: candidato` + Twig/JS da sidebar; `hook_block_access` deixa de forbidden quando já candidatada (Twig disabled) — R5.
5. Hook único `11071` com helpers; cex consolida config estrutural.
6. PRD cirúrgico na fase implement.

## Project Structure

### Documentation (this feature)

```text
specs/050-vaga-beneficios-cta/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/
│   ├── deploy-vaga-beneficios-cta.md
│   ├── vaga-beneficios-taxonomy-render.md
│   └── cta-vagas-candidatura.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_configs/
  custom_configs.install                    # + update_11071 + helpers ensure/seed/migrate
  assets/beneficios/beneficios-01.webp … 07.webp

themes/custom/default/
  templates/content/node--vagas--full.html.twig   # seção benefícios ← taxonomia
  templates/block/block--default-ctav1vagas.html.twig  # js-candidatar-vaga + script-painel
  default.theme                               # preprocess CTA; ajustar block_access
  assets/css/components/vagas-detalhe.css     # só se markup exigir (preferir reuso)
  assets/css/cta-v1-vagas.css                 # só se estado disabled precisar estilo

config/sync/  (via drush cex — não editar à mão para inventar)
  taxonomy.vocabulary.beneficios.yml
  field.storage.taxonomy_term.field_image.yml
  field.field.taxonomy_term.beneficios.field_image.yml
  field.storage.node.field_vaga_beneficios_t.yml
  field.field.node.vagas.field_vaga_beneficios_t.yml
  core.entity_*_display.taxonomy_term.beneficios.*
  core.entity_form_display.node.vagas.default.yml   # hidden empresa_u + paragraphs; checkboxes
  core.entity_view_display.node.vagas.default.yml
  block.block.default_ctav1vagas.yml                # + user_role candidato

PRD.md   # §3.1, §3.4, §3.6 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: estrutura + seed + migração + Twig/CTA + cex + PRD + cr
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Novo `field.storage.taxonomy_term.field_image` | Não há image storage em taxonomy_term; FA em `curso` não atende WebP | Reusar `field_icone_fa` (rejeitado: não é imagem) |
| Novo `field_vaga_beneficios_t` em paralelo ao ERR legado | Field API não converte ERR→taxonomy; legado fica oculto para rollback | Apagar paragraphs/storage (rejeitado: fora do escopo) |
| CTA deixa de usar `field_link` URI no Twig de vagas | Rota de apply é POST; link de cadastro genérico descumpre FR-011 | Apontar link field para `/vaga/apply/N` (GET inválido) |
| Mostrar CTA disabled pós-candidatura (em vez de hide) | Alinha à sidebar; spec aceita disabled **ou** equivalente | Manter só `hook_block_access` forbidden (aceitável, mas menos alinhado) |
