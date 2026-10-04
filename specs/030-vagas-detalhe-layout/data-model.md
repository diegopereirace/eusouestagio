# Data Model: Detalhe da Vaga — Layout Duas Colunas

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-04

## Entidades

### 1. Node type `faq` (novo)

| Aspecto | Valor |
|---------|--------|
| Machine name | `faq` |
| Label | FAQ |
| Description | Perguntas e respostas associáveis a vagas |
| Preview | opcional |
| Display submitted | `FALSE` |
| Menus | nenhum |

**Campos:**

| Campo | Tipo | Cardinality | Notas |
|-------|------|-------------|--------|
| `title` (nativo) | string | 1 | Pergunta |
| `field_resposta` | `text_long` (storage novo `node.field_resposta`) | 1 | Resposta; text format padrão do site |

**Seeds (UUID fixos):**

| UUID | Título (pergunta) | Resposta (placeholder) |
|------|-------------------|------------------------|
| `b1c2d3e4-f5a6-4789-a012-3456789abc01` | Qual a duração do estágio? | A duração varia conforme o edital da vaga e o acordo com a instituição de ensino. Consulte a descrição da vaga para o período informado. |
| `b1c2d3e4-f5a6-4789-a012-3456789abc02` | Existe auxílio home office? | Depende da vaga e da empresa. Quando disponível, o benefício aparece na seção Benefícios ou na descrição. |

Regras: criar só se UUID ausente; não sobrescrever título/corpo se o node já existir e divergir.

### 2. Paragraph `beneficio_vaga_p` (novo)

| Aspecto | Valor |
|---------|--------|
| Machine name | `beneficio_vaga_p` |
| Label | Benefício da Vaga |
| Fields | `field_image` (ícone, storage paragraph existente), `field_text_simple` (título, storage existente) |

### 3. Fields novos no bundle `vagas`

| Field | Storage | Type | Card. | Target / settings |
|-------|---------|------|-------|-------------------|
| `field_vaga_faq` | novo | `entity_reference` | -1 | `node` → bundles `faq` |
| `field_vaga_etapas_processo` | novo | `string` (max 255) | -1 | etapas do stepper |
| `field_vaga_requisitos` | novo | `string` (max 255) | -1 | um item por requisito |
| `field_vaga_beneficios` | novo | `entity_reference_revisions` | -1 | `paragraph` → `beneficio_vaga_p` |

**Displays `node.vagas` (form `default` + view `default` e/ou `full`):**

- Expor os 4 campos editáveis no form (widgets: autocomplete/tags ou entity browser para FAQ; text multi para etapas/requisitos; paragraphs para benefícios).
- No view display: campos podem ficar hidden se o Twig full ler a entity diretamente (padrão atual do detalhe).

### 4. Campos legados (mantidos)

| Campo | Uso atual | Pós-feature |
|-------|-----------|-------------|
| `field_text_simple_multiple` | Requisitos na página | Compatibilidade; migração → `field_vaga_requisitos` se novo vazio |
| `field_text_simple_multiple_2` | Benefícios texto | Compatibilidade; migração → paragraphs em `field_vaga_beneficios` (só título) se novo vazio |

### 5. Campos reutilizados no detalhe (sem alteração estrutural)

| Campo | Uso UI |
|-------|--------|
| `title` | H1 header |
| `field_text_long_formatted` | Sobre a Vaga |
| `field_empresa_u` → user | Logo (`user_picture`), nome (`field_nome_fantasia`), sobre (`field_sobre_empresa`) |
| `field_cidade` + `field_estados` | Localização header |
| `field_regime_t` | Badge + resumo Modelo |
| `field_horarios` | Badge carga + resumo Período |
| `field_text_simple` | Badge/resumo Bolsa |
| `created` / published | “Postado há…” |

### 6. Dependências de fluxo (sem schema novo)

| Recurso | Uso |
|---------|-----|
| Tabela `vagas_salvas` | estado Salvar |
| Node `candidatura` | estado Candidatar-se |
| JS `script-painel` | handlers `.js-candidatar-vaga` / `.js-salvar-vaga` |
| Rota `/painel/estudante/perfil` | CTA “Completar agora” |

## Relacionamentos

```text
Node vagas
  ├── field_vaga_faq[] ──────────► Node faq
  │                                  ├── title (pergunta)
  │                                  └── field_resposta
  ├── field_vaga_etapas_processo[]   # stepper
  ├── field_vaga_requisitos[]        # checks
  ├── field_vaga_beneficios[] ─────► Paragraph beneficio_vaga_p
  │                                    ├── field_image
  │                                    └── field_text_simple
  ├── field_text_long_formatted      # Sobre
  ├── field_empresa_u ─────────────► User (empresa)
  │                                    ├── user_picture
  │                                    ├── field_nome_fantasia
  │                                    └── field_sobre_empresa
  ├── field_regime_t / field_horarios / field_text_simple
  └── legados field_text_simple_multiple[_2]  # migração opcional
```

## Validação / regras

- Seções omitidas se coleção vazia (após preferir campos novos).
- FAQ unpublished: Drupal access — anônimo não vê.
- Migração: só preenche destino vazio; nunca apaga legado nem sobrescreve destino preenchido.
- Seed FAQ: idempotente por UUID.
- “Vagas” no resumo: literal “Não informado” (sem field).
- Sem entidades Match.

## Configs esperados no `cex` (origem)

- `node.type.faq.yml` + field/displays FAQ
- `paragraphs.paragraphs_type.beneficio_vaga_p.yml` + field/displays
- Storages + instances `field_vaga_*` + `field_resposta`
- Form/view displays `node.vagas` atualizados

## Hook

`custom_configs_update_11047` — ensure estrutura + seed FAQ + migrate opcional. Ver [contracts/deploy-vagas-detalhe.md](contracts/deploy-vagas-detalhe.md).
