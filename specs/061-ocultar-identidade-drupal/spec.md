# Feature Specification: Ocultar identificação pública do Drupal

**Feature Directory**: `specs/061-ocultar-identidade-drupal`  
**Created**: 2026-11-05  
**Status**: Approved  
**Input do usuário**: Remover metatags de identificação do Drupal em todo o projeto para reforçar a segurança, sem identificar o CMS publicamente.

## Problema

Respostas HTML e HTTP do site expõem que a plataforma é Drupal (metatag `Generator` e header `X-Generator`), facilitando fingerprinting por scanners e atacantes.

## Solução (visão de produto)

Todas as páginas HTML públicas e autenticadas deixam de emitir a metatag Generator e os headers de identidade CMS (`X-Generator`, `X-Powered-By`), sem alterar SEO Metatag (title/description/canonical) nem headers operacionais `X-Drupal-Cache*`.

## Escopo

### Inclui

- Remoção da metatag `Generator` (`system_meta_generator`) via `hook_page_attachments_alter` em `custom_configs`
- Remoção defensiva de qualquer meta `name=Generator` (ex.: Metatag futuro)
- Remoção dos headers HTTP `X-Generator` e `X-Powered-By` via EventSubscriber em `custom_configs`
- Reforço Apache em `scaffold/htaccess-security-append.txt` + `.htaccess` (`Header always unset`)
- Spec/plan/tasks/quickstart e menção cirúrgica no PRD M7

### Fora

- Remoção de `X-Drupal-Cache*` / `X-Drupal-Dynamic-Cache`
- Alteração de Metatag SEO, tema visual, `core/` ou `vendor/`
- Contrib novo (Remove Generator Tag, SecKit, etc.)
- `hook_update_N` / alteração de `config/sync` (só runtime + htaccess)

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Visitante não vê Generator no HTML (Priority: P1)

Visitante anônimo abre qualquer página pública e o HTML não contém `<meta name="Generator" content="Drupal …">`.

**Acceptance Scenarios**:

1. **Given** home (ou outra página HTML) com cache limpo, **When** o visitante vê o código-fonte, **Then** não existe meta Generator apontando Drupal.
2. **Given** Metatag com title/description/canonical, **When** inspeciona o `<head>`, **Then** essas tags SEO permanecem.

### User Story 2 — Headers HTTP sem identidade CMS (Priority: P1)

Cliente HTTP recebe resposta sem `X-Generator` e sem `X-Powered-By`.

**Acceptance Scenarios**:

1. **Given** `curl -I` na home, **When** lista headers, **Then** não há `X-Generator` nem `X-Powered-By`.
2. **Given** reexecução após `drush cr`, **When** nova request, **Then** a ausência dos headers permanece.

## Requirements *(mandatory)*

- **FR-001**: Remover attachment `system_meta_generator` em todas as páginas HTML.
- **FR-002**: Remover qualquer `html_head` meta com `name=Generator` (case-insensitive).
- **FR-003**: Remover header `X-Generator` na resposta principal.
- **FR-004**: Remover header `X-Powered-By` quando presente.
- **FR-005**: Reforçar unset Apache via scaffold de segurança do projeto.
- **FR-006**: Não editar `core/` / `vendor/`; não remover `X-Drupal-Cache*`.

## Success Criteria *(mandatory)*

- **SC-001**: View Source da home sem meta Generator Drupal.
- **SC-002**: `curl -I` sem `X-Generator` / `X-Powered-By`.
- **SC-003**: Metatags SEO existentes intactas.
