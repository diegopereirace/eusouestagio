# Implementation Plan: Refatorar UI — Nossos Diferenciais (Home)

**Branch**: `feat/nossos-diferenciais-ui` (criar a partir de `main`; scaffolding Spec Kit — `.specify/scripts` — ausente; setup via `.specify/feature.json`)  
**Date**: 2026-11-01  
**Spec**: [spec.md](spec.md)  
**Input**: Spec `042` + gap visual atual (título navy, colagem simplificada) vs Figma (título preto SemiBold, colagem composta, tipografia 30/38 e 14/20)

## Summary

Refatorar **apenas a apresentação** do bloco existente `nossos_diferenciais` (Twig + CSS + library) para fechar o gap com o Figma: tipografia e cor do cabeçalho, grade duas colunas com colagem composta à esquerda, lista à direita com cores cicláveis já existentes, Mobile First sem overflow. Sem novos tipos/fields; sem `cim`/`updb` obrigatórios. Deploy = Git + `drush cr`. A mesma instância/template cobre Home e `/para-empresas`.

## Technical Context

**Language/Version**: PHP 8.3+ (Drupal 11), Twig 3, CSS3 (Mobile First)  
**Primary Dependencies**: tema `default` (Bootstrap 5 / Barrio); bloco/paragraph já entregues na **004**. **Zero Composer novo.**  
**Storage**: sem schema/config estrutural novo; conteúdo editorial inalterado  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem PHPUnit dedicado  
**Target Platform**: site público (viewports ≤575 / ~768 / ≥992)  
**Project Type**: refatoração de tema Drupal SSR  
**Performance Goals**: CSS encapsulado em library dedicada; colagem via `image_style('wide')` + `loading="lazy"`; decoração sem JS  
**Constraints**: sem `core/`/`vendor/`; sem expandir CMS; não tocar `diferenciais_quem_somos`; CSS sem aninhamento profundo; utilitários Bootstrap quando equivalerem ao resultado  
**Scale/Scope**: 2 Twig (bloco + opcional paragraph), 1 CSS dedicado + library, remoção/neutralização do bloco legado em `style.css`, assets estáticos de apresentação no tema (opcional), contrato de render + deploy

**Estado verificado (2026-11-01):**

- Templates: `block--block-nossos-diferenciais.html.twig`, `paragraph--diferencial-item-p.html.twig`.
- Estilos hoje em `style.css` (~3243–3398): título `var(--brand-navy)` / weight 700; colagem = 1 img + `::before`/`::after` (disco navy + anel verde); sem fatias secundárias.
- Sem pipeline SCSS no tema `default` (0 `.scss`); padrão do projeto = CSS plano + library por bloco (ex.: `diferenciais_quem_somos`).
- Tokens `:root`: `--brand-green`, `--brand-navy`, `--brand-orange`.
- Mesma entidade UUID nas placements Home e PE (`config/sync/block.block.default_nossosdiferenciais*.yml`).
- Spec checklist OK; zero `[NEEDS CLARIFICATION]`.

## Constitution Check

*GATE: Must pass before Phase 0. Re-check after Phase 1.*

Sem `.specify/memory/constitution.md`. Gates = `.cursor/rules/estagio-*.mdc` + PRD + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | ✅ PASS | `spec.md` + checklist |
| Sem `core/` / `vendor/` | ✅ PASS | só `themes/custom/default/` (+ specs) |
| Sem alteração estrutural CMS | ✅ PASS | FR-001/018; zero YAML sync obrigatório |
| Deploy automatizado | ✅ PASS | Git + `drush cr`; sem passos admin |
| Performance | ✅ PASS | library dedicada; lazy na âncora; sem JS |
| Segurança / a11y | ✅ PASS | decoração `aria-hidden`; `alt` na âncora CMS |
| Clean URLs | ✅ PASS | sem rotas novas |
| Não regressar 013 | ✅ PASS | escopo só `.block-nossos-diferenciais` / `.nd-*` |
| Contrib first / custom mínimo | ✅ PASS | sem módulo novo; sem hook se assets no tema |

**Resultado**: sem violações — Complexity Tracking mínimo (ver tabela).

## Design Decisions

1. **Superfície**: Twig do bloco + CSS dedicado + `attach_library`; paragraph Twig só se classes/markup forem necessárias (preferir manter).
2. **Library** `default/nossos_diferenciais` → `assets/css/nossos-diferenciais.css`; attach no Twig do bloco (padrão 013).
3. **Migrar** regras `.block-nossos-diferenciais` / `.nd-*` de `style.css` para o CSS novo; deixar comentário de ponteiro ou remoção limpa no `style.css` para evitar duplicata.
4. **Título**: `#000000`, Poppins 600, 30px / 38px / letter-spacing −0.3px (FR-007); remover navy do título.
5. **Subtítulo**: Poppins 400, 14px / 20px, `max-width: ~663px` (FR-008/004); peso 300 legado → 400.
6. **Colagem**: wrapper relativo `.nd-media` — imagem CMS completa (`.nd-media__img`, proporção ~1324×718 via style `wide`) + spans decorativos (`.nd-media__deco-*`) com `z-index` atrás da foto.
7. **Fatias**: embutidas no asset editorial do CMS; **sem** `.nd-media__slice` nem pasta de PNGs no tema (evita duplicar a arte).
8. **Mobile First**: base empilhada; `@media (min-width: 992px)` para `col-lg-7`/`col-lg-5`; ocultar ring em viewports estreitas; `overflow: hidden` na seção.
9. **Lista**: cores via `.field__item:nth-child(3n+k)` (não `.nd-item:nth-child`, incompatível com `display: contents`); reforçar negrito; layout `d-flex` / `gap` já no paragraph.
10. **Deploy**: somente código tema + `drush cr` — ver [contracts/deploy-nossos-diferenciais-ui.md](contracts/deploy-nossos-diferenciais-ui.md).
11. **Fora**: `diferenciais_quem_somos`, novos fields, `config/sync` obrigatório, SCSS build novo.

## Project Structure

### Documentation (this feature)

```text
specs/042-nossos-diferenciais-ui/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md             # Fase 0
├── data-model.md           # Fase 1
├── contracts/
│   ├── block-render-ui.md
│   └── deploy-nossos-diferenciais-ui.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
├── default.libraries.yml                          # + nossos_diferenciais
├── templates/block/block--block-nossos-diferenciais.html.twig
├── templates/paragraph/paragraph--diferencial-item-p.html.twig  # só se preciso
├── assets/css/nossos-diferenciais.css             # NOVO — estilos do bloco
└── assets/css/style.css                           # remover bloco legado ND
```

**Structure Decision**: 100% tema (+ reuso do image style `wide` já no sync). Sem `custom_configs.install`, sem YAML novo.

## Complexity Tracking

| Trade-off | Justificativa | Alternativa rejeitada |
|-----------|---------------|------------------------|
| Colagem completa no CMS (não fatias no tema) | Asset editorial já inclui fatias; evita duplicar markup | `.nd-media__slice` + PNGs no tema |
| CSS plano + library (não SCSS) | Tema sem pipeline SCSS | Introduzir build Sass só para 1 bloco |
| Spans CSS para formas | Sem asset binário obrigatório para círculos | PNGs decorativos + hook `public://` |
| Extrair de `style.css` | Manutenibilidade / FR-016; padrão 013 | Continuar inchando `style.css` |
| `image_style('wide')` | Limita payload sem YAML novo | Original via `file_url` / style dedicado |

## Phases

### Fase 0 — Research (concluída)

Decisões em [research.md](research.md).

### Fase 1 — Design & Contracts (concluída)

- [data-model.md](data-model.md)
- [contracts/block-render-ui.md](contracts/block-render-ui.md)
- [contracts/deploy-nossos-diferenciais-ui.md](contracts/deploy-nossos-diferenciais-ui.md)
- [quickstart.md](quickstart.md)

### Fase 2 — Tasks

Fora deste comando — `/speckit-tasks`.

## Constitution Check (pós-design)

Revalidado após Phase 1: gates ✅; deploy só `cr` reforça “zero admin”; assets no tema evitam hook desnecessário.
