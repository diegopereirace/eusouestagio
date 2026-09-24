# Feature Specification: Contato como Node

**Feature Directory**: `specs/018-contato-node`  
**Created**: 2026-09-24  
**Status**: Draft  
**Input do usuário**: Refatorar a **Página de Contato** eliminando o bloco legado (ID 16 / abordagem `layout_contato` da 017), centralizando o gerenciamento em um **Tipo de Conteúdo (Node) `contato`**, com campos reutilizáveis do projeto (webform, imagem, e-mail, WhatsApp, textos da coluna direita), layout Figma em duas colunas via Twig Bootstrap 5, CSS dedicado, e deploy 100% automatizado via `hook_update_N` + Configuration Management (`drush cex` → `config/sync`).

## Escopo

### Inclui

- Remoção programática e idempotente do `block_content` legado de **ID 16** (imagem/conteúdo legado da página de contato), sem deixar placement órfão que continue a renderizar em `/contato`
- Desativação/remoção do placement do bloco **Layout de Contato** (`layout_contato` / `default_layoutcontato`) na região usada em `/contato`, para que a página passe a ser servida **somente** pelo Node `contato` (sem formulário duplicado)
- Criação do Content Type **Contato** (machine name `contato`) com campos para centralizar tudo o que o layout exibe:
  - Referência ao Webform (pedido verbal `field_formulario_contato` → reutilizar storage canônico de webform em `node` quando equivalente existir)
  - Imagem lateral (pedido verbal `field_image` → reutilizar storage canônico de imagem em `node` quando equivalente existir)
  - Contatos extras: e-mail e telefone/WhatsApp (pedido verbal `field_email`, `field_phone_wpp`)
  - Textos da coluna direita: título curto e descrição (pedido verbal `field_text_simple_small` / `field_text_simple_long` → reutilizar storages canônicos de texto simples em `node`)
- Configuração de `entity_form_display` e `entity_view_display` do bundle `contato` (modo `full` / `default` conforme padrão do tema)
- Seed idempotente do Node padrão da página de contato + alias limpo `/contato`
- Template Twig do node (ex.: `node--contato--full.html.twig`) com grid Bootstrap 5 alinhado ao Figma
- CSS/SCSS com escopo estrito (ex.: `.node--contato` / `.layout-contato-node`): caixas de e-mail/WhatsApp e fundo `#E5EEFF` da coluna da imagem
- `hook_update_N` idempotente no módulo `custom_configs` (próximo número livre após `11022`, tipicamente `11023`)
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento
- Atualização cirúrgica do `PRD.md` refletindo Content Type `contato`, campos, rota `/contato` e aposentadoria do bloco legado na página

### Fora

- Redesign do header/footer além do necessário para conviver com o node
- Banner interno em `/contato`
- Layout Builder / edição visual de colunas pelo editor
- Alteração da estrutura de campos do webform `contato` (já entregue na 017), salvo se o reuso exigir apenas referência
- Migração histórica de submissions do webform
- Remoção obrigatória do **tipo** de bloco `layout_contato` do sistema (pode permanecer no config se não usado; o requisito é não renderizar mais em `/contato` e excluir o bloco ID 16)
- Alterações em `core/` ou `vendor/`
- Código PHP/Twig/CSS de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`)

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê a página de contato no layout do Figma via Node (Priority: P1)

Visitante acessa `/contato` e vê, em desktop, duas colunas: à esquerda o título “Envie sua mensagem”, o formulário e os atalhos de E-mail/WhatsApp gerenciáveis; à direita um painel azul claro com a ilustração e os textos de apoio (“Time de especialistas” / resposta em até 24 horas úteis), tudo proveniente do Node Contato — sem bloco legado visível.

**Why this priority**: É o valor principal — canal institucional alinhado à marca, agora editável como conteúdo de página.

**Independent Test**: Abrir `/contato` em viewport ≥992px; confirmar estrutura Figma e ausência de segundo formulário/bloco legado.

**Acceptance Scenarios**:

1. **Given** um Node Contato publicado com alias `/contato`, **When** o visitante abre a URL em desktop, **Then** vê layout em duas colunas (form+contatos | painel da imagem) dentro de um container com espaçamento vertical confortável.
2. **Given** a coluna esquerda, **When** observa o topo, **Then** o título visual é “Envie sua mensagem” (ou o título do node, se configurado para isso), em negrito, família Poppins.
3. **Given** a coluna direita, **When** observa o painel, **Then** o fundo é azul claro `#E5EEFF`, a imagem está centralizada e os textos de apoio estão centralizados abaixo dela.
4. **Given** a página carregada, **When** inspeciona a região de conteúdo, **Then** não há segundo formulário nem imagem proveniente do bloco ID 16 / placement `layout_contato`.

---

### User Story 2 — Visitante preenche e envia o formulário (Priority: P1)

Visitante preenche os campos do webform referenciado pelo Node, envia e recebe confirmação de sucesso.

**Why this priority**: Sem envio funcional, a página não cumpre o propósito de contato.

**Independent Test**: Preencher campos obrigatórios válidos e submeter; verificar confirmação e submission.

**Acceptance Scenarios**:

1. **Given** o Node com webform `contato` referenciado, **When** a página renderiza, **Then** o formulário aparece na coluna esquerda com os campos já definidos (Nome Completo, E-mail, Telefone, Categoria, Assunto, Mensagem).
2. **Given** dados válidos, **When** clica em “Enviar Mensagem”, **Then** vê confirmação de sucesso e a submission é registrada.
3. **Given** e-mail inválido ou obrigatório vazio, **When** tenta enviar, **Then** o envio é bloqueado com feedback amigável.

---

### User Story 3 — Visitante usa atalhos de E-mail e WhatsApp gerenciáveis (Priority: P1)

Abaixo do formulário, o visitante clica nas caixas de E-mail e WhatsApp cujos valores vêm dos campos do Node.

**Why this priority**: Oferece alternativa imediata ao formulário e deixa os canais editáveis sem código.

**Independent Test**: Inspecionar e acionar os dois links em `/contato`; alterar valores no Node e confirmar reflexão na página.

**Acceptance Scenarios**:

1. **Given** Node com e-mail preenchido, **When** observa a faixa de contatos, **Then** vê caixa com ícone de envelope, rótulo “E-mail” e link `mailto:` para o valor do campo.
2. **Given** Node com telefone/WhatsApp preenchido, **When** observa a faixa, **Then** vê caixa com ícone WhatsApp, rótulo “WhatsApp” e link `https://wa.me/` derivado dos dígitos do campo (com DDI 55 quando número BR).
3. **Given** viewport mobile, **When** a faixa é exibida, **Then** os itens permanecem usáveis sem overflow horizontal.

---

### User Story 4 — Visitante mobile vê colunas empilhadas (Priority: P1)

Em viewport estreita, formulário acima e painel da imagem abaixo; sem scroll horizontal causado pelo layout do node.

**Why this priority**: Grande parte do tráfego é mobile.

**Independent Test**: Abrir `/contato` em viewport ≤575.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** a coluna do formulário aparece acima da coluna da imagem.
2. **Given** viewport mobile, **When** interage com o formulário, **Then** inputs e botão permanecem utilizáveis.
3. **Given** viewport mobile, **When** rola a página, **Then** não há barra de rolagem horizontal causada pelo layout do Contato.

---

### User Story 5 — Editor gerencia a página Contato sem código (Priority: P2)

Editor autenticado edita o Node Contato (webform, imagem, e-mail, WhatsApp, textos da coluna direita); mudanças refletem em `/contato` sem deploy.

**Why this priority**: Centralizar em Node elimina dependência de bloco customizado e facilita permissões/editorial padrão de conteúdo.

**Independent Test**: Editar o Node no painel, salvar e recarregar `/contato`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão no tipo Contato, **When** abre o Node, **Then** consegue editar webform, imagem, e-mail, WhatsApp e textos da coluna direita.
2. **Given** alteração salva (ex.: novo e-mail ou texto “Time de especialistas”), **When** o visitante recarrega `/contato`, **Then** vê o conteúdo atualizado (após o cache esperado do site).

---

### User Story 6 — Deploy limpa legado e provisiona Node Contato (Priority: P1)

Homologação/produção recebem Content Type, fields, displays, seed do Node, alias `/contato`, remoção do bloco ID 16 e ausência do bloco legado na página — apenas com o fluxo padrão de deploy.

**Why this priority**: Requisito explícito (Configuration Management + hooks idempotentes); zero configuração manual.

**Independent Test**: Em ambiente desatualizado, executar deploy e validar `/contato` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado o fluxo `drush cim -y && drush updb -y && drush cr` (e 2ª `cim` se o runbook exigir), **Then** `/contato` exibe o layout via Node Contato com webform, imagem e contatos seed.
2. **Given** o bloco ID 16 existia, **When** o update roda, **Then** o bloco é excluído e não reaparece em reexecução.
3. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há Node Contato duplicado nem sobrescrita de conteúdo editorial divergente do seed.
4. **Given** alterações estruturais no ambiente de origem, **When** `drush cex` é executado, **Then** o Content Type `contato`, field storages/instances e displays aparecem versionados em `config/sync`.

### Edge Cases

- Imagem lateral vazia → painel azul sem `<img>` quebrada (omitir imagem ou manter painel só com textos).
- Webform não referenciado → não quebrar a página; omitir área do formulário ou mensagem neutra sem fatal error.
- E-mail ou WhatsApp vazios → omitir a caixa correspondente (não renderizar link vazio).
- Textos da coluna direita vazios → omitir título/descrição sem deixar espaços “fantasma” confusos; painel pode ficar só com imagem.
- Bloco ID 16 já ausente → update é no-op seguro para essa exclusão.
- Alias `/contato` apontando para página `page` seed (017) ou outro node → o update reassocia o alias ao Node Contato seed sem apagar histórico desnecessário; evita dois formulários.
- Placement `layout_contato` ainda ativo → deve ser desabilitado/removido para `/contato` no mesmo update.
- Título de página Drupal em `/contato` → permanece oculto se já houver regra de ocultar page title nessa rota; o H2 visual do layout é o título da seção.
- Reexecução do hook → no-op seguro; **não** sobrescrever editorial divergente; popular apenas se Node ausente ou campos vazios.

## Requirements *(obrigatório)*

### Functional Requirements

**Limpeza do legado**

- **FR-001**: O sistema DEVE excluir programaticamente o `block_content` de ID 16 quando existir, de forma idempotente (reexecução sem erro se já ausente).
- **FR-002**: Após o update, `/contato` NÃO DEVE renderizar o bloco Layout de Contato (`layout_contato`) nem qualquer placement associado que cause formulário/imagem duplicados.

**Content Type Contato**

- **FR-003**: O sistema DEVE oferecer o tipo de conteúdo rotulado “Contato” com machine name `contato`.
- **FR-004**: O Node Contato DEVE expor referência ao webform (pedido `field_formulario_contato`), priorizando reuso do storage canônico de webform já existente no entity type `node` quando for equivalente.
- **FR-005**: O Node Contato DEVE expor imagem lateral (pedido `field_image`), priorizando reuso do storage canônico de imagem já existente em `node` (ex.: equivalente a `field_imagem`) — **não** criar storage paralelo no mesmo entity type para o mesmo propósito.
- **FR-006**: O Node Contato DEVE expor campo de e-mail (pedido `field_email`) e campo de telefone/WhatsApp (pedido `field_phone_wpp`), reutilizando storages equivalentes em `node` se existirem; caso contrário, criando storage no entity type `node` com esses machine names.
- **FR-007**: O Node Contato DEVE expor texto curto para o título da coluna direita (pedido `field_text_simple_small` → reutilizar storage canônico de string curta em `node`, tipicamente `field_text_simple`) e texto longo para a descrição (pedido `field_text_simple_long` → reutilizar `field_text_simple_long` em `node`).
- **FR-008**: Displays de formulário e visualização do bundle `contato` DEVEM incluir os campos acima para edição e renderização pública (modo full/default conforme padrão do tema).

**Apresentação (tokens Figma)**

- **FR-009**: O layout DEVE usar container com padding vertical confortável (equivalente Bootstrap `.container.py-5`) e uma linha principal com duas colunas: esquerda ≈7/12 (`.col-12.col-lg-7`), direita ≈5/12 (`.col-12.col-lg-5`).
- **FR-010**: A coluna esquerda DEVE iniciar com título “Envie sua mensagem” (Poppins, negrito), seguida do webform renderizado.
- **FR-011**: Abaixo do formulário, DEVE existir faixa de contatos (equivalente `.d-flex.gap-4.mt-4`) com caixas bordadas para E-mail e WhatsApp (ícones + rótulos + valores dos campos), clicáveis.
- **FR-012**: A coluna direita DEVE ter fundo `#E5EEFF`, cantos/padding alinhados ao design, imagem centralizada (comportamento fluido) e textos de apoio centralizados (`.text-center`) abaixo da imagem.
- **FR-013**: Em viewports &lt;992px, as colunas DEVEM empilhar com formulário acima da imagem, sem scroll horizontal causado pelo layout.
- **FR-014**: Todo o markup do Contato DEVE estar encapsulado sob classe dedicada (ex.: `.node--contato` / `.layout-contato-node`); estilos novos DEVEM aplicar-se **somente** sob esse escopo.

**Seed, rota e deploy**

- **FR-015**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11022`, tipicamente `custom_configs_update_11023`) DEVE, de forma **idempotente**, usando Entity API: (1) excluir bloco ID 16 se existir; (2) garantir Content Type `contato` + field instances/displays; (3) desativar/remover placement do `layout_contato` em `/contato`; (4) criar/seedar o Node Contato padrão **somente se** ausente/campos vazios; (5) garantir alias limpo `/contato` apontando para esse Node.
- **FR-016**: O seed DEVE popular valores iniciais razoáveis (webform `contato`, imagem a partir do asset versionado se disponível, e-mail/WhatsApp e textos do Figma) sem sobrescrever editorial divergente em reexecução.
- **FR-017**: Alterações estruturais (content type, field storage/instance, displays) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex` no ambiente de origem.
- **FR-018**: O fluxo de deploy documentado DEVE seguir o padrão do projeto: `git pull` → `drush cim -y` → `drush updb -y` → (2ª `cim` se necessário) → `drush cr`.
- **FR-019**: A seção relevante do `PRD.md` DEVE ser atualizada de forma cirúrgica refletindo o Content Type `contato`, a rota `/contato` e a aposentadoria do bloco legado nessa página.

### Key Entities

- **`contato` (node type)**: página gerenciável de contato — webform, imagem lateral, e-mail, WhatsApp, título e descrição da coluna direita.
- **`contato` (webform)**: formulário público já existente (feature 017); referenciado pelo Node.
- **Bloco legado ID 16**: `block_content` a excluir no update.
- **`layout_contato` (block_content / placement)**: abordagem da 017 a deixar de renderizar em `/contato`.
- **Alias `/contato`**: Clean URL canônica apontando para o Node Contato seed.
- **Asset seed**: `img-contato.png` (quando reutilizado) sob `modules/custom/custom_configs/assets/contato/`.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, `/contato` é reconhecível frente ao Figma (duas colunas, título, form, atalhos, painel `#E5EEFF`, textos) em ≥95% dos critérios de checklist visual.
- **SC-002**: Em viewport mobile, 100% das verificações confirmam empilhamento correto, formulário usável e ausência de scroll horizontal causado pelo layout.
- **SC-003**: 100% dos envios com dados válidos resultam em confirmação de sucesso e submission registrada.
- **SC-004**: Os links de E-mail e WhatsApp abrem destinos derivados dos campos do Node em 100% dos cliques de teste (quando preenchidos).
- **SC-005**: Um editor atualiza imagem, e-mail ou texto da coluna direita em menos de 3 minutos, sem suporte de desenvolvimento.
- **SC-006**: Após o deploy padrão, `/contato` exibe o layout via Node Contato sem passos manuais no admin.
- **SC-007**: Após o update, o bloco ID 16 não existe mais; segunda execução de `drush updb` não recria o bloco nem duplica o Node Contato.
- **SC-008**: Em `/contato` há exatamente um formulário de contato visível (zero duplicação com bloco legado).
- **SC-009**: Estilos da página de contato não alteram visualmente home, Quem Somos nem rodapé (amostragem de regressão).

## Assumptions

- Esta feature **substitui** a renderização baseada em `layout_contato` (017) na rota `/contato`; o webform `contato` e o asset `img-contato.png` são reaproveitados.
- Storages de field são por entity type: nomes pedidos pelo usuário são intenções de reuso; mapeamento canônico esperado em `node`:
  - Webform → storage existente `webform` (ou `field_formulario_contato` em `node` se `webform` não for adequado ao display)
  - Imagem → `field_imagem` (equivalente a `field_image` em outros entity types)
  - Título curto coluna direita → `field_text_simple` (equivalente verbal a `field_text_simple_small`)
  - Descrição → `field_text_simple_long`
  - `field_email` e `field_phone_wpp` → criar em `node` se ainda não existirem nesse entity type
- Título visual da seção: hardcoded “Envie sua mensagem” no Twig (como no Figma); o `title` do Node pode ser “Contato” para listagens/admin e alias.
- Valores seed iniciais: e-mail `contato@eusouestagio.com`; WhatsApp no padrão já usado no projeto; textos “Time de especialistas” e “Nossa equipe responderá sua solicitação em até 24 horas úteis.”
- Hook número: próximo após `custom_configs_update_11022` → `11023`, salvo se outro update for commitado antes da implementação.
- UUID fixo para o Node Contato seed (padrão das features anteriores).
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação (PHP `hook_update_N`, Twig `node--contato--full.html.twig`, CSS/SCSS, `drush cex`) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
