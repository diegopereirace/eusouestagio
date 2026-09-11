# Data Model: Bloco Nossa Metodologia

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-11  
**Atualizado**: 2026-09-11 — etapas/passos editáveis item a item

## Entidade

### `nossa_metodologia` (`block_content`)

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Título | `field_text_simple` | string | 1 | não | storage reutilizado |
| Subtítulo | `field_text_simple_long` | string_long | 1 | não | storage reutilizado |
| Etapas (imagens) | `field_image` | image | -1 | não | storage reutilizado; badges Identidade/Pertencimento/Performance |
| Passos | `field_metodologia_passos` | entity_reference_revisions → paragraph | -1 | não | storage novo; bundle `metodologia_passo_p` |

### `metodologia_passo_p` (`paragraph`)

| Campo | Storage | Tipo | Cardinalidade | Obrigatório | Notas |
|-------|---------|------|---------------|-------------|-------|
| Ícone | `field_image` | image | 1 | não | storage paragraph reutilizado; SVG permitido |
| Título | `field_text_simple` | string | 1 | não | storage paragraph reutilizado |

O bundle do bloco segue o padrão local sem revisão obrigatória. Etapas usam `public://block/nossa-metodologia/etapas/[ano]-[mês]`. Ícones dos passos usam `public://paragraph/metodologia-passo/[ano]-[mês]`. Alt opcional.

## Relacionamentos

```text
block_content:nossa_metodologia
├── field_text_simple
├── field_text_simple_long
├── field_image[] ──► file
└── field_metodologia_passos[] ──► paragraph:metodologia_passo_p
       ├── field_image ──► file
       └── field_text_simple

block placement (config)
└── plugin block_content:<UUID fixo> ──► block_content:nossa_metodologia
```

## Regras de validação e fallback

| Estado | Resultado público |
|--------|-------------------|
| Título vazio | omitir H2 |
| Subtítulo vazio | omitir parágrafo |
| Sem etapas | omitir `.nm-etapas` sem erro |
| Sem passos | omitir `.nm-passos` sem erro |
| Passo sem ícone | renderizar só o título (se houver) |
| Passo sem título | renderizar só o ícone (se houver) |
| Imagem maior que o container | reduzir fluidamente, sem overflow |
| Alt vazio | atributo `alt=""` (etapas) ou alt do título do passo |

Setas entre passos são **decorativas via CSS** — não há campo no CMS.

## Estado e transições

```text
inexistente
  └── seed/editor cria ──► publicado
                             ├── editor altera textos/imagens/passos ──► publicado atualizado
                             └── editor despublica ──► não renderizado
```

O seed é idempotente: entidade com o UUID fixo existente implica `no-op` (ou atualização estrutural sem sobrescrever conteúdo editorial). Imagens seed (`11007`) só preenchem campos ainda vazios.

## Configuração a versionar

**Reutilizar, sem recriar:**

- `field.storage.block_content.field_text_simple.yml`
- `field.storage.block_content.field_text_simple_long.yml`
- `field.storage.block_content.field_image.yml`
- `field.storage.paragraph.field_image.yml`
- `field.storage.paragraph.field_text_simple.yml`

**Criar/exportar:**

- `block_content.type.nossa_metodologia.yml`
- `paragraphs.paragraphs_type.metodologia_passo_p.yml`
- `field.storage.block_content.field_metodologia_passos.yml`
- field instances do bloco e do paragraph
- form/view displays do bloco e do paragraph
- `block.block.default_nossametodologia.yml`
- permissões do papel editor aplicável (`user.role.moderador.yml`)

**Removido (modelo anterior):**

- `field.storage.block_content.field_image_desktop.yml`
- `field.storage.block_content.field_image_mobile.yml`
- instances desktop/mobile no bundle

## Seed lógico

- Bundle: `nossa_metodologia`
- UUID: `1f9b40ca-aa77-4e7e-a450-5aa94888e274` (compartilhado com o placement)
- Info/título: `Nossa Metodologia`
- Subtítulo: texto de referência da FR-015
- Passos: títulos de referência via `11006` (sem sobrescrever se já houver paragraphs)
- Imagens: versionadas em `modules/custom/custom_configs/assets/nossa-metodologia/` e anexadas por `11007` somente se o campo correspondente estiver vazio
  - etapas: `etapas/identidade.png`, `pertencimento.png`, `performance.png`
  - ícones: `passos/pessoa-habilidades.png`, `ambiente-correto.png`, `entrega-resultados.png`, `cresce.png`, `empresa-evolui.png` (asset ausente → skip daquele ícone)
- Placement: `default`, `content_full`, weight `-3`, somente `<front>`

## Fora do modelo

- Campos de cor ou seta editáveis no CMS
- Variantes desktop/mobile por item
- Image styles obrigatórios
- Rotas, tabelas ou APIs novas
