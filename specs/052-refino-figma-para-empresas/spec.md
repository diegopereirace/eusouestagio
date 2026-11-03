# Feature Specification: Refino Visual "Para Empresas" — Conformidade com Figma

**Feature Directory**: `specs/052-refino-figma-para-empresas`  
**Created**: 2026-11-03  
**Status**: Draft  
**Input do usuário**: Refatorar o frontend de `/para-empresas` alinhando banner (paridade com home), Nossos Diferenciais (camadas geométricas + tipografia `#4E4E4E`), carrossel de Depoimentos (navegação por bullets), e CTA final com lógica de roles (`empresa`) + lazy load; automação via `hook_update_N` / `drush cex` quando o conteúdo do bloco for alterado; aproveitar skills `mattpocock/skills` se disponíveis.

## Nota de levantamento (estado atual vs. pedido)

Antes de detalhar o escopo, o levantamento no código confirmou o que **já funciona** e as **lacunas reais**, para evitar retrabalho e regressão do que as features `019`, `020` e `042` já entregaram:

| Seção | Já conforme | Lacuna real a corrigir |
|---|---|---|
| Banner PE (`#banner-carousel-para-empresas`) | Bootstrap carousel com indicadores/setas multi-slide; CTAs overlay no desktop; `<picture>` com breakpoint ~768px; seed de 2 slides | **Não reutiliza** a library/JS/CSS do carrossel da home (`banner_carousel`); CTAs mobile empilham a partir de **991.98px**, não `<768px`; seletores CSS legados `body.node--type-para-empresas` (página virou `page` após `11028`); seed sem arte mobile dedicada (`field_imagem_mobile` vazio → reusa desktop; placeholder visual possível) |
| Nossos Diferenciais | Anel verde atrás + dot navy na frente (feature `042`); grid responsivo; overflow controlado | Cor do texto descritivo do item (`.nd-item__desc`) é **`#4a5d73`**, não **`#4E4E4E`**; validar alinhamento topo imagem↔texto e simplificação das decorações no mobile frente ao Figma |
| Depoimentos | Markup com `data-carousel-dot` / `data-carousel-center`; `carousel-center.js` sincroniza `is-active` + `aria-current` | Relato de bullets “inativos” / possível dessincronia visual (estado ativo vs. slide central); cor do bullet ativo pode divergir do Figma (laranja no design vs. azul escuro no CSS atual) — **validar e corrigir comportamento perceptível** |
| CTA final (`cta_v1` PE) | Instância `default_ctav1paraempresas`; seed já com título “Pronto para contratar…” e secundário **“Abrir Vaga”** → `/painel/empresa/vagas/nova`; sem imagem no bloco | **Sem lógica de role `empresa`** no Twig (botão 1 nunca desabilita; botão 2 sempre visível); template genérico `block--block-cta-v1.html.twig` (não há override PE); se conteúdo editorial em destino ainda tiver “Falar com Especialista”, precisa seed/hook de correção |

## Escopo

### Inclui

- **Banner principal**: paridade de experiência de transição, overlays e setas com o carrossel da home (`<front>`), reutilizando a mesma library/comportamento sempre que possível sem quebrar o layout duas colunas específico de PE; em viewports `<768px`, CTAs com largura uniforme e disposição lado a lado (com espaçamento) quando couber, ou empilhados com espaçamento uniforme; substituir/evitar arte placeholder (`500×600`) e garantir imagem responsiva correta (desktop + mobile quando houver asset).
- **Nossos Diferenciais**: manter/refinar a composição desktop — imagem alinhada ao topo do texto lateral; anel verde **atrás** da foto e círculo azul escuro **na frente**; tipografia do texto descritivo (abaixo do título da seção e/ou descrições dos itens, conforme Figma) em `#4E4E4E`; no mobile, desativar posicionamento absoluto complexo (esconder ou tornar estático as formas) para a imagem fluir acima dos textos sem estourar o viewport.
- **Depoimentos**: garantir que clicar/ativar cada bullet navegue para o depoimento correspondente e que o estado visual ativo reflita o slide central (incluindo cor/contraste alinhados ao Figma).
- **CTA final “Pronto para contratar…”**:
  - Imagens pesadas do CTA (se existirem) com carregamento preguiçoso nativo.
  - **Botão 1 (esquerda / “Cadastrar Empresa”)**: se o visitante estiver autenticado **e** tiver a role `empresa`, renderizar controle **não clicável** (botão desabilitado ou equivalente estilizado) com aparência cinza-alaranjado fosco e texto indicativo (ex.: “Empresa Cadastrada”); caso contrário, manter link para `/cadastro/empresa`.
  - **Botão 2 (direita)**: texto **“Abrir Vaga”**; **somente** renderizado se o usuário tiver a role `empresa`; destino = rota canônica de criação de vaga do produto (ex. `/painel/empresa/vagas/nova`).
- **`hook_update_N` idempotente** em `custom_configs.install` (próximo livre após `11075` → `11076`) se a correção de rótulos/links do CTA depender de campos da instância do bloco (garantir “Abrir Vaga” e URI correta **somente quando** o valor ainda for o legado “Falar com Especialista” ou equivalente vazio/incorreto — nunca sobrescrever copy editorial divergente intencional além do legado conhecido).
- Export via `drush cex` se displays/placements/config estrutural forem alterados.
- Atualização cirúrgica do `PRD.md` (seção Para Empresas) refletindo comportamento de CTAs por role e refinamentos visuais.

### Fora

- Redesign de header, footer, Nossa Metodologia, O Que Fazemos ou Benefícios além do necessário para não regressar.
- Alterações em `/quem-somos`, `/para-estudantes`, home (exceto o **reuso** da library do carrossel da home, sem mudar o visual da home).
- Criação de novo Content Type / Block Type / Paragraph Type.
- Introdução de pipeline SCSS/Sass/bundler (o tema publica CSS plano — ver Assumptions).
- Alterações em `core/` ou `vendor/`.
- Entrega de código (PHP/Twig/CSS/JS) nesta etapa de especificação — produzida em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o banner PE com a mesma qualidade de carrossel da home (Priority: P1)

Visitante abre `/para-empresas` e o hero carrossel tem transição, overlays e setas com a mesma qualidade perceptível do carrossel da home, com imagens reais (sem placeholder genérico) e CTAs utilizáveis.

**Why this priority**: Primeira impressão B2B; divergência atual vs. home/Figma é o maior gap visual.

**Independent Test**: Comparar side-by-side o carrossel de `/` e `/para-empresas` (transição, setas, overlays); avançar slides; inspecionar ausência de placeholder `500×600`.

**Acceptance Scenarios**:

1. **Given** dois ou mais slides publicados no display PE, **When** o visitante navega por setas/indicadores, **Then** a transição e as setas têm comportamento equivalente ao carrossel da home (mesma library ou mesmo contrato visual/funcional documentado no plan).
2. **Given** a página carregada, **When** observa as artes do hero, **Then** não vê placeholder genérico `500×600`; a imagem exibida é a arte do banner (desktop e, quando existir, variante mobile).
3. **Given** viewport desktop, **When** observa os CTAs do banner, **Then** “Cadastrar Empresa” e o CTA secundário do hero permanecem acionáveis e legíveis sobre/ao lado da composição, sem regressão do layout duas colunas.

---

### User Story 2 — Visitante mobile usa CTAs do banner com largura uniforme (Priority: P1)

Em tela `<768px`, os botões do banner ficam com a mesma largura efetiva e lado a lado com espaçamento quando couber; se o espaço for insuficiente, empilham com espaçamento uniforme — sem sobrepor a arte de forma ilegível.

**Why this priority**: Pedido explícito do Figma/mobile; breakpoint atual (992px) gera layout inconsistente com o restante do site.

**Independent Test**: Abrir `/para-empresas` em viewport 402×872 e em ~767px; medir larguras dos dois botões e o gap.

**Acceptance Scenarios**:

1. **Given** viewport `<768px` com espaço horizontal suficiente para dois botões legíveis, **When** a página carrega, **Then** os CTAs do banner ficam lado a lado, com larguras iguais e espaçamento uniforme entre eles.
2. **Given** viewport muito estreito onde lado a lado comprometeria legibilidade, **When** a página carrega, **Then** os CTAs empilham com espaçamento vertical uniforme e largura plena (ou largura igual entre si).
3. **Given** viewport `≥768px`, **When** a página carrega, **Then** o comportamento desktop do overlay/posição dos CTAs permanece íntegro (sem forçar o layout mobile).

---

### User Story 3 — Visitante vê Nossos Diferenciais alinhados ao Figma (Priority: P1)

Visitante rola até “Nossos Diferenciais” e vê a foto alinhada ao topo do texto lateral, com anel verde atrás e círculo azul escuro na frente; o texto descritivo usa `#4E4E4E`. No mobile, as decorações complexas não geram scroll horizontal nem sobreposição quebrada.

**Why this priority**: Composição central da narrativa B2B e anexo visual explícito do pedido.

**Independent Test**: Comparar desktop com o Figma (alinhamento topo + camadas); medir cor do texto descritivo; testar viewport `<768px` sem overflow horizontal.

**Acceptance Scenarios**:

1. **Given** viewport desktop, **When** observa a seção, **Then** o topo da imagem principal alinha-se ao topo do bloco de texto/lista lateral (±4px) e o anel verde fica atrás da foto enquanto o círculo azul escuro fica na frente.
2. **Given** o texto descritivo da seção (e itens, conforme design), **When** inspeciona a cor computada, **Then** o valor é `#4E4E4E`.
3. **Given** viewport `<768px`, **When** a seção renderiza, **Then** as formas geométricas absolutas complexas estão ocultas ou estáticas, a imagem flui acima dos textos e não há scroll horizontal causado pela seção.

---

### User Story 4 — Visitante navega Depoimentos pelos bullets (Priority: P1)

Visitante usa os dots abaixo do carrossel de depoimentos e cada clique leva ao depoimento correspondente; o bullet ativo reflete visualmente o item central.

**Why this priority**: Relato de paginação inativa/dessincronizada; afeta confiança no conteúdo social proof.

**Independent Test**: Clicar cada bullet em sequência e confirmar troca do card central + estado `ativo` coerente; validar também swipe/arraste se disponível.

**Acceptance Scenarios**:

1. **Given** 4 depoimentos publicados, **When** o visitante clica no bullet N, **Then** o card central passa a ser o depoimento N e o bullet N fica visualmente ativo (e os demais inativos).
2. **Given** navegação por swipe/gesto (quando suportado), **When** o slide central muda, **Then** o bullet ativo atualiza em sincronia, sem atraso perceptível de estado errado.
3. **Given** o design do Figma, **When** observa o bullet ativo, **Then** o contraste/cor o distingue claramente dos inativos (alinhado ao token de destaque do design desta seção).

---

### User Story 5 — Empresa autenticada vê CTAs condicionais no bloco final (Priority: P1)

Usuário com role `empresa` vê o botão esquerdo desabilitado (“Empresa Cadastrada”) e o botão direito “Abrir Vaga” levando à criação de vaga. Visitante anônimo (ou sem role `empresa`) vê “Cadastrar Empresa” ativo e **não** vê “Abrir Vaga”.

**Why this priority**: Controle de acesso e conversão B2B; evita cadastrar empresa já cadastrada e esconde ação privilegiada.

**Independent Test**: Comparar `/para-empresas` em três sessões — anônimo, autenticado sem `empresa`, autenticado com `empresa`.

**Acceptance Scenarios**:

1. **Given** visitante anônimo, **When** rola até o CTA final, **Then** vê o botão “Cadastrar Empresa” como link para `/cadastro/empresa` e **não** vê o botão “Abrir Vaga”.
2. **Given** usuário autenticado **sem** a role `empresa`, **When** observa o CTA, **Then** o comportamento do botão 1 permanece como link de cadastro e o botão “Abrir Vaga” **não** é renderizado.
3. **Given** usuário autenticado **com** a role `empresa`, **When** observa o CTA, **Then** o botão 1 aparece desabilitado/não navegável com texto indicativo (ex. “Empresa Cadastrada”) em estilo cinza-alaranjado fosco, e o botão 2 “Abrir Vaga” é exibido e leva à rota canônica de nova vaga.
4. **Given** qualquer imagem pesada associada ao CTA (se houver), **When** a página carrega, **Then** a imagem usa carregamento preguiçoso nativo (`loading="lazy"`).

---

### User Story 6 — Deploy reproduz labels do CTA e refinamentos sem passo manual (Priority: P1)

Homologação/produção recebem labels/links corrigidos do CTA PE (quando aplicável) e os demais refinamentos apenas com o fluxo padrão de deploy.

**Why this priority**: Regra não-negociável de automação de DB/config do projeto.

**Independent Test**: Em ambiente desatualizado, `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e validar `/para-empresas`.

**Acceptance Scenarios**:

1. **Given** código e config atualizados, **When** executado o fluxo padrão, **Then** o CTA PE exibe “Abrir Vaga” (quando a role permite) e os refinamentos visuais/JS sem edição manual no admin.
2. **Given** o `hook_update_N` já executado, **When** `drush updb` roda de novo, **Then** não há duplicação nem sobrescrita de copy editorial divergente além do legado conhecido (“Falar com Especialista” → “Abrir Vaga”).
3. **Given** alterações estruturais na origem, **When** `drush cex` é executado, **Then** configs alteradas aparecem em `config/sync`.

### Edge Cases

- Usuário autenticado com múltiplas roles incluindo `empresa` → trata-se como empresa (botão 1 desabilitado + botão 2 visível).
- Usuário autenticado só com `estudante` (ou outra role) → igual ao “sem empresa”: cadastro ativo, sem “Abrir Vaga”.
- CTA com `field_link_2` vazio no banco → não renderiza botão 2 mesmo para empresa (falha segura); o hook só preenche se estiver no legado conhecido ou vazio no seed PE.
- Carrossel de depoimentos com 1 item → bullets omitidos ou único ativo sem erro de JS.
- Carrossel de banner com 1 slide → setas/indicadores ocultos ou inativos sem quebrar layout.
- Reexecução do hook → no-op seguro.
- Numeração do próximo `hook_update_N` pode mudar se outro update for commitado antes da implementação (hoje previsto `11076` após `11075`).
- Viewport intermediário (~768–991px) → banner usa regras desktop a partir de 768px; não manter o corte legado em 992px para empilhar CTAs.

## Requirements *(obrigatório)*

### Functional Requirements

**Banner**

- **FR-001**: O carrossel `#banner-carousel-para-empresas` DEVE oferecer transição, overlays e setas de navegação com paridade funcional/visual ao carrossel da home, preferindo reutilizar a mesma library/inicialização quando isso não destruir o layout específico de PE.
- **FR-002**: Em viewports `<768px`, os CTAs do banner DEVEM ter a mesma largura efetiva entre si e disposição lado a lado com espaçamento uniforme quando legíveis; caso contrário, empilhar com espaçamento uniforme.
- **FR-003**: Em viewports `≥768px`, o layout desktop do banner (incluindo posição dos CTAs) NÃO DEVE regredir.
- **FR-004**: A arte do banner NÃO DEVE exibir placeholder genérico `500×600`; imagens DEVEM ser as do conteúdo publicado, com tratamento responsivo (fonte mobile quando disponível).
- **FR-005**: Seletores CSS que ainda dependem de `body.node--type-para-empresas` DEVEM ser atualizados para o tipo de página atual (`page` / classes de body vigentes), sem afetar outras rotas indevidamente.

**Nossos Diferenciais**

- **FR-006**: No desktop, a imagem principal DEVE alinhar-se ao topo do texto/lista lateral e as formas geométricas DEVEM manter profundidade: anel verde atrás da foto, círculo azul escuro na frente.
- **FR-007**: A estratégia de camadas DEVE privilegiar wrapping relativo e pseudo-elementos/elementos decorativos com empilhamento explícito e mínimo (evitar “z-index wars” espalhados por vários ancestrais).
- **FR-008**: A cor do texto descritivo alvo desta seção (conforme Figma: parágrafo sob o título e/ou descrições dos itens) DEVE ser `#4E4E4E`.
- **FR-009**: Em viewports `<768px`, posicionamento absoluto complexo das decorações DEVE ser desabilitado (ocultar ou estático) para a imagem fluir acima dos textos sem overflow horizontal.

**Depoimentos**

- **FR-010**: Cada controle de paginação (bullet) DEVE navegar para o depoimento correspondente ao seu índice.
- **FR-011**: O estado visual ativo do bullet DEVE permanecer sincronizado com o depoimento central após clique, swipe ou inicialização.
- **FR-012**: O destaque do bullet ativo DEVE ser perceptivelmente distinto dos inativos e coerente com o design de referência desta seção.

**CTA final**

- **FR-013**: Qualquer imagem do bloco CTA nesta página DEVE usar `loading="lazy"` (quando renderizada como `<img>`).
- **FR-014**: Se o usuário atual estiver autenticado e possuir a role `empresa`, o botão esquerdo (“Cadastrar Empresa”) DEVE ser renderizado como controle não navegável (desabilitado ou equivalente) com estilo fosco cinza-alaranjado e texto indicativo de empresa já cadastrada.
- **FR-015**: Caso contrário (anônimo ou sem role `empresa`), o botão esquerdo DEVE permanecer como link para `/cadastro/empresa` com o rótulo de cadastro.
- **FR-016**: O botão direito DEVE ter o rótulo “Abrir Vaga” e DEVE ser renderizado **somente** quando o usuário tiver a role `empresa`, apontando para a rota canônica de criação de vaga.
- **FR-017**: Visitantes sem a role `empresa` NÃO DEVEM receber o markup do botão “Abrir Vaga” (ocultação no servidor, não apenas CSS).

**Backend / Deploy**

- **FR-018**: Se a correção de rótulo/URI do CTA depender de campos da instância `cta_v1` de PE, um `hook_update_N` idempotente em `custom_configs.install` (próximo após `11075`) DEVE atualizar “Falar com Especialista” (ou legado equivalente) para “Abrir Vaga” + URI canônica **somente** nesses casos; não sobrescrever copy editorial já divergente de forma intencional.
- **FR-019**: O hook DEVE ser seguro para reexecução (no-op na segunda chamada).
- **FR-020**: Alterações estruturais (displays, placements, visibilidade) DEVEM ser exportadas com `drush cex` → `config/sync` na origem.
- **FR-021**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr`.
- **FR-022**: O `PRD.md` DEVE ser atualizado de forma cirúrgica na seção Para Empresas (CTAs por role + refinamentos).

### Key Entities

- **View `banners`, display `block_para_empresas`**: slides do hero PE (campos de imagem desktop/mobile, CTAs do banner).
- **Block content `nossos_diferenciais`** (instância PE / compartilhada conforme placement atual): título, descrição, itens com ícone + rótulo + descrição.
- **View `depoimentos_carousel`, display `block_depoimentos_empresas`**: cards de depoimento + controles de paginação.
- **Block content `cta_v1`** (instância `default_ctav1paraempresas`): título, subtítulo, `field_link` (botão 1), `field_link_2` (botão 2).
- **Role `empresa`**: determina desabilitação do cadastro e visibilidade de “Abrir Vaga”.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão lado a lado, 100% dos revisores confirmam paridade perceptível de transição/setas/overlays entre o carrossel da home e o de `/para-empresas` (ou documentam equivalência contratual aprovada no plan).
- **SC-002**: Em viewport `<768px`, 100% das medições mostram CTAs do banner com larguras iguais (±2px) e espaçamento uniforme no modo lado a lado ou empilhado adotado.
- **SC-003**: Em desktop, alinhamento topo imagem↔texto de Diferenciais e camadas (verde atrás / azul na frente) passam no checklist visual do Figma em ≥95% dos itens; cor do texto descritivo alvo é `#4E4E4E` em 100% das inspeções.
- **SC-004**: Em mobile, a seção Diferenciais não causa scroll horizontal e as decorações complexas não sobrepõem texto de forma ilegível (0 ocorrências em QA).
- **SC-005**: 100% dos cliques em bullets de Depoimentos navegam para o índice correto; estado ativo permanece correto após cada navegação (0 dessincronias em roteiro de QA com N bullets).
- **SC-006**: Em matriz de 3 perfis (anônimo / autenticado sem `empresa` / com `empresa`), 100% dos estados de botão do CTA final batem com FR-014–FR-017.
- **SC-007**: Fluxo padrão de deploy reproduz os ajustes em ambiente desatualizado sem passo manual no admin; segunda `updb` não duplica nem reescreve copy editorial legítima.
- **SC-008**: Nenhuma regressão visual mensurável na home atribuível ao reuso da library do carrossel; outras seções de PE não pedidas (Metodologia, Benefícios, etc.) sem regressão funcional.

## Assumptions

- **CSS plano (sem Sass)**: o tema `default` publica CSS em `assets/css/*.css` via `default.libraries.yml`. Pedidos de “SCSS” são atendidos com CSS organizado por seção e custom properties locais — sem introduzir compilador.
- **Paridade do carrossel com a home**: significa reutilizar a library `banner_carousel` (ou extrair o contrato compartilhado) adaptando o markup PE; se a adaptação for inviável sem regressão, o plan documentará equivalência de comportamento (transição/setas/overlays) com CSS/JS dedicado alinhado ao da home.
- **Breakpoint mobile do banner**: adota-se **768px** (não 992px) para empilhar/reorganizar CTAs, alinhado ao pedido e ao restante do site.
- **Texto `#4E4E4E`**: aplica-se ao parágrafo descritivo sob “Nossos Diferenciais” e às descrições dos três itens laterais quando o Figma as mostra no mesmo tom cinza; títulos coloridos (Encontramos/Gerenciamos/Desenvolvemos) mantêm as cores de destaque atuais.
- **Depoimentos**: o JS já existe; esta feature trata o bug/relato de UX (navegação/estado/cor) como correção obrigatória mesmo que parcialmente implementada.
- **CTA seed**: na origem o secundário já é “Abrir Vaga”; o hook existe para destinos/ambientes com legado “Falar com Especialista” e para garantir URI canônica.
- **Rota de nova vaga**: `/painel/empresa/vagas/nova` (já usada no seed/banner) é a rota canônica; se o plan encontrar alias oficial diferente, documenta e usa um único destino.
- **Override Twig do CTA**: a lógica de roles vive no template do CTA (genérico com condições ou override da instância PE); campos do bloco continuam sendo a fonte dos rótulos/URIs base.
- **Pacote `mattpocock/skills`**: verificado e **não instalado** neste ambiente (igual à feature `047`). Nenhuma skill desse pacote é usada nesta especificação; se o usuário instalar, pode ser aplicada em `/speckit-plan` / `/speckit-implement`.
- **Numeração do hook**: `custom_configs_update_11076` após `11075`, salvo colisão com outro update commitado antes da implementação.
- Entregáveis de implementação (Twig/CSS/JS/`hook_update_N`) saem em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
