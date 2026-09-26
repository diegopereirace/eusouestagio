# Feature Specification: Bloco de Depoimentos — Para Empresas

**Feature Directory**: `specs/020-depoimentos-empresas`  
**Created**: 2026-09-26  
**Status**: Draft  
**Input do usuário**: Criar o **Bloco de Depoimentos** na página `/para-empresas` — tipo de conteúdo `depoimento` (autor, cargo/empresa, texto, foto), View em bloco com carrossel “center mode” (item ativo centralizado; laterais parcialmente visíveis e com opacidade reduzida), paginação por dots, seed de ≥4 depoimentos de teste, placement em `content_full` imediatamente acima do CTA existente, deploy 100% automatizado (`hook_update_N` + `drush cex`), templates Twig + estilos alinhados ao Figma (card branco, radius 16px, padding 32px, avatar circular ~48px).

## Escopo

### Inclui

- Content type **Depoimento** (machine name `depoimento`) para gestão editorial dos depoimentos.
- Campos anexados priorizando **reuso de field storage** já existentes no entity type `node` (não criar storages paralelos com o mesmo propósito):
  - Título nativo do nó → nome do autor (ex.: “Mariana Silva”).
  - Cargo/empresa → reutilizar storage de texto curto já disponível (ex.: `field_text_simple`), com label editorial adequada; **não** inventar `field_cargo_empresa` se o storage equivalente já existir.
  - Texto do depoimento → reutilizar storage de texto longo já disponível (ex.: `field_text_simple_long`), com label editorial adequada.
  - Foto do autor (avatar) → reutilizar storage de imagem de nó já disponível (ex.: `field_imagem`); o nome `field_image` do pedido aplica-se semanticamente — no projeto, o storage de imagem em `node` é `field_imagem`.
- View `depoimentos_carousel` com display Block `block_depoimentos_empresas`: nodes `depoimento` publicados, ordenados por data de criação DESC.
- Placement do bloco da View (`views_block:depoimentos_carousel-block_depoimentos_empresas`) na região `content_full`, visibilidade exclusiva `/para-empresas`, **weight** imediatamente acima do CTA `cta_v1` já existente (hoje weight 4) e abaixo de Benefícios (hoje weight 3).
- Atualização da ordem de composição de `/para-empresas`: Banner → … → Benefícios → **Depoimentos** → CTA final.
- Carrossel interativo em “center mode”: card ativo no centro; cards anterior/próximo parcialmente visíveis nas laterais com opacidade reduzida; paginação por dots abaixo.
- Card visual alinhado ao Figma: fundo `#FFFFFF`, border-radius `16px`, padding interno `32px`, largura ~`404px`, altura pelo conteúdo (min-height ~`188px`), cabeçalho flex (avatar circular ~48px + nome em destaque + cargo/empresa secundário), corpo do depoimento em cor legível (`#45464D` ou token do tema).
- Seed idempotente de **pelo menos 4** nodes `depoimento` com copy alinhada ao Figma (ex.: Mariana Silva / Head de Talentos - TechCorp; Ricardo Gomes / CEO - Inovatech; e dois adicionais coerentes).
- Assets de avatar do seed versionados em `modules/custom/custom_configs/assets/` (padrão do projeto) e copiados para `public://` pelo hook — sem commit de `sites/default/files`.
- `hook_update_N` idempotente em `custom_configs.install` (próximo livre após `11031`, tipicamente `11032`) cobrindo garantia do tipo/campos/displays (via config import + ensure quando necessário), seeds e ajuste de placement/weight.
- Exportação estrutural via `drush cex` → `config/sync` ao final do desenvolvimento na origem (node type, fields, View, block placement, displays).
- Atualização cirúrgica do `PRD.md` (content types / Views / composição `/para-empresas`) se a alteração estrutural for aprovada na feature.

### Fora

- Depoimentos em outras rotas (home, Quem Somos, Contato, Para Estudantes) nesta entrega.
- Painel de moderação/aprovação de depoimentos enviados por visitantes (só conteúdo editorial interno).
- Redesign do CTA, Benefícios ou demais seções de `/para-empresas`.
- Layout Builder / edição visual de colunas pelo editor.
- Alterações em `core/` ou `vendor/`.
- Código PHP/Twig/SCSS/JS/YAML de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê depoimentos em carrossel center mode (Priority: P1)

Visitante acessa `/para-empresas`, rola até a seção entre Benefícios e o CTA “Pronto para contratar…”, e vê cards de depoimento em carrossel: o ativo centralizado e em destaque; laterais parcialmente visíveis e com opacidade reduzida; dots de paginação abaixo.

**Why this priority**: É a prova social B2B pedida no Figma e o entregável visual principal desta feature.

**Independent Test**: Abrir `/para-empresas` em desktop ≥992px; localizar a seção acima do CTA azul; interagir com dots / swipe / setas (se houver) e confirmar center mode + opacidade.

**Acceptance Scenarios**:

1. **Given** ≥4 depoimentos publicados e o bloco posicionado, **When** o visitante abre `/para-empresas`, **Then** vê o carrossel de depoimentos imediatamente acima do CTA final e abaixo de Benefícios.
2. **Given** o carrossel no desktop, **When** observa o layout, **Then** o card ativo está centralizado; cards vizinhos aparecem parcialmente nas laterais com opacidade menor que o ativo.
3. **Given** o carrossel, **When** muda de slide (dot, gesto ou controle disponível), **Then** outro depoimento torna-se o ativo central e a paginação reflete o índice correto.
4. **Given** viewport mobile, **When** a seção carrega, **Then** o carrossel permanece utilizável sem scroll horizontal da página causado pelos cards.

---

### User Story 2 — Visitante lê o conteúdo do card (Priority: P1)

Em cada card, o visitante identifica foto circular do autor, nome, cargo/empresa e o texto do depoimento, com aparência alinhada ao Figma (card branco, cantos 16px, respiro interno generoso).

**Why this priority**: Sem legibilidade e hierarquia, a prova social não convence.

**Independent Test**: Inspecionar visualmente o card ativo no desktop e comparar com o frame Figma anexado.

**Acceptance Scenarios**:

1. **Given** um depoimento com todos os campos preenchidos, **When** o card ativo é exibido, **Then** mostra avatar circular, nome (título), cargo/empresa e o texto do depoimento.
2. **Given** o card ativo, **When** comparado ao Figma, **Then** apresenta fundo branco, cantos ~16px e padding interno generoso (~32px), largura do card ~404px no desktop.
3. **Given** o texto do depoimento, **When** lido no card, **Then** a cor e o contraste permitem leitura confortável (não compete com o cabeçalho).

---

### User Story 3 — Editor gerencia depoimentos sem código (Priority: P2)

Editor autenticado cria, edita, publica ou despublica nodes do tipo Depoimento; o carrossel em `/para-empresas` reflete apenas os publicados, na ordem de criação (mais recentes primeiro).

**Why this priority**: Conteúdo de prova social muda; não pode depender de deploy após o seed.

**Independent Test**: Criar/editar/despublicar um depoimento no painel; limpar cache esperado; recarregar a página pública.

**Acceptance Scenarios**:

1. **Given** editor com permissão, **When** cria um novo Depoimento publicado com nome, cargo, texto e foto, **Then** o item aparece no carrossel após o cache esperado.
2. **Given** um depoimento publicado, **When** o editor o despublica, **Then** ele deixa de aparecer no carrossel.
3. **Given** vários publicados, **When** a lista é renderizada, **Then** a ordem segue criação DESC (mais recente primeiro).

---

### User Story 4 — Deploy reproduz a seção sem painel manual (Priority: P1)

Homologação/produção recebem tipo de conteúdo, View, seeds (≥4), placement/weight e estilos apenas com o fluxo padrão de deploy do projeto.

**Why this priority**: Diretriz permanente (Configuration Management + hooks idempotentes); zero passos manuais no admin de produção.

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar a seção em `/para-empresas` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y` → `drush updb -y` → (2ª `cim` se placements dependerem de UUIDs seedados) → `drush cr`, **Then** `/para-empresas` exibe o carrossel com ≥4 depoimentos acima do CTA.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de nodes seed nem de placements.
3. **Given** alterações estruturais na origem, **When** `drush cex` é executado, **Then** node type, fields, View e block placement aparecem versionados em `config/sync`.

### Edge Cases

- Zero depoimentos publicados → não quebrar a página; omitir a seção ou renderizar container vazio sem erros.
- Apenas 1 depoimento → carrossel degrada de forma segura (sem laterais “fantasma” quebradas nem dots enganosos).
- 2–3 depoimentos → center mode ainda funciona; laterais podem repetir ou simplesmente mostrar o que houver (detalhe no plan; default: não clonar infinitamente na v1).
- Depoimento sem foto → avatar omitido ou placeholder seguro (sem imagem quebrada).
- Campos de texto vazios → omitir elementos vazios sem fatal error.
- Reexecução do hook → no-op; **não** sobrescrever editorial divergente do seed; **não** duplicar nodes/placements.
- Rota diferente de `/para-empresas` → o bloco de depoimentos não aparece.
- Home, Quem Somos e demais páginas → visualmente inalterados (regressão amostral).

## Requirements *(obrigatório)*

### Functional Requirements

**Tipo de conteúdo e campos**

- **FR-001**: O sistema DEVE oferecer o content type **Depoimento** (`depoimento`) gerenciável no painel.
- **FR-002**: O título nativo do nó DEVE representar o nome do autor do depoimento.
- **FR-003**: O sistema DEVE anexar ao bundle campos para: cargo/empresa (texto curto), texto do depoimento (texto longo) e foto do autor (imagem), **reutilizando field storages existentes em `node`** sempre que houver equivalente (`field_text_simple`, `field_text_simple_long`, `field_imagem`); labels editoriais DEVEM deixar o propósito claro.
- **FR-004**: Form e view displays do bundle DEVEM permitir editar e visualizar esses campos de forma completa no painel.

**View e placement**

- **FR-005**: O sistema DEVE oferecer a View `depoimentos_carousel` com display Block `block_depoimentos_empresas`.
- **FR-006**: O display DEVE listar apenas nodes `depoimento` publicados, ordenados por data de criação DESC.
- **FR-007**: O placement do bloco da View DEVE ficar na região `content_full` do tema `default`, com visibilidade exclusiva `request_path` = `/para-empresas`.
- **FR-008**: O weight do placement DEVE posicionar o bloco **imediatamente acima** do CTA `cta_v1` de `/para-empresas` e **abaixo** de Benefícios (composição: … → Benefícios → Depoimentos → CTA).
- **FR-009**: Se necessário para completar a ordem, o weight do CTA (ou de Benefícios) PODE ser ajustado de forma idempotente — sem alterar a ordem relativa das demais seções já existentes.

**Carrossel e apresentação**

- **FR-010**: A seção pública DEVE apresentar os depoimentos em carrossel com comportamento “center mode”: item ativo centralizado; adjacentes parcialmente visíveis; adjacentes com opacidade reduzida em relação ao ativo.
- **FR-011**: O carrossel DEVE exibir paginação por dots abaixo da trilha de cards.
- **FR-012**: O card ativo DEVE seguir o visual do Figma: fundo branco, cantos ~16px, padding interno ~32px, largura aproximada de 404px no desktop, altura baseada no conteúdo (com piso visual ~188px).
- **FR-013**: O cabeçalho do card DEVE alinhar avatar circular (~48×48px) e metadados (nome em destaque + cargo/empresa secundário) em layout horizontal.
- **FR-014**: O texto do depoimento DEVE aparecer abaixo do cabeçalho, com tipografia e cor legíveis alinhadas à marca (Poppins / tokens do tema; texto ~`#45464D` ou equivalente).
- **FR-015**: Estilos e scripts novos DEVEM ter escopo estrito à seção de depoimentos, sem alterar visualmente home, Quem Somos, Contato, banner ou CTA de `/para-empresas`.
- **FR-016**: Em viewport estreita, o carrossel DEVE permanecer utilizável sem causar scroll horizontal da página.

**Seed e deploy**

- **FR-017**: Um `hook_update_N` em `custom_configs` (próximo livre após `11031`, tipicamente `custom_configs_update_11032`) DEVE, de forma **idempotente**, usando Entity API / config API: garantir seeds de ≥4 nodes `depoimento` com dados de teste alinhados ao Figma; garantir placement/weight do bloco da View em `content_full` só em `/para-empresas`; nunca duplicar; nunca sobrescrever editorial divergente do seed.
- **FR-018**: Avatares do seed DEVEM ser copiados de assets versionados do módulo para o filesystem público pelo hook (padrão do projeto); ausência de arquivo de asset NÃO DEVE falhar o update de forma catastrófica — degradar com nó sem imagem ou placeholder documentado.
- **FR-019**: Alterações estruturais (node type, fields, displays, View, block placement) DEVEM ser exportáveis via `drush cex` e versionadas em `config/sync` ao final do desenvolvimento na origem.
- **FR-020**: O fluxo de deploy documentado DEVE seguir o padrão do projeto: `git pull` → `drush cim -y` → `drush updb -y` → (2ª `cim` quando placements dependerem de UUIDs seedados) → `drush cr`.
- **FR-021**: A seção relevante do `PRD.md` DEVE ser atualizada de forma cirúrgica refletindo o content type, a View e a nova composição de `/para-empresas` (quando a feature estrutural for mesclada).

### Key Entities

- **Depoimento** (`depoimento`): nó editorial com autor (título), cargo/empresa, texto e foto; aparece no carrossel quando publicado.
- **View `depoimentos_carousel` / display `block_depoimentos_empresas`**: listagem em bloco para o carrossel público.
- **Placement**: bloco da View em `content_full`, tema `default`, path `/para-empresas`, weight entre Benefícios e CTA.
- **CTA v1 Para Empresas** / **Benefícios para Empresas**: âncoras de ordenação já existentes; permanecem, com possível ajuste fino de weight.
- **Seed (≥4 nodes)**: conteúdo de teste inicial (ex.: Mariana Silva, Ricardo Gomes + dois pares adicionais).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, o carrossel de depoimentos é reconhecível frente ao Figma (center mode, card branco ~16px, avatar circular, dots) em ≥95% dos critérios do checklist visual.
- **SC-002**: Em `/para-empresas`, 100% das verificações de ordem confirmam: Benefícios → Depoimentos → CTA (sem Depoimentos abaixo do CTA nem acima de Benefícios).
- **SC-003**: Com ≥4 itens seedados, o carrossel permite navegar entre todos os itens em 100% dos testes manuais de aceite (dots ou gesto).
- **SC-004**: Em viewport mobile, 100% das verificações confirmam seção utilizável e ausência de scroll horizontal causado pelo carrossel.
- **SC-005**: Um editor cria ou edita um depoimento e vê o resultado na página pública em menos de 5 minutos (após cache esperado), sem suporte de desenvolvimento.
- **SC-006**: Após o deploy padrão, `/para-empresas` exibe o carrossel com ≥4 depoimentos sem passos manuais no admin.
- **SC-007**: Segunda execução de `drush updb` não cria nodes/placements duplicados (idempotência confirmada).
- **SC-008**: Amostragem de regressão em home e Quem Somos permanece visualmente inalterada.
- **SC-009**: Com zero depoimentos publicados, a página `/para-empresas` carrega sem erro fatal (seção omitida ou vazia de forma segura).

## Assumptions

- Rota canônica permanece `/para-empresas`; não se cria rota paralela.
- A página `/para-empresas` e o CTA `cta_v1` (placement `default_ctav1paraempresas`, weight 4) e Benefícios (weight 3) já existem pela feature `019-para-empresas-page`.
- Próximo hook: `custom_configs_update_11032` (após `11031`), salvo se outro update for commitado antes da implementação.
- Reuso de campos em `node`: `field_text_simple` (cargo/empresa), `field_text_simple_long` (texto), `field_imagem` (avatar). Nomes sugeridos pelo pedido (`field_cargo_empresa`, `field_texto_depoimento`, `field_image`) são semânticos; a implementação prioriza storages existentes conforme regra de reuso do projeto. Decisão final de labels/machine names de *instance* no plan.
- Biblioteca JS do carrossel (Swiper, Splide, CSS scroll-snap + Intersection Observer, etc.) é decisão de `/speckit-plan` / research; a spec exige o comportamento (center mode + dots + opacidade), não a biblioteca.
- Loop infinito / clone de slides: fora do MVP se houver poucos itens; plan escolhe a opção mais simples que preserve center mode com ≥4 itens.
- Textos seed desta fase são somente pt-BR; copy dos 4 seeds pode usar os exemplos do Figma + dois pares inventados coerentes.
- Avatares seed: imagens placeholder ou assets dedicados em `custom_configs/assets/depoimentos/`; não usar fotos de pessoas reais sem direito de uso.
- Autoplay do carrossel: desligado por padrão (prova social lida com atenção); plan pode habilitar se o Figma indicar — default off.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação (PHP do `hook_update_N`, Twig, SCSS/CSS, JS, YAMLs, `drush cex`) são produzidos em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
