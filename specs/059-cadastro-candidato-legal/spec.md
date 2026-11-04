# Feature Specification: Cadastro de Candidato (UX) e Páginas Legais

**Feature Directory**: `specs/059-cadastro-candidato-legal`  
**Created**: 2026-11-04  
**Status**: Draft  
**Predecessor**: `058-refino-figma-contato`, `035-refatorar-login`  
**Input do usuário**: Refatorar a página de **Cadastro de Candidato** (`/cadastro/candidato`) — experiência fluida, responsiva e alinhada à identidade visual (azul escuro, laranja `#FD7B1A`, fundos claros) — e **criar as páginas legais ausentes** (**Termos de Uso** e **Política de Privacidade**) com aliases estritos `/termos-de-uso` e `/politica-de-privacidade`, linkadas no checkbox de aceite e no rodapé. Sem layout Figma: atuar como UX/UI + desenvolvimento. Priorizar injeção de classes via Form API (`hook_form_alter`) para manter templates limpos; deploy robusto e idempotente (`hook_update_N` + `drush cex`). Banner responsivo com `<picture>` (desktop/mobile), card moderno para o formulário, validação de envio com reCAPTCHA e redirecionamento ao painel do estudante.

## Escopo

### Inclui

- Criação automatizada (seed idempotente) de duas Páginas Básicas (`page`) quando ausentes:
  - **Termos de Uso** com alias estrito `/termos-de-uso`;
  - **Política de Privacidade** com alias estrito `/politica-de-privacidade`;
  - corpo HTML genérico estruturado (títulos + parágrafos placeholder) editável depois pelo editorial;
- Garantia de que os links do rodapé (já apontando para essas URLs) e o texto do checkbox de aceite do cadastro resolvem para páginas reais (não `#` / 404);
- Refino visual da rota `/cadastro/candidato` (formulário `CandidatoRegistrationForm`):
  - banner no topo com título centralizado *“Crie sua conta e encontre seu estágio ideal”*, overlay escuro e `<picture>` (asset desktop ~1920×400 e mobile ~800×600, tema estudantes/carreira);
  - formulário em card centralizado (largura limitada, sombra sutil, cantos arredondados, padding generoso);
  - campos com aparência Bootstrap 5 confortável (inputs grandes, labels em negrito escuros);
  - botão de envio em largura total, laranja da marca;
  - checkbox de aceite estilizado, com links reais para Termos e Política abrindo em nova aba;
  - agrupamento lógico das seções já existentes (Acesso, Dados Pessoais, Endereço, Formação etc.) preservado e reforçado visualmente;
- Preservação do fluxo funcional: validação, reCAPTCHA (quando ativo), criação do usuário candidato e redirecionamento bem-sucedido ao painel `/painel/estudante/perfil` (quando o fluxo atual já autentica o usuário após o cadastro);
- Placeholders de imagem do banner versionados em assets do módulo/tema e instrução de `drush cex` quando houver config estrutural/exportável;
- Deploy sem passos manuais no admin de produção (seed + aliases via update hook).

### Fora

- Redesign do formulário de **Cadastro de Empresa** (`/cadastro/empresa`) — permanece como está nesta feature;
- Alteração das regras de negócio do cadastro (campos obrigatórios, validações de CPF, criação de paragraphs de experiência, papéis, e-mail de verificação), salvo ajustes de apresentação e do texto/links do aceite;
- Redação jurídica definitiva dos Termos/Política (seed é placeholder editorial);
- Redesign global de header, rodapé ou demais rotas públicas;
- Alteração de `core/` ou `vendor/`;
- Layout Builder / edição visual de colunas pelo editor para esta página;
- Código de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Visitante encontra Termos e Política publicados (Priority: P1)

Visitante (ou candidato em cadastro) abre `/termos-de-uso` ou `/politica-de-privacidade` e vê uma Página Básica publicada com título correto e conteúdo legível em HTML — sem 404. Os mesmos caminhos funcionam a partir dos links do rodapé.

**Why this priority**: Hoje o rodapé e o checkbox apontam para URLs que precisam existir; sem as páginas, o aceite e a conformidade visual/legal quebram.

**Independent Test**: Em ambiente após o deploy da feature, abrir as duas URLs e os links Institucionais do rodapé; confirmar 200 e título/corpo presentes.

**Acceptance Scenarios**:

1. **Given** ambiente sem as páginas legais, **When** o update de deploy é executado, **Then** existem Nodes do tipo Página Básica intitulados “Termos de Uso” e “Política de Privacidade”, publicados.
2. **Given** as páginas seedadas, **When** o visitante acessa `/termos-de-uso` e `/politica-de-privacidade`, **Then** recebe conteúdo HTML estruturado (pelo menos um título de seção e parágrafos), sem erro 404.
3. **Given** o rodapé institucional, **When** o visitante clica em Termos de Uso e Política de Privacidade, **Then** navega para as mesmas URLs canônicas e vê as páginas.
4. **Given** reexecução do update de deploy, **When** as páginas/aliases já existem, **Then** não há duplicação de Nodes nem sobrescrita do corpo editorial já alterado pelo cliente.

---

### User Story 2 — Candidato vê banner e formulário em card moderno (Priority: P1)

Visitante anônimo abre `/cadastro/candidato` e percebe um banner de topo com a mensagem de valor e, abaixo, o formulário de cadastro dentro de um card branco centralizado — não esticado em 100% da largura de forma “crua”.

**Why this priority**: É a primeira impressão da jornada de inscrição do estudante; alinhamento visual com login (035) e marca.

**Independent Test**: Abrir `/cadastro/candidato` em desktop (≥992px) e mobile (≤575px); verificar banner + card sem overflow horizontal.

**Acceptance Scenarios**:

1. **Given** viewport desktop, **When** abre `/cadastro/candidato`, **Then** vê um banner de topo com overlay escuro e o título *“Crie sua conta e encontre seu estágio ideal”* centralizado.
2. **Given** o mesmo viewport, **When** observa a área do formulário, **Then** o formulário aparece em um card com fundo claro, sombra sutil, cantos bem arredondados e largura limitada/centralizada (não “estourando” a tela inteira).
3. **Given** viewport mobile, **When** a página carrega, **Then** o banner usa a arte mobile (via `<picture>` / source adequado) e o card ocupa largura confortável da tela, sem scroll horizontal causado pelo layout.
4. **Given** o banner, **When** inspeciona a mídia, **Then** há variante desktop e mobile coerentes com o tema estudantes/carreira (placeholders aceitáveis até o editorial substituir).

---

### User Story 3 — Candidato preenche um formulário legível e alinhado à marca (Priority: P1)

O candidato interage com seções agrupadas, campos grandes, labels claras e um botão laranja de destaque para concluir.

**Why this priority**: Reduz abandono no formulário longo; reforça identidade (laranja `#FD7B1A`, azul escuro nos textos).

**Independent Test**: Percorrer seções (Acesso, Dados Pessoais, Endereço, Formação etc.); inspecionar inputs, labels e botão.

**Acceptance Scenarios**:

1. **Given** o formulário, **When** o candidato observa os grupos de campos, **Then** as seções lógicas existentes permanecem distinguíveis (títulos/agrupadores claros).
2. **Given** campos de texto, e-mail e senha, **When** observa a aparência, **Then** exibem altura confortável (estilo input grande), bordas legíveis e labels em destaque (negrito, tom escuro).
3. **Given** o botão de envio (“Cadastrar” ou equivalente), **When** observa o estilo, **Then** usa a cor laranja da marca (`#FD7B1A`), texto branco, largura total do card e espaçamento superior adequado.
4. **Given** o template da página, **When** revisado pelo time técnico, **Then** a marcação estrutural do banner/card permanece simples; classes/atributos de campos e botão são definidos via Form API (padrão do projeto, como em 035), sem reescrever o HTML interno do Drupal nos templates.

---

### User Story 4 — Candidato aceita Termos e Política com links reais (Priority: P1)

Antes de enviar, o candidato marca o aceite e consegue abrir Termos e Política em nova aba a partir do próprio texto do checkbox.

**Why this priority**: Compliance e confiança; hoje a descrição do campo usa `href="#"`.

**Independent Test**: Clicar nos dois links do checkbox; confirmar destinos e `target` de nova aba; tentar enviar sem marcar o aceite.

**Acceptance Scenarios**:

1. **Given** o checkbox de aceite, **When** lê o texto, **Then** vê menção a Termos de Uso e Política de Privacidade com links para `/termos-de-uso` e `/politica-de-privacidade`.
2. **Given** esses links, **When** o candidato clica, **Then** as páginas abrem em nova aba (`target="_blank"`) sem perder o formulário preenchido.
3. **Given** aceite desmarcado, **When** tenta enviar, **Then** o cadastro é bloqueado com mensagem amigável exigindo o aceite (comportamento já existente preservado).
4. **Given** o checkbox, **When** observa o controle, **Then** tem aparência de checkbox Bootstrap (controle alinhado ao texto).

---

### User Story 5 — Candidato conclui o cadastro sem regressão (Priority: P1)

Com dados válidos e aceite marcado, o candidato envia o formulário; o reCAPTCHA (se ativo) não quebra com o novo layout; a conta é criada e, no fluxo de sucesso com login automático, o usuário chega ao painel `/painel/estudante/perfil`.

**Why this priority**: A estética não pode comprometer a conversão nem a segurança anti-bot.

**Independent Test**: Submissão válida (com CAPTCHA desabilitado em local se necessário, e com CAPTCHA ativo em staging); verificar usuário criado e destino do redirect.

**Acceptance Scenarios**:

1. **Given** formulário válido + aceite + CAPTCHA ok (quando exigido), **When** envia, **Then** o usuário candidato é criado e a mensagem de sucesso aparece.
2. **Given** o fluxo em que a conta fica ativa e o login é finalizado automaticamente, **When** o cadastro conclui, **Then** o usuário é redirecionado ao painel do estudante (`/painel/estudante/perfil`).
3. **Given** reCAPTCHA ativo no ambiente, **When** a página renderiza e o envio ocorre, **Then** o widget permanece utilizável (visível, com espaçamento) e não impede o submit por causa do novo card/banner.
4. **Given** dados inválidos (ex.: e-mail inválido ou campo obrigatório vazio), **When** tenta enviar, **Then** erros aparecem de forma legível dentro do card, sem quebrar o layout.

---

### User Story 6 — Editorial atualiza textos legais sem novo deploy de copy (Priority: P2)

Editor autenticado com permissão de conteúdo edita o corpo das Páginas Básicas de Termos/Política no admin; as URLs públicas permanecem as mesmas.

**Why this priority**: O seed é placeholder; o valor de negócio está na editabilidade.

**Independent Test**: Alterar um parágrafo no Node e recarregar a URL pública.

**Acceptance Scenarios**:

1. **Given** Node “Termos de Uso” publicado, **When** o editor altera o `body` e salva, **Then** `/termos-de-uso` reflete o novo texto sem mudar o alias.
2. **Given** reexecução do hook de seed, **When** o corpo já diverge do placeholder, **Then** o conteúdo editorial **não** é sobrescrito.

---

### Edge Cases

- Alias `/termos-de-uso` ou `/politica-de-privacidade` já existentes apontando para outro Node: o seed **não** cria segundo Node conflitante; registra/retorna estado seguro (reuso ou no-op documentado).
- Node legal existe sem alias: o update **garante** o alias estrito faltante.
- Banner View legado já posicionado em `/cadastro/candidato` (região `banner`): a implementação evita **dois** banners simultâneos (desliga/substitui o legado nesta rota em favor do banner `<picture>` da page suggestion).
- Asset desktop/mobile ausente no disco: fallback visual aceitável (fundo sólido da marca + título) sem quebrar a página.
- Viewport intermediário (tablet): card em largura média; banner escolhe source adequado sem overflow.
- CAPTCHA desabilitado via setting local: layout do card permanece coeso sem “buraco” excessivo.
- Usuário já autenticado acessando `/cadastro/candidato`: comportamento de acesso atual do formulário/rota é preservado (sem novo requisito de UX nesta feature).
- Fluxo em que a conta exige verificação de e-mail ou aprovação: redirecionamentos já existentes (ex.: home) permanecem; o SC de painel aplica-se ao caminho de sucesso com login automático.
- Zoom alto / teclado virtual mobile: campos e botão permanecem acionáveis; sem sobreposição permanente do banner sobre o primeiro campo.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O sistema MUST garantir Páginas Básicas publicadas para “Termos de Uso” e “Política de Privacidade” após o deploy desta feature, criando-as somente se ainda não existirem (idempotência).
- **FR-002**: As páginas legais MUST responder em `/termos-de-uso` e `/politica-de-privacidade` (aliases estritos), sem 404.
- **FR-003**: O corpo seed das páginas legais MUST conter HTML genérico estruturado (títulos e parágrafos) suficiente para o editorial substituir depois.
- **FR-004**: Reexecução do seed MUST NÃO duplicar Nodes nem sobrescrever `body` já divergente do placeholder.
- **FR-005**: A rota `/cadastro/candidato` MUST exibir um banner de topo com título *“Crie sua conta e encontre seu estágio ideal”*, overlay escuro e mídia responsiva (desktop e mobile) via `<picture>`.
- **FR-006**: Assets placeholder do banner (desktop ~1920×400, mobile ~800×600, tema estudantes/carreira) MUST estar versionados no repositório (pasta de assets do módulo ou tema) para deploy reproduzível.
- **FR-007**: O formulário de cadastro de candidato MUST renderizar dentro de um container/card centralizado com aparência moderna (sem borda pesada, sombra sutil, cantos arredondados, padding generoso; largura tipicamente limitada em desktop, ex. até ~6 colunas de 12).
- **FR-008**: Inputs de texto, e-mail e senha MUST receber classes visuais de input grande (equivalente Bootstrap `form-control form-control-lg`) via Form API, sem reescrever templates de elemento do core.
- **FR-009**: Labels dos campos estilizados MUST usar aparência de rótulo em negrito e tom escuro (equivalente `form-label fw-bold text-dark`) via Form API / atributos do formulário.
- **FR-010**: O botão de envio MUST usar aparência de botão primário grande em largura total com margem superior (equivalente `btn btn-primary btn-lg w-100 mt-4`), mapeando o primário visualmente para o laranja da marca `#FD7B1A`.
- **FR-011**: O checkbox de aceite MUST usar aparência de `form-check` / `form-check-input` e seu texto MUST incluir links `<a>` para `/termos-de-uso` e `/politica-de-privacidade` com abertura em nova aba.
- **FR-012**: Seções lógicas do formulário (Acesso, Dados Pessoais, Endereço, Formação e demais já existentes) MUST permanecer agrupadas e visualmente distinguíveis.
- **FR-013**: A página MUST NÃO exibir dois banners concorrentes na mesma rota (View legado vs. banner da page suggestion).
- **FR-014**: Estilos específicos desta página MUST ter escopo restrito à rota `/cadastro/candidato` (sem alterar visual de `/cadastro/empresa`, login ou demais formulários, salvo herança intencional do token `.btn-primary` já global da marca).
- **FR-015**: Templates Twig desta feature MUST permanecer focados em estrutura (banner + wrapper do card); injeção de classes/atributos de campos, botão, placeholders e texto do aceite MUST concentrar-se em Form API (`hook_form_alter` / alter específico do form ID).
- **FR-016**: O envio válido do formulário MUST continuar criando o usuário candidato; no fluxo de sucesso com autenticação automática, MUST redirecionar para `/painel/estudante/perfil`.
- **FR-017**: Quando o reCAPTCHA estiver ativo, MUST permanecer utilizável após o refino visual (sem regressão de submit causada pelo layout).
- **FR-018**: Alterações estruturais exportáveis (placements de bloco, field description do aceite, etc.) MUST ser acompanhadas de `drush cex` na origem; seeds de conteúdo MUST ir em `hook_update_N` idempotente em `custom_configs.install`.

### Key Entities

- **Página Básica (Termos de Uso)**: Node `page` publicado; alias `/termos-de-uso`; corpo HTML editorial.
- **Página Básica (Política de Privacidade)**: Node `page` publicado; alias `/politica-de-privacidade`; corpo HTML editorial.
- **Formulário de Cadastro de Candidato**: Form ID `custom_configs_users_candidato_registration_form` na rota `/cadastro/candidato`.
- **Aceite (`field_termo`)**: Checkbox obrigatório no fluxo; texto com links legais.
- **Banner de cadastro**: Par desktop/mobile em assets versionados; renderizado no topo da page suggestion.
- **Card do formulário**: Container visual centralizado que envolve `{{ form }}`.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Após o deploy da feature, `/termos-de-uso` e `/politica-de-privacidade` respondem com sucesso (sem 404) em 100% das verificações de smoke test.
- **SC-002**: 100% dos cliques nos links legais do rodapé e do checkbox de cadastro levam às URLs canônicas corretas (e o checkbox abre em nova aba).
- **SC-003**: Em revisão visual desktop e mobile, `/cadastro/candidato` exibe banner com título canônico + formulário em card centralizado (não em formulário “esticado” sem container) em ≥95% dos critérios do checklist visual (overlay, centralização do título, sombra/cantos do card, botão laranja largura total).
- **SC-004**: Em viewport 375px e 1440px, não há scroll horizontal causado pelo layout desta página.
- **SC-005**: 100% dos envios de teste válidos (ambiente de teste) criam o usuário candidato; no fluxo de login automático pós-cadastro, 100% redirecionam para `/painel/estudante/perfil`.
- **SC-006**: Com reCAPTCHA ativo em staging, pelo menos 3 submissões de teste consecutivas válidas concluem sem falha atribuível ao novo layout.
- **SC-007**: Reexecução do update de seed não cria Nodes legais duplicados e não apaga texto editorial já customizado (verificado em teste de re-run).
- **SC-008**: Revisores conseguem validar a página (banner, card, aceite, páginas legais, submit) em até 15 minutos com o quickstart da feature.

## Assumptions

- Não há Figma desta página; a referência visual é a identidade do projeto (azul escuro, laranja `#FD7B1A`, fundos claros) e o padrão já estabelecido em `035-refatorar-login` (card + Form API).
- O formulário `CandidatoRegistrationForm` e a rota `/cadastro/candidato` já existem; esta feature é **refino de UX/UI + seed legal**, não recriação do fluxo de negócio.
- O campo `field_termo` e a validação de aceite já existem; o texto/descrição com `href="#"` será atualizado para as URLs reais (via config exportável e/ou override no alter do form).
- Existe placement de View `banners` (`block_2`) na região `banner` só em `/cadastro/candidato`; a feature assume **substituição** por banner `<picture>` na page suggestion para controle de LCP/copy fixa — o bloco legado fica inativo nesta rota (ou removido do placement) para evitar duplicidade.
- Placeholders de imagem do banner são suficientes para go-live; troca editorial futura pode ser feature separada ou upload manual se houver bloco CMS — fora do MVP se não houver field de banner editável.
- Performance do banner: `loading="eager"` (ou equivalente) se for LCP da página; caso contrário `lazy` é aceitável — decisão fina na implementação/plan.
- O pacote `mattpocock/skills` (quando disponível no ambiente do agente) deve ser consultado na implementação por práticas de CSS limpo; não há skill Drupal/Twig específica esperada — princípios de separação Form API vs. Twig aplicam-se manualmente.
- Próximo `hook_update_N` livre a partir de `11089` (último consolidado `11088`; `058` reservou `11089` só se necessário — confirmar número livre na implementação).
- Receita de destino padrão: `cim` → `updb` → `cim` → `cr` quando houver config exportada + seed; se só seed de Nodes/aliases, `updb` + `cr` podem bastar — detalhar no plan/quickstart.
- Cadastro de empresa e demais jornadas permanecem fora; links do rodapé para as URLs legais já estão hardcoded e passam a “funcionar” com o seed.
