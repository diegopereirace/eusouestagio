# Quickstart: Validação — Centralização de Banners e Busca da Home

**Feature**: [spec.md](spec.md) | **Plano**: [plan.md](plan.md) | **Data**: 2026-09-09

Guia de validação end-to-end (SC-1 a SC-6). Comandos Drush rodam **dentro do container**: `docker compose exec drupal drush <cmd>` (working_dir `/var/www/html`).

## Pré-requisitos

1. Stack local no ar: `./dev-up.ps1` (ou `docker compose up -d`) — containers `eusouestagio-drupal` + `eusouestagio-postgres` saudáveis.
2. Banco local com dados de produção (script existente `scripts/dump-prod-and-import-local.ps1`) — necessário para validar a migração com conteúdo real de `banner_internas` e block `banner`.
3. `sites/default/settings.php` local contendo: `$settings['config_sync_directory'] = 'config/sync';`
4. Branch com o código da feature; `composer install` já executado (sem dependências novas nesta feature).

## Setup (uma vez — inicialização do Config Management, research R1)

```bash
docker compose exec drupal drush cex -y        # gera baseline completo em config/sync
git add config/sync && git commit -m "chore: baseline config/sync"
```

Validação do gate: `docker compose exec drupal drush cim -y` deve terminar com "nada a importar" (sem diff).

## Cenário 1 — Banners corretos por página e dispositivo (P1 → SC-3)

1. Acesse `/` (home): carrossel exibe **somente** banners com local "Home", ordenados do mais recente ao mais antigo.
2. Acesse `/quem-somos` e `/para-empresas`: exibem **somente** banners "Internas".
3. DevTools → Network, viewport mobile (ex.: iPhone SE): o banner baixa a imagem do style `*_mobile`; em viewport desktop, o style desktop. **Nunca** a imagem errada (inspecionar `srcset`/URL do `<source>`).
4. View Source da home: 1º `<img>` do carrossel com `fetchpriority="high"` e **sem** `loading="lazy"`; demais slides com `loading="lazy"`.
5. Medir LCP da home (Lighthouse) e comparar com a baseline anterior à feature: sem degradação.

## Cenário 2 — Editor autônomo (P1 → SC-1)

1. Logado como editor/admin: `Conteúdo → Adicionar conteúdo → Banners` — enviar imagem desktop (+ mobile), escolher local "Home", publicar. Cronometrar: **< 5 min** até aparecer na home, sem suporte técnico.
2. Despublicar o node → banner some da home após o cache tag invalidar (sem `drush cr` manual).
3. Criar banner sem imagem mobile → front exibe a imagem desktop no mobile (fallback, layout intacto).
4. Criar banner sem local de exibição → não aparece em nenhum local até correção.

## Cenário 3 — Busca pelo hero (P1 → SC-4)

1. Home exibe o hero com título "Encontre seu estágio ideal hoje." e subtítulo institucional — **somente na home** (FR-11): `/para-estudantes` e demais páginas **não** exibem o hero.
2. Digitar um curso em "Qual o seu curso?" (autocomplete sugere termos após 2 caracteres) → Buscar Vagas → chega em `/para-estudantes?cursos=...` com listagem filtrada.
3. Selecionar "Remoto" em "Cidade ou Remoto" → `/para-estudantes?regime={tid}` filtrado estritamente por regime.
4. Combinar os dois → `/para-estudantes?cursos=...&regime=...` com ambos aplicados (FR-9).
5. Submeter vazio → listagem padrão, sem erro. Digitar nome de cidade no campo de regime → nenhum filtro de cidade aplicado (edge case).
6. Total de interações até resultado filtrado: ≤ 2 (SC-4).

## Cenário 4 — Pills (P2)

1. Abaixo da busca: pills "Remoto", "TI", "Administração", "Design", "Marketing".
2. Clicar "Remoto" → navega direto para `/para-estudantes?regime={tid}` filtrado (1 clique, sem botão buscar).
3. Clicar "TI" → `/para-estudantes?cursos=...` filtrado por curso.
4. Apagar um termo no admin de taxonomia e limpar cache → pill correspondente some (não renderiza link quebrado).

## Cenário 5 — Deploy sem dump (P2 → SC-2, SC-5)

Simular a sequência de produção localmente (partindo do banco com legado):

```bash
# Release 1
git pull
docker compose exec drupal drush cim -y     # cria tipo banners, Views, blocos
docker compose exec drupal drush updb -y    # custom_banners_update_11001: migra conteúdo
docker compose exec drupal drush cr
```

Verificações pós-deploy:
1. `drush php:eval "echo \Drupal::entityQuery('node')->accessCheck(FALSE)->condition('type','banner_internas')->count()->execute();"` → **0** (conteúdo migrado).
2. Home e internas renderizam a partir do tipo `banners`; banners migrados preservam ordem/imagens; texto overlay legado visível no `alt` da imagem desktop (inspecionar HTML).
3. `drush config:status` → limpo (sem drift).
4. **Release 2** (tipos legados): `git pull && drush updb -y && drush cim -y && drush cr` → `11002` apaga block_content `banner`; `11003` apaga nodes `banner_internas`; `cim` remove os tipos e configs órfãs.
5. **Campo peso**: incluído no mesmo `updb` (`11004` cria/preenche `field_peso`); `cim` importa sort da View (`field_peso ASC`). Deploy unificado Release 2+: `drush updb -y && drush cim -y && drush cr`.
5. Em nenhum passo houve importação de dump (SC-5).

## Governança (FR-14, FR-15, SC-6)

1. `.cursor/rules/estagio-prd-guardian.mdc` commitado; globs cobrem `config/sync/**` (FR-14).
2. `PRD.md` atualizado cirurgicamente: §3.1 (tipo `banners` + campos), §3.4 (machine names das taxonomias exportadas — fecha gap R4), §3.6 (blocos/Views), §4.3 (`custom_banners`).
3. SC-6: `drush sql:query "SELECT ... "` não encontra os textos do hero em nenhuma tabela de config/field — textos existem só em `block--hero-search.html.twig`.

## Evidências de validação local (2026-09-09)

Release 1 executado no Docker local (`eusouestagio-drupal`):

1. `config/sync` inicializado via `drush cex`; `$settings['config_sync_directory'] = 'config/sync'`.
2. Módulo `custom_banners` habilitado; migração `_custom_banners_migrate_legacy()` (também em `hook_install` + `update_11001`):
   - `published_banner_internas=0`
   - `banners_total=7` (`home=1`, `internas=6`) após dedupe de re-run parcial
   - Migração idempotente por `field_imagem_desktop` fid (`_custom_banners_banners_exists_for_fid`)
3. Blocos: `default_banner` e `default_banner_search_front` desabilitados; `default_custom_banners_hero_search` + `default_views_block__banners_block_home` ativos na home.
4. Sem importação de dump nesta feature (SC-5). Validação visual completa (SC-1/SC-3/SC-4/SC-6) — seguir cenários 1–4 acima no browser.

## Rollback (janela do Release 1)

Código legado permanece dormente no R1 (research R9): recolocar os blocos legados (`default_banner_search_front`, blocos da View/bundle antigos) via admin de blocos restaura o comportamento anterior sem deploy de código. Rollback completo = `git revert` + `cim` + `updb` (o update hook é idempotente; conteúdo migrado não é apagado pelo revert — remover nodes `banners` manualmente se necessário).
