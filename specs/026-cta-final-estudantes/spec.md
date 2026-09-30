# Feature Specification: Bloco CTA Final — Para Estudantes

**Feature Directory**: `specs/026-cta-final-estudantes`  
**Created**: 2026-09-30  
**Status**: Draft  
**Input do usuário**: Bloco CTA Final (“Pronto para dar o próximo passo?”) na página **Para Estudantes** (`/para-estudantes`) — **reuso obrigatório** do Custom Block Type `cta_v1` já existente (sem novos tipos/campos), nova instância com copy do layout, design de gradiente escuro do Figma **isolado exclusivamente a esta instância** (não pode vazar para o CTA claro de Quem Somos / Para Empresas), placement como **último bloco** de `content_full` imediatamente antes do footer, deploy 100% automatizado via `hook_update_N` idempotente + `drush cex`.

## Escopo

### Inclui

- Reuso do tipo de bloco **CTA v1** (`cta_v1`) e dos campos canônicos já existentes:
  - Título: `field_text_simple`
  - Corpo/subtítulo: `field_text_simple_long`
  - Link primário: `field_link`
  - Link secundário: `field_link_2`
- **Não** criar novos block types, field storages ou field instances.
- Seed idempotente de **nova instância** (distinta das de Quem Somos e Para Empresas) com copy do design:
  - Título: `Pronto para dar o próximo passo?`
  - Corpo: `Junte-se a milhares de estudantes que já encontraram a oportunidade ideal através da nossa plataforma.`
  - Primário: texto `Cadastre-se Gratuitamente` → URL `/cadastro/candidato`
  - Secundário: texto `Explorar Vagas` → URL `/vagas`
- Placement na região `content_full` do tema `default`, visível **somente** em `/para-estudantes`, com machine name / id de config no padrão do projeto: `default_ctav1paraestudantes` (pedido verbal `ctav1_para_estudantes`).
- **Weight** maior que o último bloco atual da composição PE (destaque `vagas` `block_3` em weight `3`) — tipicamente **weight `4`** — garantindo que o CTA seja o **último bloco** da região `content_full`, imediatamente antes do footer.
- Isolamento visual obrigatório desta instância (gradiente escuro, paddings, botão secundário outline branco) via:
  - template Twig por theme suggestion do placement (ex.: `block--default-ctav1paraestudantes.html.twig`), **e/ou**
  - classe/ID de escopo no preprocess + CSS referenciando o ID do bloco (ex.: `#block-default-ctav1paraestudantes`) / classe dedicada (ex.: `.block-cta-v1--para-estudantes`).
- Critérios visuais Figma (esta instância apenas):
  - Fundo full-bleed com gradiente linear `#023C62` → `#011A2B`
  - Padding do container da seção: top/bottom `64px`, left/right `40px`
  - Conteúdo interno em `.container` (max-width ~1280px Bootstrap)
  - Textos centralizados, cor branca; título `h2` Poppins semibold/bold
  - Botões centralizados: linha no desktop (gap), empilhados no mobile
  - Primário: fundo laranja do tema (`#FD7B1A`), texto branco, sem borda
  - Secundário: fundo transparente, borda branca sólida `1px`, texto branco — **sobrescreve** o secundário claro do `cta_v1` global **somente neste escopo**
- `hook_update_N` idempotente no `custom_configs` (`custom_configs_update_11043`; `11042` já usado no título `/vagas`) criando a instância via Entity API e garantindo o placement/visibilidade.
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento (placement e dependências).
- Atualização cirúrgica do `PRD.md` (§3.6 / composição `/para-estudantes`) refletindo a nova instância `cta_v1` e o weight.

### Fora

- Criação de novo Custom Block Type ou novos fields/storages.
- Alteração do visual/CSS global do `cta_v1` usado em `/quem-somos` (`default_ctav1quemsomos`) ou `/para-empresas` (`default_ctav1paraempresas`) — regressão visual nessas páginas é **proibida**.
- Redesign do Hero (`021`), Benefícios (`022`), Jornada (`023`), Perfil Destaque (`024`) ou cards/listagem (`025`).
- Alteração da View `vagas` / displays `page_1` / `block_3`.
- Layout Builder / edição visual de colunas pelo editor.
- Exibição deste placement em home, `/quem-somos`, `/para-empresas` ou outras rotas.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/CSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o CTA final em Para Estudantes (Priority: P1)

Visitante acessa `/para-estudantes`, rola até o fim do conteúdo e vê, imediatamente antes do rodapé, uma faixa escura com gradiente, título, texto de apoio e dois botões de ação, alinhados ao layout de referência.

**Why this priority**: É o valor principal — converter a jornada da landing em cadastro ou exploração de vagas.

**Independent Test**: Abrir `/para-estudantes` em viewport ≥768px com o bloco publicado e comparar faixa, tipografia, copy e botões com o design de referência.

**Acceptance Scenarios**:

1. **Given** o bloco CTA seedado com título, corpo e dois links, **When** o visitante abre `/para-estudantes` em desktop, **Then** vê a seção “Pronto para dar o próximo passo?” como último conteúdo antes do footer, com a copy do design.
2. **Given** a seção renderizada, **When** observa o fundo, **Then** o gradiente escuro ocupa 100% da largura da viewport (full-bleed) e o conteúdo interno permanece contido (container ~1280px).
3. **Given** a seção renderizada, **When** mede o padding da seção, **Then** top/bottom ≈ `64px` e left/right ≈ `40px` (±2px de arredondamento).
4. **Given** a seção renderizada, **When** observa textos, **Then** título e corpo estão centralizados e em cor branca (ou equivalente de alto contraste sobre o fundo escuro).

---

### User Story 2 — Visitante mobile vê botões empilhados (Priority: P1)

Em viewport estreita, os botões empilham verticalmente; a faixa permanece legível e não causa scroll horizontal.

**Why this priority**: Tráfego mobile não pode perder as CTAs nem quebrar o layout.

**Independent Test**: Abrir `/para-estudantes` em viewport ≤575.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** os dois botões aparecem empilhados (um abaixo do outro), ainda centralizados.
2. **Given** viewport desktop, **When** observa os botões, **Then** “Cadastre-se Gratuitamente” (primário laranja) e “Explorar Vagas” (secundário outline branco) aparecem lado a lado com espaçamento visível entre eles.
3. **Given** qualquer viewport, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pelo bloco.

---

### User Story 3 — Visitante usa os botões de ação (Priority: P1)

Visitante clica em cada botão e é levado à rota canônica correspondente, com URLs limpas.

**Why this priority**: Sem navegação correta, o CTA não entrega conversão.

**Independent Test**: Clicar nos dois botões em `/para-estudantes` e verificar destino.

**Acceptance Scenarios**:

1. **Given** o bloco seedado, **When** o visitante clica em “Cadastre-se Gratuitamente”, **Then** navega para `/cadastro/candidato`.
2. **Given** o bloco seedado, **When** o visitante clica em “Explorar Vagas”, **Then** navega para `/vagas`.
3. **Given** um editor altera URL ou texto de um botão e salva, **When** o visitante recarrega a página, **Then** vê o novo rótulo/destino após o cache esperado do site.

---

### User Story 4 — Isolamento: Quem Somos e Para Empresas não mudam (Priority: P1)

O design escuro desta instância **não** altera o CTA claro já existente em outras páginas.

**Why this priority**: Regressão em Quem Somos / Para Empresas quebraria layouts já publicados.

**Independent Test**: Comparar visualmente `/quem-somos`, `/para-empresas` e `/para-estudantes` após o deploy.

**Acceptance Scenarios**:

1. **Given** deploy aplicado, **When** o visitante abre `/quem-somos`, **Then** o CTA v1 permanece com o visual claro (fundo azul claro / card contido) — sem gradiente escuro full-bleed desta feature.
2. **Given** deploy aplicado, **When** o visitante abre `/para-empresas`, **Then** o CTA v1 daquela página mantém o visual anterior (sem herdar o outline branco / gradiente desta feature).
3. **Given** deploy aplicado, **When** o visitante abre home ou outras rotas, **Then** o placement `default_ctav1paraestudantes` **não** é exibido.

---

### User Story 5 — Editor gerencia conteúdo sem código (Priority: P1)

Editor autenticado com permissão de blocos edita título, corpo e os dois links da instância de Para Estudantes; as mudanças refletem em `/para-estudantes` sem deploy de código.

**Why this priority**: Copy e destinos de conversão mudam; não podem depender de desenvolvimento após o seed.

**Independent Test**: Editar o bloco no painel, salvar e recarregar `/para-estudantes`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre a instância CTA de Para Estudantes, **Then** consegue editar título, corpo, botão primário e botão secundário (URL + texto).
2. **Given** um bloco existente, **When** o editor altera textos/links e salva, **Then** a página pública reflete as mudanças após o cache esperado do site.
3. **Given** a edição, **When** altera apenas a instância de Para Estudantes, **Then** as instâncias de Quem Somos e Para Empresas permanecem inalteradas.

---

### User Story 6 — Deploy reproduz seed e placement (Priority: P1)

Homologação/produção recebem a nova instância, o placement e os estilos isolados apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/para-estudantes` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado o fluxo `cim` → `updb` → `cim` → `cr`, **Then** `/para-estudantes` exibe o CTA final com a copy do seed (ou conteúdo editorial já existente), como último bloco de `content_full`.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de bloco nem sobrescrita de copy editorial divergente do seed.
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** o placement `default_ctav1paraestudantes` (e dependências) aparece versionado em `config/sync`.

### Edge Cases

- Título vazio → omitir o título; manter corpo/botões se preenchidos.
- Corpo vazio → omitir o corpo; manter título/botões se preenchidos.
- Botão primário ou secundário sem URL → omitir esse botão (não renderizar link quebrado).
- Ambos os botões ausentes → renderizar apenas textos da faixa (sem área de botões vazia).
- Título e corpo ambos vazios e sem botões → omitir a faixa inteira (não renderizar seção vazia).
- Reexecução do hook → no-op seguro; **não** sobrescrever conteúdo editorial divergente do seed; popular apenas se a instância estiver ausente ou campos vazios conforme regra do projeto.
- Página diferente de `/para-estudantes` → este placement não aparece.
- Convivência com composição PE atual: hero (banner) → benefícios (w0) → jornada (w1) → perfil (w2) → vagas destaque (w3) → **CTA final (w4)** → footer; esta feature não altera os blocos anteriores.
- Template/CSS global do `cta_v1` permanece válido para outras páginas; estilos escuros só sob o escopo desta instância.

## Requirements *(obrigatório)*

### Functional Requirements

**Reuso e conteúdo**

- **FR-001**: O sistema DEVE reutilizar o Custom Block Type existente `cta_v1`; **NÃO** DEVE criar novos block types, field storages ou field instances para esta feature.
- **FR-002**: A nova instância DEVE usar os campos canônicos: título `field_text_simple`, corpo `field_text_simple_long`, primário `field_link`, secundário `field_link_2`.
- **FR-003**: Todo o conteúdo exibido (título, corpo, textos e URLs dos botões) DEVE ser gerenciável no painel do bloco; placeholders de template só para ausência de dado.
- **FR-004**: A instância de Para Estudantes DEVE ser **distinta** das instâncias já existentes de Quem Somos e Para Empresas (UUID próprio; edição independente).
- **FR-005**: Alterações estruturais de placement DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.

**Apresentação (tokens Figma — CTA Final PE, escopo isolado)**

- **FR-006**: Estilos novos desta feature DEVEM aplicar-se **somente** à instância/placement de Para Estudantes (via suggestion Twig do placement e/ou seletor de ID/classe dedicada); o CSS/Twig global do `cta_v1` claro **NÃO** DEVE ser alterado de forma que mude Quem Somos ou Para Empresas.
- **FR-007**: O fundo da seção DEVE ser full-bleed (100% da viewport) com gradiente linear de `#023C62` para `#011A2B`.
- **FR-008**: O padding da seção DEVE ser top `64px`, bottom `64px`, left `40px`, right `40px`.
- **FR-009**: O conteúdo interno DEVE usar container com largura máxima típica Bootstrap (~1280px), centralizado.
- **FR-010**: Título, corpo e grupo de botões DEVEM estar alinhados ao centro; textos DEVEM forçar cor branca sobre o fundo escuro.
- **FR-011**: O título DEVE usar família Poppins, peso semibold/negrito, hierarquia `h2`.
- **FR-012**: Os botões DEVEM ficar lado a lado no desktop (centralizados, com gap) e empilhar no mobile.
- **FR-013**: O botão primário DEVE ter fundo `#FD7B1A`, texto branco, sem borda.
- **FR-014**: O botão secundário DEVE ter fundo transparente, borda sólida branca `1px solid #FFFFFF` e texto branco — sobrescrevendo o estilo secundário claro do `cta_v1` global **apenas neste escopo**.

**Placement e ordem**

- **FR-015**: O placement DEVE ter id `default_ctav1paraestudantes` (equivalente ao machine name pedido `ctav1_para_estudantes`), tema `default`, região `content_full`, visibilidade `request_path` = `/para-estudantes`.
- **FR-016**: O weight do placement DEVE ser o maior entre os blocos path-scoped de `/para-estudantes` em `content_full` (tipicamente `4`, após `default_views_block__vagas_block_3` weight `3`), garantindo posição imediatamente antes do footer.

**Seed e deploy**

- **FR-017**: Um `hook_update_N` no módulo `custom_configs` (`custom_configs_update_11043`) DEVE, de forma **idempotente**, usando Entity API (`\Drupal::entityTypeManager()` / `BlockContent::create()`): criar/seedar a instância `cta_v1` com UUID fixo e a copy do design **somente se** ausente/campos vazios; garantir placement `default_ctav1paraestudantes` em `content_full` weight ≥4 com visibilidade `/para-estudantes`.
- **FR-018**: O seed DEVE preservar edições posteriores do editor; preencher apenas campos/instância vazios; **não** duplicar blocos em reexecução.
- **FR-019**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` (cim antes do updb; 2ª passada de cim após updb para placements que dependem de UUIDs seedados); ao final do desenvolvimento na origem, `drush cex` para versionar o placement.
- **FR-020**: A seção relevante do `PRD.md` (§3.6 / composição `/para-estudantes`) DEVE ser atualizada de forma cirúrgica refletindo a nova instância `cta_v1`, placement, weight e seed.

### Key Entities

- **`cta_v1` (block_content) — instância Para Estudantes**: chamada final — título (`field_text_simple`), corpo (`field_text_simple_long`), botão primário (`field_link`), botão secundário (`field_link_2`); UUID fixo próprio (distinto de Quem Somos `e6f7a8b9-…` e Para Empresas `a7b8c9d0-…`).
- **Placement**: `default_ctav1paraestudantes` na região `content_full`, weight tipicamente `4`, visível somente em `/para-estudantes`.
- **Seed tipográfico**: título “Pronto para dar o próximo passo?”; corpo “Junte-se a milhares de estudantes que já encontraram a oportunidade ideal através da nossa plataforma.”; primário “Cadastre-se Gratuitamente” → `/cadastro/candidato`; secundário “Explorar Vagas” → `/vagas`.
- **Isolamento de apresentação**: suggestion Twig do placement e/ou seletor `#block-default-ctav1paraestudantes` / `.block-cta-v1--para-estudantes` + library CSS dedicada (ou folha escopada), sem alterar a library global `cta_v1` de forma regressiva.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, o CTA final em `/para-estudantes` é reconhecível frente ao Figma (gradiente escuro full-bleed, textos brancos, dois botões) em ≥95% dos critérios de checklist visual (paddings 64/40, cores, tipografia, alinhamento).
- **SC-002**: Em viewport mobile, 100% das verificações confirmam botões empilhados, legibilidade e ausência de scroll horizontal causado pelo bloco.
- **SC-003**: 100% dos cliques nos botões seedados levam às rotas `/cadastro/candidato` e `/vagas` respectivamente.
- **SC-004**: Em amostragem de `/quem-somos` e `/para-empresas`, 100% das verificações confirmam que o CTA claro existente **não** herdou gradiente escuro nem botão secundário outline branco desta feature.
- **SC-005**: Um editor atualiza título, corpo ou um dos links da instância PE em menos de 3 minutos, sem suporte de desenvolvimento.
- **SC-006**: Após o deploy padrão, `/para-estudantes` exibe o CTA como último bloco de `content_full` (após o destaque de vagas), com a copy do seed (ou o conteúdo editorial já presente); demais seções da página permanecem inalteradas visualmente.
- **SC-007**: Ambiente desatualizado reproduz o comportamento com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cim -y` + `drush cr`, com zero passos manuais no painel.
- **SC-008**: Segunda execução de `drush updb` não cria bloco duplicado nem sobrescreve copy editorial divergente (idempotência confirmada).
- **SC-009**: Com campos vazios, a página não quebra; elementos ausentes são omitidos de forma segura.

## Assumptions

- O tipo `cta_v1` e os quatro campos já existem e estão estáveis (feature `015` + reuso PE em `019`/`11024`); esta feature só cria **nova instância + placement + estilos isolados**.
- Machine name pedido `ctav1_para_estudantes` mapeia para o id de config Drupal `default_ctav1paraestudantes` (mesmo padrão de `default_ctav1quemsomos` / `default_ctav1paraempresas`).
- URL do botão secundário: `/vagas` (rota canônica da listagem completa após feature `025`).
- Ordem atual de `/para-estudantes` em `content_full`: benefícios `0` → jornada `1` → perfil `2` → vagas `block_3` `3` → **CTA `4`**.
- Hook número: `custom_configs_update_11043` (`11042` já usado para título do header `/vagas`).
- UUID: a implementação definirá UUID fixo para a instância seedada e para o placement exportável (padrão das features anteriores).
- Isolamento preferencial: template suggestion do placement + CSS escopado por ID/classe da instância; não forçar mudança no Twig global `block--block-cta-v1.html.twig` além do necessário para não regredir outras páginas (preferir template específico da instância).
- Textos seed desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (PHP do `hook_update_N`, Twig suggestion, CSS com gradiente/paddings/botões, `drush cex`) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
