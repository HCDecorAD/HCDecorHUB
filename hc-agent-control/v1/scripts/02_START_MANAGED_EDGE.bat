@echo off
setlocal
cd /d %~dp0..
set EDGE=
if exist "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" set EDGE=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe
if not defined EDGE if exist "C:\Program Files\Microsoft\Edge\Application\msedge.exe" set EDGE=C:\Program Files\Microsoft\Edge\Application\msedge.exe
if not defined EDGE (echo EDGE_NOT_FOUND&exit /b 18)
if not exist runtime\edge-profile mkdir runtime\edge-profile
start "HCAC Managed Edge" "%EDGE%" --remote-debugging-port=9222 --user-data-dir="%CD%\runtime\edge-profile" https://chatgpt.com/
echo MANAGED_EDGE_STARTED port=9222
exit /b 0
