@echo off
setlocal EnableExtensions EnableDelayedExpansion
set "ROOT=%~dp0..\.."
set "OUT=D:\HCDecorHUB\HC_Visual_Builder\evidence"
if not exist "%OUT%" mkdir "%OUT%"
echo AMO PUBLIC READINESS - non-destructive
start "AMO site" /min cmd /c "powershell -NoProfile -Command \"try { $r=Invoke-WebRequest -UseBasicParsing 'https://amonguyen.hcdecorhub.com/' -TimeoutSec 25; if($r.StatusCode -ne 200){exit 1}; Write-Output 'SITE_HTTP_200' } catch { exit 1 }\" > \"%OUT%\amo-site.log\" 2>&1 & echo %%errorlevel%% > \"%OUT%\amo-site.exit\""
start "AMO catalog" /min cmd /c "powershell -NoProfile -Command \"try { $r=Invoke-WebRequest -UseBasicParsing 'https://hc-shop-engine.huycuongonline.workers.dev/api/catalog' -Headers @{'x-store-id'='store_amo'} -TimeoutSec 25; $j=$r.Content|ConvertFrom-Json; if($r.StatusCode -ne 200 -or $null -eq $j.items){exit 1}; Write-Output ('CATALOG_OK count='+$j.items.Count) } catch { exit 1 }\" > \"%OUT%\amo-catalog.log\" 2>&1 & echo %%errorlevel%% > \"%OUT%\amo-catalog.exit\""
start "AMO local gates" /min cmd /c "call \"%ROOT%\tools\hc-visual-builder-parallel-gates-20261009.bat\" \"D:\HCDecorHUB\HC_Visual_Builder\" > \"%OUT%\amo-local.log\" 2>&1 & echo %%errorlevel%% > \"%OUT%\amo-local.exit\""
set /a tries=0
:wait
if exist "%OUT%\amo-site.exit" if exist "%OUT%\amo-catalog.exit" if exist "%OUT%\amo-local.exit" goto report
set /a tries+=1
if !tries! GEQ 120 (echo TIMEOUT - PUBLIC NOT CERTIFIED & exit /b 124)
ping -n 3 127.0.0.1 >nul
goto wait
:report
set "failed=0"
for %%G in (site catalog local) do (
 set "result="
 for /f "tokens=1" %%R in (%OUT%\amo-%%G.exit) do set "result=%%R"
 echo %%G exit=!result!
 if not "!result!"=="0" set "failed=1"
)
if "!failed!"=="1" (echo PUBLIC READINESS FAILED & exit /b 1)
echo TECHNICAL CHECKS PASS. PUBLIC IS NOT CERTIFIED UNTIL BINDING, DEPLOYMENT AND BROWSER SMOKE TEST PASS.
exit /b 0
