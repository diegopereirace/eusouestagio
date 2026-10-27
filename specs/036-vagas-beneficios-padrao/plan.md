# Implementation Plan: Benefícios Padrão em Todas as Vagas

**Branch**: `036-vagas-beneficios-padrao`
**Date**: 2026-10-27
**Spec**: [spec.md](spec.md) | **Checklist**: [checklists/requirements.md](checklists/requirements.md)
**Predecessor**: `030-vagas-detalhe-layout` / `031-vagas-detalhe-refino`
**Hook**: `custom_configs_update_11056`

## Summary

Garantir que toda vaga publicada exiba a seção **Benefícios** com os quatro itens padrão do Figma (Plano de Carreira, Vale Transporte, Vale Refeição, Horário Flexível), sem sobrescrever conteúdo editorial. Reuso do modelo existente (`paragraph beneficio_vaga_p` com `field_image` + `field_text_simple`, referenciado por `field_vaga_beneficios` no CT `vagas`). Três frentes:

1. **Backfill idempotente** (`hook_update_11056`): para cada nó `vagas` com `field_vaga_beneficios` vazio, criar 4 paragraphs `beneficio_vaga_p` (ícone + título) a partir de assets versionados em `modules/custom/custom_configs/assets/beneficios/beneficios-0{1..4}.png` e anexá-los. Vagas já preenchidas (mesmo parcialmente) não são tocadas.
2. **Obrigatoriedade do campo**: após backfill bem-sucedido, tornar `field_vaga_beneficios` obrigatório via Entity API (`field_config` storage, `required: TRUE`) no próprio hook e consolidar com `drush cex` na origem — `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` passa a `required: true`. Em destino, `cim` aplica a obrigatoriedade; `updb` faz o backfill. Ordem segura: `cim` → `updb` → `cim` → `cr`.
3. **Alinhamento visual Figma**: ajuste cirúrgico no CSS existente (`.vaga-detalhe__beneficio-card` / `-icon` / `-title` em `vagas-detalhe.css`) — ícone ~48×48 sem distorção, padding generoso, texto semibold em `--vd-navy`. Grade 2×2 (mobile) / 4 colunas (desktop) já entregue por `col-6 col-lg-3` no Twig. Bump do `width/height` do `<img>` no Twig de 32 para 48.

A falha explícita de asset ausente (Edge Case) é tratada no hook: se algum dos quatro PNGs não for legível, o hook **aborta** o backfill com mensagem clara e **não** flipa `required: TRUE` — evita estado inconsistente (campo obrigatório sem backfill concluído).

## Technical Context

**Language/Version**: PHP 8.4, Drupal 11.4+, Twig 3, CSS3 (Bootstrap 5.3 via `bootstrap_barrio`).
**Primary Dependencies**: Drupal core (`node`, `field`, `paragraphs`, `image`, `file`, `entity_reference_revisions`); módulo custom `custom_configs` (ativo); tema `default`. **Nenhuma dependência Composer nova.**
**Storage**: reuso do modelo existente — `paragraph beneficio_vaga_p` (ícone `field_image` + título `field_text_simple`) referenciado por `field_vaga_beneficios` (ERR) no CT `vagas`. Nenhum storage novo. Única alteração estrutural: `required: false → true` em `field.field.node.vagas.field_vaga_beneficios.yml`.
**Testing**: validação manual via [quickstart.md](quickstart.md); sem suite PHPUnit dedicada (padrão do projeto para backfill + apresentação).
**Target Platform**: site público Drupal, detalhe da vaga (`/vaga/<alias>`), desktop + mobile (breakpoint Bootstrap `lg` 992px para 4 colunas).
**Project Type**: módulo custom (`modules/custom/custom_configs`) + tema (`themes/custom/default`) + config exportada (`config/sync`).
**Performance Goals**: backfill em lote por `loadByProperties(['type' => 'vagas'])` + `foreach` com `save()` por nó alterado — mesmo padrão de `11054/11055`. Para catálogos típicos (dezenas a centenas de vagas) roda em segundos, sem estourar memória (FR-008). Reexecução é no-op.
**Constraints**: sem `core/`/`vendor/`; sem storage/paragraph type novo (reuso 030/031); sem sobrescrever benefícios editoriais (FR-004); sem passo manual no admin; deploy 100% automatizado; pt-BR.

**Estado atual verificado (2026-10-27):**

- `paragraphs.paragraphs_type.beneficio_vaga_p.yml` existe (UUID `c34b3601-6603-4106-b2f4-4eb3103f42ca`), com `field_image` (ícone) + `field_text_simple` (título, já required no paragraph).
- `field.field.node.vagas.field_vaga_beneficios.yml`: `required: false`, handler `default:paragraph`, target_bundles `beneficio_vaga_p`.
- `field.field.paragraph.beneficio_vaga_p.field_image.yml`: `file_directory: 'paragraph/beneficio-vaga/[date:custom:Y]-[date:custom:m]'`, extensões `png gif jpg jpeg webp svg`, `alt_field: true`, `alt_field_required: false`.
- `field.field.paragraph.beneficio_vaga_p.field_text_simple.yml`: `required: true`.
- Twig `node--vagas--full.html.twig` (linhas 134–165) já renderiza a seção condicionalmente (`has_beneficios`) em `col-6 col-lg-3` (2 cols mobile / 4 desktop) com card `.vaga-detalhe__beneficio-card`, ícone 32×32 fallback `fa-gift`, título `.vaga-detalhe__beneficio-title`. Twig `paragraph--beneficio-vaga-p.html.twig` espelha o mesmo markup.
- CSS `themes/custom/default/assets/css/components/vagas-detalhe.css` (linhas 202–238): card branco, borda `--vd-border`, `border-radius: 12px`, padding `1rem 0.75rem`, ícone `2.5rem` (40px) com `max-width/height: 32px`, título `0.9rem` semibold `--vd-text`. **Ajuste Figma**: bump de ícone para ~48px e título em `--vd-navy` (FR-009).
- Helper `_custom_configs_seed_file_from_module_asset()` (linhas 2463–2505 do `.install`) já existe e é idempotente: copia asset → `public://`, reusa managed file por URI, marca `permanent`, registra `file.usage`.
- Padrão de backfill em `vagas` já estabelecido por `custom_configs_update_11054` e `11055`.
- Último hook: `custom_configs_update_11055`. Próximo número livre: **`11056`**.
- Pasta de assets do módulo: `modules/custom/custom_configs/assets/<feature>/` — convenção confirmada por `beneficios-estudantes/icon-1.png`, `diferenciais-quem-somos/icon-N.png`, `vaga-header-empresa/logo-ecoconstrutora.png`. Feature 036 usa `modules/custom/custom_configs/assets/beneficios/beneficios-0{1..4}.png`.

## Constitution Check

Não há `.specify/memory/constitution.md`. Gates equivalentes: `.cursor/rules/estagio-*.mdc` + `drupal-deploy-configs.mdc` + `estagio-fluxo-dev.mdc` + PRD.

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — spec antes do código | PASS | `spec.md` + `checklists/requirements.md` OK, zero `[NEEDS CLARIFICATION]` |
| Sem `core/` / `vendor/` | PASS | só `modules/custom/custom_configs` + `themes/custom/default` + `config/sync` |
| Reuso de storage (`estagio-fluxo-dev`) | PASS | reuso de `beneficio_vaga_p` + `field_image` + `field_text_simple` + `field_vaga_beneficios`; nenhum storage novo |
| Deploy de config estrutural (`drupal-deploy-configs.mdc`) | PASS | hook `11056` idempotente (backfill + flip `required` via Entity API) + `drush cex` na origem |
| Seeds via Entity API (não YAML editado à mão) | PASS | backfill usa `entityTypeManager()` + `Paragraph::create()` + helper de file; flip via `field_config` storage |
| Idempotência (reexecução = no-op) | PASS | hook checa `isEmpty()` antes de preencher; reexecução não duplica; flip `required: TRUE` é no-op se já true |
| Não sobrescrever editorial divergente | PASS | só preenche `field_vaga_beneficios` quando vazio; coleções parciais permanecem (Edge Case) |
| Assets versionados, sem commit de `public://` | PASS | ícones em `modules/custom/custom_configs/assets/beneficios/`; hook copia para `public://beneficios/` |
| Ordem do deploy (backfill antes de obrigatoriedade) | PASS | dentro do hook: backfill primeiro; só após sucesso (4 ícones legíveis) flipa `required: TRUE`; asset ausente aborta sem flipar |
| Sem passo manual no admin | PASS | backfill + flip + export automatizados; receita destino `cim` → `updb` → `cim` → `cr` |
| PRD guardião (`estagio-prd-guardian.mdc`) | A atualizar na implementação | mudança estrutural (flip `required`) — atualizar §3.1 (campos da vaga) cirurgicamente |
| Reinício de cache (`estagio-reiniciar-servicos.mdc`) | A executar na implementação | `drush cr` após `updb` + `cim` |

**Post-design**: nenhuma violação. Único trade-off (bump de ícone 32→48px) é puramente de apresentação, documentado em [research.md](research.md) R3.

## Design Decisions

1. **Reuso do modelo existente** (não criar storage novo): `paragraph beneficio_vaga_p` + `field_vaga_beneficios` já existem (030/031). A feature só popula dados e flipa `required`. Reforça FR-011 e a regra de reuso de storage.
2. **Backfill via `hook_update_11056`** (não via migration/plugin): segue o padrão de `11054/11055` para o CT `vagas` — `loadByProperties` + `foreach` + `isEmpty()` + `set()` + `save()`. Idempotente, sem dependência de migration.
3. **Ícones como managed files via helper existente**: `_custom_configs_seed_file_from_module_asset('beneficios/beneficios-01.png', 'public://beneficios/beneficios-01.png', 'paragraph', $paragraph->id())` — reutiliza o helper que já trata directory creation, reuse por URI, `setPermanent`, `file.usage`. Cada paragraph `beneficio_vaga_p` recebe seu ícone via `field_image` (target_id + alt).
4. **Sequência no hook**: (a) checar legibilidade dos 4 assets antes de qualquer escrita — se faltar algum, abortar com `t()` e **não** flipar `required`; (b) iterar vagas, para cada uma com `field_vaga_beneficios` vazio, criar 4 paragraphs + anexar + `save()`; (c) após backfill, carregar `field_config` de `node.vagas.field_vaga_beneficios` e setar `required: TRUE` + `save()` (no-op se já true); (d) mensagem `t()` com contagem (atualizadas / já ok / total). Garante FR-005 e o Edge Case de asset ausente.
5. **Flip de `required` via Entity API + `drush cex`**: o hook altera a config em runtime para que a regra entre em vigor imediatamente no destino; `drush cex` na origem consolida em `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` (`required: true`). Reforça FR-010. Não editar o YAML à mão.
6. **Ajuste visual isolado em `vagas-detalhe.css`**: bump de `max-width/max-height` do `<img>` de 32px para 48px (e `width/height` do Twig de 32 para 48), padding do card para `1.25rem 1rem`, título em `var(--vd-navy)`. Sem novos seletores; sem JS; sem tocar outros seletores da seção. Reforça FR-009.
7. **Twig inalterado na estrutura**: o markup `col-6 col-lg-3` + card + ícone + título já atende o layout Figma. Único ajuste: atributos `width="48" height="48"` no `<img>` (em ambos os templates `node--vagas--full.html.twig` e `paragraph--beneficio-vaga-p.html.twig`) para hint de layout sem reflow.
8. **Fora**: criar novo paragraph type ou field storage; alterar benefícios editoriais; redesenhar outras seções do detalhe da vaga; listagem `/vagas`, cards de home/para-estudantes; benefícios de outras landings (`022`, PE); upload manual obrigatório.

## Project Structure

### Documentation (this feature)

```text
specs/036-vagas-beneficios-padrao/
├── spec.md
├── checklists/requirements.md
├── plan.md                 # este arquivo
├── research.md
├── data-model.md
├── contracts/
│   ├── deploy-beneficios-padrao.md
│   └── vaga-beneficios-render.md
└── quickstart.md
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_configs/
  custom_configs.install                          # + custom_configs_update_11056 + helper _custom_configs_backfill_vaga_beneficios
  assets/beneficios/beneficios-01.png             # novo (Plano de Carreira)
  assets/beneficios/beneficios-02.png             # novo (Vale Transporte)
  assets/beneficios/beneficios-03.png             # novo (Vale Refeição)
  assets/beneficios/beneficios-04.png             # novo (Horário Flexível)

themes/custom/default/
  assets/css/components/vagas-detalhe.css         # ajuste .vaga-detalhe__beneficio-icon img + -title + padding
  templates/content/node--vagas--full.html.twig   # width/height 48 no <img>
  templates/paragraph/paragraph--beneficio-vaga-p.html.twig  # width/height 48 no <img>

config/sync/
  field.field.node.vagas.field_vaga_beneficios.yml  # required: false → true (via drush cex na origem)
```

## Phases

1. Spec/plan/research/data-model/contract/quickstart (esta entrega)
2. `hook_update_11056` + helper em `custom_configs.install` (backfill idempotente + flip `required`)
3. Assets `beneficios-0{1..4}.png` em `modules/custom/custom_configs/assets/beneficios/`
4. Ajuste CSS em `vagas-detalhe.css` (ícone 48px, padding, título `--vd-navy`)
5. Ajuste `width/height` dos `<img>` nos dois templates Twig
6. Origem: `drush updb -y` (roda `11056`) → `drush cex -y` (consolida `required: true`) → `drush cr`
7. Validação via [quickstart.md](quickstart.md)

## Complexity Tracking

| Violação / trade-off | Justificativa | Alternativa rejeitada |
|-----------------------|----------------|------------------------|
| Bump de ícone 32→48px no CSS/Twig | FR-009 exige ~48×48 conforme Figma; ajuste é só numérico no seletor existente | Manter 32px (rejeitado: descumpre FR-009); criar novo seletor `.vaga-detalhe__beneficio-icon--lg` (rejeitado: inflaria CSS sem ganho) |
| Flip de `required` via Entity API no hook + `drush cex` | Garante que a obrigatoriedade entra em vigor no destino mesmo se o `cim` falhar parcialmente; `cex` consolida a config versionada | Editar o YAML de `config/sync` à mão (rejeitado: vedado por `estagio-fluxo-dev`); deixar o flip só no YAML (rejeitado: se o `cim` falhar, o `updb` não fliparia e o backfill ficaria sem obrigatoriedade) |
| Backfill via `hook_update_N` em vez de migration | Padrão já estabelecido em `11054/11055` para o CT `vagas`; sem dependência de módulo de migration; idempotente via `isEmpty()` | Migration Drupal (rejeitado: overhead de configuração para um backfill pontual; exige `migrate_plus` e mapeamento YAML) |
