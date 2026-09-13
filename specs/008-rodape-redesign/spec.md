# Feature Specification: Redesign do Rodapé (Footer)

**Feature Directory**: `specs/008-rodape-redesign`  
**Created**: 2026-09-13  
**Status**: Draft  
**Input do usuário**: Refatorar o bloco de Rodapé (`footer`) do Drupal 11 para ficar idêntico ao novo design aprovado (anexo 2): fundo azul-marinho escuro, tipografia Poppins branca, grid Bootstrap em 5 colunas (marca + 4 ícones sociais circulares | Institucional | Para Estudantes | Para Empresas | Contato), barra inferior centralizada com copyright e crédito, accordion (Collapse Bootstrap) das colunas de links no mobile, botão flutuante de WhatsApp com `position: fixed` e `z-index: 1050`. Deploy de dados via `hook_update_N` idempotente no `custom_configs`, exportação estrutural via `drush cex` para `config/sync`, links hardcoded no Twig com rotas canônicas/futuras, e criação de Cursor Rule global do fluxo de desenvolvimento com `glob` amplo.

## Escopo

### Inclui

- Refatoração do template do rodapé (`themes/custom/default/templates/block/block--default-footer.html.twig`) para o layout aprovado em 5 colunas:
  - **Col 1 — Marca**: logo (`field_image`) linkada para `<front>` + linha de 4 ícones sociais circulares (E-mail, Instagram, LinkedIn, WhatsApp) com cores fiéis às marcas originais.
  - **Col 2 — Institucional**: título em uppercase + links Sobre Nós (`/sobre-nos`), Política de Privacidade (`/politica-de-privacidade`), Termos de Uso (`/termos-de-uso`).
  - **Col 3 — Para Estudantes**: título em uppercase + links Buscar Vagas (`/para-estudantes`), Cadastro de Estudantes (`/cadastro/candidato`), Blog (`/blog`).
  - **Col 4 — Para Empresas**: título em uppercase + links Cadastre sua Empresa (`/cadastro/empresa`), Abrir Vaga (`/painel/empresa/vagas/nova`).
  - **Col 5 — Contato**: título em uppercase + e-mail (`mailto:`) e telefone (`tel:`).
- Barra inferior: divisória sutil (`<hr>`) + texto centralizado em duas linhas — `© {ano} Eu Sou Estágio. Todos os direitos reservados.` e `Desenvolvido por Diego Pereira`.
- Ícone social de **E-mail** reutilizando o campo existente `field_email` (nenhum field storage novo).
- Links das colunas 2–4 **hardcoded no Twig** com rotas canônicas do projeto; rotas ainda inexistentes (`/sobre-nos`, `/politica-de-privacidade`, `/termos-de-uso`, `/blog`) são referenciadas desde já apontando para a futura rota.
- **Mobile**: colunas 2, 3 e 4 (Institucional, Estudantes, Empresas) transformadas em **Accordion (Collapse do Bootstrap)** — título expande/recolhe os links; colunas 1 (marca) e 5 (contato) permanecem sempre visíveis.
- Botão flutuante de WhatsApp com `position: fixed` e `z-index: 1050` (hoje `1030`).
- CSS isolado para botões sociais, divisória e botão flutuante, sem regressão em outros componentes.
- `hook_update_N` (`custom_configs_update_11011`) **idempotente** para atualizar a instância do bloco e seus conteúdos em todos os ambientes.
- Exportação de toda alteração estrutural para `config/sync` via `drush cex`.
- Criação da Cursor Rule global de fluxo de desenvolvimento (SDD, Configuration Management, reuso de campos, hooks de deploy) com `glob` para `modules/custom/**/*`, `themes/custom/**/*`, `config/sync/**/*`.
- Atualização cirúrgica do `PRD.md` (seção do bloco `footer`) refletindo o novo layout e a nova fonte de verdade dos links.

### Fora

- Criação das páginas futuras (`/sobre-nos`, `/politica-de-privacidade`, `/termos-de-uso`, `/blog`) — os links apontam para as rotas antes de elas existirem.
- Remoção dos menus legados `rodape---sobre`, `rodape---para-estudantes` e `para-empresas` (permanecem no sistema, sem uso pelo template; remoção é decisão futura).
- Novos campos ou field storages no bundle `footer` (a estrutura atual já suporta o novo layout).
- Exibição da tagline (`field_text_simple_long`) e do endereço (`field_text_simple_2`) — campos preservados no banco, fora do layout (padrão já adotado para `field_text_simple_2`).
- Alterações em `core/` ou `vendor/`.
- Mudanças no bloco flutuante de WhatsApp além do `z-index`/confirmação de `position: fixed`.

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o novo rodapé no desktop (Priority: P1)

Visitante acessa qualquer página pública em viewport larga e vê o rodapé idêntico ao design aprovado: fundo azul-marinho escuro, textos em Poppins branca, 5 colunas — marca com logo e 4 ícones sociais circulares, três colunas de links com títulos em uppercase e coluna de contato — e barra inferior centralizada com copyright e crédito ao desenvolvedor.

**Why this priority**: É o valor principal da feature — o rodapé é o fechamento visual de todas as páginas e precisa estar fiel ao design aprovado.

**Independent Test**: Com o bloco publicado, abrir qualquer página pública em viewport ≥992px e comparar com o anexo 2 (colunas, ordem, textos, ícones, barra inferior).

**Acceptance Scenarios**:

1. **Given** o bloco `footer` publicado na região do rodapé, **When** o visitante abre uma página pública em viewport ≥992px, **Then** vê as 5 colunas na ordem: marca | Institucional | Para Estudantes | Para Empresas | Contato.
2. **Given** a coluna de marca, **When** o visitante clica na logo, **Then** é levado à home (`<front>`); **When** observa os ícones sociais, **Then** vê 4 ícones circulares (E-mail, Instagram, LinkedIn, WhatsApp) com cores consistentes com as marcas originais.
3. **Given** as colunas de links, **When** o visitante lê os títulos, **Then** todos estão em uppercase (INSTITUCIONAL, PARA ESTUDANTES, PARA EMPRESAS, CONTATO).
4. **Given** a barra inferior, **When** o visitante a observa, **Then** vê uma divisória sutil seguida de texto centralizado: `© {ano corrente} Eu Sou Estágio. Todos os direitos reservados.` e, na linha seguinte, `Desenvolvido por Diego Pereira` — sem links legais na barra.
5. **Given** o rodapé renderizado, **When** comparado ao anexo 2, **Then** fundo, tipografia, espaçamentos e conteúdo correspondem ao design aprovado.

---

### User Story 2 — Visitante mobile navega pelas seções via accordion (Priority: P1)

Em viewport estreita, o visitante vê a marca (logo + sociais) e o contato sempre visíveis, e as três colunas de links (Institucional, Para Estudantes, Para Empresas) como itens de accordion: o título expande/recolhe a lista de links.

**Why this priority**: Usabilidade mobile é requisito explícito — sem accordion, 5 colunas empilhadas tornam o rodapé longo e hostil no celular.

**Independent Test**: Abrir uma página pública em viewport <992px, verificar marca e contato visíveis sem interação e expandir/recolher cada uma das 3 seções.

**Acceptance Scenarios**:

1. **Given** viewport <992px, **When** a página é renderizada, **Then** a coluna de marca (logo + sociais) e a coluna de contato estão visíveis sem nenhuma interação.
2. **Given** viewport <992px, **When** o visitante toca no título "Institucional", **Then** a lista de links dessa seção expande; **When** toca novamente, **Then** recolhe (comportamento Collapse do Bootstrap).
3. **Given** uma seção expandida, **When** o visitante toca em um link, **Then** é levado à rota correspondente.
4. **Given** viewport ≥992px, **When** a página é renderizada, **Then** nenhum accordion é exibido — todas as colunas aparecem abertas no grid.

---

### User Story 3 — Links levam às rotas canônicas (Priority: P1)

Todos os links do rodapé resolvem para as rotas canônicas do projeto; links de páginas ainda não criadas apontam para a futura rota planejada; e-mail e telefone usam protocolos `mailto:` e `tel:`; redes sociais externas abrem em nova aba.

**Why this priority**: Navegação correta é a função do rodapé; URLs limpas e previsíveis são requisito do projeto.

**Independent Test**: Inspecionar cada `href` do rodapé e comparar com a tabela de rotas da especificação.

**Acceptance Scenarios**:

1. **Given** a coluna Institucional, **When** os links são inspecionados, **Then** apontam para `/sobre-nos`, `/politica-de-privacidade` e `/termos-de-uso`.
2. **Given** a coluna Para Estudantes, **When** os links são inspecionados, **Then** apontam para `/para-estudantes`, `/cadastro/candidato` e `/blog`.
3. **Given** a coluna Para Empresas, **When** os links são inspecionados, **Then** apontam para `/cadastro/empresa` e `/painel/empresa/vagas/nova`.
4. **Given** a coluna Contato, **When** os itens são inspecionados, **Then** o e-mail usa `mailto:` e o telefone usa `tel:` com os valores dos campos do bloco.
5. **Given** os ícones sociais, **When** clicados, **Then** Instagram/LinkedIn/WhatsApp abrem em nova aba (`target="_blank"` com `rel="noopener noreferrer"`) e o E-mail abre o cliente de correio (`mailto:`).
6. **Given** uma rota futura ainda não implementada (ex.: `/blog`), **When** o link é clicado, **Then** o destino é a URL limpa planejada (resposta 404 controlada até a página existir — comportamento aceito e documentado).

---

### User Story 4 — Deploy reproduz o rodapé em outro ambiente (Priority: P1)

Ao fazer deploy em homologação/produção, as alterações de banco são aplicadas pelo `hook_update_N` idempotente e as estruturais pela importação de configuração, sem passos manuais no painel.

**Why this priority**: O projeto exige paridade entre ambientes via Configuration Management + hooks de deploy; sem isso a feature não é entregável.

**Independent Test**: Em ambiente limpo (ou desatualizado), rodar o fluxo de deploy e validar que o rodapé novo aparece sem intervenção manual.

**Acceptance Scenarios**:

1. **Given** um ambiente com o código atualizado, **When** executado `drush cim -y && drush updb -y && drush cr`, **Then** o `hook_update_N` aplica as atualizações de conteúdo do bloco `footer` e o novo layout é exibido.
2. **Given** o `hook_update_N` já executado, **When** `drush updb -y` roda novamente, **Then** nenhuma alteração duplicada ocorre (idempotência verificada por checagens de existência/estado).
3. **Given** alterações estruturais no bloco (form/view displays, fields), **When** `drush cex` é executado no ambiente de origem, **Then** os arquivos correspondentes aparecem em `config/sync` versionados.
4. **Given** um bloco com conteúdo editorial divergente do seed (ex.: e-mail/telefone reais cadastrados), **When** o hook roda, **Then** o conteúdo editorial é preservado (hook só preenche/ajusta o que for seguro).

---

### User Story 5 — Botão flutuante de WhatsApp permanece fixo e visível (Priority: P2)

O botão verde de WhatsApp no canto inferior direito permanece fixo durante a rolagem e acima das camadas comuns de conteúdo, sem conflitos de empilhamento.

**Why this priority**: Canal de contato direto; precisa estar sempre acessível, mas sem quebrar overlays do site.

**Independent Test**: Rolar páginas públicas e abrir componentes sobrepostos (ex.: banner LGPD) verificando o empilhamento.

**Acceptance Scenarios**:

1. **Given** qualquer página pública, **When** o visitante rola a página, **Then** o botão de WhatsApp permanece fixo no canto inferior direito (`position: fixed`).
2. **Given** o botão renderizado, **When** inspecionado, **Then** seu `z-index` é `1050`.
3. **Given** o rodapé visível no fim da página, **When** o visitante observa o canto inferior direito, **Then** o botão flutuante não é coberto pelo conteúdo do rodapé.

### Edge Cases

- `field_email` vazio → ícone social de e-mail e item `mailto:` da coluna Contato são omitidos (sem link quebrado).
- `field_phone_wpp` vazio → ícone social de WhatsApp e item `tel:` são omitidos.
- `field_instagram`/`field_linkedin` vazios → respectivos ícones omitidos; a linha de sociais mantém alinhamento com os ícones restantes.
- Logo (`field_image`) vazia → fallback para a arte versionada no módulo (`modules/custom/custom_configs/assets/footer/logo-rodape.png`), padrão já adotado no template atual.
- Rotas futuras ainda inexistentes → links renderizam normalmente; destino responde 404 controlada até a página ser criada (aceito por decisão de antecipação de URL).
- Viewport intermediária (768–991px) → comportamento de accordion/grid definido pelo breakpoint `lg` do Bootstrap (ver Assumptions).
- Telefone com formatação visual (`(11) 99999-9999`) → `tel:` e `wa.me` usam versão sanitizada (somente dígitos, com DDI quando aplicável).
- Ano do copyright → gerado dinamicamente (sem virada de ano manual).
- CSS do rodapé → seletores encapsulados no wrapper do rodapé para não vazar para outros componentes (e vice-versa).

## Requirements *(obrigatório)*

### Functional Requirements

**Conteúdo e estrutura**

- **FR-001**: O bundle `footer` (`block_content`) existente DEVE ser mantido com seus campos atuais: `field_image`, `field_text_simple_long`, `field_text_simple_2`, `field_email`, `field_phone_wpp`, `field_instagram`, `field_linkedin`. **Nenhum field storage novo** é criado — o ícone social de e-mail reutiliza `field_email` e o de WhatsApp reutiliza `field_phone_wpp`.
- **FR-002**: A tagline (`field_text_simple_long`) e o endereço (`field_text_simple_2`) NÃO DEVEM ser exibidos no novo layout; os campos permanecem disponíveis no painel para uso editorial futuro.
- **FR-003**: Os links das colunas Institucional, Para Estudantes e Para Empresas DEVEM ser hardcoded no Twig com as rotas canônicas: `/sobre-nos`, `/politica-de-privacidade`, `/termos-de-uso`, `/para-estudantes`, `/cadastro/candidato`, `/blog`, `/cadastro/empresa`, `/painel/empresa/vagas/nova`. O template DEIXA de consumir os menus `rodape---sobre`, `rodape---para-estudantes` e `para-empresas`.
- **FR-004**: Rotas ainda não implementadas (`/sobre-nos`, `/politica-de-privacidade`, `/termos-de-uso`, `/blog`) DEVEM ser referenciadas desde já, apontando para a futura rota planejada.
- **FR-005**: Um `hook_update_N` (`custom_configs_update_11011`) no módulo `custom_configs` DEVE atualizar a instância do bloco `footer` (UUID `d0326db5-fc80-4cf5-a0b9-8f779dc4aeba`) e seus conteúdos necessários ao novo layout, com validação de idempotência (no-op quando já aplicado) e preservação de conteúdo editorial divergente do seed.
- **FR-006**: Todas as alterações estruturais (form display, view display, field instances, placement) DEVEM ser exportadas via `drush cex` e versionadas em `config/sync`.
- **FR-007**: O fluxo de deploy documentado DEVE ser: `git pull` → `drush cim -y` → `drush updb -y` → `drush cr` (cim antes do updb, padrão do projeto).

**Apresentação**

- **FR-008**: O rodapé DEVE ter fundo azul-marinho escuro (cor exata extraída do design aprovado), tipografia Poppins e texto branco.
- **FR-009**: O layout desktop DEVE usar o grid do Bootstrap (`.row`) em 5 colunas na ordem: marca | Institucional | Para Estudantes | Para Empresas | Contato.
- **FR-010**: A coluna de marca DEVE exibir a logo linkada para `<front>` e, abaixo, uma linha (`.d-flex.gap-2`) com 4 ícones sociais circulares — E-mail, Instagram, LinkedIn, WhatsApp — com cores consistentes com as marcas originais.
- **FR-011**: Os títulos das colunas 2–5 DEVEM ser renderizados em uppercase.
- **FR-012**: A coluna Contato DEVE exibir e-mail com `mailto:` e telefone com `tel:` a partir dos campos do bloco.
- **FR-013**: A barra inferior DEVE conter uma divisória sutil (`<hr>`) e texto centralizado em duas linhas: `© {ano dinâmico} Eu Sou Estágio. Todos os direitos reservados.` e `Desenvolvido por Diego Pereira`. Os links legais saem da barra (passam a integrar a coluna Institucional).
- **FR-014**: Em viewport mobile, as colunas 2, 3 e 4 DEVEM ser apresentadas como Accordion (Collapse do Bootstrap), com o título expandindo os links; as colunas 1 e 5 permanecem sempre visíveis. Em desktop, nenhum accordion aparece.
- **FR-015**: O botão flutuante de WhatsApp DEVE ter `position: fixed` e `z-index: 1050`.
- **FR-016**: O CSS novo/alterado (botões sociais, divisória, barra inferior, accordion, flutuante) DEVE ficar isolado sob o wrapper do rodapé/de cada componente, sem regressão visual em outras seções do site.

**Governança / documentação**

- **FR-017**: A seção do bloco `footer` no `PRD.md` DEVE ser atualizada (layout em 5 colunas, links hardcoded com rotas canônicas, menus legados sem uso, novo hook de deploy).
- **FR-018**: DEVE existir uma Cursor Rule global do fluxo de desenvolvimento (SDD obrigatório, Configuration Management via `config/sync`, reuso de field storages, hooks de deploy idempotentes) com frontmatter `globs` apontando para `modules/custom/**/*`, `themes/custom/**/*` e `config/sync/**/*` (sem `alwaysApply: true`).

### Key Entities

- **`footer` (block_content)**: bloco institucional do rodapé — logo (`field_image`), contatos (`field_email`, `field_phone_wpp`), sociais (`field_instagram`, `field_linkedin`); tagline/endereço preservados e não exibidos. UUID fixo `d0326db5-fc80-4cf5-a0b9-8f779dc4aeba`; placement `block.block.default_footer` (região `footer_first`).
- **Rotas canônicas do rodapé** (fonte de verdade: Twig):

  | Coluna | Rótulo | Rota |
  |--------|--------|------|
  | Institucional | Sobre Nós | `/sobre-nos` *(futura)* |
  | Institucional | Política de Privacidade | `/politica-de-privacidade` *(futura)* |
  | Institucional | Termos de Uso | `/termos-de-uso` *(futura)* |
  | Para Estudantes | Buscar Vagas | `/para-estudantes` |
  | Para Estudantes | Cadastro de Estudantes | `/cadastro/candidato` |
  | Para Estudantes | Blog | `/blog` *(futura)* |
  | Para Empresas | Cadastre sua Empresa | `/cadastro/empresa` |
  | Para Empresas | Abrir Vaga | `/painel/empresa/vagas/nova` |

- **Bloco flutuante de WhatsApp** (`block--default-whatsapp`): componente separado do rodapé; escopo limitado a `position: fixed` + `z-index: 1050`.
- **Cursor Rule de fluxo** (`.cursor/rules/*.mdc`): artefato de governança do projeto, versionado com o repositório.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em 100% das páginas públicas em viewport ≥992px, o rodapé exibe as 5 colunas com os conteúdos e ordem definidos, correspondente ao design aprovado em revisão visual de aceite.
- **SC-002**: Em viewport <992px, as 3 seções de links funcionam como accordion (expandir/recolher) em 100% das interações, com marca e contato visíveis sem interação.
- **SC-003**: 100% dos links do rodapé apontam para as rotas da tabela de referência; e-mail e telefone usam `mailto:`/`tel:`; sociais externos abrem em nova aba.
- **SC-004**: Um ambiente desatualizado reproduz o novo rodapé executando apenas `git pull` + `drush cim -y` + `drush updb -y` + `drush cr`, com zero passos manuais no painel.
- **SC-005**: A reexecução do `hook_update_N` não produz alterações (idempotência confirmada em segunda rodada de `updb`).
- **SC-006**: O botão flutuante de WhatsApp permanece fixo e com `z-index: 1050` em 100% das páginas públicas verificadas, sem ser coberto pelo rodapé.
- **SC-007**: Nenhuma regressão visual fora do rodapé/botão flutuante (checagem por amostragem das páginas home, vagas e cadastro).
- **SC-008**: As configurações exportadas em `config/sync` e a nova Cursor Rule estão versionadas no repositório após a entrega.

## Assumptions

- **Links hardcoded no Twig** substituem os menus do rodapé como fonte de verdade: `menu_link_content` é entidade de **conteúdo** (não exportável via `config/sync`), e o hardcode garante paridade de deploy sem novos seeds; os menus legados permanecem no sistema, sem uso pelo template.
- **Breakpoint mobile** = breakpoint `lg` do Bootstrap (<992px): abaixo dele, accordion; a partir dele, grid de 5 colunas.
- O **ícone social de e-mail** usa o valor de `field_email` com `mailto:`; nenhum campo novo é necessário.
- O **ano do copyright** é dinâmico (data do servidor), exibindo "2026" em 2026.
- "Desenvolvido por Diego Pereira" é **texto simples** (sem link), conforme o design aprovado.
- O **fundo azul-marinho escuro** terá a cor exata extraída do anexo 2 na fase de implementação; o valor vigente no CSS atual do rodapé é o ponto de partida.
- Font Awesome já está disponível no tema (ícones `fa-*` já em uso no template atual).
- A logo exibida é a arte branca atual do rodapé (mesma identidade visual dos anexos); se uma nova arte for fornecida, o hook a anexa somente quando seguro (campo vazio ou arte seed anterior), nunca sobrescrevendo upload editorial divergente.
- O `z-index: 1050` do botão flutuante foi definido pelo requisito; coexistência com banner LGPD e modais Bootstrap será verificada no aceite (SC-006/SC-007).
- Textos desta fase são somente pt-BR.
