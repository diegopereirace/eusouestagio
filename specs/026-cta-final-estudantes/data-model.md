# Data Model: Bloco CTA Final — Para Estudantes

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-30

## Entidades

### 1. `cta_v1` (block_content) — **reuso** (sem alteração estrutural)

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string (255) | 1 | não* | storage **existente** |
| Corpo | `field_text_simple_long` | string_long | 1 | não* | storage **existente** |
| Botão primário | `field_link` | link | 1 | não* | URI + title |
| Botão secundário | `field_link_2` | link | 1 | não* | URI + title |

\*Publicamente, campos vazios são omitidos no Twig da instância.

**Esta feature NÃO cria** block type, field storage ou field instance.

### 2. Instância seed (nova)

| Aspecto | Valor |
|---------|--------|
| UUID | `e1f2a3b4-c5d6-4789-d012-3ef012345678` |
| Bundle | `cta_v1` |
| Admin info | `CTA v1 Para Estudantes` |
| Distinto de | QS `e6f7a8b9-c0d1-4e2f-9a3b-4c5d6e7f8091`; PE empresas `a7b8c9d0-e1f2-4345-a678-90abcdef0123` |

### 3. Placement (config Block) — novo

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_ctav1paraestudantes` |
| UUID placement | `f2a3b4c5-d6e7-4890-e123-4f0123456789` |
| Theme | `default` |
| Region | `content_full` |
| Weight | `4` (após `default_views_block__vagas_block_3` = `3`) |
| Plugin | `block_content:e1f2a3b4-c5d6-4789-d012-3ef012345678` |
| Visibility | `request_path` = `/para-estudantes` (negate false) |
| Label display | `0` (oculto) |

## Relacionamentos

```text
block_content:cta_v1 (UUID e1f2a3b4-…)
  ├── field_text_simple      (título)
  ├── field_text_simple_long (corpo)
  ├── field_link             (botão primário)
  └── field_link_2           (botão secundário)

# Placement:
block.block.default_ctav1paraestudantes
  → plugin block_content:e1f2a3b4-c5d6-4789-d012-3ef012345678
  → content_full / weight 4 / /para-estudantes

# Vizinhos PE estudantes (não alterar):
default_beneficiosestudantes              weight 0
default_jornadaestudante                  weight 1
default_perfildestaqueestudante           weight 2
default_views_block__vagas_block_3        weight 3
default_ctav1paraestudantes               weight 4   ← esta feature

# CTAs irmãos (não alterar visual/seed):
default_ctav1quemsomos      /quem-somos     w12
default_ctav1paraempresas   /para-empresas  w5
```

## Seed (conteúdo)

| Campo | Valor |
|-------|--------|
| Título | `Pronto para dar o próximo passo?` |
| Corpo | `Junte-se a milhares de estudantes que já encontraram a oportunidade ideal através da nossa plataforma.` |
| Primário | title `Cadastre-se Gratuitamente` → uri `internal:/cadastro/candidato` |
| Secundário | title `Explorar Vagas` → uri `internal:/vagas` |

**Idempotência**: se a instância com UUID fixo já existir → não duplicar; popular **somente** campos vazios; **não** sobrescrever editorial divergente do seed.

## Apresentação (não é entidade Drupal)

| Aspecto | Valor |
|---------|--------|
| Twig | `block--default-ctav1paraestudantes.html.twig` |
| Classe escopo | `.block-cta-v1--para-estudantes` |
| Library | `default/cta_v1_para_estudantes` → `cta-v1-para-estudantes.css` |
| Library clara | **não** attach `default/cta_v1` nesta instância |

## Regras de validação / fallback

| Estado | Resultado público |
|--------|-------------------|
| Título vazio | omitir título |
| Corpo vazio | omitir corpo |
| Primário sem URI | omitir botão primário |
| Secundário sem URI | omitir botão secundário |
| Ambos botões ausentes | omitir wrapper de ações |
| Todos os campos vazios | **não** renderizar a faixa |
| Página ≠ `/para-estudantes` | bloco não aparece (visibility) |

## Config YAML a versionar (checklist)

**Reutilizar (não alterar):**

- `block_content.type.cta_v1.yml`
- `field.storage.block_content.field_text_simple.yml`
- `field.storage.block_content.field_text_simple_long.yml`
- `field.storage.block_content.field_link.yml`
- `field.storage.block_content.field_link_2.yml`
- field instances / displays `block_content.cta_v1.*`
- `block.block.default_ctav1quemsomos.yml`
- `block.block.default_ctav1paraempresas.yml`
- placements 021–025 de `/para-estudantes`

**Criar/exportar:**

- `block.block.default_ctav1paraestudantes.yml`

## Fora do modelo

- Novos block types / storages / instances
- Paragraphs / Layout Builder
- Alteração de View `vagas` / displays `page_1` / `block_3`
- Alteração estrutural dos CTAs de Quem Somos / Para Empresas
