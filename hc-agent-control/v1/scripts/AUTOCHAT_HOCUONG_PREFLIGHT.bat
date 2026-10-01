@echo off
setlocal
cd /d %~dp0..
echo ===== AUTOCHAT HOCUONG PREFLIGHT =====
where python || exit /b 430
where git || exit /b 431
python --version
git --version
call scripts\03_ENSURE_MANAGED_EDGE.bat || exit /b 432
call scripts\A4_CHECK_TRANSPORT.bat || exit /b 433
python scripts\autochat_public_status.py
echo NOTE: PUBLIC status may intentionally return blocked before live evidence.
exit /b 0
