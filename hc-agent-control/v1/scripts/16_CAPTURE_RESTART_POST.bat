@echo off
cd /d %~dp0..
python scripts\capture_browser_identity.py cp1-restart-post || exit /b 281
python scripts\compare_restart_evidence.py cp1-restart-pre.json cp1-restart-post.json || exit /b 282
call scripts\14_CAPTURE_ALIAS_RESTART.bat
exit /b %errorlevel%
