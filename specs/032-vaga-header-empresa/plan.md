# Implementation Plan: Cabeçalho do Detalhe da Vaga — Perfil Empresa

**Branch**: `feature-vaga` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo; setup via `.specify/feature.json` → `specs/032-vaga-header-empresa`)  
**Date**: 2026-10-07  
**Spec**: [spec.md](spec.md)  
**Input**: Especificação em `specs/032-vaga-header-empresa/spec.md` (checklist OK; zero `[NEEDS CLARIFICATION]`)  
**Predecessor**: `031-vagas-detalhe-refino` (layout duas colunas; header ainda lê empresa via usuário `field_empresa_u`)

## Summary

Refatorar o **Header** do detalhe full do node `vagas`: Content Type público `empresa` (título = nome, logo = `field_imagem`); na vaga, `field_vaga_empresa` (ER card. 1) + `field_vaga_carga_horaria` (texto da pill); render Logo 96×96 + h1 + empresa com badge verificado + pin/localização + quatro pills (regime, carga, bolsa, “Postado há…”); seed EcoConstrutora + Engenheiro Civil; hook `custom_configs_update_11049` + `drush cex`; PRD §3.1 cirúrgico. Seção “Sobre a Empresa”, sidebar e demais blocos do `031` permanecem; `field_empresa_u` **não** é removido.

## Technical Context

**Language/Version**: PHP 8.3+, Drupal 11.4+, Twig 3, CSS3, Bootstrap 5.3 (Barrio)  
**Primary Dependencies**: Drupal core (Node, Field, Image, Text, Options/Taxonomy para regime); tema `default`; módulo `custom_configs`. **Nenhuma dependência Composer nova**.  
**Storage**: PostgreSQL; CT/fields/displays em `config/sync`; seeds empresa+vaga via Entity API no hook `11049`  
**Testing**: validação manual + [quickstart.md](quickstart.md); sem suite PHPUnit dedicada  
**Target Platform**: site público Drupal (mobile ≤576px / desktop)  
**Project Type**: Drupal theme + custom modules (`themes/custom/default`, `modules/custom/custom_configs`)  
**Performance Goals**: CSS no library `vagas_detalhe` existente; omitir logo/pills/local vazios; sem assets remotos novos  
**Constraints**: sem `core/`/`vendor/`; não alterar listagem `/vagas` nem cards laranja; sem migração em massa `field_empresa_u`→node; sem rota pública de empresa; deploy `cim` → `updb` → `cim` → `cr`; pt-BR  
**Scale/Scope**: 1 CT novo; 2 fields novos na vaga; Twig/CSS do header; preprocess tempo relativo pt-BR; 1 hook `11049`; 2 nodes seed; PRD cirúrgico

**Estado atual verificado (2026-10-07):**

- Template: `themes/custom/default/templates/content/node--vagas--full.html.twig` — header usa `node.field_empresa_u` (user) + `user_picture`; pills: `field_regime_t`, `field_horarios` (Manhã/Tarde/Noite — **não** é carga “30h”), `field_text_simple` (bolsa), `vaga_postado_ha`.
- CSS: library `default/vagas_detalhe` → `vagas-detalhe.css` — logo 72px, header sem card branco/borda; badges já pill-shaped (`#eef4f8`).
- Preprocess: `default_preprocess_node__vagas` → `formatTimeDiffSince()` (formato Drupal genérico; FR-008 exige pt-BR explícito hora/dia).
- CT `empresa` (node): **inexistente**. Role `empresa` (user) **existe** — machine names distintos por entity type.
- Storage `node.field_imagem`: **existe** (contato, depoimento, quem_somos) — reutilizar no CT Empresa.
- `field_vaga_empresa` / `field_vaga_carga_horaria`: **inexistentes**.
- `field_empresa_u`, `field_cidade`, `field_estados`, `field_regime_t`, `field_text_simple`: existem no bundle `vagas`.
- Último hook: `custom_configs_update_11048`. Esta feature: **`11049`**.
- Rota pública de empresa: **não existe** (inalterado — sem “Ver Empresa”).

## Constitution Check

Não há `.specify/memory/constitution.md` neste repositório. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + PRD + `estagio-fluxo-dev.mdc` + `drupal-deploy-configs.mdc`.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + checklist OK |
| Sem `core/` / `vendor/` | PASS | tema, `custom_configs`, `config/sync`, `PRD.md` |
| Reuso de field storage | PASS | logo via `node.field_imagem`; carga horária exige storage novo (sem string livre reutilizável com machine name do FR) |
| Deploy `cim` → `updb` → `cim` → `cr` + hook idempotente | PASS | `11049` + `drush cex` |
| Clean URLs | PASS | canonical da vaga inalterada; CT empresa sem path público obrigatório nesta feature |
| Performance / CSS isolado | PASS | ajustes sob `.vaga-detalhe__header*` no `vagas_detalhe` |
| Contrib first | PASS | Node/Field/Image; sem Composer novo |
| Sem dump / Entity API | PASS | ensures + seeds UUID no hook |
| Isolamento listagem / cards laranja / seções 031 | PASS | só header Twig/CSS + modelo; Sobre Empresa / sidebar / FAQ / CTA intactos |

**Post-design**: gates mantidos. Storages novos `field_vaga_empresa` / `field_vaga_carga_horaria` justificados em Complexity Tracking. Sem violação injustificada.

## Design Decisions

1. **CT `empresa`**: node type novo; `title` = nome público; instance `field_imagem` (logo, card. 1); form/view displays mínimos; sem pathauto obrigatório nesta entrega.
2. **`field_vaga_empresa`**: storage novo `entity_reference` → `node`, handler bundles `empresa`, card. 1, no bundle `vagas`. Header **só** lê este campo (publicado + accessível); se vazio/inacessível → omite logo/nome (fallback neutro opcional “Anônima” só se já usado; preferir omitir nome quando sem empresa node — alinhar edge case: omitir).
3. **`field_vaga_carga_horaria`**: storage novo `string` card. 1; texto livre editorial (ex. “30h semanais”). **Não** reutilizar `field_horarios` (list Manhã/Tarde/Noite) nem `field_text_simple` (bolsa).
4. **Pills do header**: (1) regime `field_regime_t` label; (2) carga `field_vaga_carga_horaria`; (3) bolsa `field_text_simple`; (4) “Postado há…” via preprocess. Remover `field_horarios` do header (permanece no form/sidebar se já usado no resumo).
5. **Twig — split de variáveis**: `empresa_node` = `field_vaga_empresa.entity` (header); manter `empresa` / `field_empresa_u` para seção “Sobre a Empresa” (legado 031) até feature de migração — evita quebrar sobre/logo do user.
6. **Visual header (FR-009)**: card `.vaga-detalhe__header` com fundo `#fff`, border `#E5E7EB` (ou token próximo), `border-radius: 16px`, padding generoso; logo 96×96 `object-fit: cover`, radius 12px; subtítulo numa linha: nome + ícone verificado (FA `fa-circle-check` ou equivalente) + `•` + pin + cidade/estado; título semibold; pills azul muito claro, `border-radius: 999px` (já próximo — unificar estilo, retirar variante laranja da bolsa **no header** se Figma unifica — ver research R6).
7. **Tempo relativo pt-BR**: substituir/`wrap` `formatTimeDiffSince` por helper no preprocess: &lt;1h → “menos de 1 hora”; &lt;24h → “X hora(s)”; ≥1d → “X dia(s)”; string completa “Postado há {…}” ou manter `|t` com `@time`.
8. **Badge verificado**: markup fixo sempre que `empresa_node` existir (Assumption); sem field CMS.
9. **Seed**: empresa UUID fixo + logo asset em `modules/custom/custom_configs/assets/empresa-seed/` (ou `vaga-header-empresa/`); vaga “Engenheiro Civil” UUID fixo com vínculo, cidade/estado, regime, carga “30h semanais”, bolsa, publicados. Idempotente por UUID; não sobrescrever editorial divergente.
10. **Hook `11049`**: ensure CT + instances + displays; ensure fields na vaga + form/view displays; seed empresa+vaga+arquivo; **não** migrar vagas existentes de `field_empresa_u`.
11. **PRD**: §3.1 — documentar CT `empresa` e campos novos em `vagas`; mencionar hook `11049` e convivência com `field_empresa_u`.
12. **Fora**: página/listagem pública empresa; migração em massa; remoção `field_empresa_u`; redesign seções não-header; listagem `/vagas`.

## Project Structure

### Documentation (this feature)

```text
specs/032-vaga-header-empresa/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── vaga-header-empresa-render.md
│   └── deploy-vaga-header-empresa.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
themes/custom/default/
  templates/content/node--vagas--full.html.twig   # header: empresa node + pills + verificado
  assets/css/components/vagas-detalhe.css         # card header, logo 96, subtítulo inline
  default.theme                                   # vaga_postado_ha pt-BR (hora/dia)

modules/custom/custom_configs/
  custom_configs.install   # custom_configs_update_11049 + helpers
  assets/vaga-header-empresa/logo-ecoconstrutora.png  # (ou .svg/.jpg) placeholder seed

config/sync/  # via drush cex (não inventar à mão)
  node.type.empresa.yml
  field.field.node.empresa.field_imagem.yml
  core.entity_*_display.node.empresa.default.yml
  field.storage.node.field_vaga_empresa.yml
  field.storage.node.field_vaga_carga_horaria.yml
  field.field.node.vagas.field_vaga_empresa.yml
  field.field.node.vagas.field_vaga_carga_horaria.yml
  core.entity_form_display.node.vagas.default.yml
  core.entity_view_display.node.vagas.default.yml
  (user.role.* / node permissions se alterados)

PRD.md   # §3.1 cirúrgico

# NÃO alterar (regressão):
#   views-view-field--vagas--* (listagem / cards)
#   field_empresa_u storage/instance
#   CTA / FAQ / requisitos / benefícios (salvo variáveis compartilhadas no Twig)
```

## Phases

1. Spec/plan/research/data-model/contracts/quickstart (esta entrega)
2. CT `empresa` + `field_imagem` + displays + permissões mínimas
3. Fields `field_vaga_empresa` + `field_vaga_carga_horaria` + displays vaga
4. Twig header + CSS card/logo/subtítulo + preprocess tempo pt-BR
5. Seed asset + hook `11049` idempotente
6. Origem: `drush cex` → versionar configs
7. PRD + validação quickstart (`cim` → `updb` → `cim` → `cr`)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|----------------------|---------------|------------------------|
| Storage novo `field_vaga_empresa` | FR-002 machine name; ER dedicado a node `empresa` | Reusar `field_empresa_u` (target user) — viola modelo público |
| Storage novo `field_vaga_carga_horaria` | FR-003 texto “30h semanais”; `field_horarios` é turno; `field_text_simple` = bolsa | Reusar `field_horarios` — semântica errada |
| Dual path empresa no Twig (node header / user “Sobre”) | Spec: header prioriza node; migração em massa fora; `field_empresa_u` permanece | Forçar “Sobre” a ler node sem campo `sobre` no CT — perda de copy; migrar user→node agora — fora de escopo |
| CT machine name `empresa` vs role `empresa` | Spec/FR; entity types diferentes | Renomear para `empresa_perfil` — diverge do FR |
| Custom relative time vs `formatTimeDiffSince` | FR-008 pluralização pt-BR e &lt;1h | Manter formatter Drupal — strings inconsistentes |
