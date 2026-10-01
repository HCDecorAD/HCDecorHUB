@echo off
setlocal
cd /d %~dp0..
python -m unittest tests.test_theme_v1 tests.test_ui_policy_v1 tests.test_registry_v1
if errorlevel 1 exit /b 31
python -c "import src.ui.app;print('CP3_UI_IMPORT_PASS')"
if errorlevel 1 exit /b 32
echo CP3_AUTOMATED_UI_PASS
exit /b 0
