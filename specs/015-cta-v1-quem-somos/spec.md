# Feature Specification: Bloco CTA v1 — Quem Somos

**Feature Directory**: `specs/015-cta-v1-quem-somos`  
**Created**: 2026-09-22  
**Status**: Draft  
**Input do usuário**: Novo **Bloco de Chamada para Ação (CTA v1)** posicionado logo abaixo da seção de Números na página Quem Somos — Custom Block Type `cta_v1`, reuso de campos canônicos, seed tipográfico fiel ao Figma (título, subtítulo, dois botões), deploy 100% automatizado via Entity API + `hook_update_N` idempotente, template Twig com utilitários Bootstrap 5 e CSS com tokens exatos do design (fundo `#D3E4FE`, radius `32px`, padding `64px`, gap `24px`, botão primário `#FD7B1A`).

## Escopo

### Inclui

- Tipo de bloco customizado **CTA v1** (`cta_v1`) com quatro campos gerenciáveis:
  - Título curto via storage reutilizado `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente; **não** criar storage paralelo).
  - Subtítulo via storage reutilizado `field_text_simple_long`.
  - Botão primário via storage reutilizado `field_link` (URL + texto do link, ex.: “Buscar vagas”).
  - Botão secundário via novo storage `field_link_2` (tipo link nativo; alternativa verbal `field_link_secundario` descartada em favor do padrão `*_2` do projeto — ex.: `field_text_simple_2`).
- Displays de formulário e visualização (form/view) do block type `cta_v1`.
- Seed idempotente: uma instância do bloco com copy fixa do design:
  - Título: `Seu próximo estágio começa aqui.`
  - Subtítulo: `Junte-se a milhares de estudantes e encontre a oportunidade que vai mudar sua carreira.`
  - Primário: texto `Buscar vagas` → URL `/vagas`
  - Secundário: texto `Cadastrar gratuitamente` → URL `/cadastro/candidato`
- Placement na região `content_full` do tema `default`, **logo após** o bloco de números (`default_impactnumbersquemsomos`, weight `11`) — tipicamente weight `12` — com visibilidade restrita a `/quem-somos`.
- Template Twig específico do bloco (suggestion tipicamente `block--block-content--cta-v1.html.twig` ou equivalente do tema).
- Layout: card contido (máx. `1200px`), conteúdo centralizado; botões lado a lado no desktop e empilhados no mobile.
- CSS/SCSS com escopo estrito (ex.: `.block-cta-v1` / `.cta-v1`) aplicando fundo `#D3E4FE`, `border-radius: 32px`, padding `64px`, tipografia Poppins e estilos dos dois botões.
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11018`, tipicamente `11019`) garantindo block type + field instances/displays, seed via Entity API e placement/visibilidade.
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento.
- Atualização cirúrgica do `PRD.md` (§3.6 blocos / página Quem somos) refletindo o block type `cta_v1`, campos e placement.

### Fora

- Alteração do banner (`009`), Sobre nós (`010`), Missão/Visão (`012`), Diferenciais Quem Somos (`013`) ou Impact in Numbers (`014` / placement `11018`).
- Layout Builder / edição visual de colunas pelo editor.
- Criação de storage paralelo `field_text_simple_small` ou `field_link_secundario`.
- Exibição do bloco em home ou outras rotas além de `/quem-somos`.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o CTA em Quem Somos (Priority: P1)

Visitante acessa `/quem-somos`, rola até após a faixa de números e vê um card claro com cantos arredondados, título, subtítulo e dois botões de ação alinhados ao layout do Figma.

**Why this priority**: É o valor principal da feature — converter atenção institucional em ação (buscar vagas / cadastrar).

**Independent Test**: Abrir `/quem-somos` em viewport ≥768px com o bloco publicado e comparar card, tipografia, copy e botões com o design de referência.

**Acceptance Scenarios**:

1. **Given** o bloco CTA v1 publicado com título, subtítulo e dois links, **When** o visitante abre `/quem-somos` em desktop, **Then** vê o card abaixo da seção de números, com conteúdo centralizado e a copy do design.
2. **Given** a seção renderizada, **When** observa o card, **Then** o fundo é azul claro (`#D3E4FE`), cantos com raio de 32px e padding interno de 64px; a largura do card não ocupa 100% da viewport (contida em ~1200px).
3. **Given** viewport desktop, **When** observa os botões, **Then** “Buscar vagas” (primário laranja) e “Cadastrar gratuitamente” (secundário branco) aparecem lado a lado.

---

### User Story 2 — Visitante mobile vê botões empilhados (Priority: P1)

Em viewport estreita, os botões empilham verticalmente, o card permanece legível e não causa scroll horizontal.

**Why this priority**: Tráfego mobile não pode perder as CTAs nem quebrar o layout.

**Independent Test**: Abrir `/quem-somos` em viewport ≤575.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** os dois botões aparecem empilhados (um abaixo do outro), ainda centralizados.
2. **Given** viewport mobile, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pelo bloco.
3. **Given** a seção em qualquer viewport, **When** mede o espaçamento vertical entre o grupo de textos e o grupo de botões, **Then** o gap visual é de 24px (±2px de arredondamento).

---

### User Story 3 — Visitante usa os botões de ação (Priority: P1)

Visitante clica em cada botão e é levado à rota canônica correspondente, com URLs limpas.

**Why this priority**: Sem navegação correta, o CTA não entrega conversão.

**Independent Test**: Clicar nos dois botões em `/quem-somos` e verificar destino.

**Acceptance Scenarios**:

1. **Given** o bloco seedado, **When** o visitante clica em “Buscar vagas”, **Then** navega para `/vagas`.
2. **Given** o bloco seedado, **When** o visitante clica em “Cadastrar gratuitamente”, **Then** navega para `/cadastro/candidato`.
3. **Given** um editor altera URL ou texto de um botão e salva, **When** o visitante recarrega a página, **Then** vê o novo rótulo/destino após o cache esperado do site.

---

### User Story 4 — Editor gerencia conteúdo sem código (Priority: P1)

Editor autenticado com permissão de blocos edita título, subtítulo e os dois links; as mudanças refletem em `/quem-somos` sem deploy de código.

**Why this priority**: Copy e destinos de conversão mudam; não podem depender de desenvolvimento após o seed.

**Independent Test**: Editar o bloco no painel, salvar e recarregar `/quem-somos`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre o bloco “CTA v1”, **Then** consegue editar título, subtítulo, botão primário e botão secundário (URL + texto).
2. **Given** um bloco existente, **When** o editor altera textos/links e salva, **Then** a página pública reflete as mudanças após o cache esperado do site.

---

### User Story 5 — Deploy reproduz estrutura, seed e placement (Priority: P1)

Homologação/produção recebem block type, fields, displays, instância seedada, placement e estilos apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/quem-somos` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr`, **Then** `/quem-somos` exibe o CTA com a copy do seed (ou conteúdo editorial já existente), abaixo dos números.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de bloco nem sobrescrita de copy editorial divergente do seed.
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** block type `cta_v1`, field storages/instances, displays e placement aparecem versionados em `config/sync`.

### Edge Cases

- Título vazio → omitir o título; manter subtítulo/botões se preenchidos.
- Subtítulo vazio → omitir o subtítulo; manter título/botões se preenchidos.
- Botão primário ou secundário sem URL → omitir esse botão (não renderizar link quebrado).
- Ambos os botões ausentes → renderizar apenas textos do card (sem área de botões vazia).
- Título e subtítulo ambos vazios e sem botões → omitir o card inteiro (não renderizar faixa vazia).
- Reexecução do hook → no-op seguro; **não** sobrescrever conteúdo editorial divergente do seed; popular apenas se a instância estiver ausente ou campos vazios conforme regra do projeto.
- Página diferente de `/quem-somos` → bloco não aparece.
- Convivência com demais seções de Quem Somos (banner, Sobre nós, Missão/Visão, Diferenciais, Números) → esta feature não as altera; apenas adiciona o CTA após os números.

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “CTA v1” com machine name `cta_v1`.
- **FR-002**: O bloco DEVE expor título via storage reutilizado `field_text_simple` e subtítulo via storage reutilizado `field_text_simple_long` (pedido verbal `field_text_simple_small` mapeia para `field_text_simple`; **não** criar storage paralelo).
- **FR-003**: O bloco DEVE expor botão primário via storage reutilizado `field_link` e botão secundário via storage `field_link_2` (tipo link nativo, cardinalidade 1; criar storage apenas se inexistente).
- **FR-004**: Todo o conteúdo exibido (título, subtítulo, textos e URLs dos botões) DEVE ser gerenciável no painel do bloco; placeholders de template só para ausência de dado.
- **FR-005**: Displays de formulário e visualização do block type `cta_v1` DEVEM incluir os quatro campos para edição e renderização pública.
- **FR-006**: Alterações estruturais (block type, field storage/instance, displays, placement) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação (tokens Figma — CTA card)**

- **FR-007**: Todo o markup do bloco DEVE estar encapsulado sob classe dedicada (ex.: `.block-cta-v1` / `.cta-v1`); estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.
- **FR-008**: O card DEVE usar fundo `#D3E4FE`, `border-radius: 32px` e padding interno `64px` em todos os lados (valores exatos do Figma).
- **FR-009**: O card DEVE respeitar largura máxima de `1200px` (centralizado; não full-bleed da viewport).
- **FR-010**: Título, subtítulo e grupo de botões DEVEM estar alinhados ao centro; o espaçamento vertical entre o grupo de textos e o grupo de botões DEVE ser `24px` (equivalente a gap/utilitário Bootstrap `.gap-4` / `.mb-4`).
- **FR-011**: O título DEVE usar família Poppins, peso semibold/negrito, cor escura `#0F172A`.
- **FR-012**: O subtítulo DEVE usar família Poppins, cor escura/cinza (ex.: `#45464D`), com hierarquia tipográfica inferior ao título.
- **FR-013**: Os botões DEVEM empilhar no mobile e ficar lado a lado no desktop (equivalente a `.d-grid.gap-2.d-md-flex.justify-content-md-center.gap-md-3` ou padrão equivalente do tema).
- **FR-014**: O botão primário DEVE ter fundo `#FD7B1A` e texto branco; o botão secundário DEVE ter fundo `#FFFFFF` e texto escuro, sem borda forte (borda ausente ou muito sutil).

**Seed e deploy**

- **FR-015**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11018`, tipicamente `custom_configs_update_11019`) DEVE, de forma **idempotente**, usando Entity API (`\Drupal::entityTypeManager()` / `BlockContent::create()`): garantir block type + field instances; garantir form/view displays; criar/seedar a instância do bloco com UUID fixo e a copy do design **somente se** ausente/campos vazios; garantir placement em `content_full` (weight após números, tipicamente `12`) com visibilidade `request_path` = `/quem-somos`.
- **FR-016**: O seed DEVE preservar edições posteriores do editor; preencher apenas campos/instância vazios; **não** duplicar blocos em reexecução.
- **FR-017**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` (cim antes do updb); ao final do desenvolvimento, `drush cex` para versionar configs geradas/alteradas.
- **FR-018**: A seção relevante do `PRD.md` (§3.6 / Quem somos) DEVE ser atualizada de forma cirúrgica refletindo `cta_v1`, campos, placement e seed.

### Key Entities

- **`cta_v1` (block_content)**: card de chamada para ação — título (`field_text_simple`), subtítulo (`field_text_simple_long`), botão primário (`field_link`), botão secundário (`field_link_2`).
- **Placement**: bloco de tema `default` na região `content_full`, weight após `default_impactnumbersquemsomos` (11), visível somente em `/quem-somos`.
- **Seed tipográfico**: título “Seu próximo estágio começa aqui.”; subtítulo “Junte-se a milhares de estudantes e encontre a oportunidade que vai mudar sua carreira.”; primário “Buscar vagas” → `/vagas`; secundário “Cadastrar gratuitamente” → `/cadastro/candidato`.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, o CTA em `/quem-somos` é reconhecível frente ao Figma (card azul claro, cantos 32px, dois botões) em ≥95% dos critérios de checklist visual (paddings, cores, tipografia, gap, largura contida).
- **SC-002**: Em viewport mobile, 100% das verificações confirmam botões empilhados, legibilidade e ausência de scroll horizontal causado pelo bloco.
- **SC-003**: 100% dos cliques nos botões seedados levam às rotas `/vagas` e `/cadastro/candidato` respectivamente.
- **SC-004**: Um editor atualiza título, subtítulo ou um dos links em menos de 3 minutos, sem suporte de desenvolvimento.
- **SC-005**: Após o deploy padrão, `/quem-somos` exibe o CTA abaixo dos números com a copy do seed (ou o conteúdo editorial já presente); demais seções da página permanecem inalteradas visualmente.
- **SC-006**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cr`, com zero passos manuais no painel.
- **SC-007**: Segunda execução de `drush updb` não cria bloco duplicado nem sobrescreve copy editorial divergente (idempotência confirmada).
- **SC-008**: Com campos vazios, a página não quebra; elementos ausentes são omitidos de forma segura.
- **SC-009**: Estilos do CTA não alteram visualmente outras seções (amostragem: Impact in Numbers, Diferenciais Quem Somos, home, bloco CTO existente).

## Assumptions

- **Campo de título**: o pedido `field_text_simple_small` mapeia para o storage canônico existente `field_text_simple` no entity type `block_content` (mesmo padrão das features `004`–`014`).
- **Campo de subtítulo**: reutiliza `field_text_simple_long` já disponível em `block_content`.
- **Botão primário**: reutiliza storage existente `field_link` (já usado no bloco `cto`).
- **Botão secundário**: cria-se `field_link_2` (padrão `*_2` do projeto); **não** se usa `field_link_secundario`.
- **URLs do seed**: “Buscar vagas” → `/vagas`; “Cadastrar gratuitamente” → `/cadastro/candidato` (rotas canônicas já usadas no produto).
- **Posição**: weight `12` em `content_full`, imediatamente após `default_impactnumbersquemsomos` (weight `11`).
- **Hook número**: próximo update após `custom_configs_update_11018` → `11019`, salvo se outro update for commitado antes da implementação.
- **UUID**: a implementação definirá UUID fixo para a instância seedada e para o placement exportável (padrão das features anteriores).
- **Convivência**: banner, Sobre nós, Missão/Visão, Diferenciais e Números permanecem; esta feature apenas adiciona o CTA.
- Textos seed desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (PHP do `hook_update_N`, Twig, SCSS/CSS com tokens Figma) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
