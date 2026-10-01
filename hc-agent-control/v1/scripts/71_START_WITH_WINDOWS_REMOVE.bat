@echo off
setlocal
set LINK=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\HC-Agent-Control-V1.cmd
if exist "%LINK%" del /q "%LINK%"
echo STARTUP_REMOVE_PASS
