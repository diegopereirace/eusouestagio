# Feature Specification: Refino visual Cadastro de Empresa

**Feature Directory**: `specs/066-refino-cadastro-empresa`  
**Created**: 2026-11-24  
**Status**: Active  
**Predecessor**: `059-cadastro-candidato-legal`  
**Input do usuário**: Atualizar o estilo de `/cadastro/empresa` (margens, paddings, ícones, cores da marca) e gerar hero 1280×356 `.webp`, espelhando o padrão visual do cadastro de candidato.

## Escopo

### Inclui

- Template de página `page--cadastro--empresa.html.twig` com hero estático (`<picture>`), overlay navy e copy de valor;
- Assets versionados no tema: `hero-desktop.webp` + `.jpg` em **1280×356**; mobile reutiliza o mesmo asset;
- Library CSS escopada `default/cadastro_empresa` (body `page-cadastro-empresa`);
- Markup Form API de `EmpresaRegistrationForm` em cartões `.cadastro-section` com ícones FA, inputs lg, submit laranja `#FD7B1A`;
- Desativar placement CMS `default_views_block__banners_block_3` (`status: false`) + `drush cex`.

### Fora

- Alterar validações, roles, CNPJ, CAPTCHA (063), upload de logo ou fluxo de submit;
- Redesign de `/cadastro/candidato` ou login;
- Seed File entity / `hook_update_N` para banner (política 057 — assets estáticos);
- Mutar token global `--brand-orange`;
- Alterar `core/` ou `vendor/`.

## User Scenarios & Testing

### User Story 1 — Empresa vê hero e formulário em cartões (Priority: P1)

Visitante anônimo abre `/cadastro/empresa` e vê banner estático com título de valor e formulário em seções-cartão alinhadas à marca.

**Acceptance Scenarios**:

1. **Given** viewport desktop, **When** abre `/cadastro/empresa`, **Then** vê hero com eyebrow “Cadastro de empresa”, título “Cadastre sua empresa e contrate estagiários”, overlay escuro e imagem WebP 1280×356.
2. **Given** a mesma página, **When** observa o formulário, **Then** seções Acesso / Empresa / Endereço / Responsável / Finalizar aparecem em cartões brancos com ícones em chip navy.
3. **Given** o botão de envio, **When** inspeciona o estilo, **Then** está full-width com cor `#FD7B1A`.
4. **Given** `/cadastro/candidato`, **When** carrega após o deploy, **Then** o visual permanece inalterado (CSS não vaza).

## Requirements

- **FR-001**: Hero estático no tema; View `banners` `block_3` desativado nessa rota.
- **FR-002**: CSS exclusivo via library + body class; sem entrar em `global-styling`.
- **FR-003**: Tokens: fundo `#f3f6fa`, navy `#003060`, CTA `#fd7b1a`, fallback banner `#07132e`.
- **FR-004**: Deploy destino: `cim` → `cr` (sem `updb`).
