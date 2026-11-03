# Research: Refino Visual "Para Estudantes" — Conformidade com Figma

**Data**: 2026-11-03 | **Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

Todas as decisões priorizam **paridade perceptível com o banner de Para Empresas**, **reuso do Hero Search de `/vagas`**, **cards de benefícios no Figma** e **deploy repetível** (`cim` → `updb` → `cim` → `cr`), sem regressão de `021`–`027` nem de `052`.

---

## R1 — Paridade do carrossel de estudantes com Para Empresas (sem CSS da home)

**Decision**: Manter a library CSS dedicada `banner_para_estudantes`. Alinhar o **comportamento** ao banner PE (pós-052):

1. No `.carousel` de `views-view--banners--block-para-estudantes.html.twig`, acrescentar `banner-carousel banner-carousel--cinematic` (e `banner-carousel--single` quando houver um slide), além das classes já existentes.
2. Atributos do multi-slide: `data-bs-ride="carousel"`, `data-bs-interval="7000"`, `data-bs-touch="true"`, `data-bs-pause="hover"`.
3. Em `default.libraries.yml`, `banner_para_estudantes` passa a depender de `default/bootstrap`, `default/banner_carousel_js` e `default/banner_carousel_cinematic`.
4. **Não** anexar `banner-carousel.css` (layout/tokens da home).

**Rationale**: FR-001 / SC-001. O gap real frente a PE é a ausência do behavior JS (`Drupal.behaviors.bannerCarousel` só casa `.banner-carousel:not(.banner-carousel--single)`), da transição cinematográfica e do pause on hover. O CSS próprio já cobre full-bleed, aspect `1200/433` e overlays.

**Alternatives considered**:
- Copiar um `banner-para-estudantes.js` — rejeitado (duplicação do behavior já extraído em `052`).
- Anexar a library inteira `banner_carousel` (inclui CSS da home) — rejeitado (quebra overlay).
- Deixar o intervalo em `6000` — rejeitado; a diferença é pequena, mas a paridade pedida é com PE (`7000`).

---

## R2 — Breakpoint dos CTAs do banner: 768px, larguras iguais

**Decision**: Em `banner-para-estudantes.css`, separar o que hoje está todo em `@media (max-width: 991.98px)`:

- **≤991.98px**: manter só aspect `16/10` e setas/indicadores menores (como PE). CTAs **continuam** em overlay.
- **≤767.98px**: `.banner-para-estudantes__ctas` `position: static`, `display: flex`, `flex-wrap: wrap`, `gap: 0.75rem`, `width: 100%`. Filhos `> a` com `flex: 1 1 0` e, abaixo de ~380px, `flex: 1 1 100%` (empilhados, largura plena igual). Espelhar o bloco final de `banner-para-empresas.css`.

**Rationale**: FR-001 (paridade perceptível com PE, que já saiu de 992px na feature `052`). O corte atual em 991px faz a landing de estudantes parecer a versão anterior de PE.

**Alternatives considered**: Manter 992px — rejeitado. Container query — sem ganho.

---

## R3 — Hero Search: segundo placement em `highlighted` + reorder do Twig

**Decision**:

- Não criar plugin, template nem library novos. Reutilizar `custom_banners_vagas_hero_search`, Twig `block--vagas-hero-search.html.twig`, library `vagas_hero_search` e o GET para `view.vagas.page_1` (`title`, `cidade`, `cursos`).
- Novo placement de config:
  - id `default_custom_banners_vagas_hero_search_estudantes`
  - UUID `e7f8a9b0-c1d2-4345-b678-0530abcdef01`
  - theme `default`, region `highlighted`, weight `-50`, `label_display: 0`
  - visibility `request_path` pages: `/para-estudantes`
  - **não** alterar `default_custom_banners_vagas_hero_search` (`/vagas`)
- Em `page.html.twig`, quando `content_full_after_banner` (hoje só `/para-estudantes`, setado em `default_preprocess_page`): renderizar `page.highlighted` **entre** `page.banner` e `page.content_full`, e **não** renderizá-lo de novo mais abaixo.
- IDs fixos do Twig (`vagas-hero-title`, etc.) permanecem: há no máximo uma instância por request. O JS de autocomplete continua por `getElementById`.
- Espaçamento: o padding já existente de `.vagas-hero-search` (48px / 40px / 32px) é a margem padrão. Sem sobreposição negativa sobre o banner (os CTAs do banner ocupam o overlay).

**Rationale**: FR-002 / FR-003 / FR-004 / SC-002. A região `highlighted` é o mesmo invólucro de `/vagas` (`aside.container.section`). Weight `-50` fica acima de mensagens (`-6`) e abas (`-5`), então o Hero é o primeiro bloco visível da região. Colocar o bloco em `content_full` com weight negativo evitaria o reorder, mas perderia o container que define a largura em `/vagas`.

**Alternatives considered**:
- Placement em `content_full` weight `-10` — rejeitado (UI deixa de compartilhar o wrapper de `/vagas`).
- Variante de Twig com copy diferente por rota — rejeitado (Assumption: mesma copy).
- Esconder o link “Ver todas” — rejeitado (faz parte do mesmo plugin; a spec pede UI idêntica).

---

## R4 — Benefícios: alinhamento e cor, grade já correta

**Decision**: Não criar tipo, field nem paragraph. Ajustar só apresentação:

- `paragraph--card-icon-text-p.html.twig` é exclusivo de `card_icon_text_p`, referenciado apenas por `beneficios_estudantes`. Trocar `text-center` / `align-items-center` por alinhamento ao início (`text-start` / `align-items-start`) para o ícone ficar no topo à esquerda. Manter `col-12 col-md-6 col-lg-3` (1 / 2 / 4).
- Em `beneficios-estudantes.css`: `.be-card__text` de `#0f172a` para `#45464D`; `.be-card__title` de `font-weight: 600` para `700`. Caixa 64×64 e `object-fit` implícito (`width/height: auto`, max 64) permanecem.
- Fundo claro, borda `rgba(15,23,42,.08)` e sombra leve já existem em `.be-card`. Não reestilizar header além do necessário.

**Rationale**: FR-005 / FR-006 / FR-007 / SC-003. A grade Bootstrap já cumpre 1 coluna &lt;768, 2 em `md`, 4 em `lg`. A lacuna visual é alinhamento, peso do título e o cinza da descrição.

**Alternatives considered**: CSS grid novo no lugar das colunas Bootstrap — rejeitado (a grade já bate com o spec). Escopar o paragraph com um view mode novo — desnecessário enquanto o bundle for exclusivo.

---

## R5 — Copy de benefícios: só legado conhecido

**Decision**: O hook `11077` reutiliza a lógica fill-if-empty já existente (`_custom_configs_seed_beneficios_estudantes` / `_custom_configs_fill_beneficios_estudantes_icons`) e, **além disso**, corrige somente estas strings exatas no bloco UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456`:

| Campo | Legado conhecido (seed `11035`) | Alvo Figma |
|-------|----------------------------------|------------|
| `field_text_simple_long` do bloco | `Nossa plataforma utiliza inteligência para conectar você às vagas e empresas que fazem sentido para o seu perfil e momento.` | `Nossa plataforma utiliza inteligência de dados para conectar seu perfil às melhores vagas do mercado.` |
| `field_text_simple` do 4º card | `Filtros Inteligentes` | `Filtros inteligentes` |

Título do bloco (`Encontre oportunidades que combinam com você.`) já coincide — não reescrever. Descrições dos cards e qualquer outro subtítulo/título **não** entram na lista. Ícones: copiar asset → `public://` só se `field_image` do card estiver vazio. Se `field_itens_lista` estiver vazio, o seed atual (4 cards) preenche.

Se o placement `default_beneficiosestudantes` existir com `status: false`, reativar (legado “desabilitado indevidamente”). Não forçar weight se a região e a visibilidade `/para-estudantes` já estiverem corretas.

**Rationale**: FR-006 / FR-010 / SC-006. O subtítulo vivo no código de seed não é o do Figma; tratá-lo como editorial livre deixaria a landing divergente para quem nunca editou o bloco. Tratar qualquer diferença como overwrite violaria o gate de não sobrescrever copy intencional.

**Alternatives considered**:
- Não tocar no subtítulo — rejeitado (a spec lista a frase do Figma como conteúdo a garantir, e a frase atual é a do seed, não uma edição posterior identificável).
- Atualizar também as descrições dos cards — rejeitado (a spec não traz copy nova de descrição).

---

## R6 — Perfil em destaque: desabilitar placement, manter entidade

**Decision**: `custom_configs_update_11077` carrega o block config `default_perfildestaqueestudante` e, se `status` for true, `setStatus(FALSE)` + save. Reexecução = no-op. Não deletar o `block_content` UUID `c0d1e2f3-a4b5-4678-c901-2def01234567` nem os paragraphs. `drush cex` grava `status: false` em `config/sync/block.block.default_perfildestaqueestudante.yml`.

**Rationale**: FR-008 / SC-004. A spec proíbe remoção definitiva da entidade (reversível). Forçar `false` em toda execução cumpre o cenário de idempotência (“permanece desabilitado”).

**Alternatives considered**:
- Só visibility negate — rejeitado (o placement continuaria “ativo” e um `cim` antigo poderia reexibir).
- `delete()` do placement — rejeitado (perde o UUID de config e dificulta reverter).
- Apagar o bloco de conteúdo — fora de escopo.

---

## R7 — Vão CTA → footer é o shell do node, não padding do CTA

**Decision**: Não mover o placement do CTA (já é o último de `content_full`, weight `4`). Em `cta-v1-para-estudantes.css`, escopo `body.path-para-estudantes`:

- `#main-wrapper`, `#main`, `#main .section`: `padding-top/bottom: 0` e `margin-top/bottom: 0`.
- `#block-default-ctav1paraestudantes` e `.content-full--after-banner > *:last-child`: `margin-bottom: 0`.
- `.site-footer-custom`: `margin-top: 0`.

Não adicionar `border-top: 1px solid #fff`. Não editar `cta-v1.css` nem o CSS de Quem Somos / Para Empresas. O `padding-top: 64px` do footer é padding **interno** da faixa do rodapé (conteúdo do footer), não vão entre as duas faixas: o fundo `#023c62` do footer deve começar no pixel seguinte ao fim do gradiente do CTA.

**Rationale**: FR-009 / SC-005. Em `/para-estudantes` o preprocess marca `content_full_after_banner`, então a ordem no `page.html.twig` é banner → `content_full` (CTA no fim) → `highlighted` → `#main-wrapper` (node `page` vazio + `.section`) → footer. Esse miolo em branco é a “divisória”; o footer escuro em seguida lê como vão escuro. SC-005 pede altura 0 do vão (a linha de 1px é teto, não requisito).

**Alternatives considered**:
- Mover o CTA para depois de `#main` — desnecessário se o main colapsa a 0.
- Borda branca de 1px por precaução — rejeitado até uma medição mostrar que o Figma exige o fio; o alvo é 0px.
- Alterar o gradiente para igualar `#023c62` do footer — fora do pedido (mudaria o CTA).

---

## R8 — `mattpocock/skills`

**Decision**: Não instalar nem bloquear. O pacote não está presente em `.cursor` / skills do usuário. O reuso desta feature segue o que o repo já faz: um plugin de bloco com dois placements, libraries CSS por seção, hook idempotente.

**Rationale**: Assumption da spec (“se ausente, seguir padrões já adotados”).

**Alternatives considered**: `npx skills add mattpocock/skills` durante o plan — rejeitado (instala dependência não pedida como bloqueio e não altera as decisões R1–R7).

---

## R9 — Numeração do hook e receita

**Decision**: `custom_configs_update_11077` (último existente: `11076`). Origem: implementar → `drush updb -y` → `drush cex -y` → `drush cr`. Destino: `cim` → `updb` → `cim` → `cr`. O segundo `cim` consolida o YAML do placement novo e o `status: false` se o hook tiver criado/ajustado config antes do export estar completo; na prática o YAML versionado já deve coincidir com o que o hook garante.

**Rationale**: Gate permanente de deploy. O placement novo **não** existe só no hook: precisa estar em `config/sync`.

**Alternatives considered**: Só hook, sem cex — rejeitado (placement sumiria num `cim` que não conhece o id). Só cex, sem hook — rejeitado (destino com config antiga e banco sem o bloco não fica idempotente, e o legado de copy não mora em YAML).
