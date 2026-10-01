@echo off
setlocal
cd /d %~dp0..
call scripts\09_FRESH_MACHINE_BOOTSTRAP.bat || exit /b 152
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 153
echo.
echo FIRST RUN READY
echo 1. Sign in to ChatGPT in the managed Edge window if needed.
echo 2. Open at least two real conversations.
echo 3. Launch HC Agent Control and bind aliases.
echo 4. Keep STOP ALL enabled until mapping is reviewed.
call HC_AutoChat.bat
