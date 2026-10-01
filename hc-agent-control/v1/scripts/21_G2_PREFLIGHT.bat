@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_arming_v1 tests.test_postverify_v1 tests.test_dispatcher_v1 || exit /b 21
python scripts\CHECK_RELEASE_GATE.py
echo G2_PREFLIGHT_TESTS_DONE
echo Real send remains unavailable unless explicit two-key live arming is separately configured.
exit /b 0
