@echo off
setlocal
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0accept-mcp-local.ps1"
exit /b %errorlevel%
