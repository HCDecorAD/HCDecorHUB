@echo off
setlocal
cd /d %~dp0..
call scripts\47_STATIC_COMPILE_ALL.bat || exit /b 330
call scripts\18_SCRIPT_REFERENCE_AUDIT.bat || exit /b 331
call scripts\48_REGRESSION_GATES.bat || exit /b 332
python -m unittest tests.test_checkpoint_integrity_v1 tests.test_deploy_safety_policy_v1 || exit /b 333
echo RELEASE_INTEGRITY_QA_PASS
exit /b 0
