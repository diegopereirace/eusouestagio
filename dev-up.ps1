#Requires -Version 5.1
<#
.SYNOPSIS
  Sobe o ambiente local Eu Sou Estagio (Docker + Postgres + dump).

.USAGE
  .\dev-up.ps1
  .\dev-up.ps1 -SkipImport
  .\dev-up.ps1 -Rebuild
#>
param(
  [switch]$SkipImport,
  [switch]$Rebuild
)

$ErrorActionPreference = 'Continue'
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root

function Write-Step {
  param([string]$Message)
  Write-Host ''
  Write-Host ('==> ' + $Message) -ForegroundColor Cyan
}

function Assert-Command {
  param([string]$Name)
  if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
    throw ('Comando ''' + $Name + ''' nao encontrado. Instale o Docker Desktop e reinicie o terminal.')
  }
}

function Wait-PostgresHealthy {
  $deadline = (Get-Date).AddMinutes(3)
  do {
    $status = docker inspect -f '{{.State.Health.Status}}' eusouestagio-postgres 2>$null
    if ($status -eq 'healthy') { return }
    Start-Sleep -Seconds 3
  } while ((Get-Date) -lt $deadline)
  throw 'Postgres nao ficou healthy a tempo. Verifique: docker compose logs postgres'
}

function Get-TableCount {
  $raw = docker compose exec -T postgres psql -U drupal -d drupal -tAc "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';"
  return [int](($raw | Out-String).Trim())
}

Write-Host ''
Write-Host 'Eu Sou Estagio - ambiente local' -ForegroundColor Green
Write-Host ('Pasta: ' + $Root)

Assert-Command 'docker'

Write-Step 'Verificando Docker...'
docker info 1>$null 2>$null
if ($LASTEXITCODE -ne 0) {
  throw 'Docker nao esta rodando. Abra o Docker Desktop e tente de novo.'
}

Write-Step 'Preparando arquivos locais...'
if (-not (Test-Path (Join-Path $Root '.env'))) {
  Copy-Item (Join-Path $Root '.env.example') (Join-Path $Root '.env')
  Write-Host 'Criado .env a partir de .env.example'
}

$settingsPath = Join-Path $Root 'sites\default\settings.php'
if (-not (Test-Path $settingsPath)) {
  $base = [System.IO.File]::ReadAllText((Join-Path $Root 'sites\default\default.settings.php'))
  $overrides = [System.IO.File]::ReadAllText((Join-Path $Root 'scripts\templates\docker-overrides.php'))
  $utf8NoBom = New-Object System.Text.UTF8Encoding $false
  [System.IO.File]::WriteAllText($settingsPath, ($base + "`r`n" + $overrides), $utf8NoBom)
  Write-Host 'Criado sites/default/settings.php'
}

$filesDir = Join-Path $Root 'sites\default\files'
New-Item -ItemType Directory -Force -Path $filesDir | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $filesDir 'translations') | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $filesDir 'styles') | Out-Null

try { attrib -R $settingsPath 2>$null } catch {}

Write-Step 'Subindo containers (Drupal + PostgreSQL 16)...'
if ($Rebuild) {
  docker compose up -d --build
}
else {
  docker compose up -d
}
if ($LASTEXITCODE -ne 0) {
  throw 'Falha ao subir docker compose.'
}

Write-Step 'Aguardando Postgres...'
Wait-PostgresHealthy

if (-not $SkipImport) {
  Write-Step 'Checando banco de dados...'
  $tableCount = Get-TableCount
  if ($tableCount -lt 5) {
    $dump = Join-Path $Root '_db\dump_vps.sql'
    if (-not (Test-Path $dump)) {
      throw 'Dump nao encontrado em _db/dump_vps.sql'
    }
    Write-Host 'Importando _db/dump_vps.sql (pode levar alguns minutos)...'
    docker cp $dump eusouestagio-postgres:/tmp/dump_vps.sql
    docker compose exec -T postgres psql -U drupal -d drupal -v ON_ERROR_STOP=1 -f /tmp/dump_vps.sql
    if ($LASTEXITCODE -ne 0) {
      throw 'Falha ao importar o dump SQL.'
    }
    docker compose exec -T postgres rm -f /tmp/dump_vps.sql | Out-Null
    Write-Host 'Dump importado.'
  }
  else {
    Write-Host ('Banco ja possui dados (' + $tableCount + ' tabelas). Import ignorado.')
  }
}
else {
  Write-Host 'Import de dump ignorado (-SkipImport).'
}

Write-Step 'Limpando cache Drupal...'
cmd /c "docker compose exec -T drupal bash /var/www/html/vendor/bin/drush --root=/var/www/html cr"
if ($LASTEXITCODE -ne 0) {
  Write-Host 'Aviso: drush cr falhou (site pode ainda estar inicializando). Tente de novo em alguns segundos.' -ForegroundColor Yellow
}

$port = '8082'
$envFile = Join-Path $Root '.env'
if (Test-Path $envFile) {
  $line = Get-Content $envFile | Where-Object { $_ -match '^\s*DRUPAL_HTTPS_PORT\s*=' } | Select-Object -First 1
  if ($line -match '=\s*(.+)$') {
    $port = $Matches[1].Trim()
  }
}

Write-Host ''
Write-Host 'Pronto.' -ForegroundColor Green
Write-Host ('Site: https://localhost:' + $port)
Write-Host 'Certificado: Avancado -> Continuar para localhost'
Write-Host 'CAPTCHA: desligado neste ambiente local'
Write-Host ''
Write-Host 'Parar: duplo clique em dev-down.bat'
Write-Host 'Guia:  COMECE-AQUI-WINDOWS.txt'
Write-Host ''
