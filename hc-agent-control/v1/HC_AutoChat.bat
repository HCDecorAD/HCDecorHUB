@echo off
setlocal
cd /d %~dp0
python -m src.ui.app
if errorlevel 1 (
 echo HC_AGENT_CONTROL_UI_FAILED
 pause
)
