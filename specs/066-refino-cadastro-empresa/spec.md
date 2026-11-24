# Feature Specification: Refino visual Cadastro de Empresa

**Feature Directory**: `specs/066-refino-cadastro-empresa`  
**Created**: 2026-11-24  
**Status**: Active  
**Predecessor**: `059-cadastro-candidato-legal`  
**Input do usuário**: Atualizar o estilo de `/cadastro/empresa` (margens, paddings, ícones, cores da marca) e tornar o banner gerenciável via Conteúdo Banners (`/node/39/edit`, título “Banner - Cadastro Empresa”).

## Escopo

### Inclui

- Template de página `page--cadastro--empresa.html.twig` com região `page.banner` (View `banners` display `block_3`);
- Placement CMS `default_views_block__banners_block_3` ativo (`status: true`), visibility `/cadastro/empresa`, filtro de título `Banner - Cadastro Empresa`;
- Library CSS escopada `default/cadastro_empresa` (body `page-cadastro-empresa`) — altura do hero CMS e formulário em cartões;
- Markup Form API de `EmpresaRegistrationForm` em cartões `.cadastro-section` com ícones FA, inputs lg, submit laranja `#FD7B1A`;
- `drush cex` do bloco + View.

### Fora

- Alterar validações, roles, CNPJ, CAPTCHA (063), upload de logo ou fluxo de submit;
- Redesign de `/cadastro/candidato` ou login;
- Seed File entity / `hook_update_N` para bytes do banner (política 057 — editor troca a imagem no CMS);
- Mutar token global `--brand-orange`;
- Alterar `core/` ou `vendor/`.

## User Scenarios & Testing

### User Story 1 — Empresa vê hero e formulário em cartões (Priority: P1)

Visitante anônimo abre `/cadastro/empresa` e vê o banner gerenciável no CMS e o formulário em seções-cartão alinhadas à marca.

**Acceptance Scenarios**:

1. **Given** viewport desktop, **When** abre `/cadastro/empresa`, **Then** vê o banner renderizado a partir do conteúdo “Banner - Cadastro Empresa” (ex.: `/node/39`).
2. **Given** editor em `/node/39/edit`, **When** troca a imagem desktop e salva, **Then** após cache clear a nova arte aparece em `/cadastro/empresa`.
3. **Given** a mesma página, **When** observa o formulário, **Then** seções Acesso / Empresa / Endereço / Responsável / Finalizar aparecem em cartões brancos com ícones em chip navy.
4. **Given** o botão de envio, **When** inspeciona o estilo, **Then** está full-width com cor `#FD7B1A`.
5. **Given** `/cadastro/candidato`, **When** carrega após o deploy, **Then** o visual permanece inalterado (CSS não vaza).

## Requirements

- **FR-001**: Hero via View `banners` `block_3` (placement ativo); arte completa no CMS (FR-5b da 001 — sem overlay HTML de título no tema).
- **FR-002**: CSS exclusivo via library + body class; sem entrar em `global-styling`.
- **FR-003**: Tokens: fundo `#f3f6fa`, navy `#003060`, CTA `#fd7b1a`, fallback banner `#07132e`.
- **FR-004**: Deploy destino: `cim` → `cr` (sem `updb`).
