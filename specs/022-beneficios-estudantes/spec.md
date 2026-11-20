# Feature Specification: Benefícios Para Estudantes

**Feature Directory**: `specs/022-beneficios-estudantes`  
**Created**: 2026-09-29  
**Status**: Draft  
**Input do usuário**: Segundo bloco da página **Para Estudantes** (`/para-estudantes`) — seção de benefícios/funcionalidades com título, subtítulo e grade de 4 cards (ícone + título + texto). Modelagem Custom Block + Paragraphs, tipografia Poppins, Bootstrap 5, dimensões de card alinhadas ao Figma (`~258×310`), seed e placement automatizados com `hook_update_N` idempotente, exportação `drush cex` → `config/sync`, templates Twig + CSS com escopo dedicado.

## Escopo

### Inclui

- Tipo de paragraph **Card Ícone e Texto** (`card_icon_text_p`, sufixo `_p` obrigatório) com ícone, título curto e descrição gerenciáveis.
- Tipo de bloco customizado **Benefícios Estudantes** (`beneficios_estudantes`).
- Reutilização de storages canônicos do projeto: ícone via `field_image`; títulos curtos via `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente); descrições via `field_text_simple_long`; lista via storage existente `field_itens_lista` (Entity Reference Revisions → `card_icon_text_p`, cardinality ilimitada ou limitada a 4 — default: ilimitada com seed de 4), com nova field instance no bundle do bloco (pedido verbal `field_cards_lista` → canônico `field_itens_lista`).
- Displays de formulário e visualização (form/view) para o paragraph e o block type.
- Seed idempotente: uma instância do bloco com título, subtítulo e **4 cards** (ícone + título + texto), assets de ícone versionados quando necessário.
- Placement na região `content_full` do tema `default`, visível **somente** em `/para-estudantes`, posicionado como primeira seção de conteúdo full abaixo do Hero (região `banner` da feature `021`).
- Templates Twig do bloco e do paragraph; layout: `.container` com cabeçalho centralizado e grid responsivo (1 coluna no mobile estreito, 2×2 no tablet, 4 colunas no desktop).
- CSS/SCSS com escopo estrito (ex.: `.block-beneficios-estudantes`): título `max-width: 404px`, subtítulo `max-width: 624px`, card com `min-height: 310px` e ícone limitado (`max-height`/`max-width` ≈ 64px), tipografia Poppins, cor de texto principal `#0F172A`.
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11034`, tipicamente `11035`) garantindo tipos/fields/displays, seed via Entity API (`BlockContent::create()` / `entityTypeManager`) e placement.
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento.
- Atualização cirúrgica do `PRD.md` (Views / rota `/para-estudantes` / §3.6) refletindo o novo block type e placement.

### Fora

- Redesign do Hero de `/para-estudantes` (feature `021`).
- Listagem/filtros de vagas da View `vagas` na mesma rota.
- Formulário de cadastro de candidato ou CTAs do hero.
- Reuso da instância “Benefícios para Empresas” (`diferenciais_quem_somos` UUID `f6a7b8c9-…`) — esta feature cria bloco **próprio** `beneficios_estudantes`.
- Alteração do paragraph legado `icone_titulo_descricao` (existe com campos similares; não é o machine name pedido).
- Layout Builder / edição visual de colunas pelo editor.
- Criação de field storages paralelos `field_text_simple_small` ou `field_cards_lista` (proibido — reutilizar canônicos).
- Exibição do bloco em home, `/para-empresas`, `/quem-somos` ou outras rotas.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê os benefícios em Para Estudantes (Priority: P1)

Visitante acessa `/para-estudantes` e, abaixo do hero, vê uma seção com título centralizado, subtítulo contido e uma grade de 4 cards (ícone + título + texto), alinhada ao layout de referência.

**Why this priority**: É o valor principal da feature — comunicar os diferenciais da plataforma na jornada do estudante.

**Independent Test**: Abrir `/para-estudantes` em viewport ≥992px com o bloco publicado e comparar estrutura (cabeçalho + 4 cards) com o layout de referência.

**Acceptance Scenarios**:

1. **Given** o bloco Benefícios Estudantes publicado com título, subtítulo e 4 cards, **When** o visitante abre `/para-estudantes` em desktop, **Then** vê o título em `h2` centralizado, o subtítulo centralizado com largura contida e os 4 cards na ordem cadastrada.
2. **Given** viewport desktop (lg+), **When** observa o grid, **Then** há 4 cards em uma única linha (equivalente a colunas `col-lg-3`).
3. **Given** a seção renderizada, **When** o visitante lê um card, **Then** vê ícone (tamanho controlado), título e texto descritivo centralizados, com altura mínima uniforme entre os cards.

---

### User Story 2 — Visitante tablet/mobile vê grade adaptada (Priority: P1)

Em viewport intermediária o grid exibe 2 cards por linha; em mobile estreito, 1 por linha; permanece legível e sem scroll horizontal.

**Why this priority**: Tráfego mobile/tablet não pode perder a mensagem nem quebrar o layout.

**Independent Test**: Abrir `/para-estudantes` em viewports ≤575.98px e 768–991px.

**Acceptance Scenarios**:

1. **Given** viewport mobile estreita, **When** a página carrega, **Then** o grid exibe 1 card por linha (equivalente a `col-12`).
2. **Given** viewport tablet (md), **When** observa o grid, **Then** há 2 cards por linha (equivalente a `col-md-6`).
3. **Given** qualquer viewport, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pelo bloco.

---

### User Story 3 — Editor gerencia conteúdo sem código (Priority: P1)

Editor autenticado com permissão de blocos edita título, subtítulo, ícones, títulos e textos dos cards e a ordem; as mudanças refletem em `/para-estudantes` sem deploy de código.

**Why this priority**: Copy de marketing muda com frequência; não pode depender de desenvolvimento.

**Independent Test**: Editar o bloco no painel, salvar e recarregar `/para-estudantes`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre o bloco “Benefícios Estudantes”, **Then** consegue editar título, subtítulo e a lista de cards (cada um com ícone, título e texto).
2. **Given** um bloco existente, **When** o editor altera textos, troca ícones, adiciona/remove/reordena cards e salva, **Then** a página pública reflete as mudanças após o cache esperado do site.
3. **Given** o formulário do card, **When** o editor cadastra título e texto sem ícone, **Then** título e texto permanecem legíveis (ícone omitido ou placeholder seguro).

---

### User Story 4 — Bloco aparece só em Para Estudantes, abaixo do hero (Priority: P1)

O bloco não contamina home nem outras internas; aparece exclusivamente em `/para-estudantes`, na região de conteúdo full, visualmente abaixo do Hero da feature `021`.

**Why this priority**: Evita regressão visual e garante a ordem de leitura da página.

**Independent Test**: Comparar `/para-estudantes`, `<front>`, `/para-empresas` e `/quem-somos` após o deploy; confirmar ordem hero → benefícios na página alvo.

**Acceptance Scenarios**:

1. **Given** deploy aplicado, **When** o visitante abre `/para-estudantes`, **Then** vê o bloco Benefícios Estudantes abaixo do hero, na região de conteúdo full.
2. **Given** deploy aplicado, **When** o visitante abre a home, `/para-empresas` ou `/quem-somos`, **Then** o bloco `beneficios_estudantes` **não** é exibido.
3. **Given** a página `/para-estudantes` carregada, **When** observa a ordem vertical, **Then** o hero (região banner) aparece antes da seção de benefícios.

---

### User Story 5 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

Homologação/produção recebem tipos, fields, displays, instância seed, placement e estilos apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/para-estudantes` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y` → `drush updb -y` → (2ª `cim` se placements dependerem de UUIDs seedados) → `drush cr`, **Then** o bloco aparece em `/para-estudantes` com título, subtítulo e 4 cards seed (ou conteúdo editorial já existente).
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de tipos, blocos, paragraphs ou placements (idempotência).
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** block type, paragraph type, field instances/storages, displays e block placement aparecem versionados em `config/sync`.

### Edge Cases

- Título ou subtítulo vazios → omitir o elemento correspondente; não exibir headings/parágrafos vazios.
- Zero cards na lista → renderizar apenas cabeçalho (quando preenchido), sem erro de template.
- Menos de 4 cards → grid preenche com as colunas disponíveis; layout não quebra.
- Mais de 4 cards → todos renderizam na ordem; o layout continua em grid (não truncar silenciosamente).
- Card sem ícone → título e texto permanecem legíveis; ícone omitido ou placeholder.
- Card sem título → ícone e texto (se houver) permanecem; não exibir heading vazio.
- Card sem texto → ícone e título permanecem; não exibir parágrafo vazio.
- Reexecução do hook → no-op seguro; **não** sobrescrever conteúdo editorial divergente do seed.
- Viewport intermediária → aplicar breakpoints Bootstrap (`col-12` / `col-md-6` / `col-lg-3`).
- Convivência com hero (`021`) e View `vagas` na mesma rota → esta feature não os altera.

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de paragraph rotulado “Card Ícone e Texto” com machine name `card_icon_text_p`.
- **FR-002**: Cada card DEVE expor ícone via storage reutilizado `field_image`, título via storage reutilizado `field_text_simple` (pedido verbal `field_text_simple_small` mapeia para o canônico `field_text_simple`; **não** criar storage paralelo) e descrição via storage reutilizado `field_text_simple_long`.
- **FR-003**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “Benefícios Estudantes” com machine name `beneficios_estudantes`.
- **FR-004**: O bloco DEVE expor: título da seção via `field_text_simple`, subtítulo via `field_text_simple_long` e lista ordenável via instance de `field_itens_lista` (pedido verbal `field_cards_lista` → canônico existente; Entity Reference Revisions → `card_icon_text_p`, cardinality ilimitada).
- **FR-005**: Todo o conteúdo exibido (título, subtítulo, ícones, títulos e textos dos cards, ordem) DEVE ser gerenciável no painel; placeholders de template só para ausência de dado.
- **FR-006**: Displays de formulário e visualização do paragraph e do bloco DEVEM incluir os campos necessários para edição e renderização pública.
- **FR-007**: Alterações estruturais (tipos, fields, displays, placement) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação**

- **FR-008**: Todo o markup do bloco DEVE estar encapsulado sob classe dedicada (ex.: `block-beneficios-estudantes`); estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.
- **FR-009**: O wrapper principal do conteúdo DEVE usar container com espaçamento vertical generoso (equivalente a `.container.py-5` ou `.container.py-lg-5`).
- **FR-010**: O título DEVE ser um `h2` centralizado, família tipográfica Poppins, cor principal escura `#0F172A`, com `max-width` aproximado de `404px` e centralização horizontal (`.mx-auto` + classe CSS dedicada).
- **FR-011**: O subtítulo DEVE ser um parágrafo centralizado, mesma família/cor de leitura, com `max-width` aproximado de `624px` (CSS dedicado e/ou `.col-lg-8.mx-auto`).
- **FR-012**: Os cards DEVEM ser renderizados em uma linha/grid com espaçamento superior e centralização (equivalente a `.row.mt-5.justify-content-center` com espaçamento vertical entre linhas).
- **FR-013**: Cada card (paragraph) DEVE ocupar colunas equivalentes a `.col-12.col-md-6.col-lg-3` (1 / 2 / 4 por linha conforme breakpoint).
- **FR-014**: Cada card DEVE alinhar conteúdo ao centro em coluna flex (equivalente a `.text-center.d-flex.flex-column.align-items-center`), com altura uniforme (`.h-100` + `min-height: 310px` no CSS), refletindo a referência de ~258×310 do design.
- **FR-015**: Ícones DEVEM usar `.img-fluid` (ou equivalente) e CSS com limite de dimensão (ex.: `max-height: 64px` / `max-width: 64px`) sob o escopo do bloco/paragraph.
- **FR-016**: Cada card DEVE ter contorno sutil ou sombra leve (equivalente a borda suave / `.shadow-sm`) sem parecer “dashboard de cards” genérico — priorizar fidelidade ao Figma.

**Placement, seed e deploy**

- **FR-017**: O bloco DEVE ser posicionado na região `content_full` do tema `default`, com visibilidade por path limitada a `/para-estudantes`, com weight que o coloque imediatamente abaixo do hero (primeira seção útil de `content_full` nessa rota, salvo composição já existente documentada no plan).
- **FR-018**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11034`, tipicamente `custom_configs_update_11035`) DEVE, de forma **idempotente**, usando Entity API: garantir paragraph type + block type e field instances; garantir form/view displays; criar/seedar a instância do bloco (ex.: `BlockContent::create()`) com UUID fixo e 4 paragraphs quando ausentes; garantir placement/visibilidade.
- **FR-019**: O seed DEVE preservar uploads/edições posteriores do editor; preencher apenas campos vazios/ausentes; ícones seed versionados em `modules/custom/custom_configs/assets/beneficios-estudantes/` (ou path equivalente) quando aplicável — sem commit de `sites/default/files`.
- **FR-020**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` (cim antes do updb; 2ª cim após updb para placements que dependem de UUIDs seedados); ao final do desenvolvimento na origem, `drush cex` para versionar configs geradas/alteradas.
- **FR-021**: A seção relevante do `PRD.md` (rota `/para-estudantes` / blocos) DEVE ser atualizada de forma cirúrgica refletindo o novo block type, fields e placement.

### Key Entities

- **`beneficios_estudantes` (block_content)**: seção de benefícios — título (`field_text_simple`), subtítulo (`field_text_simple_long`), lista (`field_itens_lista` → `card_icon_text_p`, ilimitado).
- **`card_icon_text_p` (paragraph)**: card do grid — ícone (`field_image`), título (`field_text_simple`), texto (`field_text_simple_long`).
- **Bloco placement** (`block.block.*` no tema `default`): região `content_full`, pages `/para-estudantes`, UUID alinhado ao seed.
- **Distinção**: não reutiliza a instância PE de `diferenciais_quem_somos` (“Benefícios para Empresas”); tipo e instância próprios.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a seção em `/para-estudantes` é reconhecível frente ao layout de referência (cabeçalho centralizado com larguras contidas + 4 cards uniformes) em ≥95% dos critérios de checklist visual.
- **SC-002**: Em viewport tablet, 100% das verificações confirmam 2 cards por linha; em mobile estreito, 1 por linha; sem scroll horizontal causado pelo bloco.
- **SC-003**: Um editor atualiza título, subtítulo e cards do bloco em menos de 5 minutos, sem suporte de desenvolvimento.
- **SC-004**: Após o deploy padrão, `/para-estudantes` exibe o bloco abaixo do hero; home, `/para-empresas` e `/quem-somos` amostradas **não** o exibem.
- **SC-005**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cim -y` + `drush cr`, com zero passos manuais no painel.
- **SC-006**: Segunda execução de `drush updb` não cria tipos, blocos, paragraphs ou placements duplicados (idempotência confirmada).
- **SC-007**: Com zero cards ou campos vazios, a página não quebra; elementos ausentes são omitidos de forma segura.
- **SC-008**: Estilos do bloco não alteram visualmente outras seções (amostragem: hero estudantes, benefícios PE / `diferenciais_quem_somos`, home).

## Assumptions

- **Campo curto**: o pedido `field_text_simple_small` mapeia para o storage canônico existente `field_text_simple` (mesmo padrão das features `004`–`021`); não se cria storage paralelo.
- **Lista**: o pedido `field_cards_lista` mapeia para o storage canônico `field_itens_lista` já existente em `block_content`; cria-se apenas nova **instance** no bundle `beneficios_estudantes` apontando para `card_icon_text_p`, cardinality ilimitada (seed com 4 itens).
- **Paragraph dedicado**: embora exista `icone_titulo_descricao` com campos similares, o briefing pede machine name `card_icon_text_p` — cria-se o tipo novo; não se altera o legado.
- **Região `content_full`**: no tema `default`, essa região fica abaixo do hero (`banner`); adequada para a segunda seção da página. Weight relativo será definido na implementação (preferência: primeiro bloco útil de `content_full` exclusivo de `/para-estudantes`).
- **Copy seed do cabeçalho**:
  - Título: “Encontre oportunidades que combinam com você.”
  - Subtítulo: “Nossa plataforma utiliza inteligência para conectar você às vagas e empresas que fazem sentido para o seu perfil e momento.”
- **Copy seed dos 4 cards** (títulos fixos do briefing; textos descritivos razoáveis até o editor refinar com o Figma final):
  1. **Vagas alinhadas** — Oportunidades filtradas pelo seu curso, interesses e objetivos profissionais.
  2. **Modelos de trabalho** — Presencial, híbrido ou remoto — escolha o formato que encaixa na sua rotina.
  3. **Vários segmentos** — Empresas de diferentes áreas e portes, ampliando suas possibilidades de carreira.
  4. **Filtros Inteligentes** — Refine a busca por localização, escolaridade, regime e outros critérios relevantes.
- **Ícones seed**: quatro assets vetorizados/PNG versionados em `modules/custom/custom_configs/assets/beneficios-estudantes/`; o hook copia para `public://` no destino.
- **Hook número**: próximo update após `custom_configs_update_11034` → `11035`, salvo se outro update for commitado antes da implementação.
- **UUID fixo**: a implementação define UUID estável compartilhado entre seed e `block.block.*` exportado (padrão das features anteriores).
- **Convivência**: hero (`021`) e View `vagas` permanecem; esta feature apenas adiciona a seção de benefícios.
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (PHP do `hook_update_N`, Twig do bloco e do paragraph, CSS/SCSS com max-widths 404/624 e `min-height: 310px`) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
