@echo off
setlocal EnableDelayedExpansion
cd /d %~dp0..
netstat -ano | findstr "127.0.0.1:9222" | findstr "LISTENING" >nul && (echo MANAGED_EDGE_READY&exit /b 0)
call scripts\02_START_MANAGED_EDGE.bat || exit /b 19
for /L %%I in (1,1,15) do (
 ping 127.0.0.1 -n 2 >nul
 netstat -ano | findstr "127.0.0.1:9222" | findstr "LISTENING" >nul && (echo MANAGED_EDGE_READY&exit /b 0)
)
echo MANAGED_EDGE_CDP_TIMEOUT
exit /b 22
