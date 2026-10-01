@echo off
setlocal
cd /d %~dp0..
python scripts\check_websocket_runtime.py
if errorlevel 1 (
 echo A4_TRANSPORT_DEPENDENCY_MISSING
 echo AutoChat will remain SEND OFF until a local WebSocket transport is available.
 exit /b 360
)
echo A4_TRANSPORT_RUNTIME_AVAILABLE
exit /b 0
