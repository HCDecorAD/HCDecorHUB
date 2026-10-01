@echo off
setlocal
cd /d D:\HCDecorHUB\repos\HCDecorHUB
powershell.exe -NoProfile -ExecutionPolicy Bypass -File tools\hcdr-relay\repair-hcdr.ps1
if errorlevel 1 (echo ROOT_FIX_PROD_FAILED&exit /b 10)
call tools\hcdr-relay\VERIFY-HCDR.cmd
if errorlevel 1 (echo ROOT_FIX_VERIFY_FAILED&exit /b 11)
echo ROOT_FIX_PROD_PASS
