# Feature Specification: Refino Visual "Para Estudantes" — Conformidade com Figma

**Feature Directory**: `specs/053-refino-figma-para-estudantes`  
**Created**: 2026-11-03  
**Status**: Draft  
**Input do usuário**: Refatorar e finalizar `/para-estudantes` alinhando ao Figma: paridade do banner com Para Empresas; reutilizar o Hero Search de `/vagas` abaixo do banner; garantir/refinar o bloco Benefícios para Estudantes (4 cards); remover o bloco obsoleto “Seu perfil em destaque”; corrigir o gap escuro entre CTA final e footer; orquestração automatizada via `hook_update_N` + `drush cex`; aproveitar skills `mattpocock/skills` se disponíveis (reuso de blocos + CSS modular).

## Nota de levantamento (estado atual vs. pedido)

Antes de detalhar o escopo, o levantamento no código/PRD confirmou o que **já existe** e as **lacunas reais**, para evitar recriar o que as features `021`–`027` já entregaram:

| Seção | Já conforme / existente | Lacuna real a corrigir |
|---|---|---|
| Banner (`block_para_estudantes`, região `banner`) | Display dedicado; carrossel full-bleed; library `banner_para_estudantes`; seed `11033` | Garantir **paridade perceptível** com o banner de Para Empresas (comportamento, overlays, setas, CTAs, responsividade) — sem divergência de classes/templates que faça a página de estudantes parecer “versão antiga” |
| Hero Search (`custom_banners_vagas_hero_search`) | Plugin + UI completos em `/vagas` (placement `default_custom_banners_vagas_hero_search`, região `highlighted`, só `/vagas`) | **Ausente** em `/para-estudantes`. Precisa de placement adicional (mesmo plugin) logo abaixo do banner, com espaçamento visual adequado; submissão continua apontando para `/vagas`; formulário/pills idênticos a `/vagas` |
| Benefícios (`beneficios_estudantes`) | Block type + paragraph `card_icon_text_p` + seed `11035` (título/subtítulo e 4 cards) + assets `assets/beneficios-estudantes/icon-*.png` + placement `default_beneficiosestudantes` (weight `0`) | Refino visual Figma: grid 1/2/4 colunas; cards com fundo claro, borda sutil/sombra leve; ícone ~64px no **topo à esquerda**; tipografia (título escuro/negrito; descrição cinza `#45464D`); copy/ícones alinhados ao design se ainda divergirem |
| Perfil em destaque (`default_perfildestaqueestudante`) | Placement ativo em `content_full` weight `2`, só `/para-estudantes` | **Obsoleto no Figma** — desabilitar placement (ou equivalente) para não aparecer na landing |
| Jornada + destaque `vagas` `block_3` | Presentes na composição atual (weights `1` e `3`) | **Fora do pedido de remoção** — permanecem, salvo decisão futura |
| CTA final (`default_ctav1paraestudantes`) | Variante escura isolada; copy e botões ok | Entre CTA e footer há **espaço escuro extra** / divisória branca indesejada; corrigir para encostar no footer com no máximo **uma linha branca fina** (`1px`) ou zero gap |

## Escopo

### Inclui

- **Banner principal**: mesma qualidade de experiência do banner de `/para-empresas` (transição, setas/indicadores quando multi-slide, overlays de CTA, imagem responsiva). Reutilizar padrões já estabilizados em PE sempre que o markup permitir, sem regressão em `/para-empresas` nem na home.
- **Hero Search em `/para-estudantes`**: reutilizar o **mesmo** bloco de plugin já usado em `/vagas` (não criar segundo plugin). Exibir título/subtítulo institucionais, barra de três campos e pills idênticos; envio continua levando o visitante à listagem `/vagas` com os filtros aplicados. Espaçamento vertical adequado em relação ao banner (sobreposição leve ou margem padrão — o resultado visual deve seguir o Figma).
- **Benefícios para estudantes**: reutilizar o block type `beneficios_estudantes` e o paragraph `card_icon_text_p` (campos canônicos de imagem/título/descrição). Garantir instância com:
  - Título: `Encontre oportunidades que combinam com você.`
  - Subtítulo: `Nossa plataforma utiliza inteligência de dados para conectar seu perfil às melhores vagas do mercado.`
  - Quatro cards na ordem: **Vagas alinhadas**, **Modelos de trabalho**, **Vários segmentos**, **Filtros inteligentes** (rótulo curto “Inteligentes” no pedido verbal → usar o título completo do Figma), com ícones versionados em assets e associados aos cards.
  - Layout responsivo em grade (1 coluna mobile → 2 tablet → 4 desktop), cards com padding confortável, borda/sombra sutis, ícone superior-esquerdo dimensionado de forma consistente.
- **Remoção do perfil em destaque**: desabilitar o placement de `default_perfildestaqueestudante` (ou equivalente) na rota `/para-estudantes`, de forma que a seção deixe de aparecer publicamente.
- **CTA final**: eliminar o “vão” escuro entre o bloco “Pronto para dar o próximo passo?” e o footer; permitir no máximo uma borda inferior branca de `1px` se o design exigir separação sutil; não alterar o CTA de Quem Somos / Para Empresas.
- **`hook_update_N` idempotente** em `custom_configs.install` (próximo livre após `11076` → `11077`) orquestrando: desabilitar perfil destaque; garantir placement do Hero Search em `/para-estudantes`; garantir/seed da instância de benefícios (somente preencher ausente/vazio ou corrigir legado conhecido — nunca sobrescrever copy editorial intencional divergente); assets de ícone quando faltarem.
- Export via `drush cex` de placements/config estrutural alterados.
- Atualização cirúrgica do `PRD.md` (§ composição `/para-estudantes`) refletindo a nova ordem e a ausência do perfil destaque.
- Durante plan/implement: consultar skills do pacote `mattpocock/skills` (via `find-skills`) se houver orientação útil para reuso de componentes e CSS modular — sem bloquear a entrega se o pacote não estiver instalado.

### Fora

- Redesign de header, footer, jornada do estudante, cards de vagas (`block_3`) ou páginas `/vagas`, `/para-empresas`, home (exceto **reuso** do Hero Search e paridade visual do banner).
- Criação de novo Content Type / Block Type / Paragraph Type / field storage.
- Autocomplete novo além do já existente no Hero Search de `/vagas`.
- Remoção definitiva da entidade de conteúdo do perfil destaque (apenas ocultar/desabilitar placement nesta feature).
- Alterações em `core/` ou `vendor/`.
- Introdução de pipeline SCSS/Sass/bundler se o tema continuar publicando CSS plano (ver Assumptions).
- **Entrega de código (PHP/Twig/CSS) nesta etapa de especificação** — produzida em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`.

### Ordem visual alvo em `/para-estudantes`

1. Banner principal (região `banner`)
2. Hero Search (mesmo plugin de `/vagas`)
3. Benefícios para estudantes
4. Demais seções já existentes não removidas (jornada, destaque de vagas, etc.)
5. CTA final encostado no footer (sem gap escuro extra)

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o banner com a mesma qualidade de Para Empresas (Priority: P1)

Visitante abre `/para-estudantes` e o banner principal comporta-se e aparece com a mesma qualidade perceptível do banner de `/para-empresas` (transição, setas/indicadores quando aplicável, CTAs legíveis, imagem sem placeholder genérico).

**Why this priority**: Primeira impressão da landing de estudantes; divergência vs. PE/Figma é gap visual imediato.

**Independent Test**: Comparar side-by-side `/para-estudantes` e `/para-empresas` no desktop e em viewport &lt;768px (CTAs, setas, imagem).

**Acceptance Scenarios**:

1. **Given** o display de banners de estudantes publicado, **When** o visitante abre `/para-estudantes`, **Then** o hero exibe a arte correta e CTAs acionáveis, sem placeholder genérico.
2. **Given** viewport desktop, **When** compara com `/para-empresas`, **Then** o comportamento de carrossel/overlays/setas é equivalente na qualidade perceptível (sem regressão em PE).
3. **Given** viewport mobile, **When** observa os CTAs do banner, **Then** permanecem legíveis e utilizáveis, alinhados ao padrão já aceito em PE.

---

### User Story 2 — Visitante busca vagas pelo Hero Search na landing de estudantes (Priority: P1)

Abaixo do banner, o visitante vê o Hero Search com título “Encontre a oportunidade ideal para sua carreira.”, subtítulo institucional, barra de três campos e pills; ao buscar, chega em `/vagas` com os filtros aplicados — igual à experiência de `/vagas`.

**Why this priority**: É o novo ponto de conversão imediata da landing; o Figma posiciona a busca logo após o hero.

**Independent Test**: Abrir `/para-estudantes`, localizar o Hero Search abaixo do banner, preencher campos e submeter; comparar visualmente com o mesmo bloco em `/vagas`.

**Acceptance Scenarios**:

1. **Given** a página carregada, **When** o visitante rola logo abaixo do banner, **Then** vê título, subtítulo, formulário de três campos + “Buscar Vagas” e as pills de curso, com espaçamento coerente com o Figma (sem colidir ilegivelmente com o banner).
2. **Given** o formulário preenchido, **When** submete a busca, **Then** é levado a `/vagas` com os parâmetros de filtro aplicados (mesmo contrato do Hero Search de `/vagas`).
3. **Given** comparação com `/vagas`, **When** inspeciona campos, pills e botão, **Then** a UI é idêntica em estrutura e comportamento (mesmo plugin reutilizado).
4. **Given** a página `/vagas`, **When** a feature é implantada, **Then** o Hero Search existente em `/vagas` **não** regride.

---

### User Story 3 — Visitante vê os quatro benefícios alinhados ao Figma (Priority: P1)

Visitante vê a seção “Encontre oportunidades que combinam com você.” com subtítulo e quatro cards (ícone + título + descrição) em grade responsiva, tipografia e estilo de card alinhados ao design.

**Why this priority**: Bloco central de proposta de valor da landing; o Figma define o visual de referência.

**Independent Test**: Abrir `/para-estudantes` em desktop (≥992px), tablet e mobile; comparar cards, cores e ícones com o Figma.

**Acceptance Scenarios**:

1. **Given** a instância publicada, **When** o visitante abre a seção, **Then** vê o título e o subtítulo exatos do design e quatro cards na ordem: Vagas alinhadas, Modelos de trabalho, Vários segmentos, Filtros inteligentes.
2. **Given** viewport desktop (lg+), **When** observa o grid, **Then** os quatro cards aparecem em uma única linha.
3. **Given** viewport tablet, **When** observa o grid, **Then** há duas colunas; em mobile estreito, uma coluna; sem scroll horizontal causado pela seção.
4. **Given** um card, **When** inspeciona o layout, **Then** o ícone (~64×64, proporção preservada) fica no topo à esquerda; o título está em negrito/escuro; a descrição usa cinza próximo de `#45464D`; o card tem fundo claro e borda/sombra sutis.

---

### User Story 4 — Visitante não vê mais “Seu perfil em destaque” (Priority: P1)

A seção “Seu perfil em destaque” / bloco de perfil mock deixa de aparecer em `/para-estudantes`.

**Why this priority**: Remoção explícita do Figma/pedido; conteúdo obsoleto compete com a nova narrativa.

**Independent Test**: Abrir `/para-estudantes` e confirmar ausência da seção; verificar que o placement está desabilitado após o update automatizado.

**Acceptance Scenarios**:

1. **Given** o update de deploy aplicado, **When** o visitante abre `/para-estudantes`, **Then** a seção de perfil em destaque **não** é renderizada.
2. **Given** reexecução do mesmo update, **When** o hook roda de novo, **Then** o estado permanece desabilitado (idempotente, sem erro).

---

### User Story 5 — Visitante vê o CTA final colado no footer (Priority: P1)

Ao rolar até o fim, a faixa “Pronto para dar o próximo passo?” encontra o footer sem faixa/vão escuro extra entre os dois; no máximo uma linha branca fina de separação.

**Why this priority**: Bug visual evidente no estado atual; quebra a continuidade da faixa escura com o rodapé.

**Independent Test**: Abrir `/para-estudantes`, rolar até o CTA+footer; inspecionar ausência de gap escuro e presença (ou não) de borda branca `1px` conforme o design base.

**Acceptance Scenarios**:

1. **Given** a página carregada, **When** observa a junção CTA → footer, **Then** não há faixa/espaço escuro adicional entre o fim do conteúdo do CTA e o início do footer.
2. **Given** a correção aplicada, **When** compara `/quem-somos` e `/para-empresas`, **Then** os CTAs dessas páginas **não** mudaram de aparência.
3. **Given** viewport mobile e desktop, **When** inspeciona a junção, **Then** o comportamento (sem gap) permanece consistente.

---

### User Story 6 — Deploy automatizado sem passos manuais no admin (Priority: P2)

Em ambiente de destino, após `cim`/`updb`/`cr` conforme a receita do projeto, a composição de `/para-estudantes` fica correta sem intervenção manual no Block Layout.

**Why this priority**: Governança permanente de deploy; produção não pode depender de cliques no admin.

**Independent Test**: Rodar o update em ambiente limpo/reexecução; conferir placements e conteúdo seed; exportar config na origem.

**Acceptance Scenarios**:

1. **Given** ambiente com config estrutural importada, **When** executa o `hook_update_N` da feature, **Then** perfil destaque fica oculto, Hero Search aparece em `/para-estudantes`, benefícios estão garantidos com 4 cards/ícones quando ausentes.
2. **Given** o hook já executado, **When** executa novamente, **Then** não duplica blocos nem sobrescreve copy editorial divergente intencional.
3. **Given** o desenvolvimento na origem, **When** finaliza a feature, **Then** `drush cex` exporta os placements/config estrutural alterados para `config/sync`.

---

### Edge Cases

- Hero Search em `/para-estudantes` e `/vagas` ao mesmo tempo: dois placements do mesmo plugin, sem conflito de ID/config.
- Seed de benefícios já existente com copy correta: hook não sobrescreve títulos/textos editoriais divergentes; só preenche ausências e corrige legado conhecido (ex.: placement desabilitado indevidamente).
- Ícones faltando em `public://` no destino: hook copia a partir dos assets versionados.
- Banner com um único slide: setas/indicadores seguem a mesma regra já usada em PE (não forçar controles vazios).
- Agregação de CSS/cache: após deploy, a junção CTA/footer e o grid de benefícios refletem o CSS novo (cache limpo na receita).
- Conteúdo editorial do perfil destaque permanece no banco, apenas sem placement ativo (reversível se necessário).

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: A landing `/para-estudantes` MUST exibir o banner principal com qualidade visual e comportamental equivalente à de `/para-empresas`, sem regressão nessa última.
- **FR-002**: A landing `/para-estudantes` MUST exibir o Hero Search (mesmo bloco de plugin de `/vagas`) imediatamente abaixo do banner, com título, subtítulo, formulário de três campos, botão “Buscar Vagas” e pills de curso.
- **FR-003**: A submissão do Hero Search a partir de `/para-estudantes` MUST levar o visitante a `/vagas` com os filtros correspondentes aplicados (mesmo contrato de `/vagas`).
- **FR-004**: O Hero Search em `/vagas` MUST permanecer funcional e visualmente intacto após a feature.
- **FR-005**: A seção Benefícios MUST reutilizar o tipo de bloco e o tipo de paragraph já existentes (sem novos tipos/storages) e MUST exibir título, subtítulo e quatro cards com ícone, título e descrição.
- **FR-006**: Os quatro cards MUST aparecer, nesta ordem, como: Vagas alinhadas; Modelos de trabalho; Vários segmentos; Filtros inteligentes — com ícones associados e versionados para deploy.
- **FR-007**: O grid de benefícios MUST ser responsivo: 1 coluna no mobile estreito, 2 no tablet, 4 no desktop; cards com fundo claro, borda/sombra sutis, ícone superior-esquerdo ~64px e descrição em cinza próximo de `#45464D`.
- **FR-008**: O bloco “Seu perfil em destaque” / `default_perfildestaqueestudante` MUST deixar de ser exibido em `/para-estudantes` após o deploy automatizado.
- **FR-009**: O CTA final “Pronto para dar o próximo passo?” MUST encostar no footer sem espaço escuro extra; no máximo uma linha branca de `1px` de separação; MUST NOT alterar CTAs de outras rotas.
- **FR-010**: Um `hook_update_N` idempotente MUST orquestrar desabilitação do perfil destaque, placement do Hero Search em `/para-estudantes` e garantia/seed dos benefícios (preencher apenas ausente/vazio ou legado conhecido).
- **FR-011**: Alterações estruturais de placement/config MUST ser exportáveis via `drush cex` para `config/sync` e versionadas.
- **FR-012**: O `PRD.md` MUST ser atualizado de forma cirúrgica na composição de `/para-estudantes` (ordem dos blocos e remoção do perfil destaque da superfície pública).

### Key Entities

- **Banner Para Estudantes**: slides da View de banners filtrados para a landing; CTAs do hero.
- **Hero Search (plugin reutilizado)**: formulário de busca de vagas + pills; placements distintos por rota (`/vagas` e `/para-estudantes`).
- **Benefícios Estudantes**: bloco de conteúdo com título, subtítulo e lista de cards ícone+texto.
- **Card Ícone e Texto**: item de benefício (ícone, título curto, descrição).
- **Perfil em Destaque (legado)**: bloco a ocultar na landing (entidade pode permanecer).
- **CTA Final Para Estudantes**: faixa escura de conversão imediatamente antes do footer.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão lado a lado, 100% dos revisores consideram o banner de `/para-estudantes` equivalente em qualidade ao de `/para-empresas` (transição, CTAs, ausência de placeholder).
- **SC-002**: Em &lt;10 segundos após carregar `/para-estudantes`, um visitante localiza o Hero Search abaixo do banner e completa uma busca que o leva a `/vagas` com filtros aplicados.
- **SC-003**: Em desktop, os 4 cards de benefícios aparecem em uma linha; em tablet, 2×2; em mobile, empilhados — sem scroll horizontal causado pela seção.
- **SC-004**: 100% das cargas manuais a `/para-estudantes` após o deploy **não** exibem a seção de perfil em destaque.
- **SC-005**: Na junção CTA→footer, a altura do vão escuro medido entre o fim do CTA e o início do footer é **0px** (exceto eventual borda branca de 1px).
- **SC-006**: Reexecução do update automatizado não cria blocos duplicados nem altera copy editorial já divergente do seed.
- **SC-007**: Deploy em destino sem passos manuais no Block Layout: após a receita `cim`/`updb`/`cr`, a composição alvo está publicada.

## Assumptions

- O block type `beneficios_estudantes`, o paragraph `card_icon_text_p`, os assets `icon-1`…`icon-4` e o seed da feature `022` já existem; esta feature **refina e garante**, não modela do zero.
- O título do 4º card no Figma é **“Filtros inteligentes”** (pedido verbal “Inteligentes” = abreviação).
- Jornada do estudante e o destaque de 3 vagas (`block_3`) **permanecem** na página; só o perfil destaque é removido da superfície pública.
- O Hero Search em `/para-estudantes` usa o **mesmo** plugin e o **mesmo** contrato de query string de `/vagas`; não há copy/admin diferentes por rota nesta versão.
- Região/weight exatos do novo placement do Hero Search serão definidos no plan (ex.: `highlighted` ou `content_full` com weight negativo), desde que o resultado visual fique logo abaixo do banner.
- O tema continua com CSS modular por library/arquivo plano (sem exigir novo bundler SCSS); “SCSS” no pedido do usuário lê-se como estilos modulares do tema.
- Próximo `hook_update_N` livre: `11077` (último observado: `11076`).
- Pacote `mattpocock/skills`: verificar disponibilidade via `find-skills` no plan/implement; se ausente, seguir padrões já adotados no repo (reuso de block plugins + CSS com escopo por bloco).
- Receita de destino padrão: `cim` → `updb` → `cim` → `cr` (segunda passada de `cim` se placements dependerem de UUIDs seedados).
- Esta especificação **não** entrega código; a implementação segue `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`.
