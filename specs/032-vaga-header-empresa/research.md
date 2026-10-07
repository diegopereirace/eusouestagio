# Research: Cabeçalho do Detalhe da Vaga — Perfil Empresa (032)

**Data**: 2026-10-07 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **reuso de `field_imagem`**, **header alimentado por node Empresa**, **coexistência com `field_empresa_u`**, e **deploy repetível** (`cim` → `updb` → `cim` → `cr`).

---

## R1 — Content Type `empresa` vs continuar com User

**Decision**: criar node type `empresa` (título = nome; logo = `field_imagem`).

**Rationale**: FR-001/FR-004; perfil público desacoplado da conta `user` role `empresa`; Assumption da spec.

**Alternatives considered**:
- Continuar `field_empresa_u` + `user_picture` / `field_nome_fantasia` — rejeitado (viola CT dedicado).
- Media entity para logo — rejeitado (YAGNI; padrão do projeto é Image field).

---

## R2 — Reuso de `field_imagem` para logo

**Decision**: instance `node.empresa.field_imagem` sobre storage existente `node.field_imagem`.

**Rationale**: gate de reuso; storage já usado em contato/depoimento/quem_somos; FR Assumption.

**Alternatives considered**: criar `field_logo` — rejeitado (storage paralelo).

---

## R3 — `field_vaga_empresa` (ER) vs reutilizar campo legado

**Decision**: storage novo `node.field_vaga_empresa` → node bundle `empresa`, cardinality 1.

**Rationale**: FR-002; target type diferente de `field_empresa_u` (user).

**Alternatives considered**:
- Alterar target de `field_empresa_u` para node — rejeitado (quebra painel/views/candidaturas).
- Campo genérico existente — nenhum ER node→empresa disponível.

---

## R4 — Carga horária: campo novo vs `field_horarios`

**Decision**: storage novo `string` `field_vaga_carga_horaria`; pill do header usa este valor. Remover `field_horarios` da UI do **header** (turno Manhã/Tarde/Noite permanece no modelo para resumo/form se já existir).

**Rationale**: FR-003 exemplo “30h semanais”; `field_horarios` é list_string de turno — semântica incompatível. `field_text_simple` já é bolsa.

**Alternatives considered**:
- Reusar `field_horarios` — rejeitado (valores errados).
- Reusar `field_text_simple` com label “Carga” — rejeitado (colisão com bolsa).

---

## R5 — Split Twig: header (node) vs “Sobre a Empresa” (user)

**Decision**: variáveis distintas — `empresa_node` / logo do `field_imagem` no header; manter bloco “Sobre a Empresa” em `field_empresa_u` (031) até migração futura.

**Rationale**: Escopo Fora = sem migração em massa; FR-014 mantém legado; edge case: header prioriza `field_vaga_empresa`.

**Alternatives considered**:
- Ambos os blocos só no node — rejeitado (CT empresa sem “sobre” nesta feature).
- Header dual-fallback user se node vazio — rejeitado pela edge case explícita (não obrigatório ler legado no header).

---

## R6 — Visual do card e pills (tokens Figma)

**Decision**: card branco, radius ~16px, borda cinza sutil (`#E5E7EB` / token `--vd-border` próximo), padding generoso; logo 96×96 `object-fit: cover`, radius 12px; pills fundo azul muito claro (unificar — bolsa no header sem laranja se Figma unifica as quatro); subtítulo inline com verificado + separador + pin.

**Rationale**: FR-009; CSS já em `vagas-detalhe.css` — evoluir classes `__header*`.

**Alternatives considered**: library CSS nova só do header — rejeitado (YAGNI; mesmo escopo `.vaga-detalhe`).

---

## R7 — Tempo relativo “Postado há…”

**Decision**: helper no `default_preprocess_node__vagas` baseado em `created`: &lt;1 hora → “menos de 1 hora”; &lt;24h → `N hora` / `N horas`; senão `N dia` / `N dias`. Twig: `Postado há @time` ou string já completa.

**Rationale**: FR-008 + US3; `formatTimeDiffSince` atual não garante pluralização pt-BR nem mensagem &lt;1h.

**Alternatives considered**: campo editorial “postado há” — rejeitado (FR-008). Timeago JS — rejeitado (sem JS novo; SSR suficiente).

---

## R8 — Badge verificado

**Decision**: ícone estático (Font Awesome já usado no tema, ex. `fa-circle-check`) sempre que houver empresa node publicada no header.

**Rationale**: Assumption da spec — sem flag CMS nesta versão.

**Alternatives considered**: field booleano `field_empresa_verificada` — rejeitado (YAGNI / fora).

---

## R9 — Seed + asset + hook `11049`

**Decision**:
- Empresa UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123`, title “EcoConstrutora”, logo via asset versionado → `public://`.
- Vaga UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234`, title “Engenheiro Civil”, `field_vaga_empresa` → empresa seed, carga “30h semanais”, regime/bolsa/cidade/estado preenchidos.
- Hook `custom_configs_update_11049` idempotente (load UUID; no-op se existir; não sobrescrever editorial divergente).

**Rationale**: FR-011/US4; padrão assets do módulo (nunca `sites/default/files` no Git).

**Alternatives considered**: só ensure de config sem seed — rejeitado (SC-004/SC-005). Atualizar vaga seed antiga da 030/031 in-place sem UUID novo — aceitável se já houver vaga demo estável; preferir UUIDs fixos novos documentados no data-model para rastreio.

---

## R10 — Receita de deploy

**Decision**: destino `cim` → `updb` (`11049`) → `cim` → `cr`. Origem: `drush cex` após ensures/admin.

**Rationale**: runbook permanente; CT/fields estruturais no sync; seed no hook.

**Alternatives considered**: só `updb` sem cex — rejeitado (FR-012).

---

## R11 — Colisão de nome `empresa` (role vs CT)

**Decision**: manter machine name `empresa` para o Content Type (FR); role `user.role.empresa` permanece.

**Rationale**: namespaces Drupal distintos (`node.type` vs `user.role`); PRD deve deixar explícito “CT Empresa” vs “role empresa”.

**Alternatives considered**: `perfil_empresa` / `empresa_publica` — rejeitado (diverge FR-001).
