@echo off
setlocal
cd /d %~dp0..
call scripts\95_STATUS_REPORT.bat
call scripts\97_EXPORT_DEBUG_BUNDLE.bat
call scripts\60_BACKUP_RELEASE.bat
echo SNAPSHOT_ALL_PASS
