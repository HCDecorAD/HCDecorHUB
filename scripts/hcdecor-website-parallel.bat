@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor Website Parallel QA
set "ROOT=D:\HCDecorHUB\repos\HCDecorHUB"
set "OUT=%ROOT%\.runtime\web-hcdecor"
if not exist "%OUT%" mkdir "%OUT%"
del /q "%OUT%\*.exit" "%OUT%\*.log" >nul 2>&1
call :START font-audit "powershell -NoProfile -ExecutionPolicy Bypass -Command ""Get-ChildItem 'D:\Softs\Font\Font Tong hop' -Recurse -File | Where-Object {$_.Name -match '(?i)(EVO|UTM)'} | Select FullName,Name,Extension,Length | ConvertTo-Json -Depth 3"""
call :START git-health "cmd /d /c ""cd /d %ROOT% && git status --short && git log -1 --oneline"""
call :START prod-smoke "powershell -NoProfile -ExecutionPolicy Bypass -Command ""$u=@('https://hcdecorhub.com/','https://hcdecorhub.com/dich-vu/','https://hcdecorhub.com/du-an/','https://hcdecorhub.com/gioi-thieu/','https://hcdecorhub.com/lien-he/'); foreach($x in $u){try{$r=Invoke-WebRequest -UseBasicParsing -Uri $x -TimeoutSec 20; Write-Output ($x+' '+[int]$r.StatusCode+' bytes='+$r.RawContentLength)}catch{Write-Error ($x+' '+$_.Exception.Message); exit 1}}"""
call :START media-audit "powershell -NoProfile -ExecutionPolicy Bypass -Command ""$p='D:\HCDecorHUB'; Get-ChildItem $p -Recurse -File -ErrorAction SilentlyContinue | Where-Object {$_.Extension -match '(?i)^\.(jpg|jpeg|png|webp)$' -and $_.FullName -match '(?i)(HCDecor|bang.?hieu|noi.?that|DGemma|Wait|PLAY)'} | Select -First 250 FullName,Name,Length | ConvertTo-Json -Depth 3"""
call :WAIT font-audit git-health prod-smoke media-audit
echo ==== HCDECOR WEBSITE PARALLEL SUMMARY ====
for %%N in (font-audit git-health prod-smoke media-audit) do (
 set "RC=?"
 if exist "%OUT%\%%N.exit" set /p RC=<"%OUT%\%%N.exit"
 echo %%N=!RC!
)
exit /b 0
:START
start "%~1" /b cmd /d /c ""%~2" > "%OUT%\%~1.log" 2>&1 & echo !errorlevel! > "%OUT%\%~1.exit""
exit /b 0
:WAIT
for %%N in (%*) do call :WAIT_ONE %%N
exit /b 0
:WAIT_ONE
if not exist "%OUT%\%~1.exit" (timeout /t 1 /nobreak >nul & goto WAIT_ONE)
exit /b 0
