# Research: Banner da página Quem Somos

**Data**: 2026-09-14 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## R1 — Reuso da View `banners` vs. bloco/Twig avulso

**Decision**: novo display Block `block_quem_somos` na View `banners` existente, filtrado por `field_local_exibicao = quem_somos`.

**Rationale**: mesmo padrão de `block_home` / `block_1` (feature 001); governança editorial única; cache tags e sorts canônicos já definidos; placement via Configuration Management.

**Alternatives considered**: bloco custom PHP só com imagem hardcoded; paragraph na página `quem_somos` — rejeitados (duplicam CMS; fogem do modelo de banners; dificultam on/off editorial).

## R2 — Terceiro valor em `field_local_exibicao`

**Decision**: acrescentar allowed value `quem_somos` (“Quem Somos”) no storage existente; sem campo novo.

**Rationale**: FR-001; regra de reuso; `list_string` já filtra todos os displays.

**Alternatives considered**: reutilizar `internas` com path-only visibility — rejeitado (mistura conteúdo e placement; editor não distingue Quem Somos de outras internas).

## R3 — Copy e CTAs fixos no Twig

**Decision**: tag, título bipartido, parágrafo e rótulos/destinos dos botões vivem no template; o node `banners` alimenta só a coluna de mídia (e o status publicado).

**Rationale**: Assumptions / padrão 001–002; evita drift editorial da copy aprovada no Figma.

**Alternatives considered**: fields de texto no tipo `banners` — rejeitado pela spec (fora de escopo; risco de edição acidental).

## R4 — Templates Twig

**Decision**:
- `views-view--banners--block-quem-somos.html.twig` — attach da library; sem wrapper se não houver rows.
- `views-view-unformatted--banners--block-quem-somos.html.twig` — layout bipartido + copy fixa + imagem do primeiro row; wrapper `.banner-quem-somos-wrapper`.

**Rationale**: isola suggestion do display; zero resultados omitem a caixa (SC-007); espelha o uso de suggestions por display já existente (`block-home`, `block-1`).

**Alternatives considered**: só override do field `nothing` (estilo `block_1`) — possível, mas o layout bipartido + copy fixa fica mais claro no unformatted; preprocess pesado — YAGNI.

## R5 — Tokens de cor vs. Figma

**Decision**: sob `.banner-quem-somos-wrapper`, definir `--bqs-orange: #FD7B1A` e `--bqs-title: #0F172A`. Não alterar `--brand-orange` global (`#ff8a22`).

**Rationale**: fidelidade ao Figma sem regressão visual em home/rodapé/outros blocos (SC-008). Spec trata `#FD7B1A` / `#FD761A` como o mesmo laranja.

**Alternatives considered**: atualizar `--brand-orange` global para `#FD7B1A` — rejeitado nesta feature (escopo e risco de regressão).

## R6 — Overflow da imagem e alpha PNG

**Decision**: coluna de mídia com posicionamento/margens negativas em `md+`; wrapper com `overflow: visible` controlado; mobile empilha sem overflow horizontal (`overflow-x` na página não causado pelo banner). Imagem: URI do arquivo (ou image style que preserve PNG alpha); **não** forçar fundo opaco atrás da figura.

**Rationale**: efeito “vazar” do Figma; FR-014; asset seed com transparência.

**Alternatives considered**: reusar `banner_internas` sem checar alpha — risco de fundo preto/crop inadequado; clip absoluto na região — pode cortar o rosto.

## R7 — Placement e convivência com `block_1`

**Decision**: novo bloco só em `/quem-somos`; remover `/quem-somos` de `default_views_block__banners_block_1`. Home (`block_home`) intacta. Região `banner` para ambos.

**Rationale**: FR-004/005/006; estado atual já lista `/quem-somos` no legado (verificado em `config/sync`).

**Alternatives considered**: `negate` no legado só para Quem Somos — mais frágil; desabilitar `block_1` globalmente — regressão nas outras internas.

## R8 — Um item, sem carrossel

**Decision**: display com limite efetivo de 1 resultado (pager/items); ordenação herdada `field_peso ASC`, `created DESC`; template renderiza o primeiro row.

**Rationale**: Assumptions da spec; evita JS/carrossel desnecessário.

**Alternatives considered**: carrossel multi-slide — fora de escopo.

## R9 — Hook `custom_configs_update_11012`

**Decision**: update idempotente que:
1. Garante allowed value `quem_somos` no storage se ausente (defensivo pós-`cim`).
2. Seeds node `banners` com UUID fixo + `field_local_exibicao=quem_somos` + imagem do asset do módulo **somente se** não houver banner adequado já publicado/existente para esse local.
3. Não sobrescreve mídia se o node seed já tiver imagem ou se existir outro banner Quem Somos editorial.
4. Defensivo: se placement legado ainda listar `/quem-somos`, remove o path (preferência: config já exportada via `cex`).

Estrutura View/placement **primariamente** via `config/sync` (`cim` antes do `updb`).

**Rationale**: FR-016/018; padrão `11004`–`11011` (helpers + assets em `modules/custom/custom_configs/assets/`).

**Alternatives considered**: só Twig/CSS sem seed — ambiente limpo fica sem imagem; criar display só no hook sem YAML — foge do Configuration Management do projeto.

## R10 — Library CSS dedicada

**Decision**: `default/banner_quem_somos` → `assets/css/banner-quem-somos.css`; attach no template do display.

**Rationale**: mesmo padrão de `banner_carousel` / `hero_search`; evita CSS global poluir; carrega só onde o display renderiza.

**Alternatives considered**: blocos novos em `style.css` — rejeitado (arquivo já grande; menor isolamento).

## R11 — Destinos dos CTAs

**Decision**: “Conheça nossas vagas” → `/para-estudantes`; “Cadastre-se” → `/cadastro/candidato` (paths limpos hardcoded).

**Rationale**: FR-013; rotas canônicas já usadas no rodapé/nav.

**Alternatives considered**: links editáveis no CMS — fora de escopo.

## R12 — PRD

**Decision**: atualização cirúrgica em §3.1.1b (`home` \| `internas` \| `quem_somos`) e §3.6 (display `block_quem_somos`, placement, convivência com `block_1`).

**Rationale**: FR-019; Guardião do Escopo — mudança estrutural de field storage + View + blocos.
