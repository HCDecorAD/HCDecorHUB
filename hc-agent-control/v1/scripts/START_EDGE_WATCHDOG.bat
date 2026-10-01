@echo off
cd /d "%~dp0.."
start "HC AutoChat Edge Watchdog" /min python -m src.core.edge_watchdog
echo EDGE_WATCHDOG_STARTED
