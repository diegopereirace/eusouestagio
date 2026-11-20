# Data Model: Benefícios Estudantes — Layout Figma

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

Nenhum content type / block type / paragraph type / field **storage** novo. Esta feature **estende** o bundle existente com 3 field instances e migra o seed de conteúdo.

## Entidade principal

### Block content `beneficios_estudantes`

| Aspecto | Valor |
|---------|--------|
| UUID | `a8b9c0d1-e2f3-4456-a789-0bcdef123456` |
| Placement | `default_beneficiosestudantes` — tema `default`, região `content_full`, weight `0`, pages `/para-estudantes`, `label_display: 0` |
| Info | `Benefícios Estudantes` |

| Campo | Storage | Cardinalidade | Uso nesta feature | Seed canônico |
|-------|---------|---------------|-------------------|---------------|
| `field_text_simple_2` | string 255 | 1 | **Novo** — badge | `BENEFÍCIOS PARA ESTUDANTES` |
| `field_text_simple` | string 255 | 1 | Título | `Muito mais do que encontrar um estágio.` |
| `field_text_simple_long` | string_long | 1 | Subtítulo | `Na Eu Sou Estágio você se desenvolve, recebe apoio e constrói uma carreira com mais segurança e propósito.` |
| `field_itens_lista` | ERR → paragraph | −1 (storage) | Lista de benefícios | 6 × `card_icon_text_p` |
| `field_link` | link | 1 | **Novo** — CTA | title `Cadastre-se gratuitamente`; uri `internal:/cadastro/candidato` |
| `field_image` | image | 1 | **Novo** — colagem composta | `collage.png` via asset do módulo |

### Paragraph `card_icon_text_p` (reusado)

| Campo | Uso |
|-------|-----|
| `field_image` | Ícone do item; seed `icon-1.png`…`icon-6.png` |
| `field_text_simple` | Título curto do benefício |
| `field_text_simple_long` | Descrição |

Ordem canônica do seed (delta 0…5):

| # | Título | Descrição | Asset |
|---|--------|-----------|--------|
| 1 | Encontrar propósito | Descubra oportunidades que fazem sentido para você. | icon-1.png |
| 2 | Desenvolver competências | Acesse conteúdos e recursos para a sua evolução. | icon-2.png |
| 3 | Receber acompanhamento | Conte com orientação durante toda a sua jornada. | icon-3.png |
| 4 | Aprender mais | Tenha acesso a dicas, cursos e materiais exclusivos. | icon-4.png |
| 5 | Construir carreira | Dê os primeiros passos para um futuro promissor. | icon-5.png |
| 6 | Ter uma experiência de estágio de verdade | Conecte-se com empresas que valorizam seu potencial. | icon-6.png |

## Relacionamentos

```text
block_content:beneficios_estudantes (UUID a8b9…)
  ├── field_text_simple_2          (badge)
  ├── field_text_simple            (título)
  ├── field_text_simple_long       (subtítulo)
  ├── field_itens_lista[] ──► paragraph:card_icon_text_p
  │                              ├── field_image
  │                              ├── field_text_simple
  │                              └── field_text_simple_long
  ├── field_link                   (CTA)
  └── field_image                  (colagem)

config block.block.default_beneficiosestudantes
  └── plugin block_content:a8b9c0d1-e2f3-4456-a789-0bcdef123456
```

## Validação

| Regra | Origem |
|-------|--------|
| Sem storage novo; só instances no bundle | Spec Assumptions + gates |
| Mesmo placement / UUID | FR-006 |
| Hook idempotente; editorial divergente preservado | FR-008 / SC-006 |
| Lista &lt;6: renderiza existentes; seed preenche se regra de legado/vazio aplicar | Edge cases |
| Lista &gt;6 editorial: renderiza todos, sem truncar | Edge cases |
| CTA vazio → omitir botão | Edge cases / FR-005 seed |
| Colagem ausente → omitir visual, texto ok | Edge cases |
| Ícone ausente no item → título+descrição legíveis | Edge cases |

## State transitions

### Título do bloco

```text
vazio                                              --> canônico Figma
"Encontre oportunidades que combinam com você."    --> canônico Figma
qualquer outro                                     --> preservado
```

### Subtítulo do bloco

```text
vazio
  --> canônico Figma
"Nossa plataforma utiliza inteligência para conectar você às vagas e empresas que fazem sentido para o seu perfil e momento."
  --> canônico Figma
"Nossa plataforma utiliza inteligência de dados para conectar seu perfil às melhores vagas do mercado."
  --> canônico Figma
qualquer outro
  --> preservado
```

### Badge / CTA / colagem

```text
vazio --> seed canônico
preenchido --> preservado (não sobrescrever)
```

### Lista de itens

```text
vazia
  --> criar 6 paragraphs canônicos

exatamente 4 itens cujos títulos ∈
  {Vagas alinhadas, Modelos de trabalho, Vários segmentos,
   Filtros Inteligentes, Filtros inteligentes}
  --> substituir lista pelos 6 canônicos

qualquer outra lista
  --> preservar; preencher só field_image vazio quando aplicável
```

### Placement

```text
ausente / plugin errado --> ensure (padrão 11035)
status false            --> reativar (status true) se região/visibility corretas
já ok                   --> no-op
```
