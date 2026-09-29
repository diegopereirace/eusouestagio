# Quickstart: Benefícios Para Estudantes — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-29

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs (tipos, fields, displays, placement), Twig/CSS, `custom_configs_update_11035` e assets em `modules/custom/custom_configs/assets/beneficios-estudantes/`.
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

1. `drush updatedb:status` sem pendências de `custom_configs` (`11035` aplicado).
2. `/para-estudantes` HTTP 200; seção benefícios abaixo do hero; 4 cards seed (ou editorial existente).
3. Bloco seed UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456` publicado.
4. Zero criação manual de tipos/placement no destino.

---

## B) Visual Figma + grid (US1 / US2 → SC-001, SC-002)

1. Viewport ≥992px em `/para-estudantes`: título centrado (~404px), subtítulo (~624px), 4 cards em uma linha, `min-height` ~310px, ícones ≤64px, tipografia Poppins `#0F172A`.
2. Viewport md: 2 cards/linha.
3. Viewport mobile estreito: 1 card/linha; sem scroll horizontal do bloco.
4. Hero (021) permanece acima; estilos isolados (não afetam PE benefícios / home).

---

## C) Editor (US3 → SC-003)

1. Editar título, subtítulo, ícones, textos e ordem dos cards no painel.
2. Salvar e recarregar `/para-estudantes` → mudanças após cache esperado (&lt;5 min).
3. Card sem ícone → título/texto legíveis.

---

## D) Exclusividade e ordem (US4 → SC-004)

1. `/para-estudantes`: bloco presente abaixo do hero.
2. `/`, `/para-empresas`, `/quem-somos`: bloco `beneficios_estudantes` **ausente**.
3. PE “Benefícios para Empresas” (`diferenciais_quem_somos`) intacto.

---

## E) Idempotência e edge (US5 → SC-005, SC-006, SC-007)

1. `drush updb -y` de novo → sem duplicatas; editorial divergente preservado.
2. Remover todos os cards → cabeçalho ok; sem fatal.
3. Campos vazios omitidos no markup.

---

## F) Regressão CSS (SC-008)

Amostrar hero estudantes, home diferenciais e PE benefícios: visual inalterado por esta feature.
