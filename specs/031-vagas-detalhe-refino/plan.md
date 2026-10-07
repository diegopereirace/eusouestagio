# Implementation Plan: Detalhe da Vaga — Layout Duas Colunas (Refino)

**Branch**: `feature-vaga` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup via `.specify/feature.json` → `specs/031-vagas-detalhe-refino`)  
**Date**: 2026-10-07  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/031-vagas-detalhe-refino/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)  
**Predecessor**: `030-vagas-detalhe-layout` (shell 8/4, FAQ 1:1, stepper/perfil/CTA inline — a refinar)

## Summary

Refinar o detalhe full do node `vagas`: manter grid duas colunas; migrar FAQ para **coleção** (`faq_item_p` + `field_faq_itens`, `field_vaga_faq` card. 1); converter `field_vaga_requisitos` para Text long formatted; manter benefícios (`beneficio_vaga_p`, pedido verbal `field_text_simple_small` → canônico `field_text_simple`); **remover** Match / Processo / “Por que combina” / Seu Perfil e o CTA inline do Twig; CTA final via instância `cta_v1` escura + placement com `entity_bundle:node` = `vagas`; Header global só smoke/regressão. Deploy: `custom_configs_update_11048` + `drush cex`; PRD §3.1/§3.6 cirúrgico. Listagem `/vagas` e cards laranja intocados.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3, Bootstrap 5.3 (Barrio)  
**Primary Dependencies**: Drupal core (Node, Field, Text, Image, Block); Paragraphs + Entity Reference Revisions; tema `default`; módulos `custom_configs`, `custom_panel`, `custom_candidaturas`. **Nenhuma dependência Composer nova**.  
**Storage**: PostgreSQL; tipos/fields/displays/placements em `config/sync`; seeds FAQ coleção + CTA via Entity API no hook `11048`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile &lt;992px / `lg+`)  
**Project Type**: Drupal theme + custom modules (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS em libraries dedicadas; accordion BS5; seções omitidas quando vazias; CTA isolado por suggestion Twig  
**Constraints**: sem `core/`/`vendor/`; **não** alterar Twigs/CSS da listagem `/vagas` nem cards laranja; sem Match/Processo/Perfil; deploy `cim` → `updb` → `cim` → `cr`; pt-BR  
**Scale/Scope**: migração FAQ 1:1→coleção; recreate storage requisitos; 1 paragraph FAQ; 1 CTA instância+placement; Twig/CSS detalhe; hook `11048`; PRD cirúrgico

**Estado atual verificado (2026-10-07):**

- Template canônico: `themes/custom/default/templates/content/node--vagas--full.html.twig` (`.vaga-detalhe`, col-lg-8 / col-lg-4) — contém stepper, perfil, CTA inline escuro; **sem** Match.
- CSS: library `default/vagas_detalhe` → `assets/css/components/vagas-detalhe.css`.
- Preprocess: `default_preprocess_node__vagas` → `vaga_salva` / `vaga_candidatada` / `vaga_perfil_completo` (75) / `vaga_postado_ha`.
- CT `faq`: existe — modelo 030 (title = pergunta, `field_resposta`); seeds UUIDs `…abc01` / `…abc02`; `field_vaga_faq` card. **-1**.
- `faq_item_p` / `field_faq_itens`: **inexistentes**.
- `field_vaga_requisitos`: storage `string` card. `-1` (não Text long).
- `beneficio_vaga_p`: existe (`field_image` + `field_text_simple`).
- `field_vaga_etapas_processo`: existe (sair do Twig; storage pode permanecer — YAGNI delete nesta feature).
- CTA vagas: markup Twig (não `cta_v1`). Padrão escuro isolado: `026` (`block--default-ctav1paraestudantes` + library dedicada). Visibilidade por bundle: `default_vagasvoltar` (`entity_bundle:node` → `vagas`).
- Rota pública de empresa: **não existe** → botão “Ver Empresa” permanece omitido (mesmo R7 da 030).
- Header: `block--default-top.html.twig` + `drupal_menu('main')` — sem redesign planejado; só regressão.
- Último hook: `custom_configs_update_11047`. Esta feature: **`11048`**.
- Pedido verbal `field_text_simple_small` → canônico `paragraph.field_text_simple` (padrão 004–026; storage `field_text_simple_small` **não** criar).

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storage | PASS | benefícios: `field_image`/`field_text_simple`; FAQ item: storages novos só onde FR exige machine name e entity type diferente |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11048` + `drush cex` |
| Clean URLs | PASS | canonical da vaga inalterada; FAQ coleção sem path público obrigatório |
| Performance / CSS isolado | PASS | `vagas_detalhe` + library CTA vagas; escopo por classe/ID |
| Contrib first | PASS | Node/Field/Paragraphs/Block/BS5; sem Composer novo |
| Sem dump / Entity API | PASS | ensures + seeds UUID no hook |
| Isolamento listagem / cards laranja / CTAs claros | PASS | só Twig full + CSS detalhe + CTA suggestion; Views/cards/QS/PE intocados |

**Post-design**: gates mantidos. Recreate de `field_vaga_requisitos` e storages `paragraph.field_pergunta` / `paragraph.field_resposta` justificados em Complexity Tracking. Sem violação injustificada.

## Design Decisions

1. **Template**: reescrever `node--vagas--full.html.twig` — remover seções Processo, Seu Perfil e bloco `__cta` inline; manter header / sobre / requisitos / benefícios / empresa / FAQ / sidebar (ações + resumo). Zero markup Match.
2. **CSS**: atualizar `vagas-detalhe.css` (remover stepper/perfil/CTA inline; ajustar requisitos para HTML formatado com checks via CSS em `ul`/`ol`/`p`). Library `vagas_detalhe` permanece.
3. **FAQ coleção**:
   - Paragraph `faq_item_p`: `field_pergunta` (string) + `field_resposta` (`text_long`) — storages **novos em `paragraph`** (FR-002; `node.field_resposta` não é reutilizável cross-entity).
   - No CT `faq`: `field_faq_itens` (ERR → `faq_item_p`, card. `-1`); `title` = nome da coleção (não mais a pergunta).
   - `field_resposta` no **node** `faq`: após migração, ocultar do form; não apagar storage nesta feature (evita quebra se cim/ordem falhar) — dados canônicos passam a ser os paragraphs.
4. **Seed FAQ**: **1** node coleção UUID fixo novo `c3d4e5f6-a7b8-4901-b234-56789abcdef0`, title “FAQ — Exemplo”, **2** itens com as perguntas do Figma; respostas placeholder pt-BR. Reexecução = load por UUID da coleção + contagem de itens (não duplicar). Seeds 030 (`…abc01`/`…abc02`): migrar itens para a coleção se ainda forem 1:1 órfãos; não recriar como nodes separados.
5. **`field_vaga_faq`**: cardinality **1**; handler só bundle `faq`. Migração: vagas com N refs → consolidar itens num único node coleção (reusar a 1ª ref se já for coleção; senão criar/atualizar coleção) e gravar 1 target; vagas sem ref ficam vazias (editor associa — YAGNI auto-link em massa).
6. **Requisitos → Text long**: Drupal não altera tipo de storage in-place. Hook `11048`: (a) ler valores string multi por vaga; (b) montar HTML `<ul><li>…</li></ul>` (escape); (c) remover instance + storage `string` `field_vaga_requisitos`; (d) recreate storage `text_long` card. 1 + instance + displays; (e) gravar valor migrado só se campo novo vazio. Formato texto padrão do site (`basic_html` ou equivalente já usado em `field_text_long_formatted`).
7. **Benefícios**: manter `beneficio_vaga_p` + `field_image` + `field_text_simple` (mapeamento `field_text_simple_small` → canônico). Sem storage paralelo.
8. **Etapas / perfil**: remover do Twig/CSS/preprocess (`vaga_perfil_completo` pode ficar no theme sem uso ou ser removido se só servir ao card). Storage `field_vaga_etapas_processo` **permanece** no sync (não deletar nesta feature — evita perda editorial; só some da UI full).
9. **Ver Empresa**: **omitir** botão/link — sem rota pública (igual 030). Card empresa com logo/nome/`field_sobre_empresa` truncado permanece.
10. **CTA `cta_v1` vagas**:
    - Instância UUID `d4e5f6a7-b8c9-4012-c345-6789abcdef01`; copy: título “Pronto para o próximo passo?”; corpo convidativo; primário “Candidatar-se Agora” → destino documentado (preferência: `#` âncora/`internal:/user/login` com destination **ou** JS reuso — ver research R8: link para login se anônimo; se candidato, preferir scroll/foco no botão sidebar via `#vaga-acoes` **ou** mesmo fluxo `js-candidatar-vaga` se o markup do bloco permitir botão custom — decisão: **link primário `internal:/cadastro/candidato`** para anônimo/conversão genérica alinhada a outros CTAs; candidato autenticado já tem sidebar — aceitável por FR-018 copy Figma).
    - Placement `default_ctav1vagas`: região **`content_full`**, weight alto (ex. `10`), `label_display: '0'`, visibility `entity_bundle:node` bundles `vagas` (padrão `default_vagasvoltar`). Em páginas de vaga, `content_full` renderiza **após** `page.content` (não é `/para-estudantes`).
    - Isolamento: Twig `block--default-ctav1vagas.html.twig` + library `default/cta_v1_vagas` → `cta-v1-vagas.css` (fundo `#023C62`); **não** alterar `cta-v1.css` nem Twig global claro; **não** alterar CSS/Twig do CTA PE estudantes.
11. **Header (FR-020)**: sem mudança estrutural planejada; checklist smoke home + detalhe vaga (desktop/mobile). Só corrigir regressão se o refino quebrar sticky/menu.
12. **Hook `11048`**: ensure paragraph FAQ + fields/displays; ensure `field_faq_itens` + displays FAQ; migrar FAQ 1:1→itens; seed coleção; ajustar card. `field_vaga_faq` → 1 + migrar refs; recreate requisitos text_long + migrar; seed CTA + ensure placement; **idempotente**; não sobrescrever editorial divergente.
13. **PRD**: §3.1 (FAQ coleção, requisitos text_long, campos vagas) + §3.6 (instância CTA vagas / placement / library); mencionar hook `11048`.
14. **Fora**: listagem `/vagas`; cards laranja; fluxos novos de candidatura; motor match; FAQ institucional; rota pública de empresa.

## Project Structure

### Documentation (this feature)

```text
specs/031-vagas-detalhe-refino/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── vagas-detalhe-refino-render.md
│   └── deploy-vagas-detalhe-refino.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/content/node--vagas--full.html.twig     # refino: remover processo/perfil/CTA inline; FAQ coleção
  templates/paragraph/paragraph--faq-item-p.html.twig  # opcional (accordion pode ser no node)
  templates/block/block--default-ctav1vagas.html.twig  # NOVO — CTA escuro
  assets/css/components/vagas-detalhe.css           # ajustes
  assets/css/cta-v1-vagas.css                       # NOVO
  default.libraries.yml                             # + cta_v1_vagas
  default.theme                                     # limpar preprocess perfil se órfão

modules/custom/custom_configs/
  custom_configs.install   # custom_configs_update_11048 + helpers

config/sync/  # via drush cex (não inventar à mão)
  paragraphs.paragraphs_type.faq_item_p.yml
  field.storage.paragraph.field_pergunta.yml
  field.storage.paragraph.field_resposta.yml
  field.field.paragraph.faq_item_p.*
  core.entity_*_display.paragraph.faq_item_p.default.yml
  field.storage.node.field_faq_itens.yml
  field.field.node.faq.field_faq_itens.yml
  field.storage.node.field_vaga_requisitos.yml      # type text_long card. 1
  field.storage.node.field_vaga_faq.yml             # cardinality 1
  field.field.node.vagas.field_vaga_* / displays
  core.entity_form_display.node.faq.default.yml
  block.block.default_ctav1vagas.yml

PRD.md   # §3.1 + §3.6 cirúrgico

# NÃO alterar (regressão):
#   views-view-field--vagas--*--nothing.html.twig
#   vagas-lista-vertical.css / vagas-hero-search
#   block--block-cta-v1.html.twig / cta-v1.css
#   block--default-ctav1paraestudantes* / cta-v1-para-estudantes.css
#   CTAs QS / PE empresas
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. Paragraph `faq_item_p` + `field_faq_itens` + displays FAQ + migração 1:1→coleção + seed 1×2
3. Recreate `field_vaga_requisitos` text_long + card. `field_vaga_faq` = 1 + migração refs
4. Twig full refinado (sem processo/perfil/CTA inline) + CSS
5. Seed CTA `cta_v1` + placement `default_ctav1vagas` + Twig/CSS isolados
6. Origem: `drush cex` → versionar configs
7. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Storages novos `paragraph.field_pergunta` / `paragraph.field_resposta` | FR-002 machine names; storages de `node` não compartilham entity type | Reusar `field_text_simple` / `field_text_simple_long` com labels — nome diverge do FR e do glossário |
| Recreate storage `field_vaga_requisitos` (string→text_long) | FR-005 exige Text long formatted; Drupal não muda tipo in-place | Manter multi-string — viola FR; novo machine name — viola contrato |
| Manter `field_vaga_etapas_processo` no schema sem UI | Evita purge destrutivo; seção só sai do Twig | Deletar field/storage agora — risco desnecessário nesta feature |
| Omitir “Ver Empresa” | Sem rota pública de empresa | Link painel empresa — 403; criar rota pública — fora de escopo |
| CTA link genérico vs JS candidatar no bloco | Bloco `cta_v1` é Link field; sidebar preserva fluxo real de candidatura | Embutir JS candidatar no block Twig — acopla bloco a node context de forma frágil |
| Pedido `field_text_simple_small` → `field_text_simple` | Padrão canônico do projeto (004–026); storage paralelo proibido | Criar `field_text_simple_small` — viola reuso |
