# Feature Specification: Link no título das vagas em `/vagas`

**Feature Directory**: `specs/049-vagas-titulo-link`  
**Created**: 2026-11-02  
**Status**: Approved  
**Input do usuário**: Na listagem `/vagas`, adicionar link no título de cada vaga para que o usuário (já cadastrado ou visitante) possa abrir os detalhes da vaga; garantir deploy automático nos demais ambientes.

## Escopo

### Inclui

- Tornar o título de cada card da listagem vertical (`page_1` da View `vagas`) um link para a URL canônica do node `vagas` (Pathauto `/para-estudantes/[slug]`).
- Manter tipografia/aparência do título (herdar estilo atual; hover/focus acessível).
- Preservar CTAs existentes (`Candidatura Rápida` / `Ver Detalhes`) apontando para a mesma URL.
- Documentar deploy: apenas tema + `drush cr` (sem `cex`/`updb`).

### Fora

- Alterar Pathauto para `/vagas/...`.
- Restringir visualização do detalhe a usuários autenticados (detalhe já é público com `access content`; ações de candidatar/salvar continuam gated).
- Cards laranja da Home / Para Estudantes / similares (`block_1`–`block_3`).
- Mudanças em Views YAML, fields ou seeds.
- Alterações em `core/` ou `vendor/`.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Usuário abre detalhes pelo título (Priority: P1)

Na listagem `/vagas`, o usuário clica no título da vaga e é levado à página de detalhes daquela vaga.

**Why this priority**: É o pedido explícito — descoberta do detalhe sem depender só do botão CTA.

**Independent Test**: Em `/vagas`, clicar no `<h3>`/`<a>` do primeiro card e conferir URL canônica + template `node--vagas--full`.

**Acceptance Scenarios**:

1. **Given** a listagem `/vagas` com ≥ 1 vaga, **When** o visitante clica no título do card, **Then** navega para a URL canônica do node (`entity.node.canonical` / alias Pathauto).
2. **Given** um card em destaque e um card comum, **When** o usuário inspeciona ambos, **Then** os dois títulos são links para suas respectivas vagas.
3. **Given** o título linkado, **When** o usuário usa Tab até o link e Enter, **Then** a navegação ocorre (foco visível no link).

---

### User Story 2 — CTAs e layout não regridem (Priority: P1)

Os botões de ação e o layout do card permanecem funcionais e visualmente coerentes após o título virar link.

**Independent Test**: Comparar card destaque vs comum; clicar CTA e título; checar CSS do título.

**Acceptance Scenarios**:

1. **Given** card destaque, **When** a página renderiza, **Then** o CTA “Candidatura Rápida” continua apontando para a mesma URL do título.
2. **Given** card sem destaque, **When** a página renderiza, **Then** o CTA “Ver Detalhes” continua apontando para a mesma URL do título.
3. **Given** o título como link, **When** em estado default, **Then** a tipografia (peso, tamanho, cor base) permanece alinhada ao card atual; hover/focus têm indicação visual sem quebrar o layout.

---

### User Story 3 — Deploy nos ambientes sem passo manual no admin (Priority: P1)

Após merge/pull, limpar cache Drupal basta para o link aparecer em qualquer ambiente.

**Independent Test**: Aplicar artefatos do tema e rodar `drush cr`; abrir `/vagas` e validar o link.

**Acceptance Scenarios**:

1. **Given** código do tema atualizado no destino, **When** `drush cr` é executado, **Then** os títulos já saem como links (sem editar View no admin).
2. **Given** esta feature, **When** o runbook é seguido, **Then** **não** há `drush cim`/`updb` obrigatórios específicos dela.

## Requirements *(obrigatório)*

- **FR-001**: O título de cada card em `/vagas` (`views-view-field--vagas--page-1--nothing`) DEVE ser um `<a>` para `path('entity.node.canonical', {node})`.
- **FR-002**: O link DEVE reutilizar a variável `vaga_url` já usada pelos CTAs (mesma URL).
- **FR-003**: Semântica de heading DEVE ser preservada (`h3` envolvendo ou contendo o link).
- **FR-004**: CSS escopado em `.css-vagas-page` DEVE estilizar o link do título (sem sublinhado default; hover/focus perceptível).
- **FR-005**: Nenhuma alteração estrutural em `config/sync` é necessária; deploy = código tema + `drush cr`.

## Success Criteria *(obrigatório)*

- **SC-001**: Em `/vagas`, 100% dos títulos listados são links clicáveis para o detalhe correto.
- **SC-002**: Clique no título e no CTA do mesmo card levam à mesma URL.
- **SC-003**: Após `drush cr` em ambiente atualizado, a mudança é visível sem intervenção no admin.
- **SC-004**: Nenhuma regressão visual nos cards Home/PE (templates distintos).

## Assumptions

- Detalhe da vaga permanece público; “usuário cadastrado rever detalhes” é o caso de uso principal, não um gate de permissão novo.
- Alias Pathauto existente (`/para-estudantes/[node:title]`) é a URL desejada.
