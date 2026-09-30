# Feature Specification: Jornada do Estudante

**Feature Directory**: `specs/023-jornada-estudante`  
**Created**: 2026-09-30  
**Status**: Draft  
**Input do usuário**: Terceira seção da página **Para Estudantes** (`/para-estudantes`) — bloco **“Sua jornada até o sucesso”** (Section 03: Student Journey) com título e grade de 4 passos numerados (título + descrição). Modelagem Custom Block `jornada_estudante` + Paragraph `passo_jornada_p`, tipografia Poppins, Bootstrap 5, tokens Figma (container 1280px, paddings 64/40, badge 48px, card min-height ~202px, raio 16px, passo final laranja), número/cor do badge via `loop.index` / `loop.last` (sem campo numérico no banco), seed e placement automatizados com `hook_update_N` idempotente, exportação `drush cex` → `config/sync`, templates Twig + CSS com escopo dedicado.

## Escopo

### Inclui

- Tipo de paragraph **Passo da Jornada** (`passo_jornada_p`, sufixo `_p` obrigatório) com título curto e descrição gerenciáveis.
- Tipo de bloco customizado **Jornada do Estudante** (`jornada_estudante`).
- Reutilização de storages canônicos do projeto: títulos curtos via `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente); descrições via `field_text_simple_long`; lista via storage existente `field_itens_lista` (Entity Reference Revisions → `passo_jornada_p`, **cardinalidade 4**), com nova field instance no bundle do bloco.
- Displays de formulário e visualização (form/view) para o paragraph e o block type.
- Seed idempotente: uma instância do bloco com título “Sua jornada até o sucesso” e **4 passos** com copy exato do Figma.
- Número da etapa (1–4) e cor diferenciada do último passo **inferidos no Twig/CSS** (`loop.index` / `loop.last` ou `:last-child`) — **sem** campo numérico ou de cor no banco.
- Placement na região `content_full` do tema `default`, visível **somente** em `/para-estudantes`, com weight imediatamente **após** o bloco Benefícios Estudantes (`default_beneficiosestudantes`, weight `0` → jornada tipicamente weight `1`).
- Templates Twig do bloco e do paragraph; layout: container max-width 1280px, paddings 64px/40px, título centralizado, grid responsivo (1 / 2 / 4 colunas).
- CSS/SCSS com escopo estrito (ex.: `.block-jornada-estudante`): badge 48×48, raio do card 16px, min-height ~202px, badges 1–3 navy `#023C62`, badge último laranja `#FD7B1A` (ou `#FF8C78`), tipografia Poppins.
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11036`, tipicamente `11037`) garantindo tipos/fields/displays, seed via Entity API e placement.
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento.
- Atualização cirúrgica do `PRD.md` (rota `/para-estudantes` / §3.6) refletindo o novo block type e placement.

### Fora

- Redesign do Hero (`021`) ou da seção Benefícios Estudantes (`022`).
- Listagem/filtros de vagas da View `vagas` na mesma rota.
- Formulário de cadastro de candidato ou CTAs do hero.
- Campo de número, cor ou ícone editável no CMS para os passos (número/cor são apresentação).
- Criação de field storages paralelos `field_text_simple_small` (proibido — reutilizar canônico `field_text_simple`).
- Exibição do bloco em home, `/para-empresas`, `/quem-somos` ou outras rotas.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê a jornada em Para Estudantes (Priority: P1)

Visitante acessa `/para-estudantes` e, após a seção de Benefícios, vê “Sua jornada até o sucesso” com 4 cards numerados (1–4), o último com badge laranja, alinhados ao layout de referência.

**Why this priority**: Comunica o fluxo de uso da plataforma — valor central da Section 03.

**Independent Test**: Abrir `/para-estudantes` em viewport ≥992px com o bloco publicado e comparar estrutura (título + 4 passos numerados) com o layout de referência.

**Acceptance Scenarios**:

1. **Given** o bloco Jornada do Estudante publicado com título e 4 passos, **When** o visitante abre `/para-estudantes` em desktop, **Then** vê o título centralizado e os 4 cards na ordem cadastrada, cada um com badge numerada 1–4, título do passo e descrição.
2. **Given** viewport desktop (lg+), **When** observa o grid, **Then** há 4 cards em uma única linha (equivalente a colunas `col-lg-3`).
3. **Given** a seção renderizada, **When** observa as badges, **Then** os passos 1–3 usam fundo navy e o passo 4 (último) usa fundo laranja.

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

Editor autenticado com permissão de blocos edita o título da seção, títulos e descrições dos passos e a ordem (até 4 itens); as mudanças refletem em `/para-estudantes` sem deploy de código. Números e cores das badges continuam derivados da posição na lista.

**Why this priority**: Copy de marketing muda com frequência; não pode depender de desenvolvimento.

**Independent Test**: Editar o bloco no painel, salvar e recarregar `/para-estudantes`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre o bloco “Jornada do Estudante”, **Then** consegue editar o título da seção e a lista de até 4 passos (cada um com título curto e descrição).
2. **Given** um bloco existente, **When** o editor altera textos, reordena passos e salva, **Then** a página pública reflete as mudanças após o cache esperado; as badges renumeram conforme a nova ordem.
3. **Given** o formulário do bloco, **When** o editor tenta adicionar um 5º passo, **Then** o sistema impede (cardinalidade 4).

---

### User Story 4 — Bloco aparece só em Para Estudantes, após Benefícios (Priority: P1)

O bloco não contamina home nem outras internas; aparece exclusivamente em `/para-estudantes`, na região de conteúdo full, visualmente **após** Benefícios Estudantes e abaixo do Hero.

**Why this priority**: Evita regressão visual e garante a ordem de leitura (Hero → Benefícios → Jornada).

**Independent Test**: Comparar `/para-estudantes`, `<front>`, `/para-empresas` e `/quem-somos` após o deploy; confirmar ordem hero → benefícios → jornada na página alvo.

**Acceptance Scenarios**:

1. **Given** deploy aplicado, **When** o visitante abre `/para-estudantes`, **Then** vê o bloco Jornada do Estudante na região `content_full`, imediatamente após Benefícios Estudantes.
2. **Given** deploy aplicado, **When** o visitante abre a home, `/para-empresas` ou `/quem-somos`, **Then** o bloco `jornada_estudante` **não** é exibido.
3. **Given** a página `/para-estudantes` carregada, **When** observa a ordem vertical, **Then** hero → benefícios → jornada.

---

### User Story 5 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

Homologação/produção recebem tipos, fields, displays, instância seed, placement e estilos apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/para-estudantes` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y` → `drush updb -y` → (2ª `cim` se placements dependerem de UUIDs seedados) → `drush cr`, **Then** o bloco aparece em `/para-estudantes` com título e 4 passos seed (ou conteúdo editorial já existente).
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de tipos, blocos, paragraphs ou placements (idempotência).
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** block type, paragraph type, field instances/storages, displays e block placement aparecem versionados em `config/sync`.

### Edge Cases

- Título da seção vazio → omitir o heading; não exibir heading vazio.
- Zero passos na lista → renderizar apenas cabeçalho (quando preenchido), sem erro de template.
- Menos de 4 passos → grid preenche com as colunas disponíveis; badges numeram 1…N; o último item da lista recebe estilo “último” (laranja).
- Tentativa de mais de 4 passos → impedida pela cardinalidade do field.
- Passo sem título → descrição e badge (se houver) permanecem; não exibir heading vazio.
- Passo sem descrição → título e badge permanecem; não exibir parágrafo vazio.
- Reexecução do hook → no-op seguro; **não** sobrescrever conteúdo editorial divergente do seed.
- Viewport intermediária → aplicar breakpoints Bootstrap (`col-12` / `col-md-6` / `col-lg-3`).
- Convivência com hero (`021`), benefícios (`022`) e View `vagas` na mesma rota → esta feature não os altera.

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de paragraph rotulado “Passo da Jornada” com machine name `passo_jornada_p`.
- **FR-002**: Cada passo DEVE expor título via storage reutilizado `field_text_simple` (pedido verbal `field_text_simple_small` mapeia para o canônico `field_text_simple`; **não** criar storage paralelo) e descrição via storage reutilizado `field_text_simple_long`.
- **FR-003**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “Jornada do Estudante” com machine name `jornada_estudante`.
- **FR-004**: O bloco DEVE expor: título da seção via `field_text_simple` e lista ordenável via instance de `field_itens_lista` (Entity Reference Revisions → `passo_jornada_p`, **cardinalidade 4**).
- **FR-005**: O número da etapa e a cor da badge do último passo DEVEM ser derivados da posição na lista na apresentação (Twig `loop.index` / `loop.last` ou CSS `:last-child`); **não** persistir campo numérico ou de cor no banco.
- **FR-006**: Todo o conteúdo exibido (título da seção, títulos e descrições dos passos, ordem) DEVE ser gerenciável no painel; placeholders de template só para ausência de dado.
- **FR-007**: Displays de formulário e visualização do paragraph e do bloco DEVEM incluir os campos necessários para edição e renderização pública.
- **FR-008**: Alterações estruturais (tipos, fields, displays, placement) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação**

- **FR-009**: Todo o markup do bloco DEVE estar encapsulado sob classe dedicada (ex.: `block-jornada-estudante`); estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.
- **FR-010**: O wrapper principal do conteúdo DEVE respeitar largura máxima de `1280px` e paddings aproximados Top/Bottom `64px`, Left/Right `40px` (tokens Figma da seção). A seção DEVE ter fundo full-bleed `#EFF4FF` e `margin: 0` no bloco raiz.
- **FR-011**: O título DEVE ser um `h2` centralizado (equivalente a `.text-center`), família tipográfica Poppins, negrito, cor `#000000` ou `#0F172A`, altura visual aproximada de `38px`.
- **FR-012**: Os passos DEVEM ser renderizados em grid com espaçamento e centralização (equivalente a `.row.justify-content-center.g-4.mt-4`).
- **FR-013**: Cada passo (paragraph) DEVE ocupar colunas equivalentes a `.col-12.col-md-6.col-lg-3` (1 / 2 / 4 por linha conforme breakpoint).
- **FR-014**: Cada card DEVE alinhar conteúdo ao centro em coluna flex (equivalente a `.text-center.d-flex.flex-column.align-items-center`), fundo `#FFFFFF`, `border-radius: 16px`, padding interno ~`24px` (ou `16px`), sombra sutil, `min-height` aproximado de `202px`, largura fluida no grid (referência Figma ~`258px`).
- **FR-015**: Cada card DEVE exibir badge circular `48px × 48px` (`border-radius: 50%`) com o número da posição em branco, negrito, ~`18px`, centralizado via flex.
- **FR-016**: Badges dos passos que não são o último DEVEM usar fundo navy `#023C62`; a badge do **último** passo da lista DEVE usar fundo laranja `#FD7B1A` (aceitável `#FF8C78` se o design system do tema já padronizar esse tom).
- **FR-017**: Título do passo: Poppins semibold/bold, cor `#000000`, espaçamento superior ~`16px` após a badge. Descrição: Poppins regular, ~`14px`, cor `#45464D` (ou cinza secundário do tema).

**Placement, seed e deploy**

- **FR-018**: O bloco DEVE ser posicionado na região `content_full` do tema `default`, com visibilidade por path limitada a `/para-estudantes`, com weight imediatamente após `default_beneficiosestudantes` (weight `0` → jornada tipicamente `1`).
- **FR-019**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11036`, tipicamente `custom_configs_update_11037`) DEVE, de forma **idempotente**, usando Entity API: garantir paragraph type + block type e field instances; garantir form/view displays; criar/seedar a instância do bloco com UUID fixo e 4 paragraphs quando ausentes; garantir placement/visibilidade.
- **FR-020**: O seed DEVE preservar edições posteriores do editor; preencher apenas campos vazios/ausentes — sem commit de `sites/default/files`.
- **FR-021**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` (cim antes do updb; 2ª cim após updb para placements que dependem de UUIDs seedados); ao final do desenvolvimento na origem, `drush cex` para versionar configs geradas/alteradas.
- **FR-022**: A seção relevante do `PRD.md` (rota `/para-estudantes` / blocos) DEVE ser atualizada de forma cirúrgica refletindo o novo block type, fields e placement.

### Key Entities

- **`jornada_estudante` (block_content)**: seção da jornada — título (`field_text_simple`), lista (`field_itens_lista` → `passo_jornada_p`, cardinality 4).
- **`passo_jornada_p` (paragraph)**: passo do grid — título (`field_text_simple`), descrição (`field_text_simple_long`); número/cor são apresentação.
- **Bloco placement** (`block.block.*` no tema `default`): região `content_full`, pages `/para-estudantes`, weight após benefícios, UUID alinhado ao seed.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a seção em `/para-estudantes` é reconhecível frente ao layout de referência (título centralizado + 4 cards com badges numeradas e último laranja) em ≥95% dos critérios de checklist visual (paddings, badge 48px, raio 16px, min-height ~202px).
- **SC-002**: Em viewport tablet, 100% das verificações confirmam 2 cards por linha; em mobile estreito, 1 por linha; sem scroll horizontal causado pelo bloco.
- **SC-003**: Um editor atualiza título e passos do bloco em menos de 5 minutos, sem suporte de desenvolvimento; badges renumeram automaticamente após reordenar.
- **SC-004**: Após o deploy padrão, `/para-estudantes` exibe o bloco imediatamente após Benefícios Estudantes; home, `/para-empresas` e `/quem-somos` amostradas **não** o exibem.
- **SC-005**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cim -y` + `drush cr`, com zero passos manuais no painel.
- **SC-006**: Segunda execução de `drush updb` não cria tipos, blocos, paragraphs ou placements duplicados (idempotência confirmada).
- **SC-007**: Com zero passos ou campos vazios, a página não quebra; elementos ausentes são omitidos de forma segura.
- **SC-008**: Estilos do bloco não alteram visualmente outras seções (amostragem: hero estudantes, benefícios estudantes, “Como funciona” na home).

## Assumptions

- **Campo curto**: o pedido `field_text_simple_small` mapeia para o storage canônico existente `field_text_simple` (mesmo padrão das features `004`–`022`); não se cria storage paralelo.
- **Lista**: cria-se apenas nova **instance** de `field_itens_lista` no bundle `jornada_estudante` apontando para `passo_jornada_p`, cardinality **4**.
- **Número e cor**: derivados da posição; o “último” laranja aplica-se ao último item renderizado da lista (não a um índice fixo “4” hardcoded se houver menos de 4 itens).
- **Região `content_full`**: no tema `default`, adequada para seções abaixo do hero; weight relativo: imediatamente após `default_beneficiosestudantes` (weight `0`).
- **Copy seed do título**: “Sua jornada até o sucesso”.
- **Copy seed dos 4 passos** (textos exatos do Figma / briefing):
  1. **Crie seu perfil** — Mostre suas habilidades e formação de forma clara e atrativa.
  2. **Descubra oportunidades** — Receba recomendações inteligentes baseadas no seu perfil.
  3. **Candidate-se** — Com apenas um clique, envie seu perfil para as melhores vagas.
  4. **Comece sua carreira** — Acompanhe seus processos e celebre suas conquistas.
- **Cor laranja do último passo**: preferência `#FD7B1A`; `#FF8C78` aceitável se já for token padrão do tema para CTAs/destaques finais.
- **Hook número**: próximo update após `custom_configs_update_11036` → `11037`, salvo se outro update for commitado antes da implementação.
- **UUID fixo**: a implementação define UUID estável compartilhado entre seed e `block.block.*` exportado (padrão das features anteriores).
- **Convivência**: hero (`021`), benefícios (`022`) e View `vagas` permanecem; esta feature apenas adiciona a Section 03.
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (PHP do `hook_update_N`, Twig do bloco e do paragraph, CSS/SCSS com tokens 64/40/48/16 e diferenciação do último passo) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
