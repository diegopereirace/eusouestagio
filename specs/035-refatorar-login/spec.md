# Feature Specification: Refatoração da página de Login (`/user/login`)

**Feature Directory**: `specs/035-refatorar-login`
**Created**: 2026-10-26
**Status**: Draft
**Predecessor**: `034-empresa-pagina-publica`
**Input do usuário**: Refatorar o formulário de login do usuário (`/user/login`), hoje "estourando" em 100% da tela, transformando-o em um card centralizado, elegante e alinhado à identidade visual do projeto (fundo branco/cinza claro, sombra sutil, cantos arredondados, título de boas-vindas, inputs grandes, botão laranja de largura total, link "Esqueceu a senha?" discreto). A abordagem técnica deve separar responsabilidades: Twig apenas com grid/card (sem lógica complexa) e classes Bootstrap injetadas nos campos/botão via Form API (`hook_form_alter`/`hook_form_user_login_form_alter`), mais um CSS com escopo dedicado para o refinamento visual.

## Escopo

### Inclui

- Refatorar exclusivamente a rota `/user/login` (`user_login_form`), identificada em runtime pela classe de body já existente `.page-user-login`.
- Novo wrapper de layout (grid Bootstrap 5: `.container.py-5`, `.row.justify-content-center.align-items-center` com altura mínima ~70vh, coluna `.col-12.col-md-6.col-lg-4`) e card (`.card.border-0.shadow-sm.rounded-4.p-4.p-lg-5`) aplicado **somente** quando a rota atual for `user.login`, sem alterar o layout padrão (`page.html.twig`) usado pelas demais páginas do site.
- Título de boas-vindas (ex.: "Acesse sua conta") acima dos campos, em tipografia Poppins semibold.
- Injeção de classes Bootstrap (`form-control`, `form-control-lg`, cantos arredondados) e placeholders nos campos `name` e `pass`, e classes (`btn btn-primary btn-lg w-100 mt-4`) no botão de submit, via Form API — reaproveitando o hook já existente `custom_panel_form_user_login_form_alter()` no módulo `custom_panel`.
- CSS/SCSS com escopo dedicado (`.page-user-login` ou equivalente) para tipografia, sombra, arredondamento e espaçamento do bloco de CAPTCHA/mensagens, registrado como biblioteca própria e anexado apenas na rota de login.
- Estilização discreta do link "Esqueceu a senha?" (e demais links auxiliares nativos do formulário), centralizado abaixo do botão, na cor azul padrão do tema.
- Responsividade do card entre mobile (`col-12`) e desktop (`col-lg-4` centralizado).
- Preservação de comportamentos já existentes: redirecionamento pós-login por papel (`_custom_panel_login_redirect`) e ocultação da aba "Criar nova conta" (`.page-user-login .tabs .nav-link--user-register`).

### Fora

- Páginas `/user/password` (recuperar senha) e `/user/register` (cadastro) — mantêm o visual atual nesta feature; podem receber tratamento equivalente em feature futura.
- Qualquer alteração em `core/` ou `vendor/`, incluindo os módulos `user`, `captcha`, `recaptcha`/`recaptcha_v3`.
- Regras de autenticação, políticas de senha, flood control/rate limiting ou qualquer lógica de segurança do Drupal core.
- Redesenho do widget de CAPTCHA em si (escopo limitado a espaçamento/wrapper ao redor dele).
- Qualquer bloco ou embed do `user_login_form` fora da página dedicada `/user/login` (ex.: logins em modais), caso existam — não identificados no momento desta especificação.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante acessa a tela de login (Priority: P1)

Visitante não autenticado abre `/user/login` e vê o formulário dentro de um card branco, centralizado na tela, em vez do formulário esticado ocupando 100% da largura.

**Acceptance Scenarios**:

1. **Given** um visitante não autenticado, **When** acessa `/user/login` em um desktop, **Then** o formulário aparece dentro de um card com fundo branco, sombra sutil e cantos arredondados, centralizado horizontal e verticalmente na área de conteúdo.
2. **Given** a mesma página, **When** carrega, **Then** um título de boas-vindas (ex.: "Acesse sua conta") aparece acima dos campos, com tipografia em destaque (Poppins semibold).

### User Story 2 — Visitante preenche e envia o formulário (Priority: P1)

Visitante interage com campos grandes e um botão de alto contraste para efetuar login.

**Acceptance Scenarios**:

1. **Given** o card de login, **When** o visitante observa os campos de usuário/e-mail e senha, **Then** eles exibem bordas suaves, cantos arredondados, altura confortável (`form-control-lg`) e placeholders claros ("Digite seu e-mail ou usuário", "Sua senha").
2. **Given** o card de login, **When** o visitante observa o botão de envio, **Then** ele ocupa 100% da largura do card, tem fundo laranja da marca (`--brand-orange` / `#FD7B1A`) e texto branco em negrito.
3. **Given** credenciais válidas preenchidas, **When** o visitante envia o formulário, **Then** o login é concluído e o redirecionamento por papel (candidato/empresa/moderador) já existente continua funcionando sem regressão.

### User Story 3 — Visitante lida com erros e CAPTCHA (Priority: P2)

Visitante que erra a senha ou precisa resolver um CAPTCHA continua vendo um card coeso, sem elementos colados ou desalinhados.

**Acceptance Scenarios**:

1. **Given** uma tentativa de login inválida, **When** a mensagem de erro é exibida, **Then** ela aparece dentro do card, com espaçamento adequado acima dos campos, sem quebrar o layout.
2. **Given** o módulo de CAPTCHA ativo no formulário, **When** o desafio é renderizado (topo ou abaixo dos campos), **Then** mantém espaçamento mínimo (`mb-3` ou equivalente) em relação aos elementos vizinhos.

### User Story 4 — Visitante acessa pelo celular (Priority: P2)

Visitante em um dispositivo móvel vê o mesmo card elegante, adaptado à largura da tela.

**Acceptance Scenarios**:

1. **Given** um visitante em viewport mobile (`<768px`), **When** acessa `/user/login`, **Then** o card ocupa a largura confortável da tela (`col-12`), com padding interno reduzido, mantendo a hierarquia visual do desktop.

### User Story 5 — Desenvolvedor mantém o template Twig puro (Priority: P3)

Ao evoluir o formulário no futuro, o time técnico encontra um Twig simples (apenas grid/card) e toda a lógica condicional de classes/atributos resolvida em PHP (Form API), facilitando manutenção.

**Acceptance Scenarios**:

1. **Given** o template responsável pelo card de login, **When** revisado, **Then** não contém `if`/lógica condicional sobre estado do formulário — apenas marcação estrutural do grid/card.
2. **Given** o módulo `custom_panel`, **When** revisado, **Then** concentra a injeção de classes Bootstrap, placeholders e atributos do formulário de login em um único hook (`custom_panel_form_user_login_form_alter()`), sem duplicar a mesma responsabilidade no tema (`default.theme`).

## Requirements *(obrigatório)*

- **FR-001**: A rota `user.login` DEVE renderizar o formulário dentro de um componente "Card" (fundo branco, `border-0`, `shadow-sm`, `rounded-4`, padding `p-4`/`p-lg-5`), centralizado horizontal e verticalmente no viewport.
- **FR-002**: O wrapper de grid (`.container.py-5`, `.row.justify-content-center.align-items-center`, altura mínima ~70vh, coluna `.col-12.col-md-6.col-lg-4`) DEVE se aplicar apenas quando a rota atual for `user.login`, sem alterar o layout padrão das demais páginas (`page.html.twig` permanece compatível com todas as outras rotas).
- **FR-003**: Um título de boas-vindas (ex.: "Acesse sua conta") DEVE aparecer acima dos campos do formulário, em tipografia Poppins semibold.
- **FR-004**: Os campos `name` e `pass` do formulário DEVEM receber classes Bootstrap (`form-control`, `form-control-lg`) e cantos arredondados (`rounded-3` ou equivalente) via Form API (`hook_form_user_login_form_alter`), sem reescrever `form.html.twig` do core.
- **FR-005**: Os campos `name` e `pass` DEVEM exibir placeholders ("Digite seu e-mail ou usuário", "Sua senha") definidos via Form API.
- **FR-006**: O botão de submit (`actions.submit`) DEVE receber classes (`btn btn-primary btn-lg w-100 mt-4`) via Form API, resultando visualmente em botão laranja da marca, largura total do card, texto branco em negrito.
- **FR-007**: O link "Esqueceu a senha?" (e demais links auxiliares nativos do formulário de login) DEVE aparecer discreto, centralizado, abaixo do botão de envio, na cor azul padrão do tema.
- **FR-008**: Quando o CAPTCHA/reCAPTCHA estiver presente no formulário, o elemento DEVE manter espaçamento (`mb-3` ou equivalente) em relação aos campos e ao botão, sem comprimir ou distorcer o card.
- **FR-009**: O comportamento existente de ocultar a aba "Criar nova conta" (seletor `.page-user-login .tabs .nav-link--user-register`) DEVE ser preservado sem regressão.
- **FR-010**: O redirecionamento pós-login por papel do usuário (`_custom_panel_login_redirect`, em `custom_panel`) DEVE continuar funcionando sem alteração de comportamento.
- **FR-011**: A estilização visual (card, tipografia, sombra, espaçamento) DEVE ter escopo CSS restrito à página de login (ex.: `.page-user-login`), sem efeitos colaterais em outras rotas ou formulários.
- **FR-012**: O template Twig responsável pelo grid/card NÃO DEVE conter lógica condicional complexa (múltiplos `if`, cálculos, laços) — qualquer decisão condicional DEVE ser resolvida em PHP (Form API / `hook_preprocess_page` ou equivalente) antes de chegar ao template.
- **FR-013**: A injeção de classes/atributos do formulário de login DEVE ficar centralizada em um único ponto (hook existente `custom_panel_form_user_login_form_alter()`), evitando duplicar a mesma responsabilidade em `default.theme` ou em outro módulo.
- **FR-014**: O card DEVE ser responsivo: em viewport mobile (`<768px`) ocupa `col-12` com padding reduzido; em desktop ocupa `col-lg-4` centralizado.
- **FR-015**: A URL/rota `/user/login` e o fluxo de autenticação (validação de credenciais, flood control) NÃO DEVEM ser alterados — apenas a apresentação visual do formulário.

## Assumptions

- O design aplica-se apenas à página dedicada `/user/login`; `/user/password` e `/user/register` permanecem com o visual atual até uma feature futura dedicada.
- Não foi identificado, no código atual, nenhum bloco/embed do `user_login_form` fora da rota `/user/login`; caso exista, está fora do escopo desta feature.
- A presença do CAPTCHA no formulário depende da configuração do módulo `captcha`/`recaptcha_v3` (pode ou não aparecer); o layout deve se manter coeso em ambos os casos.
- Verificado o pacote de skills `mattpocock/skills` (focado em padrões TypeScript/Node) quanto a práticas aplicáveis a este refactor: não há skill específica para Drupal/PHP/Twig nesse pacote; os princípios de clean code solicitados (separação Twig puro vs. lógica em PHP/Form API) foram aplicados manualmente, seguindo a convenção já existente no projeto (ex.: flags calculadas em `default_preprocess_page()` e consumidas como condicionais simples no Twig, como já ocorre com `content_full_after_banner`).

## Success Criteria

- **SC-001**: A página `/user/login` exibe o formulário dentro de um card centralizado (não mais ocupando 100% da largura da tela), em desktop e mobile.
- **SC-002**: O botão "Entrar" ocupa 100% da largura do card, com fundo laranja da marca e texto branco em negrito.
- **SC-003**: Os campos de usuário e senha exibem placeholders e altura confortável (`form-control-lg`), com cantos arredondados.
- **SC-004**: Mensagens de erro e o bloco de CAPTCHA (quando presente) aparecem com espaçamento adequado, sem quebrar o layout do card.
- **SC-005**: O redirecionamento pós-login por papel e a ocultação da aba "Criar nova conta" continuam funcionando sem regressão após a refatoração.
- **SC-006**: O template Twig do card não contém lógica condicional complexa; toda classe/atributo condicional do formulário é resolvido em PHP (Form API), validável por revisão de código.
