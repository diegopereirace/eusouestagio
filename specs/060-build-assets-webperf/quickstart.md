# Quickstart: Build de assets do tema e agregação CSS/JS (WebPerf)

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-11-04

Comandos Drush: `docker compose exec drupal drush <cmd>` (workdir `/var/www/html`). Em produção: `php vendor/bin/drush.php <cmd>` a partir de `/var/www/html`.

Modelo: [data-model.md](data-model.md). Contratos: [contracts/](contracts/).

## Pré-requisitos

1. Node.js LTS (recomendado ≥ 20) + npm na máquina de desenvolvimento.
2. Stack local no ar; `$settings['config_sync_directory'] = 'config/sync'`.
3. Branch com pipeline Vite + `dist/` + libraries + `custom_configs_update_11090` + `system.performance.yml` commitados.
4. Produção **não** precisa de Node.

---

## A) Pipeline do tema (US1, US4 → SC-001, SC-002, SC-007)

```bash
cd themes/custom/default
npm install
npm run build
```

Gates:

1. Exit 0; pasta `dist/css` e `dist/js` populadas (31 CSS + 12 JS).
2. Artefatos minificados (whitespace reduzido vs `src/`).
3. `default.libraries.yml` referencia `dist/...` (não `assets/css|js` legado).
4. Build produção tipicamente ≤10s após install (SC-002 ≤60s).

Watch / DX (escreve em `dist/` sem minificar à mão; source maps em modo development):

```bash
npm run dev
# alias:
npm run watch
```

Alterar um seletor em `src/css/...` → `dist/` atualiza automaticamente.

Tempo alvo novo contribuidor (só install+build, Node já instalado): ≤15 min (SC-007).

**URLs de imagem no CSS:** paths root-relative `/themes/custom/default/assets/images/...` (imagens permanecem em `assets/images/`, fora do Vite).

---

## B) Deploy / agregação (US2 → SC-003, SC-004, SC-008)

### Origem

```bash
cd themes/custom/default && npm run build && cd ../../..
docker compose exec drupal drush updb -y
docker compose exec drupal drush cex -y
docker compose exec drupal drush cr
```

### Destino

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

Gates:

1. `system.performance` → `css.preprocess` e `js.preprocess` TRUE (`cache.page.max_age` permanece `0`).
2. Home anônima: CSS/JS elegíveis aparecem agregados no HTML/Network (`/sites/default/files/css/` e `/sites/default/files/js/`).
3. Segundo `drush updb -y` → no-op sem erro (hook `11090`: “preprocess já ativos”).

Detalhe: [contracts/deploy-build-assets-webperf.md](contracts/deploy-build-assets-webperf.md), [contracts/system-performance.md](contracts/system-performance.md).

**Exceções `preprocess: false`:** nenhuma library própria do tema precisou de exceção no smoke desta feature (R6).

---

## C) LCP hero (US3 → SC-005)

1. Abrir `/` com banner home ativo.
2. Inspecionar o `<img>` do **primeiro** slide do carrossel `#banner-carousel-home-slides`.
3. Esperado: `loading="eager"` e `fetchpriority="high"`.
4. Slides seguintes podem ter `loading="lazy"`.

**Auditoria 2026-11-04 (implementação):** template `views-view--banners--block-home.html.twig` já conforme (`loop.first` → eager+high). Nenhuma correção Twig necessária.

**Não-candidato:** bloco `custom_banners_hero_search` (busca abaixo do hero) **não** renderiza imagem LCP — sem alteração de atributos de imagem ali.

Se o bloco de banners estiver desligado neste ambiente → N/A para LCP; seguir A/B.

Contrato: [contracts/lcp-hero-home.md](contracts/lcp-hero-home.md).

---

## D) Smoke visual (SC-006)

| Rota | Esperado |
|------|----------|
| `/` | Hero, topo, seções principais intactas; CSS/JS de `dist/` (ou agregado); sem FOUC grave / JS quebrado |
| Uma página com library migrada (ex. `/vagas` ou `/cadastro/candidato`) | Layout e behaviors OK |
| `/libraries/` Bootstrap/FA | Continuam servidos de `/libraries/` (não passaram pelo Vite) |

---

## E) FR-014 — skills `mattpocock/skills`

Revalidação na implementação (`npx skills find webperf` / `npx skills find vite`):

- Resultados: snippets WebPerf genéricos (~200–300 installs) e `cloudflare/skills@web-perf` (alto volume, escopo Cloudflare — **não** aplicável ao pipeline Vite multi-entry + libraries Drupal deste tema).
- Busca `vite`: skills de migração/frameworks alheios; nenhuma skill madura alinhada ao DX `src/`→`dist/` + `default.libraries.yml`.

**Resultado:** nenhuma skill adequada ao DX do pipeline Vite+Drupal desta feature — **não bloqueia**.

---

## F) Checklist rápido de PR

- [ ] `src/` alterado ⇒ `npm run build` e `dist/` no mesmo commit
- [ ] `node_modules/` não commitado
- [ ] `system.performance.yml` com preprocess true
- [ ] Hook `11090` presente e idempotente
- [ ] LCP audit documentado / corrigido

## G) PRD (T031)

Menção cirúrgica no `PRD.md`: **N/A** nesta entrega — mudança de pipeline/tema + config `system.performance` já coberta por spec/quickstart; sem alteração de CT/fields/rotas de produto.
