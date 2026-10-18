# Feature Specification: Página pública da Empresa + Sobre a Vaga

**Feature Directory**: `specs/034-empresa-pagina-publica`  
**Created**: 2026-10-18  
**Status**: Draft  
**Predecessor**: `033-vaga-sidebar-acoes`  
**Input do usuário**: Exibir “Sobre a Vaga” no detalhe; remover bloco “Sobre a Empresa” da vaga; informações da empresa em página separada (versão temporária gerenciável no CMS + lista de vagas abertas); link no nome da empresa no header (Anexo 3).

## Escopo

### Inclui

- Garantir seção **Sobre a Vaga** no detalhe full (`field_text_long_formatted`) com seed na vaga “Engenheiro Civil” quando vazio; requisitos seedados se vazios (copy do layout aprovado).
- Remover do detalhe da vaga o bloco Twig **Sobre a Empresa** (legado `field_empresa_u`).
- Transformar o nome da empresa no header em link para a página canônica do node `empresa` publicado.
- Expandir CT `empresa` com campos CMS reutilizados: `field_text_long_formatted` (Sobre a Empresa), `field_cidade`, `field_estados`.
- Pathauto `/empresa/[node:title]`.
- Template full temporário da empresa (logo, nome, local, sobre, lista de vagas abertas via View dedicada `empresa_vagas` com contextual `field_vaga_empresa`).
- Seed EcoConstrutora: sobre + Curitiba/PR se vazios.
- Deploy `custom_configs_update_11052` + `drush cex`; PRD cirúrgico.

### Fora

- Layout Figma rico da página empresa; Match / “Por que combina”; migração em massa `field_empresa_u` → node; remoção do storage legado; redesign sidebar/FAQ/CTA; alteração de `core/`/`vendor/`.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante lê Sobre a Vaga (Priority: P1)

Visitante abre o detalhe da vaga seed e vê a seção “Sobre a Vaga” com o texto descritivo, sem o bloco “Sobre a Empresa”.

**Acceptance Scenarios**:

1. **Given** a vaga seed com `field_text_long_formatted` preenchido, **When** abre o detalhe, **Then** a seção “Sobre a Vaga” aparece após o header.
2. **Given** o detalhe da vaga, **When** a página renderiza, **Then** não há heading “Sobre a Empresa” na coluna principal.

### User Story 2 — Visitante abre a página da empresa (Priority: P1)

Visitante clica no nome da empresa no header e chega à página pública com dados básicos e vagas abertas.

**Acceptance Scenarios**:

1. **Given** vaga com `field_vaga_empresa` publicado, **When** clica no nome no header, **Then** navega para `/empresa/{alias}` da empresa.
2. **Given** a página da empresa seed, **When** carrega, **Then** exibe logo, nome, localização, texto “Sobre a Empresa” e a vaga “Engenheiro Civil” na lista de abertas.
3. **Given** empresa sem vagas publicadas, **When** abre a página, **Then** a lista fica vazia/omitida sem erro.

### User Story 3 — Editor gere dados da empresa (Priority: P1)

Editor edita o node Empresa (sobre, cidade, estado, logo) e o visitante vê os dados atualizados.

**Acceptance Scenarios**:

1. **Given** permissão de editar Empresa, **When** preenche Sobre/cidade/estado e salva, **Then** a página pública reflete os valores.

### User Story 4 — Deploy automatizado (Priority: P1)

Ambiente destino recebe campos, Pathauto, View, seeds e templates sem passo manual no admin.

**Acceptance Scenarios**:

1. **Given** destino sem fields novos em `empresa`, **When** `cim` → `updb` → `cim` → `cr`, **Then** página `/empresa/ecoconstrutora` e detalhe da vaga seed estão corretos.
2. **Given** reexecução de `updb`, **When** roda de novo, **Then** não duplica seeds nem sobrescreve editorial divergente.

## Requirements *(obrigatório)*

- **FR-001**: Detalhe da vaga DEVE exibir “Sobre a Vaga” quando `field_text_long_formatted` tiver valor.
- **FR-002**: Detalhe da vaga NÃO DEVE renderizar a seção “Sobre a Empresa” baseada em `field_empresa_u`.
- **FR-003**: Nome da empresa no header DEVE ser link para o canonical do node `empresa` quando publicado/acessível.
- **FR-004**: CT `empresa` DEVE expor sobre (text long formatted), cidade e estado no form/view CMS.
- **FR-005**: Pathauto DEVE gerar `/empresa/[node:title]` para nodes `empresa`.
- **FR-006**: Página full da empresa DEVE listar vagas publicadas com `field_vaga_empresa` = NID atual.
- **FR-007**: Hook `11052` DEVE ser idempotente (fields, Pathauto, View, seeds).
- **FR-008**: `field_empresa_u` NÃO DEVE ser removido do modelo.

## Success Criteria

- **SC-001**: Vaga seed mostra “Sobre a Vaga” e não mostra “Sobre a Empresa”.
- **SC-002**: Clique em “EcoConstrutora” abre a página da empresa.
- **SC-003**: Página empresa seed lista a vaga Engenheiro Civil.
- **SC-004**: Reexecução do hook não duplica conteúdo seed.
