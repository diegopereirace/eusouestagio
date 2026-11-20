# Implementation Plan: Restaurar Benefícios Estudantes — Layout Figma (split + colagem circular)

**Branch**: `main` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup a partir de `.specify/feature.json` → `specs/055-restaurar-beneficios-estudantes`)  
**Date**: 2026-11-03  
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)  
**Predecessors**: `022` / `053` (4 cards); `054` (split Figma); `11079`/`11080` (regressão para 4 cards + ícones oportunidades)  
**Hook**: `custom_configs_update_11081`  
**Input**: Especificação em `specs/055-restaurar-beneficios-estudantes/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)

## Summary

Reestabelecer **in-place** a seção Benefícios em `/para-estudantes` (mesmo `beneficios_estudantes` / UUID `a8b9…` / placement `default_beneficiosestudantes`) como UI pública definitiva alinhada ao Figma: fundo `#eef5ff`, badge + **título bicolor**, subtítulo, **6** cards brancos em grade 2×3 (ícone à esquerda), CTA laranja “Cadastre-se gratuitamente” → `/cadastro/candidato`, e colagem circular estruturada (foto central + 5 satélites em órbita pontilhada) à direita no desktop. Seed/fill-if-empty + migração só do legado conhecido das 4 cards; recriar instância/placement se ausentes; `hook_update_N` `11081` + `drush cex` se displays mudarem; PRD §3.6 cirúrgico. Zero passos manuais em produção. Fora: banner, Hero, jornada, `block_3`, CTA final, outras rotas.

## Technical Context

| Item | Valor |
|------|--------|
| Language/Version | PHP 8.3+ / 8.4, Drupal 11, Twig 3, CSS3 |
| Primary Dependencies | Block Content `beneficios_estudantes`; paragraph `card_icon_text_p`; storages já instanciados `field_text_simple`, `field_text_simple_2`, `field_text_simple_long`, `field_itens_lista`, `field_link`, `field_image`; tema `default` (Bootstrap 5). **Sem** Composer novo |
| Storage | Sem field **storage** novo e sem instance nova (054 já exportou badge/CTA/imagem). UUID bloco `a8b9c0d1-e2f3-4456-a789-0bcdef123456`. Placement inalterado em machine name |
| Testing | Manual via [quickstart.md](quickstart.md); sem PHPUnit dedicado |
| Target Platform | `/para-estudantes`; breakpoints tema `046` (576 / 768 / 992) |
| Performance | Library `beneficios_estudantes` só no bloco; ícones e faces da colagem `loading="lazy"`; sem JS novo |
| Constraints | Sem `core/`/`vendor/`; sem SCSS toolchain; CSS isolado sob `.block-beneficios-estudantes`; não regressar banner/Hero/jornada/`block_3`/CTA final nem PE/QS/home |
| Scale/Scope | Twig bloco + paragraph + CSS; seed canônico 6 itens; hook `11081`; assets ícones + faces órbita; PRD §3.6 |

**Estado atual verificado (2026-11-03):**

- Último hook: **`custom_configs_update_11080`** → próximo livre **`11081`**.
- Bundle com instances: `field_text_simple`, `field_text_simple_2`, `field_text_simple_long`, `field_itens_lista`, `field_link`, `field_image` (já em `config/sync`).
- `_custom_configs_beneficios_estudantes_canonical_items()` retorna as **4** cards (`Vagas alinhadas` …) — regressão pós-`11079`.
- `_custom_configs_seed_beneficios_estudantes()` ainda contém copy/badge/CTA/colagem da **054**, mas monta paragraphs a partir do canônico de 4 itens → seed inconsistente com o Figma.
- Twig/CSS locais (working tree): layout split 054 parcial; **sem** fundo `#eef5ff`, **sem** título bicolor, **sem** cards brancos, colagem = 1 `<img>` composta (ocultada &lt;992).
- Assets: `icon-1`…`icon-6.png` + `collage.png` em `modules/custom/custom_configs/assets/beneficios-estudantes/`.
- Placement `default_beneficiosestudantes`: `content_full` weight `0`, `/para-estudantes` — **manter/reativar**.
- `mattpocock/skills`: não instalado; seguir libraries + CSS escopado do tema.
- PRD §3.6 ainda documenta UI pública 4 cards pós-`11079`.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist; zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só tema `default`, `custom_configs`, `config/sync` via cex se necessário, `PRD.md` |
| Reuso / sem storage paralelo | PASS | BT/PT/fields existentes; zero storage novo |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11081` + cex só se displays mudarem |
| Seeds Entity API / não sobrescrever editorial | PASS | fill-if-empty + só legado conhecido das 4 cards; recria se UUID ausente |
| Clean URLs | PASS | `/para-estudantes`; CTA seed `/cadastro/candidato` |
| Performance / CSS isolado | PASS | library existente; seletores sob `.block-beneficios-estudantes` |
| Contrib first / custom mínimo | PASS | sem módulo/tipo novo |
| Convivência 021–027 / 053 / 054 | PASS | só seção Benefícios; demais blocos intocados |
| PRD guardião | A atualizar na implementação | §3.6 — UI Figma restaurada + hook `11081` |
| Reinício cache | A executar na implementação | `drush cr` |

**Post-design**: gates mantidos. Trade-offs em [research.md](research.md) e Complexity Tracking. Sem violação injustificada.

## Architecture

```text
/para-estudantes
├─ banner / highlighted (Hero)     — FORA
├─ content_full
│   ├─ beneficios_estudantes (w0)  ← ESTA FEATURE (restaurar)
│   │    ├─ field_text_simple_2    badge
│   │    ├─ field_text_simple      título (Twig bicolor se canônico)
│   │    ├─ field_text_simple_long subtítulo
│   │    ├─ field_itens_lista      card_icon_text_p × 6
│   │    ├─ field_link             CTA
│   │    └─ field_image            foto central da colagem (+ satélites via assets seed)
│   ├─ jornada / block_3 / cta_v1  — FORA
hook 11081 — ensure structure/displays + restore seed 6 itens + recreate instance/placement + assets órbita
```

## Design Decisions

1. **In-place restore**: mesmo bundle/UUID/placement; sem segundo bloco (R1).
2. **Fields**: reusar instances já existentes (`field_text_simple_2`, `field_link`, `field_image`); zero storage/instance nova (R2).
3. **Itens = cards brancos**: Twig mantém linha ícone+texto; CSS aplica fundo branco, radius, gap ~16px; grade `col-12` (&lt;992) / `col-md-6` (≥768 desktop split) (R3).
4. **Título bicolor**: Twig quebra o título canônico em dois spans (`#07132E` / `#005B9F`); título editorial divergente = cor única (R4).
5. **Colagem estruturada**: marcação órbita (centro + 5 satélites + anel pontilhado CSS); `field_image` = foto central; satélites só via assets versionados (não CMS); fallback imagem composta se faces ausentes (R5).
6. **Responsivo**: ≥992 split; 576–991 conteúdo full + colagem abaixo redimensionada; &lt;576 omitir colagem; itens 1 coluna em estreito (R6).
7. **Seed canônico**: restaurar `_canonical_items()` para os 6 benefícios Figma; badge seed `Benefícios para Estudantes` (uppercase via CSS); migrar só vazio/legado 4 cards; título Figma da 054 também tratado como legado a preservar se já canônico (R7).
8. **Hook `11081`**: ensure structure; seed/migrate/recreate UUID; ensure placement ativo; sync assets; idempotente (R8).
9. **Origem**: `drush updb -y` → `drush cex -y` (se display mudar) → `drush cr`. **Destino**: `cim` → `updb` → `cim` → `cr`.
10. **PRD** §3.6 cirúrgico (layout Figma restaurado, 6 cards, CTA, órbita, `11081`).
11. **Fora**: SCSS; novo BT/PT; edição foto-a-foto no painel; remover CTA final; regressão outras seções.

## Project Structure

### Documentation (this feature)

```text
specs/055-restaurar-beneficios-estudantes/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── beneficios-estudantes-render.md
│   └── deploy-beneficios-estudantes.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/block/block--block-beneficios-estudantes.html.twig   # bicolor + órbita + CTA seta
  templates/paragraph/paragraph--card-icon-text-p.html.twig      # card branco (classes)
  assets/css/beneficios-estudantes.css                           # #eef5ff, cards, órbita, breakpoints
  # default.libraries.yml — library beneficios_estudantes já existe

modules/custom/custom_configs/
  custom_configs.install          # custom_configs_update_11081 + restore canonical 6 itens
  assets/beneficios-estudantes/   # icon-1…6 + collage-center / satélites (e collage.png fallback)

config/sync/   # só via drush cex se form/view display mudar (rótulos/ordem)
  # instances field_* já presentes — não inventar YAML à mão

PRD.md                            # §3.6 cirúrgico
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. `/speckit-tasks` → tasks ordenadas
3. Implementação: restore seed 6 itens → Twig/CSS (fundo, bicolor, cards, órbita, responsivo) → assets órbita → hook `11081` → `updb` → `cex` se preciso → PRD → `cr`
4. Validação [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Colagem estruturada (Twig+CSS) + satélites só em assets | FR-014 + Assumption; CMS foto-a-foto fora de escopo | Só `collage.png` opaca (054) — não comunica órbita |
| Título bicolor só quando copy = canônica | Evita partir título editorial no meio da frase | Sempre split por N caracteres (frágil) |
| Cards brancos nos itens (reintroduz chrome) | Spec/Figma 055 explícito; 054 tinha removido cards | Lista flat 054 (diverge do Figma atual) |
| Colagem visível 576–991 abaixo do texto | FR-005; 054 ocultava &lt;992 | Sempre ocultar &lt;992 (perda de brand art) |
| Hook reescreve set legado 4 cards → 6 | FR-002/FR-008; seed pós-11079 ≠ Figma | Overwrite sempre, ou deixar 4 cards |
| `mattpocock/skills` ausente | Padrões do repo cobrem Twig/CSS/hook | Bloquear feature |
