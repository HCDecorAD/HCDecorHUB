@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HC_Visual_Builder"
if not exist "%ROOT%\package.json" (echo ERROR: Visual Builder not found & exit /b 2)
cd /d "%ROOT%"
if not exist "node_modules\vite" (echo ERROR: dependencies missing & exit /b 3)
echo Starting HC Visual Builder at http://localhost:5173/
start "" "http://localhost:5173/"
call npm run dev -- --host 127.0.0.1
exit /b %ERRORLEVEL%
