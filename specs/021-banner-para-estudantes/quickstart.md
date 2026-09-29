# Quickstart: Banner Para Estudantes — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-29

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs (field storage, View display, placements), Twig/CSS, preprocess, `custom_configs_update_11033` e asset em `modules/custom/custom_configs/assets/banner-para-estudantes/`.
3. Modelo: [data-model.md](data-model.md). Contratos: [contracts/](contracts/).

---

## A) Deploy da estrutura (obrigatório)

### Origem (após implementar)

```bash
docker compose exec drupal drush cex -y
git status   # config/sync + tema + custom_configs + PRD
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

1. `drush updatedb:status` sem pendências de `custom_configs` (`11033` aplicado).
2. `/para-estudantes` HTTP 200; hero duas colunas visível; banner legado ausente.
3. Nó seed UUID `c3d4e5f6-a7b8-4901-c234-567890abcdef` publicado com `local=para_estudantes`.
4. Zero criação manual de display/placement no destino.

---

## B) Visual Figma + paddings (US1 → SC-001, SC-002, SC-003)

1. Viewport ≥992px em `/para-estudantes`.
2. Confirmar duas colunas (copy | ilustração), container ≤1280px, padding `64 / 40 / 48 / 40`.
3. Badge, `h1`, subtítulo e CTAs conforme seed/contrato.
4. Banner legado de internas **ausente**.
5. Mobile: empilhamento texto→imagem; sem scroll horizontal do hero.

---

## C) CTAs (US2 → SC-004)

1. “Encontrar minha vaga” → `/para-estudantes#main-content` (listagem/filtros abaixo do hero).
2. “Criar meu perfil” → `/cadastro/candidato`.

---

## D) Editor (US3 → SC-005)

1. Editar título Drupal / imagem do banner com local “Para Estudantes” → mudança na página após cache esperado (&lt;3 min).
2. Publicar segundo banner com mesmo local → display mostra **apenas 1** (pager); layout bipartido intacto.

---

## E) Idempotência e edge (US4 → SC-006, SC-007)

1. `drush updb -y` de novo → sem nós/placements duplicados; editorial divergente preservado.
2. Despublicar o banner seed → página carrega; hero omitido (sem fatal).
3. Sem imagem no nó → copy sem broken image.

---

## F) Exclusividade e regressão (SC-008)

1. `/para-estudantes`: novo hero presente; `block_1` ausente.
2. `/`, `/quem-somos`, `/para-empresas`, `/contato`: novo bloco ausente; amostragem visual inalterada.
