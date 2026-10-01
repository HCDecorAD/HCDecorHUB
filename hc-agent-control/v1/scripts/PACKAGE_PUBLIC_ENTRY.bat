@echo off
setlocal
cd /d "%~dp0.."
call scripts\PUBLIC_ENTRY_SMOKE.bat || exit /b 1
set OUT=dist\HC-AutoChat-Public-Entry-V1
if exist "%OUT%" rmdir /s /q "%OUT%"
mkdir "%OUT%\src\core" || exit /b 2
mkdir "%OUT%\src\adapters" || exit /b 3
mkdir "%OUT%\scripts" || exit /b 4
xcopy src\core "%OUT%\src\core\" /E /I /Y >nul || exit /b 5
xcopy src\adapters "%OUT%\src\adapters\" /E /I /Y >nul || exit /b 6
copy /Y scripts\autochat.py "%OUT%\scripts\" >nul
copy /Y scripts\autochat.cmd "%OUT%\scripts\" >nul
copy /Y scripts\PUBLIC_ENTRY_SMOKE.bat "%OUT%\scripts\" >nul
copy /Y PUBLIC-ENTRY.md "%OUT%\" >nul
copy /Y manifest.json "%OUT%\" >nul
copy /Y requirements.txt "%OUT%\" >nul
> "%OUT%\USE-FROM-HCDR.txt" echo scripts\autochat.cmd new --title "^<title^>" --prompt "^<prompt^>"
call "%OUT%\scripts\PUBLIC_ENTRY_SMOKE.bat" || exit /b 7
echo AUTOCHAT_PUBLIC_ENTRY_PACKAGE_PASS %OUT%
exit /b 0
