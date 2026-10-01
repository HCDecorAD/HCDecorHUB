@echo off
setlocal
cd /d %~dp0..
python scripts\autochat_stage_matrix.py
exit /b %errorlevel%
