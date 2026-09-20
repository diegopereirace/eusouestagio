# Feature Specification: Quem Somos — Seção Missão e Visão (no Node)

**Feature Directory**: `specs/012-quem-somos-missao-visao`  
**Created**: 2026-09-20  
**Status**: Draft  
**Input do usuário**: Atualizar o tipo de conteúdo Node `quem_somos` removendo o layout legado de Missão/Visão/Valores (Anexo 2: imagem lateral + lista com ícones) e implementando o novo design full-width (Anexo 1: imagem de fundo com overlay escuro e duas colunas “Nossa Missão” | “Nossa Visão”). Campos diretamente no Node (sem Paragraphs), agrupados na administração via Field Group (`group_missao_visao`); reuso de storages existentes; migração idempotente via `hook_update_N`; template Twig + CSS; exportação `drush cex` → `config/sync`.

## Escopo

### Inclui

- **Remoção do legado (Anexo 2)** no bundle `quem_somos`:
  - Field Group administrativo `group_segundo_bloco` (“Segundo Bloco”).
  - Field instances exclusivas dessa seção: `field_titulo_2`, `field_text_long_formatted_2`, `field_imagem_2` (instâncias no bundle, displays de formulário/visualização e markup Twig associado).
  - Remoção da renderização da 2ª seção em duas colunas (imagem + título + texto formatado) no template da página.
- **Nova seção Missão e Visão no próprio Node** (Anexo 1), **sem Paragraphs**:
  - Field Group `group_missao_visao` (rótulo: “Seção Missão e Visão”) no formulário do Node.
  - Imagem de fundo: reutilizar storage `field_imagem_desktop` (equivalente canônico do pedido `field_image_desktop`; **não** criar `field_bg_imagem` nem `field_image` paralelo neste bundle — `field_imagem` permanece exclusivo da seção “Sobre nós”).
  - Texto da Missão: reutilizar storage `field_text_simple_long`.
  - Texto da Visão: reutilizar storage `field_text_simple_long_2`.
  - Anexar as field instances ao bundle `quem_somos` quando ainda ausentes; configurar `entity_form_display` e `entity_view_display` (modo `default`/`full` conforme existir) com o grupo e a visibilidade corretos.
- **Apresentação pública** em `/quem-somos`:
  - Atualizar o template do Node (override existente `node--quem-somos.html.twig`; suggestion `--full` apenas se o tema passar a exigir) removendo HTML do legado e renderizando a nova seção full-width fora do `.container` principal do corpo (ou com técnica equivalente que ocupe 100% da viewport).
  - Fundo com `background-image` da mídia cadastrada; overlay escuro; grid duas colunas iguais; tipografia Poppins, texto branco centralizado; títulos fixos “Nossa Missão” e “Nossa Visão”; divisória vertical sutil no desktop.
  - CSS/SCSS com escopo estrito sob classe dedicada da seção (ex.: `.quem-somos-missao-visao`), sem vazar estilos para outras páginas.
- **Deploy**: `hook_update_N` idempotente em `custom_configs.install` (próximo número livre após `11014`, tipicamente `11015`) que remove legado, anexa campos, configura Field Group/displays e garante renderização; ao final do desenvolvimento, `drush cex` para versionar em `config/sync`.
- **Convivência com a feature `011-bloco-missao-visao`**: esta feature **substitui** a abordagem Custom Block + Paragraphs. O bloco `missao_visao` **não** deve continuar aparecendo em `/quem-somos` (desabilitar/remover placement ou restringir visibilidade), evitando duplicidade visual Missão/Visão.
- Atualização cirúrgica do `PRD.md` (§ content type Quem somos / blocos) refletindo campos no Node e aposentadoria do bloco como fonte da seção.

### Fora

- Alteração do banner (`009-banner-quem-somos`) ou da seção “Sobre nós” (`010-layout-sobre-nos` — `field_titulo` / `field_text_long_formatted` / `field_imagem` e `group_primeiro_bloco` permanecem).
- Manutenção do conteúdo “Valores” (subitens com ícones do Anexo 2) — **fora do novo design**; não há campos novos para Valores.
- Uso de Paragraphs para Missão/Visão (proibido nesta feature).
- Criação de storages paralelos (`field_bg_imagem`, `field_image_desktop` com nome inglês se já existe `field_imagem_desktop`, etc.).
- Edição editorial dos textos finais além do seed/migração mínima necessária para validação visual.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê Missão e Visão no novo layout (Priority: P1)

Visitante acessa `/quem-somos` e, após a seção “Sobre nós”, vê uma faixa full-width com imagem de fundo escurecida e dois textos lado a lado: “Nossa Missão” e “Nossa Visão”, legíveis em branco, tipografia Poppins, alinhados ao Anexo 1 — **sem** a coluna com foto lateral nem a lista Missão/Visão/Valores com ícones do Anexo 2.

**Why this priority**: É o valor principal da mudança de marca na página institucional.

**Independent Test**: Abrir `/quem-somos` em viewport ≥768px e comparar com o Anexo 1 (estrutura, contraste, duas colunas, ausência do layout Anexo 2).

**Acceptance Scenarios**:

1. **Given** o Node `quem_somos` com imagem de fundo e textos de Missão e Visão preenchidos, **When** o visitante abre `/quem-somos` em desktop, **Then** vê a faixa full-width com fundo cobrindo a área, overlay escuro e as duas colunas (Missão | Visão) lado a lado.
2. **Given** a mesma página, **When** o visitante procura o layout legado (foto lateral + ícones de Missão/Visão/Valores), **Then** esse layout **não** aparece.
3. **Given** viewport desktop, **When** observa a divisão entre colunas, **Then** há separação visual sutil sem poluir o layout.

---

### User Story 2 — Visitante mobile vê empilhamento legível (Priority: P1)

Em viewport estreita, Missão e Visão empilham verticalmente, permanecem legíveis, e a faixa não causa scroll horizontal.

**Why this priority**: Tráfego mobile não pode perder a mensagem institucional.

**Independent Test**: Abrir `/quem-somos` em viewport ≤767.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** Missão e Visão aparecem empilhados, ambos legíveis.
2. **Given** viewport mobile, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pela seção.
3. **Given** viewport mobile, **When** observa a divisória entre colunas, **Then** a borda lateral de desktop não aparece (ou não interfere na leitura).

---

### User Story 3 — Editor gerencia a seção no formulário do Node (Priority: P1)

Editor autenticado edita o Node Quem Somos e encontra o grupo “Seção Missão e Visão” com imagem de fundo e os dois textos; não encontra mais o “Segundo Bloco” legado; alterações refletem na página pública sem deploy de código.

**Why this priority**: Conteúdo institucional muda com frequência e deve viver no mesmo Node da página.

**Independent Test**: Abrir o formulário de edição do Node `quem_somos`, alterar campos do grupo e recarregar `/quem-somos`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão de conteúdo, **When** abre o formulário do Node Quem Somos, **Then** vê o Field Group “Seção Missão e Visão” contendo imagem de fundo, texto da Missão e texto da Visão.
2. **Given** o mesmo formulário, **When** procura “Segundo Bloco” / campos `*_2` do legado, **Then** esses campos **não** estão mais disponíveis no bundle.
3. **Given** conteúdos preenchidos no grupo, **When** o editor salva e a página é recarregada, **Then** `/quem-somos` reflete as mudanças após o cache esperado do site.

---

### User Story 4 — Sem duplicidade com o bloco legado da feature 011 (Priority: P1)

A página não exibe duas faixas Missão/Visão (Node + Custom Block).

**Why this priority**: Evita regressão visual e confusão editorial após a mudança de arquitetura.

**Independent Test**: Abrir `/quem-somos` após o deploy e contar seções Missão/Visão.

**Acceptance Scenarios**:

1. **Given** deploy desta feature aplicado, **When** o visitante abre `/quem-somos`, **Then** existe **uma** faixa Missão/Visão (a do Node), e o bloco Custom Block `missao_visao` **não** é exibido nessa rota.
2. **Given** home e outras internas amostradas, **When** o visitante navega, **Then** a nova seção do Node **não** aparece fora de Quem Somos (ela só existe no conteúdo daquele Node).

---

### User Story 5 — Deploy reproduz estrutura em outro ambiente (Priority: P1)

Homologação/produção recebem remoção do legado, anexação dos campos, Field Group, displays e estilos apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/quem-somos` e o formulário do Node sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr`, **Then** o Node `quem_somos` possui o grupo Missão/Visão, o legado foi removido e a página pública renderiza o novo layout.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há erro nem recriação duplicada de fields/grupos (idempotência).
3. **Given** alterações estruturais no ambiente de origem, **When** `drush cex` é executado, **Then** field instances, form/view displays (com Field Group) e remoções do legado aparecem versionados em `config/sync`.

### Edge Cases

- Imagem de fundo ausente → manter altura mínima e overlay/cor de fallback escura; textos continuam legíveis.
- Apenas Missão **ou** apenas Visão preenchida → renderizar a coluna existente sem quebrar o grid; coluna vazia omitida ou sem conteúdo.
- Ambos textos vazios e sem imagem → omitir a seção inteira (sem faixa vazia com overlay).
- Texto longo → quebra natural; não truncar no meio de palavras.
- Reexecução do hook → no-op seguro se legado já removido e campos/grupo já presentes; **não** sobrescrever conteúdo editorial já preenchido nos novos campos.
- Dados editoriais do legado (`*_2`) → após remoção das instances, dados deixam de ser editáveis; se houver necessidade de copiar copy antigo para os novos campos, o hook pode migrar **uma vez** apenas quando os novos campos estiverem vazios (opcional, documentado em Assumptions).
- Viewport intermediária (~768px) → bipartição Bootstrap `md`; abaixo, empilha.
- Seção “Sobre nós” → permanece intacta acima da nova faixa.

## Requirements *(obrigatório)*

### Functional Requirements

**Limpeza do legado**

- **FR-001**: O sistema DEVE remover do bundle `quem_somos` as field instances `field_titulo_2`, `field_text_long_formatted_2` e `field_imagem_2`, incluindo dependências em `entity_form_display` e `entity_view_display`.
- **FR-002**: O Field Group `group_segundo_bloco` DEVE ser removido do formulário do Node `quem_somos`.
- **FR-003**: O template público DEVE deixar de renderizar a 2ª seção legado (layout imagem lateral + título + corpo do Anexo 2).
- **FR-004**: Storages globais dos campos `*_2` SÓ DEVEM ser excluídos se nenhuma outra entidade os utilizar; caso contrário, apenas as instances do bundle `quem_somos` são removidas.

**Nova estrutura no Node**

- **FR-005**: O sistema DEVE anexar ao bundle `quem_somos` (se ainda não anexados) as instances reutilizando storages: `field_imagem_desktop` (imagem de fundo), `field_text_simple_long` (texto Missão), `field_text_simple_long_2` (texto Visão). **Não** usar Paragraphs.
- **FR-006**: O formulário do Node DEVE exibir o Field Group `group_missao_visao` com rótulo “Seção Missão e Visão”, contendo os três campos acima.
- **FR-007**: Os displays de formulário e visualização DEVEM incluir os novos campos com visibilidade adequada para edição e para o modo de visualização usado na página completa.
- **FR-008**: Títulos públicos “Nossa Missão” e “Nossa Visão” DEVEM aparecer na apresentação; os campos de texto armazenam apenas o corpo (não é obrigatório expor campos de título editáveis nesta feature).
- **FR-009**: Alterações estruturais DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação**

- **FR-010**: A seção Missão/Visão DEVE ser full-width (100% da largura da viewport), com altura mínima aproximada de 380px.
- **FR-011**: A URL da imagem (`field_imagem_desktop`) DEVE ser aplicada como `background-image` no wrapper; estilos de cobertura: `background-size: cover; background-position: center; min-height: 380px; position: relative`.
- **FR-012**: Um overlay escuro translúcido DEVE cobrir a imagem (ex.: pseudo-elemento com `rgba(15, 23, 42, 0.7)`), e o conteúdo textual DEVE ficar acima do overlay (`position: relative; z-index: 1`).
- **FR-013**: Em viewport ≥768px, os textos DEVEM usar `.container` + `.row` + duas colunas iguais (`.col-md-6`), centralizados (`.text-center`), brancos (`.text-white`), tipografia Poppins; títulos em peso Bold/SemiBold.
- **FR-014**: No desktop, DEVE haver divisória vertical sutil entre as colunas (ex.: borda na primeira coluna), desativada no mobile.
- **FR-015**: Em viewport mobile, as colunas DEVEM empilhar sem overflow horizontal.
- **FR-016**: Estilos novos/alterados DEVEM aplicar-se somente sob o escopo da seção Missão/Visão do Node Quem Somos.

**Deploy e convivência**

- **FR-017**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11014`, tipicamente `11015`) DEVE, de forma **idempotente**: remover legado (FR-001–002); anexar campos e configurar Field Group/displays (FR-005–007); garantir que o bloco `missao_visao` da feature 011 **não** continue visível em `/quem-somos`.
- **FR-018**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` (cim antes do updb); ao final do desenvolvimento, `drush cex` para versionar configs.
- **FR-019**: A seção relevante do `PRD.md` DEVE ser atualizada de forma cirúrgica refletindo campos no Node e a substituição da abordagem de bloco da feature 011 para esta seção.

### Key Entities

- **Node `quem_somos`**: página institucional; mantém seção “Sobre nós” (`group_primeiro_bloco`); passa a carregar Missão/Visão via `group_missao_visao`.
- **`group_missao_visao` (Field Group)**: agrupamento administrativo — `field_imagem_desktop`, `field_text_simple_long`, `field_text_simple_long_2`.
- **Legado removido**: `group_segundo_bloco` + `field_titulo_2` + `field_text_long_formatted_2` + `field_imagem_2` (instances no bundle).
- **Bloco `missao_visao` (feature 011)**: deixa de ser a fonte pública desta seção em `/quem-somos` (placement/visibilidade ajustados).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a seção em `/quem-somos` é reconhecível frente ao Anexo 1 (full-width, overlay, duas colunas, tipografia branca) em ≥95% dos critérios de checklist visual.
- **SC-002**: Em 100% das verificações de aceite, o layout Anexo 2 (foto lateral + Missão/Visão/Valores com ícones) **não** aparece em `/quem-somos`.
- **SC-003**: Em viewport mobile, 100% das verificações confirmam empilhamento legível e ausência de scroll horizontal causado pela seção.
- **SC-004**: Um editor atualiza imagem e textos da seção no formulário do Node em menos de 5 minutos, sem suporte de desenvolvimento.
- **SC-005**: Após o deploy padrão, `/quem-somos` exibe **uma** faixa Missão/Visão (Node); home e demais internas amostradas não exibem essa faixa.
- **SC-006**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cr`, com zero passos manuais no painel.
- **SC-007**: Segunda execução de `drush updb` não duplica fields/grupos nem reintroduz o legado (idempotência confirmada).
- **SC-008**: Com imagem ausente, textos permanecem legíveis; com ambos textos vazios (e sem imagem), a seção é omitida sem quebrar a página.
- **SC-009**: Estilos da seção não alteram visualmente “Sobre nós”, banner Quem Somos nem a home (amostragem de regressão).

## Assumptions

- O “Segundo Bloco” atual (`field_titulo_2` / `field_text_long_formatted_2` / `field_imagem_2` + markup Twig em duas colunas) **é** o legado visual do Anexo 2 a remover.
- O pedido verbal `field_image_desktop` mapeia para o storage canônico existente `field_imagem_desktop`; não se cria `field_bg_imagem` nesta feature.
- Títulos “Nossa Missão” / “Nossa Visão” são fixos na apresentação (Anexo 1); apenas corpos e imagem são editáveis.
- “Valores” do Anexo 2 não entram no novo design; não há requisito de preservar ou migrar subitens de Valores.
- Esta feature **substitui** a entrega pública da `011-bloco-missao-visao` em `/quem-somos`; o bloco pode permanecer no sistema, mas **não** deve renderizar nessa rota após o update.
- Migração opcional de copy: se `field_text_long_formatted_2` contiver texto útil e os novos campos estiverem vazios, o hook pode popular uma vez `field_text_simple_long` / `field_text_simple_long_2` com texto plain (strip tags) — sem obrigar fidelidade 100% ao HTML legado.
- Textos seed sugeridos (editáveis depois), alinhados ao Anexo 1:
  - Missão: “Desenvolver estagiários a partir do autoconhecimento, conectando suas habilidades e competências às oportunidades certas, para gerar performance, realização profissional e resultados consistentes para as empresas.”
  - Visão: “Ser referência na formação e gestão de estagiários no Brasil, reconhecida por transformar potencial em performance e por construir conexões assertivas entre talentos e organizações.”
- Hook número: próximo update após `custom_configs_update_11014` → `11015`, salvo se outro update for commitado antes da implementação.
- Template alvo: o override já existente `themes/custom/default/templates/content/node--quem-somos.html.twig` (suggestion `--full` só se necessário na implementação).
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação (PHP do `hook_update_N`, Twig, SCSS/CSS) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
