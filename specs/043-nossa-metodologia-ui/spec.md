# Feature Specification: Refatorar UI — Nossa Metodologia

**Feature Directory**: `specs/043-nossa-metodologia-ui`  
**Created**: 2026-11-01  
**Status**: Draft  
**Input do usuário**: Refatorar frontend (estilo, dimensões e responsividade) do bloco “Nossa Metodologia” para eliminar a discrepância visual com o layout Figma aprovado: alinhamento do texto à direita, cor do título em preto, larguras máximas estritas das duas linhas (chevrons/etapas superiores e ícones/passos inferiores), abordagem Mobile First, CSS limpo (sem aninhamento profundo), utilitários de layout quando possível. Entregáveis de implementação (Twig + SCSS/CSS) nas fases posteriores do Spec Kit. Verificar proveito de skills do pacote `mattpocock/skills` durante o desenvolvimento.

## Escopo

### Inclui

- Ajuste visual e responsivo do bloco já existente **Nossa Metodologia** (`nossa_metodologia`) na Home e em qualquer página que reutilize a **mesma** apresentação (ex.: `/para-empresas`).
- Cabeçalho da seção (título + subtítulo) com tipografia, cor e alinhamento alinhados ao Figma.
- Linha superior (chevrons / etapas — Identidade, Pertencimento, Performance) com largura máxima estrita e centralização horizontal.
- Linha inferior (ícones circulares + setas verdes) com largura máxima estrita, distribuição espaçada e alinhamento vertical das setas ao centro dos círculos.
- Correção da cor do título principal (hoje aparece em azul/navy no ambiente local; no Figma é preto `#000000`).
- Responsividade Mobile First: em viewports &lt;992px as larguras máximas passam a 100%; em mobile, as linhas preferem empilhamento em coluna (padrão já adotado no projeto).

### Fora

- Criação de novo tipo de bloco ou paragraph (já entregues na feature **005**).
- Redesign de outros blocos da Home (`nossos_diferenciais`, vagas em destaque, hero de busca, etc.).
- Campos novos no CMS para setas, cores ou larguras (setas e conectores permanecem apresentação).
- Edição estrutural de fields/displays via Configuration Management — esta feature é só apresentação.
- Alterações em `core/` ou `vendor/`.
- Código Twig/SCSS nesta etapa de especificação (produzido em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê a seção alinhada ao Figma (Priority: P1)

Visitante abre a Home, chega à seção “Nossa Metodologia” e reconhece o layout de referência: título preto semi-negrito alinhado à direita (desktop), subtítulo alinhado ao título, linha de chevrons centralizada com largura limitada e linha de passos/ícones centralizada com largura maior.

**Why this priority**: É o objetivo central — fechar o gap visual entre o site e o design aprovado.

**Independent Test**: Comparar side-by-side a Home (viewport ≥992px) com o frame Figma da seção; validar tipografia, alinhamento e larguras das duas linhas sem depender de outros blocos.

**Acceptance Scenarios**:

1. **Given** o bloco publicado com título, subtítulo, ≥3 etapas e ≥3 passos, **When** o visitante abre a Home em desktop (≥992px), **Then** o título da seção aparece em preto `#000000` (não azul/navy), com peso SemiBold (~600) e tamanho visual próximo a 30px / line-height ~38px / letter-spacing ~−0.3px.
2. **Given** o mesmo bloco em desktop, **When** observa o cabeçalho, **Then** título e subtítulo ficam alinhados à direita, visualmente coerentes na mesma margem direita.
3. **Given** viewport ≥992px, **When** a linha superior (chevrons/etapas) renderiza, **Then** o wrapper dessa linha fica centralizado horizontalmente e com largura máxima visual de ~770px e altura suficiente para os chevrons (~48px).
4. **Given** viewport ≥992px, **When** a linha inferior (ícones + setas) renderiza, **Then** o wrapper fica centralizado horizontalmente, com largura máxima visual de ~1160px e altura aproximada de ~154px, e as setas verdes ficam alinhadas verticalmente ao centro dos círculos.

---

### User Story 2 — Ritmo e hierarquia das duas linhas (Priority: P1)

Visitante lê a metodologia como um fluxo: primeiro as três etapas em chevron, depois os passos com ícones circulares separados por setas verdes, sem elementos desalinhados ou “esticados” além do design.

**Why this priority**: Larguras e alinhamentos errados são a discrepância principal relatada em relação ao Figma.

**Independent Test**: Inspecionar as caixas de layout das duas linhas no DevTools e conferir max-width / centralização / alinhamento das setas.

**Acceptance Scenarios**:

1. **Given** etapas cadastradas, **When** a linha superior renderiza em desktop, **Then** os chevrons ficam contidos no wrapper de ~770px (não ocupam toda a largura do container de 1280px).
2. **Given** passos cadastrados, **When** a linha inferior renderiza em desktop, **Then** ícones e setas distribuem-se no wrapper de ~1160px com espaçamento equilibrado entre itens.
3. **Given** setas decorativas entre passos, **When** a linha inferior é vista, **Then** cada seta está centrada verticalmente em relação aos círculos adjacentes (não “pendurada” no topo do rótulo).

---

### User Story 3 — Experiência mobile sem quebra (Priority: P1)

Em celular e tablet estreito, as larguras máximas não forçam overflow; as linhas empilham de forma legível.

**Why this priority**: Grande parte do tráfego é mobile; max-widths fixas de desktop são o maior risco de regressão.

**Independent Test**: Abrir a Home em viewport ≤575px e ~768px; rolar até a seção; verificar overflow e legibilidade.

**Acceptance Scenarios**:

1. **Given** viewport &lt;992px, **When** a seção renderiza, **Then** os wrappers das linhas superior e inferior adotam largura efetiva 100% do container útil (sem forçar 770px/1160px).
2. **Given** viewport estreita (mobile), **When** chevrons e passos são exibidos, **Then** preferem empilhamento em coluna (padrão já usado no projeto), mantendo legibilidade e sem overflow horizontal da página.
3. **Given** viewport intermediária (tablet), **When** a linha de passos quebra, **Then** setas horizontais que deixariam de fazer sentido no wrap são omitidas ou reorientadas de forma coerente (sem setas “sobrando” no meio do fluxo).

---

### User Story 4 — Editor continua gerenciando só o conteúdo (Priority: P2)

Editor altera textos, imagens de etapas e passos no painel; a nova apresentação visual consome esses dados sem exigir conhecimento de CSS ou upload de arte monolítica.

**Why this priority**: A refatoração é de apresentação; não pode piorar o fluxo editorial da **005**.

**Independent Test**: Editar título/subtítulo/etapas/passos no admin e recarregar a Home.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** altera título, subtítulo, etapas ou passos e salva, **Then** a Home reflete o novo conteúdo após publicação/cache esperado.
2. **Given** bloco sem etapas ou sem passos, **When** a página é exibida, **Then** o cabeçalho (quando preenchido) e a linha restante ainda renderizam sem erro de template.
3. **Given** reuso do bloco em `/para-empresas`, **When** a página pública carrega, **Then** a mesma apresentação refatorada se aplica, sem regressão de placement.

### Edge Cases

- Título ou subtítulo vazios → omitir o elemento correspondente (sem headings/parágrafos vazios).
- Sem etapas e sem passos → cabeçalho ainda renderiza sem erro.
- Mais de 3 etapas ou passos → renderizar todos; wrappers mantêm max-width e o fluxo permanece legível (pode comprimir ou wrap conforme breakpoint).
- Imagens de etapa/passo muito largas → contidas com `object-fit: contain`, sem estourar o wrapper.
- Zoom do navegador / texto grande do SO → tipografia e linhas não geram overflow horizontal grave.
- Reuso em `/para-empresas` → mesma apresentação; sem regressão.

## Requirements *(obrigatório)*

### Functional Requirements

**Apresentação e layout**

- **FR-001**: A seção DEVE manter o conteúdo gerenciável já modelado (título, subtítulo, imagens de etapas, passos com ícone/título); esta feature NÃO introduz textos institucionais hardcoded como fonte de verdade.
- **FR-002**: O bloco DEVE permanecer envolvido em container de página com ritmo vertical generoso, coerente com a Home no Figma (equivalente visual a padding vertical generoso da seção).
- **FR-003**: Em viewports médias/grandes, o cabeçalho (título + subtítulo) DEVE ficar alinhado à direita; título e subtítulo compartilham a mesma margem direita visual.
- **FR-004**: Em viewports estreitas, o cabeçalho DEVE permanecer centralizado (padrão já estabelecido na **005** / CSS atual), sem perder legibilidade.
- **FR-005**: A linha superior (chevrons/etapas) DEVE ficar em wrapper próprio, centralizado horizontalmente.
- **FR-006**: Em viewport ≥992px, o wrapper da linha superior DEVE ter largura máxima de 770px e altura suficiente para conter os chevrons (~48px).
- **FR-007**: A linha inferior (ícones + setas) DEVE ficar em wrapper próprio, centralizado horizontalmente, com distribuição espaçada dos itens e alinhamento que mantenha setas no centro vertical dos círculos.
- **FR-008**: Em viewport ≥992px, o wrapper da linha inferior DEVE ter largura máxima de 1160px e altura aproximada de ~154px.
- **FR-009**: Em viewport &lt;992px, os wrappers das duas linhas DEVEM adotar largura efetiva 100% do container útil.

**Tipografia e cor (tokens do Figma)**

- **FR-010**: Título principal DEVE usar família Poppins, peso 600 (SemiBold), tamanho 30px, line-height 38px, letter-spacing −0.3px, cor `#000000` (remover a cor azul/navy atual do título).
- **FR-011**: Subtítulo DEVE permanecer alinhado ao título (direita no desktop; centro no mobile), com tipografia Poppins coerente com o design atual da seção.
- **FR-012**: Tipografia do bloco DEVE continuar Poppins em toda a seção.

**Responsividade**

- **FR-013**: A seção DEVE seguir abordagem Mobile First: regras de largura estrita (770px / 1160px) aplicam-se a partir do breakpoint grande (≥992px).
- **FR-014**: Em mobile, linhas de chevrons e de ícones/setas DEVEM preferir empilhamento em coluna (padrão global já usado neste bloco), sem overflow horizontal da página.

**Qualidade de CSS / manutenibilidade (critérios de aceite de implementação)**

- **FR-015**: Estilos novos ou refatorados DEVEM evitar aninhamento profundo de seletores; preferir variáveis CSS do tema e utilitários de layout do Bootstrap 5 já adotados pelo projeto (ex.: alinhamento de texto, flex, centralização) quando expressarem o mesmo resultado.
- **FR-016**: A refatoração DEVE preservar acessibilidade básica: texto legível, contraste do título preto sobre fundo claro, `alt` das imagens, e setas/conectores decorativos não anunciados por leitores de tela.

**Deploy e superfície de mudança**

- **FR-017**: A entrega DEVE limitar-se a tema (templates Twig + CSS/SCSS + library); sem novos fields/config estrutural e sem passos manuais no admin de produção.
- **FR-018**: Após deploy de código + limpeza de cache, a Home e páginas que reutilizam o bloco DEVEM exibir a nova apresentação sem reconfiguração de blocos.

### Key Entities

- **`nossa_metodologia` (bloco existente)**: seção institucional — título, subtítulo, imagens de etapas (chevrons), lista de passos.
- **`metodologia_passo_p` (paragraph existente)**: passo — ícone circular + título.
- **Linha superior (apresentação)**: wrapper das etapas/chevrons com max-width 770px no desktop.
- **Linha inferior (apresentação)**: wrapper dos passos/ícones + setas com max-width 1160px no desktop.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual desktop (≥992px), a seção é aceita como alinhada ao Figma em tipografia do título, alinhamento do cabeçalho e larguras das duas linhas (checklist de aceite com ≥90% dos pontos do frame aprovados).
- **SC-002**: Título da seção medido no navegador corresponde a preto `#000000` (não navy/azul da marca) e peso visual SemiBold (~600), com métricas próximas a 30px / 38px / −0.3px.
- **SC-003**: Em viewport ≥992px, o wrapper da linha superior mede no máximo 770px de largura útil e o da linha inferior no máximo 1160px, ambos centralizados.
- **SC-004**: Em viewport ≤575px, zero overflow horizontal causado pela seção; chevrons e passos permanecem legíveis sem zoom manual.
- **SC-005**: Tempo de reconhecimento da seção (título + fluxo Identidade → Performance + passos) por revisor interno ≤ 5 segundos em desktop e mobile.
- **SC-006**: Editor altera conteúdo no painel e vê o resultado na Home após publicação/cache, sem precisar editar arquivos de tema.
- **SC-007**: Reuso em `/para-empresas` (quando aplicável) exibe a mesma apresentação atualizada, sem regressão de placement ou conteúdo.

## Assumptions

- O bloco, fields, seed e placements da feature **005** permanecem a base; esta feature é refatoração de **apresentação**.
- Tokens tipográficos e medidas (Poppins 600 / 30 / 38 / −0.3, título `#000000`, max-width 770px e 1160px, alturas ~48px / ~154px, breakpoint 992px) vêm do Figma / aceite visual e são critérios de fidelidade, não “detalhe de stack”.
- Alinhamento do cabeçalho: direita em ≥md/lg e centro no mobile — preserva o comportamento já estabelecido na **005** e no CSS atual, mesmo com ênfase do pedido em `.text-end` para o desktop.
- Comportamento mobile das linhas: empilhamento em coluna (já implementado hoje), em vez de scroll horizontal; setas reorientam ou omitem conforme o breakpoint, como no CSS vigente.
- O template atual `block--block-nossa-metodologia.html.twig` e o paragraph `metodologia-passo-p` são o ponto de partida; classes utilitárias Bootstrap 5 (`.container`, `.py-5`, `.text-end`, `.mx-auto`, `.d-flex`, `.justify-content-between`, `.align-items-center` / `align-items-start`) serão aplicadas na implementação.
- Estilos hoje concentrados em `style.css` (escopo `.block-nossa-metodologia`) podem migrar para SCSS dedicado do bloco se o pipeline do tema já comportar; caso contrário, o mesmo resultado visual é entregue no ponto de compilação/serving atual do tema.
- Skills do pacote `mattpocock/skills`: não há skill dedicada de SCSS/CSS limpo aplicável a esta refatoração fiel ao Figma (a skill `prototype/UI` gera variantes radicais descartáveis — inadequada aqui). Na implementação, seguir os princípios já pedidos (Mobile First, sem deep nesting, variáveis CSS / utilitários Bootstrap 5) e, se o time instalar, complementar com skill pública de SCSS (ex.: `mindrally/skills@scss-best-practices`) — não bloqueia a feature.
- Textos e revisão visual em pt-BR.
- Figma MCP do ambiente pode exigir autenticação; se o frame não estiver acessível via ferramenta, a implementação usa as medidas e descrições desta spec + exports fornecidos pelo time.
