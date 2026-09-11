# Quickstart: Nossa Metodologia — validação e deploy

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md) | **Data**: 2026-09-11

Comandos Drupal devem ser executados no container:

```bash
docker compose exec drupal drush <comando>
```

## Pré-requisitos

1. Stack Docker ativa e Drupal instalado.
2. Branch com config, Twig, CSS, seed e PRD da feature.
3. `config_sync_directory` apontando para `config/sync`.
4. Nenhum dump ou criação manual de fields no ambiente de destino.

## 1. Validar configuração local

```bash
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
docker compose exec drupal drush config:status
```

**Esperado**:

- tipo “Nossa Metodologia” disponível;
- campos: título, subtítulo, Etapas (imagens), Passos (paragraph);
- paragraph type “Passo da Metodologia” (ícone + título);
- seed de textos + títulos dos passos + imagens das etapas (assets no módulo) criado uma única vez;
- placement ativo em `content_full`, somente `<front>`;
- `config:status` sem diferenças inesperadas.

Execute `updb` novamente e confirme que o seed informa `no-op` e não duplica o bloco/passos/imagens.

**Assets versionados** (`modules/custom/custom_configs/assets/nossa-metodologia/`):

- `etapas/identidade.png`, `pertencimento.png`, `performance.png` — obrigatórios para o seed das badges
- `passos/*.png` — ícones dos 5 passos (opcionais até o design entregar; seed pula arquivo ausente)

## 2. Exportar na origem

```bash
docker compose exec drupal drush cex -y
git status --short
```

**Esperado**: alterações limitadas a `config/sync`, tema `default`, `custom_configs.install`, `PRD.md` e artefatos da feature.

## 3. Reproduzir em outro ambiente

```bash
git pull
docker compose exec drupal drush cim -y
docker compose exec drupal drush updb -y
docker compose exec drupal drush cr
```

Em banco totalmente limpo, se o primeiro `cim` não importar o placement porque o UUID seed ainda não existe:

```bash
docker compose exec drupal drush cim -y
docker compose exec drupal drush cr
```

**Esperado**: estrutura, conteúdo textual inicial e placement reproduzidos sem dump e sem SQL manual.

## 4. Cenário visitante — desktop

1. Abrir a home em viewport `≥768px`.
2. Confirmar “Nossa Metodologia” imediatamente após “Nossos Diferenciais”.
3. Confirmar título/subtítulo alinhados à direita.
4. Confirmar etapas (imagens) em fila e passos com setas entre itens, sem overflow.
5. Abrir uma rota interna e confirmar que o bloco não aparece.

## 5. Cenário visitante — mobile

1. Abrir a home em viewport `<768px`.
2. Confirmar título/subtítulo centralizados.
3. Confirmar etapas empilhadas e passos em coluna, legíveis e sem scroll horizontal.
4. Verificar setas decorativas (rotacionadas ou ocultas conforme CSS).

## 6. Cenário editor

1. Autenticar com papel `moderador`.
2. Criar ou editar um bloco “Nossa Metodologia”.
3. Preencher título, subtítulo, imagens das etapas e passos (ícone + título) em menos de 8 minutos.
4. Salvar e confirmar a atualização na home após limpar o cache quando necessário.
5. Confirmar que o editor não precisou de permissão administrativa ampla.

## 7. Matriz de fallbacks

1. Remover todas as etapas: passos (se houver) e textos permanecem.
2. Remover todos os passos: etapas e textos permanecem.
3. Remover ícone de um passo: só o título daquele passo aparece.
4. Remover título: não há H2 vazio.
5. Remover subtítulo: não há parágrafo vazio.
6. Manter apenas etapas ou apenas passos: seção renderiza sem erro.

## Critérios finais

- Placement usa `<front>` e weight `-3`.
- Storages de texto e `field_image` (bloco) foram reutilizados.
- Storage novo: `field_metodologia_passos`; paragraph `metodologia_passo_p`.
- Storages `field_image_desktop` / `field_image_mobile` removidos.
- Configuração está sincronizada.
- Nenhum arquivo em `core/` ou `vendor/` mudou.
- O inventário do bloco foi registrado na seção 3.6 do `PRD.md`.
