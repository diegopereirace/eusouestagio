@echo off
title Eu Sou Estagio - parar ambiente
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0dev-down.ps1" %*
pause
