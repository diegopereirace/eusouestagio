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
| UC-03 | Login e redirecionamento ao painel | Autenticado | `/painel` redireciona conforme role (estudante / empresa / moderador) |
| UC-04 | Completar / editar perfil estudante | Candidato | Dados pessoais, endereço (CEP), formação, paragraphs de instituição/cursos/experiências |
| UC-05 | Completar / editar perfil empresa | Empresa | Razão social, fantasia, CNPJ, endereço, responsável |
| UC-06 | Descobrir vagas | Público / Candidato | View `vagas` em `/para-estudantes` com filtros (curso, estado, cidade, escolaridade) |
| UC-07 | Ver detalhe da vaga | Público / Candidato | Node `vagas`; candidato logado vê ações candidatar / salvar |
| UC-08 | Salvar / remover vaga | Candidato | Toggle em tabela custom `vagas_salvas`; listagem em `/painel/estudante/vagas-salvas` |
| UC-09 | Candidatar-se a vaga | Candidato | Cria node `candidatura` (status `em_triagem`); confirmação ao candidato; listagem em vagas aplicadas |
| UC-10 | Acompanhar candidaturas (estudante) | Candidato | `/painel/estudante/vagas-aplicadas` |
| UC-11 | Publicar / gerir vagas | Empresa / Admin | Node `vagas` vinculado a `field_empresa_u`; notificação a moderadores |
| UC-12 | Ver candidaturas recebidas | Empresa | `/painel/empresa/candidaturas` (vagas da própria empresa) |
| UC-13 | Triagem de candidaturas | Moderador / Admin | `/painel/moderador/candidaturas` + POST de status |
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
| Quem somos | `quem-somos` | Institucional |
| Para empresas | `para-empresas` | Landing empresas |

#### 3.1.1 `vagas` (campos usados no tema/código)

| Campo | Tipo / uso |
|-------|------------|
| `field_empresa_u` | Entity reference → `user` (role `empresa`) |
| `field_candidatos_u` | Entity reference multi → `user` (legado; ver §1.3) |
| `field_cursos_t` | Taxonomy (cursos) |
| `field_regime_t` | Taxonomy (regime) |
| `field_tecnologias_t` | Taxonomy (fallback/exibição) |
| `field_cidade`, `field_estados`, `field_bairro` | Localização |
| `field_text_simple` | Salário / bolsa (rótulo de produto via UI) |
| `field_text_simple_2` | Nome empresa em cards (quando aplicável) |
| `field_text_long_formatted` | Descrição |
| `field_text_simple_multiple`, `_2` | Listas auxiliares |
| `field_auxilio_transporte` | Auxílio |
| `field_horarios` | Horários |

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

Vocabulários referenciados pelos campos `field_cursos_t`, `field_regime_t`, `field_tecnologias_t`. **Machine names exatos não estão no repositório** (config só no DB). Ação obrigatória no milestone de Config Management: exportar `taxonomy.vocabulary.*` e documentar aqui.

### 3.5 Tabelas custom

| Tabela | Módulo | Uso |
|--------|--------|-----|
| `vagas_salvas` | `custom_panel` | `uid`, `nid`, `created` — favoritos do estudante |

### 3.6 Block content / Views (produto)

- Block bundle `banner`; Views `banners`, `vagas` (`page_1`, `block_1`, `block_2` similares).
- Tema `default`: regiões `sidebar_painel`, `painel_page_header`.

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
| R3 | Sem `config/sync` | Drift entre ambientes | M1 prioritário |
| R4 | Machine names de taxonomies só no DB | PRD incompleto em §3.4 | Export + atualizar tabela |
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
| `/acesso-negado` / `/pagina-nao-encontrada` | público |

\*Rota estudante de senha hoje exige apenas login; alinhar a `_role: candidato` se desejado (ajuste estrutural de routing → dispara Guardião do Escopo).
