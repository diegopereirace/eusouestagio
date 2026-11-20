# Data Model: Refino Visual "Para Empresas"

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-03

Nenhuma entidade nova (content type / block type / paragraph type / field storage). Esta feature **reusa** entidades existentes e, no máximo, corrige conteúdo seed do CTA PE via hook.

## Entidades existentes (tocadas)

### 1. View `banners` — display `block_para_empresas`

| Aspecto | Valor |
|---------|--------|
| Filtro | `field_local_exibicao = para_empresas` |
| Região | `banner`, pages `/para-empresas` |
| Campos de slide | `field_imagem_desktop` (obrigatório no preprocess), `field_imagem_mobile` (opcional → fallback desktop) |
| Image Style no Twig | **nenhum** (URI crua — preserva alpha PNG) |
| Mudança nesta feature | Twig/CSS/library apenas; config da View **inalterada** salvo necessidade futura via cex |

Seed histórico slides: UUIDs `a1b2c3d4-…` / `b2c3d4e5-…` (`11024`); assets `modules/custom/custom_configs/assets/banner-para-empresas/`.

### 2. Block content `nossos_diferenciais`

| Campo | Notas |
|-------|--------|
| UUID instância | `b0a1c2d3-e4f5-4678-9abc-def012345678` (mesma da home) |
| Placement PE | `default_nossosdiferenciaisparaempresas` |
| `field_text_simple` / `field_text_simple_long` | Título / subtítulo — CSS só |
| `field_image` | Colagem — sem troca de asset nesta feature |
| `field_diferenciais_lista` → `diferencial_item_p` | Ícone + título + desc — cor CSS `#4E4E4E` na desc |

**Sem** alteração de dados no hook (só CSS).

### 3. View `depoimentos_carousel` — display `block_depoimentos_empresas`

| Aspecto | Notas |
|---------|--------|
| Nodes | bundle `depoimento` (≥4 seed `11032`) |
| Markup | `data-carousel-center` / `data-carousel-dot` / `data-index` |
| Mudança | CSS (cor do ativo) + eventual fix JS; **sem** mudança de View config |

### 4. Block content `cta_v1` — instância PE

| Campo | Seed canônico | Hook `11076` |
|-------|---------------|--------------|
| UUID | `a7b8c9d0-e1f2-4345-a678-90abcdef0123` | load by UUID |
| Placement | `default_ctav1paraempresas` | inalterado |
| `field_text_simple` | “Pronto para contratar os melhores talentos?” | não tocar se preenchido |
| `field_text_simple_long` | subtítulo seed | não tocar se preenchido |
| `field_link` | `Cadastrar Empresa` → `internal:/cadastro/empresa` | não tocar se preenchido |
| `field_link_2` | `Abrir Vaga` → `internal:/painel/empresa/vagas/nova` | corrige **somente** legado `Falar com Especialista` (ou título vazio + URI legada) |

### 5. Role `empresa`

Não é entidade de conteúdo; critério de render no preprocess/Twig do CTA PE. Múltiplas roles incluindo `empresa` ⇒ trata como empresa.

## Relacionamentos

```text
node:page (/para-empresas)
  └── placements (config block.block.*)
        ├── View banners block_para_empresas → nodes banners
        ├── block_content:nossos_diferenciais (UUID b0a1…)
        ├── View depoimentos_carousel → nodes depoimento
        └── block_content:cta_v1 (UUID a7b8…)
              ├── field_link    → botão 1 (sempre na fonte; UI condiciona)
              └── field_link_2  → botão 2 (markup só se role empresa)

user.roles[] ∋ "empresa" ──► Twig CTA PE
```

## Validação

| Regra | Origem |
|-------|--------|
| Não criar field/bundle | Spec Fora + gates |
| Hook idempotente; sem overwrite editorial livre | FR-018 / FR-019 |
| Botão 2 ausente no HTML se sem role | FR-017 |
| URI canônica nova vaga | `/painel/empresa/vagas/nova` |

## State transitions (CTA `field_link_2`)

```text
[legado "Falar com Especialista" ou vazio+URI legada]
        │ hook 11076 (uma vez / reexec no-op)
        ▼
[Abrir Vaga + internal:/painel/empresa/vagas/nova]

[qualquer outro título editorial]
        │
        ▼
[inalterado]
```
