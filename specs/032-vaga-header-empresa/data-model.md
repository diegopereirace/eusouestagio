# Data Model: Cabeçalho do Detalhe da Vaga — Perfil Empresa (032)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-10-07  
**Predecessor model**: [031 data-model](../031-vagas-detalhe-refino/data-model.md)

## Entidades

### 1. Node type `empresa` (novo)

| Aspecto | Valor |
|---------|--------|
| Machine name | `empresa` |
| Label | Empresa |
| Papel | Perfil público da organização anunciante |
| `title` | Nome público da empresa |
| Menus | nenhum obrigatório |
| Path público | não exigido nesta feature |

**Campos:**

| Campo | Storage | Tipo | Card. | Notas |
|-------|---------|------|-------|--------|
| `title` (nativo) | — | string | 1 | Nome da empresa |
| `field_imagem` | **reuso** `node.field_imagem` | image | 1 | Logo |

**Displays:** form default — title + imagem; view default — title + imagem (ou hidden se só Twig da vaga consumir a entity).

**Seed (UUID fixo):**

| UUID | Título | Logo |
|------|--------|------|
| `f6a7b8c9-d0e1-4234-e567-89abcdef0123` | EcoConstrutora | asset `modules/custom/custom_configs/assets/vaga-header-empresa/logo-ecoconstrutora.png` → `public://` |

Regras: criar só se UUID ausente; não sobrescrever título/logo se editorial divergir; reexecução = no-op seguro.

---

### 2. Fields novos no bundle `vagas`

| Field | Storage | Type | Card. | Target / notes |
|-------|---------|------|-------|----------------|
| `field_vaga_empresa` | **novo** `node.field_vaga_empresa` | `entity_reference` | 1 | `node` → bundle `empresa` |
| `field_vaga_carga_horaria` | **novo** `node.field_vaga_carga_horaria` | `string` | 1 | Texto pill (ex. “30h semanais”) |

**Displays `node.vagas`:** form default — incluir “Empresa da vaga” (autocomplete/select) + “Carga horária”; view display pode ocultar (Twig full lê a entity).

---

### 3. Seed vaga de demonstração

| UUID | Título | Campos header |
|------|--------|---------------|
| `a7b8c9d0-e1f2-4345-f678-9abcdef01234` | Engenheiro Civil | `field_vaga_empresa` → EcoConstrutora; `field_vaga_carga_horaria` = `30h semanais`; regime + bolsa + cidade + estado preenchidos; publicado |

Regras: criar só se UUID ausente; se existir com campos vazios, preencher apenas vazios; não duplicar; não apagar vagas editoriais.

---

### 4. Campos reutilizados no header (sem alteração estrutural)

| Campo | Uso no header |
|-------|----------------|
| `title` | H1 |
| `field_regime_t` | Pill regime (label do termo) |
| `field_text_simple` | Pill bolsa/salário |
| `field_cidade` + `field_estados` | Localização (pin) |
| `created` | Base do “Postado há…” (preprocess) |

| Campo | Fora do header nesta feature |
|-------|------------------------------|
| `field_horarios` | Não entra nas 4 pills do Figma (turno ≠ carga) |
| `field_empresa_u` | Legado; seção “Sobre a Empresa” / painel; **não** fonte do header |

---

### 5. Apresentação — Header da Vaga (não é entity)

Composição Twig/CSS:

| Slot | Fonte |
|------|--------|
| Logo | `field_vaga_empresa.entity.field_imagem` |
| Título | `node.title` (h1) |
| Nome empresa | `field_vaga_empresa.entity.label` |
| Verificado | markup fixo se empresa presente |
| Local | cidade/estado |
| Pills | regime, carga, bolsa, postado há |

Estados: omitir logo/nome/local/pill individual quando vazio; empresa não publicada → tratar como ausência.

---

### 6. Relacionamentos

```text
empresa (1) ────────< field_vaga_empresa >──────── (N) vagas
user role empresa ─── field_empresa_u ─── (legado, inalterado)
```

---

### 7. Validação / regras

| Regra | Detalhe |
|-------|---------|
| Cardinalidade empresa na vaga | 0..1 |
| Access | visitante só vê dados de empresa publicada/acessível |
| Idempotência seed | UUID fixo; sem duplicata |
| Não destrutivo | não remover `field_empresa_u` nem migrar em massa |
