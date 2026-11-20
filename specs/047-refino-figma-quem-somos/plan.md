# Implementation Plan: Refino Visual "Quem Somos" — Conformidade com Figma

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente a partir de `.specify/feature.json` → `specs/047-refino-figma-quem-somos`)  
**Date**: 2026-11-02  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/047-refino-figma-quem-somos/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Fechar as **lacunas reais** de `/quem-somos` frente ao Figma (features `009`–`015` já cobrem a maior parte): ocultar mídia do banner no mobile **sem** disparar request de imagem; Image Styles dedicados para foto Sobre nós, fundo Missão/Visão e ícones dos diferenciais; `background-attachment: fixed` (desktop) / `scroll` (mobile); ícones finais 80×80 / 64×64; outline no botão secundário do `cta_v1`; seed/config via `custom_configs_update_11069` idempotente + `drush cex`; PRD §3.6 cirúrgico. Zero passos manuais em produção.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3  
**Primary Dependencies**: Drupal core (Image, Image Style, Field, Block Content, Node); Paragraphs (já no projeto); tema `default` (Bootstrap Barrio 5 / Bootstrap 5.3). **Nenhuma dependência Composer nova.**  
**Storage**: PostgreSQL; Image Styles + displays em `config/sync`; assets seed em `modules/custom/custom_configs/assets/diferenciais-quem-somos/`; cópia → `public://` via hook  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (breakpoints Bootstrap; troca mobile/desktop em `768px` / `767.98px`)  
**Project Type**: Drupal theme + custom module (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: hero banner permanece LCP (`eager` + `fetchpriority="high"` só em desktop); demais imagens `lazy` + Image Style; sem request de imagem do banner em `<768px`; sem JS novo  
**Constraints**: sem `core/`/`vendor/`; sem novos content/block/paragraph types; CSS plano (sem pipeline SCSS); tokens locais por seção (não `--brand-orange`); deploy `cim` → `updb` → `cim` → `cr`; pt-BR; não regressar Impact in Numbers / home / outras rotas  
**Scale/Scope**: 3 Image Styles novos; ajustes Twig/CSS em 5 seções; 1 hook `11069`; assets ícones; displays `cex`; PRD cirúrgico

**Estado atual verificado (2026-11-02):**

- Último hook: `custom_configs_update_11068` → próximo livre **`11069`**.
- Banner Twig: coluna `.banner-quem-somos__media` **sem** `d-none d-md-block`; `<picture>` com `<source max-width:767.98px>` → **ainda baixa imagem no mobile** (lacuna SC-001).
- Sobre nós Twig: `file_url(uri)` cru + `loading="lazy"`; display `field_imagem` com `image_style: ''`; decorações `::before`/`::after` OK em `layout-sobre-nos.css`.
- Missão/Visão: `background-image` via URI cru; **sem** `background-attachment`; overlay + `min-height: 380px` OK; display `field_imagem_desktop` com `image_style: ''`.
- Diferenciais: `image_style: ''` no paragraph; CSS ícone fixo **64×64**; assets versionados ainda `icon-1.png`…`icon-8.png` (provisórios).
- CTA v1: `.cta-v1__btn--secondary` branco **sem** `border` outline.
- Image Styles existentes: `banner*`, `cto*`, `thumbnail`/`medium`/`large`/`wide` — **nenhum** dedicado a Quem Somos (foto / fundo / ícone).
- Impact in Numbers: tokens já alinhados ao Figma — só regressão.

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | só tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso / sem tipos novos | PASS | só Image Styles + displays + conteúdo seed |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11069` + `drush cex` estrutural |
| Clean URLs | PASS | rota `/quem-somos` inalterada |
| Performance / CSS isolado | PASS | libraries/seções existentes; Image Styles; sem request banner mobile |
| Contrib first / custom mínimo | PASS | Image Style core; sem módulo novo |
| Sem dump / Entity API | PASS | seed/ícones via Entity API no hook |
| Convivência 009–015 | PASS | só lacunas; Impact Numbers intocado estruturalmente |
| Tokens locais (não `--brand-orange`) | PASS | Assumption da spec |

**Post-design**: gates mantidos. Trade-off do `<picture>` (placeholder mobile vs. só `d-none`) justificado em research R1 para cumprir SC-001. Sem violação injustificada.

## Design Decisions

1. **Banner mobile sem request**: inverter `<picture>` para `<source media="(min-width: 768px)" srcset="desktop">` + `<img src>` placeholder 1×1 (data-URI) com `alt` preservado; coluna com `d-none d-md-block`; manter `eager`/`fetchpriority="high"` no `<img>` (LCP só quando o source desktop casa). Remover source mobile atual.
2. **Image Styles novos** (nomes canônicos):
   - `quem_somos_sobre_nos` — scale/crop **864×538** (2× de ~432×269);
   - `quem_somos_missao_visao` — scale largura **1920** (altura auto) para cover full-bleed;
   - `diferencial_simples_icon` — scale **160×160** (2× de 80×80).
3. **Sobre nós**: Twig passa a URL via Image Style (preprocess ou `ImageStyle::buildUrl` no Twig/`default.theme`); manter `loading="lazy"` e decorações CSS; ajustar gap texto↔imagem se régua Figma exigir (CSS só sob `.sobre-nos`).
4. **Missão/Visão**: mesma origem de URL estilizada no `style="background-image"`; CSS `background-attachment: fixed` em `@media (min-width: 768px)` e `scroll` abaixo; manter overlay/`cover`/`center`/`min-height`.
5. **Diferenciais**: configurar `image_style: diferencial_simples_icon` no view display do paragraph; CSS 80×80 (`≥768px`) / 64×64 (`<768px`); hook troca arquivos provisórios → finais **somente se** URI ainda for `icon-N.png` ou campo vazio.
6. **CTA**: `.cta-v1__btn--secondary { border: 1px solid #0F172A; }` sob `.block-cta-v1` apenas — não tocar `cta-v1-vagas` / `cta-v1-para-estudantes`.
7. **Hook `11069`**: ensure Image Styles (Config API); apply nos displays (`field_image` paragraph, `field_imagem` / `field_imagem_desktop` node — displays como rede de segurança mesmo quando Twig usa URI estilizada); copiar assets finais → `public://`; atualizar 8 paragraphs; preencher textos vazios do node; idempotente; nunca sobrescrever editorial divergente.
8. **Origem**: após implementar, `drush cex -y` versiona `image.style.*` + displays.
9. **Destino**: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`.
10. **PRD** §3.6 (Quem Somos): ícones finais, Image Styles, parallax, outline CTA, hook `11069`.
11. **Ícones finais**: se assets definitivos não forem fornecidos a tempo, manter PNGs atuais e só aplicar Image Style + caixa 80/64 (Assumption da spec).
12. **Fora**: SCSS toolchain; novos bundles; alteração estrutural de Impact Numbers; outras rotas.

## Project Structure

### Documentation (this feature)

```text
specs/047-refino-figma-quem-somos/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/quem-somos-refino-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
config/sync/
  image.style.quem_somos_sobre_nos.yml              # criar
  image.style.quem_somos_missao_visao.yml           # criar
  image.style.diferencial_simples_icon.yml          # criar
  core.entity_view_display.paragraph.diferencial_simples_p.default.yml  # image_style
  core.entity_view_display.node.quem_somos.default.yml                  # image_styles (defensivo)

themes/custom/default/
  templates/views/views-view-unformatted--banners--block-quem-somos.html.twig
  templates/content/node--quem-somos.html.twig      # URLs Image Style
  default.theme                                     # helper/preprocess Image Style (se necessário)
  assets/css/banner-quem-somos.css                  # só se CSS complementar ao d-none
  assets/css/layout-sobre-nos.css                   # gap Figma se necessário
  assets/css/quem-somos-missao-visao.css            # background-attachment
  assets/css/diferenciais-quem-somos.css            # 80/64
  assets/css/cta-v1.css                             # outline secundário

modules/custom/custom_configs/
  custom_configs.install                            # custom_configs_update_11069 + helpers
  assets/diferenciais-quem-somos/                   # ícones finais (substituir provisórios)

PRD.md                                              # §3.6 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. Image Styles + displays (admin/API) → `drush cex`
3. Twig banner (`picture` + `d-none d-md-block`) + Twig node (URLs estilizadas)
4. CSS: parallax, ícones 80/64, gap Sobre nós, outline CTA
5. Hook `11069` (styles ensure + assets + seed condicional)
6. Assets finais + PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| `<picture>` com placeholder data-URI no mobile (além de `d-none`) | SC-001 exige **zero request**; `display:none` sozinho não impede download do `<img eager>` | Só `d-none d-md-block` — esconde mas ainda baixa a arte |
| Twig/preprocess para Image Style em vez de só formatter | Missão/Visão e Sobre nós hoje usam URI cru no Twig (`background-image` / `<img>` manual) | Só configurar display — Twig atual ignora o formatter |
| Image Styles novos vs. reusar `large`/`wide` | Dimensões Figma (ícone 2×, foto 2×, fundo 1920) não batem com estilos genéricos; evita side-effects em outros bundles | Reusar `medium`/`large` — crop/scale inadequados e acoplamento |
