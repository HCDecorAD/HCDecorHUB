@echo off
setlocal
cd /d %~dp0..
call scripts\54_CHECK_PYINSTALLER.bat || (echo EXE_BUILD_SKIPPED install free PyInstaller on HOCUONG when approved&exit /b 55)
python -m PyInstaller --noconfirm --clean --windowed --name HC-Agent-Control-V1 --paths . src\ui\app.py
if errorlevel 1 exit /b 56
echo EXE_BUILD_PASS
