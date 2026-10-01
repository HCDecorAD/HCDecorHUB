@echo off
setlocal
cd /d %~dp0..
where python >nul 2>nul || (echo PYTHON_NOT_FOUND&exit /b 10)
python --version || exit /b 11
python -c "import tkinter;print('TKINTER_PASS')" || exit /b 12
if not exist data mkdir data
if not exist logs mkdir logs
if not exist checkpoints mkdir checkpoints
if not exist dist mkdir dist
echo PRECHECK_PASS
exit /b 0
