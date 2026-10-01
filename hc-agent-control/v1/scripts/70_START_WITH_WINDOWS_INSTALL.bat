@echo off
setlocal
cd /d %~dp0..
set STARTUP=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup
set LINK=%STARTUP%\HC-Agent-Control-V1.cmd
> "%LINK%" echo @echo off
>>"%LINK%" echo cd /d "%CD%"
>>"%LINK%" echo call HC_AutoChat.bat
echo STARTUP_INSTALL_PASS %LINK%
