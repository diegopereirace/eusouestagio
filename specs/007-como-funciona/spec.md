# Feature Specification: Bloco Como funciona

**Feature Directory**: `specs/007-como-funciona`  
**Created**: 2026-09-13  
**Status**: Implemented  
**Input do usuário**: Bloco gerenciável “Como funciona” (`como_funciona_bt`) no Drupal 11; título, subtítulo e até 7 itens textuais de etapas; números, chevrons e setas via CSS responsivo; instância exclusiva na home abaixo de “O que fazemos”; estrutura e seed deployáveis via Configuration Management + update hook.

## Escopo

### Inclui

- Tipo de bloco customizado **Como funciona** (`como_funciona_bt`) gerenciável no painel.
- Campos do bloco: título da seção, subtítulo/descrição e lista de até 7 etapas (paragraph só com texto).
- Paragraph type `como_funciona_item_p` com um único campo textual (`field_text_simple`).
- Apresentação pública alinhada ao layout Figma: fluxo horizontal de chevrons coloridos (navy→laranja), números 1–7 e setas superiores via CSS.
- Instância publicada **somente na home** (`<front>`), imediatamente abaixo de “O que fazemos”, região `content_full`, weight `-1`.
- Exportação estrutural para `config/sync` e seed idempotente em `custom_configs`.
- Tipografia Poppins (já carregada no tema).

### Fora

- Campos de cor, número ou imagem editáveis no CMS para as etapas.
- Faixa de logos “MAIS DE 2.000 EMPRESAS…”.
- Exibição fora da home.
- Alterações em `core/` ou `vendor/`.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê “Como funciona” (Priority: P1)

Visitante acessa a home e, abaixo de “O que fazemos”, vê título, subtítulo e sete etapas em chevrons com números e cores por posição.

**Acceptance Scenarios**:

1. **Given** o bloco publicado com título, subtítulo e 7 etapas, **When** o visitante abre a home, **Then** vê a seção completa abaixo de O que fazemos.
2. **Given** viewport desktop, **When** a seção renderiza, **Then** as etapas ficam em fluxo horizontal com chevrons sobrepostos e setas superiores.
3. **Given** viewport estreito, **When** a seção renderiza, **Then** o fluxo permanece legível sem overflow horizontal quebrado (wrap ou scroll controlado).

### User Story 2 — Editor gerencia o bloco (Priority: P1)

Editor autentica, edita título/subtítulo/textos das etapas e salva; a home reflete as mudanças após cache.

**Acceptance Scenarios**:

1. **Given** editor com permissão, **When** edita o bloco `como_funciona_bt`, **Then** consegue alterar título, subtítulo e até 7 paragraphs textuais.
2. **Given** formulário do bloco, **When** tenta adicionar um 8º item, **Then** o sistema impede (cardinalidade 7).

### User Story 3 — Fallbacks (Priority: P2)

Campos vazios são omitidos sem quebrar o layout.

**Acceptance Scenarios**:

1. **Given** título ou subtítulo vazios, **When** a seção renderiza, **Then** o elemento correspondente é omitido.
2. **Given** etapa sem texto, **When** renderiza, **Then** a etapa vazia é omitida.
3. **Given** nenhuma etapa, **When** renderiza, **Then** omite o fluxo sem erro.

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: Tipo de bloco `como_funciona_bt` rotulado “Como funciona”.
- **FR-002**: Bloco expõe título (`field_text_simple`), subtítulo (`field_text_simple_long`) e etapas (`field_como_funciona_itens` → paragraph).
- **FR-003**: Reutilizar storages de texto existentes; criar apenas `field_como_funciona_itens` (cardinality 7).
- **FR-004**: Paragraph `como_funciona_item_p` com título/texto (`field_text_simple`).
- **FR-005**: Máximo de 7 paragraphs no bloco.
- **FR-006**: Números 1–7, chevrons e setas gerados por CSS (não CMS).
- **FR-007**: Cores das bordas via CSS por posição (sequência navy→laranja).
- **FR-008**: Layout responsivo; tokens Figma: flow ~1150×136, chevron ~178×129, stroke 4px, overlap ~16px.
- **FR-009**: Placement em `content_full`, weight `-1`, somente `<front>`, UUID fixo compartilhado com seed.
- **FR-010**: Seed idempotente via `custom_configs_update_11009` com conteúdo de referência da arte.
- **FR-011**: Permissões create/edit/delete no papel `moderador`.
- **FR-012**: Toda estrutura versionada em `config/sync` para deploy (`cim` → `updb` → `cr`).

### Success Criteria

- Home mostra a seção abaixo de O que fazemos, visualmente alinhada à arte.
- Editor altera textos sem deploy de código (números/formas permanecem CSS).
- Deploy em ambiente limpo cria estrutura + seed sem duplicar.
