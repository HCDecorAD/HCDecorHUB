@echo off
setlocal
cd /d %~dp0..
call scripts\A1_A3_AUTOMATED_QA.bat || exit /b 384
call scripts\A4_A5_TRANSACTION_POLICY.bat || exit /b 385
call scripts\59_PACKAGE_EARLY_USE.bat || exit /b 386
call scripts\58_HASH_PACKAGE.bat || exit /b 387
echo AUTOCHAT_A7_PUBLIC_RC_PACKAGE_PASS
python scripts\autochat_public_status.py >nul 2>&1 && echo PUBLIC_EVIDENCE_COMPLETE || echo PACKAGE_PASS_PUBLIC_EVIDENCE_STILL_REQUIRED
exit /b 0
