# PRD — Eu Sou Estágio

| Campo | Valor |
|-------|--------|
| **Produto** | Eu Sou Estágio |
| **Plataforma** | Drupal 11 (core-recommended ^11.4) |
| **Documento** | Product Requirements Document (executável) |
| **Versão** | 1.0 |
| **Data** | 2026-09-06 |
| **Status** | Baseline a partir do código + regras do projeto |
| **Fontes** | Código em `modules/custom/`, `themes/custom/default/`, `composer.json`, `.cursor/rules/estagio-*.mdc` |
| **Lacuna** | `rascunho_requisitos.md` **não encontrado** no repositório; este PRD consolida o produto as-built e define o escopo canônico |

---

## 0. Contexto e objetivo do produto

**Eu Sou Estágio** é um portal Drupal 11 de intermediação de vagas de estágio entre **estudantes (candidatos)**, **empresas** e **moderadores**. O site é **server-rendered** (tema `default` sobre Bootstrap Barrio), com painéis autenticados, cadastros públicos protegidos por reCAPTCHA, listagem/filtro de vagas e fluxo de candidatura com acompanhamento de status.

**Objetivos de produto e engenharia (não negociáveis):**

1. Integridade da plataforma (sem alterações em `core/` ou `vendor/`).
2. Performance de carregamento (cache tags, Views e Entity API).
3. Segurança de dados (roles nas rotas, PostgreSQL 16, validação em boundaries).
4. Clean URLs (Pathauto + aliases; rotas custom com paths legíveis).
5. Spec Driven Development (SDD): planejar e confirmar antes de gerar código.

**Fora de escopo (v1 canônica):** frontend headless (React/Next), app mobile nativo, marketplace de pagamentos, SSO social (salvo se especificado em milestone futuro).

---

## 1. Visão Geral e Casos de Uso

### 1.1 Visão em uma frase

Permitir que estudantes descubram e se candidatem a vagas de estágio, que empresas publiquem vagas e acompanhem candidaturas, e que moderadores triagem e atualizem o status das candidaturas — tudo em um CMS Drupal 11 com painéis por persona.

### 1.2 Personas e jornadas principais

| ID | Caso de uso | Ator | Resultado esperado |
|----|-------------|------|--------------------|
| UC-01 | Cadastro de estudante | Anônimo → Candidato | Conta com role `candidato`, perfil inicial, e-mail de boas-vindas; moderadores notificados |
| UC-02 | Cadastro de empresa | Anônimo → Empresa | Conta com role `empresa`, dados cadastrais (CNPJ etc.); moderadores notificados |
| UC-03 | Login e redirecionamento ao painel | Autenticado | `/painel` redireciona conforme role (estudante / empresa / moderador / administrator → candidaturas) |
| UC-04 | Completar / editar perfil estudante | Candidato | Dados pessoais, endereço (CEP), formação, paragraphs de instituição/cursos/experiências |
| UC-05 | Completar / editar perfil empresa | Empresa | Razão social, fantasia, CNPJ, endereço, responsável |
| UC-06 | Descobrir vagas | Público / Candidato | View `vagas` `page_1` em `/vagas` com filtros (curso, estado, cidade, escolaridade); landing `/para-estudantes` mostra destaque `block_3` (3 cards) |
| UC-07 | Ver detalhe da vaga | Público / Candidato | Node `vagas`; candidato logado vê ações candidatar / salvar |
| UC-08 | Salvar / remover vaga | Candidato | Toggle em tabela custom `vagas_salvas`; listagem em `/painel/estudante/vagas-salvas` |
| UC-09 | Candidatar-se a vaga | Candidato | Cria node `candidatura` (status `em_triagem`); confirmação ao candidato; listagem em vagas aplicadas |
| UC-10 | Acompanhar candidaturas (estudante) | Candidato | `/painel/estudante/vagas-aplicadas` |
| UC-11 | Publicar / gerir vagas | Empresa / Admin | Node `vagas` vinculado a `field_empresa_u`; notificação a moderadores |
| UC-12 | Ver candidaturas recebidas | Empresa | `/painel/empresa/candidaturas` (vagas da própria empresa) |
| UC-13 | Triagem de candidaturas | Moderador / Admin | `/painel/moderador/candidaturas` + POST de status; link no menu `moderador` (sidebar) e em Admin → Conteúdo (`custom_panel.admin_candidaturas`); bloco sidebar também para `administrator` (`11053`) |
| UC-14 | Alterar senha | Candidato / Empresa | Rotas dedicadas no painel |
| UC-15 | Lookup de CEP | Formulários | `GET /api/cep/{cep}` (proxy ViaCEP, flood-limited) |
| UC-16 | Páginas institucionais | Público | Nodes/páginas (`quem-somos`, `para-empresas`) e blocos/banners |

### 1.3 Fluxo canônico de candidatura (produto)

```
Candidato autenticado → POST /vaga/apply/{nid}
  → cria node type candidatura
      field_candidatura_candidato → user (candidato)
      field_candidatura_vaga → node (vagas)
      field_candidatura_status → em_triagem (default)
  → e-mail de confirmação ao candidato (quando configurado)
  → moderador altera status no painel
  → empresa visualiza candidaturas das suas vagas
```

**Débito técnico a resolver (escopo de engenharia, não produto duplicado):** existe caminho legado via `field_candidatos_u` + `CandidaturasManager` / `CandidaturaController` **não roteado**. O PRD define o **node `candidatura`** como fonte da verdade. Milestone de higiene deve consolidar ou remover o legado.

### 1.4 Critérios de aceite transversais (jornadas)

- Rotas de painel respeitam `_role` / login; usuário sem role correta recebe 403 (`/acesso-negado`).
- Cadastros anônimos exigem reCAPTCHA (`recaptcha/reCAPTCHA`).
- Candidatura duplicada na mesma vaga pelo mesmo usuário é rejeitada.
- URLs públicas usam aliases limpos; rotas custom usam português legível (`/painel/...`, `/cadastro/...`).

---

## 2. Atores e Controle de Acesso (Roles e Permissões)

### 2.1 Roles (machine names)

| Role | Label de produto | Quem |
|------|------------------|------|
| `anonymous` | Visitante | Não autenticado |
| `authenticated` | Autenticado | Base Drupal |
| `candidato` | Estudante | Aluno em busca de estágio |
| `empresa` | Empresa | Contratante / anunciante de vagas |
| `moderador` | Moderador | Operação / triagem |
| `administrator` | Administrador | Superusuário Drupal |

Atribuição no cadastro: forms de registro adicionam `candidato` ou `empresa` se a role existir.

### 2.2 Matriz de acesso (rotas custom — canônica)

| Capacidade | Anônimo | Candidato | Empresa | Moderador | Admin |
|------------|---------|-----------|---------|-----------|-------|
| Cadastro candidato `/cadastro/candidato` | ✓ | — | — | — | — |
| Cadastro empresa `/cadastro/empresa` | ✓ | — | — | — | — |
| Listar / ver vagas públicas | ✓ | ✓ | ✓ | ✓ | ✓ |
| `/painel` (redirect) | — | ✓ | ✓ | ✓ | ✓ |
| Editar perfil estudante | — | ✓ | — | — | — |
| Vagas salvas / aplicadas / apply | — | ✓ | — | — | — |
| Editar perfil empresa | — | — | ✓ | — | — |
| Candidaturas da empresa | — | — | ✓ | — | — |
| Triagem + status candidaturas | — | — | — | ✓ | ✓ |
| CEP API | ✓* | ✓* | ✓* | ✓* | ✓* |
| Criar/editar nodes CMS + Views admin | — | — | conforme permissões core | conforme | ✓ |

\*Público com rate-limit (flood).

### 2.3 Modelo de permissões

- **Atual:** acesso principalmente por `_role` / `_user_is_logged_in` nas rotas; **não há** `*.permissions.yml` custom.
- **Diretriz PRD:** manter roles como eixo de produto; se surgir lógica reutilizável (ex.: “gerir candidaturas da própria empresa”), extrair permissão custom + access checker — sem abrir endpoints genéricos.

### 2.4 Privacidade e isolamento de dados

- Empresa só vê candidaturas de vagas onde `field_empresa_u` = usuário atual.
- Candidato só vê/edita o próprio perfil e próprias candidaturas / vagas salvas.
- Moderador vê fila operacional completa de candidaturas (e recebe e-mails de eventos).
- Dados sensíveis (CPF, RG, endereço): apenas no perfil autenticado; não expor em Views públicas.

---

## 3. Arquitetura de Dados

### 3.1 Content types (nodes)

| Bundle | Machine name | Função |
|--------|--------------|--------|
| Vaga | `vagas` | Anúncio de estágio |
| Candidatura | `candidatura` | Relação candidato ↔ vaga + status |
| FAQ | `faq` | Coleção (`title` = nome); itens via `field_faq_itens` → paragraph `faq_item_p` (`field_pergunta` + `field_resposta`); `field_resposta` no node é legado oculto; **FAQ padrão único** das vagas = coleção seed UUID `c3d4e5f6-a7b8-4901-b234-56789abcdef0` (feature **051**; não associável por vaga) |
| Banners | `banners` | Banners unificados (home + internas) |
| Quem somos | `quem_somos` | Institucional |
| Contato | `contato` | Página `/contato` (webform + painel) |
| Depoimento | `depoimento` | Prova social B2B no carrossel de `/para-empresas` (título = autor; `field_text_simple` cargo; `field_text_simple_long` texto; `field_imagem` foto) |
| Empresa | `empresa` | Perfil público da organização (título = nome; `field_imagem` = logo; `field_text_long_formatted` = Sobre; `field_text_simple` = Resumo curto do cartão no detalhe da vaga, opcional ≤255; `field_cidade` / `field_estados` = local). Página canônica Pathauto `/empresa/[node:title]`; o resumo curto **não** entra no Twig da página pública. **Distinto** da role user `empresa`. Seed EcoConstrutora UUID `f6a7b8c9-d0e1-4234-e567-89abcdef0123` |
| Página básica | `page` | Páginas estáticas; shell de `/para-empresas` (só blocos) |

#### 3.1.0b `/para-empresas` (landing B2B — Página básica)

Rota pública `/para-empresas`. Node bundle **`page`** (UUID seed `c9d0e1f2-a3b4-4567-89ab-cdef01234567`); sem fields exclusivos — o público vê: banner View `block_para_empresas` + blocos em `content_full` (**Nossos Diferenciais** e **Nossa Metodologia** reusam as instâncias da home; O Que Fazemos/Benefícios/**Depoimentos**/CTA são exclusivos dessa URL — placements da home intocados). Ver §3.6. Tipo legado `para_empresas` + storage `field_itens_p` removidos por `custom_configs_update_11028`. Deploy desta migração: **`updb` → `cim` → `cr`** (updb antes do cim que remove o tipo do sync). Composição 019: `11024`–`11027`; Depoimentos (020): `11032`. **Refino Figma (052)**: paridade de carrossel PE↔home (JS `banner_carousel_js`); CTAs do banner em breakpoint **768px** com larguras iguais; ND texto `#4E4E4E` + mobile sem decos; bullets de depoimentos ativos em `--brand-orange`; CTA final condicional por role `empresa` (Twig `block--default-ctav1paraempresas.html.twig`); legado “Falar com Especialista” → “Abrir Vaga” via `custom_configs_update_11076`. Deploy refino: `cim` → `updb` → `cim` → `cr`.
#### 3.1.0 `quem_somos` (página institucional / Sobre nós)

Rota pública `/quem-somos`. Layout da **1ª seção** (“Sobre nós”): título + único corpo WYSIWYG contornando a imagem à direita (`float-md-end` em viewport ≥768px), com anel verde e círculo laranja no wrapper da foto (CSS); foto via Image Style `quem_somos_sobre_nos` (`loading="lazy"`). Banner da página: ver §3.6 (`block_quem_somos`). Faixa Missão | Visão: fields no próprio Node (`group_missao_visao`); fundo via Image Style `quem_somos_missao_visao` + `background-attachment: fixed` (≥768px) / `scroll` (mobile); placement do bloco `missao_visao` desabilitado nessa rota (ver §3.6). Grid de diferenciais: bloco `diferenciais_quem_somos` em `content_full` (ver §3.6). Faixa **Impact in Numbers** (após Diferenciais): dados no Node (`field_numeros_lista` → `numero_destaque_p`); render via bloco `default_impactnumbersquemsomos` em `content_full` weight `11`, só `/quem-somos`; CSS `.section-impact-numbers` / library `impact_numbers`. Card **CTA v1** (após Impact Numbers): bloco `cta_v1` em `content_full` weight `12`, só `/quem-somos`; botão secundário outline `1px solid #0F172A` (ver §3.6).

| Campo | Tipo / uso |
|-------|------------|
| `field_titulo` | String — heading da 1ª seção |
| `field_text_long_formatted` | Text long (HTML) — corpo único que envolve a imagem |
| `field_imagem` | Image — foto ~432×269 (@2x via style 864×538); float à direita em `md+` |
| `field_imagem_desktop` | Image — fundo Missão/Visão (cover; style largura 1920) |
| `field_text_simple_long` | String long — corpo “Nossa Missão” (título H2 fixo no Twig) |
| `field_text_simple_long_2` | String long — corpo “Nossa Visão” (título H2 fixo no Twig) |
| `field_numeros_lista` | ERR → `numero_destaque_p`, cardinality **4** — Impact in Numbers (`group_numeros`) |

Paragraph `numero_destaque_p` (“Número Destaque”): `field_text_simple` (destaque, ex. `20k+`) + `field_text_simple_long` (subtexto uppercase, ex. `ESTUDANTES ATIVOS`). Tokens da faixa: fundo `#0F172A`, destaque `#FD7B1A` Poppins 700, subtexto branco opacity 0.8.

Deploy (layout v2 `/quem-somos` + refino **047**): `drush cex` na origem → destino `cim` → `updb` → `cim` → `cr`. Hooks: `11013`–`11019` (layout v2) + **`custom_configs_update_11069`** (ensure Image Styles `quem_somos_sobre_nos` / `quem_somos_missao_visao` / `diferencial_simples_icon` + displays; sync ícones provisórios `icon-N.png` → `public://`; fill-if-empty textos) + **`11070`** (re-sync idempotente de ícones + fill do corpo Sobre nós se vazio). Assets: banner `assets/banner-quem-somos/`, fundo Missão/Visão `assets/missao-visao/`, ícones `assets/diferenciais-quem-somos/icon-1.png`…`icon-8.png` (não versionar `sites/default/files`). Tema: libraries `layout_sobre_nos` + `quem_somos_missao_visao` + `impact_numbers` + `diferenciais_quem_somos` + `cta_v1`; Twig `node--quem-somos.html.twig` + `paragraph--numero-destaque-p.html.twig` + `paragraph--diferencial-simples-p.html.twig` + `block--custom-configs-impact-numbers-quem-somos.html.twig` + `block--block-diferenciais-quem-somos.html.twig` + `block--block-cta-v1.html.twig`.

#### 3.1.1 `vagas` (campos usados no tema/código)

| Campo | Tipo / uso |
|-------|------------|
| `field_empresa_u` | Entity reference → `user` (role `empresa`) — legado painel/candidaturas; **oculto** no form display da vaga (feature **050**); storage intacto; **não** fonte do header nem do cartão “Sobre a Empresa” (037) |
| `field_vaga_empresa` | Entity reference card. 1 → node `empresa` — único vínculo editorial de empresa no form; logo/nome (link)/badge no header do detalhe |
| `field_vaga_carga_horaria` | String — texto da pill de carga (ex. “30h semanais”); distinto de `field_horarios` (turno) |
| `field_candidatos_u` | Entity reference multi → `user` (legado; ver §1.3) |
| `field_cursos_t` | Taxonomy (cursos) |
| `field_regime_t` | Taxonomy (regime) |
| `field_tecnologias_t` | Taxonomy (fallback/exibição) |
| `field_cidade`, `field_estados`, `field_bairro` | Localização |
| `field_text_simple` | Salário / bolsa — linha “Bolsa Auxílio” do resumo da sidebar (033) |
| `field_text_simple_2` | Nome empresa em cards (quando aplicável) |
| `field_text_long_formatted` | Sobre a Vaga (detalhe full) |
| `field_text_simple_multiple` | Requisitos em lista (legado auxiliar; preferir `field_vaga_requisitos`) |
| `field_text_simple_multiple_2` | Benefícios em texto livre (legado); **oculto** form/view (050 / `11072`); migração → `field_vaga_beneficios_t` fill-if-empty |
| `field_auxilio_transporte` | Auxílio |
| `field_horarios` | Horários / turno (Manhã/Tarde/Noite) — **fora** das pills do header 032 e **fora** do resumo da sidebar (033) |
| `field_vaga_periodo` | String — período de duração no resumo da sidebar (ex. “6 meses +”) |
| `field_vaga_quantidade` | Integer — quantidade de vagas no resumo (ex. 2, exibido “02”) |
| `field_vaga_destaque` | Boolean “Destaque” (default off); badge/borda/prioridade na listagem `/vagas`; checkbox no form `default` |
| `field_vaga_requisitos` | Text long formatted — requisitos com checks CSS no detalhe full |
| `field_vaga_beneficios_t` | ER unlimited → taxonomy `beneficios` (**único** seletor Benefícios; checkboxes; **obrigatório** ≥1); fonte canônica no detalhe (feature **050** / `11071`–`11072`) |
| `field_vaga_beneficios` | ERR → paragraph `beneficio_vaga_p` (legado **036**); **oculto** form/view (050); storage preservado para rollback; migração título→termo fill-if-empty via `11071`/`11072` |
| `field_vaga_etapas_processo` | String multi — schema mantido; **fora** da UI full (031) |
| `field_vaga_faq` | ER card. **1** → node `faq` (legado); **oculto** no form (feature **051**); storage intacto; **não** fonte do Accordion público — fonte = FAQ padrão UUID canônico via preprocess |

Detalhe full (feature **031** refino de **030** + header **032** + sidebar **033** + página empresa **034** + cartão **037** + FAQ Accordion **038** + FAQ padrão **051**): Twig `node--vagas--full.html.twig` + include `vaga-faq-accordion.html.twig` + library `vagas_detalhe` / CSS `.vaga-detalhe` (grid 8/4; header via CT `empresa` / `field_vaga_empresa` — nome + `field_vaga_carga_horaria`; coluna principal: Sobre a Vaga / Requisitos / Benefícios / cartão “Sobre a Empresa” (logo + título + `field_text_simple` da empresa + “Ver Empresa”) / “Dúvidas Frequentes” em cards Accordion isolados (sem `data-bs-parent`; fonte = FAQ padrão UUID canônico via `custom_configs_load_faq_padrao()` / preprocess — **não** lê `field_vaga_faq`; omitido se FAQ padrão inválido) — cartão empresa só com empresa publicada e `access('view')`; sidebar ações + resumo — Candidatar-se via rota `custom_panel.vagas_candidatar` POST `/vaga/apply/{node}`, Salvar via `custom_panel.vagas_salvas_toggle`; resumo lê `field_vaga_periodo`, `field_text_simple`, `field_regime_t`, `field_vaga_quantidade`; sem Match / Processo / Seu Perfil / CTA inline); página empresa: Twig `node--empresa--full.html.twig` + library `empresa_pagina` + View `empresa_vagas` `block_empresa` (sem imprimir o resumo curto); FAQ coleção seed UUID `c3d4e5f6-…def0` (2× `faq_item_p`) = padrão único **051**; form vaga sem seletor FAQ (`11074`); CTA final via bloco `cta_v1` placement `default_ctav1vagas`; vaga seed UUID `a7b8c9d0-e1f2-4345-f678-9abcdef01234` (“Engenheiro Civil”, Curitiba/PR, bolsa “R$ 1.500/mês”, período “6 meses +”, quantidade 2, Sobre a Vaga seedado; vínculo legado `field_vaga_faq` via `11059`/`11060` permanece no banco mas é ignorado no público); deploy `custom_configs_update_11048`–`11052` + `11057` (resumo curto) + `11059`/`11060` (vínculo FAQ legado) + **`11074`** (FAQ padrão + hide form) + `drush cex` (destino `cim` → `updb` → `cim` → `cr`). Listagem `/vagas` e cards laranja Home/PE **intactos**. `field_empresa_u` permanece (sem migração em massa).

#### 3.1.1b `banners`

Tipo único de banners do site (absorve o legado `banner_internas` e o antigo block bundle `banner`). Ordenação das listagens: `field_peso` ASC (menor primeiro), desempate `created` DESC.

| Campo | Tipo / uso |
|-------|------------|
| `field_imagem_desktop` | Image (obrigatório; alt obrigatório) |
| `field_imagem_mobile` | Image (opcional; fallback para desktop) |
| `field_local_exibicao` | list_string obrigatório: `home` \| `internas` \| `quem_somos` \| `para_empresas` \| `para_estudantes` |
| `field_peso` | Integer obrigatório (default 0); quanto menor, mais cedo aparece |

Legado removido: `node.type.banner_internas` e block type `banner` (Release 2 / 2b).

#### 3.1.2 `candidatura`

| Campo | Tipo | Valores / target |
|-------|------|------------------|
| `field_candidatura_candidato` | ER → user | role `candidato` |
| `field_candidatura_vaga` | ER → node | bundle `vagas` |
| `field_candidatura_status` | list_string | ver abaixo |

**Status (canônicos):**

| Valor | Label |
|-------|-------|
| `em_triagem` | Em Triagem (default) |
| `aprovado` | Aprovado |
| `reprovado` | Reprovado |
| `lista_espera` | Lista de Espera |
| `entrevista_agendada` | Entrevista Agendada |

### 3.2 Users e campos de perfil

#### Candidato (amostra canônica — forms `CandidatoRegistrationForm` / `CandidatoEditForm`)

Identidade: `field_nome_completo`, `field_cpf`, `field_rg`, `field_orgao_emissor`, `field_data_nascimento`, `field_sexo`, `field_identidade_genero`, `field_estado_civil`, `field_quantidade_filhos`, `field_nacionalidade`, `field_estado_natal`, `field_nome_mae`, `field_nome_pai`.

Contato/endereço: `field_cep`, `field_endereco`, `field_numero`, `field_complemento`, `field_bairro`, `field_cidade`, `field_estado`, `field_telefone`, `field_instagram`, `field_linkedin`.

Formação: `field_escolaridade`, `field_periodo_letivo`, `field_nome_curso`, `field_tipo_curso`, `field_horario_curso`, `field_duracao_curso`, `field_previsao_formatura`, `field_disponibilidade_estagio`, `field_numero_matricula`, `field_possui_deficiencia`, `field_numero_cid`, `field_termo`.

Paragraphs: `field_instituicao_ensino`, `field_cursos_extracurriculares`, `field_experiencias_profissionais`.

Exportados em config install do submodule: `field_rg`, `field_responsavel_*` (também usados em empresa).

#### Empresa

`field_cnpj`, `field_razao_social`, `field_nome_fantasia`, `field_inscricao_municipal`, `field_sobre_empresa`, endereço compartilhado, `field_responsavel_nome|telefone|email`, `field_termo`. `field_cpf_empresa` tratado como nulo no save (legado).

### 3.3 Paragraph types

| Type | Parent | Campos filhos |
|------|--------|---------------|
| `instituicao_ensino` | `field_instituicao_ensino` | nome, endereço, número, CEP, bairro, cidade, estado |
| `curso_extracurricular` | `field_cursos_extracurriculares` | tipo habilidade, habilidade, nível, carga horária |
| `experiencia_profissional` | `field_experiencias_profissionais` | empresa, cargo, início/término, regime, atividades |

Paragraphs de layout em blocos/páginas (`field_icon_title_text_p`, `field_itens_p`) existem no tema; machine names de tipos de layout devem ser exportados em `config/sync` quando a CM for formalizada.

### 3.4 Taxonomies

| Vocabulário | Machine name (`vid`) | Campos / uso |
|-------------|----------------------|--------------|
| Curso | `curso` | `field_cursos_t`; filtro exposto `cursos` na View `vagas`; pills TI/Administração/Design/Marketing; autocomplete `/api/cursos/autocomplete` |
| Regime | `regime` | `field_regime_t`; filtro exposto `regime` na View `vagas` (listagem/sidebar); **sem** pill Remoto no hero da Home (feature **040**) |
| Tecnologias | `tecnologias` | `field_tecnologias_t` (fallback/exibição) |
| Benefícios | `beneficios` | Catálogo de 7 termos com ícone WebP (`field_image`); `field_vaga_beneficios_t` na vaga (feature **050** / seed `11071`; assets `modules/custom/custom_configs/assets/beneficios/beneficios-0{1..7}.webp`) |
| Tags | `tags` | Genérico |

### 3.5 Tabelas custom

| Tabela | Módulo | Uso |
|--------|--------|-----|
| `vagas_salvas` | `custom_panel` | `uid`, `nid`, `created` — favoritos do estudante |

### 3.6 Block content / Views (produto)

- View `banners`: display `block_home` (carrossel home, `field_local_exibicao=home`) + `block_1`/`block_2`/`block_3` (internas) + `block_quem_somos` (`field_local_exibicao=quem_somos`, região `banner`, pages só `/quem-somos`) + `block_para_empresas` (`field_local_exibicao=para_empresas`, região `banner`, pages só `/para-empresas`) + `block_para_estudantes` (`field_local_exibicao=para_estudantes`, região `banner`, pages só `/para-estudantes`; placement legado `block_1` **desativado** — `status: false`, pages `/para-estudantes` preservadas).
- View `vagas` `page_1` (`/vagas`): listagem vertical de cards brancos (`.item-vaga--lista` / library `vagas_lista_vertical`; **não** `.item-vaga--destaque`); **5/página** + pager `full` + `use_ajax`; ordenação primária `field_vaga_destaque` DESC + secundária `created` DESC/ASC via toolbar “Ordenar por” (`?sort_order=`; feature **029**); toolbar com total “N vagas encontradas”; aside de perfil (avatar/nome/subtítulo) para **qualquer autenticado** (sem Perfil completo / Minhas atividades); layout `.vagas-lista-layout` (lista + aside); filtros expostos `title`, `nid`, `cursos`, `estado`, `cidade`, `escolaridade`, `regime` (Hero Search 027); seed/config `11040`–`11042` + `11044` + **`11045`** (Destaque + lista) + UI **029** (tema + `custom_configs_views_query_alter`); Home `block_1` e landing `block_3` mantêm cards laranja “Vagas de Destaque” + “Ver todas as vagas” → `/vagas`.
- Landing `/para-estudantes`: Node `page` shell (UUID `d0e1f2a3-b4c5-4678-9abc-def012345678`, hook `11041`) + blocos path-scoped; **não** é mais path da View. Composição pública (features **053**/**055**/**056** / hooks `11077`/`11082`/`11085`): banner `block_para_estudantes` → Hero Search (região `highlighted`, placement `default_custom_banners_vagas_hero_search_estudantes`) → `content_full`: **oportunidades** (w0, grade 4 cards) → **benefícios** (w1, split Figma) → jornada (w2) → View `vagas` `block_3` (w3, 3 cards + “Ver todas as vagas” → `/vagas`) → CTA final `cta_v1` (w4, faixa escura colada no footer — vão 0). Placement `default_perfildestaqueestudante` **fora da superfície pública** (`status: false`; entidade permanece). Título Drupal oculto via `default_page_title` negate.
- Bloco plugin `custom_banners_hero_search` (região `highlighted`, somente `<front>`): form GET (`cursos`/`cidade`/`estado`, todos opcionais) → `/vagas`; autocomplete de cursos + **cidades** com **cruzamento inteligente** (feature **041**: curso restringe cidades e vice-versa; campo sem opções é desabilitado com hint); pills só de curso (TI/Administração/Design/Marketing — **sem** Remoto); CSS `.hero-search` / library `hero_search` (features **040** / **041**).
- Bloco plugin `custom_banners_vagas_hero_search` (região `highlighted`, weight `-50`): Hero Search com título/subtítulo fixos no Twig, form GET (`title`/`cidade`/`cursos`) + pills de curso + link **“Ver todas”** → `/vagas`; autocomplete nos 3 inputs (vanilla; Input 1 → `/api/vagas/autocomplete`; Inputs 2/3 → rotas 040/041 **sem** cruzamento); filtro `title` casa título **OU** empresa via `custom_panel_views_query_alter` (`field_vaga_empresa`); label/placeholder localização **“Cidade ou Remoto”**; CSS `.vagas-hero-search` / library `vagas_hero_search` v1.2.2; **dois** placements do mesmo plugin — `default_custom_banners_vagas_hero_search` (UUID `a1b2c3d4-e5f6-4789-a012-bcdef0123456`, pages `/vagas`, ensure `11044`) e `default_custom_banners_vagas_hero_search_estudantes` (UUID `e7f8a9b0-c1d2-4345-b678-0530abcdef01`, pages `/para-estudantes`, ensure `11077` / feature **053**; Twig da página renderiza `highlighted` entre banner e `content_full` nessa rota); **distinto** do hero da home (`.hero-search`). Feature **048** + **053**.
- View `banners` display `block_home` (região `banner`, `<front>`): carrossel `#banner-carousel-home-slides` **full-bleed** na região; texto do hero (título/CTA/contatos) **contido no banner** via overlay — não ao lado da imagem. Detalhe operacional: regra `.cursor/rules/estagio-banner-home.mdc` (sob demanda).
- View `banners` display `block_quem_somos` (região `banner`, `/quem-somos`): caixa branca bipartida (copy/CTAs fixos no Twig + imagem editorial); coluna mídia `d-none d-md-block` + `<picture>` com source só `min-width: 768px` e placeholder data-URI no `<img>` (zero request de arte em mobile; LCP `eager`/`fetchpriority="high"` no desktop — feature **047**); seed idempotente `custom_configs_update_11012` (UUID `e9f0a1b2-c3d4-4e5f-8690-1234567890ab`; asset `modules/custom/custom_configs/assets/banner-quem-somos/quem-somos-img.png`).
- View `banners` display `block_para_empresas` (região `banner`, `/para-empresas`): carrossel `#banner-carousel-para-empresas` **full-bleed** (100% da largura) com **imagem completa** por slide (aspect `1200/433`) + setas laterais (multi) + 2 CTAs em overlay no Twig; library `banner_para_empresas` **depende** de `banner_carousel_js` (init Bootstrap + reduced-motion; **sem** CSS da home); classe `banner-carousel` + `data-bs-pause="hover"`; CTAs mobile `<768px` larguras iguais (feature **052**); seletores de região `body.path-para-empresas` (pós-`11028`); seed idempotente `custom_configs_update_11024` (UUIDs `a1b2c3d4-…` / `b2c3d4e5-…`; assets `modules/custom/custom_configs/assets/banner-para-empresas/slide-1.png` / `slide-2.png`). Placement legado `default_ctoparaempresas` (tipo `cto`) **desativado** nessa rota.
- View `banners` display `block_para_estudantes` (região `banner`, `/para-estudantes`): carrossel `#banner-carousel-para-estudantes` **full-bleed** (mesmo formato de `block_para_empresas`: imagem completa por slide aspect `1200/433` + setas/indicators multi + 2 CTAs em overlay no Twig); library `banner_para_estudantes` **depende** de `banner_carousel_js` + `banner_carousel_cinematic` (**sem** CSS da home); classes `banner-carousel` + `banner-carousel--cinematic` + `data-bs-pause="hover"` / intervalo `7000`; CTAs mobile `<768px` larguras iguais (feature **053**, paridade PE); pager `none`; seed idempotente `custom_configs_update_11033` (UUID `c3d4e5f6-a7b8-4901-c234-567890abcdef`; asset `modules/custom/custom_configs/assets/banner-para-estudantes/hero.png`); CTAs `/para-estudantes#main-content` + `/cadastro/candidato`; legado `default_views_block__banners_block_1` forçado `status: false`.
- Bloco `oportunidades_estudantes` (`block_content`): título (`field_text_simple`), subtítulo (`field_text_simple_long`), cards (`field_itens_lista` → paragraph `card_icon_text_p`); placement `default_oportunidadesestudantes` em `content_full`, weight `0`, somente `/para-estudantes` (após Hero Search; **antes** de Benefícios); CSS `.block-oportunidades-estudantes` / library `oportunidades_estudantes`; Twig `block--block-oportunidades-estudantes.html.twig` + `paragraph--card-icon-text-p--oportunidades-estudantes.html.twig` (suggestion por bloco pai); seed idempotente `custom_configs_update_11085` (UUID `c1d2e3f4-a5b6-4789-c012-3456789abcde`; título “Encontre oportunidades que combinam com você.”; 4 cards — Vagas alinhadas / Modelos de trabalho / Vários segmentos / Filtros inteligentes; assets `modules/custom/custom_configs/assets/oportunidades-estudantes/icon-1.png`…`icon-4.png`). **Separado** de `beneficios_estudantes` para as duas seções coexistirem sem flip-flop de copy/layout.
- Bloco `beneficios_estudantes` (`block_content`): badge (`field_text_simple_2`), título (`field_text_simple`, Twig bicolor canônico `#07132E`/`#005B9F`), subtítulo (`field_text_simple_long`), itens (`field_itens_lista` ilimitado → paragraph `card_icon_text_p` com ícone `field_image` + título `field_text_simple` + texto `field_text_simple_long` — cards brancos), CTA (`field_link`), foto **central** da colagem (`field_image` no bloco; satélites só via assets seed, sem CMS foto-a-foto); placement `default_beneficiosestudantes` em `content_full`, weight `1`, somente `/para-estudantes` (após Oportunidades w0; View `vagas` inalterada → listagem em `/vagas`; destaque `block_3` na landing); CSS `.block-beneficios-estudantes` / library `beneficios_estudantes` (fundo `#eef5ff`; grade 2×3 cards + CTA laranja com seta + órbita circular à direita ≥992px; colagem abaixo em 576–991; omitida &lt;576 — feature **055**; predecessores **022**/**053**/**054**); Twig `block--block-beneficios-estudantes.html.twig` + `paragraph--card-icon-text-p.html.twig`; seed idempotente `custom_configs_update_11082` (UUID `a8b9c0d1-e2f3-4456-a789-0bcdef123456`; badge “Benefícios para Estudantes”; título “Muito mais do que encontrar um estágio.”; 6 itens canônicos; CTA “Cadastre-se gratuitamente” → `/cadastro/candidato`; assets `icon-1.png`…`icon-6.png` + `collage-center.png` / `collage-sat-1.png`…`collage-sat-5.png` + fallback `collage.png` em `modules/custom/custom_configs/assets/beneficios-estudantes/`; recreate se UUID/placement ausentes); **distinto** do PE “Benefícios para Empresas” (`diferenciais_quem_somos` / `diferencial_simples_p`) e do legado `icone_titulo_descricao`.
- Bloco `jornada_estudante` (`block_content`): título (`field_text_simple`), passos (`field_itens_lista` → paragraph `passo_jornada_p` com título `field_text_simple` + descrição `field_text_simple_long`; max 4 no form/validação — storage `field_itens_lista` permanece `-1`); badge numerada e destaque do último passo só no Twig (`loop.index` / `loop.last`); placement `default_jornadaestudante` em `content_full`, weight `2`, somente `/para-estudantes` (após Oportunidades w0 + Benefícios w1; hero `021` + destaque `block_3` / listagem `/vagas`); CSS `.block-jornada-estudante` / library `jornada_estudante`; Twig `block--block-jornada-estudante.html.twig` + `paragraph--passo-jornada-p.html.twig`; seed idempotente `custom_configs_update_11037` (UUID `b9c0d1e2-f3a4-4567-b890-1cdef0123456`; 4 passos; sem assets de imagem).
- Bloco `perfil_destaque_estudante` (`block_content`): coluna esquerda **mock HTML/CSS** no Twig (`.pd-visual` / gradiente + card de perfil — **sem** `field_image` no bloco); título (`field_text_simple`), itens (`field_itens_lista` → paragraph `item_lista_icone_p` com ícone `field_image` + título `field_text_simple` + descrição `field_text_simple_long`; max 3 no form/validação — storage `field_itens_lista` permanece `-1`), CTA (`field_link`); placement `default_perfildestaqueestudante` em `content_full`, weight `3`, pages `/para-estudantes` — **`status: false`** (feature **053** / `11077`; entidade UUID `c0d1e2f3-a4b5-4678-c901-2def01234567` permanece no banco, reversível); CSS `.section-perfil-destaque` / `.block-perfil-destaque-estudante` / library `perfil_destaque_estudante`; Twig `block--block-perfil-destaque-estudante.html.twig` + `paragraph--item-lista-icone-p.html.twig`; seed idempotente `custom_configs_update_11038` + cleanup mock `11039` (3 itens; CTA “Completar meu perfil” → `/painel/estudante/perfil`; ícones `modules/custom/custom_configs/assets/perfil-destaque-estudante/`).
- Composição `/para-empresas` em `content_full` (weights 0–5, só essa URL; placements home 004–006 intocados): `nossos_diferenciais` **mesma instância da home** (UUID `b0a1c2d3-…`, placement `default_nossosdiferenciaisparaempresas`) → `nossa_metodologia` **mesma instância da home** (UUID `1f9b40ca-…`, placement `default_nossametodologiaparaempresas`) → `o_que_fazemos_bt` (`e5f6a7b8-…`) → Benefícios via reuso `diferenciais_quem_somos` título “Benefícios para Empresas” (`f6a7b8c9-…`, weight 3) → View `depoimentos_carousel` display `block_depoimentos_empresas` (weight 4; nodes `depoimento`; library `depoimentos_carousel`; seed `11032`) → `cta_v1` “Pronto para contratar os melhores talentos?” (`a7b8c9d0-…`, weight **5**, links `/cadastro/empresa` + `/painel/empresa/vagas/nova`). Seed + limpeza do Node shell: `11024`; reuso Diferenciais: `11025`; reuso Metodologia: `11026`; Depoimentos: `11032`. **UI (052)**: ND desc `#4E4E4E` + decos off `<768px`; depoimentos dot ativo `--brand-orange`; CTA PE por role (ver bullet `cta_v1`).
- View `depoimentos_carousel` display `block_depoimentos_empresas` (região `content_full`, weight 4, pages só `/para-empresas`): carrossel center mode (CSS+JS próprios; dots; autoplay off; **dot ativo** `var(--brand-orange)` — feature **052**); filtros `status=1` + `type=depoimento`; sort `created` DESC; pager none; Twig `views-view--depoimentos-carousel--block-depoimentos-empresas.html.twig` + teaser `node--depoimento--teaser.html.twig`; seed idempotente `custom_configs_update_11032` (≥4 nodes UUIDs `a0b1c2d3-…0001`…`0004`; assets `modules/custom/custom_configs/assets/depoimentos/avatar-1.png`…`avatar-4.png`).
- Bloco `missao_visao` (`block_content`): tipos/conteúdo seed da feature 011 permanecem; **placement `default_missaovisao` desabilitado** em `/quem-somos` (faixa pública passou para o Node — §3.1.0). Seed histórico `custom_configs_update_11014`; aposentadoria na rota via `custom_configs_update_11015` (UUID bloco `b2c3d4e5-f6a7-4b8c-9d0e-1f2a3b4c5d6e`; asset `modules/custom/custom_configs/assets/missao-visao/fundo-missao-visao.jpg` reutilizado no seed do Node).
- Bloco `diferenciais_quem_somos` (`block_content`): título (`field_text_simple`), subtítulo opcional (`field_text_simple_long`), itens (`field_itens_lista` ilimitado → paragraph `diferencial_simples_p` com ícone `field_image` via Image Style `diferencial_simples_icon` (160×160 → caixa CSS 80×80 desktop / 64×64 mobile) + rótulo `field_text_simple` + **subtexto opcional** `field_text_simple_long`); placement `default_diferenciaisquemsomos` em `content_full`, weight `10`, somente `/quem-somos`; seed `11016` + sync/refino **`11069`** (UUID `d5e6f7a8-b9c0-4d1e-8f2a-3b4c5d6e7f80`; ícones `modules/custom/custom_configs/assets/diferenciais-quem-somos/icon-1.png`…`icon-8.png` — atualiza só se provisórios/vazios); **também** instância PE “Benefícios para Empresas” (UUID `f6a7b8c9-…`, placement `default_beneficiosparaempresas`, weight 3, só `/para-empresas` — `11024`; subtítulo seed “Muito além da contratação.” via `11027`; **5 itens Figma** com subtexto via `11029`/`11030`); **distinto** do bloco da home `nossos_diferenciais` / `diferencial_item_p` (inalterados).
- Bloco `cta_v1` (`block_content`): título (`field_text_simple`), subtítulo (`field_text_simple_long`), botão primário (`field_link`), botão secundário (`field_link_2` — storage link card 1 novo; em `/quem-somos` outline `border: 1px solid #0F172A` sob `.block-cta-v1` — feature **047**); placement `default_ctav1quemsomos` em `content_full`, weight `12` (após Impact Numbers weight `11`), somente `/quem-somos`; seed idempotente `custom_configs_update_11019` (UUID `e6f7a8b9-c0d1-4e2f-9a3b-4c5d6e7f8091`; copy Figma + links `/vagas` e `/cadastro/candidato`); **também** instância PE empresas (UUID `a7b8c9d0-…`, placement `default_ctav1paraempresas`, weight **5** pós-020, só `/para-empresas` — `11024`/`11032`; seed “Abrir Vaga” → `/painel/empresa/vagas/nova`; **feature 052**: Twig `block--default-ctav1paraempresas.html.twig` + preprocess `is_empresa` — anônimo/sem role: só cadastro; role `empresa`: botão 1 disabled “Empresa Cadastrada” + “Abrir Vaga”; botão 2 **ausente do DOM** sem role; legado “Falar com Especialista” corrigido por `custom_configs_update_11076`); **também** instância PE estudantes (UUID `e1f2a3b4-c5d6-4789-d012-3ef012345678`, placement `default_ctav1paraestudantes`, weight **4** após `block_3`, só `/para-estudantes` — seed idempotente `custom_configs_update_11043`; copy “Pronto para dar o próximo passo?” + links `/cadastro/candidato` + `/vagas`; visual escuro full-bleed via Twig suggestion `block--default-ctav1paraestudantes.html.twig` + library `cta_v1_para_estudantes` / CSS `.block-cta-v1--para-estudantes` — **não** altera o Twig global); **também** instância detalhe de vagas (UUID `d4e5f6a7-b8c9-4012-c345-6789abcdef01`, placement `default_ctav1vagas`, região `content_full` weight `10`, visibility `entity_bundle:node` = `vagas` **e** `user_role` = `candidato` — seed `11048` + visibility/ação `11071` / feature **050**; copy “Pronto para o próximo passo?”; botão primário = candidatura (`js-candidatar-vaga` / rota `custom_panel.vagas_candidatar`; já candidatada → disabled); visual escuro via Twig `block--default-ctav1vagas.html.twig` + library `cta_v1_vagas` / CSS `.block-cta-v1--vagas` — **não** altera CTAs claros QS/PE nem CSS do CTA PE estudantes); CSS claro `.block-cta-v1` / library `cta_v1`; Twig `block--block-cta-v1.html.twig`; **não** reutiliza visual/CSS do bloco `cto` da home.
- Bloco `layout_contato` (`block_content`): tipo e configs 017 **permanecem** no sync; **placement `default_layoutcontato` removido** — `/contato` não renderiza mais o bloco. Seed histórico `custom_configs_update_11021`/`11022` (UUID bloco `d1e2f3a4-b5c6-4d7e-8f90-a1b2c3d4e5f6`; página `page` UUID `f3a4b5c6-…`). Limpeza na rota + Node Contato: `custom_configs_update_11023` (exclui bloco ID 16 / UUID seed se existirem; remove placement; seed Node Contato UUID `a4b5c6d7-e8f9-4012-b345-d6e7f8a9b0c1`; reassocia alias `/contato`). Twig/CSS legado do bloco podem permanecer no tema sem placement ativo.
- Content Type `contato` (node): fields — `webform` (storage reutilizado → webform `contato`), `field_imagem`, `field_email` + `field_phone_wpp` (storages **novos** em `node`), `field_text_simple` (título painel), `field_text_simple_long` (texto painel); Twig `node--contato--full.html.twig`; library `layout_contato` / CSS sob `.node--contato` / `.layout-contato-node`; H2 “Envie sua mensagem” hardcoded; atalhos `mailto:` / `wa.me` a partir dos fields (omit empty); asset seed `modules/custom/custom_configs/assets/contato/img-contato.png`; webform `contato` **inalterado** (elementos 017); título Drupal oculto via `default_page_title` (negate `/contato`); banner legado `block_1` sem `/contato` (`11020`).
- Tema `default`: regiões `sidebar_painel`, `painel_page_header`.
- Bloco `nossos_diferenciais` (`block_content`): título (`field_text_simple`), subtítulo (`field_text_simple_long`), imagem da **colagem completa** (`field_image` ~1324×718; fatias embutidas no asset editorial), lista (`field_diferenciais_lista` → paragraph `diferencial_item_p`); placements `default_nossosdiferenciais` (`<front>`, weight `-4`) e `default_nossosdiferenciaisparaempresas` (`/para-empresas`, weight `0`) — **mesma instância** UUID `b0a1c2d3-e4f5-4678-9abc-def012345678`; seed histórico feature **004**; mídia seed idempotente `custom_configs_update_11064` (assets `modules/custom/custom_configs/assets/nossos-diferenciais/` — `colagem.png` + `icon-01.png`…`icon-03.png`, só se campos vazios); **UI (042+052)**: library `nossos_diferenciais` / CSS `.block-nossos-diferenciais` / `.nd-*` (tipografia Figma, colagem CMS + decos CSS; subtítulo/desc `#4E4E4E`; decos ocultas `<768px`; alinhamento topo desktop; sem fatias no tema); **sem** fields/CMS novos; deploy = Git + `drush updb` + `drush cr`.
- Bloco `nossa_metodologia` (`block_content`): título (`field_text_simple`), subtítulo (`field_text_simple_long`), etapas superiores (`field_image` multi), passos (`field_metodologia_passos` → paragraph `metodologia_passo_p` com ícone + título); placements `default_nossametodologia` (`<front>`, weight `-3`) e `default_nossametodologiaparaempresas` (`/para-empresas`, weight `1`) — **mesma instância** UUID `1f9b40ca-aa77-4e7e-a450-5aa94888e274`; seed idempotente `custom_configs_update_11005` (bloco) + `11006` (títulos dos passos) + `11007` (imagens versionadas em `modules/custom/custom_configs/assets/nossa-metodologia/`, só se campos vazios); **UI (043)**: library `nossa_metodologia` / CSS `.block-nossa-metodologia` / `.nm-*` (tipografia Figma, wrappers 770px/1160px desktop, setas no centro dos círculos, Mobile First); **sem** fields/CMS novos; deploy = Git + `drush cr`.
- Bloco `o_que_fazemos_bt` (`block_content`): título (`field_text_simple`), descrição (`field_text_simple_long`), cards (`field_o_que_fazemos_itens` cardinality 3 → paragraph `o_que_fazemos_item_p` com título + `field_text_simple_multiple` itens ilimitados); card do meio em duas colunas no front; cores por CSS (navy/green/orange); placement `default_oquefazemos` em `content_full`, weight `-2`, somente `<front>` (após `nossa_metodologia` weight `-3`); seed idempotente `custom_configs_update_11008`; UUID `a7c3e9f1-2b4d-4e8a-9c6f-1d2e3f4a5b6c`.
- Bloco `como_funciona_bt` (`block_content`): título (`field_text_simple`), subtítulo (`field_text_simple_long`), imagem desktop (`field_image`) e mobile (`field_imagem_mobile`) renderizadas via `<picture>` (feature **044**); placement `default_comofunciona` em `content_full`, weight `-1`, somente `<front>` (após `o_que_fazemos` weight `-2`); seed histórico `custom_configs_update_11009` + imagens `11062`/`11063` + form/view display `11067`; **045** remove etapas textuais (`field_como_funciona_itens` / paragraph `como_funciona_item_p`) via `custom_configs_update_11065`/`11066`; UUID `c8d4e0f2-3a5b-4c6d-8e9f-0a1b2c3d4e5f`.
- Bloco `footer` (`block_content`): logo branca (`field_image`; fallback versionado `modules/custom/custom_configs/assets/footer/logo-rodape.png`), contato — e-mail (`field_email`) e telefone/WhatsApp (`field_phone_wpp`) — e sociais (`field_instagram`, `field_linkedin`); `field_text_simple_long` (tagline) e `field_text_simple_2` (endereço) existem mas **não** são exibidos; layout em 5 colunas: marca (logo + 4 ícones sociais circulares: e-mail → Instagram → LinkedIn → WhatsApp), Institucional, Para Estudantes, Para Empresas, Contato (`mailto:` / `tel:`); links das colunas 2–4 **hardcoded no Twig** (rotas canônicas `/sobre-nos`, `/politica-de-privacidade`, `/termos-de-uso`, `/para-estudantes`, `/cadastro/candidato`, `/blog`, `/cadastro/empresa`, `/painel/empresa/vagas/nova`); menus legados `rodape---sobre`, `rodape---para-estudantes`, `para-empresas` preservados sem uso pelo template; mobile (&lt;992px): accordion Bootstrap nas colunas 2–4; barra inferior centralizada com copyright dinâmico + “Desenvolvido por Diego Pereira” (sem link; sem links legais na barra); float WhatsApp `.whatsapp-float-block` com `z-index: 1050`; seed idempotente `custom_configs_update_11011` (bloco + logo, sem re-seed de menus; `11010` histórico); placement `default_footer` na região `footer_first`, weight `-5`; UUID `d0326db5-fc80-4cf5-a0b9-8f779dc4aeba`.

### 3.7 Diagrama lógico (resumo)

```
User(candidato) ──< candidatura >── Node(vagas) ──> User(empresa)
       │                                  │
       ├── paragraphs (formação/exp)      ├── taxonomias (curso/regime/tech)
       └── vagas_salvas (uid,nid)         └── field_candidatos_u (legado)
```

---

## 4. Estratégia de Engenharia

### 4.1 Princípios

1. **Contrib first** via Composer; custom só para regras de negócio do portal.
2. **Nunca** editar `core/` ou `vendor/`.
3. Config via Configuration API; objetivo: `config/sync` versionado (hoje ausente no repo — gap).
4. PHP 8+ / Drupal Coding Standards; Entity API / `entityQuery` (evitar SQL cru).
5. Stack local: Docker Compose (`eusouestagio-drupal` + PostgreSQL 16).

### 4.2 Módulos contrib (Composer — baseline)

| Pacote | Função no produto |
|--------|-------------------|
| `drupal/core-recommended` ^11.4 | CMS |
| `drupal/admin_toolbar` | UX admin |
| `drupal/bootstrap_barrio` | Base do tema `default` |
| `drupal/paragraphs` + `ctools` | Perfis compostos |
| `drupal/pathauto` | Clean URLs |
| `drupal/field_group` | UX de forms |
| `drupal/metatag` | SEO |
| `drupal/webform` | Formulários avançados (disponível) |
| `drupal/captcha` + `recaptcha` + `recaptcha_v3` | Antispam cadastros |
| `drupal/smtp` + `mimemail` + phpmailer | Entrega de e-mail |
| `drupal/twig_tweak` | Templates |
| `drupal/coffee` | Atalhos admin |
| `drush/drush` | Ops |
| `drupal/devel` (+ devel_php) | **Somente não-produção** |

### 4.3 Módulos custom (escopo)

| Módulo | Responsabilidade |
|--------|------------------|
| `custom_configs` | Proxy CEP `/api/cep/{cep}` |
| `custom_configs_users` | Cadastro, perfil read-only, alterar senha, selection empresa, mail alter |
| `custom_panel` | Painéis, candidatura node, vagas salvas/aplicadas, erros 403/404, forms edição |
| `custom_candidaturas` | Service legado `field_candidatos_u` + mail moderadores — **candidatar a consolidação** |
| `custom_notifications` | E-mails: nova vaga, nova empresa, novo candidato |
| `custom_banners` | Migração de banners legados; bloco hero de busca da home; autocomplete `/api/cursos/autocomplete` e `/api/cidades/autocomplete` (constraints cruzadas curso↔cidade — feature **041**); hero `/vagas` + `/api/vagas/autocomplete` (título/empresa — feature **048**) |

**Tema:** `themes/custom/default` (Barrio).

### 4.4 O que NÃO fazer em custom

- Reimplementar auth, Fields UI, Views, Pathauto.
- Expor SQL direto ao Postgres.
- Duplicar dois modelos de candidatura em features novas (usar só node `candidatura`).

---

## 5. APIs e Integrações

### 5.1 Posição Headless / JSON:API

**Não aplicável na v1 de produto.** O portal é acoplado ao tema Drupal. JSON:API/REST não fazem parte do escopo de entrega atual.

Endpoints JSON existentes são **auxiliares ao front Twig/JS**, não API pública de produto:

| Método | Path | Auth | Uso |
|--------|------|------|-----|
| GET | `/api/cep/{cep}` | Público + flood | Autocomplete endereço |
| POST | `/vaga/apply/{node}` | `candidato` | Candidatar |
| POST | `/painel/estudante/vagas-salvas/toggle` | `candidato` | Favoritar |
| POST | `/painel/moderador/candidaturas/status` | moderador/admin | Atualizar status |

### 5.2 Integrações externas

| Sistema | Uso |
|---------|-----|
| ViaCEP | Consulta CEP (via proxy Drupal) |
| Google reCAPTCHA | Cadastros |
| SMTP / Mime Mail | Notificações transacionais |

### 5.3 E-mails (chaves)

| Módulo | Key | Destinatário |
|--------|-----|--------------|
| `custom_notifications` | `nova_vaga_moderadores` | Moderadores |
| `custom_notifications` | `nova_empresa_moderadores` | Moderadores |
| `custom_notifications` | `novo_candidato_moderadores` | Moderadores |
| `custom_candidaturas` | `nova_candidatura_moderadores` | Moderadores |
| `custom_panel` | `candidatura_confirmacao` | Candidato |
| Core user | notify de registro | Usuário |

### 5.4 Futuro (fora do baseline)

Se houver app/SPA: habilitar JSON:API + OAuth/consumer keys, filtrar fields sensíveis, documentar recursos `node--vagas`, `node--candidatura`, `user` — **somente após decisão explícita de produto** e atualização deste PRD.

---

## 6. Requisitos Não Funcionais

### 6.1 Performance e cache

- Invalidação por **cache tags** de entidades (`node:N`, `user:U`, listas de Views).
- Controllers de painel: cache contexts por `user.roles` / `user`.
- Assets: libraries Drupal (`*.libraries.yml`); evitar JS global desnecessário.
- Imagens: estilos de imagem Drupal para cards/banners.

### 6.2 Banco de dados

- **PostgreSQL 16** (Docker / produção alinhados).
- Encoding UTF-8.
- Preferir Entity API / `entityQuery`; SQL só com justificativa e placeholders.
- Migrations de schema custom via `.install` / `hook_update_N` (ex.: `vagas_salvas`, campos de usuário).

### 6.3 Configuration Management

- **Gap atual:** não há `config/sync` no repositório.
- **Requisito:** estabelecer export (`drush cex`) de content types, fields, roles, Views, Pathauto, captcha, SMTP (sem secrets), e incluir no Git.
- Secrets (DB, SMTP, reCAPTCHA) apenas em `.env` / settings.php — nunca no PRD nem no sync versionado com valores reais.

### 6.4 Segurança

- reCAPTCHA nos cadastros.
- Flood no CEP.
- CSRF nos forms Drupal; POSTs AJAX via rotas com role.
- Não vazar CPF/documentos em pages cacheáveis anonimamente.
- Harden produção: desabilitar `devel` / `devel_php`.

### 6.5 SEO e URLs

- Pathauto para nodes públicos.
- Metatag nas landings e detalhe de vaga.
- Clean URLs obrigatórias.

### 6.6 Observabilidade

- Logger channels dos módulos custom (`custom_notifications`, candidaturas, CEP).
- Monitorar falhas de mail (SMTP).

### 6.7 Acessibilidade e i18n

- UI em **pt-BR** (`langcode: pt-br` nas configs exportadas).
- Forms e painéis: labels claros; contraste herdado do tema (ajustes visuais não alteram este PRD — ver governança §8).

### 6.8 Ambientes

| Ambiente | Stack |
|----------|--------|
| Local | Docker Compose: Drupal + Postgres 16 |
| Produção | VPS (ver regra `estagio-acesso-vps`) |

---

## 7. Milestones de Entrega

| Milestone | Objetivo | Entregáveis verificáveis | Critério de done |
|-----------|----------|--------------------------|------------------|
| **M0 — Baseline & PRD** | Congelar escopo | Este `PRD.md`; inventário as-built | Stakeholders alinhados; débito `field_candidatos_u` documentado |
| **M1 — Config Sync** | CM versionada | `config/sync` com node types, fields, roles, Views, taxonomies, Pathauto | `drush cim` limpo em ambiente limpo; vocabulários nomeados neste PRD |
| **M2 — Contas & Perfis** | UC-01–05, 14–15 | Cadastros + painéis perfil + CEP + senha + reCAPTCHA | Testes manuais por role; e-mails de novo usuário |
| **M3 — Vagas públicas** | UC-06–07, 11, 16 | View filtros, detalhe, landings, banners | Filtros funcionais; clean URLs; metatags básicas |
| **M4 — Candidaturas canônicas** | UC-08–13 | Node candidatura, salvas, aplicadas, painéis empresa/moderador, status | Sem duplicidade de candidatura; status atualizável; isolamento empresa |
| **M5 — Notificações** | E-mails operacionais | Keys §5.3 via SMTP | Moderadores e candidato recebem eventos corretos |
| **M6 — Higiene técnica** | Remover legado | Deprecar/remover `CandidaturasManager` path ou migrar dados residual; permissões se necessário | Um único modelo de candidatura; código morto removido |
| **M7 — Hardening & Go-live** | NFR | Sem devel em prod; cache; backup Postgres; checklist segurança | Aceite de performance/segurança; deploy documentado |

**Dependências:** M1 alimenta todos; M4 depende de M2–M3; M5 pode paralelizar com M4; M6 após M4 estável; M7 último.

---

## 8. Governança do documento (Guardião do Escopo)

Para economizar tokens e evitar churn:

**Não** exigir revisão do PRD para mudanças de frontend (CSS/JS/Twig) ou ajustes simples de copy/UI.

**Notificar e perguntar** (sem atualizar automaticamente) somente se houver alteração **estrutural** em:

- `composer.json` (novas dependências);
- `config/sync/` ou `*.schema.yml` / `*.routing.yml`;
- `*.module` ou serviços em `modules/custom/` (ou `web/modules/custom/`).

Pergunta padrão: *“Essa alteração estrutural exige atualização no PRD.md?”*

---

## 9. Riscos e decisões abertas

| ID | Item | Impacto | Resolução sugerida |
|----|------|---------|-------------------|
| R1 | `rascunho_requisitos.md` ausente | Possível desalinhamento com intenção original | Validar este PRD com o product owner |
| R2 | Dual model candidatura | Dados inconsistentes | M6 — node `candidatura` canônico |
| R3 | ~~Sem `config/sync`~~ | Resolvido em `001-banners-busca-home` — sync em `config/sync/` | Manter `drush cex` no fluxo |
| R4 | ~~Machine names de taxonomies só no DB~~ | Resolvido — `curso`, `regime`, `tecnologias`, `tags` em §3.4 | — |
| R5 | Devel em Composer | Risco se ativo em prod | Bloquear em M7 |

---

## 10. Apêndice — Rotas custom (referência rápida)

| Path | Role |
|------|------|
| `/cadastro/candidato` | anônimo |
| `/cadastro/empresa` | anônimo |
| `/meu-perfil` | autenticado |
| `/admin/perfil/empresa` | empresa |
| `/painel` | autenticado |
| `/painel/estudante/perfil` | candidato |
| `/painel/estudante/alterar-senha` | autenticado* |
| `/painel/estudante/vagas-salvas` | candidato |
| `/painel/estudante/vagas-aplicadas` | candidato |
| `/painel/empresa/perfil` | empresa |
| `/painel/empresa/alterar-senha` | empresa |
| `/painel/empresa/candidaturas` | empresa |
| `/painel/moderador/candidaturas` | moderador+admin |
| `/vaga/apply/{node}` | candidato POST |
| `/api/cep/{cep}` | público GET |
| `/api/cursos/autocomplete` | público GET (`access content`) — params opcionais `cidade`/`estado` (feature **041**) |
| `/api/cidades/autocomplete` | público GET (`access content`) — sugestões cidade+UF com vagas publicadas (feature **040**); param opcional `curso` (feature **041**) |
| `/api/vagas/autocomplete` | público GET (`access content`) — sugestões de título de vaga + nome de empresa (feature **048**; hero `/vagas`) |
| `/contato` | anônimo (Node `contato` + alias; placement `layout_contato` removido) |
| `/empresa/{alias}` | público (Pathauto node `empresa`; ex. `/empresa/ecoconstrutora`) |
| `/acesso-negado` / `/pagina-nao-encontrada` | público |

\*Rota estudante de senha hoje exige apenas login; alinhar a `_role: candidato` se desejado (ajuste estrutural de routing → dispara Guardião do Escopo).
