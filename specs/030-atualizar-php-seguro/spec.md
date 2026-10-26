# Feature Specification: Atualizar o PHP para a release segura 8.4.27

**Feature Branch**: `030-atualizar-php-seguro`

**Created**: 2026-10-22

**Status**: Draft

**Input**: User description: "Atualizar o PHP do Eu Sou Estágio para a minor/patch mais recente e segura suportada pelo Drupal 11, de preferência a linha PHP 8.4, cobrindo as vulnerabilidades publicadas no arquivo de segurança do PHP de 2026, no ambiente local e com um procedimento seguro para a VPS de produção."

## Escopo

### Inclui

- Elevar o interpretador que serve o site para **PHP 8.4.27** ou um patch posterior da mesma linha 8.4 que já incorpore as correções de 21/10/2026
- Manter o site no ar, com o mesmo conteúdo, o mesmo banco e os mesmos arquivos publicados, depois da troca
- Preservar as capacidades que o produto já usa: páginas com imagem, conexão com o banco, HTTPS local e endereços limpos
- Alinhar o ambiente local e a produção na mesma linha de PHP
- Entregar um procedimento de produção com backup antes da troca, janela curta e confirmação da versão ao final
- Não introduzir uma trava de versão de PHP no manifesto de dependências: hoje essa trava não existe e deve continuar ausente, para o ambiente real ser o que vale

### Fora

- Mudar de servidor web ou adotar uma imagem mínima que exija outro modelo de processo
- Atualizar o núcleo do Drupal, módulos contribuídos ou o banco de dados
- Aplicar paliativos de configuração no lugar da atualização (limite de memória ilimitado, desligar progresso de upload de sessão ou reduzir tamanho de POST). O aviso de 21/10/2026 afirma que não há mitigação completa
- PHP 8.5, PHP 8.6 ou qualquer linha diferente de 8.4
- Permanecer na linha 8.3, mesmo no patch 8.3.36 que também corrige os dois avisos críticos
- Alterar `core/` ou `vendor/` à mão
- Endurecer outras superfícies (reCAPTCHA, papéis, `devel`) nesta entrega

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Site deixa de estar exposto aos RCE de outubro de 2026 (Priority: P1)

Quem opera a plataforma precisa que o Eu Sou Estágio rode um PHP que já contenha as correções publicadas em 21/10/2026. Nessa data o projeto PHP lançou correções para duas falhas críticas de execução remota de código sem autenticação, ambas com CVSS 4.0 9.2: CVE-2026-109618 e CVE-2026-109619. O risco é maior no modo em que o PHP roda dentro do servidor web — exatamente o modo do ambiente local atual. A linha pedida é a 8.4, e a release que fecha esses dois avisos nessa linha é a **8.4.27**.

**Why this priority**: Sem essa versão, o site continua na superfície que o próprio projeto PHP classificou como atualização imediata. Não há paliativo que substitua o patch.

**Independent Test**: Depois da troca, o identificador de versão exibido pelo ambiente é 8.4.27 ou um patch 8.4 posterior, e uma página pública abre com o mesmo conteúdo de antes.

**Acceptance Scenarios**:

1. **Given** o ambiente ainda na linha PHP 8.3 da imagem atual, **When** a atualização é aplicada e o serviço volta, **Then** o identificador de versão é PHP 8.4.27 ou um patch posterior da linha 8.4.
2. **Given** a versão em execução é 8.4.27 ou posterior na linha 8.4, **When** o operador compara com o arquivo de segurança de 2026, **Then** as correções de CVE-2026-109618 e CVE-2026-109619 estão cobertas por essa release.
3. **Given** a versão obtida é anterior a 8.4.27, **When** o operador valida, **Then** a entrega não é aceita e o site não é considerado protegido.

---

### User Story 2 - Visitante continua usando o site depois da troca (Priority: P1)

Estudantes, empresas e a equipe interna abrem o site como antes: home, uma página institucional, uma vaga e uma área que exige login. Imagens, formulários e dados gravados permanecem disponíveis.

**Why this priority**: A correção de segurança não pode tirar o produto do ar nem apagar conteúdo.

**Independent Test**: Com a nova versão no ar, abrir a home, uma página interna pública e uma tela autenticada; conferir que o banco e os arquivos enviados continuam íntegros.

**Acceptance Scenarios**:

1. **Given** o runtime novo está no ar, **When** um visitante abre a home e uma página interna, **Then** as duas respondem com o conteúdo anterior, inclusive imagens.
2. **Given** um usuário com conta válida, **When** entra na área logada, **Then** a sessão autentica e os dados da conta aparecem.
3. **Given** a troca concluída, **When** o operador confere o banco e os arquivos publicados, **Then** nenhum dos dois foi apagado ou recriado vazio.

---

### User Story 3 - Operador local confirma a versão e reconstrói o ambiente (Priority: P2)

Quem desenvolve reconstrói o ambiente local a partir da definição versionada, sobe o site e lê a versão do PHP sem editar nada no painel administrativo.

**Why this priority**: O ambiente de quem desenvolve precisa ser a mesma linha que irá para produção, senão a correção não é reproduzível.

**Independent Test**: Reconstruir somente o serviço da aplicação, abri-lo e ler o identificador de versão.

**Acceptance Scenarios**:

1. **Given** a definição versionada do ambiente local, **When** o operador reconstrói e sobe o serviço da aplicação, **Then** o identificador mostra PHP 8.4.27 ou patch 8.4 posterior.
2. **Given** o serviço no ar, **When** o operador limpa os caches do site pelo procedimento já usado no projeto, **Then** a home volta a responder.
3. **Given** o manifesto de dependências atual, sem trava de versão de PHP, **When** a entrega é revisada, **Then** essa trava continua ausente, para a versão conferida ser a do ambiente real.

---

### User Story 4 - Operador aplica a mesma linha na VPS com janela curta (Priority: P2)

Quem publica em produção atualiza o PHP do servidor já documentado do projeto (aplicação no diretório web da VPS, servidor web Apache), com backup do banco antes, interrupção curta e checagem da versão ao final. O banco não é recriado.

**Why this priority**: A correção só protege os visitantes quando a produção também sai da linha vulnerável. O procedimento precisa ser repetível e reversível.

**Independent Test**: Seguir o procedimento de produção em uma janela combinada e, ao final, ler a versão e abrir a home em HTTPS.

**Acceptance Scenarios**:

1. **Given** um backup do banco concluído e conferido, **When** o operador ativa o PHP 8.4.27 no servidor web de produção, **Then** o site volta a responder em HTTPS em menos de 2 minutos de indisponibilidade observável.
2. **Given** a troca feita, **When** o operador lê a versão no diretório da aplicação e reconstrói os caches, **Then** a versão é 8.4.27 ou patch 8.4 posterior e a home abre.
3. **Given** a versão disponível no servidor é anterior a 8.4.27, **When** o operador chega na validação, **Then** interrompe a virada e mantém o site na versão anterior até existir pacote que realmente entregue 8.4.27.
4. **Given** a home ou o login falham depois da virada, **When** o operador executa o retorno documentado, **Then** o site volta à versão de PHP anterior sem perda de banco nem de arquivos.

---

### Edge Cases

- O pacote ou a imagem obtidos ainda identificam PHP anterior a 8.4.27 (a release saiu em 21/10/2026; um espelho desatualizado não serve). A entrega falha até o identificador bater.
- Extensões já necessárias (imagens e PostgreSQL) ou o certificado local deixam de carregar. O site não é aceito até essas capacidades voltarem.
- A produção não usa o mesmo empacotamento do ambiente local. O procedimento de produção segue o servidor Apache já documentado e não recria o banco.
- Alguém tenta cumprir o aviso só ajustando limites de memória, tamanho de envio ou progresso de upload. Isso não encerra a entrega: o aviso oficial diz que a atualização é a única correção completa.
- A linha 8.3.36 também corrige os dois CVE, mas fica fora do escopo porque o pedido fixa a linha 8.4.
- PHP 8.2 recebe o patch 8.2.35 e perde suporte de segurança em 31/12/2026. Não é destino desta entrega.
- Drupal 11.4 aceita PHP 8.3, 8.4 e 8.5. A 8.5.12 corrige os mesmos CVE e não será adotada nesta entrega.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O ambiente que serve o site MUST executar PHP 8.4.27 ou um patch posterior da linha 8.4 publicado pelo projeto PHP.
- **FR-002**: A versão em execução MUST incluir as correções de CVE-2026-109618 e CVE-2026-109619 anunciadas em 21/10/2026. Versão anterior a 8.4.27 MUST ser tratada como entrega incompleta.
- **FR-003**: A linha escolhida MUST permanecer compatível com Drupal 11 na versão já usada pelo produto (11.4 aceita PHP 8.4).
- **FR-004**: Visitantes MUST continuar abrindo a home, uma página interna e uma área autenticada, com o conteúdo anterior à troca.
- **FR-005**: A troca MUST preservar o banco e os arquivos publicados. MUST haver backup do banco conferido antes da virada em produção.
- **FR-006**: Capacidades já usadas pelo produto (tratamento de imagem, acesso ao PostgreSQL, HTTPS local e endereços limpos) MUST continuar disponíveis.
- **FR-007**: O manifesto de dependências MUST NOT ganhar uma trava de plataforma PHP. A ausência atual permanece, para a versão verificada ser a do ambiente real.
- **FR-008**: O operador local MUST conseguir reconstruir o ambiente, ler a versão e refrescar os caches do site sem alteração manual no painel.
- **FR-009**: O operador de produção MUST seguir um procedimento escrito que atualize o PHP do servidor web já documentado, recarregue esse servidor e só então considere a produção atualizada.
- **FR-010**: A indisponibilidade observável da produção durante a virada MUST permanecer abaixo de 2 minutos quando o pacote 8.4.27 já estiver obtido.
- **FR-011**: Se a validação da versão ou a home falhar, o operador MUST conseguir retornar à versão anterior de PHP sem restaurar o banco, salvo corrupção comprovada.
- **FR-012**: Paliativos de configuração citados no aviso de 21/10/2026 MUST NOT substituir a presença do PHP 8.4.27.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Em local e em produção, o identificador de versão lido pelo operador é PHP 8.4.27 ou um patch 8.4 posterior que já contenha as correções de 21/10/2026.
- **SC-002**: Depois do retorno do serviço, a home, uma página interna e uma tela autenticada respondem com o conteúdo pré-existente em até 2 minutos.
- **SC-003**: A janela em que a produção deixa de responder fica abaixo de 2 minutos, com o banco e os arquivos publicados intactos (zero perda de conteúdo).
- **SC-004**: Um operador conclui a checagem de versão e a abertura da home seguindo só o procedimento escrito, sem editar conteúdo no painel.
- **SC-005**: Em uma tentativa controlada com pacote anterior a 8.4.27, a validação rejeita a virada em 100% dos casos e o site permanece na versão anterior.

## Assumptions

- A release alvo é PHP 8.4.27, publicada em 21/10/2026 no arquivo https://www.php.net/archive/2026.php. É o primeiro patch da linha 8.4 que corrige CVE-2026-109618 (execução remota por corrupção em falta de memória) e CVE-2026-109619 (execução remota por estado global obsoleto). Os mesmos avisos foram corrigidos em 8.5.12, 8.3.36 e 8.2.35; a 8.4.27 é a escolha porque o pedido fixa a linha 8.4 e o Drupal 11.4 a suporta.
- O ambiente local parte da imagem oficial `drupal:11-php8.3-apache`. A troca permanece nessa família oficial, na variante de PHP 8.4 com o mesmo servidor web (tag de linha `11-php8.4-apache`), porque o código do projeto é montado por cima da imagem e o que precisa mudar é o interpretador. A variante Alpine oficial dessa família usa outro modelo de processo e trocaria o servidor web; fica fora desta entrega. Na validação, a tag flutuante só é aceita se o identificador interno for 8.4.27 ou posterior — a imagem oficial `php:8.4-apache` já aponta para 8.4.27 em 22/10/2026.
- Produção, pelo runbook de deploy e pela regra de VPS, executa o PHP do sistema sob Apache, com a aplicação no diretório web da VPS. O procedimento de produção atualiza esse PHP e recarrega o servidor web. Não recria o banco.
- O manifesto de dependências não declara uma trava de versão de PHP. Nada será acrescentado.
- Os limites atuais do interpretador local permanecem. Não serão afrouxados para contornar os avisos.
- Nenhuma skill de terceiros do pacote `mattpocock/skills` está instalada no projeto. Higiene de construção (menos camadas, sem pacotes sobrando, sem privilégio novo) fica para o planejamento e não muda o que o visitante vê.
- Não há `hooks.before_specify` nem constituição em `.specify/`; a numeração sequencial segue a última pasta existente (`029`), logo esta feature é `030`.
