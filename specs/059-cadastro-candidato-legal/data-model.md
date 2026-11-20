# Data Model: Cadastro de Candidato (UX) e Páginas Legais

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-04

Sem content types / field storages novos. Seeds de Node `page` + ajuste de description de field existente + placement de bloco.

## Entidades

### 1. Página Básica — Termos de Uso

| Propriedade | Valor |
|-------------|--------|
| Bundle | `page` |
| UUID seed | `a1b2c3d4-e5f6-4789-a012-3456789abc01` |
| Título | Termos de Uso |
| Alias | `/termos-de-uso` |
| Status | Publicado |
| `body` | HTML placeholder (h2 + parágrafos); **só preenchido se vazio** |
| Idioma alias | `pt-br` |

### 2. Página Básica — Política de Privacidade

| Propriedade | Valor |
|-------------|--------|
| Bundle | `page` |
| UUID seed | `a1b2c3d4-e5f6-4789-a012-3456789abc02` |
| Título | Política de Privacidade |
| Alias | `/politica-de-privacidade` |
| Status | Publicado |
| `body` | HTML placeholder; **só se vazio** |
| Idioma alias | `pt-br` |

### 3. Formulário de Cadastro de Candidato

| Item | Valor |
|------|--------|
| Form ID | `custom_configs_users_candidato_registration_form` |
| Classe | `\Drupal\custom_configs_users\Form\CandidatoRegistrationForm` |
| Rota | `custom_configs_users.candidato_registration` → `/cadastro/candidato` |
| Acesso | anônimo (`_user_is_logged_in: FALSE`) |
| Seções (existentes) | Acesso, Dados Pessoais, Endereço, Formação, complementar, termo, captcha, actions |
| Redirect sucesso + login | `custom_panel.painel_estudante_perfil` → `/painel/estudante/perfil` |

### 4. Aceite — `field_termo` (user)

| Campo | Detalhe |
|-------|---------|
| Storage | `field.storage.user.field_termo` (boolean) — **inalterado** |
| Instance | `field.field.user.user.field_termo` |
| Mudança | `description` com links `<a href="/termos-de-uso" target="_blank" rel="noopener noreferrer">` e equivalente Política |
| Consumo | Candidato lê description como `#title` do checkbox; Empresa **não** usa description (título próprio) |

### 5. Banner de cadastro (apresentação)

| Item | Valor |
|------|--------|
| Tipo | Markup Twig `<picture>` (não entidade) |
| Assets | `themes/custom/default/assets/images/cadastro-candidato/*` |
| Legado | `block.block.default_views_block__banners_block_2` → `status: false` |

### 6. Card do formulário (apresentação)

Container Twig + CSS; não é field Drupal.

## Relacionamentos

```text
alias /termos-de-uso ──────────────► node:page (Termos)
alias /politica-de-privacidade ────► node:page (Política)

rodapé Twig ──href──► mesmas aliases
field_termo.description ──<a>──► mesmas aliases

/cadastro/candidato
  ├── page suggestion → banner picture + card
  ├── form CandidatoRegistrationForm
  │     ├── field_termo (aceite)
  │     ├── captcha (opcional)
  │     └── submit → user + role candidato
  │           └── (login auto) → /painel/estudante/perfil
  └── View banners block_2 (desligado)
```

## Validação

| Regra | Origem |
|-------|--------|
| Não duplicar Node no mesmo alias/UUID | FR-001, FR-004 |
| Não sobrescrever body editorial | FR-004, US6 |
| Alias estrito presente | FR-002 |
| Sem CT/storage novos | Spec Fora |
| Empresa registration intocada | Spec Fora |
| Redirect painel só no ramo login automático | FR-016 / edge cases |

## State transitions

```text
[ausente] --11089--> [page publicada + alias + body seed]
[page + body editorial] --11089--> [no-op body; alias garantido]
[alias órfão/alheio page] --11089--> [reuso Node alvo; sem 2º Node]
[cadastro submit OK + ativo + !verify_mail] --> login --> /painel/estudante/perfil
[cadastro submit OK + verify/pending] --> <front> (inalterado)
```
