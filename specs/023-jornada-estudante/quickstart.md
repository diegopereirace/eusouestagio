# Quickstart: Jornada do Estudante — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-30

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs (tipos, fields, displays, placement), Twig/CSS, `custom_configs_update_11037` e limite form max 4.
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

1. `drush updatedb:status` sem pendências de `custom_configs` (`11037` aplicado).
2. `/para-estudantes` HTTP 200; jornada **após** benefícios; 4 passos seed (ou editorial existente).
3. Bloco seed UUID `b9c0d1e2-f3a4-4567-b890-1cdef0123456` publicado.
4. Zero criação manual de tipos/placement no destino.

---

## B) Visual Figma + grid (US1 / US2 → SC-001, SC-002)

1. Viewport ≥992px em `/para-estudantes`: título centrado, 4 cards em uma linha, badge 48px, raio 16px, min-height ~202px, badges 1–3 navy `#023C62`, badge 4 laranja `#FD7B1A`, Poppins.
2. Viewport md: 2 cards/linha.
3. Viewport mobile estreito: 1 card/linha; sem scroll horizontal do bloco.
4. Hero (`021`) e benefícios (`022`) permanecem acima; estilos isolados.

---

## C) Editor (US3 → SC-003)

1. Editar título da seção, títulos/descrições dos passos e ordem no painel.
2. Salvar e recarregar `/para-estudantes` → mudanças após cache; badges renumeram; último da lista fica laranja.
3. Tentar 5º passo → sistema impede (UI e/ou validação).

---

## D) Exclusividade e ordem (US4 → SC-004)

1. `/para-estudantes`: hero → benefícios → jornada.
2. `/`, `/para-empresas`, `/quem-somos`: bloco `jornada_estudante` **ausente**.
3. Benefícios (`022`) e hero (`021`) intactos.

---

## E) Idempotência e edge (US5 → SC-005, SC-006, SC-007)

1. `drush updb -y` de novo → sem duplicatas; editorial divergente preservado.
2. Remover todos os passos → cabeçalho ok; sem fatal.
3. Com 2 passos → badges 1–2; o 2º (último) laranja.
4. Campos vazios omitidos no markup.

---

## F) Regressão CSS (SC-008)

Amostrar hero estudantes, benefícios estudantes e “Como funciona” na home: visual inalterado por esta feature.
