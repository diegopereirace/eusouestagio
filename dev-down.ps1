#Requires -Version 5.1
$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root

Write-Host "Parando containers..." -ForegroundColor Cyan
docker compose down
Write-Host "Parado. Dados do Postgres permanecem no volume docker." -ForegroundColor Green
