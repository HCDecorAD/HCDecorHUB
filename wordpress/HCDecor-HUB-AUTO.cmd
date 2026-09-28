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

if not "%HCDECOR_APPROVE_SITE_CONFIG_WRITE%"=="1" (
  echo [BLOCKED] MASTER AUTO changes plugins, theme and rewrite configuration.
  echo Set HCDECOR_APPROVE_SITE_CONFIG_WRITE=1 only for an explicitly approved local/bootstrap run.
  exit /b 3
)

for %%X in (elementor advanced-custom-fields fluentform wp-webhooks hcdecor-core) do (
 call wp plugin is-installed %%X >nul 2>&1
 if errorlevel 1 call wp plugin install %%X --activate >>"%LOG%" 2>&1
 call wp plugin activate %%X >>"%LOG%" 2>&1
)
call wp theme is-installed hello-elementor >nul 2>&1
if errorlevel 1 call wp theme install hello-elementor >>"%LOG%" 2>&1
call wp theme activate hello-elementor >>"%LOG%" 2>&1
echo [AUTO] Platform ready

if /I not "%HCDECOR_APPROVE_CODE_SYNC%"=="1" (
  echo [BLOCKED] Source sync requires HCDECOR_APPROVE_CODE_SYNC=1.
  exit /b 3
)
call "%~dp0HCDecor-HUB-SERVICES.cmd"
if errorlevel 1 goto :fail
echo [AUTO] HUB source synced through manifest-complete service manager

call wp plugin is-active hcdecor-core >>"%LOG%" 2>&1 || goto :fail
echo [AUTO] HCDecor Core active; normal WordPress lifecycle owns migrations/init

if "%HCDECOR_APPROVE_HOMEPAGE_WRITE%"=="1" (
  call wp eval "define('HCDECOR_APPROVE_HOMEPAGE_WRITE',true); require '%P%/homepage-builder.php';" >>"%LOG%" 2>&1 || goto :fail
  echo [AUTO] Website homepage explicitly rebuilt
) else (
  echo [SAFE] Website homepage write skipped
)

call wp rewrite flush >>"%LOG%" 2>&1
echo.
echo ==================================================
echo HCDECOR HUB AUTO: READY
echo.
echo Website : http://hcdecor-hub.local/
echo HUB     : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-hub
echo AI Agent: http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-agent
echo AgentBridge: http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-bridge
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
