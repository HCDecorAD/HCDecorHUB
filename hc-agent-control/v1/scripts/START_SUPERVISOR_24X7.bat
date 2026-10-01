@echo off
cd /d "%~dp0.."
start "HC AutoChat Supervisor 24x7" /min python -m src.core.supervisor_daemon
echo SUPERVISOR_24X7_STARTED
