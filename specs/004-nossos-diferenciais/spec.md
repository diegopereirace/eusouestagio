# Feature Specification: Bloco Nossos Diferenciais

**Feature Directory**: `specs/004-nossos-diferenciais`  
**Created**: 2026-09-11  
**Status**: Draft  
**Input do usuário**: Bloco gerenciável “Nossos Diferenciais” (Custom Block + Paragraph), layout Figma em duas colunas (imagem com geometria decorativa + lista de diferenciais com ícone/título/descrição), tipografia Poppins, conteúdo 100% editável no painel, reutilização estrita de storage de campos existentes.

## Escopo

### Inclui

- Tipo de bloco customizado **Nossos Diferenciais** (`nossos_diferenciais`) gerenciável no painel.
- Tipo de paragraph **Item de Diferencial** (`diferencial_item_p`) para cada item da lista.
- Campos do bloco: título da seção, subtítulo, imagem de destaque (arquivo único) e lista de diferenciais.
- Campos do item: ícone, título e descrição.
- Apresentação pública alinhada ao layout de referência (header + duas colunas em telas grandes; lista com ícone à esquerda do texto).
- Placeholders/fallbacks de conteúdo e imagem quando campos estiverem vazios.
- Estilo tipográfico Poppins no bloco e elementos geométricos decorativos atrás da imagem de destaque (sem exigir upload extra de “arte de fundo”).

### Fora

- Edição do layout via Layout Builder / drag-and-drop de colunas.
- Upload de múltiplas imagens fatiadas no CMS (o recorte visual do Figma é apresentação; o editor cadastra **uma** imagem).
- Campo de cor por item no CMS (cores dos títulos seguem a ordem visual do design).
- Reutilização do paragraph legado `icone_titulo_descricao` no lugar de `diferencial_item_p` (machine name novo é obrigatório nesta feature).
- Alterações em `core/` ou `vendor/`.
- Código Twig/CSS/YAML de implementação nesta etapa de especificação (entregue em `/speckit-plan` + `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê os diferenciais institucionais (Priority: P1)

Visitante acessa a página onde o bloco está publicado e reconhece a seção “Nossos Diferenciais”: título, subtítulo, imagem de destaque à esquerda (em desktop) e três diferenciais com ícone, título destacado e descrição.

**Why this priority**: É o valor principal da feature — comunicar o diferencial da marca de forma clara e fiel ao design.

**Independent Test**: Com um bloco publicado e preenchido, abrir a página pública e validar conteúdo + layout sem depender de outros blocos.

**Acceptance Scenarios**:

1. **Given** um bloco “Nossos Diferenciais” publicado com título, subtítulo, imagem e três itens, **When** o visitante abre a página, **Then** vê título em destaque, subtítulo explicativo, imagem de destaque e a lista completa de itens na ordem cadastrada.
2. **Given** viewport de tela grande, **When** a seção é renderizada, **Then** imagem e lista aparecem lado a lado (duas colunas).
3. **Given** viewport estreita (mobile), **When** a seção é renderizada, **Then** o conteúdo empilha de forma legível (imagem e lista em sequência), sem sobreposição que impeça leitura.
4. **Given** a seção renderizada, **When** o visitante observa os títulos dos itens, **Then** o 1º título aparece em verde, o 2º em azul-escuro e o 3º em laranja/terracota, conforme o design de referência.

---

### User Story 2 — Editor gerencia o bloco sem suporte técnico (Priority: P1)

Editor de conteúdo cria ou edita o bloco no painel, preenche textos, envia a imagem única, adiciona/reordena/remove itens (ícone + título + descrição) e publica. As mudanças refletem no site sem alteração de código.

**Why this priority**: O bloco deve ser 100% gerenciável; caso contrário a feature não atende o requisito de operação.

**Independent Test**: Criar o bloco do zero no admin, publicar e conferir a página pública.

**Acceptance Scenarios**:

1. **Given** um editor autenticado com permissão de blocos, **When** cria um bloco do tipo “Nossos Diferenciais”, **Then** consegue preencher título, subtítulo, imagem de destaque e a lista de itens.
2. **Given** um bloco existente, **When** o editor altera textos, troca a imagem ou reordena itens e salva, **Then** a página pública reflete as mudanças após publicação/cache esperado do site.
3. **Given** o formulário do item, **When** o editor cadastra ícone, título e descrição, **Then** os três dados ficam disponíveis na renderização do item.

---

### User Story 3 — Fallbacks preservam o layout (Priority: P2)

Quando algum campo opcional estiver vazio, a seção não quebra: usa placeholder visual/textual de referência ou omite o elemento de forma segura.

**Why this priority**: Evita páginas quebradas durante cadastro parcial ou migração de conteúdo.

**Independent Test**: Publicar o bloco com campos omitidos um a um e verificar a página.

**Acceptance Scenarios**:

1. **Given** bloco sem imagem de destaque, **When** a página é exibida, **Then** um placeholder de imagem mantém a estrutura da coluna visual sem erro.
2. **Given** bloco sem itens na lista, **When** a página é exibida, **Then** título/subtítulo (quando preenchidos) e área da imagem ainda renderizam sem erro de template.
3. **Given** item sem ícone, **When** a lista é exibida, **Then** título e descrição do item permanecem legíveis (ícone omitido ou placeholder).

### Edge Cases

- Título ou subtítulo vazios → omitir o elemento correspondente; não exibir headings/parágrafos vazios.
- Mais de 3 itens → renderizar todos na ordem cadastrada; cores de título ciclam pela sequência do design (1º verde, 2º azul, 3º laranja, 4º verde…).
- Menos de 3 itens → renderizar apenas os existentes.
- Imagem muito larga/alta → exibida de forma responsiva (`img-fluid` / equivalente), sem estourar a coluna.
- Texto longo na descrição → quebra de linha natural; não corta de forma truncada por CSS.
- Alt da imagem/ícone ausente → usar texto vazio ou fallback acessível sem quebrar o markup.

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “Nossos Diferenciais” com machine name `nossos_diferenciais`.
- **FR-002**: O bloco DEVE expor: título da seção (texto curto sem formatação), subtítulo (texto longo sem formatação), imagem de destaque (uma imagem) e lista ordenável de diferenciais.
- **FR-003**: Cada diferencial DEVE ser um paragraph do tipo “Item de Diferencial” com machine name obrigatório `diferencial_item_p`, contendo ícone (imagem), título (texto curto) e descrição (texto longo).
- **FR-004**: A modelagem DEVE **reutilizar storages de campo já existentes** no projeto — não criar novos field storages com nomes paralelos quando o equivalente já existir. Em particular:
  - texto curto → storage `field_text_simple` (o rótulo de produto “texto curto / simple small” mapeia para este storage canônico; **não** criar `field_text_simple_small`);
  - texto longo → storage `field_text_simple_long`;
  - imagem/ícone → storage `field_image`;
  - lista de itens → novo field instance/storage de referência a paragraphs `field_diferenciais_lista` (Entity Reference Revisions → `diferencial_item_p`), pois não há storage equivalente reutilizável com esse propósito no bloco.
- **FR-005**: Todo o conteúdo exibido (títulos, textos, imagens, itens e ordem) DEVE ser gerenciável no painel; nenhum texto institucional desta seção fica “hardcoded” como única fonte de verdade (placeholders de template só para ausência de dado).
- **FR-006**: Alterações estruturais (tipos, fields, displays) DEVEM ser exportáveis via Configuration Management e versionadas com o restante do site.

**Apresentação**

- **FR-007**: Em telas grandes, o bloco DEVE apresentar duas colunas principais: coluna visual (imagem + geometria decorativa) e coluna de conteúdo da lista; o título e o subtítulo da seção DEVEM aparecer acima dessa grade, alinhados ao layout de referência.
- **FR-008**: A coluna visual DEVE renderizar a imagem de destaque de forma responsiva e, na ausência de imagem, um placeholder nativo do template.
- **FR-009**: Elementos geométricos do design (círculo azul sólido, anel verde, anéis cinza claros) DEVEM ser decoração de apresentação (estilo), sem campos adicionais no CMS.
- **FR-010**: Cada item da lista DEVE exibir ícone à esquerda e, à direita, título em destaque (negrito) + descrição, com alinhamento superior entre ícone e texto.
- **FR-011**: A tipografia do bloco DEVE usar a família Poppins em toda a estrutura da seção.
- **FR-012**: Cores dos títulos dos itens DEVEM seguir a sequência do design por posição: 1º verde, 2º azul-escuro, 3º laranja/terracota (ciclável).

**Conteúdo de referência (placeholders / seed editorial)**

- **FR-013**: Placeholders e conteúdo inicial de referência DEVEM refletir:
  - Título: “Nossos Diferenciais”
  - Subtítulo: “Não conectamos apenas estudantes e empresas. Criamos relações duradouras por meio de uma seleção inteligente, gestão completa e desenvolvimento contínuo.”
  - Item 1 — Encontramos: “Selecionamos candidatos com aderência técnica e comportamental.”
  - Item 2 — Gerenciamos: “Assumimos toda a gestão administrativa e legal do estágio.”
  - Item 3 — Desenvolvemos: “Acompanhamos continuamente a evolução do estudante para garantir desempenho e permanência.”

### Key Entities

- **`nossos_diferenciais` (block_content)**: seção institucional — título (`field_text_simple`), subtítulo (`field_text_simple_long`), imagem de destaque (`field_image`), lista (`field_diferenciais_lista`).
- **`diferencial_item_p` (paragraph)**: item da lista — ícone (`field_image`), título (`field_text_simple`), descrição (`field_text_simple_long`).
- **Imagem de destaque**: arte única cadastrável; eventual aparência “fatiada” do mockup é responsabilidade da apresentação, não do modelo de dados.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Um editor cria e publica a seção completa (título, subtítulo, imagem, ≥3 itens) em menos de 10 minutos, sem suporte de desenvolvimento.
- **SC-002**: Em viewport ≥992px, 100% das visualizações da seção mostram duas colunas (imagem | lista) sem quebra de leitura.
- **SC-003**: Em viewport &lt;768px, 100% das visualizações mantêm textos legíveis e imagem sem overflow horizontal.
- **SC-004**: Com imagem ausente, a página pública carrega sem erro e preserva a estrutura da seção (placeholder visível).
- **SC-005**: Alterar qualquer texto/imagem/item no painel reflete na página pública na próxima visualização após publicação (sem deploy de código).
- **SC-006**: Visitantes reconhecem a seção frente ao layout de referência (título, subtítulo, composição visual com geometria, três diferenciais com ícones e cores de título na ordem correta) em revisão visual de aceite.

## Assumptions

- O tema customizado do projeto já carrega ou pode carregar Poppins; a feature não introduz nova marca tipográfica fora desta família.
- Storages `field_text_simple`, `field_text_simple_long` e `field_image` já existem para `block_content` e/ou `paragraph` e serão **reutilizados** via novas field instances nos bundles desta feature.
- O pedido original citava `field_text_simple_small`; no vocabulário canônico do projeto o equivalente reutilizável é `field_text_simple` — adotado para cumprir reutilização de storage.
- Existe paragraph semelhante `icone_titulo_descricao`, mas o machine name `diferencial_item_p` é requisito explícito; não se substitui um pelo outro nesta feature.
- Posicionamento do bloco na home (ou outra região) é feito pelo editor/administrador de blocos; a feature entrega o tipo e a apresentação, não um placement fixo obrigatório.
- Textos desta fase são somente pt-BR.
- Ícones dos itens são arquivos de imagem (PNG/SVG/WebP etc. conforme política atual de `field_image`), não classes de icon font.
- Entregáveis de implementação pedidos pelo usuário (resumo YAML de fields, Twig do bloco, Twig do paragraph, CSS/SCSS dos círculos e Poppins) são produzidos nas fases `/speckit-plan` e `/speckit-implement`, não nesta especificação.
