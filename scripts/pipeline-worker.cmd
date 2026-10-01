@echo off
setlocal EnableExtensions
set "NAME=%~1"
set "CMD=%~2"
set "LOGDIR=%~3"
if not exist "%LOGDIR%" mkdir "%LOGDIR%"
cmd /d /c "%CMD%" > "%LOGDIR%\%NAME%.log" 2>&1
set "RC=%ERRORLEVEL%"
> "%LOGDIR%\%NAME%.exit" echo %RC%
exit /b %RC%
