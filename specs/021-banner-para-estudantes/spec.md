# Feature Specification: Banner (Hero) Para Estudantes

**Feature Directory**: `specs/021-banner-para-estudantes`  
**Created**: 2026-09-29  
**Status**: Draft  
**Input do usuário**: Implementar o Banner (Hero) da página **Para Estudantes** (`/para-estudantes`) replicando a arquitetura do banner **Para Empresas** (View `banners`, display Block dedicado, placement com visibilidade por path, seed + `hook_update_N` + `drush cex`), adaptando conteúdo e layout ao Figma da jornada do estudante (duas colunas: textos/CTAs | ilustração; container `max-width: 1280px`; paddings Top `64px` / Right `40px` / Bottom `48px` / Left `40px`).

## Escopo

### Inclui

- Novo display Block da View `banners` com machine name `block_para_estudantes`, filtrado exclusivamente aos banners destinados a `/para-estudantes`.
- Extensão do vocabulário de destino do content type `banners` (`field_local_exibicao`) com o valor `para_estudantes` (label “Para Estudantes”), no mesmo padrão de `para_empresas` / `quem_somos`.
- Placement do bloco `views_block:banners-block_para_estudantes` na região de Hero do tema (`banner` — mesma região usada por `block_para_empresas` / `block_home` / `block_quem_somos`), com visibilidade exclusiva `request_path` = `/para-estudantes`.
- Remoção/desativação do placement legado `banners-block_1` em `/para-estudantes` (hoje o único path desse bloco), para evitar dois heroes na mesma rota.
- Seed idempotente de **um** nó `banners` (hero único, sem carrossel) com copy alinhada ao Figma e imagem destacada (ilustração de cards da plataforma).
- Template Twig isolado do display + CSS/SCSS com wrapper `.hero-estudantes-wrapper` (ou equivalente): `max-width: 1280px`, paddings exatos `64px 40px 48px 40px`, grid Bootstrap 5 duas colunas (`.row.align-items-center`; esquerda `.col-12.col-lg-6` com badge, `h1`, subtítulo e CTAs; direita `.col-12.col-lg-6` com imagem `.img-fluid`).
- Copy seed do hero:
  - Badge: “Plataforma exclusiva para estudantes”
  - Título (`h1`): “Seu futuro profissional começa aqui.”
  - Subtítulo: “Conecte-se com as melhores empresas, descubra oportunidades alinhadas ao seu perfil e dê o primeiro passo para uma carreira de sucesso.”
  - CTA primário (laranja): “Encontrar minha vaga”
  - CTA secundário (outline): “Criar meu perfil”
- `hook_update_N` idempotente em `custom_configs` (próximo livre após `11032`, tipicamente `11033`) cobrindo: valor de `field_local_exibicao`, display da View, placement, seed do banner e desativação do legado `block_1` na rota.
- Exportação estrutural via `drush cex` → `config/sync` ao final do desenvolvimento na origem.
- Asset de seed da ilustração versionado em `modules/custom/custom_configs/assets/` (espelho do arquivo editorial), copiado para `public://` pelo hook — sem commit de `sites/default/files`.
- Atualização cirúrgica do `PRD.md` (Views / rota `/para-estudantes`) refletindo o novo display.

### Fora

- Redesign das seções abaixo do hero em `/para-estudantes` (“Encontre oportunidades…”, grade de benefícios, listagem de vagas, formulário exposto).
- Redesign do header/footer além do necessário para conviver com o novo hero.
- Alteração dos displays/placements de home, Quem Somos, Para Empresas ou Contato.
- Carrossel multi-slide nesta página (o Figma apresenta um único hero).
- Layout Builder / edição visual de colunas pelo editor.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS/YAML de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o hero de estudantes alinhado ao Figma (Priority: P1)

Visitante acessa `/para-estudantes` e, no topo, vê o hero em duas colunas: à esquerda badge + título + subtítulo + dois CTAs; à direita a ilustração da plataforma — dentro de um container de no máximo 1280px, com paddings do Figma, sem o banner legado de “internas”.

**Why this priority**: É a primeira impressão da jornada do estudante e o principal desvio visual atual vs. design.

**Independent Test**: Abrir `/para-estudantes` em desktop ≥992px e comparar o hero com o Figma (Section 01); confirmar ausência do carrossel/imagem legada de internas.

**Acceptance Scenarios**:

1. **Given** o display `block_para_estudantes` publicado com o banner seedado, **When** o visitante abre `/para-estudantes`, **Then** vê o hero em duas colunas (copy/CTA | imagem) dentro de um container de no máximo 1280px.
2. **Given** a página carregada em desktop, **When** inspeciona o wrapper do hero, **Then** o padding corresponde a Top 64px, Right 40px, Bottom 48px, Left 40px.
3. **Given** a página carregada, **When** lê o conteúdo do hero, **Then** encontra o badge “Plataforma exclusiva para estudantes”, o `h1` “Seu futuro profissional começa aqui.”, o subtítulo Figma e os botões “Encontrar minha vaga” e “Criar meu perfil”.
4. **Given** viewport mobile, **When** a página carrega, **Then** as colunas empilham (texto acima da imagem) sem scroll horizontal causado pelo hero.

---

### User Story 2 — Visitante usa os CTAs do hero (Priority: P1)

Visitante aciona “Encontrar minha vaga” e “Criar meu perfil” e é levado a rotas canônicas limpas da jornada do estudante.

**Why this priority**: Sem conversão, o hero não cumpre o objetivo da página.

**Independent Test**: Clicar em cada CTA seedado e verificar destino.

**Acceptance Scenarios**:

1. **Given** o hero seedado, **When** clica em “Encontrar minha vaga”, **Then** navega para a rota/âncora canônica de listagem de vagas do produto (documentada no plan; default: `#` da listagem em `/para-estudantes` ou path canônico de busca de vagas).
2. **Given** o hero seedado, **When** clica em “Criar meu perfil”, **Then** navega para `/cadastro/candidato` (ou rota canônica equivalente de cadastro de estudante).

---

### User Story 3 — Editor gerencia o hero sem código (Priority: P2)

Editor autenticado altera copy/imagem do banner destinado a estudantes; a mudança reflete em `/para-estudantes` sem deploy de código.

**Why this priority**: Conteúdo da jornada do estudante evolui; não pode depender de desenvolvimento após o seed.

**Independent Test**: Editar o nó banner com local “Para Estudantes” no painel; recarregar a página pública.

**Acceptance Scenarios**:

1. **Given** editor com permissão, **When** altera título, subtítulo ou imagem do banner de estudantes, **Then** a alteração aparece no hero após o cache esperado.
2. **Given** editor com permissão, **When** cria um segundo banner com local “Para Estudantes”, **Then** o display limita a apresentação conforme pager do display (item único / primeiro por peso) sem quebrar o layout de duas colunas.

---

### User Story 4 — Deploy reproduz o hero sem painel manual (Priority: P1)

Homologação/produção recebem valor de local, display da View, seed, placement e desativação do legado apenas com o fluxo padrão de deploy.

**Why this priority**: Diretriz permanente do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/para-estudantes` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y` → `drush updb -y` → (2ª `cim` se placements dependerem de UUIDs seedados) → `drush cr`, **Then** `/para-estudantes` exibe o novo hero e não exibe o banner legado de internas.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de banners/placements nem sobrescrita de editorial divergente do seed.
3. **Given** alterações estruturais na origem, **When** `drush cex` é executado, **Then** View display, field storage (valor `para_estudantes`) e placement aparecem versionados em `config/sync`.

### Edge Cases

- Zero banners com local `para_estudantes` → omitir markup do hero (página não quebra; sem faixa vazia quebrada).
- Banner sem imagem → renderizar coluna de texto; omitir coluna visual ou placeholder seguro (sem ícone quebrado).
- Campos de copy vazios → omitir elementos vazios (badge/título/subtítulo/CTAs) sem fatal error.
- Reexecução do hook → no-op; **não** sobrescrever editorial divergente; **não** duplicar nó/placement.
- Rota diferente de `/para-estudantes` → o novo placement **não** aparece.
- Convivência com `banners-block_1` → placement legado desativado ou sem path `/para-estudantes` após o deploy.
- Home, Quem Somos, Para Empresas e Contato → visualmente inalterados (regressão amostral).

## Requirements *(obrigatório)*

### Functional Requirements

**Banner (View `banners`)**

- **FR-001**: O sistema DEVE oferecer um display Block da View `banners` com machine name `block_para_estudantes`.
- **FR-002**: O display DEVE filtrar apenas os banners com `field_local_exibicao` = `para_estudantes` (mesmo padrão de `block_para_empresas` / `block_quem_somos`).
- **FR-003**: O storage `field_local_exibicao` DEVE incluir o valor permitido `para_estudantes` (label “Para Estudantes”).
- **FR-004**: O placement do display DEVE ficar na região `banner` do tema `default`, com visibilidade exclusiva `/para-estudantes`.
- **FR-005**: O placement legado `banners-block_1` DEVE deixar de aparecer em `/para-estudantes` (desativado ou sem esse path).
- **FR-006**: O seed DEVE cadastrar **um** banner publicado com local `para_estudantes`, copy Figma e imagem destacada (asset em `custom_configs/assets/`).
- **FR-007**: O template Twig do display DEVE renderizar:
  - wrapper com `max-width: 1280px` e padding `64px 40px 48px 40px` (classe dedicada, ex.: `.hero-estudantes-wrapper`);
  - `.row.align-items-center`;
  - coluna esquerda `.col-12.col-lg-6`: badge, `h1`, subtítulo, CTAs em `.d-flex.gap-3`;
  - coluna direita `.col-12.col-lg-6`: imagem destacada com `.img-fluid` e alinhamento `.text-center` / `.text-lg-end`.
- **FR-008**: Em viewport estreita, o hero DEVE empilhar colunas (texto acima da imagem) sem causar scroll horizontal.
- **FR-009**: Copy seed DEVE incluir badge “Plataforma exclusiva para estudantes”, título “Seu futuro profissional começa aqui.”, subtítulo Figma e CTAs “Encontrar minha vaga” + “Criar meu perfil”.
- **FR-010**: Tipografia e tokens DEVEM seguir a marca já adotada (Poppins, navy, texto secundário cinza, CTA laranja `#FD7B1A` / equivalente do design system).
- **FR-011**: Estilos novos DEVEM ter escopo estrito (classes do display), sem alterar visualmente home, Quem Somos, Para Empresas, Contato ou rodapé.

**Deploy e documentação**

- **FR-012**: Um `hook_update_N` em `custom_configs` (próximo livre após `11032`, tipicamente `custom_configs_update_11033`) DEVE, de forma **idempotente**, usando Entity API / config API: garantir valor `para_estudantes` no storage; garantir display `block_para_estudantes` + filtro; garantir placement na região `banner` só em `/para-estudantes`; seedar o banner default (sem sobrescrever editorial divergente); desativar/remover legado `banners-block_1` desta rota; nunca duplicar.
- **FR-013**: Alterações estruturais DEVEM ser exportáveis via `drush cex` e versionadas em `config/sync` (View, field storage, block placement).
- **FR-014**: O fluxo de deploy documentado DEVE seguir: `git pull` → `drush cim -y` → `drush updb -y` → (2ª `cim` quando placements dependerem de UUIDs seedados) → `drush cr`.
- **FR-015**: A seção relevante do `PRD.md` DEVE ser atualizada de forma cirúrgica refletindo o display `block_para_estudantes` e a rota `/para-estudantes`.

### Key Entities

- **View `banners` / display `block_para_estudantes`**: hero único da página de estudantes; filtro `local = para_estudantes`.
- **Nó `banners` (seed)**: um item publicado com copy/imagem do Figma; UUID fixo no hook.
- **`field_local_exibicao`**: lista de destinos; novo valor `para_estudantes`.
- **Placement**: `views_block:banners-block_para_estudantes` na região `banner`, tema `default`, `request_path` `/para-estudantes`.
- **Placement legado**: `default_views_block__banners_block_1` — deixa de servir `/para-estudantes`.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, o hero de `/para-estudantes` é reconhecível frente ao Figma (duas colunas, container ≤1280px, paddings 64/40/48/40, badge, título, CTAs, ilustração) em ≥95% dos critérios do checklist visual.
- **SC-002**: Em viewport mobile, 100% das verificações confirmam empilhamento do hero, textos legíveis e ausência de scroll horizontal causado pelo hero.
- **SC-003**: O banner legado de internas **não** aparece em `/para-estudantes` após o deploy (verificação visual + inspeção de blocos na região).
- **SC-004**: Os dois CTAs do hero levam a destinos canônicos utilizáveis em 100% dos testes manuais de aceite.
- **SC-005**: Um editor atualiza copy ou imagem do banner de estudantes em menos de 3 minutos, sem suporte de desenvolvimento.
- **SC-006**: Após o deploy padrão, `/para-estudantes` exibe o novo hero sem passos manuais no admin.
- **SC-007**: Segunda execução de `drush updb` não cria banners/placements duplicados nem sobrescreve editorial divergente (idempotência confirmada).
- **SC-008**: Home, Quem Somos, Para Empresas e Contato permanecem visualmente inalterados na amostragem de regressão.

## Assumptions

- Rota canônica permanece `/para-estudantes` (Clean URL / alias já existente); não se cria rota paralela.
- Região do Hero é `banner` (não `content_full`), alinhada a `block_para_empresas` / `block_quem_somos` / `block_home`. O pedido de “content_full ou região designada” resolve-se pela região já designada ao Hero no tema.
- Layout é **duas colunas com copy HTML + imagem** (como descrito no Figma Section 01), distinto do carrossel full-bleed atual de Para Empresas; a **arquitetura** (View `banners` + display + placement + hook + cex) é o que se reutiliza.
- Hero único (1 slide); sem carrossel Bootstrap nesta feature.
- CTA “Criar meu perfil” → `/cadastro/candidato`.
- CTA “Encontrar minha vaga” → permanece em `/para-estudantes` com âncora à listagem/filtros de vagas (path/âncora exatos documentados no plan); se não houver âncora estável, aponta para a própria `/para-estudantes` (comportamento seguro).
- Textos do badge/título/subtítulo/CTAs podem viver no Twig (padrão Quem Somos / Para Empresas) **ou** em campos do nó `banners` quando já existirem campos adequados; o plan escolhe a opção que reutilize fields existentes sem criar storage paralelo. Em ambos os casos o seed garante a copy Figma na primeira instalação.
- Próximo hook: `custom_configs_update_11033` (após `11032`), salvo se outro update for commitado antes da implementação.
- Asset da ilustração: exportado do Figma / arte editorial e versionado em `modules/custom/custom_configs/assets/banner-para-estudantes/`; o hook copia para `public://`.
- Textos seed desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação (PHP do `hook_update_N`, Twig, SCSS/CSS, YAMLs, `drush cex`) são produzidos em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
