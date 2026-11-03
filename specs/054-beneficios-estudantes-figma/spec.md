# Feature Specification: Benefícios Estudantes — Layout Figma (split + colagem)

**Feature Directory**: `specs/054-beneficios-estudantes-figma`  
**Created**: 2026-11-03  
**Status**: Draft  
**Input do usuário**: Substituir o layout atual da seção Benefícios em `/para-estudantes` (grade de 4 cards centrados das features `022`/`053`) pelo frame Figma de referência: badge “BENEFÍCIOS PARA ESTUDANTES”, título “Muito mais do que encontrar um estágio.”, subtítulo institucional, **6** benefícios em lista/grade de duas colunas (ícone + título + descrição), CTA “Cadastre-se gratuitamente” e, à direita no desktop, composição circular de fotos (colagem/órbita). Deploy automatizado (`hook_update_N` + `drush cex`); reuso do block type / paragraphs existentes quando couber; sem passos manuais em produção.

## Nota de levantamento (estado atual vs. pedido)

| Aspecto | Estado atual (`022`/`053`) | Alvo Figma (esta feature) |
|---|---|---|
| Cabeçalho | Título + subtítulo centralizados; **sem** badge | Badge/rótulo + título + subtítulo **alinhados à esquerda** (desktop) |
| Copy | “Encontre oportunidades que combinam com você.” + 4 cards (Vagas alinhadas…) | “Muito mais do que encontrar um estágio.” + 6 itens (Encontrar propósito… Ter uma experiência…) |
| Layout itens | Cards em grade 1/2/4, tipicamente centrados | Lista/grade **2 colunas** no desktop (3×2), ícone à esquerda do texto; sem “card box” dominante |
| Coluna visual | Ausente | Colagem circular (foto central + órbita de rostos + acento thumbs-up) à **direita** no desktop |
| CTA na seção | Ausente (CTA final é bloco separado `026`) | Botão laranja “Cadastre-se gratuitamente” **dentro** da seção |
| Placement | `default_beneficiosestudantes`, `content_full`, `/para-estudantes` | **Mesmo placement** — redesenho in-place, não segunda seção |

## Escopo

### Inclui

- Redesenho da seção pública Benefícios em `/para-estudantes` para conformidade visual e de conteúdo com o frame Figma fornecido (container de referência ~1280 de largura).
- Reutilização do block type `beneficios_estudantes` e do paragraph `card_icon_text_p` (ícone + título + descrição) sempre que o design couber; **sem** segundo bloco de benefícios na página.
- Conteúdo canônico da seção:
  - **Badge**: `BENEFÍCIOS PARA ESTUDANTES`
  - **Título**: `Muito mais do que encontrar um estágio.`
  - **Subtítulo**: `Na Eu Sou Estágio você se desenvolve, recebe apoio e constrói uma carreira com mais segurança e propósito.`
  - **Seis itens**, nesta ordem:
    1. **Encontrar propósito** — Descubra oportunidades que fazem sentido para você.
    2. **Desenvolver competências** — Acesse conteúdos e recursos para a sua evolução.
    3. **Receber acompanhamento** — Conte com orientação durante toda a sua jornada.
    4. **Aprender mais** — Tenha acesso a dicas, cursos e materiais exclusivos.
    5. **Construir carreira** — Dê os primeiros passos para um futuro promissor.
    6. **Ter uma experiência de estágio de verdade** — Conecte-se com empresas que valorizam seu potencial.
  - **CTA**: rótulo `Cadastre-se gratuitamente` → destino `/cadastro/candidato` (mesma intenção do CTA final de estudantes).
- Colagem visual à direita (desktop): arte versionada para deploy (foto central + satélites + acento), alinhada ao Figma; comportamento responsivo definido nas Assumptions (empilhar ou ocultar em viewport estreita sem quebrar a leitura).
- Atualização de Twig/CSS da seção (escopo isolado sob o bloco de benefícios existentes) para layout split, tipografia, badge, lista de 6 itens e botão.
- Seed / fill-if-empty idempotente: garantir os 6 itens, copy canônica quando vazio ou legado conhecido das 4 cards antigas, ícones versionados, CTA e assets da colagem; **nunca** sobrescrever copy editorial intencional divergente.
- `hook_update_N` idempotente em `custom_configs` (próximo livre após `11077` → `11078`) + `drush cex` na origem para config estrutural se fields/displays/placements mudarem.
- Atualização cirúrgica do `PRD.md` §3.6 descrevendo o novo layout/copy da seção Benefícios (não a composição inteira da página, salvo menção ao visual).

### Fora

- Redesign de banner, Hero Search, jornada, destaque `block_3`, perfil (já oculto na `053`) ou CTA final `cta_v1` de `/para-estudantes`.
- Alterações em `/para-empresas`, home, `/quem-somos`, `/vagas` (exceto ausência de regressão).
- Criação de Content Type / View / taxonomia nova.
- Colagem editável foto a foto pelo painel nesta versão (arte seed/versionada; editor foca em badge/título/subtítulo/itens/CTA).
- Remoção do block type `beneficios_estudantes` ou do paragraph `card_icon_text_p`.
- Alterações em `core/` ou `vendor/`.
- Entrega de código nesta etapa de especificação — segue `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`.

### Ordem visual em `/para-estudantes` (inalterada)

1. Banner  
2. Hero Search  
3. **Benefícios** ← esta feature (novo layout)  
4. Jornada + destaque vagas  
5. CTA final  

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Visitante vê o novo layout de benefícios (Priority: P1)

Visitante abre `/para-estudantes`, rola até Benefícios e vê o badge, o título “Muito mais do que encontrar um estágio.”, o subtítulo, os seis itens com ícone e o botão “Cadastre-se gratuitamente”; no desktop, a colagem circular aparece à direita.

**Why this priority**: É o valor central da feature — alinhar a proposta de valor da landing ao Figma atual.

**Independent Test**: Abrir `/para-estudantes` em viewport ≥992px e comparar cabeçalho, 6 itens, CTA e colagem com o frame de referência.

**Acceptance Scenarios**:

1. **Given** a seção publicada, **When** o visitante visualiza Benefícios no desktop, **Then** vê badge, título, subtítulo, seis itens na ordem canônica e o CTA laranja na coluna de conteúdo.
2. **Given** viewport desktop, **When** observa o lado direito da seção, **Then** a colagem circular (foto central + órbita) está visível e alinhada ao espírito do Figma.
3. **Given** a página, **When** procura a antiga grade de 4 cards (“Vagas alinhadas” / layout centrado de `053`), **Then** esse layout **não** é mais a apresentação pública da seção.

---

### User Story 2 — Visitante em tablet/mobile lê os seis benefícios sem quebra (Priority: P1)

Em viewport intermediária e estreita, os seis itens permanecem legíveis (empilhados ou em duas colunas conforme breakpoint), o CTA permanece acionável e não há scroll horizontal causado pela seção; a colagem não prejudica a leitura.

**Why this priority**: Tráfego mobile não pode perder a mensagem nem a conversão.

**Independent Test**: Abrir `/para-estudantes` em ~768px e ~375px; rolar a seção Benefícios.

**Acceptance Scenarios**:

1. **Given** viewport mobile estreita, **When** a seção carrega, **Then** título, subtítulo, itens e CTA são legíveis e utilizáveis sem overflow horizontal da seção.
2. **Given** viewport tablet, **When** observa os itens, **Then** a disposição permanece coerente (até 2 colunas quando houver espaço) sem sobreposição ilegível com a colagem.
3. **Given** qualquer viewport alvo, **When** o visitante interage com “Cadastre-se gratuitamente”, **Then** navega para `/cadastro/candidato`.

---

### User Story 3 — Editor atualiza copy e itens sem código (Priority: P2)

Editor com permissão de blocos altera badge (se editável), título, subtítulo, ícones/textos dos seis itens e o link/rótulo do CTA; as mudanças refletem em `/para-estudantes` sem deploy de código. A colagem permanece a arte de seed (não itemizada no painel nesta versão).

**Why this priority**: Marketing altera copy com frequência; a colagem é brand art estável.

**Independent Test**: Editar o bloco no painel, salvar, recarregar `/para-estudantes`.

**Acceptance Scenarios**:

1. **Given** editor autenticado, **When** altera o título de um item e salva, **Then** o novo texto aparece na landing.
2. **Given** editor, **When** altera o destino ou rótulo do CTA da seção, **Then** o botão público reflete a mudança.
3. **Given** esta versão, **When** o editor busca editar cada foto da órbita individualmente, **Then** isso **não** é exigido (arte versionada/seed).

---

### User Story 4 — Deploy automatizado da nova composição de conteúdo (Priority: P1)

Após a receita de destino, a seção Benefícios exibe o layout e o seed canônicos sem cliques no Block Layout.

**Why this priority**: Governança permanente de deploy do projeto.

**Independent Test**: Executar `hook_update_N` em ambiente com o bloco legado de 4 cards; reexecutar; conferir ausência de duplicatas.

**Acceptance Scenarios**:

1. **Given** ambiente com benefícios legados (4 cards), **When** o update roda, **Then** a superfície pública passa a apresentar os 6 itens canônicos (ou preenche apenas lacunas se editorial divergente já existir — ver Assumptions).
2. **Given** o update já aplicado, **When** reexecuta, **Then** não duplica itens/blocos nem sobrescreve copy editorial intencional divergente.
3. **Given** desenvolvimento na origem, **When** a feature fecha, **Then** `drush cex` exporta qualquer config estrutural nova/alterada.

---

### Edge Cases

- Bloco com menos de 6 itens: renderiza os existentes sem erro; seed preenche faltantes quando aplicável.
- Mais de 6 itens editoriais: todos renderizam na ordem cadastrada (sem truncar silenciosamente), layout permanece utilizável.
- Ícone ausente em um item: título + descrição legíveis.
- Colagem com asset faltando em `public://`: fallback sem quebrar a coluna de texto (imagem omitida ou placeholder controlado — detalhe no plan); hook recopia a partir de assets versionados.
- CTA vazio: omitir o botão (sem link quebrado).
- Agregação CSS/cache: após `cr`, o layout split é o que o visitante vê.
- Convivência com CTA final (`026`): dois CTAs de cadastro na página são aceitáveis (seção + rodapé da landing); esta feature **não** remove o CTA final.

## Requirements *(obrigatório)*

### Functional Requirements

- **FR-001**: A seção Benefícios em `/para-estudantes` MUST apresentar badge, título, subtítulo, lista de benefícios com ícone e CTA conforme o frame Figma de referência desta feature.
- **FR-002**: A seção MUST exibir os seis benefícios canônicos na ordem listada no Escopo (salvo copy editorial divergente intencional já publicada).
- **FR-003**: No desktop, o layout MUST ser em duas áreas: conteúdo textual/itens/CTA à esquerda e colagem circular à direita.
- **FR-004**: Em viewports estreitas, a seção MUST permanecer legível e sem scroll horizontal causado por ela; a colagem MUST NOT impedir leitura do conteúdo.
- **FR-005**: O botão “Cadastre-se gratuitamente” MUST levar o visitante a `/cadastro/candidato` no seed canônico.
- **FR-006**: A feature MUST reutilizar o block type `beneficios_estudantes` e o paragraph de ícone+texto existentes; MUST NOT criar uma segunda seção de benefícios na mesma rota.
- **FR-007**: A apresentação antiga de 4 cards centrados (`022`/`053`) MUST deixar de ser a UI pública desta seção após o deploy da feature.
- **FR-008**: Um `hook_update_N` idempotente MUST garantir seed/fill dos 6 itens, ícones, CTA, assets da colagem e copy canônica apenas quando vazio ou legado conhecido.
- **FR-009**: Alterações estruturais MUST ser exportáveis via `drush cex` e versionadas; destino sem passos manuais no Block Layout.
- **FR-010**: O `PRD.md` MUST ser atualizado de forma cirúrgica na descrição da seção Benefícios Estudantes.
- **FR-011**: Banner, Hero Search, jornada, destaque de vagas e CTA final MUST NOT regredir por causa desta feature.
- **FR-012**: Estilos da seção MUST permanecer com escopo isolado (não alterar visual de Benefícios PE / diferenciais home / Quem Somos).

### Key Entities

- **Benefícios Estudantes (bloco)**: seção da landing com badge, título, subtítulo, lista de itens, CTA e referência visual à colagem.
- **Item de benefício**: ícone + título curto + descrição (paragraph reutilizado).
- **Colagem circular**: composição visual de marca (arte versionada) associada à seção.
- **CTA da seção**: link de conversão para cadastro de candidato.

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: Em revisão lado a lado com o frame Figma, ≥90% dos revisores confirmam correspondência de estrutura (badge + título + 6 itens + CTA + colagem no desktop).
- **SC-002**: Em desktop (≥992px), visitante identifica os **6** benefícios e o CTA em menos de 10 segundos após chegar à seção.
- **SC-003**: Em mobile (~375px), a seção não causa scroll horizontal; CTA permanece acionável com um toque.
- **SC-004**: 100% dos cliques no CTA da seção (seed) levam a `/cadastro/candidato`.
- **SC-005**: Após o deploy, 0 ocorrências da grade pública antiga de 4 cards centrados como layout da seção Benefícios.
- **SC-006**: Reexecução do update automatizado não duplica itens/blocos nem altera copy editorial divergente intencional.
- **SC-007**: Deploy em destino sem intervenção manual no Block Layout para ativar o novo layout/seed.

## Assumptions

- Esta feature **substitui** o visual e o copy canônico da seção Benefícios entregues em `022`/`053` (4 cards); não cria bloco paralelo.
- A colagem é **arte de marca versionada** (como mock visual do perfil destaque), não um CMS de 7 fotos individuais nesta versão; o plan pode optar por uma única imagem composta ou montagem CSS/Twig a partir de assets.
- Badge, título, subtítulo, itens e CTA são gerenciáveis no painel na medida em que os campos existam ou sejam acrescentados por **reuso de storages canônicos** (`field_text_simple`, `field_text_simple_long`, `field_link`, `field_image`, `field_itens_lista`); decisão exata de field instances no plan — **proibido** storage paralelo com o mesmo propósito.
- Legado conhecido das 4 cards (`Vagas alinhadas` etc.) pode ser substituído pelo seed dos 6 itens **somente** quando o conteúdo ainda for o seed antigo ou estiver vazio; copy claramente reescrita pelo editorial permanece.
- Ícones dos 6 itens serão versionados em `modules/custom/custom_configs/assets/` (caminho exato no plan) e copiados para `public://` pelo hook.
- Breakpoints de tema (`046`) e tokens de marca (laranja CTA, azul título) seguem o design system já usado em `/para-estudantes`.
- Próximo `hook_update_N` livre: `11078`.
- Receita de destino padrão: `cim` → `updb` → `cim` → `cr`.
- Feature `053` permanece válida para banner/Hero/perfil oculto/vão CTA; esta feature **só** reabre o capítulo Benefícios com o Figma novo.
- Esta especificação **não** entrega código.
