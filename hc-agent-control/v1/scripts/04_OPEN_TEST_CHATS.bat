@echo off
setlocal
cd /d %~dp0..
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 19
echo Open at least two existing ChatGPT conversations in the managed Edge window.
echo Each test tab must have a URL like https://chatgpt.com/c/CONVERSATION_ID
echo No credentials are requested or stored by HC Agent Control.
exit /b 0
