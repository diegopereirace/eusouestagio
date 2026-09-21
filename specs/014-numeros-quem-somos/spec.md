# Feature Specification: Números / Estatísticas Quem Somos (Impact in Numbers)

**Feature Directory**: `specs/014-numeros-quem-somos`  
**Created**: 2026-09-21  
**Status**: Draft  
**Input do usuário**: Seção **Números/Estatísticas (“Impact in Numbers”)** na página Quem Somos — Paragraph gerenciável no Node `quem_somos`, seed tipográfico fiel ao design (4 itens), deploy automatizado via Entity API + `hook_update_N` idempotente, template Twig com grid Bootstrap 5 e CSS com tokens exatos do Figma (fundo `#0F172A`, paddings `64px`/`40px`, max-width `1280px`, tipografia Poppins, laranja `#FD7B1A`, subtexto branco com opacidade 80%, gap `8px`).

## Escopo

### Inclui

- Tipo de paragraph **Número Destaque** (`numero_destaque_p`, sufixo `_p` obrigatório) com dois textos gerenciáveis:
  - Destaque (ex.: “20k+”) via storage reutilizado `field_text_simple` (pedido verbal `field_text_simple_small` → canônico existente; **não** criar storage paralelo).
  - Subtexto (ex.: “ESTUDANTES ATIVOS”) via storage reutilizado `field_text_simple_long` (alternativa canônica ao pedido `field_text_simple_small_2` / `field_text_simple_long`; **não** criar `field_text_simple_small_2`).
- Campo Entity Reference Revisions no Node bundle `quem_somos`: **`field_numeros_lista`** → `numero_destaque_p`, **cardinality exatamente 4**.
- Displays de formulário e visualização (form/view) do paragraph e do Node (modo usado pela página `/quem-somos`, tipicamente `default`/`full`).
- Seed idempotente no Node `quem_somos` com **exatamente 4** paragraphs e copy fixa do design:
  1. Destaque `20k+` | Subtexto `ESTUDANTES ATIVOS`
  2. Destaque `1.2k+` | Subtexto `EMPRESAS PARCEIRAS`
  3. Destaque `8k+` | Subtexto `ESTÁGIOS INICIADOS`
  4. Destaque `95%` | Subtexto `SATISFAÇÃO GLOBAL`
- Apresentação pública em `/quem-somos`: atualizar o template do Node (override existente `node--quem-somos.html.twig`; suggestion `--full` apenas se o tema passar a exigir) renderizando a seção com classe dedicada (ex.: `.section-impact-numbers`).
- Layout: container da seção com tokens Figma; grid responsivo 2 colunas no mobile / 4 no desktop (equivalente Bootstrap `.row` > `.col-6.col-md-3`); item em fluxo vertical com gap de `8px` entre destaque e subtexto.
- CSS/SCSS com escopo estrito sob `.section-impact-numbers` (e filhos) aplicando paddings, cores, tipografia, alturas de caixa e opacidade do Figma.
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11016`, tipicamente `11017`) garantindo tipos/fields/displays e seed via Entity API (`\Drupal::entityTypeManager()`).
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento.
- Atualização cirúrgica do `PRD.md` (§ content type Quem somos) refletindo o paragraph, o campo `field_numeros_lista` e a seção Impact in Numbers.

### Fora

- Alteração do banner (`009`), seção “Sobre nós” (`010`), Missão/Visão no Node (`012`) ou bloco Diferenciais Quem Somos (`013`).
- Título/subtítulo editoriais da seção além dos 4 números (o design desta fase é só a faixa de estatísticas).
- Layout Builder / edição visual de colunas pelo editor.
- Criação de storages paralelos `field_text_simple_small` / `field_text_simple_small_2`.
- Reuso forçado de `field_itens_p` (cardinality 3 no Node — incompatível com o limite de 4 itens desta seção).
- Exibição da seção fora de `/quem-somos`.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê Impact in Numbers em Quem Somos (Priority: P1)

Visitante acessa `/quem-somos` e vê uma faixa escura com quatro estatísticas lado a lado (desktop): número em destaque laranja e subtexto em caixa alta abaixo, alinhados ao layout “Impact in Numbers” do Figma.

**Why this priority**: É o valor principal da feature — credibilidade institucional por números mensuráveis.

**Independent Test**: Abrir `/quem-somos` em viewport ≥768px com o Node populado e comparar fundo, tipografia, ordem e copy dos quatro itens com o design de referência.

**Acceptance Scenarios**:

1. **Given** o Node `quem_somos` com 4 itens em `field_numeros_lista`, **When** o visitante abre `/quem-somos` em desktop, **Then** vê a seção de fundo escuro com os quatro números na ordem cadastrada.
2. **Given** a seção renderizada, **When** observa um item, **Then** o destaque aparece em laranja (Poppins negrito) e o subtexto em branco com opacidade reduzida, em caixa alta, abaixo do número.
3. **Given** viewport desktop (md+), **When** observa o grid, **Then** há 4 itens em uma única linha.

---

### User Story 2 — Visitante mobile vê 2 itens por linha (Priority: P1)

Em viewport estreita, o grid exibe 2 itens por linha, permanece legível e não causa scroll horizontal.

**Why this priority**: Tráfego mobile não pode perder a mensagem nem quebrar o layout.

**Independent Test**: Abrir `/quem-somos` em viewport ≤575.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** o grid exibe 2 itens por linha.
2. **Given** viewport mobile, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pela seção.
3. **Given** a seção em qualquer viewport, **When** mede o espaçamento entre número e subtexto, **Then** o gap visual é de 8px (±1px de arredondamento).

---

### User Story 3 — Editor gerencia os quatro números no Node (Priority: P1)

Editor autenticado edita o Node Quem Somos, encontra a lista de números (máximo 4 itens) e altera destaque/subtexto; as mudanças refletem em `/quem-somos` sem deploy de código.

**Why this priority**: Métricas institucionais mudam; não podem depender de desenvolvimento após o seed.

**Independent Test**: Editar o Node no painel, salvar e recarregar `/quem-somos`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão de editar `quem_somos`, **When** abre o formulário do Node, **Then** consegue gerenciar até 4 paragraphs “Número Destaque”, cada um com destaque e subtexto.
2. **Given** a lista já com 4 itens, **When** tenta adicionar um quinto, **Then** a interface impede (cardinality 4).
3. **Given** um item existente, **When** o editor altera destaque ou subtexto e salva, **Then** a página pública reflete as mudanças após o cache esperado do site.

---

### User Story 4 — Deploy reproduz estrutura e seed em outro ambiente (Priority: P1)

Homologação/produção recebem paragraph type, field no Node, displays, seed dos 4 itens e estilos apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/quem-somos` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr`, **Then** `/quem-somos` exibe a seção com os 4 números do seed (ou conteúdo editorial já existente).
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de paragraphs nem sobrescrita de copy editorial divergente do seed.
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** paragraph type, field storage/instance `field_numeros_lista`, displays e anexos ao bundle `quem_somos` aparecem versionados em `config/sync`.

### Edge Cases

- Lista vazia (0 itens) → omitir a seção inteira; não renderizar faixa vazia nem quebrar o template.
- Menos de 4 itens → renderizar apenas os existentes; grid não quebra.
- Item com destaque vazio e subtexto preenchido (ou o inverso) → omitir o elemento vazio; manter o preenchido legível.
- Ambos vazios em um item → não renderizar o item (ou célula vazia sem texto).
- Reexecução do hook → no-op seguro; **não** sobrescrever conteúdo editorial divergente do seed; popular apenas se a lista estiver vazia/ausente.
- Viewport intermediária → manter 2 colunas até o breakpoint md; 4 colunas a partir de md.
- Convivência com demais seções de Quem Somos (banner, Sobre nós, Missão/Visão, Diferenciais) → esta feature não as altera.

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O sistema DEVE oferecer um tipo de paragraph rotulado “Número Destaque” com machine name `numero_destaque_p`.
- **FR-002**: Cada item DEVE expor destaque via storage reutilizado `field_text_simple` e subtexto via storage reutilizado `field_text_simple_long` (pedidos verbais `field_text_simple_small` / `field_text_simple_small_2` mapeiam para esses canônicos; **não** criar storages paralelos).
- **FR-003**: O bundle Node `quem_somos` DEVE expor a lista `field_numeros_lista` (Entity Reference Revisions → `numero_destaque_p`) com **cardinality exatamente 4**.
- **FR-004**: Todo o conteúdo exibido (destaques, subtextos, ordem dos itens) DEVE ser gerenciável no painel do Node; placeholders de template só para ausência de dado.
- **FR-005**: Displays de formulário e visualização do paragraph e do Node DEVEM incluir `field_numeros_lista` / campos do item para edição e renderização pública.
- **FR-006**: Alterações estruturais (paragraph type, field storage/instance, displays) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação (tokens Figma — Section 6: Impact in Numbers)**

- **FR-007**: Todo o markup da seção DEVE estar encapsulado sob classe dedicada (ex.: `.section-impact-numbers`); estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.
- **FR-008**: O container da seção DEVE usar fundo `#0F172A`, padding vertical `64px` e padding horizontal `40px` (valores exatos; classe customizada, não apenas utilitários genéricos).
- **FR-009**: O conteúdo interno DEVE respeitar largura máxima de `1280px` (centralizado na viewport).
- **FR-010**: Os itens DEVEM ser renderizados em grid responsivo equivalente a `.row` > `.col-6.col-md-3` (2 por linha no mobile; 4 por linha a partir de md).
- **FR-011**: Cada item DEVE alinhar conteúdo ao centro em coluna flex com gap de `8px` entre destaque e subtexto (equivalente a `.d-flex.flex-column.align-items-center.gap-2` ou CSS customizado equivalente).
- **FR-012**: O texto de destaque DEVE usar família Poppins, `font-weight: 700`, cor `#FD7B1A`, com altura de caixa alvo de `56px` (font-size/line-height ajustados para caber nessa altura).
- **FR-013**: O subtexto DEVE usar cor `#FFFFFF` com `opacity: 0.8`, `text-transform: uppercase`, altura de caixa alvo de `20px`, tipografia compatível com essa altura.

**Seed e deploy**

- **FR-014**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11016`, tipicamente `custom_configs_update_11017`) DEVE, de forma **idempotente**, usando Entity API (`\Drupal::entityTypeManager()`): garantir paragraph type + field instances; anexar `field_numeros_lista` ao bundle `quem_somos` e atualizar form/view displays; localizar o Node `quem_somos`; criar e anexar os 4 paragraphs com a copy do design **somente se** a lista estiver vazia/ausente.
- **FR-015**: O seed DEVE preservar edições posteriores do editor; preencher apenas campos/lista vazios; **não** duplicar paragraphs em reexecução.
- **FR-016**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` (cim antes do updb); ao final do desenvolvimento, `drush cex` para versionar configs geradas/alteradas.
- **FR-017**: A seção relevante do `PRD.md` (content type Quem somos) DEVE ser atualizada de forma cirúrgica refletindo `numero_destaque_p`, `field_numeros_lista` e a seção Impact in Numbers.

### Key Entities

- **`numero_destaque_p` (paragraph)**: item de estatística — destaque (`field_text_simple`), subtexto (`field_text_simple_long`).
- **`quem_somos` (node)**: página institucional — lista `field_numeros_lista` (ERR → `numero_destaque_p`, cardinality 4), além dos campos já existentes de Sobre nós / Missão-Visão (intocados por esta feature).
- **Seed tipográfico**: quatro pares fixos (20k+ / ESTUDANTES ATIVOS; 1.2k+ / EMPRESAS PARCEIRAS; 8k+ / ESTÁGIOS INICIADOS; 95% / SATISFAÇÃO GLOBAL).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, a seção em `/quem-somos` é reconhecível frente ao Figma (fundo escuro, 4 números laranja, subtextos em caixa alta) em ≥95% dos critérios de checklist visual (paddings, cores, tipografia, gap).
- **SC-002**: Em viewport mobile, 100% das verificações confirmam 2 itens por linha, legibilidade e ausência de scroll horizontal causado pela seção.
- **SC-003**: Um editor atualiza um dos quatro números no Node em menos de 3 minutos, sem suporte de desenvolvimento.
- **SC-004**: Após o deploy padrão, `/quem-somos` exibe os 4 números do seed (ou o conteúdo editorial já presente); demais seções da página permanecem inalteradas visualmente.
- **SC-005**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cr`, com zero passos manuais no painel.
- **SC-006**: Segunda execução de `drush updb` não cria paragraphs duplicados nem sobrescreve copy editorial divergente (idempotência confirmada).
- **SC-007**: Com lista vazia ou campos vazios, a página não quebra; a seção ou elementos ausentes são omitidos de forma segura.
- **SC-008**: Estilos da seção não alteram visualmente outras seções (amostragem: Sobre nós, Missão/Visão, Diferenciais Quem Somos, home).

## Assumptions

- **Campo curto**: o pedido `field_text_simple_small` mapeia para o storage canônico existente `field_text_simple` no entity type `paragraph` (mesmo padrão das features `004`–`013`).
- **Campo de subtexto**: o pedido `field_text_simple_small_2` não existe; usa-se `field_text_simple_long` (já disponível em `paragraph`) para o rótulo em caixa alta — comprimento editorial curto, storage reutilizado.
- **Lista dedicada**: cria-se storage/instance `field_numeros_lista` no Node (ERR → paragraph, cardinality 4). Não se reutiliza `field_itens_p` (cardinality 3) nem storages de `block_content`.
- **Template**: o arquivo ativo é `themes/custom/default/templates/content/node--quem-somos.html.twig`; suggestion `node--quem-somos--full.html.twig` só se o view mode `full` passar a exigir override separado.
- **Hook número**: próximo update após `custom_configs_update_11016` → `11017`, salvo se outro update for commitado antes da implementação.
- **Posição na página**: a seção Impact in Numbers é renderizada no template do Node em posição coerente com o design (após as seções já existentes do Node); não compete com placements de bloco da feature `013`.
- **Convivência**: banner, Sobre nós, Missão/Visão e Diferenciais permanecem; esta feature apenas adiciona a faixa de números.
- Textos seed desta fase são somente pt-BR (subtextos em caixa alta conforme design).
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (PHP do `hook_update_N`, Twig, SCSS/CSS com tokens Figma) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
