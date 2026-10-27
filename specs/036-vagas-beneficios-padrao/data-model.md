# Data Model: 036-vagas-beneficios-padrao

## Resumo

Nenhum storage novo é criado. A feature reutiliza o modelo de dados já existente (features 030/031):

- **Paragraph type** `beneficio_vaga_p` (UUID `c34b3601-6603-4106-b2f4-4eb3103f42ca`)
- **Field instance** `field.field.paragraph.beneficio_vaga_p.field_image` (ícone, image, opcional)
- **Field instance** `field.field.paragraph.beneficio_vaga_p.field_text_simple` (título, string, required)
- **Field instance** `field.field.node.vagas.field_vaga_beneficios` (ERR → `beneficio_vaga_p`, ilimitado, **atualmente `required: false`** → **após feature: `required: true`**)

## CT `vagas` — alteração

| Campo | Storage | Antes | Depois |
|-------|---------|-------|--------|
| `field_vaga_beneficios` | ERR → `beneficio_vaga_p` | `required: false` | `required: true` |

A alteração é feita via Entity API no `hook_update_11056` (`field_config` storage, `set('required', TRUE)->save()`) e consolidada em `config/sync/field.field.node.vagas.field_vaga_beneficios.yml` via `drush cex` na origem.

## Seed de backfill (4 paragraphs por vaga vazia)

Para cada nó `vagas` com `field_vaga_beneficios` vazio, o hook cria 4 paragraphs `beneficio_vaga_p` nesta ordem:

| # | Título (`field_text_simple`) | Ícone (`field_image`) | Asset versionado |
|---|------------------------------|----------------------|------------------|
| 1 | Plano de Carreira | mala de couro | `modules/custom/custom_configs/assets/beneficios/beneficios-01.png` |
| 2 | Vale Transporte | maleta azul | `modules/custom/custom_configs/assets/beneficios/beneficios-02.png` |
| 3 | Vale Refeição | talheres | `modules/custom/custom_configs/assets/beneficios/beneficios-03.png` |
| 4 | Horário Flexível | relógio | `modules/custom/custom_configs/assets/beneficios/beneficios-04.png` |

Cada ícone é copiado para `public://beneficios/beneficios-0N.png` via `_custom_configs_seed_file_from_module_asset()` (helper existente em `custom_configs.install` linhas 2463–2505), que reusa managed file por URI, marca `permanent` e registra `file.usage` em `paragraph`.

## Estado / transições

- **Vaga sem benefícios** (antes do deploy) → **Vaga com 4 benefícios padrão** (após `updb`).
- **Vaga com benefícios editoriais** (qualquer quantidade) → **inalterada** (Edge Case: coleção parcial não recebe complemento).
- **Vaga em rascunho ou não publicada** com campo vazio → também recebe o padrão (todas as vagas do tipo, qualquer status).
- **Reexecução do hook** → no-op: `isEmpty()` falso para as já preenchidas; `required: TRUE` já é true; managed files reusados por URI.

## Validação

- `field_text_simple` do paragraph já é `required: true` (cada card tem título).
- `field_image` é opcional no storage, mas o seed sempre anexa um ícone (FR-006); se o asset ausente, o hook aborta antes de criar paragraphs (Edge Case).
- Após o flip, `field_vaga_beneficios` é `required: true` no form — Drupal valida que ao menos 1 paragraph está anexado ao salvar/editar vaga (FR-005).
