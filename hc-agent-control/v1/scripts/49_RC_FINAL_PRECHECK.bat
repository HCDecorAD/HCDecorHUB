@echo off
setlocal
cd /d %~dp0..
call scripts\36_UI_REGRESSION_TEST.bat || exit /b 230
call scripts\47_RC_ACCEPTANCE_ALL.bat || exit /b 231
call scripts\79_PROMOTE_NEXT_TO_EARLY_USE_PRECHECK.bat || exit /b 232
echo RC_FINAL_PRECHECK_PASS
echo HOCUONG live acceptance is still required before promotion.
exit /b 0
