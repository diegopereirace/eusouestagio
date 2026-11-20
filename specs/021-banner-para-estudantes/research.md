# Research: Banner (Hero) Para Estudantes

**Data**: 2026-09-29 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **deploy repetível** (`cim` → `updb` → `cim` → `cr`), **reuso da View `banners`**, **Clean URL `/para-estudantes`**, e **isolamento CSS** sob o wrapper do hero.

---

## R1 — Display `block_para_estudantes` + valor `para_estudantes`

**Decision**: acrescentar allowed value `para_estudantes` (label “Para Estudantes”) em `field_local_exibicao`; criar display Block `block_para_estudantes` filtrado por esse valor; placement na região `banner` só em `/para-estudantes`.

**Rationale**: FR-001–004; padrão `block_home` / `block_quem_somos` / `block_para_empresas` (001/009/019); editor distingue destino sem misturar com `internas`.

**Alternatives considered**:
- Reusar `internas` + só path visibility — rejeitado (mesmo motivo da 019: misturaria destinos editoriais).
- Filtrar por NID — rejeitado (frágil entre ambientes).

---

## R2 — Copy/CTAs no Twig vs. fields em `banners`

**Decision**: badge, `h1`, subtítulo e dois CTAs vivem no **Twig** do display; o nó `banners` seed alimenta **somente** `field_imagem_desktop` / `field_imagem_mobile` (+ peso + local). Mesmo contrato de Quem Somos (009) e decisão de texto da 019.

**Rationale**: content type `banners` não tem storages de texto/link; FR-009; Assumptions da spec; zero storage novo (`estagio-fluxo-dev.mdc`).

**Alternatives considered**:
- Novos fields de texto/link em `banners` — rejeitado (YAGNI + risco de drift entre displays).
- Paragraph / bloco custom só para copy — rejeitado (duplica arquitetura View já padronizada).

**URLs canônicas dos CTAs**:

| Rótulo | URI |
|--------|-----|
| Encontrar minha vaga | `/para-estudantes#main-content` |
| Criar meu perfil | `/cadastro/candidato` |

**Nota âncora**: a listagem/filtros de vagas é a própria View `vagas` `page_1` em `/para-estudantes` (região de conteúdo). `#main-content` é âncora estável em `page.html.twig` e leva o visitante abaixo do hero na mesma rota. Não criar âncora CSS ad-hoc nesta feature.

---

## R3 — Layout Twig (duas colunas Figma, sem carrossel)

**Decision**:
- `views-view--banners--block-para-estudantes.html.twig` — attach library; omit markup se zero rows/media.
- `views-view-unformatted--…` + preprocess `banner_media` (espelho Quem Somos): wrapper `.hero-estudantes-wrapper` (max-width **1280px**, padding **64px 40px 48px 40px**) > `.row.align-items-center` > esquerda `.col-12.col-lg-6` (badge, h1, lead, CTAs `.d-flex.gap-3`) | direita `.col-12.col-lg-6` (imagem `.img-fluid`, alinhamento `.text-center` / `.text-lg-end`).
- Stack em &lt;992px: texto acima da imagem; sem scroll horizontal.
- Tokens: Poppins/tema; CTA laranja `#FD7B1A` (token local; não alterar `--brand-orange` global); navy / cinza secundário.
- **Sem** Bootstrap Carousel nesta feature (hero único).

**Rationale**: FR-007–011; SC-001/002; Figma Section 01; arquitetura = 019, markup = 009 adaptado (paddings/container distintos).

**Alternatives considered**:
- Reutilizar markup full-bleed de PE — rejeitado (Figma pede duas colunas com copy HTML + ilustração).
- Layout Builder — fora do escopo.

---

## R4 — Desativação do legado `banners-block_1`

**Decision**: após o novo placement, setar `default_views_block__banners_block_1.status = false` (hook + `cex`). **Não** esvaziar `pages`.

**Rationale**: FR-005; hoje o legado só lista `/para-estudantes`. Em Drupal, `request_path.pages` vazio com `negate: false` = **sem restrição** (aparece em todas as rotas) — esvaziar pages seria regressão grave. Desativar o placement remove o hero interno sem efeito colateral.

**Alternatives considered**:
- Remover o YAML do sync — desnecessário; status false basta.
- Manter ativo sem path — rejeitado (ver acima).

---

## R5 — Pager item único + seed + hook `11033`

**Decision**:
- Display: pager tipo `some`, `items_per_page: 1` (primeiro por `field_peso` ASC / desempate `created` DESC herdado).
- Seed: **um** nó `banners` publicado, `local=para_estudantes`, UUID fixo, asset em `modules/custom/custom_configs/assets/banner-para-estudantes/`.
- Hook `custom_configs_update_11033` idempotente: ensure allowed value; ensure display/placement (defensivo); seed + cópia asset → `public://`; desativar `block_1`; **não** sobrescrever editorial divergente; **não** duplicar.
- Ausência de PNG: criar nó sem imagem (coluna visual omitida); não falhar o update.

**Rationale**: FR-006, FR-012; US3 (segundo banner não quebra layout); último hook verificado = `11032` → próximo **`11033`**.

**Alternatives considered**:
- Pager `none` — rejeitado (dois banners publicados quebrariam o layout bipartido).
- Carrossel multi-slide — fora do escopo (spec).

---

## R6 — Deploy, PRD e isolamento

**Decision**:
- Estrutura via admin/API + `drush cex` → `config/sync` (field storage, View, placements).
- Destino: `cim` → `updb` (`11033`) → `cim` → `cr`.
- PRD cirúrgico: §3.1.1b (`para_estudantes` na lista); §3.6 (display `block_para_estudantes`, placement, `block_1` desativado).
- CSS só sob `.hero-estudantes-wrapper` / classes do display; home, Quem Somos, PE, Contato inalterados.

**Rationale**: FR-013–015; `drupal-deploy-configs.mdc`.

**Alternatives considered**: só `updb` sem `cex` — rejeitado (estrutura precisa estar no sync).
