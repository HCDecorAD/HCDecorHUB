@echo off
setlocal
cd /d %~dp0..
call scripts\A4_A5_PREFLIGHT.bat || exit /b 372
call scripts\A4_DISARM.bat || exit /b 373
echo A4_A5_TRANSACTION_POLICY_PASS
echo Live send remains disabled until exact-send transport is committed and HOCUONG A1 identity is proven.
exit /b 0
