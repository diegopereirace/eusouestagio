# Feature Specification: Rodapé — Abrir Vaga por permissão

**Feature Directory**: `specs/065-rodape-abrir-vaga-permissao`  
**Created**: 2026-11-24  
**Status**: Approved  
**Input do usuário**: O link "Abrir Vaga" no rodapé só deve ser mostrado para usuários logados com permissão para cadastrar uma vaga; para os demais, não mostra.

## Problema

O link **Abrir Vaga** (`/painel/empresa/vagas/nova`) na coluna **Para Empresas** do rodapé aparece para todos (anônimos e autenticados sem permissão de criar vaga), levando a 403 ou expectativa falsa de acesso.

## Solução (visão de produto)

O markup do link **Abrir Vaga** só é emitido no servidor quando o visitante está autenticado **e** possui a permissão de cadastrar vaga (`create vagas content`). Anônimos, candidatos e demais papéis sem essa permissão não recebem o link no DOM (ocultação no servidor, não só CSS). Os demais links do rodapé permanecem inalterados.

## Escopo

### Inclui

- Visibilidade condicional do link **Abrir Vaga** no template do bloco `footer` (`default_footer`)
- Critério: autenticado + permissão de criar conteúdo do tipo vagas
- Cache de renderização do bloco sensível ao usuário/permissões (sem servir markup errado entre usuários)
- Atualização cirúrgica do PRD (§3.6 bloco `footer`) e artefatos SDD desta feature

### Fora

- Alterar CTA de `/para-sua-empresa`, banner PE, topo ou outros “Abrir Vaga”
- Novos fields, menus Drupal, `hook_update_N` ou mudanças em `config/sync`
- Mudança visual/CSS do rodapé além da presença/ausência do item
- Alterações em `core/` ou `vendor/`

## Assumptions

- A permissão canônica para “cadastrar uma vaga” é `create vagas content` (já concedida a `empresa`, `moderador` e implícita a administradores).
- “Cadastre sua Empresa” continua visível para todos.

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Anônimo / sem permissão não vê Abrir Vaga (Priority: P1)

Visitante anônimo ou usuário autenticado sem permissão de criar vaga abre qualquer página e, no rodapé (desktop ou accordion mobile), a coluna Para Empresas mostra apenas **Cadastre sua Empresa**.

**Acceptance Scenarios**:

1. **Given** visitante anônimo, **When** inspeciona o rodapé, **Then** não há link/texto “Abrir Vaga” no HTML.
2. **Given** usuário autenticado sem `create vagas content` (ex.: candidato), **When** inspeciona o rodapé, **Then** “Abrir Vaga” não está no DOM.

### User Story 2 — Quem pode cadastrar vaga vê o link (Priority: P1)

Usuário autenticado com permissão de criar vaga vê **Abrir Vaga** apontando para `/painel/empresa/vagas/nova`.

**Acceptance Scenarios**:

1. **Given** usuário com role `empresa` (ou outro com `create vagas content`), **When** vê o rodapé, **Then** o link “Abrir Vaga” está presente e aponta para `/painel/empresa/vagas/nova`.
2. **Given** administrador (todas as permissões), **When** vê o rodapé, **Then** “Abrir Vaga” está presente.

### User Story 3 — Cache não vaza o link entre usuários (Priority: P1)

Após um usuário com permissão carregar a página, um anônimo (ou o inverso) não vê o markup do outro.

**Acceptance Scenarios**:

1. **Given** sessão com permissão que carregou a home, **When** em seguida um anônimo abre a mesma URL (cache limpo de página privada / contexts corretos), **Then** o anônimo não recebe “Abrir Vaga”.

## Requirements *(mandatory)*

- **FR-001**: O link “Abrir Vaga” no rodapé DEVE ser renderizado somente se o usuário corrente estiver autenticado e tiver a permissão `create vagas content`.
- **FR-002**: Sem essa permissão, o link NÃO DEVE existir no markup (ocultação no servidor).
- **FR-003**: Quando renderizado, o destino DEVE permanecer `/painel/empresa/vagas/nova`.
- **FR-004**: “Cadastre sua Empresa” e demais colunas do rodapé NÃO DEVEM mudar por esta feature.
- **FR-005**: O bloco `default_footer` DEVE variar o cache de renderização conforme permissões do usuário, evitando vazamento entre sessões.

## Success Criteria *(mandatory)*

- **SC-001**: Anônimo e candidato: zero ocorrências de “Abrir Vaga” no HTML do rodapé.
- **SC-002**: Empresa/moderador/admin com permissão: link presente e funcional.
- **SC-003**: Alternar entre usuários com e sem permissão na mesma URL não mostra o link indevido.
