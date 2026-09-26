@echo off
setlocal EnableExtensions
title HCDecor HUB DEMO
cd /d "%~dp0"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress/hcdecor-core"
set "P=wp-content\plugins\hcdecor-core"
set "V=demo-%RANDOM%%RANDOM%"
where wp >nul 2>&1 || (echo [FAIL] Open LocalWP Site Shell & exit /b 2)
echo [DEMO] Sync visual source...
curl.exe -fL "%BASE%/hcdecor-core.php?v=%V%" -o "%P%\hcdecor-core.php" >nul 2>&1 || goto :fail
curl.exe -fL "%BASE%/homepage-builder.php?v=%V%" -o "%P%\homepage-builder.php" >nul 2>&1 || goto :fail
curl.exe -fL "%BASE%/assets/hcdecor-homepage.css?v=%V%" -o "%P%\assets\hcdecor-homepage.css" >nul 2>&1 || goto :fail
echo [DEMO] Build homepage...
call wp eval-file "%P%\homepage-builder.php" >nul 2>&1 || goto :fail
echo.
echo ================================================
echo HCDECOR HUB DEMO: READY
echo Open: http://hcdecor-hub.local/
echo ================================================
exit /b 0
:fail
echo [FAIL] Demo sync/build. Existing website remains intact.
exit /b 1
