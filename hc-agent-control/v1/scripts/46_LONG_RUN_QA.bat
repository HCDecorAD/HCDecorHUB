@echo off
setlocal
cd /d %~dp0..
call scripts\44_SOAK_TEST.bat || exit /b 230
python -m unittest tests.test_queue_maintenance_v1 tests.test_queue_compaction_policy_v1 tests.test_transitions_v1 tests.test_autorefresh_policy_v1 || exit /b 231
echo LONG_RUN_QA_PASS
exit /b 0
