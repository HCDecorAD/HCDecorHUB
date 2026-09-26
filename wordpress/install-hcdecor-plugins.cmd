@echo off
setlocal EnableExtensions
title HCDecor Plugin Installer
cd /d "%~dp0"
set "LOG=%CD%\hcdecor-plugins.log"

echo ================================================== > "%LOG%"
echo HCDecor Plugin Installer %date% %time% >> "%LOG%"
echo ================================================== >> "%LOG%"

where wp >nul 2>&1
if errorlevel 1 goto :need_shell

call wp core is-installed >>"%LOG%" 2>&1
if errorlevel 1 goto :fail_wp
echo [PASS] WordPress / WP-CLI

call :install advanced-custom-fields "Advanced Custom Fields"
if errorlevel 1 exit /b 1
call :install fluentform "Fluent Forms"
if errorlevel 1 exit /b 1
call :install wp-webhooks "WP Webhooks"
if errorlevel 1 exit /b 1

echo.
echo ================================================
echo HCDECOR PLUGINS: PASS
echo ACF          : ACTIVE
echo Fluent Forms : ACTIVE
echo WP Webhooks  : ACTIVE
echo Log: %LOG%
echo ================================================
exit /b 0

:install
set "SLUG=%~1"
set "NAME=%~2"
echo [AUTO] %NAME%
call wp plugin is-installed %SLUG% >nul 2>&1
if errorlevel 1 (
  call wp plugin install %SLUG% --activate >>"%LOG%" 2>&1
  if errorlevel 1 goto :plugin_fail
) else (
  call wp plugin activate %SLUG% >>"%LOG%" 2>&1
  if errorlevel 1 goto :plugin_fail
)
call wp plugin is-active %SLUG% >nul 2>&1
if errorlevel 1 goto :plugin_fail
echo [PASS] %NAME%
echo [PASS] %NAME% >>"%LOG%"
exit /b 0

:plugin_fail
echo [FAIL] %NAME%
echo [FAIL] %NAME% >>"%LOG%"
exit /b 1

:need_shell
echo [FAIL] WP-CLI not found. Run this file inside LocalWP Site Shell.
echo [FAIL] WP-CLI not found. >>"%LOG%"
exit /b 2

:fail_wp
echo [FAIL] WordPress preflight.
echo [FAIL] WordPress preflight. >>"%LOG%"
exit /b 1
