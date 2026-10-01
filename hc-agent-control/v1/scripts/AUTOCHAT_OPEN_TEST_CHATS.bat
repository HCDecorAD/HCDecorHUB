@echo off
setlocal
cd /d %~dp0..
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 342
call scripts\04_OPEN_TEST_CHATS.bat
echo Open at least two real ChatGPT conversations in managed Edge, then run AUTOCHAT_FASTTRACK.bat.
exit /b %errorlevel%
