@echo off
setlocal
set SRC=D:\HCDecorHUB\repos\HCDecorHUB\tools\hcdr-relay\START-SUPERVISOR.bat
set DST=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\HCDecor-HCDR-Supervisor.bat
if not exist "%SRC%" (echo SUPERVISOR_LAUNCHER_MISSING&exit /b 50)
copy /Y "%SRC%" "%DST%" >nul || exit /b 51
call "%SRC%" || exit /b 52
echo HCDR_SUPERVISOR_STARTUP_INSTALLED
