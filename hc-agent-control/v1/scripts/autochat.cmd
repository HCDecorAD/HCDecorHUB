@echo off
setlocal
cd /d "%~dp0.."
python scripts\autochat.py %*
exit /b %ERRORLEVEL%
