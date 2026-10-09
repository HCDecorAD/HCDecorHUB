@echo off
setlocal EnableExtensions EnableDelayedExpansion
set "REPO=%~dp0..\.."
set "BUILDER=D:\HCDecorHUB\HC_Visual_Builder"
if not exist "%BUILDER%\src\App.tsx" (echo FAIL: Builder missing & exit /b 2)
if not exist "%REPO%\integrations\amo\amo-catalog.ts" (echo FAIL: AMO adapter missing & exit /b 3)
if not exist "%BUILDER%\evidence" mkdir "%BUILDER%\evidence"
echo [1/3] Installing AMO catalog adapter...
pushd "%REPO%"
node integrations\amo\install-adapter.mjs "%BUILDER%"
if errorlevel 1 (popd & echo FAIL: adapter install & exit /b 4)
popd
echo [2/3] Checking AMO API independently...
powershell -NoProfile -Command "$r=Invoke-WebRequest -UseBasicParsing -Uri 'https://hc-shop-engine.huycuongonline.workers.dev/api/catalog' -Headers @{'x-store-id'='store_amo'}; if($r.StatusCode -ne 200){exit 1}; $j=$r.Content|ConvertFrom-Json; if($null -eq $j.items){exit 2}; Write-Host ('AMO API PASS items='+$j.items.Count)"
if errorlevel 1 (echo FAIL: AMO API & exit /b 5)
echo [3/3] Running independent local validation workers...
call "%REPO%\tools\hc-visual-builder-parallel-gates-20261009.bat" "%BUILDER%"
if errorlevel 1 (echo FAIL: check logs in Builder evidence & exit /b 6)
echo ADAPTER AND LOCAL TESTS PASS.
echo IMPORTANT: AMO binding into App.tsx and live website publish still require separate checks.
exit /b 0
