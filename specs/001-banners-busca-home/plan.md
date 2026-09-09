# Implementation Plan: Centralização de Banners e Busca da Home

**Branch**: `dev` (workflow do projeto; scaffolding Spec Kit — `.specify/scripts` — ausente neste repo, setup executado manualmente) |
**Date**: 2026-09-09 | **Spec**: [spec.md](spec.md)
**Input**: Especificação em `specs/001-banners-busca-home/spec.md` (clarificações da sessão 2026-09-09 já incorporadas)

## Summary

Centralizar todos os banners no content type `banners` (absorvendo `banner_internas` e o block bundle `banner`), servir imagem correta por viewport via `<picture>`, substituir a busca da home por um bloco hero customizado com textos fixos em código e quick filters (pills), modernizar os filtros da View `vagas` (curso + regime), e estabelecer o fluxo de deploy via Configuration Management + `hook_update_N` — sem dumps de banco. A inicialização do `config/sync` (gap R3 do PRD) é pré-requisito desta feature.

## Technical Context

**Language/Version**: PHP 8.3+ (Drupal 11.4, `core-recommended ^11.4`), Twig, JS vanilla (Drupal behaviors)
**Primary Dependencies**: Drupal core (Views, Image, Taxonomy, Block), tema `default` (Bootstrap Barrio 5.5 / Bootstrap 5), `twig_tweak`. **Nenhuma dependência contrib nova.**
**Storage**: PostgreSQL 16 (Docker local / VPS produção); configuração via Configuration API
**Testing**: Sem suíte PHPUnit no repo — validação via `drush` + cenários manuais do [quickstart.md](quickstart.md)
**Target Platform**: Linux/Apache (VPS prod) e Docker Compose local (`eusouestagio-drupal` + `postgres:16-alpine`)
**Project Type**: CMS Drupal server-rendered (webroot = raiz do repo)
**Performance Goals**: LCP da home sem degradação vs. baseline (SC-3); mobile nunca baixa imagem desktop; 1º banner `fetchpriority="high"`, demais `loading="lazy"`
**Constraints**: Sem alterações em `core/`/`vendor/`; deploy somente com `git pull` + `drush cim` + `drush updb`; textos do hero 100% em código (zero no banco); pt-BR
**Scale/Scope**: 1 content type novo, 2 tipos legados absorvidos, 1 View `banners` reconfigurada, 1 filtro exposto novo na View `vagas`, 1 módulo custom novo (~pequeno), ~6 templates Twig

**Estado atual verificado no repo (fonte da verdade em 2026-09-09):**

- `config/sync` **não existe** e `settings.php` não define `config_sync_directory` (gap R3/R4 do PRD). Toda config (types, Views, image styles) vive só no banco.
- Carrossel da home: block bundle `banner` (`field_image`/`field_image_mobile`), template `block--bundle--banner.html.twig` já usa `<picture>` + image styles `banner_carousel`/`banner_carousel_mobile` + `fetchpriority` — o padrão de renderização é reaproveitado.
- Banners internas: View `banners` (displays `block_1`/`block_2`/`block_3`, templates idênticos) sobre nodes com `field_imagem` (style `banner_internas`) + overlay `field_text_simple_long` via `background-image`.
- Busca da home: bloco `default_banner_search_front` (região `highlighted`) gateado por `custom_configs_block_access()` + theme setting `banner_carousel`; form = exposed form da View `vagas` `page_1` com filtros `nid`, `cursos`, `estados`, `cidade`, `escolaridade` (sem filtro de regime hoje).
- `.htaccess` da raiz já nega download de `*.yml` → `config/sync` na raiz é seguro sob Apache.
- `settings.php.prod` é versionado; `settings.php` não (gitignored) — ajuste local documentado no quickstart.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Não existe `.specify/memory/constitution.md` neste repo; a constituição de fato são as regras de workspace (`estagio-projeto.mdc`, `estagio-backend.mdc`, `estagio-banco-dados.mdc`) + PRD §4.1. Avaliação:

| Gate | Status | Evidência |
|------|--------|-----------|
| SDD — planejar e confirmar antes de codar | ✅ PASS | Este workflow Spec Kit (spec → plan → tasks) |
| Nunca alterar `core/` ou `vendor/` | ✅ PASS | Mudanças apenas em `modules/custom/`, `themes/custom/`, `config/sync/`, `sites/default/settings*.php` |
| Config via Configuration API versionada | ✅ PASS | FR-12; inicialização de `config/sync` é a primeira fase do plano |
| Performance de carregamento | ✅ PASS | FR-4 (`<picture>`, `fetchpriority`, lazy); nenhuma lib JS nova — só `core/drupal.autocomplete` |
| Segurança de dados | ✅ PASS | Conteúdo público; endpoint de autocomplete read-only com permissão `access content`; nenhum dado sensível exposto |
| Clean URLs | ✅ PASS | Nenhuma rota de página nova; destino da busca é `/para-estudantes` existente; endpoint JSON auxiliar sob `/api/` (padrão já usado por `/api/cep/{cep}`) |
| Contrib first / custom mínimo | ✅ PASS | Zero dependências novas; 1 módulo custom pequeno (`custom_banners`) para regra de negócio (migração + hero) |
| PostgreSQL via camada de abstração | ✅ PASS | Migração 100% via Entity API/`entityQuery`, sem SQL cru |

**Resultado**: nenhuma violação — sem entradas em Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/001-banners-busca-home/
├── spec.md                  # existente
├── checklists/requirements.md
├── plan.md                  # este arquivo
├── research.md              # Fase 0 — decisões técnicas
├── data-model.md            # Fase 1 — entidades e mapeamento de migração
├── contracts/               # Fase 1 — contratos de interface (GET form, pills, autocomplete)
│   ├── home-search.md
│   └── cursos-autocomplete.md
└── quickstart.md            # Fase 1 — validação end-to-end
```

### Source Code (mudanças planejadas)

```text
modules/custom/custom_banners/              # NOVO módulo
├── custom_banners.info.yml
├── custom_banners.install                  # hook_update_N: migração de conteúdo legado
├── custom_banners.module
├── src/Plugin/Block/HeroSearchBlock.php    # bloco hero (FR-6..FR-11)
├── src/Controller/CursosAutocompleteController.php  # endpoint JSON p/ autocomplete (FR-7)
├── custom_banners.routing.yml              # /api/cursos/autocomplete
└── templates/block--hero-search.html.twig  # textos fixos + form GET + pills

themes/custom/default/
├── templates/views/views-view--banners--block-home.html.twig   # NOVO: carrossel via View
├── templates/views/views-view-unformatted--banners--block-home.html.twig  # NOVO: slides <picture>
├── templates/views/views-view-field--banners--block-*--nothing.html.twig  # internas: <picture> sem overlay
├── default.theme                       # remove lógica do bundle banner (R2); preprocess do carrossel via View
└── assets/{css,js}/                    # ajustes finos hero/pills (sem libs novas)

config/sync/                            # NOVO diretório (inicializado nesta feature)
├── node.type.banners.yml + field.storage/field.field do novo tipo
├── views.view.banners.yml              # displays block_home + internas filtrados por field_local_exibicao
├── views.view.vagas.yml                # + filtro exposto regime (identifier `regime`)
├── image.style.banner_internas_mobile.yml
├── block placements (hero na highlighted <front>; carrossel na região banner <front>)
└── (R2) remoção de node.type.banner_internas.yml / block_content.type.banner.yml

sites/default/settings.php              # + $settings['config_sync_directory'] (local, não versionado)
sites/default/settings.php.prod         # + $settings['config_sync_directory'] (template versionado)
PRD.md                                  # §3.1, §3.4, §3.6, §4.3 (FR-15 — cirúrgico, via Guardião do Escopo)
```

## Phases

### Fase 0 — Research (concluída)

Todas as decisões técnicas resolvidas em [research.md](research.md): inicialização do config sync, estratégia de migração em 2 releases, carrossel via View, hero como Block plugin, pills sem JS, image styles, autocomplete.

### Fase 1 — Design & Contracts (concluída)

- [data-model.md](data-model.md): entidade `banners`, mapeamento de migração dos 2 tipos legados, regras de validação/fallback.
- [contracts/home-search.md](contracts/home-search.md): contrato GET do hero + pills contra a View `vagas`.
- [contracts/cursos-autocomplete.md](contracts/cursos-autocomplete.md): contrato do endpoint JSON de autocomplete.
- [quickstart.md](quickstart.md): cenários de validação executáveis (SC-1..SC-6).

### Fase 2 — Tasks

Fora do escopo deste comando — gerar com `/speckit-tasks`. Ordem macro esperada:

1. **Release 1**: config sync baseline → novo tipo `banners` + Views + blocos → módulo `custom_banners` (hero + autocomplete) → templates → `hook_update_N` (migração) → validação quickstart → PRD.md.
2. **Release 2** (follow-up pequeno): remoção dos YAMLs legados do sync + limpeza de código morto (preprocess/templates/theme setting/`block_access`).

### Release 2 — remoção estrutural planejada (T035)

Após estabilização do R1 em produção, remover do `config/sync` (e do código dormente):

| Artefato | Ação |
|----------|------|
| `block_content.type.banner.yml` + fields/displays + storages exclusivos | Removido do sync (R2 executado) |
| Placements `default_banner`, `default_banner_search_front` | Removidos |
| Código morto do bundle banner (preprocess/templates/theme setting/`block_access`) | Removido |
| `node.type.banner_internas.yml` + fields/displays/captcha | Removido no Release 2b (`update_11003` + cim) |

Ordem de deploy R2 (block `banner`): `git pull → drush updb -y → drush cim -y → drush cr` (`update_11002` apaga conteúdo antes do `cim` deletar o tipo).
