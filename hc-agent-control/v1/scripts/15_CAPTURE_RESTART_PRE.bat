@echo off
cd /d %~dp0..
python scripts\capture_browser_identity.py cp1-restart-pre
exit /b %errorlevel%
