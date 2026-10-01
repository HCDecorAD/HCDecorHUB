@echo off
setlocal
cd /d %~dp0..
forfiles /p logs /m *.log /d -14 /c "cmd /c del /q @path" 2>nul
forfiles /p logs /m *.exit /d -2 /c "cmd /c del /q @path" 2>nul
echo LOG_CLEANUP_PASS
