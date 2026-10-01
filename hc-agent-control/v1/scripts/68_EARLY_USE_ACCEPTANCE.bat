@echo off
setlocal
cd /d %~dp0..
call scripts\09_FRESH_MACHINE_BOOTSTRAP.bat || exit /b 80
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 81
call scripts\07_SELFTEST_NO_BROWSER.bat || exit /b 82
call scripts\59_PACKAGE_EARLY_USE.bat || exit /b 83
call scripts\58_HASH_PACKAGE.bat || exit /b 84
call scripts\80_RESET_SAFE_STATE.bat || exit /b 85
python scripts\write_checkpoint.py EARLY_USE_ACCEPTANCE logs\package-hashes.json || exit /b 86
echo EARLY_USE_ACCEPTANCE_PASS
echo Scope: discovery alias dry-run diagnostics recovery. Real Send remains OFF.
exit /b 0
