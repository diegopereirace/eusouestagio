# Feature Specification: Deploy em Produção — Consolidação do Novo Layout (Home + Quem Somos)

**Feature Directory**: `specs/016-deploy-producao-layout`  
**Created**: 2026-09-23  
**Status**: Draft  
**Input do usuário**: Especificar e executar o **Deploy em Produção** de todas as alterações recentes de layout (novas Views, Blocks, Paragraphs, Nodes e CSS) da Home e da página Quem Somos (Diferenciais, Metodologia, Missão/Visão, Números, CTA v1, Rodapé). Como o desenvolvimento foi feito localmente com SDD, todas as alterações de banco estão encapsuladas em `hook_update_N` e as configurações exportadas em `config/sync`. O deploy DEVE ser precedido de backup completo do banco PostgreSQL 16, executado via esteira automatizada (`git` → `composer install --no-dev --optimize-autoloader` → `drush updatedb -y` → `drush config:import -y` → `drush cache:rebuild`), com procedimento de rollback documentado e verificação final das rotas `<front>` e `/quem-somos`.

## Resumo das Alterações

Release consolidado de **32 commits** (`origin/main..dev`), com **+21.998 / −1.015 linhas em 234 arquivos**:

**Home (`<front>`)**

- Topo/cabeçalho responsivo conforme Figma, com novos botões (feature `002-topo-figma-home`).
- Carrossel de banners full-width + busca hero com autocomplete de cursos (feature `001-banners-busca-home`, módulo `custom_banners` com `HeroSearchBlock` e `CursosAutocompleteController`).
- Vagas em destaque com ícones por categoria e layout responsivo (feature `003-vagas-destaque-home`).
- Blocos gerenciáveis: Nossos Diferenciais (`004`), Nossa Metodologia (`005`), O Que Fazemos (`006`), Como Funciona (`007`) — itens via Paragraphs.
- Novo bloco de depoimentos.
- Rodapé redesenhado com colunas de menu, tagline e links (feature `008-rodape-redesign`).

**Quem Somos (`/quem-somos`)**

- Banner bipartido com novo bloco (feature `009`).
- Layout Sobre Nós com texto contornando imagem e elementos decorativos (feature `010`).
- Bloco Missão/Visão responsivo (feature `011`) + substituição do layout legado por campos dedicados no node (feature `012`).
- Diferenciais Quem Somos com grid de ícones e rótulos (feature `013`).
- Seção Impact in Numbers com dados estatísticos (feature `014`).
- Bloco CTA v1 com título, subtítulo e dois botões (feature `015`).

**Estrutura e infraestrutura**

- 19 atualizações de banco automatizadas e idempotentes: `custom_configs_update_11001` a `custom_configs_update_11019` (block types, field storages/instances, displays, paragraphs, seeds via Entity API e placements).
- Configurações versionadas em `config/sync`: novas Views e displays, fields, paragraphs (`diferencial_simples_p`, `numero_destaque_p`), block types (`cta_v1`, `diferenciais_quem_somos`) e **remoção de legados** (`banner` legacy, `field_titulo_2`, `field_imagem_2`, `field_text_long_formatted_2`).
- Novos assets CSS/JS no tema `default` (banner-carousel, hero-search, banner-quem-somos, block-missao-visao, cta-v1, diferenciais-quem-somos, impact-numbers, layout-sobre-nos, quem-somos-missao-visao) e ~20 novos templates Twig.
- Melhorias na gestão de modais com z-index dinâmico.

## Escopo

### Inclui

- Backup completo do banco PostgreSQL 16 de produção, gzipado, com timestamp no nome, salvo **fora da raiz pública** e verificado (existência + tamanho) **antes** de qualquer alteração.
- Consolidação do código: merge de `origin/dev` em `main` e atualização do working tree do servidor para a `main` mais recente.
- Instalação de dependências de produção via Composer (`--no-dev --optimize-autoloader`).
- Execução de todas as atualizações de banco pendentes (`drush updatedb -y` → roda os `hook_update_N`).
- Sincronização das configurações versionadas (`drush config:import -y` → cria Views, displays, fields, blocos estruturais e remove legados).
- Reconstrução de caches (`drush cache:rebuild`).
- Verificação pós-deploy das rotas `<front>` e `/quem-somos`: HTTP 200, presença das classes `.container`, novos blocos renderizados, layouts `full-width` aplicados, ausência de erros 500 e de erros de configuração no log.
- Procedimento de rollback documentado e pronto para execução (seção dedicada abaixo).

### Fora

- Alterações de conteúdo editorial diretamente em produção.
- Qualquer feature ou alteração de código além do que já está consolidado em `dev`.
- Mudanças de infraestrutura (Apache, SSL/TLS, PHP, PostgreSQL, SO).
- Versionamento ou deploy de `.env` (nunca commitado; ver regra de segurança do projeto).
- Edição manual de arquivos em `core/` ou `vendor/` no servidor.
- Janela de manutenção com site fora do ar (deploy sem downtime planejado — ver Assumptions).

## User Scenarios & Testing *(obrigatório)*

### User Story 1 — Backup de segurança antes de qualquer mudança (Priority: P1)

O responsável pelo release conecta-se ao servidor de produção e executa um dump completo do banco de dados, gzipado, com timestamp, fora da raiz pública, confirmando o sucesso e o tamanho do arquivo **antes** de baixar código ou rodar atualizações.

**Why this priority**: É o portão de segurança do release — sem backup verificado, nenhuma outra etapa pode acontecer; é o que garante o rollback.

**Independent Test**: Após o comando de dump, listar o arquivo no diretório de backups e confirmar que existe, é recente e tem tamanho maior que zero (coerente com dumps anteriores).

**Acceptance Scenarios**:

1. **Given** acesso SSH ao servidor de produção, **When** o responsável executa o dump do banco para `../backups/backup_eu_sou_estagio_<timestamp>.sql.gz` (fora da raiz pública), **Then** o comando conclui sem erro e o arquivo é criado.
2. **Given** o dump concluído, **When** o responsável lista o diretório de backups, **Then** o arquivo aparece com tamanho > 0 e timestamp correspondente à execução.
3. **Given** qualquer falha no dump (erro, arquivo vazio ou inexistente), **When** o responsável verifica o resultado, **Then** o deploy é abortado antes de qualquer alteração e a falha é reportada.

---

### User Story 2 — Esteira de deploy 100% automatizada (Priority: P1)

O responsável executa sequencialmente: atualização do código via Git (merge de `dev` em `main` + pull), instalação de dependências de produção, atualizações de banco, importação de configurações e reconstrução de caches — sem nenhum passo manual no painel administrativo.

**Why this priority**: Requisito explícito do projeto (Configuration Management + hooks idempotentes); passos manuais em produção são fonte de divergência e erro.

**Independent Test**: Em produção desatualizada, executar a esteira e confirmar que cada etapa conclui com exit code 0 e que o estado final (banco + config ativa) corresponde ao versionado.

**Acceptance Scenarios**:

1. **Given** o backup verificado, **When** executados `git fetch origin` → `git checkout main` → `git merge origin/dev` → `git pull origin main`, **Then** o working tree do servidor passa a conter exatamente o código mais recente da branch de produção, sem conflitos.
2. **Given** o código atualizado, **When** executado `composer install --no-dev --optimize-autoloader`, **Then** todas as dependências de produção são instaladas sem pacotes de desenvolvimento e com autoloader otimizado.
3. **Given** as dependências instaladas, **When** executado `drush updatedb -y`, **Then** todos os `hook_update_N` pendentes (`11001`–`11019`) rodam com sucesso, criando/populando blocos e paragraphs de forma automatizada.
4. **Given** o banco atualizado, **When** executado `drush config:import -y`, **Then** as novas Views, displays, fields e blocos estruturais são criados e os legados removidos, sem erro de sincronização.
5. **Given** a configuração importada, **When** executado `drush cache:rebuild`, **Then** os caches são reconstruídos e o site responde normalmente.
6. **Given** qualquer etapa com falha (exit code ≠ 0 ou erro no output), **When** detectada, **Then** a esteira para imediatamente e o procedimento de rollback é avaliado/acionado.

---

### User Story 3 — Visitante vê o novo layout sem erros (Priority: P1)

Após o deploy, um visitante anônimo acessa `<front>` e `/quem-somos` e vê o novo layout completo: containers alinhados, novos blocos renderizados, seções full-width onde previsto, sem erros 500 ou mensagens de configuração.

**Why this priority**: É o resultado final do release — o valor entregue ao público.

**Independent Test**: Requisitar `<front>` e `/quem-somos` como anônimo e inspecionar status HTTP e HTML renderizado.

**Acceptance Scenarios**:

1. **Given** o deploy concluído, **When** o visitante acessa `<front>`, **Then** a resposta é HTTP 200 e o HTML contém as classes `.container`, o carrossel de banners full-width, a busca hero e os blocos Nossos Diferenciais, Nossa Metodologia, O Que Fazemos, Como Funciona, Vagas em Destaque, Depoimentos e o novo Rodapé.
2. **Given** o deploy concluído, **When** o visitante acessa `/quem-somos`, **Then** a resposta é HTTP 200 e o HTML contém o banner bipartido, Sobre Nós, Missão/Visão, Diferenciais, Impact in Numbers e o CTA v1 (`.cta-v1` / `.block-cta-v1`), nesta ordem visual.
3. **Given** as duas rotas, **When** inspecionadas, **Then** não há erro 500, WSOD, nem mensagens de erro de configuração; o log recente do site não registra erros novos após o deploy.
4. **Given** viewport mobile, **When** o visitante navega nas duas rotas, **Then** não há scroll horizontal e os elementos responsivos se comportam conforme as specs de cada feature.

---

### User Story 4 — Rollback completo em caso de falha (Priority: P1)

Se o deploy falhar de forma irrecuperável (erro 500 persistente, importação de config corrompida, atualização de banco quebrada), o responsável restaura o banco a partir do backup e reverte o código para o commit anterior ao deploy, devolvendo o site ao estado funcional anterior.

**Why this priority**: Toda mudança em produção precisa de saída segura; sem rollback ensaiado, o backup vira peça decorativa.

**Independent Test**: Seguir o Procedimento de Rollback (seção dedicada) e confirmar que as rotas críticas voltam a responder HTTP 200 com o layout anterior.

**Acceptance Scenarios**:

1. **Given** uma falha irrecuperável após o deploy, **When** o responsável reverte o Git para o commit registrado antes do deploy e restaura o dump no banco, **Then** o site volta a responder HTTP 200 com o layout anterior em até 15 minutos.
2. **Given** o rollback executado, **When** os caches são reconstruídos e as rotas `<front>` e `/quem-somos` verificadas, **Then** não há erro 500 nem referências a configurações inexistentes.
3. **Given** o início do deploy, **When** o responsável registra o hash do commit atual do servidor, **Then** esse hash é o alvo garantido de reversão de código.

### Edge Cases

- Dump falha ou gera arquivo vazio → deploy abortado antes de qualquer alteração; investigar espaço em disco/permissões e tentar novamente.
- Working tree do servidor com alterações locais não commitadas (ex.: `settings.php`, arquivos de ambiente) → `git checkout`/`merge` podem falhar; resolver preservando arquivos de ambiente antes de prosseguir.
- Conflito de merge entre `main` e `origin/dev` → não forçar; abortar o merge, reportar e resolver na origem (repositório de desenvolvimento) antes de repetir o deploy.
- `composer install` falha (rede, memória, plataforma) → não prosseguir para `updatedb`; código e banco permanecem íntegros (nada de banco foi alterado ainda).
- `drush updatedb` falha no meio → banco pode estar parcialmente atualizado; **não** tentar reexecutar às cegas em loop — avaliar o erro e, se irrecuperável, acionar rollback (o dump é do estado íntegro).
- `drush config:import` reporta diferenças inesperadas ou erro de dependência → interromper, registrar o erro e avaliar rollback.
- Reexecução da esteira após sucesso → hooks idempotentes e config já sincronizada tornam `updatedb`/`cim` no-op seguros.
- Tráfego ativo durante o deploy → caches reconstruídos ao final minimizam respostas inconsistentes; usuários podem ver layout antigo até o `cache:rebuild`.

## Requirements *(obrigatório)*

### Functional Requirements

**Backup (portão obrigatório)**

- **FR-001**: Antes de qualquer alteração em produção, DEVE ser executado dump completo do banco PostgreSQL 16 via `drush sql-dump --result-file=../backups/backup_eu_sou_estagio_$(date +%Y%m%d_%H%M%S).sql --gzip` (ou equivalente), com destino **fora** da raiz pública `web/`.
- **FR-002**: O sucesso do backup DEVE ser confirmado por verificação explícita do arquivo gerado (existência e tamanho > 0, coerente com o banco) antes de prosseguir; em caso de falha, o deploy DEVE ser abortado.
- **FR-003**: O hash do commit atualmente ativo no servidor DEVE ser registrado antes da atualização de código, como alvo de rollback.

**Esteira de deploy**

- **FR-004**: O código DEVE ser atualizado exclusivamente via Git: `git fetch origin`, `git checkout main`, `git merge origin/dev`, `git pull origin main` — garantindo que `main` contenha tudo o que está em `dev`, sem edição manual de arquivos no servidor.
- **FR-005**: As dependências DEVEm ser instaladas com `composer install --no-dev --optimize-autoloader`, sem pacotes de desenvolvimento.
- **FR-006**: As atualizações de banco DEVEm ser aplicadas com `drush updatedb -y`, executando todos os `hook_update_N` pendentes de forma automatizada e idempotente.
- **FR-007**: As configurações DEVEm ser sincronizadas com `drush config:import -y` a partir de `config/sync`, criando Views, displays, fields e blocos estruturais novos e removendo os legados.
- **FR-008**: Ao final, os caches DEVEm ser reconstruídos com `drush cache:rebuild`.
- **FR-009**: Nenhuma etapa do deploy PODE exigir ação manual no painel administrativo de produção.
- **FR-010**: Cada etapa DEVE ser verificada (exit code e output) antes da próxima; qualquer falha interrompe a esteira e dispara a avaliação de rollback.

**Verificação pós-deploy**

- **FR-011**: As rotas `<front>` e `/quem-somos` DEVEm responder HTTP 200 para usuário anônimo após o deploy.
- **FR-012**: O HTML de `<front>` DEVE conter as classes `.container`, o carrossel full-width, a busca hero e os novos blocos da Home; o HTML de `/quem-somos` DEVE conter os blocos Banner, Sobre Nós, Missão/Visão, Diferenciais, Impact in Numbers e CTA v1.
- **FR-013**: Os layouts `full-width` previstos DEVEM estar aplicados (ausência de contenção indevida nas seções full-bleed e contenção correta nas demais via `.container`).
- **FR-014**: NÃO DEVE haver erro 500, WSOD ou erros novos de configuração no log do site após o deploy.

**Rollback**

- **FR-015**: DEVE existir procedimento de rollback documentado (seção própria nesta spec) cobrindo reversão de código (Git para o hash registrado) e restauração do banco (dump do início do deploy), com verificação final das rotas críticas.

### Key Entities

- **Release**: conjunto de 32 commits de `origin/main..dev` (features `001`–`015` + ajustes), consolidado em `main`.
- **Backup de segurança**: dump PostgreSQL 16 gzipado com timestamp, armazenado fora da raiz pública; ponto de restauração (RPO = início do deploy).
- **Configuração versionada**: conjunto de YAMLs em `config/sync` que passa a ser a config ativa após a importação.
- **Atualizações de banco**: `custom_configs_update_11001`–`11019`, idempotentes, executadas uma única vez por ambiente.
- **Ambiente de produção**: VPS com Apache + HTTPS (Let's Encrypt), aplicação em `/var/www/html`, acesso SSH por chave.

## Plano de Release (Runbook normativo)

Sequência obrigatória executada no servidor de produção, nesta ordem, cada passo condicionado ao sucesso do anterior:

1. **Backup**: `drush sql-dump --result-file=../backups/backup_eu_sou_estagio_$(date +%Y%m%d_%H%M%S).sql --gzip` → verificar arquivo (FR-001, FR-002).
2. **Registro de rollback**: anotar `git rev-parse HEAD` do servidor (FR-003).
3. **Versionamento**: `git fetch origin` → `git checkout main` → `git merge origin/dev` → `git pull origin main`.
4. **Dependências**: `composer install --no-dev --optimize-autoloader`.
5. **Banco e conteúdo**: `drush updatedb -y`.
6. **Configurações**: `drush config:import -y`.
7. **Caches**: `drush cache:rebuild`.
8. **Verificação**: rotas `<front>` e `/quem-somos` (FR-011 a FR-014).

## Procedimento de Rollback

Acionado quando uma falha irrecuperável é detectada em qualquer etapa após o início das alterações de banco/config, ou quando a verificação pós-deploy reprova as rotas críticas.

**Pré-requisitos já garantidos pela esteira**: dump íntegro do início do deploy (FR-001/FR-002) e hash do commit anterior registrado (FR-003).

**Passo a passo**:

1. **Reverter o código**: no servidor, `git reset --hard <hash-registrado>` (hash do commit ativo antes do deploy) seguido de `composer install --no-dev --optimize-autoloader` para alinhar dependências à versão revertida.
2. **Restaurar o banco**: `gunzip -c ../backups/backup_eu_sou_estagio_<timestamp>.sql.gz | drush sql-cli` (equivalente: `gunzip` + `psql` direto no banco de produção). Isso desfaz `hook_update_N` e importações de config parciais ou totais.
3. **Reconstruir caches**: `drush cache:rebuild`.
4. **Verificar**: `<front>` e `/quem-somos` respondendo HTTP 200 com o layout anterior; log sem erros novos.
5. **Reportar**: registrar causa da falha, etapa atingida e ações tomadas antes de nova tentativa de deploy.

**Alvos**: RTO ≤ 15 minutos; RPO = estado do banco no início do deploy (conteúdo editorial criado durante a janela do deploy é perdido — mitigado pela janela curta e horário de baixo tráfego).

## Success Criteria *(obrigatório)*

### Measurable Outcomes

- **SC-001**: 100% dos deploys iniciados somente após backup verificado (arquivo existe, tamanho > 0, fora da raiz pública); nenhuma alteração de código/banco ocorre sem esse portão.
- **SC-002**: A esteira completa (Git → Composer → updatedb → config:import → cache:rebuild) executa sem nenhum passo manual no painel administrativo.
- **SC-003**: 100% das rotas críticas (`<front>` e `/quem-somos`) respondem HTTP 200 em até 3 segundos após o deploy.
- **SC-004**: Zero erros 500 e zero erros novos de configuração no log do site na verificação pós-deploy.
- **SC-005**: Inspeção do HTML confirma 100% dos elementos-chave do novo layout: classes `.container`, seções full-width, blocos novos da Home e os 6 blocos/seções de Quem Somos (incluindo CTA v1).
- **SC-006**: Indisponibilidade percebida do site durante todo o processo inferior a 5 minutos.
- **SC-007**: Em caso de falha, rollback concluído (código + banco + caches + verificação) em até 15 minutos.
- **SC-008**: Reexecução acidental de `updatedb`/`config:import` após o sucesso não gera duplicações nem sobrescreve conteúdo editorial (idempotência preservada).

## Assumptions

- **Branch de produção**: `main`; origem das novidades: `origin/dev` (32 commits à frente, já sincronizado localmente com `origin/dev`).
- **Acesso ao repositório no servidor**: o fetch/pull no servidor usa o remote configurado em `/var/www/html` (GitHub via HTTPS ou bare repo local da VPS); a topologia exata é verificada na execução e a esteira é adaptada apenas no nome do remote, sem mudar os requisitos.
- **Ferramentas no servidor**: Drush acessível a partir da raiz da aplicação (`vendor/bin/drush` ou global) e Composer disponível para o usuário do deploy.
- **Diretório de backups**: `../backups` relativo à raiz da aplicação (fora da raiz pública); criado se inexistente.
- **Arquivos de ambiente**: `.env` e `settings.php` de produção já estão configurados, não são versionados e não são tocados pelo deploy (`settings.php.prod` é apenas referência versionada).
- **Ordem updb → cim**: executada conforme runbook solicitado; os hooks são idempotentes com UUIDs fixos iguais aos do `config/sync`, tornando a ordem segura em qualquer direção.
- **Sem janela de manutenção**: deploy em horário de baixo tráfego, com indisponibilidade percebida mínima (rebuild de cache ao final).
- **Conteúdo editorial de produção**: preservado; seeds só preenchem instâncias ausentes/vazias (regra já garantida pelos hooks das features `001`–`015`).
- **Core 11.4.7**: já presente em `main` (commit `a30c8b68`) e, presumivelmente, já em produção; este release não altera a versão do core.
