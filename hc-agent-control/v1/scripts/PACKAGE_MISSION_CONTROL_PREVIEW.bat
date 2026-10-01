@echo off
setlocal
cd /d "%~dp0.."
python -m py_compile src\ui\app.py src\core\public_entry.py scripts\autochat.py || exit /b 1
python -m unittest discover -s tests -p "test_*.py" || exit /b 2
set OUT=dist\HC-AutoChat-Mission-Control-Preview
if exist "%OUT%" rmdir /s /q "%OUT%"
mkdir "%OUT%" || exit /b 3
xcopy src "%OUT%\src\" /E /I /Y >nul || exit /b 4
xcopy scripts "%OUT%\scripts\" /E /I /Y >nul || exit /b 5
xcopy docs "%OUT%\docs\" /E /I /Y >nul 2>nul
mkdir "%OUT%\data" 2>nul
mkdir "%OUT%\logs" 2>nul
mkdir "%OUT%\runtime" 2>nul
mkdir "%OUT%\config" 2>nul
> "%OUT%\data\chats.json" echo {"version":1,"chats":{}}
> "%OUT%\data\queue.json" echo {"items":[],"global_paused":true,"paused_aliases":[]}
copy /Y manifest.json "%OUT%\" >nul
copy /Y PUBLIC-ENTRY.md "%OUT%\" >nul
copy /Y requirements.txt "%OUT%\" >nul
> "%OUT%\START-MISSION-CONTROL.bat" echo @echo off
>>"%OUT%\START-MISSION-CONTROL.bat" echo cd /d "%%~dp0"
>>"%OUT%\START-MISSION-CONTROL.bat" echo python -m src.ui.app
> "%OUT%\README-FIRST.txt" echo HC AutoChat Mission Control Preview - standalone test package
>>"%OUT%\README-FIRST.txt" echo Start: START-MISSION-CONTROL.bat
>>"%OUT%\README-FIRST.txt" echo NEW + SEND is a REAL side effect and asks for confirmation.
>>"%OUT%\README-FIRST.txt" echo Public Entry baseline: GREEN. Interactive UI: CANARY for user testing.
>>"%OUT%\README-FIRST.txt" echo Safety: exact CID + composer scoped + post verify + fail closed.
echo MISSION_CONTROL_PACKAGE_PASS %OUT%
