@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
cd /d "%ROOT%" || exit /b 1
echo [1/3] JavaScript syntax
node --check app.js || exit /b 1
echo [2/3] Required files
for %%F in (index.html projects.html services.html contact.html style.css app.js) do if not exist "%%F" (echo MISSING %%F& exit /b 2)
echo [3/3] Duplicate transformation marker
powershell -NoProfile -Command "$s=[IO.File]::ReadAllText('index.html');$n=([regex]::Matches($s,'BEFORE / AFTER')).Count;if($n -gt 1){Write-Error ('Duplicate BEFORE / AFTER: '+$n);exit 3}else{Write-Host ('Before/After count='+$n)}" || exit /b 3
echo QA PASS
