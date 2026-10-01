@echo off
setlocal
cd /d %~dp0..
call scripts\53_PORTABLE_SMOKE.bat || exit /b 107
call scripts\54_CHECK_PYINSTALLER.bat || (echo CP5_BLOCKED_PYINSTALLER_MISSING&exit /b 111)
call scripts\55_BUILD_EXE.bat || exit /b 108
set EXE=dist\HC-Agent-Control-V1\HC-Agent-Control-V1.exe
if not exist "%EXE%" (echo CP5_EXE_MISSING&exit /b 109)
"%EXE%" --selftest > logs\cp5-exe-selftest.json
if errorlevel 1 (type logs\cp5-exe-selftest.json&echo CP5_EXE_SELFTEST_FAIL&exit /b 112)
type logs\cp5-exe-selftest.json
python scripts\write_checkpoint.py CP5_PACKAGE_SMOKE "%EXE%" || exit /b 110
echo CP5_AUTOMATED_ACCEPTANCE_PASS
exit /b 0
