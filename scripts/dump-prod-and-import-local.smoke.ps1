<#
  Smoke test do dump-prod-and-import-local.ps1.
  Não faz SSH, não toca no container: só valida parse do .env e sintaxe do script.
  Rode com: pwsh ./scripts/dump-prod-and-import-local.smoke.ps1
#>
$ErrorActionPreference = 'Stop'
$script = Join-Path $PSScriptRoot 'dump-prod-and-import-local.ps1'

# 1) Parse syntax
$errors = $null
[System.Management.Automation.Language.Parser]::ParseFile($script, [ref]$null, [ref]$errors) | Out-Null
if ($errors.Count -gt 0) {
    $errors | ForEach-Object { Write-Error $_ }
    throw "Erros de sintaxe no script."
}
Write-Host "OK: sintaxe do script válida." -ForegroundColor Green

# 2) Parse do .env (mesma regex do script real)
$envFile = Join-Path (Split-Path -Parent $PSScriptRoot) '.env'
$envVars = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$') {
        $envVars[$Matches[1]] = $Matches[2].Trim()
    }
}
foreach ($k in 'POSTGRES_DB','POSTGRES_USER','POSTGRES_PASSWORD') {
    if (-not $envVars[$k]) { throw "Chave $k ausente após parse do .env" }
}
Write-Host "OK: .env parseado (DB=$($envVars.POSTGRES_DB) USER=$($envVars.POSTGRES_USER))." -ForegroundColor Green
