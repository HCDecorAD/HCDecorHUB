@echo off
setlocal EnableExtensions
cd /d "%~dp0"
echo === HCDecor Foundation Authority Gate ===
node scripts\foundation-contract-check.mjs
if errorlevel 1 goto FAIL
node scripts\dr-readiness-check.mjs
if errorlevel 1 goto FAIL
node scripts\persistence-contract-check.mjs
if errorlevel 1 goto FAIL
echo HCDECOR_AUTHORITY_CONTRACT_PASS
echo Durable production provider, identity authority and DR controls must be bound before production mutation unlock.
exit /b 0
:FAIL
echo HCDECOR_AUTHORITY_CONTRACT_FAIL
exit /b 1
