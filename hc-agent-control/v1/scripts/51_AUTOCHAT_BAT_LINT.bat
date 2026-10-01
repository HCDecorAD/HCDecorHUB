@echo off
setlocal
cd /d %~dp0..
python scripts\script_reference_audit.py || exit /b 460
python -m unittest tests.test_exact_adapter_v1 tests.test_dom_snapshot_policy_v1 tests.test_single_arm_policy_v1 tests.test_cdp_input_policy_v1 tests.test_live_transaction_policy_v1 tests.test_checkpoint_integrity_v1 tests.test_live_transaction_policy_v1 || exit /b 461
echo AUTOCHAT_BAT_LINT_PASS
exit /b 0
