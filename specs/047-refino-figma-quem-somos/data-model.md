# Data Model: Refino Visual "Quem Somos"

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-02

Nenhuma entidade nova (content type / block type / paragraph type). Esta feature **altera** configurações de Image Style, view displays e conteúdo seed já existentes.

## Entidades existentes (tocadas)

### 1. Node `quem_somos` (bundle existente)

| Campo | Storage | Uso nesta feature |
|-------|---------|-------------------|
| `field_imagem` | image | Foto Sobre nós → Image Style `quem_somos_sobre_nos` |
| `field_text_long_formatted` / corpo | text | Copy Sobre nós — hook preenche **só se vazio** |
| `field_text_simple_long` | string_long | Texto Missão — só se vazio |
| `field_text_simple_long_2` | string_long | Texto Visão — só se vazio |
| `field_imagem_desktop` | image | Fundo Missão/Visão → Image Style `quem_somos_missao_visao` |

View display `node.quem_somos.default`: setar `image_style` nos campos de imagem (defensivo; Twig também usa URL estilizada).

### 2. Block content `diferenciais_quem_somos` + paragraph `diferencial_simples_p`

| Campo | Notas |
|-------|--------|
| `field_itens_lista` → 8× `diferencial_simples_p` | Ordem/rótulos preservados |
| `field_image` (paragraph) | Troca arquivo provisório → final; `image_style: diferencial_simples_icon` |
| `field_text_simple` (+ subtexto se houver) | Não sobrescrever editorial |

UUID do bloco seed (feature 013): `d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80` — **não alterar**.

### 3. Block content `cta_v1`

| Campo | Notas |
|-------|--------|
| `field_link` / `field_link_2` | Sem mudança de dados; só CSS do secundário |

Placement `default_ctav1quemsomos` (weight 12) — **não alterar**.

### 4. Block / View Impact in Numbers + banner View

| Entidade | Notas |
|----------|--------|
| `impact_numbers_quem_somos` / placement weight 11 | Sem mudança estrutural |
| View `banners` display `block_quem_somos` | Sem novo Image Style; só Twig/CSS de visibilidade |

## Image Styles novos

| Config ID | Label sugerido | Effect | Dados |
|-----------|----------------|--------|-------|
| `image.style.quem_somos_sobre_nos` | Quem Somos — Sobre nós | `image_scale_and_crop` | width 864, height 538, anchor `center-center` |
| `image.style.quem_somos_missao_visao` | Quem Somos — Missão/Visão | `image_scale` | width 1920, height null/upscale false |
| `image.style.diferencial_simples_icon` | Diferencial simples — ícone | `image_scale` | width 160, height 160 |

## Assets seed

| Origem (versão) | Destino runtime | Regra |
|-----------------|-----------------|--------|
| `modules/custom/custom_configs/assets/diferenciais-quem-somos/*` | `public://` (subdir estável do projeto, ex. `diferenciais-quem-somos/` ou path já usado em 013) | Copiar se ausente; atualizar paragraph só se provisório/vazio |

Nomes provisórios a detectar: `icon-1.png` … `icon-8.png`.

## Relacionamentos

```text
node:quem_somos
  ├── field_imagem          → ImageStyle quem_somos_sobre_nos
  ├── field_imagem_desktop  → ImageStyle quem_somos_missao_visao
  └── textos (fill-if-empty via 11069)

block_content:diferenciais_quem_somos
  └── field_itens_lista[] → paragraph:diferencial_simples_p
        └── field_image → ImageStyle diferencial_simples_icon
              (+ file sync assets → public://)

block_content:cta_v1
  └── (sem mudança de modelo; CSS outline em field_link_2)

image.style.* (3 novos) ──cex──► config/sync
```

## Validação / estados

| Regra | Comportamento |
|-------|----------------|
| Idempotência | 2ª execução de `11069` = no-op seguro |
| Editorial divergente | Nunca sobrescrever textos/ícones já trocados pelo editor (exceto ícones ainda provisórios) |
| Campo imagem vazio | Não gerar erro; seções já condicionais (`show_missao_visao`, omit empty) |
| Display sem style | Hook ensure aplica style se ainda `''` |

## Transições (deploy)

```text
Origem: alterar API/admin → cex → commit
Destino: cim → updb(11069) → cim → cr
         └── styles/displays ──┘    └── displays pós-UUID/seed
```
