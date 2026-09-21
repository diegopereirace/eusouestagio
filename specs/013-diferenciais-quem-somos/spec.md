# Feature Specification: Diferenciais Quem Somos

**Feature Directory**: `specs/013-diferenciais-quem-somos`  
**Created**: 2026-09-20  
**Status**: Draft  
**Input do usuário**: Novo bloco **Diferenciais Quem Somos** na página `/quem-somos` — grid de até 8 itens (ícone + rótulo), modelagem Custom Block + Paragraphs, tipografia Poppins, Bootstrap 5, seed e placement automatizados com `hook_update_N` idempotente, exportação `drush cex` → `config/sync`, regra Cursor de deploy, templates Twig + CSS com escopo dedicado. Machine names distintos do bloco da home (`nossos_diferenciais` / `diferencial_item_p`) para evitar conflito.

## Escopo

### Inclui

- Tipo de paragraph **Item Diferencial Simples** (`diferencial_simples_p`, sufixo `_p` obrigatório) com ícone e rótulo curtos gerenciáveis.
- Tipo de bloco customizado **Diferenciais Quem Somos** (`diferenciais_quem_somos`) — nome escolhido para **não** colidir com o bloco homônimo da home (`nossos_diferenciais`).
- Reutilização de storages canônicos do projeto: ícone via `field_image`; título/rótulos curtos via `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente); descrição do bloco via `field_text_simple_long`; lista via storage existente `field_itens_lista` (Entity Reference Revisions → `diferencial_simples_p`, **cardinality ilimitada**), com nova field instance no bundle do bloco.
- Displays de formulário e visualização (form/view) para o paragraph e o block type.
- Seed idempotente: uma instância do bloco com título, texto descritivo e **8 itens** (ícone + rótulo), assets versionados quando necessário.
- Placement na região `content_full` do tema `default`, visível **somente** em `/quem-somos`.
- Templates Twig do bloco e do paragraph; layout: container com título/descrição centralizados e grid responsivo (2 colunas no mobile, 4 no desktop).
- CSS/SCSS com escopo estrito (ex.: `.block-diferenciais-quem-somos`) limitando ícones (`max-width: 64px`) e tipografia Poppins SemiBold nos rótulos.
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11015`, tipicamente `11016`) garantindo tipos/fields/displays, seed via Entity API (`BlockContent::create()` / `entityTypeManager`) e placement.
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento.
- Regra Cursor `.cursor/rules/drupal-deploy-configs.mdc` orientando `drush cex` + `hook_update_N` idempotente para alterações estruturais e seeds.
- Atualização cirúrgica do `PRD.md` (§3.6 blocos / página Quem somos) refletindo o novo block type e placement.

### Fora

- Alteração ou remoção do bloco da home `nossos_diferenciais` / paragraph `diferencial_item_p` (feature `004`).
- Redesign do banner (`009`), seção “Sobre nós” (`010`) ou faixa Missão/Visão no Node (`012`).
- Layout Builder / edição visual de colunas pelo editor.
- Criação de field storage paralelo `field_text_simple_small` (proibido — reutilizar `field_text_simple`).
- Exibição do bloco em home ou outras rotas além de `/quem-somos`.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê os diferenciais em Quem Somos (Priority: P1)

Visitante acessa `/quem-somos` e vê uma seção com título centralizado, texto descritivo contido e um grid de diferenciais (ícone acima do rótulo), alinhado ao layout de referência — tipicamente 8 itens em 4 colunas no desktop.

**Why this priority**: É o valor principal da feature — reforçar os diferenciais da marca na página institucional.

**Independent Test**: Abrir `/quem-somos` em viewport ≥992px com o bloco publicado e comparar estrutura (cabeçalho + grid) com o layout de referência.

**Acceptance Scenarios**:

1. **Given** o bloco Diferenciais Quem Somos publicado com título, descrição e 8 itens, **When** o visitante abre `/quem-somos` em desktop, **Then** vê título em destaque centralizado, descrição centralizada com largura contida e o grid completo na ordem cadastrada.
2. **Given** viewport desktop (lg+), **When** observa o grid, **Then** há até 4 itens por linha.
3. **Given** a seção renderizada, **When** o visitante lê um item, **Then** vê ícone (tamanho controlado) acima do rótulo em tipografia Poppins SemiBold.

---

### User Story 2 — Visitante mobile vê 2 itens por linha (Priority: P1)

Em viewport estreita, o grid exibe 2 itens por linha, permanece legível e não causa scroll horizontal.

**Why this priority**: Tráfego mobile não pode perder a mensagem nem quebrar o layout.

**Independent Test**: Abrir `/quem-somos` em viewport ≤575.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** o grid exibe 2 itens por linha.
2. **Given** viewport mobile, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pelo bloco.
3. **Given** viewport intermediária (md), **When** observa o grid, **Then** há até 3 itens por linha (comportamento Bootstrap `col-md-4`).

---

### User Story 3 — Editor gerencia conteúdo sem código (Priority: P1)

Editor autenticado com permissão de blocos edita título, descrição, ícones, rótulos e ordem dos itens; as mudanças refletem em `/quem-somos` sem deploy de código.

**Why this priority**: Conteúdo institucional muda com frequência; não pode depender de desenvolvimento.

**Independent Test**: Editar o bloco no painel, salvar e recarregar `/quem-somos`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre o bloco “Diferenciais Quem Somos”, **Then** consegue editar título, descrição e a lista ilimitada de itens (cada um com ícone e rótulo).
2. **Given** um bloco existente, **When** o editor altera textos, troca ícones, adiciona/remove/reordena itens e salva, **Then** a página pública reflete as mudanças após o cache esperado do site.
3. **Given** o formulário do item, **When** o editor cadastra apenas o rótulo sem ícone, **Then** o rótulo permanece legível na página (ícone omitido ou placeholder seguro).

---

### User Story 4 — Bloco aparece só em Quem Somos (Priority: P1)

O bloco não contamina home nem outras internas; aparece exclusivamente em `/quem-somos`. O bloco da home `nossos_diferenciais` permanece intacto.

**Why this priority**: Evita regressão visual e conflito com a feature `004`.

**Independent Test**: Comparar `/quem-somos`, `<front>` e outra interna após o deploy.

**Acceptance Scenarios**:

1. **Given** deploy aplicado, **When** o visitante abre `/quem-somos`, **Then** vê o bloco Diferenciais Quem Somos na região de conteúdo full.
2. **Given** deploy aplicado, **When** o visitante abre a home, **Then** o bloco `diferenciais_quem_somos` **não** é exibido; o bloco da home `nossos_diferenciais` (quando publicado) continua disponível.
3. **Given** deploy aplicado, **When** o visitante abre outra interna sem o path `/quem-somos`, **Then** o bloco `diferenciais_quem_somos` **não** é exibido.

---

### User Story 5 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

Homologação/produção recebem tipos, fields, displays, instância seed, placement e estilos apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/quem-somos` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr`, **Then** o bloco aparece em `/quem-somos` com título, descrição e itens seed (ou conteúdo editorial já existente).
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de tipos, blocos, paragraphs ou placements (idempotência).
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** block type, paragraph type, field instances/storages, displays e block placement aparecem versionados em `config/sync`.

### Edge Cases

- Título ou descrição vazios → omitir o elemento correspondente; não exibir headings/parágrafos vazios.
- Zero itens na lista → renderizar apenas cabeçalho (quando preenchido), sem erro de template.
- Menos de 8 itens → grid preenche com as colunas disponíveis; layout não quebra.
- Mais de 8 itens → todos renderizam na ordem; o layout continua em grid (não truncar silenciosamente).
- Item sem ícone → rótulo permanece legível; ícone omitido ou placeholder.
- Item sem rótulo → ícone (se houver) permanece; não exibir texto vazio.
- Reexecução do hook → no-op seguro; **não** sobrescrever conteúdo editorial divergente do seed.
- Viewport intermediária → aplicar breakpoints Bootstrap (`col-6` / `col-md-4` / `col-lg-3`).
- Convivência com demais seções de Quem Somos (banner, Sobre nós, Missão/Visão) → esta feature não as altera.

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de paragraph rotulado “Item Diferencial Simples” com machine name `diferencial_simples_p`.
- **FR-002**: Cada item DEVE expor ícone via storage reutilizado `field_image` e rótulo via storage reutilizado `field_text_simple` (o pedido verbal `field_text_simple_small` mapeia para o canônico `field_text_simple`; **não** criar storage paralelo).
- **FR-003**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “Diferenciais Quem Somos” com machine name `diferenciais_quem_somos` (distinto de `nossos_diferenciais` da home).
- **FR-004**: O bloco DEVE expor: título via `field_text_simple`, descrição via `field_text_simple_long` e lista ordenável ilimitada via instance de `field_itens_lista` (Entity Reference Revisions → `diferencial_simples_p`).
- **FR-005**: Todo o conteúdo exibido (título, descrição, ícones, rótulos, ordem dos itens) DEVE ser gerenciável no painel; placeholders de template só para ausência de dado.
- **FR-006**: Displays de formulário e visualização do paragraph e do bloco DEVEM incluir os campos necessários para edição e renderização pública.
- **FR-007**: Alterações estruturais (tipos, fields, displays, placement) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação**

- **FR-008**: Todo o markup do bloco DEVE estar encapsulado sob classe dedicada (ex.: `block-diferenciais-quem-somos`); estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.
- **FR-009**: O wrapper principal do conteúdo DEVE usar container com espaçamento vertical generoso (equivalente a `.container.py-5`).
- **FR-010**: O título DEVE ser um `h2` centralizado, em negrito, com família tipográfica Poppins.
- **FR-011**: A descrição DEVE ser um parágrafo centralizado, com largura máxima contida no desktop (equivalente a `.col-lg-8.mx-auto`).
- **FR-012**: Os itens DEVEM ser renderizados em uma linha/grid com espaçamento superior e centralização (equivalente a `.row.mt-5.justify-content-center`).
- **FR-013**: Cada item (paragraph) DEVE ocupar colunas equivalentes a `.col-6.col-md-4.col-lg-3.mb-4` (2 / 3 / 4 por linha conforme breakpoint).
- **FR-014**: Cada item DEVE alinhar conteúdo ao centro em coluna flex (equivalente a `.text-center.d-flex.flex-column.align-items-center`).
- **FR-015**: Ícones DEVEM usar `.img-fluid` e CSS com `max-width: 64px` (ou equivalente) sob o escopo do bloco/paragraph.
- **FR-016**: Rótulos DEVEM ter espaçamento superior (equivalente a `.mt-3`), família Poppins e peso SemiBold.

**Placement, seed e deploy**

- **FR-017**: O bloco DEVE ser posicionado na região `content_full` do tema `default`, com visibilidade por path limitada a `/quem-somos` (e variantes de alias já usadas no projeto, se necessário).
- **FR-018**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11015`, tipicamente `custom_configs_update_11016`) DEVE, de forma **idempotente**, usando Entity API: garantir paragraph type + block type e field instances; garantir form/view displays; criar/seedar a instância do bloco (ex.: `BlockContent::create()`) com UUID fixo e 8 paragraphs quando ausentes; garantir placement/visibilidade.
- **FR-019**: O seed DEVE preservar uploads/edições posteriores do editor; preencher apenas campos vazios/ausentes; ícones seed versionados em `modules/custom/custom_configs/assets/` quando aplicável.
- **FR-020**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` (cim antes do updb); ao final do desenvolvimento, `drush cex` para versionar configs geradas/alteradas.
- **FR-021**: A regra Cursor `.cursor/rules/drupal-deploy-configs.mdc` DEVE existir e orientar exportação (`drush cex`) + `hook_update_N` idempotente com Entity API para seeds/placements.
- **FR-022**: A seção relevante do `PRD.md` (blocos / página Quem somos) DEVE ser atualizada de forma cirúrgica refletindo o novo block type, fields e placement.

### Key Entities

- **`diferenciais_quem_somos` (block_content)**: seção institucional — título (`field_text_simple`), descrição (`field_text_simple_long`), lista (`field_itens_lista` → `diferencial_simples_p`, ilimitado).
- **`diferencial_simples_p` (paragraph)**: item do grid — ícone (`field_image`), rótulo (`field_text_simple`).
- **Bloco placement** (`block.block.*` no tema `default`): região `content_full`, pages `/quem-somos`, UUID alinhado ao seed.
- **Distinção da home**: `nossos_diferenciais` + `diferencial_item_p` (feature `004`) permanecem inalterados.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a seção em `/quem-somos` é reconhecível frente ao layout de referência (cabeçalho centralizado + grid de ícones/rótulos Poppins) em ≥95% dos critérios de checklist visual.
- **SC-002**: Em viewport mobile, 100% das verificações confirmam 2 itens por linha, legibilidade e ausência de scroll horizontal causado pelo bloco.
- **SC-003**: Um editor atualiza título, descrição e itens do bloco em menos de 5 minutos, sem suporte de desenvolvimento.
- **SC-004**: Após o deploy padrão, `/quem-somos` exibe o bloco; home e demais internas amostradas **não** o exibem; o bloco da home `nossos_diferenciais` não é afetado.
- **SC-005**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cr`, com zero passos manuais no painel.
- **SC-006**: Segunda execução de `drush updb` não cria tipos, blocos, paragraphs ou placements duplicados (idempotência confirmada).
- **SC-007**: Com zero itens ou campos vazios, a página não quebra; elementos ausentes são omitidos de forma segura.
- **SC-008**: Estilos do bloco não alteram visualmente outras seções (amostragem: home / `nossos_diferenciais`, banner Quem Somos, Sobre nós, Missão/Visão).

## Assumptions

- **Campo curto**: o pedido `field_text_simple_small` mapeia para o storage canônico existente `field_text_simple` (mesmo padrão das features `004`–`012`); não se cria storage paralelo.
- **Lista `field_itens_lista`**: storage já existe em `block_content` (feature `011`); cria-se apenas nova **instance** no bundle `diferenciais_quem_somos` apontando para `diferencial_simples_p`, cardinality ilimitada.
- **Região `content_full`**: no tema `default`, essa região renderiza abaixo do `page.content` — adequada para diferenciais após o conteúdo principal do node Quem Somos; weight relativo às demais placements da rota será definido na implementação sem sobrepor o banner.
- **Quantidade seed**: 8 itens com rótulos placeholder institucionais (ex.: “Atendimento personalizado”, “Seleção qualificada”, etc.) e ícones genéricos versionados; o editor substitui pelo conteúdo final do design.
- **Hook número**: próximo update após `custom_configs_update_11015` → `11016`, salvo se outro update for commitado antes da implementação.
- **UUID fixo**: a implementação define UUID estável compartilhado entre seed e `block.block.*` exportado (padrão das features anteriores).
- **Convivência**: banner, Sobre nós e Missão/Visão no Node permanecem; esta feature apenas adiciona a seção de diferenciais.
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (PHP do `hook_update_N`, Twig do bloco e do paragraph, SCSS/CSS de ícones/espaçamentos) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
- A regra Cursor de deploy (`drupal-deploy-configs.mdc`) é entregue já nesta etapa de specify, pois é governança transversal solicitada explicitamente e não depende do plano de implementação da feature.
