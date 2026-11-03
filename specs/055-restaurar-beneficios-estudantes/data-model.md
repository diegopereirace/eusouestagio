# Data Model: Restaurar Benefícios Estudantes — Layout Figma

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

Nenhum content type / block type / paragraph type / field **storage** ou **instance** novo. Esta feature **restaura** o seed e a apresentação do bundle já estendido pela `054`.

## Entidade principal

### Block content `beneficios_estudantes`

| Aspecto | Valor |
|---------|--------|
| UUID | `a8b9c0d1-e2f3-4456-a789-0bcdef123456` |
| Placement | `default_beneficiosestudantes` — tema `default`, região `content_full`, weight `0`, pages `/para-estudantes`, `label_display: 0` |
| Info | `Benefícios Estudantes` |

| Campo | Storage | Cardinalidade | Uso | Seed canônico |
|-------|---------|---------------|-----|---------------|
| `field_text_simple_2` | string 255 | 1 | Badge / label | `Benefícios para Estudantes` |
| `field_text_simple` | string 255 | 1 | Título | `Muito mais do que encontrar um estágio.` |
| `field_text_simple_long` | string_long | 1 | Subtítulo | `Na Eu Sou Estágio você se desenvolve, recebe apoio e constrói uma carreira com mais segurança e propósito.` |
| `field_itens_lista` | ERR → paragraph | −1 | Lista de benefícios | 6 × `card_icon_text_p` |
| `field_link` | link | 1 | CTA | title `Cadastre-se gratuitamente`; uri `internal:/cadastro/candidato` |
| `field_image` | image | 1 | Foto **central** da colagem | `collage-center.png` (fallback `collage.png`) |

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

### Colagem (órbita) — assets fora do CMS

| Asset | Papel |
|-------|--------|
| `collage-center.png` | Foto central (também seed de `field_image`) |
| `collage-sat-1.png` … `collage-sat-5.png` | Satélites da órbita (somente `public://` via hook) |
| `collage.png` | Fallback composto (legado 054) se faces ausentes |

## Relacionamentos

```text
block_content:beneficios_estudantes (UUID a8b9…)
  ├── field_text_simple_2          (badge)
  ├── field_text_simple            (título → Twig bicolor se canônico)
  ├── field_text_simple_long       (subtítulo)
  ├── field_itens_lista[] ──► paragraph:card_icon_text_p
  │                              ├── field_image
  │                              ├── field_text_simple
  │                              └── field_text_simple_long
  ├── field_link                   (CTA)
  └── field_image                  (centro da colagem)
       + assets públicos satélites (não-field)

config block.block.default_beneficiosestudantes
  └── plugin block_content:a8b9c0d1-e2f3-4456-a789-0bcdef123456
```

## Validação

| Regra | Origem |
|-------|--------|
| Sem storage/instance nova | Spec Assumptions + gates |
| Mesmo placement / UUID; recreate se ausente | FR-007 / FR-009 / SC-008 |
| Hook idempotente; editorial divergente preservado | FR-009 / SC-006 |
| Lista &lt;6: renderiza existentes; seed preenche se legado/vazio | Edge cases |
| Lista &gt;6 editorial: renderiza todos, sem truncar | Edge cases |
| CTA vazio → omitir botão | Edge cases / FR-006 seed |
| Colagem/faces ausentes → fallback ou omitir visual; texto ok | Edge cases / R5 |
| Ícone ausente no item → título+descrição legíveis no card | Edge cases |

## State transitions

### Título do bloco

```text
vazio                                              --> canônico Figma
"Encontre oportunidades que combinam com você."    --> canônico Figma
"Muito mais do que encontrar um estágio."          --> preservado (já canônico)
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
subtítulo canônico Figma já aplicado
  --> preservado
qualquer outro
  --> preservado
```

### Badge / CTA / colagem (centro)

```text
badge vazio --> seed "Benefícios para Estudantes"
badge "BENEFÍCIOS PARA ESTUDANTES" --> preservado (equivalente canônico)
CTA / field_image vazios --> seed canônico
preenchidos (não vazios) --> preservados
```

### Lista de itens

```text
bloco ausente
  --> recriar UUID + 6 paragraphs canônicos

lista vazia
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
ausente / desabilitado --> ensure ativo em content_full / /para-estudantes / weight 0
já correto --> no-op
```
