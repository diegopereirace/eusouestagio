# Quickstart: Página Para Empresas — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-25

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar; `config_sync_directory = 'config/sync'`.
2. Branch com YAMLs (allowed value, View display, placements), Twig/CSS, `custom_configs_update_11024` e assets seed.
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

1. `drush updatedb:status` sem pendências de `custom_configs`.
2. `/para-empresas` HTTP 200 com composição completa (banner + 5 seções).
3. Zero criação manual de blocos/View no destino.

---

## B) Hero desktop (US1 → SC-001, SC-005)

1. Viewport ≥992px em `/para-empresas`.
2. Duas colunas; inner ≤1200px; CTAs “Cadastrar Empresa” e “Contrate o Estágio Certo”.
3. Avançar carrossel → 2 slides de imagem.
4. Ausência de “Olá, empresa!” / “Por que anunciar aqui!” no HTML do nó.
5. Comparar com Figma (≥95% checklist visual).

---

## C) Composição e exclusividade (US2 → SC-003, SC-009)

1. Ordem: Diferenciais → Metodologia → O Que Fazemos → Benefícios → CTA.
2. Home (`/`): blocos originais 004–006 ainda presentes.
3. `/quem-somos` e `/contato`: sem placements PE.
4. `default_ctoparaempresas` não renderiza.

---

## D) CTAs (US3)

1. Hero “Cadastrar Empresa” → `/cadastro/empresa`.
2. Hero “Contrate o Estágio Certo” → `/painel/empresa/vagas/nova`.
3. CTA final com título seed e botões utilizáveis.

---

## E) Mobile (SC-002)

1. Viewport estreita: copy acima da imagem; seções legíveis; sem scroll horizontal do hero.

---

## F) Editor (US4 → SC-006)

1. Editar imagem de um banner `local=para_empresas` → carrossel atualiza após cache.
2. Editar CTA v1 PE → título/links atualizam.
3. Editar Benefícios (itens ícone+texto) → grade atualiza.

---

## G) Idempotência (US5 → SC-007, SC-008)

```bash
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
```

1. Sem banners/blocos duplicados.
2. Fields do nó limpos não reaparecem com HTML legado se permanecerem vazios.
3. Editorial divergente do seed preservado.

---

## H) Regressão rápida

- Home hero + seções 004–006 OK.
- Quem Somos banner + diferenciais + CTA v1 OK.
- `/para-estudantes` ainda recebe `banners-block_1` se aplicável.
