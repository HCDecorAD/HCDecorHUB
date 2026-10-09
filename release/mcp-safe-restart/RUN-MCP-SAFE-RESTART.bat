@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0MCP-SAFE-RESTART.ps1"
exit /b %ERRORLEVEL%
