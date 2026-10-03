@echo off
setlocal
cd /d "%~dp0.."
if not defined HC_SENTINEL_PORT set HC_SENTINEL_PORT=43110
node runtime\start-sentinel.js
endlocal
