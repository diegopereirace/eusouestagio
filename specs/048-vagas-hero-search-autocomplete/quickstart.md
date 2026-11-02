# Quickstart — Validação da feature 048 (Hero `/vagas`: autocomplete + visual)

**Spec**: [spec.md](spec.md) · **Plan**: [plan.md](plan.md) · **Contratos**: [vagas-autocomplete-api](contracts/vagas-autocomplete-api.md), [vagas-hero-ui](contracts/vagas-hero-ui.md)

## Pré-requisitos

- Ambiente local Docker ativo (`eusouestagio-drupal`, `eusouestagio-postgres`); site em `https://localhost:8082`.
- Vagas seed publicadas (≥ 15) com empresas (`field_vaga_empresa`), cidades e cursos.
- Nenhuma alteração de config/seed: **não** há `cex` nem `updb` específico desta feature.

## 1. Aplicar e limpar caches

```powershell
docker compose exec -T drupal vendor/bin/drush cr
docker compose exec -T drupal vendor/bin/drush pm:list --filter=custom_banners --status=enabled
```

Esperado: `cr` sem erro; rota nova registrada:

```powershell
docker compose exec -T drupal vendor/bin/drush route:debug --path=/api/vagas/autocomplete 2>&1
```

## 2. Testes unitários (matcher)

```powershell
docker compose exec -T drupal vendor/bin/phpunit -c core modules/custom/custom_banners/tests/src/Unit
```

Esperado: `SearchTextNormalizerTest` + `VagasSuggestionMatcherTest` verdes.

## 3. Endpoint (Input 1) — curl dentro do container

```powershell
docker compose exec -T drupal sh -c "curl -sk 'https://localhost/api/vagas/autocomplete?q=est'"
docker compose exec -T drupal sh -c "curl -sk 'https://localhost/api/vagas/autocomplete?q=estagio'"   # igual ao anterior (sem acento)
docker compose exec -T drupal sh -c "curl -sk 'https://localhost/api/vagas/autocomplete?q=e'"          # []
docker compose exec -T drupal sh -c "curl -sk 'https://localhost/api/vagas/autocomplete?q=%25%25'"     # []
docker compose exec -T drupal sh -c "curl -skI 'https://localhost/api/vagas/autocomplete?q=est'"       # Cache-Control: public, max-age=300
```

Esperado: JSON `[ {value,label,type} ]`, ≤ 10 itens, `type ∈ {vaga, empresa}`, prefixos primeiro.

## 4. Filtro da listagem (título OU empresa) — SC-003

1. Escolha um nome de empresa retornado como `type=empresa` (ex.: "Atlas Engenharia").
2. Abra `/vagas?title=atlas%20engenharia` → listagem com ≥ 1 vaga dessa empresa.
3. Escolha um título de vaga com acento → `/vagas?title=<titulo sem acento>` → ≥ 1 resultado.
4. **Lote**: para cada item do endpoint com `q=a`, `q=e`, `q=es` (normalizando `value`), `GET /vagas?title=<norm>` deve conter ≥ 1 vaga (script `drush php:script` ou loop curl).
5. Caso de locale: criar/usar título com maiúscula acentuada ("ÁGIL …") e buscar `?title=agil` → deve casar; se não, estender a lista `translate` com maiúsculas (research D3).

## 5. UI no navegador (`/vagas`)

### 5.1 Autocomplete (US1/US2)

- Input 1: digite `es` → dropdown ≤ 10 itens com trecho destacado e badge Vaga/Empresa; escolha uma → input preenchido, dropdown fecha; **Buscar Vagas** → `/vagas?title=…`.
- Input 2: `sao` → `São Paulo - SP - (N)`; escolha → input recebe só a cidade.
- Input 3: `adm` → cursos com vaga; escolha.
- Cidade + curso → URL `/vagas?cidade=<n>&cursos=<n>`; Input 3 **não** é restringido pela cidade.
- `x` (1 caractere) → **nenhuma** requisição (DevTools → Network).
- Termo sem resultado (`zzzz`) → sem dropdown vazio; Enter ainda busca por texto livre.

### 5.2 Debounce / cancelamento (SC-001)

DevTools → Network (filtro `autocomplete`): digitar `administracao` continuamente → **1** requisição por input após a pausa de 300 ms; digitar `adm`, pausar, continuar `inistr…` → a anterior aparece `(canceled)`; repetir o mesmo termo → sem nova requisição (cache em memória).

### 5.3 Matriz de combinações (SC-004)

| title | cidade | cursos | URL esperada |
|:-:|:-:|:-:|---|
| – | – | – | `/vagas` |
| ✔ | – | – | `/vagas?title=…` |
| – | ✔ | – | `/vagas?cidade=…` |
| – | – | ✔ | `/vagas?cursos=…` |
| ✔ | ✔ | – | `/vagas?title=…&cidade=…` |
| ✔ | – | ✔ | `/vagas?title=…&cursos=…` |
| – | ✔ | ✔ | `/vagas?cidade=…&cursos=…` |
| ✔ | ✔ | ✔ | `/vagas?title=…&cidade=…&cursos=…` |

Cada URL carrega com inputs pré-preenchidos (cidade/curso canônicos) **sem** abrir dropdown (FR-019).

### 5.4 Teclado e ARIA (US5, SC-007)

- Tab até Input 1 → digitar → ↓/↑ mudam item ativo (`aria-activedescendant` muda) → Enter seleciona → Tab → … → Enter no botão. Tudo sem mouse.
- Esc fecha sem limpar o texto. Enter com dropdown fechado submete o form.
- Inspecionar: input `role="combobox"`, `aria-expanded`, `aria-controls`; lista `role="listbox"`; itens `role="option"`.
- Foco visível no campo (contorno), sem outline nativo do `<input>`.

### 5.5 Visual (US4, SC-005, SC-006)

Comparar com Figma `196-1160`/`196-1186`/`196-1189`:

| Viewport | Verificar |
|----------|-----------|
| 1280 px | Barra em linha, ≈ 56 px, raio 12 px, sombra leve; divisórias verticais; botão `#58A83C` raio 8 px; pills `#EFF4FF` 30 px, `9999px`; "Ver todas" laranja abaixo das pills |
| 768 px (< 992) | Campos empilhados, divisórias horizontais, botão largura total |
| 375 px | Sem scroll horizontal; alvos ≥ 44 px; dropdown na largura do campo |

- Clicar uma pill → `/vagas?cursos=<norm>`; **Ver todas** → `/vagas` sem query.

### 5.6 Falhas e segurança

- DevTools → Offline (ou bloquear `/api/vagas/autocomplete`) → dropdown fecha sem erro; form continua enviável.
- Digitar `<script>alert(1)</script>`, `%`, `_`, `'` → nada executa, sem 500; texto tratado como literal.
- Termo > 80 caracteres → resposta `[]`/truncada, sem 500.
- Sem JavaScript (desabilitar JS): form GET nativo envia; `/vagas?title=Estágio` redireciona 302 para versão normalizada.

## 6. Regressão (SC-008)

- Home `/`: hero com `.hero-search` e autocomplete jQuery UI funcionam como antes (cidade ↔ curso cruzados).
- `GET /api/cidades/autocomplete?q=sao` e `GET /api/cursos/autocomplete?q=adm` retornam o mesmo contrato (040/041).
- `/vagas` paginação/ordenação/`use_ajax` e filtros `estado`, `escolaridade`, `regime`, `nid` inalterados.
- Console do navegador sem erros JS em `/` e `/vagas`.

## 7. Deploy em destino (SC-009)

Pré-requisito de produção: `settings.php` alinhado a `settings.php.prod` (`config_sync_directory = 'config/sync'`).

```bash
cd /var/www/html
git pull
php vendor/bin/drush.php cim -y     # no-op nesta feature
php vendor/bin/drush.php updb -y    # no-op nesta feature
php vendor/bin/drush.php cim -y
php vendor/bin/drush.php cr         # registra rota nova; limpa Twig/CSS/JS agregados
```

Verificar em destino: `curl -s https://<host>/api/vagas/autocomplete?q=est` retorna JSON e `/vagas` exibe o novo hero. Sem passos manuais no admin.
