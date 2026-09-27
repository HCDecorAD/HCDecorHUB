@echo off
setlocal
title HCDecor HUB Recovery Sync
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0hcdecor-sync.ps1" %*
if errorlevel 1 pause
