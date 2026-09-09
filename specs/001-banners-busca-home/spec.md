# Especificação de Feature: Centralização de Banners e Busca da Home

**Feature Directory**: `specs/001-banners-busca-home`
**Criada em**: 2026-09-09
**Status**: Draft
**Input do usuário**: Refatoração e centralização de banners (novo content type `banners`, absorção de `banner_internas`, controle de exibição Home/Internas, imagens responsivas desktop/mobile), bloco customizado de busca com textos fixos e quick filters (pills), modernização dos filtros da View `vagas` (curso + regime presencial/remoto), fluxo de deploy via Configuration Management + `hook_update_N`, e governança de atualização do `PRD.md`.

## Clarifications

### Session 2026-09-09

- Q: O que acontece com o texto sobreposto (`field_text_simple_long`) dos banners internos no novo tipo `banners`? → A: Overlay eliminado (opção B) — banners internos passam a ser arte completa como a home (texto embutido na imagem); o campo `field_text_simple_long` não é portado e o texto legado é migrado para o `alt` da imagem desktop.
- Q: O campo "Cidade ou Remoto" do bloco de busca filtra por qual(is) campo(s) da vaga? → A: Somente regime — aplica a regra "Presencial ou Remoto" estritamente sobre `field_regime_t` (confirma FR-8); o campo NÃO filtra por cidade.
- Q: Como deve ser o mecanismo de ordenação dos banners dentro de cada local (Home/Internas)? → A: Sem campo novo — listagens ordenadas pela data de publicação do banner, mais recente primeiro (opção C).

## User Scenarios & Testing *(obrigatório)*

### Cenário 1 — Visitante vê banners corretos por página e dispositivo (P1)

1. Visitante acessa a **home** e vê o carrossel apenas com banners marcados para exibição na "Home".
2. Visitante acessa uma **página interna** e vê apenas banners marcados como "Internas".
3. Em viewport **mobile**, o banner servido é a imagem mobile; em **desktop**, a imagem desktop — nunca a imagem errada para o dispositivo.
4. O primeiro banner da home carrega com prioridade alta (LCP otimizado); banners subsequentes usam carregamento lazy.

### Cenário 2 — Editor gerencia banners sem suporte técnico (P1)

1. Editor cria um conteúdo do tipo `banners`, envia imagem desktop e mobile, escolhe o local de exibição ("Home" ou "Internas") e publica.
2. O banner passa a aparecer automaticamente no local escolhido, sem alteração de código, blocos ou Views.
3. Editor despublica o banner e ele some do site.

### Cenário 3 — Visitante busca vagas pelo bloco hero (P1)

1. Visitante vê o título "Encontre seu estágio ideal hoje." e o subtítulo institucional fixos no bloco de busca.
2. No campo **"Qual o seu curso?"**, digita sua área de estudo/curso e recebe resultados de vagas associadas a essa área.
3. No campo **"Cidade ou Remoto"**, escolhe a modalidade; a busca aplica a regra de negócio **"Presencial ou Remoto"** sobre o campo de regime da vaga.
4. Ao submeter, é levado à listagem de vagas já filtrada pelos dois critérios combinados.

### Cenário 4 — Visitante usa Quick Filters (pills) (P2)

1. Abaixo da busca, o visitante vê as pills "Remoto", "TI", "Administração", "Design", "Marketing".
2. Ao clicar em uma pill, o valor correspondente é aplicado ao filtro adequado e a busca é disparada automaticamente, sem necessidade de digitar ou clicar em "buscar".

### Cenário 5 — Deploy local → produção sem dump de banco (P2)

1. Toda alteração estrutural (content type, campos, Views, blocos) é exportada via Configuration Management e versionada no Git.
2. Na produção, após `drush cim` e `drush updb`, os conteúdos legados de `banner_internas` foram migrados para `banners` e o tipo antigo foi removido — sem importação de dump de banco.

### Edge Cases

- Banner sem imagem mobile cadastrada → exibir a imagem desktop como fallback (nunca quebrar o layout).
- Banner sem local de exibição selecionado → não exibir em nenhum local até o editor corrigir.
- Nenhum banner publicado para um local → a seção de banner é omitida (sem container vazio).
- Pill referencia termo inexistente na taxonomia → pill não é renderizada (ou submissão retorna listagem sem filtro inválido).
- Busca submetida vazia → listagem padrão de vagas, sem erro.
- Visitante digita nome de cidade no campo de regime do hero → nenhum filtro de cidade é aplicado pelo bloco; o campo aceita apenas valores de regime (Presencial/Remoto).
- Conteúdo legado `banner_internas` sem imagem mobile → migrar usando imagem desktop como fallback.
- Conteúdo legado `banner_internas` com texto sobreposto (`field_text_simple_long`) → texto migrado para o `alt` da imagem desktop; nenhum overlay HTML é renderizado após a migração.

## Requirements *(obrigatório)*

### Functional Requirements

**Banners**

- **FR-1**: O sistema DEVE ter um único tipo de conteúdo `banners` que centraliza todos os banners do site (home e internas).
- **FR-2**: O tipo `banners` DEVE possuir um campo obrigatório de local de exibição (ex.: `field_local_exibicao`) com pelo menos os valores "Home" e "Internas"; as listagens de banners DEVEM ser filtradas por esse campo e ordenadas por `field_peso` ASC (menor peso primeiro), com desempate por data de publicação DESC.
- **FR-3**: O tipo `banners` DEVE possuir campos de imagem separados para desktop (`field_imagem_desktop`) e mobile (`field_imagem_mobile`), com mobile opcional e fallback para desktop.
- **FR-4**: A renderização DEVE servir a imagem correta por viewport usando tag `<picture>` com media queries nativas e estilos de imagem; o primeiro banner visível DEVE carregar com prioridade alta (`fetchpriority="high"`, sem lazy) e os demais com `loading="lazy"`.
- **FR-5**: O tipo de conteúdo legado `banner_internas` DEVE ser totalmente absorvido: conteúdos migrados para `banners` (com local de exibição "Internas") e o tipo removido/depreciado estruturalmente durante o deploy.
- **FR-5a**: O block bundle `banner` existente (carrossel atual da home, com `field_image`/`field_image_mobile` e rollback para template legacy) TAMBÉM DEVE ser absorvido pelo novo content type `banners` (decisão Q1-A): seus conteúdos são migrados com local de exibição "Home", o carrossel passa a ser alimentado pela listagem filtrada de `banners`, e o block bundle legado é removido/depreciado após a migração.
- **FR-5b**: O campo de texto sobreposto `field_text_simple_long` do `banner_internas` NÃO DEVE ser portado para o tipo `banners` (decisão Q1-B da sessão 2026-09-09): banners internos passam a ser arte completa (texto embutido na imagem), sem overlay HTML; na migração, o texto legado DEVE ser preservado no `alt` da imagem desktop.

**Bloco de busca (hero)**

- **FR-6**: O bloco de busca DEVE ser customizado (código), com título ("Encontre seu estágio ideal hoje.") e subtítulo ("Conectamos talentos universitários às melhores oportunidades do mercado através de uma plataforma moderna e intuitiva.") **fixos no template/lógica de renderização** — NENHUM campo administrável no banco de dados para esses textos.
- **FR-7**: O campo "Qual o seu curso?" DEVE filtrar vagas pela área de estudo/curso, mapeado ao campo `field_cursos_t` da vaga (taxonomia), com interface de busca moderna (ex.: autocomplete).
- **FR-8**: O campo com placeholder "Cidade ou Remoto" DEVE aplicar a regra de negócio "Presencial ou Remoto", filtrando estritamente pelo campo `field_regime_t` da vaga. O campo NÃO filtra por `field_cidade` (decisão Q2 da sessão 2026-09-09); a busca por cidade permanece disponível apenas no filtro exposto `cidade` já existente na listagem `/para-estudantes`.
- **FR-9**: Os dois filtros DEVEM funcionar de forma combinada na View `vagas` (exposed filters), redirecionando à listagem filtrada.
- **FR-10**: As pills ("Remoto", "TI", "Administração", "Design", "Marketing") DEVEM, ao clique, preencher o filtro correspondente e disparar a busca automaticamente. "Remoto" aplica o filtro de regime; as demais aplicam o filtro de curso/área.
- **FR-11**: O bloco de busca hero DEVE ser exibido **somente na home** (decisão Q2-A); demais páginas públicas mantêm a listagem/filtros atuais.

**Deploy e governança**

- **FR-12**: TODA alteração estrutural (content type, campos, Views, posicionamento de blocos) DEVE ser exportável via Configuration Management (`drush cex` → `config/sync`) e versionada no Git.
- **FR-13**: A migração de conteúdo legado e a remoção do tipo antigo DEVEM ser executadas programaticamente (`hook_update_N` ou script Drush) durante o deploy — PROIBIDO exigir importação/exportação de dump de banco.
- **FR-14**: Deve existir uma regra de contexto (Cursor) que dispare a atualização do `PRD.md` somente quando houver alterações estruturais reais (content types, fields, taxonomias, rotas, dependências) — sem `alwaysApply`, para economia de tokens, alinhada à governança da §8 do PRD.
- **FR-15**: O `PRD.md` DEVE ser atualizado refletindo precisamente as mudanças aprovadas: unificação no content type `banners` e seus campos, remoção do `banner_internas`, e mudanças nos filtros da View `vagas`.

### Key Entities

- **`banners` (node)**: banner centralizado — imagens desktop/mobile, local de exibição, `field_peso` (menor primeiro), status de publicação; sem campo de texto sobreposto (overlay eliminado — decisão Q1-B; arte completa em todos os locais).
- **`vagas` (node)**: anúncio de estágio — `field_cursos_t` (área/curso) e `field_regime_t` (presencial/remoto) como eixos de busca.
- **Taxonomias de cursos e regime**: vocabulários que alimentam os filtros e as pills (machine names a documentar no PRD após export — gap R4 existente).

## Success Criteria *(obrigatório)*

- **SC-1**: Um editor publica ou remove um banner em qualquer local do site em menos de 5 minutos, sem suporte técnico.
- **SC-2**: Após o deploy, 100% dos banners do site são servidos pelo tipo `banners` e não resta nenhuma referência funcional ao tipo `banner_internas` (tipo removido, zero conteúdo órfão).
- **SC-3**: Em 100% dos banners, dispositivos mobile nunca baixam a imagem desktop (verificável por inspeção de rede), e o LCP da home não degrada em relação à baseline atual.
- **SC-4**: Um visitante consegue filtrar vagas por curso e/ou regime e chegar a resultados relevantes em até 2 interações (digitar+submeter ou 1 clique em pill + submissão automática).
- **SC-5**: O deploy em produção é concluído apenas com `git pull` + importação de configuração + atualizações de banco programáticas, sem nenhuma importação de dump.
- **SC-6**: Textos institucionais do bloco de busca exigem zero linhas no banco de dados (100% em código/template).

## Assumptions

- Os vocabulários de taxonomia para cursos e regime já existem no banco (machine names serão exportados/documentados no milestone de Config Management — gap R4 do PRD).
- As pills "TI", "Administração", "Design" e "Marketing" correspondem a termos existentes (ou a criar) no vocabulário de cursos/áreas; "Remoto" corresponde a um termo do vocabulário de regime.
- O carrossel da home mantém o comportamento visual atual (Bootstrap carousel, indicadores, autoplay), mudando apenas a fonte de dados.
- Banners internos legados foram produzidos sem texto na arte (o texto era overlay HTML); após a migração exibem apenas a imagem, com o texto preservado no `alt` — novas artes de banners internos devem embutir o texto na imagem (decisão Q1-B).
- Textos fixos do bloco são apenas em pt-BR nesta fase.
- A listagem de destino da busca é a página pública de vagas existente (`/para-estudantes`).
