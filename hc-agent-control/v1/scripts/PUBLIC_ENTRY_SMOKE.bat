@echo off
setlocal
cd /d "%~dp0.."
python scripts\autochat.py new --help >nul || exit /b 1
python -m py_compile src\core\public_entry.py scripts\autochat.py || exit /b 2
echo AUTOCHAT_PUBLIC_ENTRY_SMOKE_PASS
exit /b 0
