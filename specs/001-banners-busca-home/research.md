# Research: Centralização de Banners e Busca da Home

**Data**: 2026-09-09 | **Feature**: [spec.md](spec.md)

Todas as incógnitas do Technical Context foram resolvidas contra o código real do repo (não há `config/sync` — config vive só no banco; itens dependentes de valores de banco estão marcados como **verificação no export**, não como bloqueio).

---

## R1 — Inicialização do Configuration Management (pré-requisito FR-12)

**Decision**: `$settings['config_sync_directory'] = 'config/sync';` (relativo ao Drupal root = raiz do repo), aplicado em `sites/default/settings.php` (local, não versionado) e `sites/default/settings.php.prod` (template versionado). Primeiro `drush cex` gera o baseline completo, commitado antes de qualquer mudança estrutural da feature.

**Rationale**:
- Webroot = raiz do repo (`composer.json`: `"web-root": "./"`), então o padrão `../config/sync` do `default.settings.php` resolveria para **fora** do repo (`c:\Projetos\config\sync`) — errado aqui.
- Segurança sob Apache: o `.htaccess` da raiz já nega `*.yml` (FilesMatch com `Require all denied`, linha 6) — YAMLs de config não são baixáveis via HTTP em produção.
- `.gitignore` não exclui `config/` → sync versionável sem ajustes.
- Secrets (SMTP, reCAPTCHA, DB) vivem em `settings.php`/`.env`, nunca entram no export (PRD §6.3).

**Alternatives considered**:
- `sites/default/files/config_HASH/sync` (default Drupal): protegido, mas dentro de `files/` (gitignored) e fora do Git — reprovado, viola FR-12.
- Módulo `config_split`/`config_ignore`: dependência nova sem necessidade nesta fase — reprovado (YAGNI).

---

## R2 — Ordem de deploy: migração de conteúdo × remoção de tipos legados (FR-5, FR-13)

**Decision**: estratégia de **2 releases**.

- **Release 1 (esta feature)**: `config/sync` contém o novo tipo `banners` **e ainda mantém** os YAMLs de `banner_internas` / block type `banner`. Deploy: `git pull → drush cim -y → drush updb -y → drush cr`. O `custom_banners_update_11001()` migra o conteúdo legado para `banners` (Entity API) e despublica/desposiciona os blocos legados. Os tipos antigos ficam vazios, porém presentes.
- **Release 2 (follow-up pequeno)**: remove `node.type.banner_internas.yml`, `block_content.type.banner.yml` e fields órfãos do sync + remove código morto (ver R8). Deploy: `git pull → drush cim -y` — os tipos são deletados já sem conteúdo, zero órfãos.

**Rationale**: `drush cim` deleta config ausente do sync **sem checar conteúdo** — se o YAML de `banner_internas` sumir no mesmo release da migração, o `cim` removeria o bundle antes do `updb` rodar, deixando nodes órfãos (bundle inexistente → erros fatais). Inverter a ordem (`updb` antes do `cim`) também falha: o tipo `banners` ainda não existiria durante a migração. Não existe ordem de comando única que seja segura num release só; 2 releases é o padrão comunidade para "migrar conteúdo + remover tipo".

**Alternatives considered**:
- Release único com tipo novo em `config/install` do módulo: cria dupla fonte de verdade para a config (módulo × sync) e drift futuro — reprovado.
- Deletar tipos programaticamente no `hook_update_N` mantendo YAMLs no sync: o próximo `cim` recriaria os tipos — reprovado.
- `config_ignore` para excluir tipos legados do sync: dependência nova para problema que 2 releases resolvem — reprovado.

---

## R3 — Fonte de dados do carrossel da home após absorção do block bundle `banner` (FR-5a)

**Decision**: a View `banners` **existente** ganha um display novo `block_home` (filtro: tipo `banners` + `field_local_exibicao = home` + publicado; sort `created DESC`), colocado como bloco na região `banner` restrito a `<front>`. Os displays `block_1`/`block_2`/`block_3` são reconfigurados para `field_local_exibicao = internas`. O markup do carrossel (já pronto e correto em `block--bundle--banner.html.twig`) migra para `views-view--banners--block-home.html.twig` + `views-view-unformatted--banners--block-home.html.twig`, com preprocess `default_preprocess_views_view__banners()` montando o array `slides` (mesma lógica do atual `default_preprocess_block()`).

**Rationale**: reusa a View `banners` já existente (um só lugar admin para banners), reusa 100% do padrão `<picture>`/image styles/`fetchpriority` já validado, e elimina o block bundle `banner` (FR-5a). Comportamento visual idêntico (assumption da spec: Bootstrap carousel, indicadores, autoplay).

**Alternatives considered**:
- View nova separada `banners_home`: duplica admin; reprovado.
- Bloco custom com `entityQuery` em PHP: reinventa Views (PRD §4.4 proíbe); reprovado.

**Verificação no export**: inventariar onde `block_1`/`block_2`/`block_3` estão posicionados hoje (placements só existem no banco) para replicar/restrições de páginas internas.

---

## R4 — Bloco hero de busca (FR-6 a FR-11)

**Decision**: Block plugin custom `HeroSearchBlock` no módulo novo `custom_banners`, renderizado via template próprio (`block--hero-search.html.twig`) com título/subtítulo **hardcoded em Twig** (FR-6, SC-6). O form é GET puro para `view.vagas.page_1` (`/para-estudantes`) com campos nomeados conforme os identifiers dos filtros expostos (`cursos`, `regime`). Bloco colocado na região `highlighted` restrito a `<front>` (FR-11). Substitui o bloco `default_banner_search_front` (desposicionado no R1; `custom_configs_block_access()` removido no R2).

**Rationale**: textos fixos em template = zero linhas no banco (SC-6); GET nativo para a View = filtros combinados de graça (FR-9), URLs compartilháveis e SEO-friendly; sem AJAX.

**Alternatives considered**:
- Manter o exposed form block da View na home (estado atual): acopla hero à View, impede textos fixos/pills com layout do mockup e exige os alters atuais de redirect — reprovado.
- Form API custom com submit handler + redirect: mais código para o mesmo resultado que um `<form method="get">` estático — reprovado (ponytail).

---

## R5 — Quick filters (pills) (FR-10)

**Decision**: pills são **links `<a>`** para `/para-estudantes?regime={tid}` ("Remoto") e `/para-estudantes?cursos={valor}` (demais), renderizados server-side. TIDs resolvidos por **lookup de termo por nome + vocabulário no preprocess do bloco**, com cache tags de taxonomia; termo inexistente → pill omitida (edge case da spec).

**Rationale**: clique na pill = submissão automática (satisfaz FR-10) com **zero JS**, acessível, cacheável e resiliente. Formato do valor de `cursos` depende do widget do filtro exposto (ver contrato `home-search.md` — verificação no export).

**Alternatives considered**:
- JS que preenche o form e dispara submit: mais código, pior a11y, mesmo resultado — reprovado.
- TIDs hardcoded em config do bloco: quebra entre ambientes (TIDs não são estáveis) — reprovado.

---

## R6 — Filtro de regime na View `vagas` (FR-8) e campo "Qual o seu curso?" (FR-7)

**Decision**:
- Adicionar à View `vagas` `page_1` filtro exposto sobre `field_regime_t` com identifier **`regime`**, widget select (vocabulário pequeno: Presencial/Remoto), combinável com os demais (comportamento padrão de exposed filters). O hero envia `regime={tid}`; o filtro `cidade` existente permanece intacto na listagem (FR-8).
- Campo "Qual o seu curso?" do hero: espelha o filtro exposto `cursos` existente. **Verificação no export**: se o widget for autocomplete-por-nome, o hero envia texto livre; se for select por TID, o hero usa autocomplete com TID oculto. Autocomplete implementado com `core/drupal.autocomplete` + endpoint JSON próprio (R7) como progressive enhancement — sem ele, o campo funciona como texto simples.

**Rationale**: reusa a View e os exposed filters existentes (FR-9); select para regime é o mínimo código correto para 2 valores; autocomplete de curso atende a "interface moderna" (FR-7) sem dependência nova.

**Alternatives considered**:
- Search API / faceting: dependência e infra novas para 2 filtros — reprovado (YAGNI).
- Filtro de regime como texto livre: valores fechados pedem select — reprovado.

---

## R7 — Endpoint de autocomplete de cursos (FR-7)

**Decision**: `GET /api/cursos/autocomplete?q={texto}` no `custom_banners`, retornando JSON `[{value, label}]` de termos do vocabulário de cursos (prefix match, limite 10, permissão `access content`, cache + flood não necessário por ser read-only de termos públicos). JS do tema usa `core/drupal.autocomplete` (já no core, sem lib nova).

**Rationale**: ~40 linhas de controller padrão Drupal; segue o precedente do projeto (`/api/cep/{cep}` — endpoint JSON auxiliar, não API pública, PRD §5.1).

**Alternatives considered**:
- `views` autocomplete endpoint / `entity_autocomplete` do core: exigem contexto de entity reference widget que o hero não tem — reprovado.
- Sem autocomplete (texto puro): não atende o espírito do FR-7 — mantido como fallback degradável, não como alvo.

---

## R8 — Renderização de banners internas sem overlay (FR-5b) e image styles (FR-3, FR-4)

**Decision**:
- Templates `views-view-field--banners--block-{1,2,3}--nothing.html.twig` trocam `background-image` + overlay por **`<picture>` com `<img>` e `alt`** (texto legado migrado para o `alt` — FR-5b), mesmo padrão do carrossel.
- Image styles: reutilizar `banner_carousel` (desktop home), `banner_carousel_mobile` (mobile home), `banner_internas` (desktop internas); **criar `banner_internas_mobile`** (mesmos efeitos de `banner_internas` com largura reduzida — dimensão final definida no export/implementação). Fallback mobile→desktop no preprocess/template (edge case da spec).
- Seção omitida quando não há banners publicados para o local (View vazia não renderiza container — edge case da spec).

**Rationale**: `<img>` real (vs. `background-image`) melhora LCP, `alt`/a11y e permite `loading`/`fetchpriority` — alinhado a SC-3 e à remoção do overlay.

**Alternatives considered**:
- Módulo Responsive Image (`responsive_image` core): adiciona camada de config (breakpoints mapping) sem ganho sobre `<picture>` com 2 sources já em uso — reprovado.
- Manter `background-image`: sem `alt`, sem lazy nativo, pior LCP — reprovado.

---

## R9 — Limpeza de código legado (Release 2)

**Decision**: no R2 remover: `default_preprocess_block()` (lógica banner), `default_theme_suggestions_block_alter()` (branch banner), `default_banner_carousel_enabled()`, theme setting `banner_carousel` + seu form alter, `default_preprocess_html()` (classe `banner-carousel-enabled`), `default_preprocess_block__default_banner_search_front()`, `custom_configs_block_access()`, templates `block--bundle--banner*.html.twig` (3). No R1 esse código fica **dormente** (rollback imediato: recolocar blocos legados).

**Rationale**: deleção sobre adição (ponytail), mas só depois que o R1 provar estabilidade em produção — rollback barato durante a janela de validação.

---

## R10 — Governança PRD (FR-14, FR-15)

**Decision**: FR-14 já está atendido pela regra `.cursor/rules/estagio-prd-guardian.mdc` (globs cobrem `config/sync/**`, `*.module`, `*.install`, `composer.json`; `alwaysApply: false`) — esta feature apenas a commita. FR-15 executado no fim do R1: atualização cirúrgica do PRD (§3.1 tipo `banners`, §3.4 machine names das taxonomias exportadas — fecha gap R4, §3.6 blocos/Views, §4.3 módulo `custom_banners`).

---

## Pendências não bloqueantes (verificar no export do config baseline)

| Item | Onde resolve |
|------|--------------|
| Machine names dos vocabulários cursos/regime (gap R4 do PRD) | tasks de export → data-model/PRD |
| Widget atual do filtro exposto `cursos` (nome vs. TID) | contrato `home-search.md` |
| Placements atuais dos displays `block_1/2/3` da View `banners` | tasks de config |
| Machine name do campo mobile de `banner_internas` (se existir) | mapeamento em data-model.md (fallback cobre ausência) |
| Dimensões exatas de `banner_internas_mobile` | task de image styles |
