# Feature Specification: Perfil em Destaque (Estudante)

**Feature Directory**: `specs/024-perfil-destaque-estudante`  
**Created**: 2026-09-30  
**Status**: Draft  
**Input do usuário**: Quarta seção da página **Para Estudantes** (`/para-estudantes`) — bloco **“Seu perfil em destaque”** (Section 04: Profile Showcase) em duas colunas (ilustração à esquerda; título + lista com ícone + CTA à direita). Modelagem Custom Block `perfil_destaque_estudante` + Paragraph `item_lista_icone_p`, tipografia Poppins, Bootstrap 5, tokens Figma (container 1280px, paddings 64/40, ícones 20×20, CTA ~208×44), seed e placement automatizados com `hook_update_N` idempotente **abaixo** da Jornada (`jornada_estudante`), exportação `drush cex` → `config/sync`, templates Twig + CSS com escopo dedicado.

## Escopo

### Inclui

- Tipo de paragraph **Item de Lista com Ícone** (`item_lista_icone_p`, sufixo `_p` obrigatório) com ícone, título curto e descrição gerenciáveis.
- Tipo de bloco customizado **Perfil em Destaque** (`perfil_destaque_estudante`).
- Reutilização de storages canônicos do projeto:
  - Coluna esquerda: mock HTML/CSS no Twig (`.pd-visual` + gradiente + card Habilidades/Formação) — **não** usar `field_image` no block_content para essa ilustração.
  - Ícone do item via `field_image` (paragraph).
  - Títulos curtos via `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente; **não** criar storage paralelo).
  - Descrições via `field_text_simple_long`.
  - Lista via storage existente `field_itens_lista` (Entity Reference Revisions → `item_lista_icone_p`, **cardinalidade 3**), com nova field instance no bundle do bloco.
  - CTA via storage existente `field_link`.
- Displays de formulário e visualização (form/view) para o paragraph e o block type.
- Seed idempotente: uma instância do bloco com título “Seu perfil em destaque”, **3 itens** de lista (ícone + título + descrição) e link CTA “Completar meu perfil” apontando para `/painel/estudante/perfil` (sem ilustração cadastrável).
- Placement na região `content_full` do tema `default`, visível **somente** em `/para-estudantes`, com weight **imediatamente após** o bloco Jornada do Estudante (`default_jornadaestudante`, weight `1` → perfil tipicamente weight `2`).
- Templates Twig do bloco e do paragraph; layout em duas colunas (`.row.align-items-center.g-5`; `.col-12.col-lg-6` × 2); classe de seção `.section-perfil-destaque`.
- CSS/SCSS com escopo estrito (ex.: `.section-perfil-destaque` / `.block-perfil-destaque-estudante`): fundo `#FFFFFF`, max-width `1280px`, paddings Top/Bottom `64px` Left/Right `40px`, ícones `20×20px`, título `h2` com max-width de texto `528px` no desktop, tipografia Poppins, cores de destaque do item (`#9D4300` ou tom do check do Figma) e descrição `#45464D`, botão CTA ~`208×44`, fundo azul escuro, texto branco.
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11037`, tipicamente `11038`) garantindo tipos/fields/displays, seed via Entity API e placement.
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento.
- Atualização cirúrgica do `PRD.md` (rota `/para-estudantes` / §3.6) refletindo o novo block type e placement.

### Fora

- Redesign do Hero (`021`), Benefícios Estudantes (`022`) ou Jornada do Estudante (`023`).
- Listagem/filtros de vagas da View `vagas` na mesma rota.
- Formulário de cadastro de candidato ou edição real do perfil no painel (apenas o CTA apontando para a rota existente).
- Criação de field storages paralelos `field_text_simple_small` ou `field_imagem` em `block_content` (proibido — reutilizar canônicos).
- Exibição do bloco em home, `/para-empresas`, `/quem-somos` ou outras rotas.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o perfil em destaque em Para Estudantes (Priority: P1)

Visitante acessa `/para-estudantes` e, após a seção da Jornada, vê “Seu perfil em destaque” em duas colunas: ilustração à esquerda e, à direita, título, lista de até 3 itens com ícone e um botão “Completar meu perfil”.

**Why this priority**: Valor central da Section 04 — incentivar o estudante a completar o perfil.

**Independent Test**: Abrir `/para-estudantes` em viewport ≥992px com o bloco publicado e comparar estrutura (duas colunas + 3 itens + CTA) com o layout de referência.

**Acceptance Scenarios**:

1. **Given** o bloco Perfil em Destaque publicado com título, ilustração, 3 itens e CTA, **When** o visitante abre `/para-estudantes` em desktop, **Then** vê ilustração à esquerda e título + lista + botão à direita.
2. **Given** viewport desktop (lg+), **When** observa o layout, **Then** as duas colunas estão lado a lado (equivalente a `.col-lg-6` + `.col-lg-6`) e alinhadas verticalmente ao centro.
3. **Given** a seção renderizada, **When** observa cada item da lista, **Then** vê ícone (~20×20), título em destaque e descrição secundária, na ordem cadastrada.

---

### User Story 2 — Visitante tablet/mobile vê colunas empilhadas (Priority: P1)

Em viewport estreita as colunas empilham (ilustração acima, conteúdo abaixo); lista e CTA permanecem legíveis, sem scroll horizontal.

**Why this priority**: Tráfego mobile não pode perder a mensagem nem quebrar o layout.

**Independent Test**: Abrir `/para-estudantes` em viewports ≤575.98px e 768–991px.

**Acceptance Scenarios**:

1. **Given** viewport mobile estreita, **When** a página carrega, **Then** as colunas ocupam largura total (equivalente a `.col-12`) e empilham verticalmente.
2. **Given** qualquer viewport, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pelo bloco.
3. **Given** viewport mobile, **When** observa a ilustração, **Then** a imagem escala de forma responsiva (equivalente a `.img-fluid`) sem estourar o container.

---

### User Story 3 — Visitante aciona o CTA de completar perfil (Priority: P1)

O botão “Completar meu perfil” leva o visitante à rota de perfil do estudante (`/painel/estudante/perfil`). Visitante anônimo pode ser redirecionado pelo fluxo de autenticação já existente da plataforma (fora do escopo desta feature ajustar esse fluxo).

**Why this priority**: O CTA é o objetivo de conversão da seção.

**Independent Test**: Clicar no CTA em `/para-estudantes` e verificar o destino `/painel/estudante/perfil` (ou o gate de login já vigente).

**Acceptance Scenarios**:

1. **Given** o bloco com `field_link` apontando para `/painel/estudante/perfil`, **When** o visitante clica no CTA, **Then** navega para essa URL (ou para o fluxo de autenticação padrão se não autenticado).
2. **Given** o CTA renderizado, **When** observa o botão, **Then** o rótulo exibido corresponde ao título do link cadastrado (seed: “Completar meu perfil”).

---

### User Story 4 — Editor gerencia conteúdo sem código (Priority: P1)

Editor autenticado com permissão de blocos edita título, ilustração, até 3 itens (ícone/título/descrição), ordem e CTA; as mudanças refletem em `/para-estudantes` sem deploy de código.

**Why this priority**: Copy e artes de marketing mudam com frequência.

**Independent Test**: Editar o bloco no painel, salvar e recarregar `/para-estudantes`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre o bloco “Perfil em Destaque”, **Then** consegue editar título, ilustração, lista de até 3 itens e o link do CTA.
2. **Given** um bloco existente, **When** o editor altera textos/ícones/ordem/CTA e salva, **Then** a página pública reflete as mudanças após o cache esperado.
3. **Given** o formulário do bloco, **When** o editor tenta adicionar um 4º item, **Then** o sistema impede (cardinalidade 3).

---

### User Story 5 — Bloco aparece só em Para Estudantes, após a Jornada (Priority: P1)

O bloco não contamina home nem outras internas; aparece exclusivamente em `/para-estudantes`, na região de conteúdo full, visualmente **após** “Sua jornada até o sucesso”.

**Why this priority**: Evita regressão visual e garante a ordem de leitura (Hero → Benefícios → Jornada → Perfil em Destaque).

**Independent Test**: Comparar `/para-estudantes`, `<front>`, `/para-empresas` e `/quem-somos` após o deploy; confirmar ordem na página alvo.

**Acceptance Scenarios**:

1. **Given** deploy aplicado, **When** o visitante abre `/para-estudantes`, **Then** vê o bloco Perfil em Destaque na região `content_full`, imediatamente após Jornada do Estudante.
2. **Given** deploy aplicado, **When** o visitante abre a home, `/para-empresas` ou `/quem-somos`, **Then** o bloco `perfil_destaque_estudante` **não** é exibido.
3. **Given** a página `/para-estudantes` carregada, **When** observa a ordem vertical, **Then** hero → benefícios → jornada → perfil em destaque.

---

### User Story 6 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

Homologação/produção recebem tipos, fields, displays, instância seed, assets placeholder, placement e estilos apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/para-estudantes` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y` → `drush updb -y` → (2ª `cim` se placements dependerem de UUIDs seedados) → `drush cr`, **Then** o bloco aparece em `/para-estudantes` com título, ilustração, 3 itens e CTA seed (ou conteúdo editorial já existente).
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de tipos, blocos, paragraphs, arquivos ou placements (idempotência).
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** block type, paragraph type, field instances/storages, displays e block placement aparecem versionados em `config/sync`.

### Edge Cases

- Título da seção vazio → omitir o heading; não exibir heading vazio.
- Ilustração ausente → coluna esquerda vazia ou omitida sem erro de template; coluna direita permanece.
- Zero itens na lista → renderizar cabeçalho/ilustração/CTA (quando preenchidos), sem erro.
- Menos de 3 itens → lista renderiza os disponíveis.
- Tentativa de mais de 3 itens → impedida pela cardinalidade do field.
- Item sem ícone → título e descrição permanecem; não quebrar layout flex.
- Item sem título → ícone e descrição permanecem; não exibir heading vazio.
- Item sem descrição → ícone e título permanecem; não exibir parágrafo vazio.
- CTA (link) vazio → omitir o botão.
- Reexecução do hook → no-op seguro; **não** sobrescrever conteúdo editorial divergente do seed.
- Viewport intermediária → colunas empilham abaixo do breakpoint `lg`.
- Convivência com hero (`021`), benefícios (`022`), jornada (`023`) e View `vagas` → esta feature não os altera.

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de paragraph rotulado “Item de Lista com Ícone” com machine name `item_lista_icone_p`.
- **FR-002**: Cada item DEVE expor: ícone via storage reutilizado `field_image`; título via storage reutilizado `field_text_simple` (pedido verbal `field_text_simple_small` → canônico); descrição via storage reutilizado `field_text_simple_long`.
- **FR-003**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “Perfil em Destaque” com machine name `perfil_destaque_estudante`.
- **FR-004**: O bloco DEVE expor: ilustração via `field_image` (pedido verbal `field_imagem` → canônico `field_image` em `block_content`); título da seção via `field_text_simple`; lista ordenável via instance de `field_itens_lista` (Entity Reference Revisions → `item_lista_icone_p`, **cardinalidade 3**); CTA via `field_link`.
- **FR-005**: Todo o conteúdo exibido (título, ilustração, itens, ordem, CTA) DEVE ser gerenciável no painel; placeholders de template só para ausência de dado.
- **FR-006**: Displays de formulário e visualização do paragraph e do bloco DEVEM incluir os campos necessários para edição e renderização pública.
- **FR-007**: Alterações estruturais (tipos, fields, displays, placement) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação**

- **FR-008**: Todo o markup do bloco DEVE estar encapsulado sob classe dedicada de seção (ex.: `section-perfil-destaque`) e/ou bloco (ex.: `block-perfil-destaque-estudante`); estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.
- **FR-009**: O wrapper principal do conteúdo DEVE respeitar largura máxima de `1280px`, fundo `#FFFFFF` e paddings exatos Top/Bottom `64px`, Left/Right `40px` (tokens Figma da seção), aplicados via `.section-perfil-destaque`.
- **FR-010**: O layout DEVE usar grid de duas colunas equivalentes a `.row.align-items-center.g-5` com `.col-12.col-lg-6` (esquerda: ilustração; direita: textos/lista/CTA).
- **FR-011**: A ilustração DEVE ser responsiva (equivalente a `.img-fluid`) e centralizada na coluna.
- **FR-012**: O título DEVE ser um `h2`, família tipográfica Poppins, com largura máxima do container de texto de `528px` no desktop.
- **FR-013**: Cada item da lista DEVE ser renderizado em flex (equivalente a `.d-flex.gap-3.mb-4`), com ícone limitado rigorosamente a `20px × 20px` (`flex-shrink-0`), título em negrito/semibold na cor de destaque (`#9D4300` ou tom exato do check do Figma) e descrição na cor secundária `#45464D`.
- **FR-014**: O CTA DEVE renderizar `field_link` com aparência de botão (fundo azul escuro, texto branco, largura aproximada `208px`, altura `44px`, raio/borda alinhados ao design system do tema).

**Placement, seed e deploy**

- **FR-015**: O bloco DEVE ser posicionado na região `content_full` do tema `default`, com visibilidade por path limitada a `/para-estudantes`, com weight imediatamente após `default_jornadaestudante` (weight `1` → perfil tipicamente `2`).
- **FR-016**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11037`, tipicamente `custom_configs_update_11038`) DEVE, de forma **idempotente**, usando Entity API: garantir paragraph type + block type e field instances; garantir form/view displays; criar/seedar a instância do bloco com UUID fixo, ilustração placeholder, 3 paragraphs e CTA quando ausentes; garantir placement/visibilidade com weight após a jornada.
- **FR-017**: Assets de seed (ilustração + ícones dos 3 itens) DEVEM viver em `modules/custom/custom_configs/assets/` e ser copiados para `public://` pelo hook; **sem** commit de `sites/default/files`.
- **FR-018**: O seed DEVE preservar edições posteriores do editor; preencher apenas campos vazios/ausentes.
- **FR-019**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` (cim antes do updb; 2ª cim após updb para placements que dependem de UUIDs seedados); ao final do desenvolvimento na origem, `drush cex` para versionar configs geradas/alteradas.
- **FR-020**: A seção relevante do `PRD.md` (rota `/para-estudantes` / blocos) DEVE ser atualizada de forma cirúrgica refletindo o novo block type, fields e placement.

### Key Entities

- **`perfil_destaque_estudante` (block_content)**: seção de perfil em destaque — ilustração (`field_image`), título (`field_text_simple`), lista (`field_itens_lista` → `item_lista_icone_p`, cardinality 3), CTA (`field_link`).
- **`item_lista_icone_p` (paragraph)**: item da lista — ícone (`field_image`), título (`field_text_simple`), descrição (`field_text_simple_long`).
- **Bloco placement** (`block.block.*` no tema `default`): região `content_full`, pages `/para-estudantes`, weight após jornada, UUID alinhado ao seed.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a seção em `/para-estudantes` é reconhecível frente ao layout de referência (duas colunas + título + 3 itens com ícone 20×20 + CTA) em ≥95% dos critérios de checklist visual (paddings 64/40, max-width 1280px, ícones 20×20, CTA ~208×44).
- **SC-002**: Em viewport mobile, 100% das verificações confirmam colunas empilhadas em largura total; sem scroll horizontal causado pelo bloco.
- **SC-003**: Um editor atualiza título, itens e CTA do bloco em menos de 5 minutos, sem suporte de desenvolvimento.
- **SC-004**: Após o deploy padrão, `/para-estudantes` exibe o bloco imediatamente após Jornada do Estudante; home, `/para-empresas` e `/quem-somos` amostradas **não** o exibem.
- **SC-005**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cim -y` + `drush cr`, com zero passos manuais no painel.
- **SC-006**: Segunda execução de `drush updb` não cria tipos, blocos, paragraphs, arquivos ou placements duplicados (idempotência confirmada).
- **SC-007**: Com ilustração/itens/CTA vazios, a página não quebra; elementos ausentes são omitidos de forma segura.
- **SC-008**: Estilos do bloco não alteram visualmente outras seções (amostragem: hero estudantes, benefícios, jornada, “Como funciona” na home).
- **SC-009**: Clique no CTA leva a `/painel/estudante/perfil` (ou ao gate de autenticação já existente da plataforma).

## Assumptions

- **Campo curto**: o pedido `field_text_simple_small` mapeia para o storage canônico existente `field_text_simple` (mesmo padrão das features `004`–`023`); não se cria storage paralelo.
- **Ilustração do bloco**: o pedido `field_imagem` mapeia para o storage canônico existente `field_image` em `block_content`; não se cria `field_imagem` paralelo neste entity type.
- **Lista**: cria-se apenas nova **instance** de `field_itens_lista` no bundle `perfil_destaque_estudante` apontando para `item_lista_icone_p`, cardinality **3**.
- **CTA**: reutiliza storage `field_link` já existente em `block_content`.
- **Região `content_full`**: weight relativo imediatamente após `default_jornadaestudante` (weight `1`) → perfil tipicamente weight `2`.
- **Copy seed do título**: “Seu perfil em destaque”.
- **Copy seed do CTA**: título “Completar meu perfil”, URI `/painel/estudante/perfil` (rota canônica já listada no PRD).
- **Copy seed dos 3 itens** (completar a partir do briefing truncado; se o Figma trouxer texto exatamente diferente, o seed deve seguir o Figma no implement):
  1. **Mostre suas habilidades** — Vá além do currículo padrão e destaque competências, formação e diferenciais do seu perfil.
  2. **Seja encontrado pelas empresas** — Recrutadores buscam ativamente candidatos com perfil completo e atualizado.
  3. **Receba oportunidades relevantes** — Notificações personalizadas quando surgirem vagas alinhadas ao seu perfil.
- **Assets**: ilustração + 3 ícones versionados em `modules/custom/custom_configs/assets/perfil-destaque-estudante/`; hook copia para `public://` se ausentes.
- **Hook número**: próximo update após `custom_configs_update_11037` → `11038`, salvo se outro update for commitado antes da implementação.
- **UUID fixo**: a implementação define UUID estável compartilhado entre seed e `block.block.*` exportado (padrão das features anteriores).
- **Cor de destaque do título do item**: preferência `#9D4300`; usar tom exato do check do Figma se divergir.
- **Cor do CTA**: azul escuro alinhado ao design system do tema (ex. navy `#023C62` usado em outras seções), texto `#FFFFFF`.
- **Convivência**: hero (`021`), benefícios (`022`), jornada (`023`) e View `vagas` permanecem; esta feature apenas adiciona a Section 04.
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (PHP do `hook_update_N`, Twig do bloco e do paragraph, CSS/SCSS com tokens 64/40/20/208×44) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
