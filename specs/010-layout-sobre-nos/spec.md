# Feature Specification: Layout “Sobre nós” (wrap texto + imagem)

**Feature Directory**: `specs/010-layout-sobre-nos`  
**Created**: 2026-09-14  
**Status**: Draft  
**Input do usuário**: Revisar o tipo de conteúdo Node `quem_somos` e sua exibição no frontend da seção “Sobre nós”, de modo que um único texto descritivo (WYSIWYG) envolva (wrap) a imagem no layout, conforme Figma (imagem ~432×269 à direita, arco/círculo verde vazado ~185×185 no topo direito, círculo laranja sólido no canto inferior direito, tipografia Poppins). Garantir reuso dos campos do projeto, displays de formulário/visualização, deploy idempotente via `hook_update_N`, exportação `drush cex` → `config/sync`, template Twig com float Bootstrap e CSS com escopo estrito.

## Escopo

### Inclui

- Revisão do bundle Node `quem_somos` para garantir os campos da seção “Sobre nós”:
  - Título da seção: `field_titulo` (já existente; alinhado ao padrão `field_text_simple*` / título de seção do projeto — **não** criar `field_text_simple_small` paralelo).
  - Corpo WYSIWYG único que contorna a imagem: `field_text_long_formatted` (Text formatted, long — storage já existente no projeto; o pedido verbal `field_text_formatted_long` mapeia para este machine name canônico).
  - Imagem: `field_imagem` (Image — storage/instance já existentes; o pedido verbal `field_image` mapeia para este machine name no bundle `quem_somos`; `field_image` permanece o storage usado em outros bundles/blocos e **não** deve ser duplicado aqui).
- Garantia de que esses campos estão anexados ao bundle e visíveis no formulário (`entity_form_display`) e na renderização completa (`entity_view_display` modo `full`, ou `default` quando `full` não existir como display separado).
- Atualização do template Twig da página (ex.: `node--quem-somos--full.html.twig` e/ou o override já existente `node--quem-somos.html.twig`) para a **primeira seção** (“Sobre nós”):
  - Container principal com clearfix.
  - Imagem renderizada **antes** / no início do fluxo do texto, com wrapper flutuante à direita em breakpoints médios/grandes (float-end + margens), para o HTML formatado do único campo de texto contornar a imagem.
  - Proporção visual da imagem alinhada ao Figma (~432×269), com imagem fluida e `max-width` adequado.
  - Elementos decorativos geométricos (anel verde vazado ~185×185 no topo direito; círculo laranja sólido no canto inferior direito) isolados ao wrapper da imagem.
  - Tipografia Poppins na seção.
- CSS/SCSS com escopo estrito sob `.node-quem-somos` (e/ou a classe de tipo gerada pelo tema, desde que o escopo não vaze para outros content types).
- `hook_update_N` idempotente no `custom_configs` para anexar/garantir fields e displays quando ausentes.
- Exportação estrutural via `drush cex` para `config/sync`.
- Atualização cirúrgica do `PRD.md` na seção do content type / página Quem somos, se a estrutura de display mudar de forma relevante.

### Fora

- Redesign do banner do topo de `/quem-somos` (feature `009-banner-quem-somos`).
- Redesign completo da segunda seção do node (par `field_titulo_2` / `field_text_long_formatted_2` / `field_imagem_2` — hoje layout em duas colunas; permanece funcional; eventual alinhamento futuro a “Missão/Visão” do Figma é feature aparte).
- Criação de field storages novos com nomes paralelos (`field_text_formatted_long`, `field_image` no node `quem_somos`) quando já existem equivalentes.
- Edição do conteúdo editorial (copy WYSIWYG / troca de foto) além do necessário para validação visual — o editor continua dono do conteúdo.
- Alterações em `core/` ou `vendor/`.
- Novas rotas; a página continua em `/quem-somos` (alias institucional existente).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê “Sobre nós” com texto envolvendo a imagem (Priority: P1)

Visitante abre `/quem-somos` em desktop e, abaixo do banner, vê o título “Sobre nós”, a foto do grupo flutuando à direita e o texto descritivo único fluindo à esquerda e por baixo da imagem — não em duas colunas rígidas lado a lado.

**Why this priority**: É o valor principal da feature e a regra de negócio explícita (campo único + wrap).

**Independent Test**: Abrir `/quem-somos` em viewport ≥768px e confirmar float/wrap (texto contorna a imagem) frente ao print Figma.

**Acceptance Scenarios**:

1. **Given** o node `quem_somos` publicado com título, texto formatado e imagem preenchidos, **When** o visitante abre `/quem-somos` em desktop, **Then** a imagem aparece à direita e o texto a contorna (esquerda e, após a altura da imagem, em largura total abaixo).
2. **Given** o layout renderizado, **When** comparado ao Figma, **Then** a proporção da imagem é reconhecível (~432×269) sem estourar o container nem quebrar a tipografia.
3. **Given** o texto WYSIWYG com negritos (ex.: nome da marca e frases de ênfase), **When** a página renderiza, **Then** a formatação HTML do campo é preservada dentro do fluxo que envolve a imagem.

---

### User Story 2 — Elementos geométricos acompanham a imagem (Priority: P1)

O wrapper da imagem exibe o anel verde vazado no topo direito (parcialmente atrás/ao redor da foto) e o círculo laranja sólido no canto inferior direito, sem interferir na leitura do texto nem em outros nós do site.

**Why this priority**: Fazem parte do design aprovado e diferenciam a seção institucional.

**Independent Test**: Inspecionar visualmente o wrapper da imagem em desktop e mobile; verificar ausência do efeito em outras páginas.

**Acceptance Scenarios**:

1. **Given** a seção “Sobre nós” com imagem, **When** vista em desktop, **Then** o anel verde (~185×185) aparece associado ao canto superior direito da imagem.
2. **Given** a mesma seção, **When** vista em desktop, **Then** o círculo laranja aparece no canto inferior direito da imagem (sobrepondo a borda inferior/direita conforme Figma).
3. **Given** estilos da feature, **When** o visitante navega home ou outra interna, **Then** não há círculos/anel “fantasma” vazando de `.node-quem-somos`.

---

### User Story 3 — Mobile empilha sem float quebrado (Priority: P1)

Em viewport estreita, a imagem não fica flutuando de forma que corte o texto; o conteúdo permanece legível, sem scroll horizontal, e os decorativos não cobrem palavras.

**Why this priority**: Tráfego mobile não pode perder a mensagem institucional.

**Independent Test**: Abrir `/quem-somos` em viewport ≤767.98px.

**Acceptance Scenarios**:

1. **Given** viewport mobile, **When** a seção carrega, **Then** a imagem deixa de flutuar à direita (ou o float é desativado) e o fluxo texto/imagem permanece legível.
2. **Given** viewport mobile, **When** o visitante rola a página, **Then** não há barra de rolagem horizontal causada pela seção ou pelos decorativos.
3. **Given** decorativos geométricos, **When** em mobile, **Then** não cobrem o texto de forma que impeça a leitura.

---

### User Story 4 — Editor mantém título, texto e imagem no CMS (Priority: P2)

Editor autentica, edita o node `quem_somos` e encontra título da seção, corpo formatado longo e imagem nos displays de formulário; ao salvar e publicar, a página reflete as mudanças no layout wrap.

**Why this priority**: Sem campos visíveis no form/view, o layout wrap não tem conteúdo sustentável.

**Independent Test**: Abrir o formulário de edição do node e o modo de visualização completa após salvar.

**Acceptance Scenarios**:

1. **Given** um editor com permissão, **When** abre o formulário do node `quem_somos`, **Then** vê `field_titulo`, `field_text_long_formatted` e `field_imagem` disponíveis para edição (além dos campos da segunda seção já existentes).
2. **Given** alteração no texto ou na imagem, **When** publica e recarrega `/quem-somos`, **Then** a seção “Sobre nós” reflete o novo conteúdo no layout wrap.
3. **Given** imagem ausente, **When** a página renderiza, **Then** o texto ainda é exibido de forma legível (sem wrapper decorativo vazio obrigatório que quebre o layout).

---

### User Story 5 — Deploy reproduz estrutura e display sem painel manual (Priority: P1)

Homologação/produção recebem anexos de campos (se faltarem) e displays de form/view atualizados apenas com o fluxo padrão de deploy; configs estruturais versionadas em `config/sync`.

**Why this priority**: Requisito explícito do projeto (zero passos manuais em produção).

**Independent Test**: Em ambiente desatualizado, rodar o fluxo de deploy e validar formulário + `/quem-somos`.

**Acceptance Scenarios**:

1. **Given** código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr`, **Then** fields e displays necessários estão aplicados.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda de novo, **Then** não há duplicação de field instances nem displays corrompidos (idempotência).
3. **Given** alterações estruturais no ambiente de origem, **When** `drush cex` é executado, **Then** form/view displays (e field instances, se alterados) aparecem versionados em `config/sync`.

### Edge Cases

- Imagem ausente → omitir wrapper da imagem (e decorativos); texto em largura total.
- Texto vazio → omitir corpo; se houver imagem, exibir só imagem+título sem float obrigatório quebrado.
- Texto muito curto (menor que a altura da imagem) → imagem flutua à direita; área abaixo não “colapsa” de forma a sobrepor a seção seguinte de modo ilegível.
- Texto com HTML complexo (listas, vários `<p>`, strong) → wrap continua válido; clearfix no container evita vazamento do float para a segunda seção.
- Viewport intermediária (~768px) → float-md aplica; abaixo, empilha/cancela float.
- Segunda seção (`field_*_2`) → continua renderizando após clearfix, sem herdar float da primeira.
- Reexecução do hook → no-op seguro se fields/displays já estão corretos.
- Conteúdo editorial divergente → hook **não** sobrescreve texto/imagem do node.

## Requirements *(obrigatório)*

### Functional Requirements

**Dados e reuso de campos**

- **FR-001**: O bundle `quem_somos` DEVE utilizar, para a seção “Sobre nós”, os campos já canônicos do projeto: `field_titulo` (título da seção), `field_text_long_formatted` (corpo WYSIWYG único) e `field_imagem` (imagem). NÃO DEVEM ser criados storages paralelos `field_text_formatted_long` nem `field_image` neste bundle.
- **FR-002**: O corpo da seção “Sobre nós” DEVE ser um **único** campo formatado longo (não dois campos de parágrafo separados para “lado da imagem” vs “abaixo da imagem”).
- **FR-003**: Os campos `field_titulo_2`, `field_text_long_formatted_2` e `field_imagem_2` DEVEM permanecer anexados ao bundle para a seção seguinte; esta feature NÃO os remove.

**Displays**

- **FR-004**: O `entity_form_display` de `node.quem_somos` DEVE expor `field_titulo`, `field_text_long_formatted` e `field_imagem` para edição.
- **FR-005**: O `entity_view_display` usado na página completa (`full`, ou `default` se for o display efetivo da rota) DEVE tornar `field_titulo`, `field_text_long_formatted` e `field_imagem` disponíveis à renderização do template (não ocultos de forma que impeça o Twig de acessá-los conforme o padrão atual do projeto).

**Apresentação (Figma / wrap)**

- **FR-006**: Todo markup/estilo novo da seção wrap DEVE estar sob escopo `.node-quem-somos` (ou equivalente estrito do tipo), sem alterar visualmente outros content types.
- **FR-007**: No HTML da primeira seção, a imagem DEVE ser renderizada antes (ou no início) do fluxo do texto, com wrapper em `position-relative`, para permitir float e decorativos.
- **FR-008**: Em breakpoints médios/grandes, o wrapper da imagem DEVE flutuar à direita (equivalente Bootstrap: float-md-end + margem start + margem bottom) e o container da seção DEVE conter clearfix para não vazar float.
- **FR-009**: A tag da imagem DEVE ser responsiva (fluida) com limite de largura coerente com ~432px do Figma em desktop, sem quebrar o layout mobile.
- **FR-010**: O wrapper da imagem DEVE apresentar (via CSS) um anel/círculo verde vazado (~185×185) associado ao topo direito e um círculo laranja sólido no canto inferior direito.
- **FR-011**: A tipografia da seção DEVE usar Poppins (já adotada no tema, quando disponível).
- **FR-012**: Em viewport mobile, o float à direita DEVE ser desativado (ou equivalente) para preservar legibilidade e evitar scroll horizontal.
- **FR-013**: A segunda seção do node NÃO DEVE herdar o float da primeira (clearfix efetivo entre seções).

**Deploy e governança**

- **FR-014**: Um `hook_update_N` no módulo `custom_configs` (próximo número livre após `11012`, tipicamente `custom_configs_update_11013`) DEVE, de forma **idempotente**: garantir field instances no bundle `quem_somos` quando ausentes; garantir/atualizar form display e view display (`full`/`default`) para os campos da seção “Sobre nós”.
- **FR-015**: Todas as alterações estruturais (field instances, form/view displays) DEVEM ser exportadas com `drush cex` e versionadas em `config/sync`.
- **FR-016**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` (cim antes do updb).
- **FR-017**: O `PRD.md` DEVE ser atualizado de forma cirúrgica se a descrição do content type `quem_somos` / layout da página institucional mudar de forma relevante para a governança do produto.

### Key Entities

- **`quem_somos` (node)**: página institucional; seção “Sobre nós” alimentada por `field_titulo` + `field_text_long_formatted` + `field_imagem`; seção seguinte pelos campos `*_2`.
- **`field_text_long_formatted`**: corpo WYSIWYG único cujo HTML flui ao redor da imagem no frontend.
- **`field_imagem`**: fotografia da seção (~proporção 432×269 no design); alt textual obrigatório quando houver imagem.
- **`field_titulo`**: título visível da seção (ex.: “Sobre nós”).
- **Displays `node.quem_somos`**: form e view que expõem os campos ao editor e ao tema.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão visual de aceite desktop (≥768px), a seção “Sobre nós” é reconhecível frente ao Figma (wrap do texto, posição da imagem, anel verde e círculo laranja) em ≥95% dos critérios do checklist visual.
- **SC-002**: Em viewport mobile, 100% das verificações de aceite confirmam legibilidade, ausência de scroll horizontal causado pela seção e decorativos sem cobrir o texto de forma ilegível.
- **SC-003**: Com texto WYSIWYG contendo negritos/parágrafos, 100% das amostragens preservam a formatação e o wrap ao redor da imagem em desktop.
- **SC-004**: Após o deploy padrão, editor vê título, texto longo formatado e imagem no formulário; visitante vê o layout wrap em `/quem-somos`.
- **SC-005**: Ambiente desatualizado reproduz a estrutura com apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cr`, com zero passos manuais no painel.
- **SC-006**: Segunda execução de `drush updb` não duplica field instances nem corrompe displays (idempotência confirmada).
- **SC-007**: Estilos da seção não alteram visualmente outros content types (amostragem: home e pelo menos uma interna distinta).
- **SC-008**: Com imagem ausente, a página ainda exibe o texto “Sobre nós” de forma legível (sem layout quebrado).

## Assumptions

- **Nomenclatura canônica**: o pedido `field_text_formatted_long` / `field_image` refere-se aos storages já usados no bundle — `field_text_long_formatted` e `field_imagem`. Reuso obrigatório (regra do projeto); sem renomeação destrutiva de fields em produção.
- **Título da seção**: usa `field_titulo` (já no node e no Twig atual), não o título nativo do Node (que tipicamente alimenta o `<h1>` da página / metatag) nem um novo `field_text_simple_small`.
- **Modo de view**: hoje só existe `entity_view_display` `default` para `quem_somos`; a implementação pode criar `full` ou continuar em `default` desde que a página completa consuma o display com os campos visíveis — documentado no plan.
- **Segunda seção**: fora do redesign Figma desta feature; apenas garantir que o clearfix da primeira seção não a quebre.
- **Rota**: `/quem-somos` permanece a URL pública; o heading interno “Sobre nós” vem do campo de título da seção.
- **Hook número**: próximo update após `custom_configs_update_11012` → `11013`, salvo se outro update for commitado antes da implementação.
- **Bootstrap 5 / tema default**: classes utilitárias de float/clearfix/espaçamento do Bootstrap disponível no tema são a base do comportamento responsivo; CSS custom cobre proporção, Poppins (se necessário) e geometria.
- **Banner 009**: permanece acima desta seção; esta feature não o altera.
- Textos desta fase são somente pt-BR.
- Nenhum passo manual no admin de produção é aceitável para ativar a feature.
- **Código PHP/Twig/CSS**: fora do escopo de `/speckit-specify`; será produzido em `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`, conforme SDD do projeto.
