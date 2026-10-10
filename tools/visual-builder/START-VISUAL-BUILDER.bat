@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HC_Visual_Builder"
set "LOG=%ROOT%\visual-builder-server.log"
if not exist "%ROOT%\package.json" (echo ERROR: Visual Builder not found & exit /b 2)
cd /d "%ROOT%"
if not exist "node_modules\vite" (echo ERROR: dependencies missing & exit /b 3)
start "HC Visual Builder Server" /min cmd /c "cd /d %ROOT% && npm run dev -- --host 127.0.0.1 --port 5173 --strictPort > %LOG% 2>&1"
echo SERVER_START_REQUESTED
echo LOG=%LOG%
exit /b 0
