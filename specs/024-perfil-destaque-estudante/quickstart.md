# Quickstart: Perfil em Destaque (Estudante) — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-30

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs (tipos, fields, displays, placement), Twig/CSS, assets seed, `custom_configs_update_11038` e limite form max 3.
3. Modelo: [data-model.md](data-model.md). Contratos: [contracts/](contracts/).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync + tema + custom_configs (+ assets) + PRD
# commit / push
```

### Destino (staging / prod / outro local)

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Gates pós-deploy:**

1. `drush updatedb:status` sem pendências de `custom_configs` (`11038` aplicado).
2. `/para-estudantes` HTTP 200; perfil **após** jornada; título + ilustração + 3 itens + CTA seed (ou editorial existente).
3. Bloco seed UUID `c0d1e2f3-a4b5-4678-c901-2def01234567` publicado.
4. Zero criação manual de tipos/placement no destino.

---

## B) Visual Figma + duas colunas (US1 / US2 → SC-001, SC-002)

1. Viewport ≥992px em `/para-estudantes`: ilustração à esquerda, título + lista + CTA à direita; paddings 64/40; max-width 1280px; ícones 20×20; CTA ~208×44; Poppins; título item `#9D4300`; descrição `#45464D`.
2. Viewport &lt;992px: colunas empilham (ilustração acima); sem scroll horizontal do bloco.
3. Viewport mobile estreito: ilustração `.img-fluid` sem estourar; lista/CTA legíveis.
4. Hero (`021`), benefícios (`022`) e jornada (`023`) permanecem acima; estilos isolados.

---

## C) CTA (US3 → SC-009)

1. Clicar em “Completar meu perfil” → navega para `/painel/estudante/perfil` (ou gate de login já vigente).
2. Rótulo do botão = título do `field_link` cadastrado.

---

## D) Editor (US4 → SC-003)

1. Editar título, ilustração, itens (ícone/título/descrição/ordem) e CTA no painel.
2. Salvar e recarregar `/para-estudantes` → mudanças após cache.
3. Tentar 4º item → sistema impede (UI e/ou validação).

---

## E) Exclusividade e ordem (US5 → SC-004)

1. `/para-estudantes`: hero → benefícios → jornada → perfil em destaque.
2. `/`, `/para-empresas`, `/quem-somos`: bloco `perfil_destaque_estudante` **ausente**.
3. Jornada (`023`), benefícios (`022`) e hero (`021`) intactos.

---

## F) Idempotência e edge (US6 → SC-005, SC-006, SC-007)

1. `drush updb -y` de novo → sem duplicatas; editorial divergente preservado.
2. Remover ilustração / todos os itens / CTA → página ok; elementos ausentes omitidos.
3. Com 1–2 itens → lista renderiza só os disponíveis.
4. Campos vazios omitidos no markup.

---

## G) Regressão CSS (SC-008)

Amostrar hero estudantes, benefícios, jornada e “Como funciona” na home: visual inalterado por esta feature.
