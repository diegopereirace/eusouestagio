# Implementation Plan: Refino Visual "Para Empresas" — Conformidade com Figma

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup executado manualmente a partir de `.specify/feature.json` → `specs/052-refino-figma-para-empresas`)  
**Date**: 2026-11-03  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessors**: `019-para-empresas-page`, `020-depoimentos-empresas`, `042-nossos-diferenciais-ui`, `047-refino-figma-quem-somos` (padrão de refino), `11028` (shell `page`)  
**Hook**: `custom_configs_update_11076`  
**Input**: Especificação em `specs/052-refino-figma-para-empresas/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Fechar as **lacunas reais** de `/para-empresas` frente ao Figma e à home, sem redesenhar o que `019`/`020`/`042` já entregaram:

1. **Banner**: paridade de init/transição/setas com a home (reuso do JS `banner_carousel`, sem CSS da home); CTAs mobile em **768px** com larguras iguais; seletores pós-`11028` (`path-para-empresas`); sem placeholder genérico.
2. **Nossos Diferenciais**: texto descritivo `#4E4E4E`; decorações absolutas off no mobile; camadas desktop intactas.
3. **Depoimentos**: bullet ativo alinhado ao Figma (laranja) + validação/correção da sync clique↔slide.
4. **CTA final**: override Twig da instância PE com lógica de role `empresa` (cadastro desabilitado + “Abrir Vaga” só para empresa); hook `11076` para legado “Falar com Especialista”; PRD cirúrgico.

Zero passos manuais em produção.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / 8.4, Drupal 11, Twig 3, CSS3, JS ES5 (behaviors) |
| Primary Dependencies | Views `banners` / `depoimentos_carousel`; Block Content `nossos_diferenciais` / `cta_v1`; tema `default` (Bootstrap 5). **Sem** Composer novo |
| Storage | Sem tipos/fields novos; seed CTA UUID `a7b8c9d0-…0123`; banners PE existentes |
| Testing | Manual via [quickstart.md](quickstart.md); sem PHPUnit dedicado |
| Target Platform | `/para-empresas` (node `page`); breakpoints 768px |
| Performance | Banner LCP `eager` no 1º slide; CTA sem imagem hoje; lazy se houver `<img>` |
| Constraints | Sem `core/`/`vendor/`; sem SCSS toolchain; sem novos bundles; CSS plano; tokens locais; não regressar home / QS / Metodologia / Benefícios PE |
| Scale/Scope | CSS/JS/Twig tema + 1 preprocess CTA + hook `11076` + PRD |

**Estado atual verificado (2026-11-03):**

- Último hook: **`custom_configs_update_11075`** → próximo livre **`11076`**.
- Banner PE: Twig Bootstrap carousel + library **só CSS** `banner_para_empresas`; **não** anexa `banner_carousel` JS; CTAs empilham em **991.98px**; CSS ainda usa `body.node--type-para-empresas`.
- Home: `banner_carousel` JS em `.banner-carousel` + `data-bs-pause="hover"`.
- ND: anel verde atrás + dot navy na frente OK; `.nd-header__subtitle` / `.nd-item__desc` = **`#4a5d73`** (alvo `#4E4E4E`); decos ainda absolutas no mobile.
- Depoimentos: `carousel-center.js` + `data-carousel-dot` OK; `.is-active` = **navy** (Figma: laranja).
- CTA PE: seed “Abrir Vaga” + URI canônica; template genérico `block--block-cta-v1.html.twig` **sem** role; sem override `block--default-ctav1paraempresas.html.twig`.
- `mattpocock/skills`: não instalado.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só tema `default`, `custom_configs`, `PRD.md`, `config/sync` via cex se necessário |
| Reuso / sem tipos novos | PASS | sem CT/BT/PT novos |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11076` + cex se estrutural |
| Seeds Entity API / não sobrescrever editorial | PASS | legado conhecido só; fill-if-empty |
| Clean URLs | PASS | rota `/para-empresas` e `/painel/empresa/vagas/nova` inalteradas |
| Performance / CSS isolado | PASS | libraries por seção; sem pipeline SCSS |
| Contrib first / custom mínimo | PASS | reuso Bootstrap + behaviors existentes |
| Convivência 019/020/042 | PASS | só lacunas; Metodologia/Benefícios fora |
| Tokens locais (não mutar `--brand-orange` global) | PASS | uso de `var(--brand-orange)` no dot ativo; sem redefinir token |
| PRD guardião | A atualizar na implementação | §3.1.0b / §3.6 (CTA por role + refinamentos) |
| Reinício cache | A executar na implementação | `drush cr` |

**Post-design**: gates mantidos. Trade-offs em [research.md](research.md) e Complexity Tracking. Sem violação injustificada.

## Architecture

```
/para-empresas (node page)
├─ região banner
│   └─ View banners / block_para_empresas
│        Twig + library banner_para_empresas
│          └─ depends → banner_carousel (JS init)
│        CTAs overlay; mobile <768px equal-width
├─ content_full
│   ├─ nossos_diferenciais (instância home) — CSS #4E4E4E + mobile decos off
│   ├─ nossa_metodologia / o_que_fazemos / benefícios — FORA (regressão só)
│   ├─ depoimentos_carousel — dots laranja + sync
│   └─ cta_v1 default_ctav1paraempresas
│        preprocess is_empresa
│        Twig override: botões condicionais
└─ hook 11076 — legado field_link_2 → Abrir Vaga (idempotente)
```

## Design Decisions

1. **Banner JS**: classe `banner-carousel` no carousel PE + dependency da library home (JS), sem CSS home (R1).
2. **Breakpoint CTAs**: `767.98px` com flex larguras iguais (R2).
3. **Body/path**: `body.path-para-empresas` (R3).
4. **Mobile art**: fallback desktop; sem placeholder Twig (R4).
5. **ND**: `#4E4E4E` + hide decos `<768px` (R5).
6. **Depoimentos**: ativo `--brand-orange`; auditar sync (R6).
7. **CTA**: Twig + preprocess por machine name do bloco; ocultar botão 2 no servidor (R7).
8. **Hook `11076`**: só legado “Falar com Especialista” / URI canônica (R8).
9. **Origem**: `drush cex -y` se houver diff estrutural; senão só código + PRD.
10. **Destino**: `git pull` → `cim` → `updb` → `cim` → `cr`.
11. **PRD** cirúrgico §3.1.0b / §3.6.
12. **Fora**: SCSS; home visual; Metodologia/Benefícios/QS; novos bundles.

## Project Structure

### Documentation (this feature)

```text
specs/052-refino-figma-para-empresas/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── para-empresas-refino-render.md
│   └── deploy-para-empresas-refino.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  default.libraries.yml              # banner_para_empresas → depends banner_carousel (JS)
  default.theme                      # preprocess block__default_ctav1paraempresas (is_empresa)
  templates/views/views-view--banners--block-para-empresas.html.twig
  templates/block/block--default-ctav1paraempresas.html.twig   # NOVO
  assets/css/banner-para-empresas.css
  assets/css/nossos-diferenciais.css
  assets/css/depoimentos-carousel.css
  assets/css/cta-v1.css              # ou CSS dedicado PE para estado disabled
  assets/js/carousel-center.js       # só se bug de sync for reproduzido

modules/custom/custom_configs/
  custom_configs.install             # custom_configs_update_11076 (+ helper legado CTA)

config/sync/                         # via drush cex se houver alteração estrutural (esperado: nenhum)

PRD.md                               # §3.1.0b / §3.6 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: banner (library/Twig/CSS) → ND CSS → depoimentos CSS/(JS) → CTA Twig+preprocess+CSS → hook `11076` → PRD → `cex` se preciso → `cr`
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Reusar só JS da home, não o CSS `banner-carousel` | SC-001 exige paridade de comportamento; CSS home quebraria overlay PE (SC-008) | Unificar libraries CSS |
| Override Twig por machine name do bloco | Isola lógica B2B sem poluir CTA de Quem Somos | If no template genérico |
| Hook condicional a título legado | Preserva editorial divergente (gate deploy) | Overwrite sempre |
| Cor do bullet via token `--brand-orange` | Alinha Figma sem mutar o token global | Hex hardcoded paralelo |
