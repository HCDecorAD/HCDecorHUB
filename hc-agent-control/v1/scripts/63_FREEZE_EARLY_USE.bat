@echo off
setlocal
cd /d %~dp0..
call scripts\07_SELFTEST_NO_BROWSER.bat || exit /b 63
call scripts\62_BACKUP_UPGRADE_BASELINE.bat || exit /b 64
python scripts\write_checkpoint.py EARLY_USE_FREEZE manifest.json || exit /b 65
echo EARLY_USE_FREEZE_PASS
exit /b 0
