# Feature Specification: Refino Figma Contato (bleed + cards) e tipografia de títulos

**Feature Directory**: `specs/058-refino-figma-contato`  
**Created**: 2026-11-04  
**Status**: Draft  
**Input do usuário**: Refatorar a página **Contato** (`/contato`) — template do Node Contato e estilos dedicados — para eliminar a discrepância com o Figma: painel direito azul com *bleed* até a borda direita do navegador, formulário alinhado à margem esquerda do header, dimensões/estilos corretos dos campos e botão, cards de E-mail/WhatsApp na linha de ações, e empilhamento responsivo em viewports estreitas. Em complemento, o título **“Envie sua mensagem”** deve seguir o token tipográfico do anexo Figma (Inter 600 / 30px / 38px / letter-spacing −0.3px / `#000000`), e esse token deve **padronizar os títulos de seção no projeto inteiro**. Aproveitar CSS limpo (Grid/Flexbox, sem aninhamentos profundos) durante a implementação.

## Escopo

### Inclui

- Correção do layout desktop da página `/contato` (Node bundle `contato`, view mode `full`) para que:
  - a coluna do formulário continue alinhada visualmente à coluna de conteúdo do header (margem esquerda equivalente ao container padrão do tema);
  - o painel visual direito (`#E5EEFF`) ocupe a faixa restante e **encoste na borda direita da viewport**, sem margem branca lateral;
- Exibição da linha de ações abaixo do formulário com três elementos no desktop: botão **Enviar Mensagem**, card de **E-mail** e card de **WhatsApp**, com proporções e aparência do Figma;
- Renderização dos valores dos campos do Node (`field_email`, `field_phone_wpp`) nos cards, com links acionáveis (`mailto:` e WhatsApp);
- Renderização do painel direito com imagem (`field_imagem`), título curto e texto longo do Node, centralizados vertical e horizontalmente no painel;
- Ajuste visual dos campos do webform (altura, bordas finas, cantos suaves, larguras proporcionais ao grid de design) e do botão de envio (laranja `#FD7B1A`, texto branco, sem borda);
- Comportamento responsivo abaixo de ~992px de largura: colunas empilhadas (formulário acima, painel em largura total), linha de ações com quebra graciosa e botão de envio em largura total;
- **Token tipográfico de título de seção** (fonte de verdade do anexo Figma de Contato):
  - família **Inter**;
  - peso **600**;
  - tamanho **30px**;
  - altura de linha **38px**;
  - letter-spacing **−0.3px**;
  - cor **`#000000`**;
- Aplicação desse token ao título “Envie sua mensagem” em `/contato`;
- **Padronização projeto inteiro** dos títulos de seção de páginas/blocos públicos para o mesmo token (em substituição ao uso inconsistente de Poppins/tamanhos variados nesses títulos), via estilo base reutilizável do tema;
- Documentação cirúrgica no quickstart/PRD da feature se o comportamento visual publicado mudar de forma relevante para editores/deploy.

### Fora

- Alteração da estrutura de campos do webform `contato` (já definida nas features 017/018), salvo ajustes puramente de apresentação;
- Criação de novos field storages ou novo content type;
- Redesign de header, footer ou FAB de WhatsApp global;
- Layout Builder / edição visual de colunas pelo editor;
- Mudança de copy institucional além do que já vem dos campos do Node;
- Tipografia de **microtítulos** (títulos de cards internos, labels de formulário, links de navegação, rodapé, metadados);
- Tipografia de **heroes/banners** e displays especiais que já possuem especificação Figma própria distinta deste token de seção;
- Painéis administrativos / área logada (candidato, empresa), salvo se reutilizarem o mesmo componente de título de seção pública;
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Visitante vê o painel direito sem margem na borda (Priority: P1)

Visitante abre `/contato` em desktop e percebe o painel azul claro (“Time de especialistas”) colado na borda direita da janela, enquanto o formulário à esquerda permanece alinhado com a margem do header — sem “caixa” centralizada deixando faixa branca dos dois lados.

**Why this priority**: É a discrepância principal em relação ao Figma e o critério visual mais perceptível da página.

**Independent Test**: Abrir `/contato` em viewport ≥992px e inspecionar se o fundo `#E5EEFF` do painel direito toca a borda direita da viewport e se o título “Envie sua mensagem” alinha com o início do conteúdo do header.

**Acceptance Scenarios**:

1. **Given** viewport desktop (≥992px), **When** o visitante abre `/contato`, **Then** o fundo do painel direito alcança a borda direita da janela (sem margem branca entre o painel e a borda).
2. **Given** o mesmo viewport, **When** compara a margem esquerda do título/formulário com a margem esquerda do logo/conteúdo do header, **Then** o formulário inicia alinhado a essa referência (desvio visual imperceptível em uso normal).
3. **Given** o layout em duas colunas, **When** observa a altura das colunas, **Then** o painel direito estica na altura da linha do formulário (colunas com altura visual coerente, sem “faixa” azul curta flutuando no meio).

---

### User Story 2 — Visitante usa a linha de ações (enviar + cards) (Priority: P1)

Abaixo do formulário, o visitante vê o botão **Enviar Mensagem** ao lado dos cards de E-mail e WhatsApp, com aparência e proporções do Figma, e consegue acionar cada canal.

**Why this priority**: Completa o canal de contato e corrige o alinhamento/estilo dos elementos que hoje divergem do design.

**Independent Test**: Inspecionar a faixa abaixo do webform em desktop; clicar nos dois cards e confirmar destinos; verificar dimensões/aparência aproximadas do Figma.

**Acceptance Scenarios**:

1. **Given** Node Contato com e-mail e telefone/WhatsApp preenchidos, **When** a página renderiza em desktop, **Then** a linha de ações mostra botão de envio, card de E-mail e card de WhatsApp na mesma fileira horizontal (com espaçamento consistente).
2. **Given** o card de E-mail, **When** o visitante observa e clica, **Then** vê ícone laranja, rótulo “E-mail”, o endereço do campo do Node em tom cinza escuro, borda fina cinza clara e fundo branco; o clique abre `mailto:` para esse endereço.
3. **Given** o card de WhatsApp, **When** o visitante observa e clica, **Then** vê ícone laranja, rótulo “WhatsApp”, o número do campo do Node e link `https://wa.me/` derivado dos dígitos (com DDI 55 quando número brasileiro).
4. **Given** o botão **Enviar Mensagem**, **When** observa o estilo, **Then** o botão é laranja `#FD7B1A`, texto branco, sem borda aparente, com proporção próxima de 180×56 no desktop.

---

### User Story 3 — Visitante vê o painel de especialistas conforme o Figma (Priority: P1)

Na coluna direita, o visitante vê ilustração, título e texto de apoio centralizados sobre o fundo `#E5EEFF`.

**Why this priority**: Fecha a fidelidade visual da metade direita da composição.

**Independent Test**: Comparar `/contato` desktop com o frame Figma de Contato (painel direito).

**Acceptance Scenarios**:

1. **Given** Node com imagem e textos do painel preenchidos, **When** observa a coluna direita, **Then** o fundo é `#E5EEFF`, a imagem está acima do título/texto e o bloco de conteúdo está centralizado no painel.
2. **Given** o título curto do Node (ex.: “Time de especialistas”), **When** é exibido, **Then** aparece em destaque (negrito) e centralizado.
3. **Given** o texto longo do Node, **When** é exibido, **Then** aparece centralizado abaixo do título, legível e sem overflow horizontal.

---

### User Story 4 — Visitante mobile vê empilhamento usable (Priority: P1)

Em viewport &lt;992px, formulário e painel empilham; o painel ocupa 100% da largura; a linha de ações quebra sem overflow; o botão de envio ocupa a largura disponível.

**Why this priority**: Grande parte do tráfego é mobile; o bleed assimétrico de desktop não se aplica.

**Independent Test**: Abrir `/contato` em viewport ≤991.98px e percorrer formulário + painel + cards.

**Acceptance Scenarios**:

1. **Given** viewport &lt;992px, **When** a página carrega, **Then** a coluna do formulário aparece acima do painel visual.
2. **Given** o mesmo viewport, **When** observa o painel, **Then** ele ocupa a largura total da área de conteúdo (sem exigir “colar só na direita”).
3. **Given** a linha de ações, **When** o espaço horizontal é insuficiente, **Then** botão e cards quebram em linhas sem causar scroll horizontal da página.
4. **Given** viewport mobile, **When** observa o botão **Enviar Mensagem**, **Then** ele ocupa a largura total da coluna do formulário.

---

### User Story 5 — Visitante vê o título de Contato no token tipográfico do Figma (Priority: P1)

Visitante abre `/contato` e lê “Envie sua mensagem” com a tipografia do anexo: Inter 600, 30px, line-height 38px, letter-spacing −0.3px, cor preta `#000000`.

**Why this priority**: O anexo define a fonte de verdade tipográfica do título; a página Contato é o caso canônico.

**Independent Test**: Inspecionar o título da coluna esquerda em `/contato` e comparar com as propriedades do Figma.

**Acceptance Scenarios**:

1. **Given** `/contato` carregada, **When** inspeciona “Envie sua mensagem”, **Then** a tipografia efetiva é Inter, peso 600, tamanho 30px, line-height 38px, letter-spacing −0.3px e cor `#000000`.
2. **Given** o mesmo título, **When** compara com o estado anterior (Poppins/clamp), **Then** não há mais Poppins nem tamanho fluido divergente do token neste título.

---

### User Story 6 — Visitante encontra títulos de seção consistentes no site (Priority: P1)

Nas páginas públicas, títulos de seção com o mesmo papel tipográfico de “Envie sua mensagem” compartilham o mesmo token (Inter 600 / 30 / 38 / −0.3px / `#000`), em vez de misturas de Poppins e tamanhos ad hoc.

**Why this priority**: Pedido explícito de padronização para o projeto inteiro a partir do anexo.

**Independent Test**: Amostrar títulos de seção em Home, Quem Somos, Para Estudantes, Para Empresas, Contato e Vagas; verificar família/peso/tamanho/line-height/letter-spacing/cor.

**Acceptance Scenarios**:

1. **Given** uma página pública com título de seção principal de bloco/conteúdo, **When** o visitante compara com “Envie sua mensagem”, **Then** o token tipográfico é o mesmo (Inter 600 / 30px / 38px / −0.3px / `#000000`), salvo exceções listadas em Fora (heroes/banners/microtítulos).
2. **Given** o tema após a padronização, **When** um novo bloco público usa o estilo base de título de seção, **Then** herda o token sem precisar redefinir família/tamanho localmente.

---

### User Story 7 — Visitante envia o formulário sem regressão (Priority: P2)

O refino visual não quebra o envio do webform nem a legibilidade dos campos.

**Why this priority**: Layout novo não pode comprometer a função principal da página.

**Independent Test**: Preencher campos obrigatórios válidos e submeter; repetir com e-mail inválido.

**Acceptance Scenarios**:

1. **Given** dados válidos, **When** envia o formulário, **Then** recebe confirmação de sucesso e a submission é registrada.
2. **Given** e-mail inválido ou obrigatório vazio, **When** tenta enviar, **Then** o envio é bloqueado com feedback amigável.
3. **Given** desktop, **When** observa os campos de texto do formulário, **Then** eles mantêm bordas finas, cantos suavemente arredondados e altura confortável para digitação (proporcional ao grid de design ~57px de altura nos inputs de linha).

---

### Edge Cases

- Campo de e-mail ou WhatsApp vazio no Node: o card correspondente não é exibido (ou fica oculto), sem deixar “caixa vazia” na linha de ações.
- Somente um dos cards presente: botão + card restante permanecem alinhados de forma legível (sem grandes buracos).
- Imagem do painel ausente: título e texto do painel ainda aparecem centralizados sobre `#E5EEFF`.
- Textos do painel vazios: a imagem (se houver) continua centralizada no painel.
- Viewport intermediário (tablet ~768–991px): empilhamento mobile se aplica; sem overflow horizontal.
- Zoom/browser com scrollbar: o painel direito continua visualmente colado à borda direita útil da viewport em desktop.
- Mensagens de validação/sucesso do webform: não empurram o layout de forma a gerar overflow ou sobreposição com os cards.
- Título de seção muito longo (quebra de linha): line-height 38px e letter-spacing −0.3px permanecem; não há overflow horizontal.
- Página/bloco cujo “título” era display de hero: permanece com tipografia própria (Fora); não é forçado ao token de seção.
- Título de card interno (ex.: nome de benefício): permanece fora do token de seção.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: A página `/contato` (Node Contato, view mode full) MUST apresentar layout em duas colunas no desktop (≥992px): formulário/ações à esquerda e painel visual à direita.
- **FR-002**: No desktop, o painel visual direito MUST usar fundo `#E5EEFF` e MUST alcançar a borda direita da viewport (bleed), sem margem branca lateral direita causada pelo container da página.
- **FR-003**: No desktop, o conteúdo da coluna esquerda (título, formulário e linha de ações) MUST alinhar-se à margem esquerda de referência do header/conteúdo principal do tema (equivalente visual a um container restrito).
- **FR-004**: As colunas do desktop MUST esticar na mesma linha de layout (painel com altura coerente à coluna do formulário).
- **FR-005**: A coluna esquerda MUST exibir o título “Envie sua mensagem” e o webform referenciado pelo Node.
- **FR-006**: Abaixo do webform, a página MUST renderizar a linha de ações com o botão de envio do formulário e os cards de contato derivados dos campos do Node.
- **FR-007**: O card de E-mail MUST exibir ícone, rótulo “E-mail” e o valor de `field_email`, com link `mailto:` e aparência de card (fundo branco, borda fina `#C6C6CD`, cantos arredondados, valor em tom `#45464D`), com proporção desktop próxima de 280×70.
- **FR-008**: O card de WhatsApp MUST exibir ícone, rótulo “WhatsApp” e o valor de `field_phone_wpp`, com link `https://wa.me/` adequado, mesma linguagem visual do card de E-mail, proporção desktop próxima de 214×70.
- **FR-009**: O botão **Enviar Mensagem** MUST usar cor `#FD7B1A`, texto branco e aparência sem borda; no desktop, proporção próxima de 180×56; em viewport &lt;992px, MUST ocupar 100% da largura da coluna do formulário.
- **FR-010**: O painel direito MUST renderizar `field_imagem` (quando presente), o título curto do Node e o texto longo do Node, centralizados no painel.
- **FR-010a**: A imagem do painel (`field_imagem`) MUST ser servida via Image Style `contato_painel` (scale max-width 640, conversão WebP), com `loading="lazy"` e `decoding="async"`, e MUST ser omitida se o campo estiver vazio. Sem rewrite dos bytes do arquivo seed (057).
- **FR-011**: Em viewport &lt;992px, formulário e painel MUST empilhar (formulário primeiro); o painel MUST ocupar 100% da largura disponível; a linha de ações MUST quebrar sem overflow horizontal.
- **FR-012**: Campos do webform MUST manter aparência de inputs com borda fina, cantos suaves e altura confortável; pares de campos nas primeiras linhas MUST permanecer lado a lado no desktop e empilhar no mobile conforme o comportamento já esperado do formulário.
- **FR-013**: Estilos de layout específicos de Contato (bleed, cards, painel) MUST não alterar o layout de outras rotas. A tipografia de títulos de seção MUST propagar-se globalmente conforme FR-016–FR-018.
- **FR-014**: A refatoração MUST preservar o envio funcional do webform `contato` (sucesso e erros de validação).
- **FR-015**: Se e-mail ou WhatsApp estiverem vazios no Node, o card correspondente MUST ser omitido sem quebrar o alinhamento dos elementos restantes.
- **FR-016**: O título “Envie sua mensagem” MUST usar o token tipográfico de título de seção: Inter, peso 600, 30px, line-height 38px, letter-spacing −0.3px, cor `#000000`.
- **FR-017**: O tema MUST expor um estilo base reutilizável de **título de seção** com exatamente esse token, para uso nas páginas/blocos públicos.
- **FR-018**: Títulos de seção públicos existentes que hoje usam tipografia ad hoc (ex.: Poppins em headers de bloco com o mesmo papel) MUST ser alinhados a esse token, sem alterar microtítulos, heroes/banners com spec própria, nav ou rodapé.

### Key Entities

- **Node Contato**: Página canônica `/contato`; fornece webform, imagem do painel, textos do painel e canais E-mail/WhatsApp.
- **Webform Contato**: Formulário embutido na coluna esquerda (campos e submit já existentes).
- **Card de contato**: Unidade visual clicável (E-mail ou WhatsApp) na linha de ações.
- **Painel visual**: Faixa direita (ou bloco inferior no mobile) com fundo `#E5EEFF`, ilustração e textos de apoio.
- **Token de título de seção**: Spec tipográfica canônica (Inter 600 / 30 / 38 / −0.3px / `#000`) aplicada a títulos de seção públicos.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Em revisão visual desktop (≥992px) contra o Figma de Contato, o painel direito toca a borda direita da viewport em 100% das capturas de referência (sem faixa branca residual à direita do painel).
- **SC-002**: Em desktop, a margem esquerda do bloco do formulário coincide visualmente com a margem do conteúdo do header (desvio ≤8px em viewport 1440px de referência).
- **SC-003**: Em desktop, botão e cards da linha de ações aparecem na mesma fileira e batem as proporções-alvo (±10% em largura/altura) do Figma (180×56 / 280×70 / ~214×70).
- **SC-004**: Em viewport 375px e 768px, não há scroll horizontal causado pelo layout de Contato; botão de envio ocupa a largura da coluna; painel em 100% da largura.
- **SC-005**: 100% dos envios de teste com dados válidos continuam a concluir com confirmação de sucesso após o refino visual.
- **SC-006**: Revisores de UI conseguem validar a fidelidade Figma da página Contato em até 10 minutos (checklist: bleed, alinhamento esquerdo, cards, painel, tipografia do título, mobile).
- **SC-007**: Em `/contato`, o título “Envie sua mensagem” corresponde 100% ao token tipográfico do anexo (Inter 600 / 30px / 38px / −0.3px / `#000000`) na inspeção computada.
- **SC-008**: Em amostragem de ao menos 5 páginas/blocos públicos distintos, ≥95% dos títulos de seção no escopo usam o mesmo token; exceções documentadas apenas para heroes/banners/microtítulos.

## Assumptions

- O Content Type `contato`, alias `/contato`, webform `contato` e campos (`webform`, `field_imagem`, `field_email`, `field_phone_wpp`, textos do painel) já existem via features 017/018; esta feature é **refino de apresentação**, não recriação estrutural.
- O header do tema continua usando container restrito como referência de margem esquerda; a solução de alinhamento assimétrico (conteúdo esquerdo “dentro” + painel direito em bleed) é a abordagem esperada.
- Breakpoint de empilhamento: large do Bootstrap 5 (992px), coerente com `col-lg-*` já usados na página.
- Cores e medidas do Figma fornecidas pelo pedido (`#E5EEFF`, `#FD7B1A`, `#C6C6CD`, `#45464D`, dimensões dos cards/botão/inputs) são a fonte de verdade visual desta iteração.
- O anexo tipográfico de “Envie sua mensagem” (Inter 600 / 30 / 38 / −0.3px / `#000`) é a **fonte de verdade** para títulos de seção públicos do projeto, mesmo onde specs anteriores citavam Poppins.
- “Título de seção” = cabeçalho principal de uma seção/bloco de página pública com o mesmo papel de “Envie sua mensagem” (tipicamente o H2/header do bloco). Não inclui títulos de cards, CTAs, nav, footer, labels nem displays de hero/banner com Figma próprio.
- Inter já está disponível no tema (carregada junto com Poppins); a padronização reusa essa família.
- Padding vertical extremo do mockup (~318px) é referência de centralização generosa no frame Figma; na implementação, a centralização vertical do conteúdo do painel (em vez de paddings fixos gigantes) é aceitável desde que o resultado visual seja equivalente.
- Proporção de colunas desktop ~7/5 ou ~8/4 é aceitável desde que o painel direito alcance ~499px de largura em viewport próximo ao frame de design e o formulário caiba sem aperto.
- Não há mudança obrigatória de Configuration Management / `hook_update_N` salvo se a implementação descobrir ausência dos cards por falta de campos no display — nesse caso, garantir display + export `drush cex` e update idempotente conforme governança do projeto.
- Durante a implementação, preferir CSS/SCSS limpo (Grid/Flexbox, aninhamento raso) e skills de CSS do pacote `mattpocock/skills` quando disponíveis no ambiente do agente; isso é diretriz de qualidade de código, não requisito de negócio.
- Header, footer e botão flutuante de WhatsApp permanecem inalterados (exceto herança indireta se algum título de seção estiver neles — hoje não é o caso).
