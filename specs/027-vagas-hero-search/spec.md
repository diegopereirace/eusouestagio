# Feature Specification: Hero Search — Página de Vagas

**Feature Directory**: `specs/027-vagas-hero-search`  
**Created**: 2026-10-04  
**Status**: Draft  
**Input do usuário**: Primeiro bloco da página **Vagas** (`/vagas`) — **Hero Search** com título/subtítulo fixos, formulário GET de três campos (cargo/palavra-chave, cidade ou remoto, curso) mapeados aos filtros expostos da View `vagas` `page_1`, pills rápidas de cursos (sem link “Ver todas”), visual alinhado ao Figma (container 1280px, paddings 48/32/40, barra “pílula”, botão `#58A83C`), Block Plugin programático em módulo customizado, placement automatizado via `hook_update_N` idempotente + `drush cex`.

## Escopo

### Inclui

- Novo **Block Plugin** programático (ex.: `VagasHeroSearchBlock`) no módulo `custom_banners` (já hospeda o hero de busca da home; reutiliza o padrão de form GET + pills + resolução de termos).
- Renderização de formulário simples (`action` = `/vagas`, `method` = `GET`) com três campos e botão “Buscar Vagas”.
- **Mapeamento obrigatório dos parâmetros GET** aos filtros expostos da View `vagas` display `page_1`:
  1. “Cargo ou palavra-chave” → identifier do filtro de título/palavra-chave (novo se ainda não existir; ver FR-003).
  2. “Cidade ou Remoto” → identifier `cidade` (já existente sobre `field_cidade`).
  3. “Seu curso” → identifier `cursos` (já existente sobre `field_cursos_t`, vocabulário `curso`).
- Inclusão, na View `vagas` `page_1`, de filtro exposto de **título** (operador contém) com identifier estável (ex.: `title`), exportável via Configuration Management — necessário porque a baseline atual de `page_1` não expõe título/palavra-chave.
- Lista de **pills** de curso: Tecnologia, Marketing, Administração, Engenharia, Saúde, Design, Direito — links GET para `/vagas` com o parâmetro `cursos` aplicado ao termo correspondente; **sem** link “Ver todas”.
- Textos institucionais **fixos** no template (não administráveis):
  - Título: `Encontre a oportunidade ideal para sua carreira.`
  - Subtítulo: `Explore milhares de vagas de estágio em empresas parceiras e dê o próximo passo na sua jornada profissional.`
- Template Twig dedicado + CSS/SCSS com escopo da seção (tokens Figma):
  - Wrapper `max-width: 1280px`; paddings top `48px`, bottom `32px`, left/right `40px`; fundo branco.
  - Título com max-width ~`1062px`; subtítulo ~`715px`; tipografia Poppins.
  - Barra de busca em “pílula” (border-radius alto, sombra sutil); flex row no desktop / coluna no mobile; inputs sem borda Bootstrap; ícones (lupa, pino, capelo); divisórias verticais no desktop; botão `#58A83C`.
  - Pills: flex wrap, gap, outline azul claro.
- Placement do bloco na região **`highlighted`** do tema `default` (equivalente acima do conteúdo da View page — `/vagas` é display de página, não composição de blocos em `content_full`), visível **somente** em `/vagas`, com weight baixo o suficiente para ser o **primeiro** elemento da página (acima da listagem/`page_1`).
- `hook_update_N` idempotente em `custom_configs.install` (próximo livre: `11044`) garantindo o placement/visibilidade/weight.
- Exportação estrutural (`drush cex` → `config/sync`) do placement e da alteração da View (novo filtro `title`).
- Atualização cirúrgica do `PRD.md` (§3.6 / rota `/vagas`) refletindo o novo bloco e o filtro de título.

### Fora

- Redesign da listagem de cards, contador “N vagas encontradas”, filtros laterais da View ou perfil lateral do Figma.
- Alteração do hero de busca da **home** (`custom_banners_hero_search` / feature `001`).
- Link “Ver todas” sob as pills (explicitamente removido).
- Autocomplete avançado de curso (opcional futuro; v1 aceita texto livre no campo “Seu curso”).
- Criação de novos content types, paragraphs ou field storages.
- Exibição do bloco em home, `/para-estudantes`, `/para-empresas` ou outras rotas.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o Hero Search no topo de /vagas (Priority: P1)

Visitante acessa `/vagas` e, antes da listagem de vagas, vê o bloco hero com título, subtítulo, barra de busca de três campos e a fileira de pills de curso — sem o link “Ver todas”.

**Why this priority**: É o primeiro bloco da página e o ponto de entrada da busca; sem ele a experiência do Figma não existe.

**Independent Test**: Abrir `/vagas` em desktop e confirmar presença do hero acima da listagem, copy correta e ausência de “Ver todas”.

**Acceptance Scenarios**:

1. **Given** o bloco publicado e posicionado, **When** o visitante abre `/vagas`, **Then** vê título, subtítulo, formulário de três campos + “Buscar Vagas” e as pills listadas no escopo.
2. **Given** a página renderizada, **When** inspeciona a ordem vertical, **Then** o hero aparece **antes** da listagem da View `page_1`.
3. **Given** a fileira de pills, **When** procura o link “Ver todas”, **Then** ele **não** está presente.

---

### User Story 2 — Visitante busca por cargo, cidade e curso (Priority: P1)

Visitante preenche um ou mais campos e submete; a URL `/vagas` recebe os parâmetros GET correspondentes e a listagem reflete os filtros.

**Why this priority**: É o fluxo principal de valor do bloco.

**Independent Test**: Submeter o formulário com valores conhecidos e verificar query string + resultados filtrados.

**Acceptance Scenarios**:

1. **Given** o hero em `/vagas`, **When** o visitante digita um termo em “Cargo ou palavra-chave” e clica “Buscar Vagas”, **Then** a URL inclui o parâmetro do filtro de título e a listagem restringe vagas cujo título contém o termo.
2. **Given** o hero, **When** preenche “Cidade ou Remoto” com um nome de cidade existente nas vagas e submete, **Then** a URL inclui `cidade={valor}` e a listagem filtra por cidade.
3. **Given** o hero, **When** preenche “Seu curso” com um curso válido e submete, **Then** a URL inclui `cursos={valor}` e a listagem filtra por curso.
4. **Given** múltiplos campos preenchidos, **When** submete, **Then** os filtros combinam (AND) na listagem.
5. **Given** todos os campos vazios, **When** submete, **Then** chega em `/vagas` sem filtros obrigatórios e vê a listagem padrão, sem erro.

---

### User Story 3 — Visitante usa pill de curso (Priority: P1)

Visitante clica em uma pill (ex.: “Marketing”) e é levado à listagem já filtrada por aquele curso.

**Why this priority**: Atalho de descoberta sem digitar; presente no Figma.

**Independent Test**: Clicar em cada pill visível e confirmar filtro `cursos` aplicado.

**Acceptance Scenarios**:

1. **Given** pills renderizadas, **When** clica em “Engenharia”, **Then** navega para `/vagas` com `cursos` correspondente ao termo Engenharia e a listagem aplica o filtro.
2. **Given** um rótulo de pill cujo termo **não** existe no vocabulário `curso`, **When** o bloco renderiza, **Then** essa pill é omitida (não gera link quebrado).
3. **Given** qualquer pill, **When** observa o markup, **Then** não há CTA “Ver todas” ao lado ou abaixo das pills.

---

### User Story 4 — Visitante mobile usa a barra empilhada (Priority: P2)

Em viewport estreita, os três inputs e o botão empilham verticalmente; pills quebram em múltiplas linhas; sem scroll horizontal causado pelo hero.

**Why this priority**: Tráfego mobile significativo; layout Figma exige adaptação.

**Independent Test**: Abrir `/vagas` em viewport ≤575.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** os campos da busca empilham (não ficam em uma única linha espremida).
2. **Given** viewport mobile, **When** o visitante rola, **Then** não há barra de rolagem horizontal causada pelo hero.
3. **Given** viewport desktop, **When** observa a barra, **Then** os três inputs e o botão ficam em uma única linha com divisórias verticais sutis entre inputs.

---

### User Story 5 — Deploy automatizado do placement (Priority: P1)

Após deploy padrão, o hero aparece em `/vagas` sem configuração manual no admin de produção.

**Why this priority**: Regra permanente de deploy do projeto.

**Independent Test**: Ambiente limpo/desatualizado executa a receita `cim` → `updb` → `cim` → `cr` e valida presença/ordem do bloco.

**Acceptance Scenarios**:

1. **Given** código e `config/sync` versionados, **When** roda o deploy padrão, **Then** o bloco está ativo em `/vagas` com weight que o coloca primeiro.
2. **Given** segunda execução de `drush updb`, **When** o hook roda de novo, **Then** não duplica placement nem altera indevidamente o que já está correto (idempotência).
3. **Given** rota diferente de `/vagas`, **When** o visitante navega, **Then** este hero **não** aparece.

### Edge Cases

- Submissão com campos vazios → listagem padrão em `/vagas`, sem erro.
- Valor de cidade inexistente → listagem vazia ou mensagem “Nenhum resultado encontrado” já prevista pela View; sem quebra de página.
- Termo de curso digitado inexistente → listagem sem matches (comportamento nativo do filtro); sem erro fatal.
- Pill cujo termo de taxonomia não existe → pill omitida na renderização.
- Parâmetros GET malformados → View ignora/valida nativamente; página permanece utilizável.
- Digitar “Remoto” no campo de cidade → filtra o campo de cidade por texto “Remoto” (não aplica filtro de regime); filtrar por regime remoto continua disponível nos filtros laterais existentes da página.
- Bloco desabilitado ou placement ausente → página `/vagas` continua funcional só com a View (degradação sem hero).
- Convivência com o hero da home: placement e plugin distintos; home permanece inalterada.
- Reexecução do hook → no-op seguro.

## Requirements *(obrigatório)*

### Functional Requirements

**Bloco e formulário**

- **FR-001**: O sistema DEVE expor um Block Plugin programático dedicado ao Hero Search de `/vagas` (módulo `custom_banners`), distinto do hero da home.
- **FR-002**: O formulário do hero DEVE usar `method="get"` e `action` apontando para a rota pública `/vagas` (View `vagas` `page_1`).
- **FR-003**: A View `vagas` display `page_1` DEVE passar a ter filtro exposto de título (contains) com identifier estável (ex.: `title`), exportado em `config/sync`; o campo “Cargo ou palavra-chave” DEVE enviar esse identifier.
- **FR-004**: O campo “Cidade ou Remoto” DEVE enviar o parâmetro `cidade` (filtro exposto já existente).
- **FR-005**: O campo “Seu curso” DEVE enviar o parâmetro `cursos` (filtro exposto já existente sobre o vocabulário `curso`).
- **FR-006**: Título e subtítulo do hero DEVEM ser textos fixos no template/código de apresentação — **não** campos administráveis no banco.
- **FR-007**: Placeholders DEVEM ser: “Cargo ou palavra-chave”, “Cidade ou Remoto”, “Seu curso”; botão “Buscar Vagas”.
- **FR-008**: Valores já presentes na query string (ex.: após clique em pill ou nova busca) DEVEM pré-preencher os inputs correspondentes quando o visitante permanece em `/vagas`.

**Pills**

- **FR-009**: O bloco DEVE renderizar pills para os rótulos: Tecnologia, Marketing, Administração, Engenharia, Saúde, Design, Direito.
- **FR-010**: Cada pill DEVE ser um link GET para `/vagas` com `cursos` igual ao valor aceito pelo filtro exposto para o termo correspondente (resolvido em runtime por nome no vocabulário `curso`; nunca TID hardcoded no código-fonte).
- **FR-011**: O bloco **NÃO** DEVE renderizar link ou botão “Ver todas” associado às pills.
- **FR-012**: Se o termo de uma pill não existir (publicado) no vocabulário `curso`, essa pill DEVE ser omitida.

**Apresentação (tokens Figma)**

- **FR-013**: A seção do hero DEVE ter fundo branco e conteúdo em wrapper com `max-width: 1280px`.
- **FR-014**: O padding da seção DEVE ser top `48px`, bottom `32px`, left `40px`, right `40px`.
- **FR-015**: O título DEVE usar hierarquia de heading (`h1` preferencial nesta página, ou `h2` se `h1` já existir no chrome), família Poppins, negrito; max-width aproximada `1062px`.
- **FR-016**: O subtítulo DEVE ter max-width aproximada `715px` e cor de texto secundária compatível com o design (`#45464D` ou token equivalente do tema).
- **FR-017**: A barra de busca DEVE parecer uma pílula única (border-radius alto, sombra sutil, borda clara); inputs sem borda/outline/box-shadow padrão do Bootstrap; ícones à esquerda de cada input.
- **FR-018**: No desktop, inputs e botão DEVEM ficar em uma linha (flex); no mobile, empilhar; divisórias verticais apenas no layout em linha.
- **FR-019**: O botão “Buscar Vagas” DEVE usar fundo `#58A83C`, texto branco, sem borda externa, cantos alinhados à pílula, altura coerente com a barra (~56px).
- **FR-020**: As pills DEVEM usar layout flex wrap com gap; estilo outline (texto/borda azul claro, fundo transparente ou azul muito claro, cantos arredondados).
- **FR-021**: Estilos novos DEVEM ter escopo dedicado (classe de seção/bloco do hero de vagas), sem regredir o hero da home.

**Placement e deploy**

- **FR-022**: O placement DEVE usar o tema `default`, região `highlighted`, visibilidade `request_path` = `/vagas` (somente essa rota).
- **FR-023**: O weight do placement DEVE garantir que o hero seja o primeiro bloco visível da página `/vagas` (abaixo do header do site, acima da listagem da View).
- **FR-024**: Um `hook_update_N` idempotente em `custom_configs` (`custom_configs_update_11044`) DEVE garantir existência/ativação do placement com região, visibilidade e weight corretos (Configuration API / Entity API conforme padrão do projeto).
- **FR-025**: Alterações estruturais (placement + View com filtro `title`) DEVEM ser exportadas com `drush cex` para `config/sync` e versionadas no Git ao final do desenvolvimento na origem.
- **FR-026**: O fluxo de deploy em destino DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`; zero passos manuais no admin de produção.
- **FR-027**: A seção relevante do `PRD.md` DEVE ser atualizada de forma cirúrgica (§3.6 / `/vagas`) refletindo o Hero Search, o filtro de título e o placement.

### Key Entities

- **Block Plugin Hero Search Vagas**: bloco de apresentação com form GET + pills; textos fixos; sem entity `block_content` obrigatória (plugin puro, como o hero da home).
- **View `vagas` / `page_1`**: listagem em `/vagas`; filtros expostos relevantes: `title` (novo), `cidade`, `cursos` (existentes), além dos demais filtros laterais fora do escopo visual deste hero.
- **Vocabulário `curso`**: termos que alimentam o campo “Seu curso” e as pills.
- **Placement**: config `block.block.*` no tema `default`, região `highlighted`, path `/vagas`, weight prioritário.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, o hero em `/vagas` é reconhecível frente ao Figma em ≥95% dos critérios do checklist visual (paddings 48/32/40, pílula, botão `#58A83C`, pills, tipografia, ausência de “Ver todas”).
- **SC-002**: Em viewport mobile, 100% das verificações confirmam campos empilhados, pills com wrap e ausência de scroll horizontal causado pelo hero.
- **SC-003**: Um visitante consegue iniciar uma busca filtrada (título e/ou cidade e/ou curso) em ≤2 interações (preencher+submeter ou 1 clique em pill).
- **SC-004**: 100% das submissões do formulário resultam em navegação GET para `/vagas` com os parâmetros mapeados corretamente aos filtros da View.
- **SC-005**: 100% das pills cujo termo existe no vocabulário aplicam o filtro `cursos` ao clicar; pills sem termo correspondente não são exibidas.
- **SC-006**: Após o deploy padrão, `/vagas` exibe o hero como primeiro conteúdo abaixo do header; home e demais rotas não recebem este bloco.
- **SC-007**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cim -y` + `drush cr`, com zero passos manuais no painel.
- **SC-008**: Segunda execução de `drush updb` não duplica o placement (idempotência confirmada).
- **SC-009**: Textos de título/subtítulo do hero exigem zero linhas no banco de dados (100% em código/template).

## Assumptions

- O display `page_1` da View `vagas` continua com path canônico `/vagas` (já alterado em relação a docs antigos que citavam `/para-estudantes`).
- Região correta para o hero acima da View page é `highlighted` (mesmo padrão do hero da home e do antigo bloco de filtros expostos `default_formularioexpostovagaspage_1`); `content_full` é usado nas composições de páginas baseadas em blocos/nodes, não no corpo da View page.
- O placeholder “Cidade ou Remoto” no Figma mapeia ao filtro de **cidade** nesta página (pedido explícito do usuário). Diferente do hero da home (feature `001`), que mapeia o mesmo rótulo visual para **regime**. Filtrar vagas remotas nesta página permanece nos filtros laterais (`regime`).
- O filtro `cursos` permanece `taxonomy_index_tid` com widget textfield: pills e campo de curso enviam o **nome do termo** (contrato já validado no hero da home), não um TID hardcoded. A notação `[ID_DO_TERMO]` do briefing é interpretada como “identificador do termo aceito pelo filtro” (nome/TID conforme o widget vigente).
- Rótulos das pills podem não coincidir 100% com nomes de termos (ex.: “Tecnologia” vs “Tecnologia da Informação” / “TI”); a resolução em runtime DEVE usar match exato e, se necessário, prefixo/sinônimo seguro no mesmo espírito do `HeroSearchBlock` da home — sem inventar termos no vocabulário nesta feature (criar termo só se o produto confirmar ausência).
- Módulo-alvo: `custom_banners` (não `custom_panel`), por já concentrar o padrão de hero de busca.
- Hook: `custom_configs_update_11044` (último existente: `11043`).
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação (PHP do plugin + `hook_update_N`, Twig, SCSS/CSS, `drush cex`) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
