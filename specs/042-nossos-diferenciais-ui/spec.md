# Feature Specification: Refatorar UI — Nossos Diferenciais (Home)

**Feature Directory**: `specs/042-nossos-diferenciais-ui`  
**Created**: 2026-11-01  
**Status**: Draft  
**Input do usuário**: Refatorar frontend (estilo e responsividade) do bloco “Nossos Diferenciais” na página inicial para eliminar a discrepância visual com o Figma: tipografia, margens, grade em duas colunas e colagem de imagens complexa à esquerda; abordagem Mobile First; CSS limpo (sem aninhamento profundo); utilitários de layout quando possível. Entregáveis de implementação (Twig + SCSS) nas fases posteriores do Spec Kit.

## Escopo

### Inclui

- Ajuste visual e responsivo do bloco já existente **Nossos Diferenciais** (`nossos_diferenciais`) na Home (e qualquer página que reutilize a **mesma** instância/apresentação, ex.: `/para-empresas`).
- Cabeçalho da seção (título + subtítulo) com tipografia e largura máxima alinhadas ao Figma.
- Corpo em duas colunas em telas grandes: colagem de imagens à esquerda + lista de diferenciais à direita.
- Colagem visual (asset CMS completo + formas decorativas CSS), sem alterar o modelo de conteúdo editorial além do necessário para fidelidade visual.
- Lista de itens com ícone à esquerda e título/descrição à direita, cores de título por posição conforme design.
- Correção da cor do título principal (hoje aparece em azul/navy no ambiente local; no Figma é preto).
- Responsividade Mobile First: empilhamento legível; simplificação da colagem em viewports estreitas quando necessário.

### Fora

- Criação de novo tipo de bloco ou paragraph (já entregues na feature **004**).
- Redesign de outros blocos da Home (`nossa_metodologia`, vagas em destaque, hero de busca, etc.).
- Alteração do bloco distinto `diferenciais_quem_somos` (página Quem Somos / Benefícios PE).
- Edição estrutural de fields/displays via Configuration Management, salvo se a implementação da colagem exigir cardinalidade extra de imagem — preferência é **não** expandir o CMS.
- Alterações em `core/` ou `vendor/`.
- Código Twig/SCSS nesta etapa de especificação (produzido em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê a seção alinhada ao Figma (Priority: P1)

Visitante abre a Home, chega à seção “Nossos Diferenciais” e reconhece o layout de referência: título preto semi-negrito, subtítulo compacto com largura limitada, colagem de imagens à esquerda e lista de diferenciais à direita (desktop).

**Why this priority**: É o objetivo central — fechar o gap visual entre o site e o design aprovado.

**Independent Test**: Comparar side-by-side a Home (viewport ≥992px) com o frame Figma da seção; validar tipografia, margens, colagem e lista sem depender de outros blocos.

**Acceptance Scenarios**:

1. **Given** o bloco publicado com título, subtítulo, imagem e ≥3 itens, **When** o visitante abre a Home em desktop, **Then** o título da seção aparece em preto (não azul), com peso semi-negrito e tamanho visual próximo a 30px / line-height ~38px.
2. **Given** o mesmo bloco, **When** observa o subtítulo, **Then** o texto usa peso regular, tamanho visual próximo a 14px / line-height ~20px, e a largura útil do subtítulo fica limitada (~663px ou equivalente de coluna).
3. **Given** viewport ≥992px, **When** a seção renderiza, **Then** a colagem de imagens e a lista ficam lado a lado, alinhadas verticalmente ao centro da linha.
4. **Given** a coluna esquerda, **When** a colagem é exibida, **Then** a imagem CMS (colagem completa ~1324×718) domina a composição; formas decorativas (anel verde, ponto navy ~49×49, ring cinza no desktop) aparecem atrás/ao redor da foto, sem impedir a leitura da lista nem gerar overflow.

---

### User Story 2 — Lista de diferenciais legível e com hierarquia de cor (Priority: P1)

Visitante identifica cada diferencial pela combinação ícone + título colorido + descrição, com espaçamento confortável entre itens.

**Why this priority**: A lista é o conteúdo persuasivo da seção; tipografia e cor erradas reduzem impacto.

**Independent Test**: Inspecionar cada item na ordem editorial e conferir layout flex (ícone | texto) e cores cicláveis.

**Acceptance Scenarios**:

1. **Given** itens cadastrados, **When** a lista renderiza, **Then** cada item mostra ícone à esquerda e título + descrição à direita, alinhados ao topo, com espaçamento consistente entre itens.
2. **Given** três ou mais itens, **When** o visitante observa os títulos, **Then** as cores seguem a sequência do design por posição (1º verde, 2º azul-escuro, 3º laranja/terracota, ciclável).
3. **Given** descrição longa, **When** o texto quebra linha, **Then** não há truncamento artificial; o layout não estoura horizontalmente.

---

### User Story 3 — Experiência mobile sem quebra (Priority: P1)

Em celular, a seção empilha de forma legível; a colagem complexa não causa scroll horizontal nem sobreposição que esconda texto.

**Why this priority**: Grande parte do tráfego é mobile; a colagem é o maior risco de regressão.

**Independent Test**: Abrir a Home em viewport ≤575px e ~768px; rolar até a seção; verificar overflow e legibilidade.

**Acceptance Scenarios**:

1. **Given** viewport &lt;992px, **When** a seção renderiza, **Then** cabeçalho, colagem e lista empilham em ordem legível (topo → mídia → lista).
2. **Given** viewport estreita, **When** a colagem é exibida, **Then** o container escala proporcionalmente (sem overflow horizontal da página) — via proporção/escala, não dimensões fixas absolutas que quebrem o layout.
3. **Given** telas muito pequenas, **When** a simplificação de design for necessária, **Then** o ring cinza pode ser ocultado, mantendo a colagem CMS e os decos mínimos sem overflow.

---

### User Story 4 — Editor continua gerenciando só o conteúdo (Priority: P2)

Editor altera textos, ícones, itens e a imagem de destaque no painel; a nova apresentação visual consome esses dados sem exigir upload de “arte de fundo” ou conhecimento de CSS.

**Why this priority**: A refatoração é de apresentação; não pode piorar o fluxo editorial da **004**.

**Independent Test**: Editar título/subtítulo/imagem/itens no admin e recarregar a Home.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** altera título, subtítulo ou itens e salva, **Then** a Home reflete o novo conteúdo após publicação/cache esperado.
2. **Given** troca da imagem de destaque no CMS, **When** a página pública carrega, **Then** a colagem completa usa a nova imagem; elementos decorativos CSS permanecem intactos.
3. **Given** bloco sem imagem, **When** a página é exibida, **Then** um placeholder preserva a estrutura da coluna visual sem erro.

### Edge Cases

- Título ou subtítulo vazios → omitir o elemento correspondente (sem headings/parágrafos vazios).
- Lista vazia → cabeçalho e área visual ainda renderizam sem erro de template.
- Mais de 3 itens → renderizar todos; cores de título ciclam (verde → navy → laranja).
- Imagem muito larga/alta → contida na âncora da colagem (`object-fit` / max dimensions), sem estourar a coluna.
- Reuso do bloco em `/para-empresas` → a mesma apresentação refatorada se aplica (mesma instância/template); sem regressão de placement.
- Zoom do navegador / texto grande do SO → tipografia e colagem não geram overflow horizontal grave.

## Requirements *(obrigatório)*

### Functional Requirements

**Apresentação e layout**

- **FR-001**: A seção DEVE manter o conteúdo gerenciável já modelado (título, subtítulo, imagem de destaque, lista de itens com ícone/título/descrição); esta feature NÃO introduz textos institucionais hardcoded como fonte de verdade.
- **FR-002**: Em viewports grandes (≥992px), o corpo da seção DEVE exibir duas colunas (`col-lg-7` colagem + `col-lg-5` lista), com espaçamento generoso entre colunas e alinhamento vertical centralizado.
- **FR-003**: Título e subtítulo da seção DEVEM permanecer acima da grade de duas colunas.
- **FR-004**: O subtítulo DEVE ter largura máxima restrita (~663px ou equivalente de coluna estreita), alinhado ao Figma.
- **FR-005**: A seção DEVE ter margens/paddings verticais generosos, coerentes com o ritmo visual da Home no Figma.
- **FR-006**: Cada item da lista DEVE usar disposição horizontal ícone + texto (alinhamento superior), com espaçamento vertical consistente entre itens.

**Tipografia e cor (tokens do Figma)**

- **FR-007**: Título principal DEVE usar família Poppins, peso 600 (SemiBold), tamanho 30px, line-height 38px, letter-spacing −0.3px, cor `#000000` (remover a cor azul/navy atual do título).
- **FR-008**: Subtítulo/descrição DEVE usar Poppins, peso 400 (Regular), tamanho 14px, line-height 20px.
- **FR-009**: Títulos dos itens da lista DEVEM permanecer em negrito, com cores por posição conforme o Figma (verde / azul-escuro / laranja, cicláveis).
- **FR-010**: Tipografia do bloco DEVE continuar Poppins em toda a seção.

**Colagem de imagens**

- **FR-011**: A coluna esquerda DEVE apresentar a colagem visual do Figma (não uma foto única centralizada com disco navy legado).
- **FR-012**: A imagem do CMS (`field_image`) DEVE ser a **colagem completa** (âncora + fatias embutidas no asset editorial), com proporção aproximada 1324×718; servida via image style adequado (ex.: `wide`) com `width`/`height` para CLS.
- **FR-013**: Fatias secundárias **não** são markup/assets separados do tema quando já estão no PNG editorial — evita duplicar a arte; a fidelidade das fatias é responsabilidade do asset CMS.
- **FR-014**: Elementos decorativos (anel verde, ponto navy ~49×49, ring cinza no desktop) DEVEM ser puramente apresentacionais (spans/CSS + `aria-hidden`), sem campos novos no CMS. Anel verde e ring cinza ficam **atrás** da foto (`z-index` menor); o ponto navy PODE ficar **na frente** da âncora (`z-index` maior), conforme o Figma.
- **FR-015**: Em mobile, a colagem DEVE escalar de forma proporcional ao container sem overflow horizontal; o ring cinza PODE ser ocultado em viewports estreitas.

**Qualidade de CSS / manutenibilidade (critérios de aceite de implementação)**

- **FR-016**: Estilos novos ou refatorados DEVEM seguir abordagem Mobile First e evitar aninhamento profundo de seletores; preferir variáveis CSS do tema e utilitários de layout do Bootstrap 5 já adotados pelo projeto quando expressarem o mesmo resultado.
- **FR-017**: A refatoração DEVE preservar acessibilidade básica: texto legível, contraste adequado nos títulos coloridos, `alt` das imagens, e decoração não anunciada por leitores de tela (`aria-hidden` / pseudo-elementos).

**Deploy e superfície de mudança**

- **FR-018**: A entrega DEVE limitar-se a tema (templates Twig + CSS + library), image styles já existentes no sync e, quando necessário para SC-001 em ambientes só-seed, assets versionados em `custom_configs/assets/nossos-diferenciais/` + `hook_update_N` idempotente; sem fatias obrigatórias no tema e sem passos manuais no admin de produção.
- **FR-019**: Após deploy de código + `drush updb` (mídia seed se vazia) + limpeza de cache (`drush cr` **obrigatório**), a Home e `/para-empresas` DEVEM exibir a nova apresentação sem reconfiguração de blocos.

### Key Entities

- **`nossos_diferenciais` (bloco existente)**: seção institucional — título, subtítulo, imagem de destaque (âncora da colagem), lista de diferenciais.
- **`diferencial_item_p` (paragraph existente)**: item — ícone, título, descrição.
- **Colagem visual (apresentação)**: asset CMS (colagem completa) + formas decorativas CSS; não é entidade de conteúdo separada.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual desktop (≥992px), a seção é aceita como alinhada ao Figma em tipografia do título/subtítulo, margens gerais, colagem e lista (checklist de aceite com ≥90% dos pontos do frame aprovados).
- **SC-002**: Título da seção medido no navegador corresponde a preto `#000000` (não navy/azul da marca) e peso visual SemiBold (~600).
- **SC-003**: Em viewport ≤575px, zero overflow horizontal causado pela seção; textos da lista permanecem legíveis sem zoom manual.
- **SC-004**: Tempo de reconhecimento da seção (título + três diferenciais) por revisor interno ≤ 5 segundos em desktop e mobile.
- **SC-005**: Editor altera conteúdo no painel e vê o resultado na Home após publicação/cache, sem precisar editar arquivos de tema.
- **SC-006**: Reuso em `/para-empresas` exibe a mesma apresentação atualizada, sem regressão de placement ou conteúdo.

## Assumptions

- O bloco, fields, seed e placements da feature **004** permanecem a base; esta feature é refatoração de **apresentação**.
- A imagem cadastrada em `field_image` é a **única** imagem editorial obrigatória e carrega a colagem completa (âncora + fatias no mesmo arquivo). Decos CSS não exigem upload extra — alinhado ao princípio da **004**.
- Tokens tipográficos e medidas (30/38/−0.3, 14/20, colagem ~1324×718, decos, max-width 663px, colunas 7/5) vêm do Figma / aceite visual e são critérios de fidelidade, não “detalhe de stack”.
- Cores dos títulos dos itens permanecem verde / navy / laranja cicláveis via `.field__item:nth-child(3n+k)` (wrappers do field com `display: contents`).
- O template atual `block--block-nossos-diferenciais.html.twig` e o paragraph `diferencial-item-p` são o ponto de partida; classes utilitárias Bootstrap 5 (container, row, col, d-flex, gap, etc.) serão aplicadas na implementação.
- Estilos hoje concentrados em `style.css` (escopo `.block-nossos-diferenciais`) podem migrar para SCSS dedicado do bloco se o pipeline do tema já comportar; caso contrário, o mesmo resultado visual é entregue no ponto de compilação/serving atual do tema.
- Skills do pacote `mattpocock/skills`: na pesquisa atual não há skill CSS/UI desse owner aplicável; na implementação, considerar skill pública de boas práticas SCSS (ex.: `mindrally/skills@scss-best-practices`) se o time optar por instalá-la — não bloqueia a feature.
- Textos e revisão visual em pt-BR.
- Figma MCP do ambiente pode exigir autenticação; se o frame não estiver acessível via ferramenta, a implementação usa as medidas e descrições desta spec + exports fornecidos pelo time.
