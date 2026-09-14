# Feature Specification: Banner da página Quem Somos

**Feature Directory**: `specs/009-banner-quem-somos`  
**Created**: 2026-09-14  
**Status**: Draft  
**Input do usuário**: Implementar o novo banner da página Quem Somos conforme layout Figma (caixa branca bipartida: copy + CTAs à esquerda, foto da mulher com prancheta “vazando” da caixa à direita). Reutilizar a View `banners` com display exclusivo `block_quem_somos`, filtro por local de exibição `quem_somos`, placement automatizado com visibilidade só em `/quem-somos`, remoção/desabilitação do banner antigo nessa página, template Twig isolado sob `.banner-quem-somos-wrapper`, CSS com escopo estrito, e deploy automatizado via `hook_update_N` idempotente + `drush cex` → `config/sync` (sem passos manuais em produção).

## Escopo

### Inclui

- Novo local de exibição `quem_somos` (“Quem Somos”) no campo `field_local_exibicao` do tipo `banners`.
- Novo display Block da View `banners` com machine name `block_quem_somos`, filtrando somente banners com local `quem_somos` (publicados).
- Placement do bloco `views_block:banners-block_quem_somos` na região `banner` do tema `default`, com visibilidade restrita ao path `/quem-somos`.
- Remoção de `/quem-somos` da lista de páginas do bloco legado `views_block:banners-block_1` (hoje exibido também em Quem Somos), para que o carrossel interno genérico não dispute o topo da página.
- Seed idempotente (via `hook_update_N` no `custom_configs`) de um banner publicado com local `quem_somos` e a imagem fornecida (`quem-somos-img` com fundo transparente), quando ainda não existir conteúdo adequado.
- Template Twig exclusivo do display (ex.: `views-view-unformatted--banners--block-quem-somos.html.twig` e/ou override de fields), com wrapper `.banner-quem-somos-wrapper`.
- Layout desktop bipartido (grid Bootstrap): coluna de texto `col-12 col-md-7` + coluna de imagem `col-12 col-md-5`.
- Copy e CTAs **fixos no Twig** conforme Figma (tag “QUEM SOMOS”, título bipartido de cor, parágrafo, botões “Conheça nossas vagas” e “Cadastre-se”).
- CSS/SCSS isolado sob `.banner-quem-somos-wrapper`, incluindo efeito de overflow da imagem (margens negativas / posicionamento) sem quebrar o empilhamento no mobile.
- Exportação estrutural via `drush cex` para `config/sync` e atualização cirúrgica do `PRD.md` (seção de banners / página Quem Somos).

### Fora

- Redesign da seção “Sobre nós” abaixo do banner (texto + foto de grupo + círculo verde/laranja) — permanece como está nesta feature.
- Alterações no carrossel da home (`block_home`) ou nos displays internos (`block_1`/`block_2`/`block_3`) além de retirar `/quem-somos` da visibilidade de `block_1`.
- Novos fields no tipo `banners` para título/parágrafo/CTAs do banner Quem Somos (textos fixos no Twig, padrão das features 001/002).
- Alterações em `core/` ou `vendor/`.
- Criação ou redesign do content type `quem_somos` (página/node) — apenas o banner no topo.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o banner Figma em Quem Somos (Priority: P1)

Visitante acessa `/quem-somos` em desktop e vê, abaixo do header, o banner em caixa branca com tag “QUEM SOMOS”, título “Mais que conectar” (azul escuro) + “desenvolvemos futuros.” (laranja), parágrafo descritivo, dois botões lado a lado e a foto da mulher com prancheta na coluna direita, com leve overflow da caixa — alinhado ao design aprovado.

**Why this priority**: É o valor principal da feature — a primeira impressão da página institucional.

**Independent Test**: Abrir `/quem-somos` em viewport ≥768px e comparar com os prints Figma (estrutura, textos, cores, CTAs, overflow da imagem).

**Acceptance Scenarios**:

1. **Given** a página `/quem-somos` com banner publicado para o local Quem Somos, **When** o visitante abre a página em desktop, **Then** vê o layout bipartido (texto à esquerda, imagem à direita) dentro do wrapper do banner.
2. **Given** o banner renderizado, **When** o visitante lê o conteúdo, **Then** vê a tag “QUEM SOMOS”, o título com as duas cores de marca e o parágrafo descritivo exatamente como no design.
3. **Given** a coluna de CTAs, **When** o visitante observa os botões, **Then** vê “Conheça nossas vagas” (primário laranja) e “Cadastre-se” (secundário com borda) lado a lado.
4. **Given** a imagem do banner, **When** comparada ao Figma, **Then** a figura “vaza” levemente da caixa branca sem cortar o rosto nem quebrar o restante da página.

---

### User Story 2 — Visitante mobile vê empilhamento legível (Priority: P1)

Em viewport estreita, o conteúdo empilha (texto/CTAs acima, imagem abaixo), permanece legível e tocável, e o efeito de overflow não causa scroll horizontal nem sobreposição indesejada do conteúdo “Sobre nós”.

**Why this priority**: Grande parte do tráfego é mobile; o efeito visual do Figma não pode sacrificar usabilidade.

**Independent Test**: Abrir `/quem-somos` em viewport ≤767.98px e verificar ordem, legibilidade e ausência de overflow horizontal.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** a coluna de texto/CTAs aparece acima da imagem.
2. **Given** viewport mobile, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pelo banner.
3. **Given** viewport mobile, **When** toca nos botões, **Then** ambos são alcançáveis e navegam para as rotas definidas.

---

### User Story 3 — CTAs levam às rotas canônicas (Priority: P1)

Os botões do banner direcionam o visitante para as jornadas corretas do produto (vagas e cadastro).

**Why this priority**: O banner é porta de entrada para conversão, não apenas decoração.

**Independent Test**: Clicar cada CTA e confirmar o destino.

**Acceptance Scenarios**:

1. **Given** o banner em `/quem-somos`, **When** o visitante clica em “Conheça nossas vagas”, **Then** é levado a `/para-estudantes`.
2. **Given** o banner em `/quem-somos`, **When** o visitante clica em “Cadastre-se”, **Then** é levado a `/cadastro/candidato`.

---

### User Story 4 — Banner antigo some; novo aparece só em Quem Somos (Priority: P1)

O carrossel genérico de internas deixa de aparecer em `/quem-somos`. O novo banner aparece **somente** nessa rota e não contamina home nem outras internas.

**Why this priority**: Evita regressão visual e banner duplicado no topo da página.

**Independent Test**: Comparar `/quem-somos`, home, `/para-estudantes` e `/contato` após o deploy.

**Acceptance Scenarios**:

1. **Given** deploy aplicado, **When** o visitante abre `/quem-somos`, **Then** vê apenas o novo banner Quem Somos no topo (não o carrossel genérico de internas).
2. **Given** deploy aplicado, **When** o visitante abre `/para-estudantes`, `/para-empresas` ou `/contato`, **Then** o bloco legado de banners internas continua disponível nessas rotas (sem o path `/quem-somos` na lista).
3. **Given** deploy aplicado, **When** o visitante abre a home, **Then** o carrossel `block_home` permanece inalterado.
4. **Given** o novo bloco, **When** inspecionada a visibilidade, **Then** ele só é exibido em `/quem-somos`.

---

### User Story 5 — Editor controla a imagem via banners (Priority: P2)

Editor publica/despublica (ou troca a imagem de) um conteúdo `banners` com local “Quem Somos”; a coluna de mídia do banner reflete essa escolha sem alterar os textos fixos do layout.

**Why this priority**: Reusa a governança editorial já existente do tipo `banners`, sem abrir os textos do Figma para edição acidental.

**Independent Test**: Publicar/despublicar um banner com local Quem Somos e recarregar `/quem-somos`.

**Acceptance Scenarios**:

1. **Given** um banner publicado com local Quem Somos, **When** o visitante abre a página, **Then** a imagem desktop (e mobile, se houver) alimenta a coluna direita.
2. **Given** nenhum banner publicado para Quem Somos, **When** a página abre, **Then** a seção do banner é omitida (sem caixa vazia).
3. **Given** os textos do Figma, **When** o editor altera campos do node `banners`, **Then** tag, título, parágrafo e rótulos dos botões **não** mudam (permanecem no template).

---

### User Story 6 — Deploy reproduz o banner em outro ambiente (Priority: P1)

Homologação/produção recebem display, filtro, allowed value, placement, seed da imagem e remoção do path legado apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto e desta feature.

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/quem-somos` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr`, **Then** o display, o placement e o seed necessários estão aplicados e o banner novo aparece.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de display, bloco ou conteúdo seed (idempotência).
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** View, field storage/allowed values e block placement aparecem versionados em `config/sync`.

### Edge Cases

- Zero banners publicados com local Quem Somos → omitir o wrapper inteiro (sem caixa branca vazia).
- Banner com imagem mobile ausente → usar desktop como fallback (padrão da feature 001).
- Banner com mais de um item publicado para Quem Somos → exibir apenas o primeiro conforme ordenação canônica (`field_peso` ASC, desempate por data DESC); não transformar em carrossel nesta feature.
- Imagem com fundo transparente → preservar alpha; fundo da coluna não deve “pintar” preto atrás da figura.
- Overflow da imagem em desktop → não cobrir header sticky nem CTAs; não gerar scroll horizontal.
- Viewport intermediária (~768px) → grid Bootstrap `md` aplica a bipartição; abaixo, empilha.
- Reexecução do hook → no-op seguro se display/bloco/allowed value/seed já existirem.
- Conteúdo editorial divergente do seed (imagem já trocada pelo editor) → hook **não** sobrescreve o arquivo/media existente.

## Requirements *(obrigatório)*

### Functional Requirements

**Dados e listagem**

- **FR-001**: O campo `field_local_exibicao` do tipo `banners` DEVE passar a aceitar o valor `quem_somos` (rótulo “Quem Somos”), além dos valores existentes `home` e `internas`.
- **FR-002**: A View `banners` DEVE ter um display Block com machine name `block_quem_somos` que lista somente nodes `banners` publicados com `field_local_exibicao = quem_somos`, ordenados por `field_peso` ASC e data de publicação DESC.
- **FR-003**: O display `block_quem_somos` DEVE expor a imagem desktop (e mobile quando existir) para o template; textos de copy/CTAs **não** vêm de fields do node.

**Placement e convivência com legado**

- **FR-004**: O bloco `views_block:banners-block_quem_somos` DEVE ser posicionado na região `banner` do tema `default`, com condição de visibilidade por path limitada a `/quem-somos`.
- **FR-005**: O bloco legado `views_block:banners-block_1` DEVE deixar de incluir `/quem-somos` em suas páginas de visibilidade (mantendo as demais rotas atuais).
- **FR-006**: O novo banner DEVE aparecer somente em `/quem-somos` e NÃO DEVE alterar o comportamento de `block_home` nem dos demais displays de internas fora dessa rota.

**Apresentação (Figma)**

- **FR-007**: Todo o markup do banner DEVE estar encapsulado em um elemento com a classe `banner-quem-somos-wrapper`; estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.
- **FR-008**: O layout desktop DEVE usar `.container` + `.row` com coluna de texto `col-12 col-md-7` e coluna de imagem `col-12 col-md-5` (ou proporção equivalente aprovada no aceite visual).
- **FR-009**: A tag superior DEVE exibir “QUEM SOMOS” em uppercase, com borda/fundo sutis na paleta laranja da marca.
- **FR-010**: O título DEVE renderizar “Mais que conectar” na cor `#0F172A` e “desenvolvemos futuros.” na cor `#FD7B1A` (aceitar variação documentada `#FD761A` do Figma como o mesmo token de marca laranja).
- **FR-011**: O parágrafo descritivo DEVE ser: “Somos especialistas em unir empresas e estudantes de forma estratégica, promovendo experiências de estágio que geram aprendizado, crescimento e resultados para todos.” com tipografia Poppins.
- **FR-012**: Os CTAs DEVEM ser “Conheça nossas vagas” (fundo `#FD7B1A`, texto branco) e “Cadastre-se” (fundo branco, texto escuro, borda), agrupados com espaçamento consistente (ex.: `d-flex gap-3`).
- **FR-013**: Os CTAs DEVEM apontar, respectivamente, para `/para-estudantes` e `/cadastro/candidato`.
- **FR-014**: A imagem da coluna direita DEVE permitir overflow controlado da caixa (efeito “vazar” do Figma) em desktop, com adaptação responsiva que preserve legibilidade e não cause scroll horizontal no mobile.
- **FR-015**: Em viewport mobile, o conteúdo DEVE empilhar texto/CTAs acima da imagem.

**Deploy e governança**

- **FR-016**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11011`, tipicamente `custom_configs_update_11012`) DEVE, de forma **idempotente**: garantir o allowed value `quem_somos`; garantir o display `block_quem_somos` (ou validar pós-`cim`); garantir placement/visibilidade do bloco; retirar `/quem-somos` do bloco legado; e seedar o banner/imagem quando ausente.
- **FR-017**: Todas as alterações estruturais (View, field storage, block placement) DEVEM ser exportadas com `drush cex` e versionadas em `config/sync`.
- **FR-018**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` (cim antes do updb).
- **FR-019**: A seção relevante do `PRD.md` (banners / página Quem Somos) DEVE ser atualizada de forma cirúrgica refletindo o novo local, display e comportamento.

### Key Entities

- **`banners` (node)**: conteúdo de banner; nesta feature, instância(s) com `field_local_exibicao = quem_somos` e imagem desktop (PNG com transparência preferencial) alimentando a coluna de mídia.
- **View `banners` / display `block_quem_somos`**: listagem filtrada que alimenta o bloco do topo de `/quem-somos`.
- **Bloco `views_block:banners-block_quem_somos`**: placement na região `banner`, visível só em `/quem-somos`.
- **Bloco legado `views_block:banners-block_1`**: carrossel/listagem de internas; perde o path `/quem-somos`.
- **Asset seed**: arte da mulher com prancheta (fornecida na especificação; versionada sob `modules/custom/custom_configs/assets/` na implementação).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, o banner de `/quem-somos` é reconhecível frente aos prints Figma (estrutura bipartida, textos, cores, CTAs e overflow da imagem) em ≥95% dos critérios de checklist visual.
- **SC-002**: Em viewport mobile, 100% das verificações de aceite confirmam empilhamento texto→imagem, CTAs tocáveis e ausência de scroll horizontal causado pelo banner.
- **SC-003**: 100% dos cliques nos CTAs levam às rotas `/para-estudantes` e `/cadastro/candidato`.
- **SC-004**: Após o deploy padrão, `/quem-somos` exibe o novo banner e **não** exibe o carrossel genérico de internas; home e demais internas listadas em `block_1` não regridem.
- **SC-005**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cr`, com zero passos manuais no painel.
- **SC-006**: Segunda execução de `drush updb` não cria displays, blocos ou nodes duplicados (idempotência confirmada).
- **SC-007**: Com zero banners Quem Somos publicados, a página não mostra caixa vazia no lugar do banner.
- **SC-008**: Estilos do banner não alteram visualmente outras seções (amostragem: home, `/para-estudantes`, seção “Sobre nós” na própria página).

## Assumptions

- **Textos e CTAs fixos no Twig** (padrão features 001/002): o Figma é a fonte de verdade da copy; o tipo `banners` continua responsável pela **imagem** (e publicação on/off), não pelos textos.
- **Região `banner`**: o bloco atual que inclui Quem Somos já usa `banner`; o novo display permanece nessa região (abaixo do header), não em `content_full`.
- **Um slide apenas**: o display Quem Somos não é carrossel; se houver múltiplos publicados, prevalece a ordenação canônica e o template renderiza o primeiro item relevante.
- **Destinos dos CTAs**: “Conheça nossas vagas” → `/para-estudantes`; “Cadastre-se” → `/cadastro/candidato` (rotas canônicas já usadas no rodapé/nav).
- **Token laranja**: `#FD7B1A` e `#FD761A` são tratados como o mesmo laranja de marca; a implementação usa o token/variável já existente no tema quando houver (`--brand-orange` ou equivalente).
- **Hook número**: próximo update após `custom_configs_update_11011` → `11012`, salvo se outro update for commitado antes da implementação.
- **Seed da imagem**: o PNG fornecido (fundo transparente) é versionado no módulo e anexado ao node seed somente se ainda não houver banner Quem Somos adequado; uploads editoriais posteriores são preservados.
- **Seção “Sobre nós”** abaixo do banner está fora do escopo; o overflow da imagem pode sobrepor visualmente o início dessa seção de forma controlada, como no Figma, sem ocultar o título “Sobre nós”.
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
