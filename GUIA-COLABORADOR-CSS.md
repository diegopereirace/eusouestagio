# Guia do colaborador — CSS (Eu Sou Estágio)

Ambiente local roda **só com Docker**. Não precisa instalar PHP, Composer nem PostgreSQL na máquina.

Site local: **http://localhost:8082**

---

## Pré-requisitos

1. **Git**
2. **Docker Desktop** instalado e em status *Running*  
   https://www.docker.com/products/docker-desktop/
3. **Node.js ≥ 20** (só para build/watch do CSS do tema)  
   https://nodejs.org/

---

## 1. Clonar e subir o projeto

```bash
git clone <URL_DO_REPOSITORIO> EuSouEstagio
cd EuSouEstagio
```

### Windows (mais simples)

1. Duplo clique em `dev-up.bat`
2. Espere a mensagem **Pronto.**
3. Abra http://localhost:8082

Na primeira vez o script:

- cria `.env` a partir de `.env.example`
- cria `sites/default/settings.php` (não versionado)
- sobe os containers `drupal` + `postgres`
- importa o banco de `_db/dump_vps.sql` se o banco estiver vazio
- limpa o cache Drupal

### Alternativa (PowerShell / terminal)

```powershell
.\dev-up.ps1
```

Parar o ambiente: duplo clique em `dev-down.bat` ou `.\dev-down.ps1`.

Guia curto também em: `COMECE-AQUI-WINDOWS.txt`.

---

## 2. Dump do banco

O arquivo canônico usado no setup é:

```text
_db/dump_vps.sql
```

Se o banco local já tiver dados, o `dev-up` **não** reimporta. Para forçar um banco limpo a partir do dump:

```powershell
docker compose down -v
.\dev-up.ps1
```

> `-v` apaga o volume do Postgres. Só use se quiser começar do zero.

---

## 3. Onde mexer no CSS

Tema custom: `themes/custom/default/`

| Pasta | Uso |
|---|---|
| `src/css/` | **Edite aqui** (fonte) |
| `dist/css/` | Gerado pelo Vite (não edite na mão) |
| `default.libraries.yml` | Declara quais CSS/JS o Drupal carrega |
| `assets/images/` | Imagens referenciadas no CSS |

### Pipeline diário (recomendado)

Em um terminal separado, com o Docker já no ar:

```bash
cd themes/custom/default
npm install
npm run watch
```

- Altere arquivos em `src/css/...`
- O Vite reescreve `dist/css/...` automaticamente
- Atualize o browser (Ctrl+F5 se necessário)

Build de produção (antes do commit, se alterou CSS/JS):

```bash
cd themes/custom/default
npm run build
```

Commit tipicamente inclui **`src/` e `dist/`** (o `dist/` é versionado neste projeto).

---

## 4. Cache Drupal (importante para ver CSS)

Com agregação de CSS ativa, o Drupal pode servir CSS antigo até limpar cache:

```bash
docker compose exec -T drupal vendor/bin/drush cr
```

Dica para trabalhar estilo com mais fluidez (opcional, só local):

```bash
docker compose exec -T drupal vendor/bin/drush cset system.performance css.preprocess 0 -y
docker compose exec -T drupal vendor/bin/drush cset system.performance js.preprocess 0 -y
docker compose exec -T drupal vendor/bin/drush cr
```

Não exporte/commite essa alteração de preprocess `false` — em produção o preprocess deve permanecer ativo.

---

## 5. Fluxo Git sugerido

1. Atualize a branch base (`main`/`master` conforme o time)
2. Crie uma branch só para o ajuste de estilo, ex.: `style/ajuste-home-hero`
3. Edite CSS → `npm run build` (ou deixe o `watch` gerar) → `drush cr`
4. Commit em **português (pt-BR)**, Conventional Commits, ex.:

```text
style: ajusta espaçamento do hero na home
```

5. Abra PR / peça revisão

### Não versionar / não alterar

- `core/`, `vendor/`
- `.env`, `sites/default/settings.php`
- `sites/default/files/` (uploads locais)
- Credenciais, dumps sensíveis novos sem alinhar com o time

---

## 6. Comandos úteis

```bash
# Status dos containers
docker compose ps

# Logs do Drupal
docker compose logs -f drupal

# Shell no container
docker compose exec drupal bash

# Limpar cache
docker compose exec -T drupal vendor/bin/drush cr

# Reconstruir imagem (raro; só se Dockerfile mudar)
.\dev-up.ps1 -Rebuild
```

---

## 7. Problemas comuns

| Sintoma | O que fazer |
|---|---|
| “Docker não está rodando” | Abrir Docker Desktop e esperar *Running* |
| Porta 8082 ocupada | Fechar o outro processo ou mudar `DRUPAL_HTTP_PORT` no `.env` |
| Site sobe mas CSS não muda | Rodar `npm run watch`/`build` + `drush cr` + hard refresh |
| Banco vazio / site quebrado | Confirmar `_db/dump_vps.sql` presente; `docker compose down -v` e `.\dev-up.ps1` |
| CAPTCHA no cadastro local | No local o CAPTCHA fica desligado pelo setup |

---

## Escopo esperado deste colaborador

Foco em **estilo (CSS/SCSS)** no tema `themes/custom/default`.

Evite, sem combinar com o time:

- novos módulos / hooks de deploy
- mudanças em `config/sync`
- alterações estruturais no admin (fields, views, block layout)
- commits em `core/` ou `vendor/`
