@echo off
setlocal
cd /d %~dp0..
call scripts\RUN_STAGING_TESTS.bat || exit /b 50
python scripts\CHECK_RELEASE_GATE.py
if errorlevel 1 (
 echo BUILD_STAGING_ONLY
 exit /b 51
)
echo BUILD_GATE_PASS
exit /b 0
