@echo off
setlocal
python -c "import PyInstaller;print('PYINSTALLER_READY',PyInstaller.__version__)" 2>nul
if errorlevel 1 (echo PYINSTALLER_NOT_INSTALLED&exit /b 54)
exit /b 0
