@echo off
title Eu Sou Estagio - subir ambiente
cd /d "%~dp0"
echo.
echo  Eu Sou Estagio - ambiente local (Windows)
echo  Guia completo: COMECE-AQUI-WINDOWS.txt
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0dev-up.ps1" %*
if errorlevel 1 (
  echo.
  echo  Falhou. Leia COMECE-AQUI-WINDOWS.txt ou avise o time.
  pause
  exit /b 1
)
echo.
pause
