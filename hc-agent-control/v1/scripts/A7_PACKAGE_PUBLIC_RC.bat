@echo off
setlocal
cd /d %~dp0..
call scripts\A1_A3_AUTOMATED_QA.bat || exit /b 384
call scripts\A4_A5_TRANSACTION_POLICY.bat || exit /b 385
call scripts\59_PACKAGE_EARLY_USE.bat || exit /b 386
call scripts\58_HASH_PACKAGE.bat || exit /b 387
echo AUTOCHAT_A7_PUBLIC_RC_PACKAGE_PASS
echo Exact Send remains OFF until live A4 A5 evidence passes.
exit /b 0
