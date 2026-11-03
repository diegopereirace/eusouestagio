# Feature Specification: Restaurar Benefícios Estudantes — Layout Figma (split + colagem circular)

**Feature Directory**: `specs/055-restaurar-beneficios-estudantes`  
**Created**: 2026-11-03  
**Status**: Draft  
**Input do usuário**: Recuperar e reexibir o bloco “Benefícios para Estudantes” (título “Muito mais do que encontrar um estágio.”) em `/para-estudantes` com layout idêntico ao Figma: fundo claro azul, duas colunas no desktop (conteúdo + colagem circular), 6 benefícios em grade 2×3 com cards claros, CTA “Cadastre-se gratuitamente”, tipografia com título bicolor, responsividade mobile-first. Garantir instância + placement via `hook_update_N` idempotente e `drush cex`; reutilizar block type / paragraphs existentes. Skills `mattpocock/skills` devem ser consultadas se disponíveis no ambiente; caso contrário, seguir padrões de CSS modular já usados no tema.

## Nota de levantamento (estado atual vs. alvo)

| Aspecto | Estado atual (pós-`11079`/`11080`) | Alvo desta feature |
|---|---|---|
| Copy / itens | Título “Encontre oportunidades…”, **4** cards (Vagas alinhadas…) | “Muito mais do que encontrar um estágio.” + **6** benefícios canônicos |
| Layout | Cabeçalho centrado + grade 1/2/4 | Split: conteúdo à esquerda + colagem circular à direita (desktop) |
| Fundo / cards | Fundo `#F8F9FF`; cards com chrome legado | Fundo seção `#eef5ff`; itens em cards brancos arredondados (ícone + texto) |
| Título | Cor única | Duas cores de marca (`#07132E` / `#005B9F`) dividindo o título |
| CTA na seção | Ausente na UI pública | Botão laranja “Cadastre-se gratuitamente” |
| Colagem | Campo pode existir; UI pública não monta órbita | Colagem circular (foto central + 5 satélites em órbita pontilhada) |
| Placement | `default_beneficiosestudantes`, `content_full`, `/para-estudantes` | **Mesmo placement** — restaurar conteúdo/UI in-place, logo após Hero Search |
| Relação com `054` | `054` entregou split; `11079` reverteu para 4 cards | Esta feature **reestabelece** o layout Figma como UI pública definitiva |

## Escopo

### Inclui

- Restauração da seção pública Benefícios em `/para-estudantes` para conformidade com o frame Figma (badge, título bicolor, subtítulo, 6 itens, CTA, colagem circular).
- Reutilização do block type `beneficios_estudantes` e do paragraph `card_icon_text_p`; **sem** segundo bloco de benefícios na página.
- Conteúdo canônico da seção:
  - **Badge / label**: `Benefícios para Estudantes` (ou variante uppercase já seedada `BENEFÍCIOS PARA ESTUDANTES` — uma forma canônica no seed).
  - **Título**: `Muito mais do que encontrar um estágio.` com divisão visual de cor:
    - trecho escuro `#07132E`: `Muito mais do que`
    - trecho azul `#005B9F`: `encontrar um estágio.`
  - **Subtítulo**: `Na Eu Sou Estágio você se desenvolve, recebe apoio e constrói uma carreira com mais segurança e propósito.`
  - **Seis itens**, nesta ordem:
    1. **Encontrar propósito** — Descubra oportunidades que fazem sentido para você.
    2. **Desenvolver competências** — Acesse conteúdos e recursos para a sua evolução.
    3. **Receber acompanhamento** — Conte com orientação durante toda a sua jornada.
    4. **Aprender mais** — Tenha acesso a dicas, cursos e materiais exclusivos.
    5. **Construir carreira** — Dê os primeiros passos para um futuro promissor.
    6. **Ter uma experiência de estágio de verdade** — Conecte-se com empresas que valorizam seu potencial.
  - **CTA**: rótulo `Cadastre-se gratuitamente` (com indicação visual de continuidade, ex. seta) → destino `/cadastro/candidato`.
- Colagem visual à direita (desktop): composição circular — foto principal ao centro, cinco fotos menores em órbita sobre anel pontilhado; assets versionados para deploy; construção visual no tema (marcação + estilos) alinhada ao Figma.
- Layout desktop em duas áreas (~7/5 ou 6/6): conteúdo (badge, título, subtítulo, grade 2 colunas de benefícios, CTA) e colagem.
- Fundo da seção `#eef5ff`; itens de benefício como cards brancos com cantos arredondados, ícone à esquerda do texto, gap consistente (~16px) entre itens.
- Mobile-first: abaixo de ~992px, coluna de conteúdo 100%; grade de benefícios em **1 coluna**; colagem redimensionada abaixo do conteúdo (sem overflow horizontal) — se a órbita comprometer a leitura em viewports muito estreitas, pode ser omitida abaixo de ~576px mantendo texto + CTA completos.
- Seed / fill-if-empty idempotente: localizar instância pelo UUID fixo conhecido (ou recriar se ausente); garantir 6 itens, copy canônica quando vazio ou legado conhecido das **4 cards** atuais (`Vagas alinhadas`…), badge/CTA/colagem, ícones e assets; **nunca** sobrescrever copy editorial intencional divergente.
- Placement garantido em `content_full` do tema `default`, somente `/para-estudantes`, imediatamente após o Hero Search (ordem de leitura: Banner → Hero Search → **Benefícios** → demais seções).
- `hook_update_N` idempotente em `custom_configs` (próximo livre após `11080` → `11081`) + `drush cex` na origem para config estrutural se fields/displays/placements mudarem.
- Atualização cirúrgica do `PRD.md` §3.6 refletindo o retorno do layout Figma como UI pública da seção.
- Escopo CSS isolado sob `.block-beneficios-estudantes` (library existente do tema).

### Fora

- Redesign de banner, Hero Search, jornada, destaque `block_3`, perfil ou CTA final (`026`) de `/para-estudantes`.
- Alterações em `/para-empresas`, home, `/quem-somos`, `/vagas` (exceto ausência de regressão).
- Criação de Content Type / View / taxonomia / field **storage** paralelo.
- CMS foto a foto da órbita no painel nesta versão (arte/seed versionada; editor foca em badge/título/subtítulo/itens/CTA).
- Remoção do block type `beneficios_estudantes` ou do paragraph `card_icon_text_p`.
- Alterações em `core/` ou `vendor/`.
- Entrega de código nesta etapa de especificação — segue `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`.

### Ordem visual em `/para-estudantes` (inalterada)

1. Banner  
2. Hero Search  
3. **Benefícios** ← esta feature (layout Figma restaurado)  
4. Jornada + destaque vagas  
5. CTA final  

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o layout Figma restaurado (Priority: P1)

Visitante abre `/para-estudantes`, rola até Benefícios e vê o label “Benefícios para Estudantes”, o título “Muito mais do que encontrar um estágio.” com as duas cores de marca, o subtítulo, os seis cards de benefício e o botão “Cadastre-se gratuitamente”; no desktop, a colagem circular aparece à direita sobre fundo `#eef5ff`.

**Why this priority**: Recupera a proposta de valor alinhada ao Figma após a regressão para 4 cards.

**Independent Test**: Abrir `/para-estudantes` em viewport ≥992px e comparar com o frame Figma (cabeçalho, 6 cards, CTA, colagem).

**Acceptance Scenarios**:

1. **Given** a seção publicada, **When** o visitante visualiza Benefícios no desktop, **Then** vê label, título bicolor, subtítulo, seis itens na ordem canônica e o CTA laranja na coluna de conteúdo.
2. **Given** viewport desktop, **When** observa o lado direito, **Then** a colagem circular (foto central + órbita de rostos) está visível e coerente com o Figma.
3. **Given** a página, **When** procura a grade pública de 4 cards centrados (“Vagas alinhadas” / layout pós-`11079`), **Then** essa apresentação **não** é mais a UI pública da seção.

---

### User Story 2 — Visitante mobile/tablet lê sem quebra (Priority: P1)

Em viewports intermediárias e estreitas, os seis benefícios ficam em uma coluna (ou até duas quando houver espaço estável), o CTA permanece acionável, não há scroll horizontal causado pela seção, e a colagem não prejudica a leitura.

**Why this priority**: Tráfego mobile não pode perder a mensagem nem a conversão.

**Independent Test**: Abrir `/para-estudantes` em ~768px e ~375px; rolar a seção Benefícios.

**Acceptance Scenarios**:

1. **Given** viewport &lt;992px, **When** a seção carrega, **Then** o conteúdo textual ocupa a largura útil e a grade de benefícios usa uma coluna (em viewports estreitas).
2. **Given** viewport mobile estreita, **When** observa a seção, **Then** título, itens e CTA são legíveis; se a colagem estiver visível, está redimensionada sem overflow; se omitida, o restante permanece completo.
3. **Given** qualquer viewport alvo, **When** o visitante aciona “Cadastre-se gratuitamente”, **Then** navega para `/cadastro/candidato`.

---

### User Story 3 — Editor atualiza copy e itens sem código (Priority: P2)

Editor com permissão de blocos altera label/badge, título, subtítulo, ícones/textos dos seis itens e o link/rótulo do CTA; as mudanças refletem em `/para-estudantes` sem deploy de código. A colagem permanece arte de seed nesta versão.

**Why this priority**: Marketing altera copy com frequência; a colagem é brand art estável.

**Independent Test**: Editar o bloco no painel, salvar, recarregar `/para-estudantes`.

**Acceptance Scenarios**:

1. **Given** editor autenticado, **When** altera o título de um item e salva, **Then** o novo texto aparece na landing.
2. **Given** editor, **When** altera destino ou rótulo do CTA da seção, **Then** o botão público reflete a mudança.
3. **Given** esta versão, **When** o editor busca editar cada foto da órbita individualmente, **Then** isso **não** é exigido.

---

### User Story 4 — Deploy automatizado recupera instância e placement (Priority: P1)

Após a receita de destino, a seção Benefícios exibe o layout e o seed canônicos sem cliques no Block Layout — mesmo se a instância tiver sido apagada do banco ou o placement estiver ausente/desabilitado.

**Why this priority**: Governança permanente de deploy; o bloco “sumiu” da UI pública e precisa voltar de forma automatizada.

**Independent Test**: Executar o update em ambiente com 4 cards legados e/ou bloco ausente; reexecutar; conferir ausência de duplicatas.

**Acceptance Scenarios**:

1. **Given** ambiente com benefícios legados (4 cards) ou bloco deletado, **When** o update roda, **Then** a instância existe, o placement em `/para-estudantes` está ativo e a superfície pública apresenta os 6 itens canônicos (ou preenche lacunas se editorial divergente — ver Assumptions).
2. **Given** o update já aplicado, **When** reexecuta, **Then** não duplica itens/blocos nem sobrescreve copy editorial intencional divergente.
3. **Given** desenvolvimento na origem, **When** a feature fecha, **Then** `drush cex` exporta qualquer config estrutural nova/alterada.

---

### Edge Cases

- Bloco ausente no banco: hook recria pela UUID fixa com seed completo.
- Placement ausente ou desabilitado: hook recria/reativa na região `content_full` com visibilidade só em `/para-estudantes`.
- Menos de 6 itens: renderiza os existentes sem erro; seed preenche faltantes quando aplicável.
- Mais de 6 itens editoriais: todos renderizam na ordem cadastrada; layout permanece utilizável.
- Ícone ausente em um item: título + descrição legíveis no card.
- Asset de colagem faltando em `public://`: coluna de texto intacta; hook recopia a partir de assets versionados.
- CTA vazio: omitir o botão (sem link quebrado).
- Agregação CSS/cache: após limpeza de cache, o layout split é o que o visitante vê.
- Convivência com CTA final (`026`): dois CTAs de cadastro na página são aceitáveis; esta feature **não** remove o CTA final.

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: A seção Benefícios em `/para-estudantes` MUST apresentar label/badge, título bicolor, subtítulo, lista de benefícios com ícone e CTA conforme o frame Figma de referência.
- **FR-002**: A seção MUST exibir os seis benefícios canônicos na ordem do Escopo (salvo copy editorial divergente intencional já publicada).
- **FR-003**: No desktop (≥992px), o layout MUST ser em duas áreas: conteúdo à esquerda e colagem circular à direita, sobre fundo `#eef5ff`.
- **FR-004**: Cada item de benefício MUST aparecer como card claro (fundo branco, cantos arredondados) com ícone e textos; no desktop, os itens MUST formar grade de duas colunas com espaçamento consistente.
- **FR-005**: Em viewports &lt;992px, a seção MUST permanecer legível e sem scroll horizontal; a grade de benefícios MUST usar uma coluna em viewports estreitas; a colagem MUST NOT impedir a leitura.
- **FR-006**: O botão “Cadastre-se gratuitamente” MUST levar a `/cadastro/candidato` no seed canônico.
- **FR-007**: A feature MUST reutilizar o block type `beneficios_estudantes` e o paragraph de ícone+texto existentes; MUST NOT criar uma segunda seção de benefícios na mesma rota.
- **FR-008**: A apresentação pública de 4 cards centrados (pós-`11079`) MUST deixar de ser a UI desta seção após o deploy.
- **FR-009**: Um `hook_update_N` idempotente MUST recuperar/criar a instância, seed/fill dos 6 itens, badge, CTA, assets da colagem e garantir placement ativo após o Hero Search.
- **FR-010**: Alterações estruturais MUST ser exportáveis via `drush cex` e versionadas; destino sem passos manuais no Block Layout.
- **FR-011**: O `PRD.md` MUST ser atualizado de forma cirúrgica na descrição da seção Benefícios Estudantes.
- **FR-012**: Banner, Hero Search, jornada, destaque de vagas e CTA final MUST NOT regredir por causa desta feature.
- **FR-013**: Estilos da seção MUST permanecer com escopo isolado (não alterar Benefícios PE / diferenciais home / Quem Somos).
- **FR-014**: A colagem circular MUST comunicar visualmente foto central + satélites em órbita pontilhada no desktop, alinhada ao Figma.

### Key Entities

- **Benefícios Estudantes (bloco)**: seção da landing com badge, título, subtítulo, lista de itens, CTA e referência visual à colagem.
- **Item de benefício**: ícone + título curto + descrição (paragraph reutilizado), apresentado como card claro.
- **Colagem circular**: composição visual de marca (foto central + órbita) associada à seção.
- **CTA da seção**: link de conversão para cadastro de candidato.
- **Placement**: posicionamento do bloco em `content_full` restrito a `/para-estudantes`.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão lado a lado com o frame Figma, ≥90% dos revisores confirmam correspondência de estrutura (label + título bicolor + 6 cards + CTA + colagem no desktop + fundo claro).
- **SC-002**: Em desktop (≥992px), visitante identifica os **6** benefícios e o CTA em menos de 10 segundos após chegar à seção.
- **SC-003**: Em mobile (~375px), a seção não causa scroll horizontal; CTA permanece acionável com um toque.
- **SC-004**: 100% dos cliques no CTA da seção (seed) levam a `/cadastro/candidato`.
- **SC-005**: Após o deploy, 0 ocorrências da grade pública antiga de 4 cards centrados como layout da seção Benefícios.
- **SC-006**: Reexecução do update automatizado não duplica itens/blocos nem altera copy editorial divergente intencional.
- **SC-007**: Deploy em destino sem intervenção manual no Block Layout para ativar o layout/seed restaurado.
- **SC-008**: Em ambiente com instância deletada, após o update a seção volta a aparecer em `/para-estudantes` na ordem correta.

## Assumptions

- Esta feature **substitui** a UI pública atual (4 cards pós-`11079`) e **reestabelece** a intenção visual da `054`, com refinamentos explícitos: fundo `#eef5ff`, cards brancos nos itens, título bicolor e colagem em órbita (não apenas arte composta opaca sem estrutura).
- A “grade de 4 cards / oportunidades” visível hoje **é** o próprio bloco `beneficios_estudantes` (não um bloco paralelo do Hero Search); o placement continua o mesmo, logo após o Hero Search — não se cria um segundo bloco “depois de si mesmo”.
- Divisão de cor do título: `Muito mais do que` → `#07132E`; `encontrar um estágio.` → `#005B9F` (ajustável no plan se o Figma indicar o inverso, sem mudar o requisito de bicoloridade).
- Badge canônico no seed: `Benefícios para Estudantes` (apresentação tipográfica uppercase via estilo, se o Figma pedir caixa alta).
- Colagem: arte(s) versionada(s) em assets do módulo + marcação no tema permitindo órbita (foto central + satélites); detalhe de uma imagem composta vs. múltiplos arquivos fica no plan, desde que o resultado visual bata com o Figma.
- Mobile: colagem abaixo do conteúdo e redimensionada; omitir abaixo de ~576px é aceitável se a órbita poluir a experiência — texto + 6 itens + CTA permanecem.
- Fields de badge/CTA/colagem já introduzidos na `054` (`field_text_simple_2`, `field_link`, `field_image`) devem ser reutilizados; **proibido** storage paralelo.
- Legado conhecido das 4 cards (`Vagas alinhadas` etc.) e títulos/subtítulos pós-`11079` podem ser substituídos pelo seed dos 6 itens **somente** quando o conteúdo ainda for o seed legado ou estiver vazio.
- UUID da instância seed: `a8b9c0d1-e2f3-4456-a789-0bcdef123456`.
- Próximo `hook_update_N` livre: `11081`.
- Receita de destino padrão: `cim` → `updb` → `cim` → `cr`.
- Pacote `mattpocock/skills`: se instalado no ambiente do agente, preferir skills de CSS modular / composição; se ausente, usar padrões já estabelecidos no tema `default` (libraries + CSS escopado).
- Esta especificação **não** entrega código.
