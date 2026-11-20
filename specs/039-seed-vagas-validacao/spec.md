# Feature Specification: Seed 15 vagas + empresas (validação / deploy)

**Feature Directory**: `specs/039-seed-vagas-validacao`  
**Feature Branch**: `039-seed-vagas-validacao`  
**Created**: 2026-10-29  
**Status**: Active  
**Predecessor**: `032`–`038` (CT `empresa`, fields de detalhe da vaga, benefícios padrão, FAQ coleção)  
**Input do usuário**: 15 nodes de vagas com todos os campos e dependências (Cadastro de Empresa + perfil público com imagem); remover todas as vagas anteriores; deploy fácil em todos os ambientes; limpar lixo remanescente; cadastro de empresa deve aceitar imagem.

## Escopo

### Inclui

- Upload de **Logo da empresa** no formulário `/cadastro/empresa`, persistido em `user_picture` (reuso de storage existente).
- Exibição do logo no perfil da empresa logada.
- Hook `custom_configs_update_11061` **destrutivo** que:
  1. Apaga todos os nodes `candidatura`;
  2. Apaga todos os nodes `vagas` (incluindo demo Engenheiro Civil e conteúdo editorial);
  3. Apaga nodes `empresa` fora do conjunto seed de 15 UUIDs;
  4. Remove paragraphs/arquivos órfãos do purge;
  5. Cria/atualiza **15** nodes `empresa` (logo + todos os campos do CT);
  6. Cria/atualiza **15** users role `empresa` (todos os campos do cadastro + logo);
  7. Cria **15** nodes `vagas` com todos os campos relevantes preenchidos e vínculos `field_vaga_empresa` + `field_empresa_u`.
- Assets de logo versionados em `modules/custom/custom_configs/assets/seed-vagas-validacao/`.
- Deploy 100% automatizado via `cim` → `updb` → `cim` → `cr`.

### Fora

- Migração preservando vagas editoriais (wipe é intencional).
- Criar field storage novo de imagem no user (`user_picture` basta).
- Auto-criar node `empresa` no submit do cadastro público (seed cobre validação; produto editorial continua criando o perfil público à parte).
- Alterações em `core/` ou `vendor/`.

## User Scenarios & Testing

### User Story 1 — Validar listagem e detalhe com dados reais (P1)

Após deploy, `/vagas` lista 15 vagas publicadas com empresa, logo, regime, cidade e mix de destaque; o detalhe de cada uma mostra sobre, requisitos, benefícios, FAQ, sidebar e cartão da empresa.

**Acceptance Scenarios**:

1. **Given** ambiente após `updb` 11061, **When** visitante abre `/vagas`, **Then** vê exatamente as 15 vagas seed (nenhuma vaga legada).
2. **Given** uma vaga seed, **When** abre o detalhe, **Then** todos os campos da UI (header, benefícios, FAQ, sobre empresa, resumo) estão populados sem placeholders vazios críticos.

### User Story 2 — Conta empresa seed acessível (P1)

Existem 15 users `empresa-seed-01`…`15` com senha documentada, logo e campos de cadastro preenchidos.

### User Story 3 — Cadastro público aceita logo (P1)

Em `/cadastro/empresa`, o visitante pode enviar uma imagem de logo; ela é salva em `user_picture` e aparece no perfil.

### User Story 4 — Limpeza sem lixo (P1)

Após o update, não restam vagas antigas, candidaturas órfãs nem nodes `empresa` fora do conjunto seed; logo legado EcoConstrutora sem usage é removido quando possível.

## Requirements

- **FR-001**: Wipe total de `vagas` + `candidatura` no `11061`.
- **FR-002**: 15 pares user+node empresa + 15 vagas com UUIDs/usernames fixos.
- **FR-003**: Cada vaga popula campos de texto, list_string, taxonomias, benefícios padrão, FAQ seed, empresa node e user.
- **FR-004**: Cadastro empresa com `managed_file` → `user_picture`.
- **FR-005**: Zero passo manual no admin de produção.
- **FR-006**: Quickstart documenta senha seed e aviso de destruição.

## Assumptions

- FAQ coleção seed UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` é reutilizada pelas 15 vagas.
- Benefícios usam os 4 paragraphs padrão (feature 036).
- Hook é destrutivo em **todos** os ambientes onde rodar `updb`.
