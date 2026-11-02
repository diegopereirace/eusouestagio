# Feature Specification: Hero de busca de /vagas — Autocomplete e refino visual (Figma)

**Feature Directory**: `specs/048-vagas-hero-search-autocomplete`  
**Created**: 2026-11-02  
**Status**: Draft  
**Input do usuário**: Refatorar e finalizar o bloco `custom_banners_vagas_hero_search` (topo de `/vagas`): os três inputs viram autocompletes assíncronos com debounce, o botão "Buscar Vagas" envia GET para `/vagas` filtrando a View `vagas`, e o visual segue o Figma (barra branca com divisórias, ícones, botão verde embutido, pills e link "Ver todas").  
**Figma**: `Eu sou estágio` — Hero Section (`node-id=196-1160`), barra de busca (`196-1186`), pill (`196-1189`).

## Escopo

### Inclui

- Autocomplete assíncrono nos **três** inputs do hero de `/vagas`, com debounce de 300 ms e cancelamento de requisição obsoleta.
- **Input 1 — "Cargo ou palavra-chave"**: novo endpoint JSON de busca ampla (título da vaga + nome da empresa), complementando os endpoints existentes.
- **Input 2 — "Cidade ou Remoto"**: reaproveita `/api/cidades/autocomplete` (features 040/041); placeholder passa a ser **"Cidade ou Remoto"**.
- **Input 3 — "Seu curso"**: reaproveita `/api/cursos/autocomplete` (feature 041).
- Ajuste do filtro exposto `title` da View `vagas` (`page_1`) para casar **título da vaga OU nome da empresa**, de modo que toda sugestão do Input 1 devolva resultados.
- Refino visual conforme Figma: container branco com sombra leve e cantos de 8–12 px, inputs sem borda nativa, ícones SVG (lupa, pin, capelo), divisórias verticais cinza claro no desktop, botão verde `#58A83C` com altura total do container e raio 8 px.
- Pills de curso (Tecnologia, Marketing, Administração, Engenharia, Saúde, Design, Direito) com fundo `#EFF4FF`, raio 9999 px e borda fina; link **"Ver todas"** abaixo das pills apontando para `/vagas` sem query (limpa filtros).
- Submit GET tradicional para `/vagas` preservando o comportamento atual de normalização (minúsculas, sem acento).
- Deploy automatizado: `hook_update_N` idempotente + `drush cex` das configs estruturais alteradas (View).

### Fora

- Hero da **Home** (`custom_banners_hero_search`, `.hero-search`) — não alterar classes, CSS ou JS da home.
- Cruzamento curso × cidade (restringir um campo pelo outro) no hero de `/vagas`; permanece exclusivo da Home (feature 041).
- Tratamento especial de "Remoto" (mapear para o filtro `regime`): **fora**; "Remoto" é apenas texto do placeholder (decisão do usuário).
- Campo de tags em vagas (não existe no modelo atual); busca por palavra-chave **não** cobre descrição, requisitos ou cursos.
- Migrar o tema para SCSS/PostCSS (o tema `default` usa CSS puro; ver feature 046).
- Spinner obrigatório, histórico de buscas, busca por voz.
- Alterações em `core/` ou `vendor/`.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante encontra uma vaga digitando cargo ou empresa (Priority: P1)

O visitante digita parte de um cargo ou do nome de uma empresa no primeiro campo e vê sugestões rápidas de vagas e empresas; ao escolher uma e buscar, a listagem mostra as vagas correspondentes.

**Why this priority**: É o campo principal da busca e o único que ainda não tem autocomplete.

**Independent Test**: Digitar "est" no Input 1, aguardar o dropdown, escolher uma sugestão, clicar em "Buscar Vagas" e conferir que a listagem contém ao menos uma vaga cujo título ou empresa corresponde.

**Acceptance Scenarios**:

1. **Given** o hero de `/vagas`, **When** o visitante digita ≥ 2 caracteres no Input 1 e pausa por 300 ms, **Then** um dropdown lista até 10 sugestões (títulos de vagas e empresas) com o trecho digitado destacado.
2. **Given** o visitante digita rapidamente várias teclas em sequência, **When** cada tecla ocorre antes de 300 ms da anterior, **Then** apenas **uma** requisição é enviada (após a pausa) e requisições anteriores em voo são canceladas.
3. **Given** o dropdown aberto, **When** o visitante escolhe uma sugestão (clique, ou setas + Enter), **Then** o input recebe o texto e o dropdown fecha.
4. **Given** uma sugestão de **empresa** escolhida, **When** o visitante busca, **Then** a listagem exibe as vagas dessa empresa (título OU empresa casa o termo).
5. **Given** um termo sem correspondência, **When** o dropdown consulta, **Then** o dropdown permanece aberto com a mensagem **"Nenhum resultado encontrado"** (mesmo texto da listagem de `/vagas`; item não selecionável) e o visitante ainda pode buscar pelo texto livre.
6. **Given** < 2 caracteres, **When** o visitante digita, **Then** nenhuma requisição é feita.

---

### User Story 2 — Visitante filtra por cidade e por curso com autocomplete (Priority: P1)

O visitante usa os campos "Cidade ou Remoto" e "Seu curso" com sugestões assíncronas (mesmos contratos de 040/041) e busca; a listagem filtra por cidade e/ou curso.

**Why this priority**: Fecha o requisito "três inputs funcionam como autocompletes"; reaproveita backend existente.

**Independent Test**: Digitar "sao" no Input 2, escolher `{Cidade} - {UF} - (N)`; digitar "adm" no Input 3 e escolher o curso; buscar e conferir os parâmetros na URL e a listagem.

**Acceptance Scenarios**:

1. **Given** o Input 2, **When** o visitante digita ≥ 2 caracteres, **Then** o dropdown exibe cidades no formato `{Cidade} - {UF} - ({N})`.
2. **Given** o Input 3, **When** o visitante digita ≥ 2 caracteres, **Then** o dropdown exibe cursos com vagas publicadas.
3. **Given** cidade e curso preenchidos, **When** busca, **Then** a URL é `/vagas?cidade=<norm>&cursos=<norm>` (sem acento, minúsculas) e a View aplica ambos os filtros.
4. **Given** os campos são independentes neste hero, **When** o visitante escolhe uma cidade, **Then** o Input 3 **não** é restringido nem desabilitado.

---

### User Story 3 — Visitante busca com qualquer combinação de campos (Priority: P1)

Qualquer combinação (um, dois, três ou nenhum campo preenchido) é válida; o botão envia GET para a página atual `/vagas`.

**Why this priority**: Regressão crítica — nenhum campo pode ser obrigatório.

**Independent Test**: Buscar com cada combinação e conferir URL e listagem.

**Acceptance Scenarios**:

1. **Given** apenas o Input 1 preenchido, **When** busca, **Then** a URL contém só `title=<norm>`.
2. **Given** todos os campos vazios, **When** busca, **Then** o destino é `/vagas` sem query (listagem padrão).
3. **Given** a URL de `/vagas?title=...&cidade=...&cursos=...`, **When** a página carrega, **Then** os três inputs reaparecem preenchidos (nome canônico da cidade/curso).
4. **Given** o visitante pressiona Enter em qualquer input com o dropdown fechado, **When** o formulário envia, **Then** a busca é executada.

---

### User Story 4 — Visual fiel ao Figma (Priority: P1)

O hero exibe a barra "pílula" do Figma: container branco com sombra leve, ícones à esquerda de cada input, divisórias finas, botão verde embutido, pills azuis claras e o link "Ver todas".

**Why this priority**: Objetivo explícito de alinhamento de layout.

**Independent Test**: Comparar `/vagas` a 1280 px com o frame Figma `196-1160` (barra 56 px de altura, botão verde, pills 30 px de altura).

**Acceptance Scenarios**:

1. **Given** viewport ≥ 992 px, **When** o hero renderiza, **Then** os três campos e o botão ficam em uma linha, com divisórias verticais cinza claro entre os campos e o botão ocupando toda a altura útil do container (≈ 56 px).
2. **Given** viewport < 992 px, **When** o hero renderiza, **Then** campos e botão empilham em coluna, divisórias viram linhas horizontais, e o botão ocupa a largura total.
3. **Given** qualquer input em foco, **When** o visitante interage, **Then** nenhuma borda/outline nativa do input aparece; o foco é indicado de forma acessível no campo (ex.: contorno suave no container do campo), sem quebrar o visual.
4. **Given** as pills, **When** renderizadas, **Then** têm fundo `#EFF4FF`, borda fina em azul claro, `border-radius: 9999px`, e cada uma leva a `/vagas?cursos=<norm>`.
5. **Given** o link "Ver todas" abaixo das pills, **When** clicado, **Then** navega para `/vagas` (sem query) e a listagem volta ao estado sem filtros.
6. **Given** o Input 1 com texto (digitado ou pré-preenchido pela URL), **When** o hero renderiza em ≥ 992 px, **Then** os inputs restantes (cidade e curso) ficam agrupados num cluster compacto à direita, e o Input 1 ocupa o espaço livre.
7. **Given** o Input 1 vazio, **When** o hero renderiza, **Then** os três campos voltam ao layout equilibrado (sem cluster).

---

### User Story 5 — Navegação por teclado e acessibilidade no dropdown (Priority: P2)

O dropdown é operável por teclado e anunciado por tecnologias assistivas.

**Independent Test**: Navegar só com teclado (setas, Enter, Esc, Tab) e validar roles ARIA com leitor de tela/inspeção.

**Acceptance Scenarios**:

1. **Given** o dropdown aberto, **When** o visitante usa setas ↑/↓, **Then** o item ativo muda e é refletido em `aria-activedescendant`.
2. **Given** o dropdown aberto, **When** pressiona Esc, **Then** o dropdown fecha sem limpar o texto digitado.
3. **Given** o input, **When** inspecionado, **Then** possui `role="combobox"`, `aria-expanded`, `aria-controls` e o dropdown tem `role="listbox"` com itens `role="option"`.

---

### Edge Cases

- Falha de rede/erro 5xx no autocomplete: dropdown fecha silenciosamente; o formulário continua enviável com o texto livre.
- Resposta atrasada de requisição antiga chegando depois da nova: deve ser descartada (sem sobrescrever o dropdown atual).
- Termo com caracteres especiais (`%`, `_`, aspas, `<script>`): tratado como texto literal; nada é interpretado como curinga nem injetado como HTML (sugestões renderizadas como texto).
- Termo muito longo (> 80 caracteres): truncado/ignorado de forma segura; sem erro 500.
- Acentos e maiúsculas: "estagio" encontra "Estágio"; "administracao" encontra "Administração".
- Vagas despublicadas ou empresas despublicadas não aparecem nas sugestões.
- Empresa sem nenhuma vaga publicada não aparece nas sugestões do Input 1.
- Vaga nova ou despublicada: sugestões refletem a mudança em até o tempo de cache (≤ 5 min) ou imediatamente por invalidação de cache.
- JavaScript desabilitado: o formulário GET nativo ainda funciona (sem autocomplete).
- Valores já presentes na URL ao carregar (`?title=`, `?cidade=`, `?cursos=`) não abrem dropdown sozinhos.
- Viewport estreito (≤ 575.98 px): dropdown ocupa a largura do campo, sem overflow horizontal.

## Requirements *(obrigatório)*

### Functional Requirements

**Autocomplete (frontend)**

- **FR-001**: Os três inputs do hero de `/vagas` MUST oferecer sugestões assíncronas a partir de **2 caracteres** digitados.
- **FR-002**: O disparo da requisição MUST usar **debounce de 300 ms** após a última tecla; digitação contínua não gera uma requisição por tecla.
- **FR-003**: Ao disparar nova requisição para um input, a anterior em voo MUST ser cancelada; respostas obsoletas MUST ser descartadas.
- **FR-004**: Sugestões repetidas (mesmo endpoint + mesmo termo) na mesma visita SHOULD ser servidas de cache em memória, sem nova requisição.
- **FR-005**: O dropdown MUST ser renderizado abaixo do campo ativo, com largura mínima da largura do campo, trecho digitado destacado e conteúdo inserido como **texto** (nunca HTML bruto).
- **FR-006**: O dropdown MUST suportar mouse e teclado (↑/↓, Enter, Esc, Tab) e atributos ARIA de combobox/listbox/option.
- **FR-007**: O dropdown MUST fechar ao escolher item, pressionar Esc, perder o foco ou clicar fora. Lista vazia NÃO fecha o dropdown: exibe estado vazio (FR-032).
- **FR-008**: O JS do hero de `/vagas` MUST ser independente do JS da Home (`hero-search.js`) e MUST NOT depender de jQuery UI; cada behavior inicializa uma única vez por form (sem duplicidade em AJAX/bfcache).
- **FR-009**: Os inputs MUST manter `autocomplete="off"` nativo do navegador para evitar sobreposição com o histórico do browser.

**Endpoints (backend)**

- **FR-010**: Um novo endpoint GET `/api/vagas/autocomplete?q=<termo>` MUST retornar JSON (lista) com objetos `{ value, label, type }`, onde `type` ∈ {`vaga`, `empresa`}; `value` é o texto a ser preenchido no input.
- **FR-011**: O endpoint MUST buscar por **título da vaga** e **nome da empresa** vinculada (somente vagas publicadas e empresas publicadas), ignorando acentos e maiúsculas, e MUST retornar no máximo **10** itens, sem duplicatas (mesmo `value`/`type`), priorizando correspondências por prefixo.
- **FR-012**: O endpoint MUST responder `[]` para `q` com menos de 2 caracteres, MUST limitar o tamanho de `q`, e MUST tratar `q` como literal (sem curingas); erros internos MUST resultar em `[]` (nunca stack trace).
- **FR-013**: O endpoint MUST ser público de leitura (permissão `access content`), método GET, e MUST ser cacheável por termo com invalidação ao criar/editar/despublicar vagas e empresas (padrão dos endpoints 040/041, janela máxima 5 min).
- **FR-014**: Os endpoints `/api/cidades/autocomplete` e `/api/cursos/autocomplete` MUST ser reaproveitados **sem mudança de contrato** (chamados no hero de `/vagas` sem parâmetros de cruzamento).
- **FR-015**: O código PHP novo MUST usar tipagem estrita (`declare(strict_types=1)`, tipos de parâmetro/retorno, injeção de dependência) e seguir o padrão dos controllers existentes em `custom_banners`.

**Busca e View**

- **FR-016**: O botão "Buscar Vagas" MUST enviar GET para `/vagas` com os parâmetros `title`, `cidade`, `cursos` (somente os preenchidos), valores normalizados (minúsculas, sem acento).
- **FR-017**: O filtro exposto `title` da View `vagas` (`page_1`) MUST casar **título da vaga OU nome da empresa** (contém, sem acento/caixa), mantendo o identificador `title` e demais filtros (`cidade`, `cursos`, `estado`, `escolaridade`, `regime`) inalterados.
- **FR-018**: Para visitantes sem JS, o envio nativo do formulário MUST funcionar com o mesmo destino e parâmetros (a normalização é aplicada no servidor se necessário para manter resultados).
- **FR-019**: Ao carregar `/vagas` com parâmetros na URL, os inputs MUST ser pré-preenchidos (valores canônicos de cidade e curso quando resolvíveis), sem disparar dropdown.

**Frontend visual (Twig/CSS)**

- **FR-020**: O formulário `.vagas-hero-search__form` MUST ter fundo `#FFFFFF`, sombra leve (ex.: `0 4px 12px rgba(0,0,0,0.05)`), borda sutil opcional e **raio entre 8 e 12 px** (não mais pílula 999 px).
- **FR-021**: Em ≥ 992 px o layout dos campos MUST ser linha única (`flex-row`, itens centralizados); abaixo disso, coluna (`flex-column`).
- **FR-022**: Os inputs MUST ter borda/outline/box-shadow nativos removidos, fundo transparente e **ícone SVG à esquerda**: lupa (cargo), pin (cidade) e capelo (curso).
- **FR-023**: Em ≥ 992 px MUST haver divisórias verticais finas em cinza muito claro entre os campos; em < 992 px, divisórias horizontais.
- **FR-024**: O botão submit MUST ter fundo `#58A83C`, texto branco, `border-radius: 8px`, padding horizontal interno (`px-4`) e altura total do container (≈ 56 px) no desktop; largura total no mobile; estados `hover`/`focus` visíveis.
- **FR-025**: As pills MUST ter fundo `#EFF4FF`, borda fina azul clara, texto em azul escuro do tema, `border-radius: 9999px`, altura ≈ 30 px (padding 2×16 px), e cada link MUST apontar para `/vagas?cursos=<curso normalizado>`.
- **FR-026**: O link **"Ver todas"** MUST aparecer abaixo das pills, apontar para `/vagas` **sem query**, usar a cor de destaque do tema (laranja da marca) e MUST ser acessível por teclado.
- **FR-027**: O estilo MUST permanecer escopado em `.vagas-hero-search` (nenhuma regra nova em `.hero-search`) e usar os breakpoints Bootstrap 5 do tema (feature 046).
- **FR-028**: O dropdown de autocomplete MUST ter visual consistente com o hero (fundo branco, raio compatível, sombra, hover/ativo em azul claro) com classes próprias do `/vagas` (prefixo `vagas-hero-search`).
- **FR-032**: Quando a consulta de autocomplete (≥ 2 caracteres) retornar zero itens, o dropdown MUST permanecer aberto com a mensagem **"Nenhum resultado encontrado"** (não selecionável, sem `role="option"`); Enter nesse estado MUST submeter a busca pelo texto livre. Falha de rede continua fechando o dropdown (edge case existente).
- **FR-033**: Quando o Input 1 tiver texto não vazio, o formulário MUST agrupar visualmente os inputs restantes (cidade + curso) num cluster compacto; o Input 1 MUST expandir para o espaço livre. Com o Input 1 vazio, o layout MUST voltar aos três campos equilibrados. Em < 992 px o agrupamento MUST empilhar cidade e curso no cluster, sem overflow.

**Deploy**

- **FR-029**: A mudança estrutural na View `vagas` MUST ser entregue com `hook_update_N` **idempotente** em `custom_configs.install` (garante o filtro combinado no destino; no-op se já aplicado) e `drush cex` para `config/sync`; zero passos manuais no admin.
- **FR-030**: Receita de deploy: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`.
- **FR-031**: O PRD (§3.6 bloco/View, §10 rotas) MUST ser atualizado cirurgicamente: nova rota `/api/vagas/autocomplete`, "Ver todas" no hero de `/vagas` (substitui a nota "sem Ver todas"), filtro `title` com empresa.

### Key Entities

- **Vaga (node `vagas`)**: título, empresa (referência a node `empresa`), cidade, cursos; só publicadas entram nas sugestões.
- **Empresa (node `empresa`)**: título = nome da empresa; só publicadas com ≥ 1 vaga publicada entram nas sugestões.
- **Sugestão de autocomplete**: `{ value, label, type }` (Input 1) ou contratos existentes `{ value, label, estado?, count? }` (Inputs 2 e 3).
- **Parâmetros de busca**: `title`, `cidade`, `cursos` (query string GET para `/vagas`).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em digitação contínua de uma palavra de 10 letras, o visitante dispara **no máximo 1** requisição de autocomplete por input (após a pausa de 300 ms).
- **SC-002**: O dropdown aparece em **≤ 1 s** após a pausa de digitação em 95% das tentativas (meta ≤ 300 ms com resposta em cache).
- **SC-003**: 100% das sugestões do Input 1 (vagas e empresas), ao serem buscadas, retornam **≥ 1 vaga** na listagem.
- **SC-004**: Todas as 8 combinações de preenchimento (3 campos presentes/ausentes) levam a `/vagas` com os parâmetros corretos e listagem coerente.
- **SC-005**: Comparação visual a 1280 px com o Figma: barra com ≈ 56 px de altura, botão `#58A83C`, raio do botão 8 px, pills `#EFF4FF`; sem diferença perceptível de layout.
- **SC-006**: Em < 992 px não há overflow horizontal e todos os controles são utilizáveis (alvo de toque ≥ 44 px no botão e nos campos).
- **SC-007**: Fluxo completo (digitar, escolher sugestão, buscar) é realizável **somente por teclado**.
- **SC-008**: Zero regressão visual/funcional no hero da Home e nos endpoints 040/041.
- **SC-009**: Deploy em ambiente limpo exige apenas a receita da FR-030, sem passos manuais.

## Assumptions

- **Sem** `hook_update_N` nesta feature (D1/D6): filtro título/empresa vive em `custom_panel_views_query_alter`; `11071` permanece livre.
- O tema `default` usa **CSS puro** (`assets/css/components/vagas-hero-search.css`); "SCSS" do pedido é atendido por CSS escopado equivalente, sem introduzir build.
- O bloco, o theme hook `custom_banners_vagas_hero_search`, a library `default/vagas_hero_search` e o placement existente são **reaproveitados**; não há novo bloco nem novo placement.
- Remoto não tem tratamento especial (decisão do usuário): o placeholder é "Cidade ou Remoto", mas o valor digitado segue como `cidade`.
- Sugestões de curso/cidade no hero de `/vagas` são **sem cruzamento** entre campos (diferente da Home).
- O pacote de skills `mattpocock/skills` **não está instalado** neste workspace; suas práticas (código limpo, tipagem estrita, debounce/cancelamento) são aplicadas como diretrizes nesta spec e verificadas no plano. Instalar o pacote é opcional e não bloqueia a feature.
- Raio do container: 12 px (dentro da faixa 8–12 px do pedido); raio do botão: 8 px (valor do Figma).
- A normalização `title` na URL (minúsculas, sem acento) é mantida; a View compara sem acento/caixa conforme collation do banco.
- **Nota do plano (2026-11-02)**: no Postgres sem `unaccent`, o filtro nativo `title` da View **não** é accent-insensitive; o match real é feito em `custom_panel_views_query_alter()` (`translate(lower())`). Por isso FR-017 é atendido **estendendo esse hook** (título OU empresa via `field_vaga_empresa`), **sem** alterar `views.view.vagas.yml`; consequentemente FR-029 não exige `hook_update_N` nem `drush cex` (nenhuma config/seed muda). Ver `plan.md` (D1/D6) e `research.md`.
- Alterações de CSS/JS/Twig exigem `drush cr` e limpeza de agregação ao final da implementação (regra de reinício de serviços).
