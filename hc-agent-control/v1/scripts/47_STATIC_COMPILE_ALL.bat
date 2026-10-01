@echo off
setlocal
cd /d %~dp0..
python -m compileall -q src scripts tests
if errorlevel 1 exit /b 270
echo STATIC_COMPILE_ALL_PASS
exit /b 0
