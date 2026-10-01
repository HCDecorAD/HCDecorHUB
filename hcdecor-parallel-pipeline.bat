@echo off
setlocal
cd /d %~dp0
if not exist .runtime\pipeline mkdir .runtime\pipeline
call scripts\pipeline-worker.cmd architecture "npm run test:architecture" .runtime\pipeline
if errorlevel 1 exit /b 1
start "data-authority" /b cmd /c call scripts\pipeline-worker.cmd data-authority "node scripts\persistence-contract-check.mjs" .runtime\pipeline
start "identity-rbac" /b cmd /c call scripts\pipeline-worker.cmd identity-rbac "npm run test:architecture" .runtime\pipeline
call scripts\pipeline-worker.cmd commerce "node scripts\commerce-contract-check.mjs" .runtime\pipeline
if errorlevel 1 exit /b 1
call scripts\pipeline-worker.cmd production-smoke "powershell -NoProfile -ExecutionPolicy Bypass -File scripts\production-smoke.ps1" .runtime\pipeline
exit /b %ERRORLEVEL%
