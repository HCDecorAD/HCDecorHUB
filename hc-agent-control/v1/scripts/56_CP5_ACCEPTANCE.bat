@echo off
setlocal
cd /d %~dp0..
call scripts\53_PORTABLE_SMOKE.bat || exit /b 107
call scripts\54_CHECK_PYINSTALLER.bat
if errorlevel 1 (
 echo CP5_PARTIAL_PASS portable-only
 python scripts\write_checkpoint.py CP5_PORTABLE_ONLY dist\HC-Agent-Control-V1-Staging
 exit /b 0
)
call scripts\55_BUILD_EXE.bat || exit /b 108
if not exist dist\HC-Agent-Control-V1\HC-Agent-Control-V1.exe (echo CP5_EXE_MISSING&exit /b 109)
python scripts\write_checkpoint.py CP5_PACKAGE_SMOKE dist\HC-Agent-Control-V1\HC-Agent-Control-V1.exe || exit /b 110
echo CP5_ACCEPTANCE_EVIDENCE_READY
echo Promote CP5 only after launching the EXE successfully on HOCUONG.
exit /b 0
