# Implementation Plan: Refatorar UI — Nossa Metodologia

**Branch**: `feat/nossa-metodologia-ui` (criar a partir de `main`; scaffolding Spec Kit — `.specify/scripts` — ausente; setup via `.specify/feature.json`)  
**Date**: 2026-11-01  
**Spec**: [spec.md](spec.md)  
**Input**: Spec `043` + gap visual atual (título navy 700, etapas/passos esticados na largura do container 1280px, setas com `top: 48px` fixo) vs Figma (título preto SemiBold, wrappers 770px / 1160px centralizados, Mobile First)

## Summary

Refatorar **apenas a apresentação** do bloco existente `nossa_metodologia` (Twig + CSS + library) para fechar o gap com o Figma: tipografia e cor do cabeçalho, larguras máximas estritas das duas linhas (chevrons ~770px; ícones+setas ~1160px), alinhamento vertical das setas ao centro dos círculos, Mobile First sem overflow. Sem novos tipos/fields; sem `cim`/`updb` obrigatórios. Deploy = Git + `drush cr`. A mesma instância/template cobre Home e `/para-empresas`.

## Technical Context

**Language/Version**: PHP 8.3+ (Drupal 11), Twig 3, CSS3 (Mobile First)  
**Primary Dependencies**: tema `default` (Bootstrap 5 / Barrio); bloco/paragraph já entregues na **005**. **Zero Composer novo.**  
**Storage**: sem schema/config estrutural novo; conteúdo editorial inalterado  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem PHPUnit dedicado  
**Target Platform**: site público (viewports ≤575 / ~768 / ≥992)  
**Project Type**: refatoração de tema Drupal SSR  
**Performance Goals**: CSS encapsulado em library dedicada; imagens de etapas/passos com `loading="lazy"`; setas/conectores via CSS (sem JS, sem assets binários novos)  
**Constraints**: sem `core/`/`vendor/`; sem expandir CMS; CSS sem aninhamento profundo; utilitários Bootstrap quando equivalerem ao resultado; não redesenhar outros blocos da Home  
**Scale/Scope**: 2 Twig (bloco + opcional paragraph), 1 CSS dedicado + library, remoção do bloco legado em `style.css`, contrato de render + deploy

**Estado verificado (2026-11-01):**

- Templates: `block--block-nossa-metodologia.html.twig`, `paragraph--metodologia-passo-p.html.twig`.
- Sem `attach_library` no Twig do bloco.
- Estilos hoje em `style.css` (~3245–3452): título `var(--brand-navy)` / weight 700; `.nm-etapas` / `.nm-passos .field__item` com `flex: 1 1 0` e `max-width: none` (esticam no container 1280px); setas com `top: 48px`; mobile coluna em ≤767.98px; wrap de passos em ≤991.98px com setas ocultas.
- Sem pipeline SCSS no tema `default` (0 `.scss`); padrão do projeto = CSS plano + library por bloco (ex.: `nossos_diferenciais` / 042).
- Tokens `:root`: `--brand-green`, `--brand-navy`, `--brand-orange`.
- Mesma entidade UUID nas placements Home e PE (`block.block.default_nossametodologia*.yml` → `1f9b40ca-aa77-4e7e-a450-5aa94888e274`).
- Spec checklist OK; zero `[NEEDS CLARIFICATION]`.

## Constitution Check

*GATE: Must pass before Phase 0. Re-check after Phase 1.*

Sem `.specify/memory/constitution.md`. Gates = `.cursor/rules/estagio-*.mdc` + PRD + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | ✅ PASS | `spec.md` + checklist |
| Sem `core/` / `vendor/` | ✅ PASS | só `themes/custom/default/` (+ specs) |
| Sem alteração estrutural CMS | ✅ PASS | FR-001/017; zero YAML sync obrigatório |
| Deploy automatizado | ✅ PASS | Git + `drush cr`; sem passos admin |
| Performance | ✅ PASS | library dedicada; lazy nas imgs; sem JS |
| Segurança / a11y | ✅ PASS | setas/conectores decorativos; `alt` nas imgs; título preto contraste |
| Clean URLs | ✅ PASS | sem rotas novas |
| Não regressar outros blocos | ✅ PASS | escopo só `.block-nossa-metodologia` / `.nm-*` |
| Contrib first / custom mínimo | ✅ PASS | sem módulo novo; sem hook |

**Resultado**: sem violações — Complexity Tracking mínimo (ver tabela).

## Design Decisions

1. **Superfície**: Twig do bloco + CSS dedicado + `attach_library`; paragraph Twig só se classes/markup forem necessárias (preferir manter).
2. **Library** `default/nossa_metodologia` → `assets/css/nossa-metodologia.css`; attach no Twig do bloco (padrão 042/013).
3. **Migrar** regras `.block-nossa-metodologia` / `.nm-*` de `style.css` para o CSS novo; remoção limpa no `style.css` para evitar duplicata.
4. **Título**: `#000000`, Poppins 600, 30px / 38px / letter-spacing −0.3px (FR-010); remover navy do título.
5. **Cabeçalho**: manter utilitários Bootstrap `text-center text-md-end` no Twig; CSS reforça tipografia/cor; margem direita visual preservada (FR-003/004).
6. **Wrappers de linha**: `.nm-etapas` com `max-width: 770px` + centralização; `.nm-passos` (ou wrapper interno) com `max-width: 1160px` + centralização — aplicados em `@media (min-width: 992px)`; abaixo = 100% do container (FR-005–009, Mobile First FR-013).
7. **Flex das linhas**: em desktop, evitar `flex: 1 1 0` + `max-width: none` que esticam itens até 1280px; distribuir com `justify-content: space-between` / gap equilibrado dentro do wrapper estrito.
8. **Setas**: alinhar verticalmente ao centro dos círculos (`.nm-passo__icon` 96px → centro ~48px do ícone; preferir `align-items: center` no flex da linha + posição da seta relativa ao eixo dos ícones, não ao topo do rótulo). Em wrap &lt;992px: ocultar setas horizontais; em coluna mobile: reorientar 90° como hoje (FR-007, US3).
9. **Conectores entre chevrons**: manter `::after` decorativos atuais (cores existentes); contidos pelo wrapper 770px.
10. **Deploy**: somente código tema + `drush cr` — ver [contracts/deploy-nossa-metodologia-ui.md](contracts/deploy-nossa-metodologia-ui.md).
11. **Fora**: novos fields, `config/sync` obrigatório, SCSS build novo, redesign de outros blocos.

## Project Structure

### Documentation (this feature)

```text
specs/043-nossa-metodologia-ui/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md             # Fase 0
├── data-model.md           # Fase 1
├── contracts/
│   ├── block-render-ui.md
│   └── deploy-nossa-metodologia-ui.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
├── default.libraries.yml                          # + nossa_metodologia
├── templates/block/block--block-nossa-metodologia.html.twig  # attach_library (+ utilitários se preciso)
├── templates/paragraph/paragraph--metodologia-passo-p.html.twig  # só se preciso
├── assets/css/nossa-metodologia.css               # NOVO — estilos do bloco
└── assets/css/style.css                           # remover bloco legado NM
```

**Structure Decision**: 100% tema. Sem `custom_configs.install`, sem YAML novo.

## Complexity Tracking

| Trade-off | Justificativa | Alternativa rejeitada |
|-----------|---------------|------------------------|
| CSS plano + library (não SCSS) | Tema sem pipeline SCSS; padrão 042 | Introduzir build Sass só para 1 bloco |
| Max-width nos wrappers (não em cada item) | FR-006/008; um único ponto de controle | `max-width` por chevron/passo |
| Setas via CSS `::after` (já existente) | FR-001; sem field de seta | Markup SVG no Twig / assets PNG |
| Extrair de `style.css` | Manutenibilidade / FR-015; padrão 042 | Continuar inchando `style.css` |
| Mobile First `min-width: 992px` | FR-013; evita max-widths fixas no mobile | Desktop-first com overrides |

## Phases

### Fase 0 — Research (concluída)

Decisões em [research.md](research.md).

### Fase 1 — Design & Contracts (concluída)

- [data-model.md](data-model.md)
- [contracts/block-render-ui.md](contracts/block-render-ui.md)
- [contracts/deploy-nossa-metodologia-ui.md](contracts/deploy-nossa-metodologia-ui.md)
- [quickstart.md](quickstart.md)

### Fase 2 — Tasks

Fora deste comando — `/speckit-tasks`.

## Constitution Check (pós-design)

Revalidado após Phase 1: gates ✅; deploy só `cr` reforça “zero admin”; library dedicada isola regressões sem hook/config.
