# Feature Specification: Bloco O que fazemos

**Feature Directory**: `specs/006-o-que-fazemos`  
**Created**: 2026-09-13  
**Status**: Implemented  
**Input do usuário**: Bloco gerenciável “O que fazemos” (`o_que_fazemos_bt`) no Drupal 11, 100% editável no painel, com título, descrição e até 3 paragraphs (`o_que_fazemos_item_p`: título + itens ilimitados); card do meio com itens em duas colunas; Bootstrap responsivo; estilo fiel ao layout de referência; instância exclusiva na home abaixo de “Nossa Metodologia”; estrutura e seed deployáveis via Configuration Management + update hook.

## Escopo

### Inclui

- Tipo de bloco customizado **O que fazemos** (`o_que_fazemos_bt`) 100% gerenciável no painel.
- Campos do bloco: título da seção, descrição e lista de até 3 cards (paragraph com título + itens).
- Paragraph type `o_que_fazemos_item_p` com título e lista ilimitada de itens (string multi-valor).
- Apresentação pública alinhada ao layout de referência: três cards coloridos (navy / green / orange por posição), item do meio com itens em duas colunas Bootstrap.
- Instância publicada **somente na home** (`<front>`), imediatamente abaixo de “Nossa Metodologia”, região `content_full`, weight `-2`.
- Exportação estrutural para `config/sync` e seed idempotente em `custom_configs`.
- Tipografia Poppins (já carregada no tema).

### Fora

- Campos de cor editáveis no CMS (cores via CSS por posição).
- Exibição fora da home.
- Alterações em `core/` ou `vendor/`.
- Imagens ou ícones nos cards.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê “O que fazemos” (Priority: P1)

Visitante acessa a home e, abaixo de “Nossa Metodologia”, vê título, descrição e três cards de serviços com listas de itens; no card do meio, os itens aparecem em duas colunas no desktop.

**Acceptance Scenarios**:

1. **Given** o bloco publicado com título, descrição e 3 cards, **When** o visitante abre a home, **Then** vê a seção completa abaixo de Nossa Metodologia.
2. **Given** viewport ≥768px, **When** a seção renderiza, **Then** os três cards ficam lado a lado e o card do meio mostra itens em duas colunas.
3. **Given** viewport &lt;768px, **When** a seção renderiza, **Then** os cards empilham verticalmente sem overflow.

### User Story 2 — Editor gerencia o bloco (Priority: P1)

Editor autentica, edita título/descrição/cards/itens e salva; a home reflete as mudanças após cache.

**Acceptance Scenarios**:

1. **Given** editor com permissão, **When** edita o bloco `o_que_fazemos_bt`, **Then** consegue alterar título, descrição e até 3 paragraphs com itens ilimitados.
2. **Given** formulário do bloco, **When** tenta adicionar um 4º card, **Then** o sistema impede (cardinalidade 3).

### User Story 3 — Fallbacks (Priority: P2)

Campos vazios são omitidos sem quebrar o layout.

**Acceptance Scenarios**:

1. **Given** título ou descrição vazios, **When** a seção renderiza, **Then** o elemento correspondente é omitido.
2. **Given** card sem itens, **When** renderiza, **Then** mostra só o título do card (se houver).
3. **Given** nenhum card, **When** renderiza, **Then** omite a grade de cards sem erro.

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: Tipo de bloco `o_que_fazemos_bt` rotulado “O que fazemos”.
- **FR-002**: Bloco expõe título (`field_text_simple`), descrição (`field_text_simple_long`) e cards (`field_o_que_fazemos_itens` → paragraph).
- **FR-003**: Reutilizar storages de texto existentes em `block_content` e `paragraph` para título; criar apenas storages novos inevitáveis (`field_o_que_fazemos_itens` cardinality 3; `field.storage.paragraph.field_text_simple_multiple` cardinality -1).
- **FR-004**: Paragraph `o_que_fazemos_item_p` com título (`field_text_simple`) e itens (`field_text_simple_multiple`).
- **FR-005**: Máximo de 3 paragraphs no bloco.
- **FR-006**: Card do meio (índice 1) renderiza itens em duas colunas Bootstrap (`col-6`).
- **FR-007**: Cores dos headers via CSS por posição (1º navy, 2º green, 3º orange) usando tokens da marca.
- **FR-008**: Layout responsivo com classes Bootstrap (`container`, `row`, `col-12`, `col-md-4`).
- **FR-009**: Placement em `content_full`, weight `-2`, somente `<front>`, UUID fixo compartilhado com seed.
- **FR-010**: Seed idempotente via `custom_configs_update_11008` com conteúdo de referência da arte.
- **FR-011**: Permissões create/edit/delete no papel `moderador`.
- **FR-012**: Toda estrutura versionada em `config/sync` para deploy (`cim` → `updb` → `cr`).

### Success Criteria

- Home mostra a seção abaixo de Nossa Metodologia, visualmente alinhada à arte.
- Editor altera conteúdo sem deploy de código.
- Deploy em ambiente limpo cria estrutura + seed sem duplicar.
