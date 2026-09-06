<#
.SYNOPSIS
  Faz dump do banco de produção via SSH e importa no PostgreSQL local (Docker).

.DESCRIPTION
  Espelha o banco de produção no PostgreSQL local (Docker). O container e o
  database local podem já existir: o script DROP + CREATE o database e importa
  o dump (substitui o conteúdo; o volume Docker permanece).

  Passo a passo:
    1) Pré-requisitos: chave SSH, OpenSSH client, Docker com
       `eusouestagio-postgres` rodando (`docker compose up -d`).
    2) SSH na VPS e dump via
       `php vendor/drush/drush/drush.php sql:dump --create-db` em /var/www/html
       (--create-db omite DROP TABLE/INDEX; o script já recria o DB local.
        vendor/bin/drush existe mas sem +x na VPS — usar o entrypoint PHP).
       Drush lê credenciais do settings.php.
    3) `scp` do dump para `_db/dump_prod_<timestamp>.sql`.
    4) Remove o dump temporário em /tmp na VPS.
    5) Sem -SkipImport: `docker cp` → DROP/CREATE database → `psql -f`.
    6) Após import: limpar cache Drupal
       `docker exec eusouestagio-drupal php vendor/drush/drush/drush.php cr`

  Encoding: usa `scp` + `docker cp` (não pipe pelo PowerShell) para evitar
  BOM/CRLF do Windows PowerShell 5.1.

.PARAMETER SkipImport
  Só baixa o dump; não recria/importa o banco local.

.PARAMETER SshKey
  Caminho da chave privada SSH. Default: ~/.ssh/id_rsa do usuário atual.

.PARAMETER SshHost
  Host SSH de produção. Default: root@177.153.59.190.

.PARAMETER RemoteDrupalPath
  Diretório raiz do Drupal na VPS. Default: /var/www/html.

.EXAMPLE
  # Banco local já existe: sobe o stack e espelha produção
  docker compose up -d
  ./scripts/dump-prod-and-import-local.ps1
  docker exec eusouestagio-drupal php vendor/drush/drush/drush.php cr

.EXAMPLE
  ./scripts/dump-prod-and-import-local.ps1 -SkipImport
  # Só baixa o dump para _db/.
#>
[CmdletBinding()]
param(
    [switch]$SkipImport,
    [string]$SshKey          = "$env:USERPROFILE\.ssh\id_rsa",
    [string]$SshHost         = 'root@177.153.59.190',
    [string]$RemoteDrupalPath = '/var/www/html',
    [string]$ContainerName    = 'eusouestagio-postgres'
)

$ErrorActionPreference = 'Stop'

function Invoke-Native {
    <#
      Executa um bloco nativo e falha se $LASTEXITCODE != 0.
      Necessário porque $ErrorActionPreference='Stop' não pega exit codes de exes.
    #>
    param(
        [Parameter(Mandatory)][scriptblock]$Command,
        [Parameter(Mandatory)][string]$Description
    )
    & $Command
    if ($LASTEXITCODE -ne 0) {
        throw "Falhou em '$Description' (exit code $LASTEXITCODE)."
    }
}

# --- Paths e nomes ------------------------------------------------------------
$repoRoot   = Split-Path -Parent $PSScriptRoot
$dumpDir    = Join-Path $repoRoot '_db'
$timestamp  = Get-Date -Format 'yyyyMMdd_HHmmss'
$dumpName   = "dump_prod_$timestamp.sql"
$localDump  = Join-Path $dumpDir $dumpName
$remoteDump = "/tmp/$dumpName"

if (-not (Test-Path $dumpDir)) {
    New-Item -ItemType Directory -Path $dumpDir | Out-Null
}

# --- Sanidade -----------------------------------------------------------------
if (-not (Test-Path $SshKey)) {
    throw "Chave SSH não encontrada: $SshKey"
}
foreach ($bin in @('ssh', 'scp')) {
    if (-not (Get-Command $bin -ErrorAction SilentlyContinue)) {
        throw "Binário '$bin' não encontrado no PATH. Instale o OpenSSH client do Windows."
    }
}

Write-Host "==> Testando conexão SSH com $SshHost..." -ForegroundColor Cyan
Invoke-Native -Description 'ssh handshake' {
    ssh -i $SshKey -o BatchMode=yes -o ConnectTimeout=10 $SshHost 'echo ok' | Out-Null
}

# --- Dump remoto --------------------------------------------------------------
Write-Host "==> Gerando dump em $RemoteDrupalPath (drush sql:dump)..." -ForegroundColor Cyan
# --no-owner / --no-privileges: dump portável entre roles locais e de produção.
# --create-db: omite DROP TABLE/INDEX (Postgres). Já fazemos DROP DATABASE localmente.
# Entrypoint PHP: vendor/bin/drush na VPS está sem +x.
$remoteCmd = @(
    "cd '$RemoteDrupalPath'",
    "php vendor/drush/drush/drush.php sql:dump --create-db --result-file='$remoteDump' --extra-dump='--no-owner --no-privileges'"
) -join ' && '

Invoke-Native -Description 'drush sql:dump remoto' {
    ssh -i $SshKey $SshHost $remoteCmd
}

# --- Transferência ------------------------------------------------------------
Write-Host "==> Baixando dump para $localDump..." -ForegroundColor Cyan
Invoke-Native -Description 'scp download' {
    scp -i $SshKey "${SshHost}:${remoteDump}" $localDump
}

# Limpa o dump remoto (best-effort).
ssh -i $SshKey $SshHost "rm -f '$remoteDump'" | Out-Null

if (-not (Test-Path $localDump) -or (Get-Item $localDump).Length -lt 1KB) {
    throw "Dump inválido/vazio: $localDump"
}
$sizeMB = [math]::Round((Get-Item $localDump).Length / 1MB, 2)
Write-Host "    Dump OK: $localDump ($sizeMB MB)" -ForegroundColor Green

if ($SkipImport) {
    Write-Host "==> -SkipImport ativo. Fim." -ForegroundColor Yellow
    return
}

# --- Credenciais locais (via .env do repo) ------------------------------------
$envFile = Join-Path $repoRoot '.env'
if (-not (Test-Path $envFile)) { throw ".env não encontrado em $envFile" }

$envVars = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$') {
        $envVars[$Matches[1]] = $Matches[2].Trim()
    }
}
$db   = $envVars['POSTGRES_DB']
$user = $envVars['POSTGRES_USER']
if (-not $db -or -not $user) {
    throw "POSTGRES_DB/POSTGRES_USER ausentes no .env"
}

# --- Container up? -----------------------------------------------------------
if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw "Docker não encontrado no PATH."
}
$running = docker ps --filter "name=^/${ContainerName}$" --format '{{.Names}}'
if (-not $running) {
    throw "Container '$ContainerName' não está rodando. Suba com: docker compose up -d"
}

# --- Import ------------------------------------------------------------------
Write-Host "==> Copiando dump para dentro do container..." -ForegroundColor Cyan
Invoke-Native -Description 'docker cp' {
    docker cp $localDump "${ContainerName}:/tmp/$dumpName"
}

Write-Host "==> Recriando database '$db' no container..." -ForegroundColor Cyan
# Encerra conexões ativas antes do DROP para evitar "database is being accessed".
docker exec $ContainerName psql -U $user -d postgres -v ON_ERROR_STOP=1 -c `
    "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname='$db' AND pid<>pg_backend_pid();" | Out-Null
Invoke-Native -Description 'DROP DATABASE' {
    docker exec $ContainerName psql -U $user -d postgres -v ON_ERROR_STOP=1 -c "DROP DATABASE IF EXISTS $db;" | Out-Null
}
Invoke-Native -Description 'CREATE DATABASE' {
    docker exec $ContainerName psql -U $user -d postgres -v ON_ERROR_STOP=1 -c "CREATE DATABASE $db OWNER $user;" | Out-Null
}

Write-Host "==> Importando dump em '$db'..." -ForegroundColor Cyan
Invoke-Native -Description 'psql -f import' {
    docker exec $ContainerName psql -U $user -d $db -v ON_ERROR_STOP=1 -f "/tmp/$dumpName"
}

# Cleanup dentro do container
docker exec $ContainerName rm -f "/tmp/$dumpName" | Out-Null

Write-Host ""
Write-Host "==> Sincronização concluída." -ForegroundColor Green
Write-Host "    Sugestão: limpe o cache do Drupal em seguida:" -ForegroundColor DarkGray
Write-Host "    docker exec eusouestagio-drupal php vendor/drush/drush/drush.php cr" -ForegroundColor DarkGray
