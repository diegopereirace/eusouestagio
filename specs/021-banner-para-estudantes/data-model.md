# Data Model: Banner (Hero) Para Estudantes

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-29

## Entidades

### 1. `field_local_exibicao` — novo valor

| value | label |
|-------|--------|
| `para_estudantes` | Para Estudantes |

Config: `field.storage.node.field_local_exibicao.yml` (+ ensure defensivo no hook).

Valores existentes **inalterados**: `home`, `internas`, `quem_somos`, `para_empresas`.

**Storages novos**: nenhum.

---

### 2. View `banners` / display `block_para_estudantes`

| Aspecto | Valor |
|---------|--------|
| Machine name | `block_para_estudantes` |
| Display title | Banners Para Estudantes |
| Filtros | `status=1`, `type=banners`, `field_local_exibicao=para_estudantes` |
| Sort | herda default (`field_peso` ASC, `created` DESC) |
| Pager | `some`, `items_per_page: 1` |
| Empty | sem área (zero resultados → omit Twig) |
| css_class | `css-banners-para-estudantes` (ou equivalente) |

---

### 3. Node seed `banners` (1 hero)

| Atributo | Valor |
|----------|--------|
| UUID | `c3d4e5f6-a7b8-4901-c234-567890abcdef` |
| Bundle | `banners` |
| Status | publicado |
| `field_local_exibicao` | `para_estudantes` |
| `field_peso` | `0` |
| `field_imagem_desktop` | asset seed (ilustração Figma) |
| `field_imagem_mobile` | mesmo asset (ou variante se existir) |
| Copy / CTAs | **não** no node (Twig) |

- Asset: `modules/custom/custom_configs/assets/banner-para-estudantes/hero.png` → `public://banners/…` (ou diretório já usado pelos seeds de banner) via hook.
- Idempotência: load by UUID; se existe e editorial divergiu, **não** sobrescrever; se falta imagem e asset existe, anexar só se campo vazio.

---

### 4. Placement do novo display

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_views_block__banners_block_para_estudantes` |
| Plugin | `views_block:banners-block_para_estudantes` |
| Tema | `default` |
| Região | `banner` |
| Weight | `0` |
| Pages | `/para-estudantes` |
| `label_display` | `'0'` |
| `status` | `true` |

UUID do block config: gerar estável no `cex` / fixar no hook ensure (não reutilizar UUIDs de PE/QS/home).

---

### 5. Placement legado `banners-block_1`

| Config ID | Campo | Antes | Depois |
|-----------|-------|-------|--------|
| `default_views_block__banners_block_1` | `status` | `true` | **`false`** |
| idem | `pages` | `/para-estudantes` | **inalterado** (não esvaziar) |

---

### 6. Hook `custom_configs_update_11033`

Responsabilidades (idempotentes):

1. Ensure allowed value `para_estudantes` em `field_local_exibicao`.
2. Ensure defensivo: display `block_para_estudantes` (se `cim` ainda não aplicou).
3. Seed do nó banner + cópia do asset.
4. Ensure placement do novo bloco (pages/região/status).
5. Desativar `default_views_block__banners_block_1` (`status = false`).
6. Mensagem Drush com criada/skipped.

**Não faz**: sobrescrever copy/imagem editorial; criar storages; alterar placements home/QS/PE/Contato; esvaziar `pages` do `block_1`.

---

## Relacionamentos

```text
node.banners (local=para_estudantes, ×1 seed)
  └── View banners / block_para_estudantes (pager 1)
        └── Placement → região banner (/para-estudantes)
              └── Twig: copy/CTAs fixos + imagem do nó

default_views_block__banners_block_1 → status false
View vagas page_1 (/para-estudantes) → listagem abaixo do hero (#main-content)
```

## Validation rules (runtime)

- Zero banners publicados com local → omitir markup do hero.
- Banner sem imagem → coluna de texto; omitir coluna visual (sem broken image).
- Campos de copy vazios no Twig — N/A (strings fixas); se no futuro forem condicionais, omit empty.
- Reexecução hook → no-op seguro.
- Rotas ≠ `/para-estudantes` → novo placement ausente.
