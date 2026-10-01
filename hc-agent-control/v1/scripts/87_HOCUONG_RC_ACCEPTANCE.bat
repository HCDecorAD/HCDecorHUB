@echo off
setlocal
cd /d %~dp0..
echo ===== HC AGENT CONTROL HOCUONG RC ACCEPTANCE =====
call scripts\62_BACKUP_UPGRADE_BASELINE.bat || exit /b 240
call scripts\08_MIGRATE_RUNTIME.bat || exit /b 241
call scripts\32_UI_RUNTIME_PROBE.bat || exit /b 242
call scripts\45_DAILY_STABILITY_SUITE.bat || exit /b 243
call scripts\54_PACKAGE_SAFETY_PROBE.bat || exit /b 244
call scripts\83_HEALTH_SUMMARY.bat || exit /b 245
call scripts\86_ACCEPTANCE_MANIFEST.bat || exit /b 246
call scripts\95_STATUS_REPORT.bat || exit /b 247
echo HOCUONG_RC_AUTOMATED_ACCEPTANCE_PASS
echo NEXT: visual demo + real multi-chat CP1 evidence. Real Send remains OFF.
exit /b 0
