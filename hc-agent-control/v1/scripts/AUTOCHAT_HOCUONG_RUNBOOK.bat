@echo off
setlocal
cd /d %~dp0..
call scripts\AUTOCHAT_HOCUONG_PREFLIGHT.bat || exit /b 440
call scripts\AUTOCHAT_OPEN_TEST_CHATS.bat || exit /b 441
echo Open at least two real ChatGPT conversations in managed Edge.
echo Then run AUTOCHAT_LIVE_ACCEPTANCE.bat after aliases are bound.
exit /b 0
