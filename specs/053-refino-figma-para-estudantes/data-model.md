# Data Model: Refino Visual "Para Estudantes"

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

Nenhuma entidade nova (content type / block type / paragraph type / field storage). Esta feature **reusa** entidades existentes, adiciona um placement de config e ajusta estado/copy legado via hook.

## Entidades existentes (tocadas)

### 1. View `banners` — display `block_para_estudantes`

| Aspecto | Valor |
|---------|--------|
| Filtro | `field_local_exibicao = para_estudantes` |
| Placement | `default_views_block__banners_block_para_estudantes` |
| Região | `banner`, pages `/para-estudantes`, weight `0` |
| Campos de slide | `field_imagem_desktop` (obrigatório no preprocess), `field_imagem_mobile` (opcional → fallback desktop) |
| Image Style no Twig | **nenhum** (URI crua — preserva alpha PNG) |
| Seed histórico | UUID `c3d4e5f6-a7b8-4901-c234-567890abcdef` (`11033`); asset `modules/custom/custom_configs/assets/banner-para-estudantes/hero.png` |
| Mudança nesta feature | Twig + library + CSS. Config da View **inalterada** |

### 2. Plugin block `custom_banners_vagas_hero_search` (dois placements)

| Aspecto | `/vagas` (intocado) | `/para-estudantes` (novo) |
|---------|---------------------|---------------------------|
| Config id | `default_custom_banners_vagas_hero_search` | `default_custom_banners_vagas_hero_search_estudantes` |
| UUID | `a1b2c3d4-e5f6-4789-a012-bcdef0123456` | `e7f8a9b0-c1d2-4345-b678-0530abcdef01` |
| Plugin | `custom_banners_vagas_hero_search` | o mesmo |
| Região / weight | `highlighted` / `-50` | `highlighted` / `-50` |
| Visibility | `/vagas` | `/para-estudantes` |
| Form | GET `title`, `cidade`, `cursos` → rota `view.vagas.page_1` | o mesmo |
| Copy | Twig fixo (título, subtítulo, pills) | o mesmo Twig |

Não há field storage. Valores preenchidos não são persistidos neste bloco.

### 3. Block content `beneficios_estudantes`

| Campo | Notas |
|-------|--------|
| UUID | `a8b9c0d1-e2f3-4456-a789-0bcdef123456` |
| Placement | `default_beneficiosestudantes`, `content_full`, weight `0`, pages `/para-estudantes` |
| `field_text_simple` | Título. Seed já é o Figma: `Encontre oportunidades que combinam com você.` — não sobrescrever se preenchido |
| `field_text_simple_long` | Subtítulo. Ver transição de legado em R5 |
| `field_itens_lista` | Paragraphs `card_icon_text_p` (ilimitado no storage; seed = 4) |

Paragraph `card_icon_text_p`:

| Campo | Uso |
|-------|-----|
| `field_image` | Ícone; hook preenche só se vazio, a partir de `assets/beneficios-estudantes/icon-1.png` … `icon-4.png` |
| `field_text_simple` | Título do card. Único legado de título: `Filtros Inteligentes` → `Filtros inteligentes` |
| `field_text_simple_long` | Descrição. Não reescrever se preenchida |

Ordem dos cards no seed (preservar se a lista já tiver itens): Vagas alinhadas → Modelos de trabalho → Vários segmentos → Filtros inteligentes.

### 4. Block content `perfil_destaque_estudante` (legado oculto)

| Campo | Notas |
|-------|--------|
| UUID conteúdo | `c0d1e2f3-a4b5-4678-c901-2def01234567` — **permanece** |
| Placement | `default_perfildestaqueestudante` |
| Estado alvo | `status: false` |
| Região / visibility | não precisam mudar; com status false não renderiza em `/para-estudantes` |
| Paragraphs `item_lista_icone_p` | intocados |

### 5. Block content `cta_v1` — instância estudantes

| Campo | Notas |
|-------|--------|
| UUID | `e1f2a3b4-c5d6-4789-d012-3ef012345678` |
| Placement | `default_ctav1paraestudantes`, `content_full`, weight `4`, pages `/para-estudantes` |
| Mudança de dados | **nenhuma** |
| Mudança visual | CSS de vão, escopo `body.path-para-estudantes` |

### 6. Demais blocos da landing (não modelados de novo)

| Placement | Weight | Esta feature |
|-----------|--------|----------------|
| `default_jornadaestudante` | `1` | permanece |
| View `vagas` `block_3` | `3` | permanece |
| Node `page` shell | UUID `d0e1f2a3-b4c5-4678-9abc-def012345678` (`11041`) | permanece; o `#main` só perde padding/margin |

## Relacionamentos

```text
node:page (/para-estudantes)
  └── placements (config block.block.*)
        ├── View banners block_para_estudantes → nodes banners
        ├── plugin custom_banners_vagas_hero_search
        │     └── GET → view.vagas.page_1
        ├── block_content:beneficios_estudantes (UUID a8b9…)
        │     └── field_itens_lista → paragraph card_icon_text_p × 4
        ├── block_content:jornada_estudante          (inalterado)
        ├── block_content:perfil_destaque_estudante  (placement disabled)
        ├── View vagas block_3                       (inalterado)
        └── block_content:cta_v1 (UUID e1f2…)
```

## Validação

| Regra | Origem |
|-------|--------|
| Não criar field/bundle/plugin | Spec Fora + gates |
| Dois placements, um plugin, ids de config distintos | FR-002 / edge case |
| Hook idempotente; copy livre preservada | FR-010 / SC-006 |
| Perfil fora do HTML público; entidade no banco | FR-008 |
| Ícone ausente em `public://` é copiado do asset versionado | edge case |
| Submissão do Hero não muda o contrato de query de `/vagas` | FR-003 / FR-004 |

## State transitions

### Placement perfil (`default_perfildestaqueestudante.status`)

```text
true  --11077--> false
false --11077--> false   (no-op)
ausente --11077--> mensagem; não recria o bloco de conteúdo
```

### Subtítulo benefícios (só UUID `a8b9…`)

```text
vazio            --> frase Figma
frase seed 11035 --> frase Figma
qualquer outra   --> preservada
```

Frase seed `11035`: `Nossa plataforma utiliza inteligência para conectar você às vagas e empresas que fazem sentido para o seu perfil e momento.`

Frase Figma: `Nossa plataforma utiliza inteligência de dados para conectar seu perfil às melhores vagas do mercado.`

### Título do 4º card

```text
vazio (card sem título, ícone pode ser preenchido à parte) --> não inventar título se o card já existe com outro texto
exato "Filtros Inteligentes" --> "Filtros inteligentes"
qualquer outro título --> preservado
```

### Placement Hero estudantes

```text
ausente --> criar com UUID/região/weight/visibility acima
existente com plugin errado --> recriar apontando para custom_banners_vagas_hero_search
existente correto --> no-op (garantir region highlighted, weight -50, pages /para-estudantes, status true)
```

O placement de `/vagas` não entra nessa máquina de estados.
