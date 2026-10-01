@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor Website Parallel QA
set "ROOT=D:\HCDecorHUB\repos\HCDecorHUB"
set "OUT=%ROOT%\.runtime\web-hcdecor"
if not exist "%OUT%" mkdir "%OUT%"
del /q "%OUT%\*.exit" "%OUT%\*.log" >nul 2>&1
call :START font-audit "powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts\web-font-audit.ps1"
call :START git-health "cmd /d /c ""cd /d %ROOT% && git status --short && git log -1 --oneline"""
call :START prod-smoke "powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts\web-prod-smoke.ps1"
call :START media-audit "powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts\web-media-audit.ps1"
call :WAIT font-audit git-health prod-smoke media-audit
echo ==== HCDECOR WEBSITE PARALLEL SUMMARY ====
for %%N in (font-audit git-health prod-smoke media-audit) do (
 set "RC=?"
 if exist "%OUT%\%%N.exit" set /p RC=<"%OUT%\%%N.exit"
 echo %%N=!RC!
)
exit /b 0
:START
start "%~1" /b cmd /v:on /d /c ""%~2" ^> "%OUT%\%~1.log" 2^>^&1 ^& echo !errorlevel! ^> "%OUT%\%~1.exit""
exit /b 0
:WAIT
for %%N in (%*) do call :WAIT_ONE %%N
exit /b 0
:WAIT_ONE
if not exist "%OUT%\%~1.exit" (timeout /t 1 /nobreak >nul & goto WAIT_ONE)
exit /b 0
