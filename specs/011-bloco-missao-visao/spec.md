# Feature Specification: Bloco Missão e Visão

**Feature Directory**: `specs/011-bloco-missao-visao`  
**Created**: 2026-09-16  
**Status**: Draft  
**Input do usuário**: Implementar o bloco institucional **Missão e Visão** na página Quem Somos (`/quem-somos`), full-width com imagem de fundo, overlay escuro e dois textos lado a lado (Missão | Visão), conforme layout Figma. Modelagem via Custom Block + Paragraphs reutilizando storages canônicos do projeto; seed e placement automatizados com `hook_update_N` idempotente; exportação via `drush cex` → `config/sync`; template Twig + CSS com escopo estrito `.block-missao-visao`.

## Escopo

### Inclui

- Tipo de paragraph **Item Missão/Visão** (`missao_visao_item_p`) com título curto e texto descritivo longos gerenciáveis.
- Tipo de bloco customizado **Missão e Visão** (`missao_visao`) com imagem de fundo e lista de até 2 itens (paragraphs).
- Reutilização obrigatória de field storages existentes: `field_text_simple` (título), `field_text_simple_long` (descrição), `field_image` (fundo); lista via novo storage de referência a paragraphs `field_itens_lista` (Entity Reference Revisions → `missao_visao_item_p`, cardinality 2), pois não há storage equivalente reutilizável com esse propósito.
- Displays de formulário e visualização (form/view) para o paragraph e o block type.
- Seed idempotente: uma instância do bloco preenchida com textos padrão (Missão e Visão) e imagem placeholder versionada no módulo.
- Placement na região `content_full` do tema `default`, visível **somente** em `/quem-somos`.
- Template Twig específico do bloco (ex.: `block--block-content--missao-visao.html.twig`) iterando os paragraphs.
- Layout full-width (~380px de altura mínima), imagem de fundo cobrindo a área, overlay azul/escuro translúcido, grid Bootstrap 5 (duas colunas `col-md-6`), tipografia Poppins, texto branco centralizado; divisória sutil entre colunas no desktop.
- CSS/SCSS com escopo estrito sob `.block-missao-visao` (incluindo `::before` do overlay e `min-height`).
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11013`, tipicamente `11014`) garantindo tipos, fields, displays, seed e placement.
- Exportação estrutural via `drush cex` para `config/sync`.
- Atualização cirúrgica do `PRD.md` (§3.6 blocos / página Quem somos) refletindo o novo block type e placement.

### Fora

- Redesign do banner (`009-banner-quem-somos`) ou da seção “Sobre nós” (`010-layout-sobre-nos`).
- Remoção, ocultação ou redesign da 2ª seção do node `quem_somos` (`field_titulo_2` / `field_text_long_formatted_2` / `field_imagem_2`) — permanece como está; convivência visual é responsabilidade editorial/ops fora desta feature, salvo ajuste futuro explícito.
- Criação de field storage `field_text_simple_small` (não existe no projeto; o canônico é `field_text_simple`).
- Layout Builder / edição visual de colunas pelo editor.
- Mais de 2 itens Missão/Visão no mesmo bloco (cardinality máxima = 2).
- Exibição do bloco em home ou outras rotas.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` + `/speckit-tasks` + `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê Missão e Visão em Quem Somos (Priority: P1)

Visitante acessa `/quem-somos` e, abaixo do conteúdo principal da página, vê uma faixa full-width com imagem de fundo escurecida, e dois textos lado a lado: “Nossa Missão” e “Nossa Visão”, com descrições legíveis em branco, tipografia Poppins, alinhados ao layout Figma.

**Why this priority**: É o valor principal da feature — comunicar propósito e direção da marca na página institucional.

**Independent Test**: Abrir `/quem-somos` em viewport ≥768px com o bloco publicado e comparar com o print Figma (estrutura, tipografia, contraste, duas colunas).

**Acceptance Scenarios**:

1. **Given** o bloco Missão e Visão publicado com imagem e dois itens, **When** o visitante abre `/quem-somos` em desktop, **Then** vê a faixa full-width com fundo cobrindo a área, overlay escuro e as duas colunas (Missão | Visão) lado a lado.
2. **Given** o bloco renderizado, **When** o visitante lê o conteúdo, **Then** vê os títulos e textos cadastrados em branco, centralizados, com contraste suficiente sobre o overlay.
3. **Given** viewport desktop, **When** observa a divisão entre colunas, **Then** há separação visual sutil (ex.: borda na primeira coluna) sem poluir o layout.

---

### User Story 2 — Visitante mobile vê empilhamento legível (Priority: P1)

Em viewport estreita, Missão e Visão empilham verticalmente, permanecem legíveis, e a faixa não causa scroll horizontal nem texto ilegível sobre a imagem.

**Why this priority**: Tráfego mobile não pode perder a mensagem institucional.

**Independent Test**: Abrir `/quem-somos` em viewport ≤767.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** os itens Missão e Visão aparecem empilhados (um abaixo do outro), ambos legíveis.
2. **Given** viewport mobile, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pelo bloco.
3. **Given** viewport mobile, **When** observa a divisória entre colunas, **Then** a borda lateral de desktop não aparece (ou não interfere na leitura).

---

### User Story 3 — Editor gerencia conteúdo sem código (Priority: P1)

Editor autenticaido com permissão de blocos edita títulos, textos, imagem de fundo e a ordem dos dois itens; as mudanças refletem em `/quem-somos` sem deploy de código.

**Why this priority**: Conteúdo institucional muda com frequência; não pode depender de desenvolvimento.

**Independent Test**: Editar o bloco no painel, salvar e recarregar `/quem-somos`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre o bloco “Missão e Visão”, **Then** consegue editar imagem de fundo e até 2 itens (cada um com título curto e texto longo).
2. **Given** um bloco existente, **When** o editor altera textos ou troca a imagem e salva, **Then** a página pública reflete as mudanças após o cache esperado do site.
3. **Given** o formulário do item, **When** o editor tenta adicionar um 3º paragraph, **Then** o sistema impede (cardinality máxima = 2).

---

### User Story 4 — Bloco aparece só em Quem Somos (Priority: P1)

O bloco não contamina home nem outras internas; aparece exclusivamente em `/quem-somos`.

**Why this priority**: Evita regressão visual em outras páginas.

**Independent Test**: Comparar `/quem-somos`, `<front>`, `/para-estudantes` e `/contato` após o deploy.

**Acceptance Scenarios**:

1. **Given** deploy aplicado, **When** o visitante abre `/quem-somos`, **Then** vê o bloco Missão e Visão na faixa full-width abaixo do conteúdo principal.
2. **Given** deploy aplicado, **When** o visitante abre a home ou outra interna sem o path `/quem-somos`, **Then** o bloco **não** é exibido.

---

### User Story 5 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

Homologação/produção recebem tipos, fields, displays, instância seed, placement e estilos apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/quem-somos` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr`, **Then** o bloco aparece em `/quem-somos` com os dois itens seed (ou conteúdo editorial já existente) e a imagem de fundo.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de tipos, blocos, paragraphs ou placements (idempotência).
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** block type, paragraph type, field instances/storages, displays e block placement aparecem versionados em `config/sync`.

### Edge Cases

- Zero itens na lista → omitir a grade de textos (ou o bloco inteiro se também não houver imagem), sem erro de template.
- Apenas 1 item → renderizar uma coluna; a outra permanece vazia/omitida sem quebrar o grid.
- Imagem de fundo ausente → manter overlay/altura mínima com cor de fallback escura; textos continuam legíveis.
- Texto longo → quebra natural; não truncar por CSS de forma que corte palavras no meio.
- Reexecução do hook → no-op seguro se tipos/bloco/placement/seed já existirem; **não** sobrescrever conteúdo editorial divergente do seed.
- Conteúdo editorial já alterado (textos/imagem) → hook preserva o que o editor salvou; preenche apenas campos vazios/ausentes.
- Viewport intermediária (~768px) → grid Bootstrap `md` aplica a bipartição; abaixo, empilha.
- Convivência com a 2ª seção do node `quem_somos` → ambas podem aparecer na página; esta feature não as sincroniza.

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de paragraph rotulado “Item Missão/Visão” com machine name `missao_visao_item_p`.
- **FR-002**: Cada item DEVE expor título via storage reutilizado `field_text_simple` e descrição via storage reutilizado `field_text_simple_long` (o pedido verbal `field_text_simple_small` mapeia para o canônico `field_text_simple`; **não** criar storage paralelo).
- **FR-003**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “Missão e Visão” com machine name `missao_visao`.
- **FR-004**: O bloco DEVE expor: imagem de fundo via storage reutilizado `field_image` e lista ordenável de itens via `field_itens_lista` (Entity Reference Revisions → `missao_visao_item_p`, **cardinality máxima 2**).
- **FR-005**: Todo o conteúdo exibido (títulos, textos, imagem, ordem dos itens) DEVE ser gerenciável no painel; placeholders de template só para ausência de dado.
- **FR-006**: Displays de formulário e visualização do paragraph e do bloco DEVEM incluir os campos necessários para edição e renderização pública.
- **FR-007**: Alterações estruturais (tipos, fields, displays, placement) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação**

- **FR-008**: Todo o markup do bloco DEVE estar encapsulado sob a classe `block-missao-visao`; estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.
- **FR-009**: O bloco DEVE ser full-width, com altura mínima aproximada de 380px (`min-height: 380px`).
- **FR-010**: A imagem de fundo (`field_image`) DEVE cobrir toda a área do wrapper (`background-size: cover; background-position: center`), via estilo inline ou classe dinâmica gerada no Twig a partir da URL da mídia.
- **FR-011**: Um overlay azul/escuro translúcido DEVE sobrepor a imagem via pseudo-elemento `::before` no wrapper principal, garantindo legibilidade do texto branco.
- **FR-012**: Em viewport ≥768px, os itens DEVEM usar `.container` + `.row` + `.col-md-6` (dois itens lado a lado), com padding lateral adequado (ex.: `.px-4` / `.px-lg-5`) para proporção próxima a ~520px por coluna de texto.
- **FR-013**: Textos DEVEM estar centralizados (`.text-center`) e brancos (`.text-white`), com família tipográfica Poppins; títulos com peso maior (Bold/SemiBold) e parágrafos com peso menor (Regular/Light).
- **FR-014**: No desktop, DEVE haver divisória visual sutil entre as duas colunas (ex.: `border-end` na primeira coluna), desativada no mobile.
- **FR-015**: Em viewport mobile, os itens DEVEM empilhar sem overflow horizontal.

**Placement, seed e deploy**

- **FR-016**: O bloco DEVE ser posicionado na região `content_full` do tema `default`, com visibilidade por path limitada a `/quem-somos` (e, se necessário para o alias, variantes equivalentes já usadas no projeto).
- **FR-017**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11013`, tipicamente `custom_configs_update_11014`) DEVE, de forma **idempotente**: garantir paragraph type + block type e field instances; garantir form/view displays; criar/seedar a instância do bloco com UUID fixo, dois paragraphs (Missão e Visão) e imagem placeholder quando ausentes; garantir placement/visibilidade.
- **FR-018**: O seed DEVE usar asset versionado em `modules/custom/custom_configs/assets/` (imagem placeholder de fundo) e textos padrão documentados em Assumptions; uploads/edições posteriores do editor DEVEM ser preservados.
- **FR-019**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` (cim antes do updb); ao final do processo de desenvolvimento, `drush cex` para versionar configs geradas/alteradas.
- **FR-020**: A seção relevante do `PRD.md` (blocos / página Quem somos) DEVE ser atualizada de forma cirúrgica refletindo o novo block type, fields e placement.

### Key Entities

- **`missao_visao` (block_content)**: seção institucional — imagem de fundo (`field_image`), lista (`field_itens_lista`, max 2).
- **`missao_visao_item_p` (paragraph)**: item Missão ou Visão — título (`field_text_simple`), descrição (`field_text_simple_long`).
- **Bloco placement** (`block.block.*` no tema `default`): região `content_full`, pages `/quem-somos`, UUID alinhado ao seed.
- **Asset seed**: imagem de fundo placeholder versionada sob `modules/custom/custom_configs/assets/` (caminho exato na implementação).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, o bloco em `/quem-somos` é reconhecível frente ao layout Figma (full-width, overlay, duas colunas, tipografia branca Poppins) em ≥95% dos critérios de checklist visual.
- **SC-002**: Em viewport mobile, 100% das verificações confirmam empilhamento legível e ausência de scroll horizontal causado pelo bloco.
- **SC-003**: Um editor atualiza títulos, textos e imagem do bloco em menos de 5 minutos, sem suporte de desenvolvimento.
- **SC-004**: Após o deploy padrão, `/quem-somos` exibe o bloco; home e demais internas amostradas **não** o exibem.
- **SC-005**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cr`, com zero passos manuais no painel.
- **SC-006**: Segunda execução de `drush updb` não cria tipos, blocos, paragraphs ou placements duplicados (idempotência confirmada).
- **SC-007**: Com imagem ausente, textos permanecem legíveis (fallback de fundo/overlay); com zero itens, a página não quebra.
- **SC-008**: Estilos do bloco não alteram visualmente outras seções (amostragem: home, banner Quem Somos, seção “Sobre nós”).

## Assumptions

- **Campo de título**: o pedido `field_text_simple_small` mapeia para o storage canônico existente `field_text_simple` (mesmo padrão das features `004`–`007`); não se cria storage paralelo.
- **Lista `field_itens_lista`**: não existe storage reutilizável com esse propósito; cria-se storage/instance novos apontando para `missao_visao_item_p`, cardinality 2.
- **Região `content_full`**: no tema `default`, essa região renderiza **abaixo** do `page.content` — posição adequada para Missão/Visão após “Sobre nós” e demais campos do node.
- **Textos seed padrão** (editáveis depois no painel):
  - Item 1 — título: “Nossa Missão”; texto: “Conectar empresas e estudantes por meio de experiências de estágio que geram aprendizado real, crescimento profissional e resultados sustentáveis para todos os envolvidos.”
  - Item 2 — título: “Nossa Visão”; texto: “Ser referência nacional em estágio, reconhecida pela qualidade da seleção, pela gestão responsável e pelo desenvolvimento contínuo de talentos.”
- **Imagem placeholder**: arte genérica institucional (foto/ambiente de trabalho ou textura alinhada à marca) versionada no módulo; o editor substitui pela arte final do Figma quando disponível.
- **Hook número**: próximo update após `custom_configs_update_11013` → `11014`, salvo se outro update for commitado antes da implementação.
- **UUID fixo**: a implementação define UUID estável compartilhado entre seed e `block.block.*` exportado (padrão das features anteriores).
- **2ª seção do node**: permanece intacta; se houver sobreposição semântica com Missão/Visão, o ajuste editorial (esvaziar/ocultar `*_2`) fica fora desta feature.
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (PHP do `hook_update_N`, Twig completo, SCSS/CSS do overlay) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
