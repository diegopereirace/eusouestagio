# Feature Specification: Página de Contato

**Feature Directory**: `specs/017-pagina-contato`  
**Created**: 2026-09-23  
**Status**: Draft  
**Input do usuário**: Nova **Página de Contato** (`/contato`) alinhada ao Figma — ajustar o Webform existente `contato` com campos e placeholders do layout (grid nativo lado a lado), Custom Block Type `layout_contato` envelopando formulário + imagem lateral (`img-contato.png`), layout duas colunas (form + contatos extras à esquerda; painel azul `#E5EEFF` com ilustração à direita), links rápidos E-mail/WhatsApp, botão “Enviar Mensagem” laranja `#FD7B1A`, e deploy 100% automatizado via Configuration Management + `hook_update_N` idempotente em `custom_configs`.

## Escopo

### Inclui

- Ajuste do Webform **Contato** (machine name `contato`, já existente em `config/sync`) para a estrutura de campos do Figma:
  - Linha 1: `nome_completo` (texto, placeholder “Seu nome”) | `email` (e-mail, placeholder “seu@email.com”)
  - Linha 2: `telefone` (tel, placeholder “(00) 00000-0000”) | `categoria` (select, empty option “Estudante”)
  - Linha 3: `assunto` (texto, placeholder “Como podemos ajudar?”)
  - Linha 4: `mensagem` (textarea, placeholder “Escreva sua mensagem aqui...”)
  - Submit com rótulo **Enviar Mensagem** e aparência laranja `#FD7B1A`
  - Layout interno do webform com campos lado a lado nas linhas 1–2 (Flexbox/Grid nativo do Webform)
- Custom Block Type **Layout de Contato** (`layout_contato`) com:
  - Referência ao webform (pedido verbal `field_formulario_contato` — storage novo de referência a webform, se inexistente)
  - Imagem de destaque (pedido verbal `field_imagem_destaque` → **reutilizar** storage canônico `field_image`; criar apenas a field instance no bundle)
- Seed idempotente: uma instância do bloco com webform `contato` referenciado e imagem `img-contato.png` (asset versionado em `modules/custom/custom_configs/assets/` + cópia para `public://` pelo hook)
- Placement na região `content_full` do tema `default`, visibilidade restrita a `/contato`
- Garantia de rota limpa `/contato` (página/alias canônica) se ainda não existir de forma estável
- Template Twig do bloco (suggestion tipicamente `block--block-content--layout-contato.html.twig`)
- Layout Bootstrap 5: `.container.py-5` → `.row` → esquerda `.col-12.col-lg-7` (título “Envie sua mensagem” + webform + faixa de contatos) | direita `.col-12.col-lg-5` (painel `#E5EEFF` + imagem `.img-fluid`)
- Faixa de contatos extras abaixo do formulário (`.d-flex.gap-4.mt-4`): E-mail (`mailto:`) e WhatsApp (`https://wa.me/`), com ícones
- CSS/SCSS com escopo estrito (ex.: `.block-layout-contato` / `.layout-contato`): inputs arredondados, botão submit `#FD7B1A`, painel direito `#E5EEFF`, tipografia Poppins no título
- `hook_update_N` idempotente no `custom_configs` (próximo número livre após `11020`, tipicamente `11021`) garantindo webform atualizado, block type + fields/displays, seed, placement e rota
- Exportação estrutural via `drush cex` para `config/sync` ao final do desenvolvimento
- Atualização cirúrgica do `PRD.md` (blocos / rotas / webforms) refletindo `layout_contato`, webform `contato` e rota `/contato`

### Fora

- Redesign do header/footer desta página além do necessário para conviver com o bloco
- Banner interno em `/contato` (já removido de `banners-block_1` em `11020`)
- Layout Builder / edição visual de colunas pelo editor
- Criação de storage paralelo `field_imagem_destaque` quando `field_image` já cobre imagem em `block_content`
- Handlers de e-mail/CRM novos além do comportamento atual do webform (salvo se já existirem e precisarem ser preservados)
- Migração histórica de submissions antigas para os novos machine names de elementos
- Exibição do bloco em rotas além de `/contato`
- Alterações em `core/` ou `vendor/`
- Código PHP/Twig/SCSS/YAML de implementação nesta etapa de especificação (entregue em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`)

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê a página de contato no layout do Figma (Priority: P1)

Visitante acessa `/contato` e vê, em desktop, duas colunas: à esquerda o título “Envie sua mensagem”, o formulário e os atalhos de E-mail/WhatsApp; à direita um painel azul claro com a ilustração de atendimento e o texto de apoio (“Time de especialistas” / resposta em até 24 horas úteis), conforme o design.

**Why this priority**: É o valor principal — canal institucional de contato alinhado à marca.

**Independent Test**: Abrir `/contato` em viewport ≥992px e comparar estrutura, tipografia, cores e composição com o Figma de referência.

**Acceptance Scenarios**:

1. **Given** o bloco Layout de Contato publicado com webform e imagem, **When** o visitante abre `/contato` em desktop, **Then** vê layout em duas colunas (form+contatos | painel da imagem) dentro de um container com espaçamento vertical confortável.
2. **Given** a coluna esquerda, **When** observa o topo, **Then** o título é “Envie sua mensagem”, em negrito, família Poppins.
3. **Given** a coluna direita, **When** observa o painel, **Then** o fundo é azul claro `#E5EEFF`, a imagem está centralizada e fluida, e o copy de apoio do design é legível (inclusive se vier composto na própria imagem seed `img-contato.png`).

---

### User Story 2 — Visitante preenche e envia o formulário (Priority: P1)

Visitante preenche os campos do layout, envia e recebe confirmação de envio bem-sucedido, sem perder dados válidos por layout quebrado.

**Why this priority**: Sem envio funcional, a página não cumpre o propósito de contato.

**Independent Test**: Preencher todos os campos obrigatórios com dados válidos e submeter; verificar mensagem de sucesso e registro da submission.

**Acceptance Scenarios**:

1. **Given** o formulário exibido, **When** observa os campos, **Then** encontra Nome Completo, E-mail, Telefone, Categoria, Assunto e Mensagem com os placeholders do Figma.
2. **Given** viewport desktop, **When** observa as duas primeiras linhas, **Then** Nome|E-mail e Telefone|Categoria aparecem lado a lado; Assunto e Mensagem ocupam largura total.
3. **Given** campos obrigatórios válidos, **When** clica em “Enviar Mensagem”, **Then** a mensagem é aceita e o visitante vê confirmação de sucesso (equivalente à mensagem atual “Mensagem enviada!” / “Sua mensagem foi enviada com sucesso!”).
4. **Given** e-mail inválido ou campo obrigatório vazio, **When** tenta enviar, **Then** o formulário bloqueia o envio e exibe erro amigável sem limpar indevidamente os demais campos válidos.

---

### User Story 3 — Visitante usa atalhos de E-mail e WhatsApp (Priority: P1)

Abaixo do formulário, o visitante clica nos cartões/links de E-mail e WhatsApp e é levado aos canais corretos.

**Why this priority**: Oferece alternativa imediata ao formulário, conforme o Figma.

**Independent Test**: Inspecionar e acionar os dois links em `/contato`.

**Acceptance Scenarios**:

1. **Given** a faixa de contatos extras, **When** observa E-mail, **Then** vê ícone de envelope, rótulo “E-mail” e o endereço `contato@eusouestagio.com` como `mailto:contato@eusouestagio.com`.
2. **Given** a faixa de contatos extras, **When** observa WhatsApp, **Then** vê ícone do WhatsApp, rótulo “WhatsApp” e o número `(61) 99999-9999` apontando para `https://wa.me/5561999999999` (somente dígitos no path, com DDI 55).
3. **Given** viewport mobile, **When** a faixa é exibida, **Then** os dois itens permanecem usáveis (empilhados ou em linha com wrap) sem overflow horizontal.

---

### User Story 4 — Visitante mobile vê coluna empilhada (Priority: P1)

Em viewport estreita, as colunas empilham (formulário primeiro, painel da imagem depois), o formulário permanece utilizável e não há scroll horizontal causado pelo bloco.

**Why this priority**: Grande parte do tráfego é mobile.

**Independent Test**: Abrir `/contato` em viewport ≤575.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a página carrega, **Then** a coluna do formulário aparece acima da coluna da imagem.
2. **Given** viewport mobile, **When** o visitante interage com os campos, **Then** inputs e select usam largura adequada (aparência `form-control` / `form-select`) e o botão “Enviar Mensagem” permanece visível e clicável.
3. **Given** viewport mobile, **When** rola a página, **Then** não há barra de rolagem horizontal causada pelo bloco.

---

### User Story 5 — Editor gerencia webform e imagem sem código (Priority: P2)

Editor autenticado com permissão adequada altera a imagem de destaque ou a referência do webform no bloco; mudanças refletem em `/contato` sem deploy de código.

**Why this priority**: Conteúdo visual e o formulário associado podem evoluir; não devem depender de desenvolvimento após o seed.

**Independent Test**: Editar o bloco no painel, salvar e recarregar `/contato`.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre o bloco “Layout de Contato”, **Then** consegue editar a referência do webform e a imagem de destaque.
2. **Given** alteração de imagem salva, **When** o visitante recarrega `/contato`, **Then** vê a nova imagem no painel direito (após o cache esperado do site).

---

### User Story 6 — Deploy reproduz estrutura, seed e placement (Priority: P1)

Homologação/produção recebem webform atualizado, block type, fields, displays, instância seedada, placement, estilos e rota `/contato` apenas com o fluxo padrão de deploy — zero configuração manual no painel.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes).

**Independent Test**: Em ambiente desatualizado, executar o fluxo de deploy e validar `/contato` sem intervenção no admin.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr` (e 2ª passada de `cim` quando o runbook do projeto exigir), **Then** `/contato` exibe o layout completo com o webform e a imagem seed.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de bloco nem sobrescrita de conteúdo editorial divergente do seed.
3. **Given** alterações estruturais geradas no ambiente de origem, **When** `drush cex` é executado, **Then** webform `contato`, block type `layout_contato`, field storages/instances, displays e placement aparecem versionados em `config/sync`.

### Edge Cases

- Imagem de destaque vazia → omitir o painel visual ou renderizar painel azul sem `<img>` quebrada (sem ícone quebrado / alt vazio enganoso).
- Webform não referenciado no bloco → não quebrar a página; omitir a área do formulário ou exibir mensagem neutra sem fatal error.
- Campos obrigatórios vazios / e-mail inválido → erros de validação do webform; envio bloqueado.
- Reexecução do hook → no-op seguro; **não** sobrescrever imagem/editorial divergente do seed; popular apenas se instância ausente ou campos vazios.
- Página diferente de `/contato` → bloco não aparece.
- Título de página Drupal em `/contato` → permanece oculto (comportamento já existente do bloco `default_page_title` com negate em `/contato`); o H2 “Envie sua mensagem” do layout é o título visual da seção.
- Submissions antigas com elementos `nome`, `e_mail`, `whatsapp`, `tipo` → permanecem legíveis no histórico administrativo; novos envios usam os novos machine names (sem migração obrigatória nesta feature).
- Convivência com header/footer → esta feature não redesenha header/footer; apenas entrega o conteúdo principal de `/contato`.

## Requirements *(obrigatório)*

### Functional Requirements

**Webform Contato**

- **FR-001**: O sistema DEVE manter/atualizar o webform com machine name `contato` e título institucional “Contato”.
- **FR-002**: O webform DEVE expor os elementos: `nome_completo` (textfield), `email` (email), `telefone` (tel), `categoria` (select), `assunto` (textfield), `mensagem` (textarea), com placeholders exatamente iguais aos do Figma.
- **FR-003**: `categoria` DEVE apresentar empty option “Estudante” e opções selecionáveis **Estudante**, **Empresa** e **Outro** (valores alinhados ao público do produto).
- **FR-004**: Nas linhas 1 e 2, os pares de campos DEVEM renderizar lado a lado no desktop via layout nativo do Webform (Flexbox/Grid); no mobile, empilhar.
- **FR-005**: O botão de envio DEVE exibir o rótulo “Enviar Mensagem” e aparência primária laranja `#FD7B1A` (texto legível em contraste).
- **FR-006**: Visitantes anônimos e autenticados DEVEM poder criar submissions (acesso público de criação preservado).
- **FR-007**: Após envio bem-sucedido, o sistema DEVE mostrar confirmação clara ao visitante (mensagem de sucesso).

**Bloco Layout de Contato**

- **FR-008**: O sistema DEVE oferecer um tipo de bloco customizado rotulado “Layout de Contato” com machine name `layout_contato`.
- **FR-009**: O bloco DEVE expor referência ao webform via campo `field_formulario_contato` (tipo referência a webform; criar storage se inexistente).
- **FR-010**: O bloco DEVE expor imagem de destaque via storage reutilizado `field_image` (pedido verbal `field_imagem_destaque` → canônico `field_image`; **não** criar storage paralelo).
- **FR-011**: Displays de formulário e visualização do block type `layout_contato` DEVEM incluir os dois campos para edição e renderização pública.
- **FR-012**: Todo o markup do bloco DEVE estar encapsulado sob classe dedicada (ex.: `.block-layout-contato` / `.layout-contato`); estilos novos/alterados DEVEM aplicar-se **somente** sob esse escopo.

**Apresentação (tokens Figma)**

- **FR-013**: O layout DEVE usar container com padding vertical confortável (equivalente Bootstrap `.container.py-5`) e uma linha principal com duas colunas: esquerda ≈7/12 (`col-12 col-lg-7`), direita ≈5/12 (`col-12 col-lg-5`).
- **FR-014**: A coluna esquerda DEVE iniciar com H2 “Envie sua mensagem” (Poppins, negrito), seguida do webform renderizado com inputs no padrão visual Bootstrap 5 (aparência `form-control` / `form-select`, bordas arredondadas ≈8px).
- **FR-015**: Abaixo do formulário, DEVE existir faixa de contatos extras (equivalente `.d-flex.gap-4.mt-4`) com E-mail e WhatsApp clicáveis (`mailto:` e `https://wa.me/`), ícones e rótulos conforme Figma.
- **FR-016**: Valores seed dos atalhos: e-mail `contato@eusouestagio.com`; WhatsApp `(61) 99999-9999` → `wa.me/5561999999999`.
- **FR-017**: A coluna direita DEVE ter fundo `#E5EEFF`, cantos arredondados alinhados ao design, padding interno e imagem centralizada com comportamento fluido (`.img-fluid` / equivalente).
- **FR-018**: Em viewports &lt;992px, as colunas DEVEM empilhar com formulário acima da imagem, sem scroll horizontal causado pelo bloco.

**Seed, rota e deploy**

- **FR-019**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11020`, tipicamente `custom_configs_update_11021`) DEVE, de forma **idempotente**, usando Entity API e/ou config API: garantir atualização do webform `contato`; garantir block type + field instances/displays; garantir asset `img-contato.png` em `public://` a partir de `modules/custom/custom_configs/assets/`; criar/seedar a instância do bloco com UUID fixo **somente se** ausente/campos vazios; garantir placement em `content_full` com visibilidade `request_path` = `/contato`; garantir existência da rota limpa `/contato`.
- **FR-020**: O seed DEVE preservar edições posteriores do editor; preencher apenas campos/instância vazios; **não** duplicar blocos em reexecução.
- **FR-021**: Alterações estruturais (webform, block type, field storage/instance, displays, placement) DEVEM ser exportáveis via Configuration Management e versionadas em `config/sync` após `drush cex`.
- **FR-022**: O fluxo de deploy documentado DEVE seguir o padrão do projeto: `git pull` → `drush cim -y` → `drush updb -y` → (2ª `cim` se placements dependerem de UUIDs seedados) → `drush cr`.
- **FR-023**: A seção relevante do `PRD.md` DEVE ser atualizada de forma cirúrgica refletindo webform `contato`, block type `layout_contato`, placement e rota `/contato`.

### Key Entities

- **`contato` (webform)**: formulário público de mensagem — elementos `nome_completo`, `email`, `telefone`, `categoria`, `assunto`, `mensagem`; submit “Enviar Mensagem”.
- **`layout_contato` (block_content)**: envelope visual da página — webform (`field_formulario_contato`), imagem (`field_image`).
- **Placement**: bloco de tema `default` na região `content_full`, visível somente em `/contato`.
- **Asset seed**: `img-contato.png` (ilustração + copy “Time de especialistas…” conforme arte do Figma) versionado sob `modules/custom/custom_configs/assets/` (ex.: `contato/img-contato.png`).
- **Atalhos de contato (Twig)**: e-mail e WhatsApp com valores seed do Figma (não são fields do bloco nesta fase).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop, `/contato` é reconhecível frente ao Figma (duas colunas, título, form, atalhos, painel `#E5EEFF`) em ≥95% dos critérios de checklist visual.
- **SC-002**: Em viewport mobile, 100% das verificações confirmam empilhamento correto, formulário usável e ausência de scroll horizontal causado pelo bloco.
- **SC-003**: 100% dos envios com dados válidos resultam em confirmação de sucesso e submission registrada.
- **SC-004**: 100% dos envios com e-mail inválido ou obrigatórios vazios são bloqueados com feedback visível.
- **SC-005**: Os links de E-mail e WhatsApp abrem os destinos corretos (`mailto:` e `wa.me`) em 100% dos cliques de teste.
- **SC-006**: Um editor atualiza a imagem de destaque em menos de 3 minutos, sem suporte de desenvolvimento.
- **SC-007**: Após o deploy padrão, `/contato` exibe layout + formulário + imagem seed (ou conteúdo editorial já presente) sem passos manuais no admin.
- **SC-008**: Segunda execução de `drush updb` não cria bloco duplicado nem sobrescreve editorial divergente (idempotência confirmada).
- **SC-009**: Estilos da página de contato não alteram visualmente home, Quem Somos nem rodapé (amostragem de regressão).

## Assumptions

- O webform `contato` **já existe** (UUID `38532b7b-3193-4b6a-b666-e1e68693dd94`); esta feature **atualiza** seus elementos (substituindo `tipo`/`nome`/`e_mail`/`whatsapp`/`mensagem` pelos novos machine names), preservando o mesmo `id`.
- Opções de `categoria`: Estudante, Empresa, Outro; empty option visual “Estudante” conforme Figma.
- Campos obrigatórios: todos os seis elementos são obrigatórios (padrão de formulário de contato e do webform legado).
- Imagem: pedido `field_imagem_destaque` mapeia para storage canônico `field_image` no entity type `block_content`.
- Atalhos E-mail/WhatsApp ficam **hardcoded no Twig** com os valores do Figma nesta fase (não criam fields novos); evolução editorial futura pode reutilizar `field_email` / `field_phone_wpp` se necessário.
- Copy “Time de especialistas” / “24 horas úteis” pode estar **embutido na arte** `img-contato.png` (como no export do Figma); se a arte for só ilustração, o Twig pode complementar com o texto abaixo da imagem — a implementação escolhe a variante que bater com o asset final sem inventar conteúdo paralelo conflitante.
- Rota canônica: `/contato` (Clean URL); page title Drupal permanece oculto nessa rota.
- Hook número: próximo update após `custom_configs_update_11020` → `11021`, salvo se outro update for commitado antes da implementação.
- UUID: a implementação definirá UUID fixo para a instância seedada e para o placement exportável (padrão das features anteriores).
- Região: `content_full` (mesmo padrão de Quem Somos / CTA).
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- Entregáveis de implementação pedidos pelo usuário (YAML do webform, PHP do `hook_update_N`, Twig, SCSS/CSS) são produzidos nas fases `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, não nesta especificação.
