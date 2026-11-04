# Implementation Plan: Refino Figma Contato (bleed + cards) e tipografia de títulos

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup executado manualmente a partir de `.specify/feature.json` → `specs/058-refino-figma-contato`)  
**Date**: 2026-11-04  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessors**: `017-pagina-contato`, `018-contato-node`, `047`/`052`/`053` (padrão de refino Figma), `057-seed-arquivos-imutaveis` (não alterar assets sem ordem)  
**Hook**: nenhum esperado (só CSS/Twig/estilo base); se display/fields faltarem na implementação → `custom_configs_update_11089`  
**Input**: Especificação em `specs/058-refino-figma-contato/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Fechar as **lacunas reais** de `/contato` frente ao Figma e padronizar o **token tipográfico de título de seção** no site público, sem recriar o Content Type / webform das features 017/018:

1. **Bleed assimétrico**: formulário alinhado à margem do header; painel `#E5EEFF` encostado na borda direita da viewport (≥992px); empilhamento abaixo de 992px.
2. **Linha de ações**: botão Enviar (~180×56, `#FD7B1A`) + cards E-mail (~280×70) e WhatsApp (~214×70) com borda `#C6C6CD` e valor `#45464D`; omit empty já existente no `hook_webform_submission_form_alter`.
3. **Painel**: imagem + textos centralizados; cantos/“caixa” arredondada interior substituídos pelo bleed de coluna quando desktop.
4. **Inputs**: altura ~57px, borda fina, radius suave; submit full-width &lt;992px.
5. **Token de seção**: Inter 600 / 30px / 38px / ls −0.3px / `#000` — estilo base reutilizável + aplicação em Contato e headers de seção públicos (exceto heroes/microtítulos/nav/footer/CTAs especiais).

Zero passos manuais em produção. Sem tipos/fields novos.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / 8.4, Drupal 11, Twig 3, CSS3 |
| Primary Dependencies | Node `contato`, Webform `contato`, tema `default` (Bootstrap 5 / Barrio). **Sem** Composer novo |
| Storage | Sem tipos/fields/storages novos; Node UUID seed `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1` |
| Testing | Manual via [quickstart.md](quickstart.md); sem PHPUnit dedicado |
| Target Platform | `/contato` (node `contato` full) + tipografia global de seção em páginas públicas; breakpoint 992px |
| Performance | CSS dedicado `layout_contato` + utilitário tipográfico leve; sem JS novo; imagem painel via style `contato_painel` (640 + WebP) + `lazy` + `decoding=async` |
| Constraints | Sem `core/`/`vendor/`; sem SCSS toolchain; CSS plano / Grid+Flex rasos; layout Contato isolado (FR-013); tipografia de seção propaga globalmente; não regressar webform/envio; assets seed imutáveis (057) |
| Scale/Scope | Twig node Contato + CSS Contato + utilitário/token tipográfico + alinhamento de headers de bloco existentes + PRD cirúrgico se visual publicado mudar |

**Estado atual verificado (2026-11-04):**

- Último hook: **`custom_configs_update_11088`** → próximo livre **`11089`** (só se estrutural surgir).
- Twig: `node--contato--full.html.twig` — wrapper `.layout-contato-node.container` + `row` / `col-lg-7` | `col-lg-5`; H2 Poppins via CSS; **sem** bleed; painel com `border-radius: 24px` dentro da coluna.
- Cards: injetados em `.form-actions` por `custom_configs_webform_submission_form_alter()` (classes `layout-contato-node__shortcut*`); já omit empty + `wa.me` + DDI 55.
- CSS: título = Poppins 700 + `clamp(...)` / `#0f172a` — **diverge** do token Inter 600 / 30 / 38 / −0.3px / `#000`.
- Cards: borda `#d0d7e5`, valor `#64748b` — alvos Figma `#C6C6CD` / `#45464D`; dimensões ainda fluidas (não ~280×70 / ~214×70 / 180×56).
- Headers de seção no tema: maioria Poppins + tamanhos ad hoc (`nd-`, `be-`, `oe-`, `je-`, `oqf-`, `cf-`, `nm-`, `dqs-`, etc.).
- Inter já carregada no tema (ex.: hero search estudantes).
- Padrão full-bleed do projeto: liberar `#main.container` via `:has(...)` **sem** `100vw` (Missão/Visão, Impact Numbers) — base para R1.
- `mattpocock/skills`: não instalado; seguir CSS limpo do repo.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só tema `default`, opcional `custom_configs`/`PRD.md`/`config/sync` |
| Reuso / sem tipos novos | PASS | CT/fields/webform 017–018 inalterados |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | sem hook estrutural esperado; se `11089`, receita padrão |
| Seeds Entity API / não sobrescrever editorial / 057 | PASS | sem reseed de imagem; cards só leitura de fields |
| Clean URLs | PASS | `/contato`, `mailto:`, `wa.me` inalterados |
| Performance / CSS isolado | PASS | Contato sob `.node--contato`; token tipográfico como utilitário nomeado |
| Contrib first / custom mínimo | PASS | Webform + Node; alter só apresentação |
| Convivência 017/018 | PASS | refino visual; envio webform preservado (FR-014) |
| Tokens locais Contato (cores Figma) | PASS | hex da spec; tipografia Inter reutilizada |
| PRD guardião | A avaliar na implementação | só se contrato visual §3.6 Contato mudar de forma relevante para editores |
| Reinício cache | A executar na implementação | `drush cr` |

**Post-design**: gates mantidos. Trade-offs em [research.md](research.md) e Complexity Tracking. Sem violação injustificada.

## Architecture

```text
/contato (node contato, view mode full)
├─ Twig node--contato--full.html.twig
│    layout assimétrico (sem container que corte o painel)
│    ├─ coluna form: título .ese-section-title + webform
│    │    └─ form-actions (Webform)
│    │         ├─ submit Enviar Mensagem
│    │         └─ shortcuts (hook alter) → cards E-mail / WhatsApp
│    └─ coluna painel: #E5EEFF bleed direita (≥992px)
│         image + field_text_simple + field_text_simple_long
├─ library default/layout_contato → layout-contato.css
└─ token tipográfico global
     .ese-section-title (+ aliases nos __header__title de blocos públicos)
```

## Design Decisions

1. **Bleed Contato**: remover/afrouxar `.container` no wrapper do node; grid CSS (ou row full-width) com padding-left = gutter do container do tema e painel direito até a borda da viewport; **sem** `width: 100vw` (R1).
2. **Mobile &lt;992px**: empilhar form → painel; painel 100% largura; submit 100%; cards wrap sem overflow (R2).
3. **Cards/botão**: só CSS (+ markup já injetado); dimensões alvo ±10%; borda `#C6C6CD`; valor `#45464D`; ícones laranja mantidos (R3).
4. **Painel**: desktop = coluna full-bleed sem “card” flutuante; conteúdo centralizado flex; `border-radius` só se Figma exigir canto interno residual — priorizar face reta na borda direita (R4).
5. **Token**: classe utilitária `.ese-section-title` (Inter 600 / 30px / 38px / −0.3px / `#000`) em CSS base do tema ou library tipográfica leve (R5).
6. **Padronização**: Contato H2 + headers de seção públicos listados em research R6 herdam o token; **não** forçar heroes, CTAs escuros, card titles, nav, footer, labels (R6).
7. **Acentos de cor** (ex. Benefícios azul/escuro): métricas do token; spans de cor locais preservados (R6).
8. **Hook**: nenhum por padrão; `11089` só se display/fields/placement faltarem (R7).
9. **Origem**: `drush cr`; `drush cex -y` só se houver diff estrutural.
10. **Destino**: se só tema → `git pull` + `drush cr`; se houver `11089`/config → `cim` → `updb` → `cim` → `cr`.
11. **PRD**: cirúrgico §3.6 Contato / tipografia de seção se o contrato publicado mudar.
12. **Fora**: SCSS; novos bundles; redesign header/footer/FAB; alterar elementos do webform; Layout Builder.

## Project Structure

### Documentation (this feature)

```text
specs/058-refino-figma-contato/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── contato-refino-render.md
│   └── section-title-token.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/node/node--contato--full.html.twig
      # markup assimétrico; classe .ese-section-title no H2
  assets/css/layout-contato.css
      # bleed, painel, cards, botão, inputs, mobile
  assets/css/ese-section-title.css   # NOVO (ou bloco em style.css)
      # token + lista de aliases de headers de seção
  default.libraries.yml
      # anexar utilitário tipográfico (global ou via dependência)

modules/custom/custom_configs/
  custom_configs.install             # só se 11089 necessário
  custom_configs.module              # alter de shortcuts: só se markup/classes precisarem ajuste fino

config/sync/                         # via cex só se display/estrutural

PRD.md                               # §3.6 cirúrgico se aplicável
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: Twig/CSS bleed Contato → cards/botão/inputs → token + aliases de seção → PRD se preciso → `cr` (+ `cex`/`11089` se surgir)
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Tipografia global além de `/contato` | FR-017/FR-018 + SC-008 exigem padronização projeto-wide | Só alterar Contato |
| Preservar spans de cor em títulos (Benefícios) | Token define `#000` default; Figma local usa acento azul | Forçar preto absoluto e quebrar marca da seção |
| Liberar container / grid assimétrico sem `100vw` | SC-001 + edge case scrollbar; padrão Missão/Visão | `width: 100vw` / margin negativo cego |
| Sem hook por padrão | Feature é apresentação; 057 desencoraja touch de assets | Seed/recreate Node Contato |
| `mattpocock/skills` ausente | Pacote não instalado; CSS plano do repo cobre Grid/Flex | Bloquear feature à espera do pacote |
