# Data Model: Página Para Empresas

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-25

## Entidades

### 1. Nó canônico `para_empresas` (`/para-empresas`)

| Aspecto | Valor |
|---------|--------|
| Bundle | `para_empresas` |
| Alias | `/para-empresas` (preservar) |
| Pós-update | fields de conteúdo **vazios**; render público vem de View + blocos |

**Fields a limpar (se preenchidos):** `field_titulo`, `field_text_simple`, `field_text_simple_long`, `field_text_simple_long_2`, `field_imagem`, `field_itens_p`.

**Não apagar:** entidade node, UUID, alias, status publicado.

---

### 2. View `banners` / display `block_para_empresas`

| Aspecto | Valor |
|---------|--------|
| Machine name | `block_para_empresas` |
| Filtros | `status=1`, `type=banners`, `field_local_exibicao=para_empresas` |
| Pager | `none` (até 2+ itens no carrossel) |
| Sort | herda default (`field_peso` ASC, `created` DESC) |
| Empty | sem área (zero resultados → omit Twig) |

**Allowed value novo** em `field.storage.node.field_local_exibicao`:

| value | label |
|-------|--------|
| `para_empresas` | Para Empresas |

---

### 3. Nodes seed `banners` (2 slides)

| Atributo | Slide 1 | Slide 2 |
|----------|---------|---------|
| UUID | `a1b2c3d4-e5f6-4789-a012-34567890abcd` | `b2c3d4e5-f6a7-4890-b123-4567890abcde` |
| `field_local_exibicao` | `para_empresas` | `para_empresas` |
| `field_peso` | 0 | 1 |
| Imagem | asset seed slide-1 | asset seed slide-2 |
| Copy/CTA | **não** no node (Twig) | idem |

---

### 4. Placement banner

| Aspecto | Valor |
|---------|--------|
| Config ID | `default_views_block__banners_block_para_empresas` |
| Plugin | `views_block:banners-block_para_empresas` |
| Região | `banner` |
| Pages | `/para-empresas` |
| Weight | 0 |

**Ajuste legado:** `default_views_block__banners_block_1.pages` = `/para-estudantes` apenas.

---

### 5. Blocos reutilizados (instâncias novas)

| Tipo | UUID conteúdo | Placement ID | Weight | Visibilidade |
|------|---------------|--------------|--------|--------------|
| `nossos_diferenciais` | `c3d4e5f6-a7b8-4901-c234-567890abcdef` | `default_nossosdiferenciaisparaempresas` | 0 | `/para-empresas` |
| `nossa_metodologia` | `d4e5f6a7-b8c9-4012-d345-67890abcdef0` | `default_nossametodologiaparaempresas` | 1 | `/para-empresas` |
| `o_que_fazemos_bt` | `e5f6a7b8-c9d0-4123-e456-7890abcdef01` | `default_oquefazemosparaempresas` | 2 | `/para-empresas` |

Região: `content_full`, tema `default`, `label_display: '0'`.

**Seed copy:** títulos alinhados aos da home (ou tom B2B leve pt-BR); paragraphs/itens espelhando estrutura home se assets/ícones disponíveis; preencher **somente** ausente.

**Isolado (não alterar):** UUIDs/placements da home `b0a1c2d3-…`, `1f9b40ca-…`, `a7c3e9f1-…`.

---

### 6. Benefícios para Empresas (= `diferenciais_quem_somos`)

| Aspecto | Valor |
|---------|--------|
| Bundle | `diferenciais_quem_somos` |
| UUID | `f6a7b8c9-d0e1-4234-f567-890abcdef012` |
| Título seed | `Benefícios para Empresas` |
| Itens | paragraphs `diferencial_simples_p` (ícone + rótulo); quantidade alinhada ao Figma (ex. 6–8) |
| Placement ID | `default_beneficiosparaempresas` |
| Weight | 3 |
| Visibilidade | `/para-empresas` |

**Isolado:** instância Quem Somos UUID `d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80` / `default_diferenciaisquemsomos`.

---

### 7. CTA v1 (instância nova)

| Aspecto | Valor |
|---------|--------|
| Bundle | `cta_v1` |
| UUID | `a7b8c9d0-e1f2-4345-a678-90abcdef0123` |
| Título | `Pronto para contratar os melhores talentos?` |
| Subtítulo | texto curto pt-BR (placeholder editorial no seed) |
| `field_link` | title `Cadastrar Empresa` → `internal:/cadastro/empresa` |
| `field_link_2` | title `Abrir Vaga` → `internal:/painel/empresa/vagas/nova` |
| Placement ID | `default_ctav1paraempresas` |
| Weight | 4 |
| Visibilidade | `/para-empresas` |

**CTO legado:** `default_ctoparaempresas` → `status: false` (conteúdo `f37cafe0-…` permanece, sem render).

---

## Relacionamentos

```text
/para-empresas
├── região banner
│     └── views_block:banners-block_para_empresas
│           └── nodes banners (local=para_empresas) ×2  → imagens do carrossel
│           └── Twig: copy + CTAs fixos
└── região content_full (weights 0→4)
      ├── nossos_diferenciais (UUID c3d4…)
      ├── nossa_metodologia (UUID d4e5…)
      ├── o_que_fazemos_bt (UUID e5f6…)
      ├── diferenciais_quem_somos “Benefícios…” (UUID f6a7…)
      └── cta_v1 (UUID a7b8…)

# Desativado nesta rota:
default_ctoparaempresas (status false)
banners-block_1 (sem /para-empresas)

# Intocado:
home placements diferenciais/metodologia/o_que_fazemos
quem-somos placements diferenciais/cta_v1
```

---

## Regras de validação / fallback

| Estado | Resultado |
|--------|-----------|
| Nó com fields já vazios | limpeza = no-op |
| 0 banners no filtro | omit região hero (sem fatal) |
| 1 banner | carrossel single (sem controles quebrados) |
| Banner sem imagem | omit slide / coluna visual segura |
| Campos vazios nos blocos | omit elementos (padrão 004–006/013/015) |
| Reexecução `11024` | sem duplicar; sem sobrescrever editorial divergente |
| Outra rota | nenhum placement novo aparece |

---

## Config YAML a versionar (checklist)

**Alterar:**
- `field.storage.node.field_local_exibicao.yml`
- `views.view.banners.yml`
- `block.block.default_views_block__banners_block_1.yml`
- `block.block.default_ctoparaempresas.yml`

**Criar:**
- `block.block.default_views_block__banners_block_para_empresas.yml`
- 5× `block.block.default_*paraempresas.yml` (diferenciais, metodologia, oquefazemos, beneficios, ctav1)

**Não criar:** novos block types, paragraph types ou field storages (Benefícios = reuso).

---

## Fora do modelo

- Layout Builder
- Fields de copy em `banners`
- Tipo `beneficios_empresas` (salvo pivot visual na implementação)
- Alteração dos seeds/placements da home ou Quem Somos
