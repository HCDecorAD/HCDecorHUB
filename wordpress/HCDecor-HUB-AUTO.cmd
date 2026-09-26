@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor HUB - MASTER AUTO
cd /d "%~dp0"
set "LOG=%CD%\hcdecor-hub-auto.log"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
set "P=wp-content\plugins\hcdecor-core"
set "V=%RANDOM%%RANDOM%"
echo HCDecor HUB MASTER AUTO %date% %time% >"%LOG%"

where wp >nul 2>&1 || goto :need_shell
call wp core is-installed >>"%LOG%" 2>&1 || goto :fail
echo [AUTO] WordPress ready

for %%X in (elementor advanced-custom-fields fluentform wp-webhooks hcdecor-core) do (
 call wp plugin is-installed %%X >nul 2>&1
 if errorlevel 1 call wp plugin install %%X --activate >>"%LOG%" 2>&1
 call wp plugin activate %%X >>"%LOG%" 2>&1
)
call wp theme is-installed hello-elementor >nul 2>&1
if errorlevel 1 call wp theme install hello-elementor >>"%LOG%" 2>&1
call wp theme activate hello-elementor >>"%LOG%" 2>&1
echo [AUTO] Platform ready

if not exist "%P%\assets" mkdir "%P%\assets"
curl.exe -fL "%BASE%/hcdecor-core/hcdecor-core.php?v=%V%" -o "%P%\hcdecor-core.php" >>"%LOG%" 2>&1 || goto :fail
curl.exe -fL "%BASE%/hcdecor-core/homepage-builder.php?v=%V%" -o "%P%\homepage-builder.php" >>"%LOG%" 2>&1 || goto :fail
curl.exe -fL "%BASE%/hcdecor-core/assets/hcdecor-homepage.css?v=%V%" -o "%P%\assets\hcdecor-homepage.css" >>"%LOG%" 2>&1 || goto :fail
echo [AUTO] HUB source synced

call wp plugin deactivate hcdecor-core >>"%LOG%" 2>&1
call wp plugin activate hcdecor-core >>"%LOG%" 2>&1 || goto :fail
call wp eval "do_action('init'); echo 'MIGRATE_OK';" >>"%LOG%" 2>&1
echo [AUTO] Data model + demo seed ready

call wp eval-file "%P%\homepage-builder.php" >>"%LOG%" 2>&1 || goto :fail
echo [AUTO] Website demo built

call wp rewrite flush >>"%LOG%" 2>&1
echo.
echo ==================================================
echo HCDECOR HUB AUTO: READY
echo.
echo Website : http://hcdecor-hub.local/
echo HUB     : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-hub
echo Projects: http://hcdecor-hub.local/wp-admin/edit.php?post_type=hc_project
echo Media   : http://hcdecor-hub.local/wp-admin/upload.php
echo Leads   : http://hcdecor-hub.local/wp-admin/edit.php?post_type=hc_lead
echo Quotes  : http://hcdecor-hub.local/wp-admin/edit.php?post_type=hc_quote
echo.
echo Log: %LOG%
echo ==================================================
exit /b 0

:need_shell
echo [FAIL] Open LocalWP Site Shell and run this same AUTO file.
exit /b 2
:fail
echo [FAIL] MASTER AUTO stopped. Existing website/data were not reset.
echo Log: %LOG%
exit /b 1
