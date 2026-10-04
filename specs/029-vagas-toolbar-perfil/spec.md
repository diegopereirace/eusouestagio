# Feature Specification: Toolbar + perfil lateral em `/vagas`

**Feature Directory**: `specs/029-vagas-toolbar-perfil`  
**Created**: 2026-10-04  
**Status**: Draft  
**Input**: Cabeçalho com total de vagas + “Ordenar por” (Mais recentes / Mais antigas); card lateral do usuário logado (sem Perfil completo / Minhas atividades); ignorar toggle grid/lista; filtros esquerda Figma continuam fora.

## Escopo

### Inclui

- Toolbar acima da listagem `page_1`: contador “N vagas encontradas” (total filtrado) + dropdown “Ordenar por” com **Mais recentes** / **Mais antigas**.
- Ordenação: `created` DESC ou ASC conforme seleção; **Destaque (`field_vaga_destaque`) permanece sort primário**.
- Card lateral direito para **qualquer usuário autenticado**: avatar, nome, subtítulo por role, link ao painel.
- Layout 2 colunas (lista + aside) quando logado; 1 coluna quando anônimo.
- Spec Kit + PRD §3.6 cirúrgico; CSS/library no escopo `.css-vagas-page`.

### Fora

- Toggle grid/lista; Perfil completo; Minhas atividades; sidebar de filtros esquerda; favoritar; infinite scroll Composer; alterar Hero 027 / cards Home/PE.

## User Stories

### US1 — Visitante vê total e ordena (P1)

1. Given `/vagas`, When carrega, Then vê “N vagas encontradas” com N = total da View filtrada.
2. Given dropdown “Ordenar por”, When escolhe Mais antigas, Then `created` ASC mantendo Destaque primeiro.
3. Given Mais recentes (default), When carrega, Then `created` DESC com Destaque primeiro.

### US2 — Usuário logado vê card de perfil (P1)

1. Given autenticado, When abre `/vagas`, Then vê card à direita (avatar, nome, subtítulo).
2. Given anônimo, When abre `/vagas`, Then não há card lateral.
3. Given o card, When inspeciona, Then não há “Perfil completo” nem “Minhas atividades”.

## Requirements

- **FR-001**: Contador pt-BR no toolbar (`1 vaga encontrada` / `N vagas encontradas`).
- **FR-002**: Ordenar por só Mais recentes / Mais antigas via query `sort_order`.
- **FR-003**: Sort primário Destaque DESC sempre; secundário `created` conforme `sort_order`.
- **FR-004**: Aside só se autenticado; sem Perfil completo / Minhas atividades / toggle grid.
- **FR-005**: Isolamento Home / PE / Hero 027; sem Composer novo.

## Success Criteria

- SC-001: Contador reflete filtros Hero.
- SC-002: Troca Mais antigas/recentes altera ordem de `created` sem dropar Destaque.
- SC-003: Aside só para logados; conteúdo mínimo Figma.
- SC-004: Sem regressão visual dos cards laranja / Hero.

## Assumptions

- `field_nome_curso` ausente no sync → subtítulo candidato = “Candidato” (fallback).
- Zero YAML View se query alter + Twig bastarem; hook `11046` só se necessário.
