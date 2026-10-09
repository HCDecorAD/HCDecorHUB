@echo off
setlocal
set "ROOT=D:\HCDecorHUB"
set "HUB=%ROOT%\repos\HCDecorHUB"
set "VB=%ROOT%\HC_Visual_Builder"
set "OUT=%ROOT%\public-audit-%RANDOM%"
mkdir "%OUT%" 2>nul
if not exist "%HUB%\package.json" exit /b 2
if not exist "%VB%" exit /b 3
start "W1" /b cmd /c "cd /d %VB% & findstr /S /N /I /C:localStorage /C:publish *.ts *.tsx > %OUT%\W1.txt 2>&1 & echo done > %OUT%\W1.done"
start "W2" /b cmd /c "cd /d %VB% & dir /s /b *api* *config* > %OUT%\W2.txt 2>&1 & echo done > %OUT%\W2.done"
start "W3" /b cmd /c "cd /d %HUB% & npm run build > %OUT%\W3.txt 2>&1 & echo done > %OUT%\W3.done"
start "W4" /b cmd /c "cd /d %HUB% & git status --short > %OUT%\W4.txt 2>&1 & git rev-parse HEAD >> %OUT%\W4.txt 2>&1 & echo done > %OUT%\W4.done"
:WAIT
if not exist "%OUT%\W1.done" (timeout /t 2 /nobreak >nul & goto WAIT)
if not exist "%OUT%\W2.done" (timeout /t 2 /nobreak >nul & goto WAIT)
if not exist "%OUT%\W3.done" (timeout /t 2 /nobreak >nul & goto WAIT)
if not exist "%OUT%\W4.done" (timeout /t 2 /nobreak >nul & goto WAIT)
echo AUDIT_COMPLETE_NOT_DEPLOYED>"%OUT%\STATUS.txt"
echo Evidence: %OUT%
endlocal