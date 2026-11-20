# Feature Specification: Página Para Empresas

**Feature Directory**: `specs/019-para-empresas-page`  
**Created**: 2026-09-25  
**Status**: Draft  
**Input do usuário**: Refatorar a página **Para Empresas** (`/para-empresas`) alinhada ao Figma — limpar HTML hardcoded do corpo do nó; novo display de banner (View `banners`, machine name `block_para_empresas`) em carrossel com duas imagens e layout duas colunas (textos/CTA | imagem) com container `max-width: 1200px`; reutilizar blocos existentes **Nossos Diferenciais**, **Nossa Metodologia** e **O Que Fazemos** na região `content_full` exclusivos da rota; seção **Benefícios para Empresas** (reuso se o design couber em tipos já existentes, senão novo Custom Block Type); CTA final reutilizando `cta_v1` (“Pronto para contratar os melhores talentos?”); deploy 100% automatizado via Configuration Management + `hook_update_N` idempotente; regra Cursor permanente de automação de DB.

## Escopo

### Inclui

- Atualização da regra de desenvolvimento `.cursor/rules/drupal-deploy-configs.mdc` fixando que toda alteração que impacte banco (blocos, tipos, campos, views, placements) **deve** vir com `hook_update_N` + `drush cex`, sem esperar o usuário pedir.
- Limpeza do conteúdo HTML obsoleto no corpo do nó canônico de `/para-empresas` (remoção via `hook_update_N`; não deixar “Olá, empresa!” / “Por que anunciar aqui?” hardcoded no body).
- Novo display Block da View `banners` com machine name `block_para_empresas`, filtrado apenas aos banners destinados a esta página; placement na região de banner; exclusividade em `/para-empresas`.
- Remoção de `/para-empresas` do placement legado `banners-block_1` (hoje compartilhado com `/para-estudantes`), para evitar dois banners na mesma rota.
- Seed de **dois** itens de banner (carrossel) com copy alinhada ao Figma (rótulo “SOLUÇÕES CORPORATIVAS”, título “Encontre os melhores talentos para sua empresa.”, CTAs “Cadastrar Empresa” e “Contrate o Estágio Certo”, arte da coluna direita).
- Template Twig isolado do display + CSS/SCSS: container interno `max-width: 1200px`, gap/padding coerentes com Figma (~48px entre colunas, padding horizontal ~24px), duas colunas (textos/CTA à esquerda; imagem à direita); empilhamento no mobile.
- Instâncias **dedicadas** (não reutilizar a mesma instância da home) dos tipos existentes **Nossos Diferenciais** (`nossos_diferenciais`), **Nossa Metodologia** (`nossa_metodologia`) e **O Que Fazemos** (`o_que_fazemos_bt`), posicionadas em `content_full` logo abaixo do banner, com visibilidade **somente** `/para-empresas`.
- Seção **Benefícios para Empresas**: na fase de plan/research, analisar se o design (grade de benefícios com ícone + texto) cabe em tipos já existentes (ex.: padrão de ícone+texto de Diferenciais / O Que Fazemos) apenas alterando título/copy; **se couber**, nova instância do tipo reaproveitado; **se não couber**, criar Custom Block Type `beneficios_empresas` + Paragraph Type correspondente, seed e Twig Bootstrap 5.
- Instância nova do tipo **CTA v1** (`cta_v1`) com título “Pronto para contratar os melhores talentos?”, placement no final da composição de `/para-empresas` (antes do rodapé), região `content_full`.
- Ordem vertical de composição em `/para-empresas`: Banner → Nossos Diferenciais → Nossa Metodologia → O Que Fazemos → Benefícios para Empresas → CTA final.
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11023`, tipicamente `11024`) cobrindo limpeza do body, display/placement do banner, seeds, instâncias e placements com visibilidade por path.
- Exportação estrutural via `drush cex` → `config/sync` ao final do desenvolvimento na origem.
- Atualização cirúrgica do `PRD.md` (blocos / Views / rota `/para-empresas`) refletindo a nova composição.

### Fora

- Redesign do header/footer além do necessário para conviver com a composição.
- Alteração dos placements/instâncias da **home** dos blocos Nossos Diferenciais, Nossa Metodologia e O Que Fazemos (permanecem em `<front>`).
- Redesign das páginas `/quem-somos`, `/contato`, `/para-estudantes` ou home.
- Layout Builder / edição visual de colunas pelo editor.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS/YAML de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o hero corporativo em carrossel (Priority: P1)

Visitante acessa `/para-empresas` e, no topo, vê o banner em carrossel (duas slides) com textos/CTAs à esquerda e imagem à direita, contido em largura máxima de 1200px, alinhado ao Figma — sem o placeholder “1800×600” nem o HTML legado “Olá, empresa!”.

**Why this priority**: É a primeira impressão da oferta B2B e o principal desvio visual atual vs. design.

**Independent Test**: Abrir `/para-empresas` em desktop ≥992px e comparar hero com o Figma; avançar o carrossel para a segunda slide.

**Acceptance Scenarios**:

1. **Given** o display `block_para_empresas` publicado com dois banners, **When** o visitante abre `/para-empresas`, **Then** vê o hero em duas colunas (copy/CTA | imagem) dentro de um container de no máximo 1200px.
2. **Given** o carrossel, **When** navega entre slides, **Then** vê duas imagens/slides distintas sem erro de layout.
3. **Given** a página carregada, **When** inspeciona o conteúdo principal, **Then** não encontra o HTML legado “Olá, empresa!” / “Por que anunciar aqui?” no corpo do nó.
4. **Given** viewport mobile, **When** a página carrega, **Then** as colunas do banner empilham (texto acima da imagem) sem scroll horizontal causado pelo hero.

---

### User Story 2 — Visitante percorre a composição institucional B2B (Priority: P1)

Abaixo do banner, o visitante vê em sequência Nossos Diferenciais, Nossa Metodologia, O Que Fazemos, Benefícios para Empresas e o CTA final — todos exclusivos desta URL.

**Why this priority**: Substitui o body hardcoded pela narrativa de produto aprovada no design.

**Independent Test**: Rolar `/para-empresas` do hero ao rodapé e conferir ordem e presença de cada seção; abrir home e confirmar que as seções da home não sumiram.

**Acceptance Scenarios**:

1. **Given** placements seedados, **When** o visitante rola `/para-empresas`, **Then** encontra as seções na ordem: Diferenciais → Metodologia → O Que Fazemos → Benefícios → CTA.
2. **Given** a mesma sessão, **When** visita a home (`/`), **Then** os blocos originais da home (Diferenciais, Metodologia, O Que Fazemos) permanecem visíveis na home.
3. **Given** `/quem-somos` ou outra rota interna, **When** a página carrega, **Then** nenhum dos novos placements de `/para-empresas` aparece.

---

### User Story 3 — Visitante usa CTAs do hero e do bloco final (Priority: P1)

Visitante aciona os CTAs do banner (“Cadastrar Empresa”, “Contrate o Estágio Certo”) e o CTA final (“Pronto para contratar…”) e é levado a rotas canônicas limpas.

**Why this priority**: Sem conversão, a página não cumpre o objetivo B2B.

**Independent Test**: Clicar em cada CTA seedado e verificar destino.

**Acceptance Scenarios**:

1. **Given** o hero seedado, **When** clica em “Cadastrar Empresa”, **Then** navega para a rota canônica de cadastro de empresa do produto.
2. **Given** o hero seedado, **When** clica em “Contrate o Estágio Certo”, **Then** navega para a URL canônica definida no seed (documentada no plan).
3. **Given** o CTA final `cta_v1`, **When** observa o título, **Then** lê “Pronto para contratar os melhores talentos?” e encontra botões de ação utilizáveis.

---

### User Story 4 — Editor gerencia conteúdo sem código (Priority: P2)

Editor autenticado altera copy/imagens dos banners, itens dos blocos reutilizados, benefícios e CTA; mudanças refletem em `/para-empresas` sem deploy de código.

**Why this priority**: Conteúdo B2B evolui; não pode depender de desenvolvimento após o seed.

**Independent Test**: Editar um banner e o CTA no painel; recarregar a página pública.

**Acceptance Scenarios**:

1. **Given** editor com permissão, **When** edita um dos dois banners do display, **Then** a alteração aparece no carrossel após cache esperado.
2. **Given** editor com permissão, **When** edita o bloco CTA da página, **Then** título/subtítulo/links atualizam em `/para-empresas`.
3. **Given** editor com permissão, **When** edita Benefícios para Empresas, **Then** consegue alterar título e itens (ícone + rótulo + subtexto opcional) sem código.
4. **Given** o paragraph `diferencial_simples_p` sem subtexto (ex. Quem Somos), **When** a seção renderiza, **Then** só ícone + rótulo aparecem — sem linha vazia de subtexto.

---

### User Story 5 — Deploy reproduz a página sem painel manual (Priority: P1)

Homologação/produção recebem limpeza do body, display da View, seeds de banner, instâncias/placements de todos os blocos e estilos apenas com o fluxo padrão de deploy.

**Why this priority**: Diretriz permanente do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/para-empresas` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y` → `drush updb -y` → (2ª `cim` se placements dependerem de UUIDs seedados) → `drush cr`, **Then** `/para-empresas` exibe a composição completa.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de blocos/banners nem reintrodução do HTML legado no body se o editor já tiver conteúdo editorial próprio.
3. **Given** alterações estruturais na origem, **When** `drush cex` é executado, **Then** View display, placements e tipos novos (se houver) aparecem versionados em `config/sync`.

### Edge Cases

- Body do nó já vazio ou já limpo → hook não falha; no-op seguro.
- Banner sem imagem → omitir coluna visual ou placeholder seguro (sem ícone quebrado).
- Apenas um banner publicado → carrossel degrada para slide único sem controles quebrados.
- Zero banners no filtro → não quebrar a página; omitir região de banner ou faixa vazia.
- Campos vazios nos blocos reutilizados → omitir elementos vazios sem fatal error (padrão das features 004–006).
- Reexecução do hook → no-op; **não** sobrescrever editorial divergente do seed; **não** duplicar instâncias/placements.
- Rota diferente de `/para-empresas` → nenhum placement novo desta feature aparece.
- Convivência com `default_ctoparaempresas` (CTO legado) → plan decide se desativa/substitui pelo novo CTA `cta_v1` para evitar dois CTAs concorrentes; default: CTA legado desativado na rota após o novo CTA estar seedado.
- Home e Quem Somos → visualmente inalterados (regressão amostral).

## Requirements *(obrigatório)*

### Functional Requirements

**Governança de deploy**

- **FR-001**: A regra Cursor de deploy DEVE exigir, para qualquer alteração estrutural/DB, entrega automática de `hook_update_N` idempotente + instrução/`drush cex`, sem depender de pedido explícito do usuário.
- **FR-002**: O fluxo de deploy documentado DEVE seguir o padrão do projeto: `git pull` → `drush cim -y` → `drush updb -y` → (2ª `cim` quando placements dependerem de UUIDs seedados) → `drush cr`.

**Limpeza do nó**

- **FR-003**: O `hook_update_N` DEVE remover o HTML hardcoded obsoleto do corpo do nó canônico de `/para-empresas` (conteúdo legado tipo “Olá, empresa!” / grade “Por que anunciar aqui?”), de forma idempotente.
- **FR-004**: A limpeza NÃO DEVE apagar o nó nem o alias `/para-empresas`; apenas o body obsoleto (e campos equivalentes usados só para o legado, se aplicável).

**Banner (View `banners`)**

- **FR-005**: O sistema DEVE oferecer um display Block da View `banners` com machine name `block_para_empresas`.
- **FR-006**: O display DEVE filtrar apenas os banners destinados a `/para-empresas` (critério alinhado ao padrão dos displays `block_home` / `block_quem_somos`).
- **FR-007**: O placement do display DEVE aparecer na região de banner do tema `default`, com visibilidade exclusiva `/para-empresas`.
- **FR-008**: O placement legado `banners-block_1` DEVE deixar de incluir `/para-empresas` (permanece para `/para-estudantes` ou o que o plan confirmar).
- **FR-009**: O seed DEVE cadastrar **dois** banners (slides) para o carrossel desta página.
- **FR-010**: O template Twig do display DEVE renderizar container interno com `max-width: 1200px`, duas colunas (textos/CTA à esquerda; imagem à direita), gap ~48px e padding horizontal ~24px conforme Figma.
- **FR-011**: Em viewport estreita, o banner DEVE empilhar colunas sem causar scroll horizontal.
- **FR-012**: Copy seed do hero DEVE incluir rótulo “SOLUÇÕES CORPORATIVAS”, título “Encontre os melhores talentos para sua empresa.” e CTAs “Cadastrar Empresa” + “Contrate o Estágio Certo” (URLs canônicas documentadas no plan).

**Blocos reutilizados**

- **FR-013**: O hook DEVE criar/garantir instâncias dedicadas dos tipos `nossos_diferenciais`, `nossa_metodologia` e `o_que_fazemos_bt` para `/para-empresas` (distintas das instâncias da home).
- **FR-014**: Cada instância DEVE ter placement em `content_full` com visibilidade `request_path` = `/para-empresas` e weights que preservem a ordem: Diferenciais → Metodologia → O Que Fazemos → Benefícios → CTA.
- **FR-015**: Instâncias e placements da home desses tipos NÃO DEVEM ser removidos nem ter visibilidade alterada por esta feature.

**Benefícios para Empresas**

- **FR-016**: Se o design da seção Benefícios for satisfeito por um tipo existente (ícone + texto / cards), o sistema DEVE reutilizar esse tipo com nova instância e título adequado; caso contrário, DEVE criar Custom Block Type `beneficios_empresas` e Paragraph Type correspondente.
- **FR-016b**: O paragraph `diferencial_simples_p` DEVE expor subtexto opcional (`field_text_simple_long`, label **Subtexto**, `required: false`) para alinhar ao Figma de Benefícios PE; instâncias sem valor (Quem Somos) NÃO DEVEM renderizar a linha.
- **FR-017**: Em qualquer caso, a seção DEVE ser gerenciável no painel, ter Twig com grid Bootstrap 5, placement em `content_full` só em `/para-empresas`, e seed idempotente (5 itens Figma com rótulo + subtexto via `11029`).

**CTA final**

- **FR-018**: O sistema DEVE reutilizar o Custom Block Type `cta_v1` com uma **nova** instância cujo título seed é “Pronto para contratar os melhores talentos?”.
- **FR-019**: O placement do CTA DEVE ser o último bloco de conteúdo da composição de `/para-empresas` em `content_full` (antes do rodapé), visível somente nessa URL.
- **FR-020**: Se o bloco legado `default_ctoparaempresas` (tipo `cto`) permanecer ativo na mesma rota, a implementação DEVE desativá-lo ou removê-lo da região para evitar CTAs duplicados (preferência: desativar placement legado).

**Apresentação e escopo de CSS**

- **FR-021**: Estilos novos do banner e das seções exclusivas DEVEM ter escopo estrito (classes dedicadas do display/bloco), sem alterar visualmente home, Quem Somos, Contato ou rodapé.
- **FR-022**: Tipografia e tokens de cor DEVEM seguir a marca já adotada (Poppins, azul institucional, CTA laranja `#FD7B1A` / equivalente do design system).

**Deploy e documentação**

- **FR-023**: Um `hook_update_N` em `custom_configs` (próximo livre após `11023`, tipicamente `custom_configs_update_11024`) DEVE, de forma **idempotente**, usando Entity API / config API: limpar body legado; garantir display `block_para_empresas` + filtro + placement; seedar dois banners; garantir instâncias/placements dos blocos reutilizados; garantir Benefícios (reuso ou tipo novo); garantir CTA `cta_v1`; ajustar/desativar CTO legado se necessário; nunca duplicar; nunca sobrescrever editorial divergente.
- **FR-024**: Alterações estruturais DEVEM ser exportáveis via `drush cex` e versionadas em `config/sync`.
- **FR-025**: A seção relevante do `PRD.md` DEVE ser atualizada de forma cirúrgica refletindo a composição de `/para-empresas`.

### Key Entities

- **Nó Para Empresas** (`/para-empresas`): página canônica; body limpo após o update; conteúdo visual passa a viver em blocos/View.
- **View `banners` / display `block_para_empresas`**: carrossel do hero; filtro por destino da página; dois itens seed.
- **`nossos_diferenciais`**, **`nossa_metodologia`**, **`o_que_fazemos_bt`**: tipos existentes; novas instâncias + placements só `/para-empresas`.
- **Benefícios para Empresas**: instância reutilizada **ou** tipo `beneficios_empresas` + paragraph (decisão no plan).
- **`cta_v1`**: nova instância com título “Pronto para contratar os melhores talentos?”.
- **Placements**: região `banner` (View) + `content_full` (blocos), tema `default`, `request_path` `/para-empresas`.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, o hero de `/para-empresas` é reconhecível frente ao Figma (duas colunas, container ≤1200px, CTAs, carrossel) em ≥95% dos critérios do checklist visual.
- **SC-002**: Em viewport mobile, 100% das verificações confirmam empilhamento do hero, seções legíveis e ausência de scroll horizontal causado pela composição.
- **SC-003**: 100% das seções da composição (banner + 5 blocos) aparecem na ordem especificada em `/para-empresas` e **não** aparecem em amostragem de outras rotas (home, Quem Somos, Contato).
- **SC-004**: O HTML legado “Olá, empresa!” / “Por que anunciar aqui?” não aparece no body público após o deploy (verificação de conteúdo).
- **SC-005**: Carrossel exibe 2 slides seedados; navegação entre slides funciona em 100% dos testes manuais de aceite.
- **SC-006**: Um editor atualiza copy do CTA ou de um banner em menos de 3 minutos, sem suporte de desenvolvimento.
- **SC-007**: Após o deploy padrão, `/para-empresas` exibe a composição completa sem passos manuais no admin.
- **SC-008**: Segunda execução de `drush updb` não cria blocos/banners duplicados nem reintroduz body legado indevidamente (idempotência confirmada).
- **SC-009**: Home e Quem Somos permanecem visualmente inalterados na amostragem de regressão.

## Assumptions

- Rota canônica permanece `/para-empresas` (Clean URL / alias já existente); não se cria rota paralela.
- Próximo hook: `custom_configs_update_11024` (após `11023`), salvo se outro update for commitado antes da implementação.
- Instâncias dos blocos reutilizados em `/para-empresas` são **novas** (UUIDs fixos distintos das da home); copy seed pode espelhar a home ou adaptar tom B2B — detalhe editorial no plan/Figma.
- Decisão Benefícios (reuso vs `beneficios_empresas`): tomada em `/speckit-plan` após comparar o design com `nossos_diferenciais` / `o_que_fazemos_bt` / paragraphs de ícone+texto; critério: se só muda título/copy e o grid 3 colunas cabe, reutilizar; senão criar tipo novo.
- URLs dos CTAs do hero (“Cadastrar Empresa”, “Contrate o Estágio Certo”) e botões do CTA final serão as rotas canônicas já usadas no produto (ex.: cadastro empresa / fluxo comercial); paths exatos documentados no plan.
- Placement CTO legado `default_ctoparaempresas` será desativado em favor do novo `cta_v1` na mesma rota.
- Região de banner do tema `default` é a mesma usada pelos displays `block_home` / `block_quem_somos`.
- Textos seed desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação (PHP do `hook_update_N`, Twig, SCSS/CSS, YAMLs, `drush cex`) são produzidos em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
