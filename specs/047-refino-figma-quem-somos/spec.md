# Feature Specification: Refino Visual "Quem Somos" — Conformidade com Figma

**Feature Directory**: `specs/047-refino-figma-quem-somos`
**Created**: 2026-11-02
**Status**: Draft
**Input do usuário**: Refatorar e finalizar a página `/quem-somos` (banner, Sobre nós, Missão/Visão, Nossos Diferenciais, Impact in Numbers, CTA final `cta_v1`), eliminando discrepâncias de tipografia, margens, dimensões e cores frente ao layout de referência do Figma (5 anexos de imagem), com ajustes de conteúdo via `hook_update_N` idempotente em `custom_configs.install`, `drush cex` e otimização de imagens (lazy load + Image Styles responsivos), preservando tudo que já está em conformidade nas features `009`–`015`.

## Nota de levantamento (estado atual vs. pedido)

Antes de detalhar o escopo, o levantamento no código confirmou que **parte do pedido já está implementada** pelas features `009`–`015`. Esta spec documenta apenas as **lacunas reais** encontradas, para evitar retrabalho e regressão do que já está correto:

| Seção | Já conforme | Lacuna real a corrigir |
|---|---|---|
| Banner | Copy, cores, botões, `<picture>` com `loading="eager"`/`fetchpriority="high"` no hero | Imagem lateral **aparece no mobile** (deveria ocultar: `d-none d-md-block`) |
| Sobre nós | Anel verde (`::before`) e círculo laranja (`::after`) já existem via CSS puro; `loading="lazy"` já presente na foto | Foto usa URL bruta do arquivo (sem Image Style/derivativo otimizado); padding/gap frente à imagem a validar com régua do Figma |
| Missão/Visão | `min-height: 380px`, overlay escuro, duas colunas `.col-md-6`, `background-size: cover`, `background-position: center` | **Falta `background-attachment: fixed`** (efeito parallax ainda não existe); imagem de fundo sem Image Style |
| Nossos Diferenciais | Grid responsivo, `loading="lazy"` no Image formatter | `image_style` do campo de ícone está **vazio** (serve original); ícones ainda são os provisórios (`icon-1.png`…`icon-8.png`); tamanho fixo em 64px via CSS — Figma admite até 80px |
| Impact in Numbers | `max-width: 1280px`, `padding: 64px 40px`, fundo `#0F172A`, número `#FD7B1A`, texto `#FFFFFF` — **já idêntico ao Figma** | Validar apenas altura renderizada (~212px) — sem mudança estrutural esperada |
| CTA final (`cta_v1`) | Fundo, raio, padding, botão primário laranja já conformes (feature `015`) | Botão secundário **sem contorno visível** (Figma pede estilo *outline*) |

## Escopo

### Inclui

- **Banner** (`views-view-unformatted--banners--block-quem-somos.html.twig` + `banner-quem-somos.css`): ocultar a coluna de imagem em telas `<768px` (`d-none d-md-block`), sem alterar copy, botões ou o comportamento `eager`/`fetchpriority="high"` do LCP.
- **Sobre nós** (`node--quem-somos.html.twig` + `layout-sobre-nos.css`): manter a decoração via `::before`/`::after` (anel verde + círculo laranja) já existente; aplicar um Image Style dedicado (derivativo otimizado, não o arquivo original) na foto `field_imagem`, preservando `loading="lazy"`; validar/ajustar padding do texto para não encostar na imagem conforme régua do Figma.
- **Missão/Visão** (`node--quem-somos.html.twig` + `quem-somos-missao-visao.css`): adicionar `background-attachment: fixed` (efeito parallax) com fallback `scroll` em mobile (limitação conhecida de Safari iOS/alguns Android para `fixed`); aplicar Image Style otimizado na imagem de fundo (`field_imagem_desktop`) em vez do arquivo original; manter overlay escuro e min-height atuais.
- **Nossos Diferenciais** (`block--block-diferenciais-quem-somos.html.twig` + `diferenciais-quem-somos.css` + paragraph `diferencial_simples_p`): substituir os 8 ícones provisórios pelos arquivos finais (fornecidos como assets versionados em `modules/custom/custom_configs/assets/diferenciais-quem-somos/`); configurar `image_style` do campo `field_image` (hoje vazio) com um Image Style dedicado de alta resolução; aumentar a área de exibição do ícone para `80×80px` no desktop e `64×64px` no mobile; validar alinhamento/centralização do grid de 2×4 colunas.
- **Impact in Numbers**: nenhuma mudança estrutural esperada — apenas verificação de regressão (altura renderizada ≈212px, cores e paddings mantidos).
- **CTA final (`cta_v1`)**: ajustar o botão secundário para estilo *outline* (borda visível, ex. `1px solid #0F172A`), mantendo fundo branco e demais tokens já aprovados na feature `015`.
- **Geral — performance de imagens**: todas as imagens da página, exceto o hero do banner (LCP), DEVEM usar `loading="lazy"` (já é o caso hoje) e DEVEM ser servidas por Image Style/derivativo (nunca o arquivo original) — a lacuna real está nos campos sem `image_style` configurado (ícones dos diferenciais e foto de Sobre nós).
- **Geral — tipografia**: título/parágrafos das seções acima DEVEM consumir os tokens locais já estabelecidos por seção (`--bqs-*`, `--sin-*`, cores hex fixas documentadas no CSS de cada seção) — ver Assumptions sobre por que não se usa a variável global `--brand-orange`.
- **`hook_update_N`** idempotente em `custom_configs.install` (próximo número livre após `custom_configs_update_11068` → `custom_configs_update_11069`) que: (a) copia os ícones finais para `public://` e atualiza os 8 itens seedados do bloco `diferenciais_quem_somos` (`field_image`) somente se ainda apontarem para os ícones provisórios ou estiverem vazios; (b) garante o `image_style` novo nas displays de `field_image` (paragraph `diferencial_simples_p`) e `field_imagem`/`field_imagem_desktop` (node `quem_somos`) via Entity API/Config API; (c) confere que os textos seed do node `quem_somos` (título, corpo, missão, visão) batem com o copy de validação visual do Figma, preenchendo apenas o que estiver vazio — **nunca sobrescrevendo conteúdo editorial divergente**.
- Export via `drush cex` ao final do desenvolvimento no ambiente de origem, versionando os novos Image Styles e displays alterados em `config/sync`.
- Atualização cirúrgica do `PRD.md` (seção da página Quem Somos) refletindo o estado final (ícones, Image Styles, parallax).

### Fora

- Alterações em `core/` ou `vendor/`.
- Criação de novo Content Type, Block Type ou Paragraph Type (todos já existem desde as features `009`–`015`).
- Mudança de copy/texto do banner, botões, títulos de Missão/Visão ou CTA (a não ser para igualar o que já está seedado, se divergente).
- Reescrita do pipeline de build do tema para introduzir um compilador SCSS (o tema `default` hoje publica CSS puro diretamente em `assets/css/*.css` referenciado em `default.libraries.yml`; não há `.scss` nem Gulp/Webpack no projeto — ver Assumptions).
- Alteração do bloco de Números (`impact_numbers_quem_somos`) além de verificação de regressão — já está em conformidade.
- Qualquer alteração em `/para-empresas`, `/para-estudantes`, home ou outras rotas.
- Entrega de código (PHP/Twig/CSS) nesta etapa de especificação — produzida em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante mobile não carrega a imagem do banner (Priority: P1)

Visitante acessa `/quem-somos` em um celular; a coluna de imagem do banner fica oculta, poupando banda e evitando layout quebrado, mantendo copy e botões íntegros.

**Why this priority**: Pedido explícito do Figma; afeta diretamente o peso de dados no mobile (maior parte do tráfego).

**Independent Test**: Abrir `/quem-somos` em viewport `≤767.98px` e confirmar ausência de requisição/renderização da imagem do banner.

**Acceptance Scenarios**:

1. **Given** viewport mobile (`<768px`), **When** a página carrega, **Then** a coluna `.banner-quem-somos__media` não é renderizada/visível (classe `d-none d-md-block` ou equivalente) e nenhuma requisição de imagem do banner ocorre.
2. **Given** viewport desktop (`≥768px`), **When** a página carrega, **Then** a imagem do banner aparece normalmente com `loading="eager"` e `fetchpriority="high"`, sem alteração de comportamento.
3. **Given** viewport mobile, **When** o visitante observa copy e botões do banner, **Then** título, texto e CTAs permanecem idênticos ao desktop (apenas a imagem some).

---

### User Story 2 — Visitante vê a foto de "Sobre nós" otimizada e com as decorações corretas (Priority: P1)

Visitante rola até "Sobre nós" e vê a foto com anel verde (topo) e círculo laranja (base), servida em tamanho otimizado (não o arquivo original pesado), sem o texto colar na imagem.

**Why this priority**: Impacta performance de carregamento (peso de imagem) e fidelidade visual da primeira seção do node.

**Independent Test**: Inspecionar a URL da imagem renderizada (deve apontar para um derivativo de Image Style, não para o caminho original em `public://quem-somos/imagem/...`) e medir visualmente o espaçamento texto↔imagem contra o Figma.

**Acceptance Scenarios**:

1. **Given** a seção "Sobre nós" renderizada, **When** inspeciona o atributo `src` da foto, **Then** a URL aponta para um estilo de imagem derivado (ex. `/files/styles/<nome>/...`), não para o arquivo original.
2. **Given** a foto renderizada, **When** observa o topo-direito e a base-direita, **Then** vê o anel verde (`::before`) e o círculo laranja (`::after`) posicionados sobre a borda da imagem, sem gerar scroll horizontal.
3. **Given** viewport desktop, **When** mede o espaço entre o bloco de texto e a imagem flutuante, **Then** o padding/margem bate com a régua do Figma (±4px), sem o texto tocar a borda da imagem.
4. **Given** a foto, **When** inspeciona o atributo `loading`, **Then** o valor é `lazy` (seção abaixo da dobra).

---

### User Story 3 — Visitante vê o efeito parallax em Missão/Visão (Priority: P1)

Visitante rola até "Nossa Missão / Nossa Visão" em desktop e percebe o fundo se mover em velocidade diferente do scroll (parallax), com overlay escuro garantindo legibilidade do texto branco.

**Why this priority**: É o efeito visual mais citado no pedido do usuário e o que mais diverge hoje (atualmente o fundo rola junto com o conteúdo).

**Independent Test**: Em viewport desktop ≥992px, rolar a página sobre a seção e observar se o fundo permanece fixo relativo à viewport enquanto o conteúdo rola.

**Acceptance Scenarios**:

1. **Given** viewport desktop, **When** o visitante rola a página com a seção visível, **Then** a propriedade computada `background-attachment` da seção é `fixed` e o fundo não acompanha o scroll do conteúdo (efeito parallax perceptível).
2. **Given** viewport mobile (onde `background-attachment: fixed` é inconsistente/pode travar o scroll em alguns navegadores), **When** a seção é observada, **Then** o fundo usa `scroll` (fallback) sem travar ou gerar *jank* no scroll da página.
3. **Given** a seção renderizada em qualquer viewport, **When** observa o texto de Missão e Visão, **Then** o overlay escuro garante contraste suficiente (texto branco legível) e o `min-height: 380px` é respeitado.
4. **Given** a imagem de fundo, **When** inspeciona a URL referenciada em `background-image`, **Then** aponta para um Image Style derivado (não o arquivo original).

---

### User Story 4 — Visitante vê os ícones finais e maiores em Nossos Diferenciais (Priority: P2)

Visitante vê os 8 diferenciais com os ícones definitivos do Figma, maiores e nítidos, alinhados em grid 2×4 (mobile/desktop) com títulos e subtextos centralizados.

**Why this priority**: Melhoria visual relevante, mas a seção já funciona corretamente hoje (grid, lazy load) — o ganho é de fidelidade de assets, não de funcionalidade quebrada.

**Independent Test**: Comparar visualmente os 8 ícones renderizados em `/quem-somos` com o Figma; medir a caixa do ícone (deve ser 80×80px desktop / 64×64px mobile).

**Acceptance Scenarios**:

1. **Given** o bloco "Nossos Diferenciais" publicado, **When** a página carrega em desktop, **Then** os 8 ícones exibidos são os arquivos finais (não mais `icon-1.png`…`icon-8.png` provisórios) em caixa de 80×80px.
2. **Given** viewport mobile, **When** a página carrega, **Then** os ícones aparecem em caixa de 64×64px, sem distorcer proporção (nitidez preservada — sem upscaling visível).
3. **Given** o campo de ícone, **When** inspeciona a URL da imagem, **Then** aponta para um Image Style de alta resolução (não o arquivo original) e mantém `loading="lazy"`.
4. **Given** o grid de 8 itens, **When** observado em qualquer viewport, **Then** título e subtexto de cada card permanecem centralizados e alinhados entre si (mesma altura de bloco por linha).

---

### User Story 5 — Visitante vê o botão secundário do CTA final com contorno (Priority: P2)

Visitante rola até "Seu próximo estágio começa aqui" e vê dois botões: um laranja sólido ("Buscar vagas") e um com contorno visível ("Cadastrar gratuitamente"), ambos com a mesma hierarquia visual do Figma.

**Why this priority**: Ajuste visual pontual — o bloco já está estruturalmente correto (dimensões, cores de fundo, tipografia) desde a feature `015`.

**Independent Test**: Inspecionar o botão secundário do CTA e confirmar presença de borda visível.

**Acceptance Scenarios**:

1. **Given** o bloco CTA v1 renderizado, **When** observa o botão "Cadastrar gratuitamente", **Then** vê uma borda sólida visível (ex. `1px solid #0F172A`) delimitando o botão sobre o fundo `#D3E4FE` do card.
2. **Given** o botão primário "Buscar vagas", **When** observado, **Then** permanece com fundo `#FD7B1A` e texto branco, sem alteração.
3. **Given** viewport mobile, **When** a página carrega, **Then** os botões continuam empilhados e o contorno do botão secundário permanece visível.

---

### User Story 6 — Deploy automatizado reproduz ícones, Image Styles e textos seed (Priority: P1)

Homologação/produção recebem os ícones finais, os novos Image Styles e os textos de validação do node `quem_somos` apenas com o fluxo padrão de deploy — zero passos manuais no admin.

**Why this priority**: Requisito não-negociável do projeto (regra `drupal-deploy-configs`); sem isso, nenhuma das correções visuais chega a produção de forma confiável.

**Independent Test**: Em ambiente desatualizado, rodar `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` e validar `/quem-somos` sem intervenção manual.

**Acceptance Scenarios**:

1. **Given** código e config atualizados, **When** executado o fluxo padrão de deploy, **Then** `/quem-somos` exibe os ícones finais, o parallax, a foto otimizada e o botão outline — sem edição manual no painel.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda novamente, **Then** não há duplicação de arquivos/ícones nem sobrescrita de texto editorial divergente do seed (idempotência).
3. **Given** um editor já tiver alterado manualmente o texto do node `quem_somos` (divergente do seed), **When** o hook roda, **Then** o texto editorial é preservado — o hook não sobrescreve.
4. **Given** as alterações estruturais (Image Styles, displays) geradas na origem, **When** `drush cex` é executado, **Then** os novos `image.style.*.yml` e displays alterados aparecem versionados em `config/sync`.

### Edge Cases

- Ícone do diferencial ausente/vazio em um dos 8 itens → card renderiza sem imagem quebrada (placeholder ou espaço reservado), sem quebrar o grid.
- `field_imagem` (Sobre nós) ou `field_imagem_desktop` (Missão/Visão) vazios → seções já tratam isso hoje (`show_missao_visao` condicional); Image Style novo não deve introduzir erro quando o campo está vazio.
- Navegador sem suporte a `background-attachment: fixed` (a maioria dos mobile browsers) → fallback `scroll` sem quebra de legibilidade/contraste.
- Reexecução do `hook_update_N` em ambiente já migrado → no-op seguro (não duplica cópia de assets em `public://`, não reseta ícones já trocados, não sobrescreve texto divergente).
- Conexão lenta/3G → todas as imagens abaixo da dobra (`loading="lazy"`) não bloqueiam o LCP do banner.
- Numeração do próximo `hook_update_N` pode mudar se outro update for commitado antes da implementação (hoje `11069`, após `11068`).

## Requirements *(obrigatório)*

### Functional Requirements

**Geral — performance e tipografia**

- **FR-001**: Toda imagem `<img>` da página `/quem-somos`, exceto a imagem do hero do banner (LCP), DEVE manter/ter o atributo `loading="lazy"` (banner hero permanece `loading="eager"` + `fetchpriority="high"`).
- **FR-002**: Toda imagem exibida via campo de imagem (ícones dos diferenciais, foto de "Sobre nós", fundo de Missão/Visão) DEVE ser servida por um Image Style/derivativo dedicado — nunca o arquivo original — para reduzir o peso transferido.
- **FR-003**: Títulos e parágrafos de cada seção DEVEM continuar usando os tokens tipográficos/cores locais já estabelecidos por seção no CSS do tema (ex. `--bqs-*` no banner, `--sin-*` nos números, hex fixos documentados em cada arquivo `.css`); nenhuma seção desta página deve passar a depender da variável global `--brand-orange` (valor diferente do Figma desta página — ver Assumptions).

**Banner**

- **FR-004**: A coluna de imagem do banner (`.banner-quem-somos__media`) DEVE ficar oculta em viewports `<768px` (`d-none d-md-block` ou equivalente) e visível a partir de `≥768px`.
- **FR-005**: Copy, botões primário/secundário e o comportamento `eager`/`fetchpriority="high"` da imagem em desktop NÃO DEVEM ser alterados por este ajuste.

**Sobre nós**

- **FR-006**: A foto de `field_imagem` DEVE ser renderizada através de um Image Style dedicado (dimensão compatível com a caixa de exibição ~`432×269px`, considerando densidade 2x), mantendo `loading="lazy"`.
- **FR-007**: A decoração visual (anel verde superior via `::before`, círculo laranja inferior via `::after`) DEVE continuar implementada em CSS puro sobre o wrapper da imagem, com posicionamento absoluto — sem introduzir markup HTML extra para os elementos decorativos.
- **FR-008**: O padding/margem do bloco de texto em relação à imagem flutuante DEVE ser validado e ajustado para não encostar na imagem, respeitando a medida do Figma (anexo 2).

**Missão/Visão**

- **FR-009**: Em viewports `≥768px`, a seção `.quem-somos-missao-visao` DEVE aplicar `background-attachment: fixed` à imagem de `field_imagem_desktop`, mantendo `background-size: cover` e `background-position: center` já existentes.
- **FR-010**: Em viewports `<768px`, a seção DEVE usar `background-attachment: scroll` (fallback), evitando o comportamento inconsistente/travamento de scroll do `fixed` em navegadores móveis.
- **FR-011**: O overlay escuro semitransparente sobre a imagem (já implementado via `::before`) e o `min-height: 380px` DEVEM ser mantidos para garantir legibilidade do texto branco.
- **FR-012**: A imagem de fundo DEVE ser servida via Image Style dedicado (não o arquivo original).

**Nossos Diferenciais**

- **FR-013**: Os 8 ícones seedados no bloco `diferenciais_quem_somos` DEVEM ser substituídos pelos arquivos finais (assets versionados em `modules/custom/custom_configs/assets/diferenciais-quem-somos/`), preservando a ordem/rótulos atuais (Pessoas, Autoconhecimento, Conexões, Respeito, Compromisso, Desenvolvimento, Performance, Ética).
- **FR-014**: O campo `field_image` (paragraph `diferencial_simples_p`) DEVE ter um `image_style` configurado na view display (hoje vazio) apontando para um Image Style de alta resolução dedicado a ícones.
- **FR-015**: A caixa de exibição do ícone DEVE medir `80×80px` em desktop (`≥768px`) e `64×64px` em mobile (`<768px`), sem distorcer a proporção original do ícone.
- **FR-016**: O grid de 8 itens DEVE manter 2 colunas no mobile e 4 colunas no desktop, com título e subtexto de cada card centralizados e alinhados entre si.

**Impact in Numbers**

- **FR-017**: Nenhuma alteração estrutural é esperada nesta seção; a implementação DEVE validar, por regressão visual, que `max-width: 1280px`, `padding: 64px 40px`, fundo `#0F172A`, número `#FD7B1A` e texto `#FFFFFF` permanecem inalterados após as mudanças das demais seções.

**CTA final (`cta_v1`)**

- **FR-018**: O botão secundário ("Cadastrar gratuitamente") DEVE exibir uma borda sólida visível (ex. `1px solid #0F172A`) sobre o fundo `#D3E4FE` do card, caracterizando o estilo *outline* pedido no Figma.
- **FR-019**: O botão primário ("Buscar vagas") e os demais tokens do card (fundo `#D3E4FE`, raio `32px`, padding `64px`, largura máxima `1200px`) NÃO DEVEM ser alterados — já aprovados na feature `015`.

**Backend / Deploy**

- **FR-020**: Um `hook_update_N` idempotente em `custom_configs.install` (próximo número livre após `custom_configs_update_11068`) DEVE, usando Entity API (`\Drupal::entityTypeManager()`) e Config API: (a) copiar os ícones finais de `modules/custom/custom_configs/assets/diferenciais-quem-somos/` para `public://` e atualizar o `field_image` dos 8 parágrafos seedados **somente se** ainda apontarem para os ícones provisórios (`icon-1.png`…`icon-8.png`) ou estiverem vazios; (b) garantir o(s) novo(s) Image Style(s) e aplicá-los nas displays de `field_image` (diferenciais), `field_imagem` (Sobre nós) e `field_imagem_desktop` (Missão/Visão) via Config API, somente se ainda não aplicados; (c) conferir que os textos seed do node `quem_somos` (título, corpo de "Sobre nós", Missão, Visão) existem e preencher **apenas os campos vazios**, nunca sobrescrevendo conteúdo editorial já divergente do seed.
- **FR-021**: O hook DEVE ser seguro para reexecução (no-op na segunda chamada): sem duplicar arquivos em `public://`, sem resetar ícones já trocados, sem sobrescrever texto editorial divergente.
- **FR-022**: Toda alteração estrutural desta feature (novos Image Styles, displays alterados) DEVE ser exportada via `drush cex` para `config/sync` e versionada no Git, ao final do desenvolvimento no ambiente de origem.
- **FR-023**: O fluxo de deploy documentado para esta feature DEVE ser o padrão do projeto: `git pull` → `drush cim -y` → `drush updb -y` → `drush cim -y` → `drush cr` (import antes do update; segunda passada de `cim` após `updb` cobre displays/estilos que dependem de config seedada pelo hook).
- **FR-024**: A seção relevante do `PRD.md` (página Quem Somos) DEVE ser atualizada de forma cirúrgica refletindo os ícones finais, os novos Image Styles e o efeito parallax.

### Key Entities

- **Node `quem_somos`** (view mode full): título, corpo ("Sobre nós" via `field_imagem` + `field_text_long_formatted`), Missão/Visão (`field_text_simple_long`, `field_text_simple_long_2`, `field_imagem_desktop`).
- **Block `diferenciais_quem_somos`** (block_content): título, descrição, `field_itens_lista` → 8 parágrafos `diferencial_simples_p` (ícone + rótulo + subtexto).
- **Block `impact_numbers_quem_somos`** (plugin derivado de `field_numeros_lista` do node): 4 itens estatísticos — sem alteração nesta feature, apenas validação.
- **Block `cta_v1`**: título, subtítulo, botão primário (`field_link`), botão secundário (`field_link_2`) — ajuste apenas no estilo do botão secundário.
- **Image Style(s) novos**: derivativo dedicado para ícones de diferenciais (alta resolução, ex. `160×160px` fonte para exibição `80×80px` em telas de alta densidade) e derivativo(s) para a foto de "Sobre nós" e para o fundo de Missão/Visão.
- **View `banners`, display `block_quem_somos`**: fonte da imagem do banner — sem alteração de Image Style (decisão deliberada preservada, ver Assumptions); apenas visibilidade responsiva da coluna de mídia.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em viewport mobile (`<768px`), 100% das verificações confirmam ausência de requisição de rede para a imagem do banner.
- **SC-002**: Em revisão visual desktop, a seção "Sobre nós" bate com o Figma (padding texto↔imagem, anel verde, círculo laranja) em ≥95% dos itens do checklist visual; a URL da foto aponta para um Image Style derivado.
- **SC-003**: Em viewport desktop, o efeito parallax em Missão/Visão é confirmado via inspeção de `background-attachment: fixed` computado; em mobile, `scroll` é usado sem travar o scroll da página.
- **SC-004**: 100% dos 8 ícones de "Nossos Diferenciais" exibem os arquivos finais (não mais os provisórios), em caixa de `80×80px` desktop / `64×64px` mobile, servidos por Image Style (não arquivo original).
- **SC-005**: A seção "Impact in Numbers" não sofre nenhuma regressão visual mensurável (cores, padding, max-width) após o deploy desta feature.
- **SC-006**: O botão secundário do CTA final exibe contorno visível em 100% das verificações, sem alterar o botão primário.
- **SC-007**: Em ambiente desatualizado, o fluxo padrão de deploy (`git pull` + `drush cim -y` + `drush updb -y` + `drush cim -y` + `drush cr`) reproduz 100% dos ajustes acima sem nenhum passo manual no painel admin.
- **SC-008**: Segunda execução de `drush updb` não duplica ícones/arquivos nem sobrescreve texto editorial divergente (idempotência confirmada).
- **SC-009**: Nenhuma página fora de `/quem-somos` (home, `/para-empresas`, `/para-estudantes`) sofre alteração visual mensurável por esta feature.

## Assumptions

- **Sem pipeline SCSS**: o tema `default` publica CSS puro em `assets/css/*.css`, referenciado via `default.libraries.yml` — não há `.scss`, Sass ou bundler no projeto. O pedido de "SCSS limpo" do usuário é atendido escrevendo **CSS plano, organizado por seção, com baixo aninhamento e custom properties (`--token`) como variáveis locais**, seguindo o padrão já usado em `banner-quem-somos.css`, `impact-numbers.css` etc. Introduzir um compilador Sass está fora de escopo (mudança de infraestrutura de build, não pedida explicitamente e fora do essencial desta correção visual).
- **Tokens de cor por seção, não globais**: a variável global `--brand-orange` (`#ff8a22`) é usada por outras páginas do site e **diverge** do laranja do Figma desta página (`#fd7b1a`). Cada seção de Quem Somos já define tokens locais (`--bqs-orange`, `--sin-stat`, hex fixos) desde as features `009`–`015`; esta feature mantém esse padrão em vez de sobrescrever a variável global.
- **Ícones finais**: os arquivos definitivos dos 8 ícones não foram anexados nesta rodada (apenas screenshots do Figma); a implementação (`/speckit-plan` → `/speckit-implement`) irá exportar/receber os assets finais e versioná-los em `modules/custom/custom_configs/assets/diferenciais-quem-somos/`, substituindo `icon-1.png`…`icon-8.png`. Caso os arquivos finais não sejam fornecidos a tempo, o hook mantém os ícones atuais e aplica apenas o Image Style maior.
- **Tamanho do ícone**: adota-se `80×80px` desktop / `64×64px` mobile como padrão desta feature (o pedido do usuário aceitava `64` ou `80` "dependendo da visualização ideal"); o breakpoint de troca é `768px`, consistente com o restante da página.
- **Numeração do hook**: próximo update livre após `custom_configs_update_11068` → `custom_configs_update_11069`, salvo se outro update for commitado antes da implementação.
- **Parallax em mobile**: `background-attachment: fixed` é propositalmente desativado (`scroll`) abaixo de `768px` por limitação conhecida de navegadores móveis (jank/scroll travado), prática padrão de performance — não é regressão, é proteção.
- **Pacote de skills `mattpocock/skills`**: verificado e **não está instalado** neste ambiente (nenhuma skill com esse nome nos diretórios de skills disponíveis). Nenhuma skill desse pacote foi usada nesta especificação; se o usuário confirmar a instalação, ela pode ser aplicada nas fases seguintes (`/speckit-plan`/`/speckit-implement`).
- **Impact Numbers já conforme**: comparação direta entre o CSS atual (`impact-numbers.css`) e os anexos 4/5 do Figma mostrou paridade total de cor/dimensão; não há FR de mudança, apenas validação/regressão.
- Entregáveis de implementação (PHP do `hook_update_N`, Twig, CSS com os tokens do Figma) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
