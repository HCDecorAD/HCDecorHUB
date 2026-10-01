@echo off
setlocal
cd /d %~dp0..
set EDGE=
if exist "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" set "EDGE=C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
if not defined EDGE if exist "C:\Program Files\Microsoft\Edge\Application\msedge.exe" set "EDGE=C:\Program Files\Microsoft\Edge\Application\msedge.exe"
if not defined EDGE (echo EDGE_NOT_FOUND&exit /b 18)
if not exist runtime\edge-profile mkdir runtime\edge-profile
powershell -NoProfile -Command "try { Invoke-RestMethod http://127.0.0.1:9222/json/version -TimeoutSec 1 | Out-Null; exit 0 } catch { exit 1 }"
if %errorlevel%==0 (echo MANAGED_EDGE_ALREADY_READY port=9222&exit /b 0)
start "HCAC Managed Edge" "%EDGE%" --remote-debugging-port=9222 --remote-allow-origins=http://127.0.0.1:9222 --user-data-dir="%CD%\runtime\edge-profile" about:blank
for /L %%I in (1,1,20) do (
 powershell -NoProfile -Command "try { Invoke-RestMethod http://127.0.0.1:9222/json/version -TimeoutSec 1 | Out-Null; exit 0 } catch { exit 1 }"
 if not errorlevel 1 (echo MANAGED_EDGE_READY port=9222&exit /b 0)
 ping 127.0.0.1 -n 2 >nul
)
echo MANAGED_EDGE_FAILED port=9222
exit /b 19
