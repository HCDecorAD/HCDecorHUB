@echo off
setlocal EnableExtensions
set "R=D:\HCDecorHUB\repos\HCDecorHUB"
cd /d "%R%" || exit /b 40
node --check tools\hcdr-relay\relay-agent.mjs || exit /b 41
node --check tools\hcdr-relay\skill-loader.mjs || exit /b 42
node tools\imaster\TEST-SKILL-LOADER.mjs
exit /b %ERRORLEVEL%
