# Feature Specification: Bloco Nossa Metodologia

**Feature Directory**: `specs/005-nossa-metodologia`  
**Created**: 2026-09-11  
**Updated**: 2026-09-11 (itens editáveis: etapas imagens + passos paragraph)  
**Status**: Implemented — modelo modular (etapas + passos)  
**Input do usuário**: Bloco gerenciável “Nossa Metodologia” (`nossa_metodologia`) no Drupal 11, 100% editável no painel, com título (`field_text_simple`), subtítulo (`field_text_simple_long`), **etapas superiores como imagens multi-valor** (`field_image`) e **passos inferiores como paragraph** `metodologia_passo_p` (ícone + título); layout com textos à direita em desktop / centralizados em mobile; tipografia Poppins; instância exclusiva na home (`<front>`) imediatamente abaixo de “Nossos Diferenciais”; exportação estrutural via Configuration Management.

## Escopo

### Inclui

- Tipo de bloco customizado **Nossa Metodologia** (`nossa_metodologia`) 100% gerenciável no painel.
- Campos do bloco: título da seção, subtítulo/descrição, lista de imagens das etapas superiores e lista de passos (paragraph ícone + título).
- Apresentação pública alinhada ao layout de referência: textos à direita em telas médias/grandes e centralizados em telas estreitas; etapas e passos reorganizáveis via CSS responsivo.
- Instância do bloco publicada **somente na home** (`<front>`), posicionada **imediatamente abaixo** do bloco “Nossos Diferenciais”, na região de conteúdo (ou região equivalente do tema Barrio em uso).
- Exportação de toda alteração estrutural (tipo de bloco, field instances/storages novos quando inevitáveis, form/view displays, placement do bloco, paragraph type) para `config/sync` via Configuration Management.
- Placeholders/fallbacks quando campos opcionais estiverem vazios.
- Tipografia Poppins em toda a seção.

### Fora

- Campos de cor ou setas editáveis no CMS (setas entre passos são CSS decorativo).
- Variantes desktop/mobile por item (um asset por etapa/passo; o layout CSS faz o reflow).
- Exibição do bloco em páginas que não sejam a home.
- Alterações em `core/` ou `vendor/`.
- Artes monolíticas desktop/mobile do fluxograma (substituídas pelo modelo item a item).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê a metodologia institucional (Priority: P1)

Visitante acessa a home e, abaixo da seção “Nossos Diferenciais”, reconhece “Nossa Metodologia”: título e subtítulo no alinhamento do design e o fluxograma legível para o dispositivo em uso.

**Why this priority**: É o valor principal da feature — comunicar a metodologia da marca de forma clara e fiel ao design.

**Independent Test**: Com o bloco publicado e preenchido na home, abrir a página pública e validar conteúdo, ordem relativa aos diferenciais e troca de arte desktop/mobile sem depender de outros blocos além do posicionamento.

**Acceptance Scenarios**:

1. **Given** um bloco “Nossa Metodologia” publicado na home com título, subtítulo e imagens desktop/mobile, **When** o visitante abre a home, **Then** vê título, subtítulo e o fluxograma na seção correspondente.
2. **Given** viewport de tela média/grande (≥768px), **When** a seção é renderizada, **Then** título e subtítulo ficam alinhados à direita e a arte desktop do fluxograma é a exibida.
3. **Given** viewport estreita (&lt;768px), **When** a seção é renderizada, **Then** título e subtítulo ficam centralizados e a arte mobile do fluxograma é a exibida (legível, sem overflow horizontal).
4. **Given** a home com “Nossos Diferenciais” e “Nossa Metodologia” publicados, **When** o visitante rola o conteúdo principal, **Then** “Nossa Metodologia” aparece imediatamente abaixo de “Nossos Diferenciais”.

---

### User Story 2 — Editor gerencia o bloco sem suporte técnico (Priority: P1)

Editor de conteúdo cria ou edita o bloco no painel, preenche textos, envia as duas artes do fluxograma e publica. As mudanças refletem no site sem alteração de código.

**Why this priority**: O bloco deve ser 100% gerenciável; caso contrário a feature não atende o requisito de operação.

**Independent Test**: Criar o bloco do zero no admin, publicar na home e conferir a página pública.

**Acceptance Scenarios**:

1. **Given** um editor autenticado com permissão de blocos, **When** cria um bloco do tipo “Nossa Metodologia”, **Then** consegue preencher título, subtítulo, imagem desktop e imagem mobile.
2. **Given** um bloco existente, **When** o editor altera textos ou troca qualquer das imagens e salva, **Then** a home reflete as mudanças após publicação/cache esperado do site.
3. **Given** o formulário do bloco, **When** o editor cadastra apenas a imagem desktop (mobile vazio), **Then** o sistema aceita o salvamento e a apresentação pública usa fallback seguro (ver User Story 3).

---

### User Story 3 — Fallbacks preservam o layout (Priority: P2)

Quando algum campo opcional estiver vazio, a seção não quebra: omite textos vazios ou usa fallback de imagem sem erro de página.

**Why this priority**: Evita páginas quebradas durante cadastro parcial ou migração de conteúdo.

**Independent Test**: Publicar o bloco com campos omitidos um a um e verificar a home.

**Acceptance Scenarios**:

1. **Given** bloco sem imagem mobile, **When** a home é vista em viewport estreita, **Then** a arte desktop é usada como fallback (nunca quebra o layout).
2. **Given** bloco sem imagem desktop e sem mobile, **When** a página é exibida, **Then** título/subtítulo (quando preenchidos) ainda renderizam sem erro; a área do diagrama é omitida ou usa placeholder seguro.
3. **Given** título ou subtítulo vazio, **When** a seção é exibida, **Then** o elemento textual correspondente é omitido (sem headings/parágrafos vazios).

### Edge Cases

- Título e subtítulo ambos vazios → seção ainda pode exibir o fluxograma se houver imagem; sem erro.
- Imagem desktop presente e mobile ausente → mobile usa desktop (fallback).
- Imagem mobile presente e desktop ausente → desktop usa mobile como fallback, ou omite o diagrama se a política de apresentação exigir arte desktop; default adotado: usar a única arte disponível em qualquer viewport.
- Arte muito larga → escala dentro do container sem overflow horizontal.
- Texto longo no subtítulo → quebra de linha natural; sem truncamento por CSS.
- Alt da imagem ausente → usar texto vazio ou fallback acessível sem quebrar o markup.
- Bloco acidentalmente colocado fora da home → não deve aparecer em outras rotas públicas (visibilidade restrita a `<front>`).

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “Nossa Metodologia” com machine name `nossa_metodologia`.
- **FR-002**: O bloco DEVE expor: título da seção (texto curto sem formatação), subtítulo/descrição (texto longo sem formatação), lista de imagens das etapas superiores e lista de passos inferiores (paragraph com ícone + título).
- **FR-003**: A modelagem DEVE **reutilizar storages de campo já existentes** no projeto para textos e para as imagens das etapas — não criar novos field storages com nomes paralelos quando o equivalente já existir. Em particular:
  - texto curto → storage `field_text_simple`;
  - texto longo → storage `field_text_simple_long`;
  - etapas (imagens) → storage `field_image` em `block_content` (cardinalidade ilimitada).
- **FR-004**: Os passos inferiores DEVEM usar o paragraph type **`metodologia_passo_p`** com ícone (`field_image`) e título (`field_text_simple`), referenciados pelo bloco via storage novo `field_metodologia_passos` (`entity_reference_revisions`). Setas entre passos são CSS, não campos CMS.
- **FR-005**: Todo o conteúdo exibido (títulos, textos, imagens e ordem relativa) DEVE ser gerenciável no painel; nenhum texto institucional desta seção fica “hardcoded” como única fonte de verdade (placeholders de template só para ausência de dado).
- **FR-006**: Alterações estruturais (tipo de bloco, fields, form/view displays e posicionamento/visibilidade do bloco) DEVEM ser exportáveis via Configuration Management (`drush cex`) e versionadas em `config/sync` com o restante do site.

**Posicionamento**

- **FR-007**: A instância do bloco DEVE estar configurada para exibição **exclusiva na home** (caminho/contexto `<front>`).
- **FR-008**: Na região de conteúdo principal (ou região customizada equivalente do tema Barrio em uso na home), o bloco DEVE ficar **imediatamente abaixo** da instância de “Nossos Diferenciais”.
- **FR-009**: O placement e a restrição de visibilidade DEVEM constar na configuração exportada (não apenas como ajuste manual local não versionado).

**Apresentação**

- **FR-010**: Em viewport ≥768px, título e subtítulo DEVEM aparecer alinhados à direita; em viewport &lt;768px, DEVEM aparecer centralizados.
- **FR-011**: Etapas e passos DEVEM reorganizar-se responsivamente (fila em desktop; coluna/wrap em mobile) sem overflow horizontal.
- **FR-012**: Se etapas ou passos estiverem vazios, a apresentação DEVE omitir a área correspondente sem erro.
- **FR-013**: A tipografia do bloco DEVE usar a família Poppins em toda a estrutura da seção.
- **FR-014**: A composição da seção DEVE ocupar a largura útil do container do tema, alinhada ao mockup de referência.

**Conteúdo de referência (placeholders / seed editorial)**

- **FR-015**: Placeholders e conteúdo inicial de referência DEVEM refletir:
  - Título: “Nossa Metodologia”
  - Subtítulo: “Transformamos potencial em performance por meio de uma metodologia que une autoconhecimento, alinhamento cultural e desenvolvimento contínuo.”
  - Etapas: imagens Identidade / Pertencimento / Performance (upload editorial)
  - Passos: títulos de referência (pessoa/ambiente/resultados/cresce/empresa); ícones upload editorial.

**Operação / sync**

- **FR-016**: Após criar/ajustar tipo, fields, displays e placement, o fluxo de entrega DEVE incluir exportação de configuração (`drush cex`) para o diretório `config/sync`, garantindo paridade entre ambientes via importação posterior (`drush cim`).

### Key Entities

- **`nossa_metodologia` (block_content)**: seção institucional — título (`field_text_simple`), subtítulo (`field_text_simple_long`), etapas (`field_image` multi), passos (`field_metodologia_passos` → `metodologia_passo_p`).
- **`metodologia_passo_p` (paragraph)**: ícone (`field_image`) + título (`field_text_simple`).
- **Etapa superior**: imagem individual da sequência Identidade / Pertencimento / Performance.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Um editor cria e publica a seção completa (título, subtítulo, duas imagens) em menos de 8 minutos, sem suporte de desenvolvimento.
- **SC-002**: Em viewport ≥768px, 100% das visualizações mostram textos alinhados à direita e a arte desktop do fluxograma.
- **SC-003**: Em viewport &lt;768px, 100% das visualizações mostram textos centralizados e a arte mobile (ou fallback desktop se mobile ausente), sem overflow horizontal.
- **SC-004**: Na home, a seção “Nossa Metodologia” aparece imediatamente abaixo de “Nossos Diferenciais” em 100% das verificações com ambos publicados.
- **SC-005**: Em rotas que não sejam a home, o bloco não é exibido (0 aparições fora de `<front>`).
- **SC-006**: Alterar qualquer texto/imagem no painel reflete na home na próxima visualização após publicação (sem deploy de código).
- **SC-007**: Após exportação, as configs estruturais e de placement desta feature estão presentes em `config/sync` e um ambiente limpo consegue reproduzir o bloco via importação de configuração + conteúdo seed/editorial.
- **SC-008**: Visitantes reconhecem a seção frente ao layout de referência (título, subtítulo, fluxograma) em revisão visual de aceite.

## Assumptions

- O tema customizado do projeto já carrega ou pode carregar Poppins; a feature não introduz nova marca tipográfica fora desta família.
- Storages `field_text_simple` e `field_text_simple_long` já existem para `block_content` e serão **reutilizados** via field instances no bundle `nossa_metodologia`.
- Storage `field_image` (cardinalidade ilimitada) em `block_content` é **reutilizado** para as etapas superiores.
- Paragraph `metodologia_passo_p` usa storages `paragraph.field_image` e `paragraph.field_text_simple` existentes.
- Storage novo: `field_metodologia_passos` (`entity_reference_revisions`).
- Storages `field_image_desktop` / `field_image_mobile` foram **removidos** (substituídos pelo modelo item a item).
- Breakpoint de layout: 767px / 768px (header) e empilhamento de passos em mobile.
- Setas entre passos são CSS decorativo.
- Textos desta fase são somente pt-BR.
