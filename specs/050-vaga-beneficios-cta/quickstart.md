# Quickstart: 050-vaga-beneficios-cta

Validação ponta a ponta após implementação. Detalhes de modelo/contratos: [data-model.md](data-model.md), [contracts/](contracts/).

## Orientação de manutenção (FR-014)

| Tema | Regra canônica |
|------|----------------|
| Empresa no detalhe | Só `field_vaga_empresa` (perfil público). `field_empresa_u` fica **oculto** no form; storage permanece para painel/candidaturas/notificações. Role `empresa` grava o UID automaticamente no save (`custom_panel`). |
| Benefícios | Só catálogo taxonomia `beneficios` via `field_vaga_beneficios_t` (checkboxes). Paragraphs / texto livre legado ficam ocultos. |
| CTA final da vaga | Visível **apenas** role `candidato` em páginas `vagas`. Botão = mesma candidatura da sidebar (`js-candidatar-vaga`). |

## Pré-requisitos

- Feature `050-vaga-beneficios-cta` aplicada (código + `config/sync` + hooks `11071`/`11072`/`11075`).
- Drupal + Drush disponíveis (Docker do projeto ou equivalente).
- Usuários de teste: moderador/admin, candidato, empresa (ou admin sem role candidato), anônimo.
- Ao menos uma vaga publicada com empresa pública (`field_vaga_empresa`).

## Setup (origem)

```bash
# Após código + assets WebP commitáveis
drush updb -y
drush cex -y
drush cr
```

Destino / espelho local completo:

```bash
drush cim -y && drush updb -y && drush cim -y && drush cr
```

Smoke automatizado (estrutura):

```bash
drush php:script modules/custom/custom_configs/scripts/verify_050_beneficios_cta.php
```

Esperado: `vocab_beneficios=ok`, terms 7 com ícone, form flags `ok`, CTA visibility `ok`. Revisar `vagas_sem_beneficios_t` se > 0 (correção editorial).

## Cenários de validação

### 1. Form — Empresa e Benefícios (US1 / US2)

1. Editar uma vaga como moderador.
2. **Esperado**: campo de empresa pública (`field_vaga_empresa`) visível; campo legado de conta empresa **ausente**.
3. **Esperado**: checkboxes dos ≥7 benefícios; **sem** editor paragraphs de benefício.
4. Marcar 2–3 benefícios, salvar → detalhe público mostra exatamente esses (ícone + nome).
5. Tentar salvar vaga nova sem benefício → bloqueio de validação.

### 1b. Empresa cria vaga (regressão 050)

1. Logar como role `empresa` e criar/salvar uma vaga.
2. **Esperado**: `field_empresa_u` preenchido com o UID da empresa (painel lista a vaga; notificações não ignoram).
3. Conferir listagem `/painel/empresa/...` e candidaturas da empresa.

### 2. Catálogo seed (US3)

1. `/admin/structure/taxonomy/manage/beneficios/overview` (ou equivalente).
2. **Esperado**: sete nomes da spec, cada um com ícone WebP.
3. Reexecutar `drush updb -y` → sem duplicar termos.

### 3. CTA — audiência e candidatura (US4)

| Sessão | Esperado no detalhe da vaga |
|--------|-----------------------------|
| Anônimo | CTA final **ausente**; sidebar com login |
| Empresa / admin sem `candidato` | CTA **ausente** |
| Candidato, ainda não aplicou | CTA visível; “Candidatar-se Agora” |
| Candidato, já aplicou | CTA visível com “Candidatura Enviada” (disabled) **ou** equivalente sem reenvio |

Candidato elegível: clicar CTA → mesma resposta da sidebar (candidatura criada / UI “Candidatura Enviada” **nos dois botões**); **não** ir para `/cadastro/candidato`.

### 4. Regressões rápidas

- Header/cartão “Sobre a Empresa” ainda usam `field_vaga_empresa`.
- Sidebar Candidatar-se / Salvar / Compartilhar inalterados para o papel correspondente.
- Outros CTAs (`quem-somos`, PE, estudantes) intocados.

### 5. Deploy idempotente (US5)

1. Rodar de novo `cim` → `updb` → `cim` → `cr`.
2. **Esperado**: zero termos duplicados; displays/placement estáveis; formulário e detalhe coerentes.
3. Mensagem do migrate (ou `11075`) lista nids sem benefícios taxonomia, se houver.

## Critério de pronto

Todos os SC-001…SC-007 da [spec](spec.md) verificados manualmente; `verify_050_beneficios_cta.php` sem `FAIL`/`MISSING`; `config/sync` contém vocab/fields/displays/placement com role; PRD §3.1/§3.4/§3.6 atualizado.
