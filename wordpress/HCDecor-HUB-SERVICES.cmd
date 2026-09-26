@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor HUB - Services Manager
cd /d "%~dp0"
set "LOG=%CD%\hcdecor-hub-services.log"
set "BASE=https://raw.githubusercontent.com/HCDecorAD/HCDecorHUB/main/wordpress"
set "P=wp-content\plugins\hcdecor-core"
set "V=%RANDOM%%RANDOM%"
echo HCDecor HUB Services %date% %time% >"%LOG%"

where wp >nul 2>&1 || goto :need_shell
call wp core is-installed >>"%LOG%" 2>&1 || goto :fail
echo [1/6] WordPress OK

rem SERVICE A - Source Sync (isolated)
echo [2/6] Sync Core / Agent / Bridge...
if not exist "%P%\assets" mkdir "%P%\assets"
if not exist "%P%\modules" mkdir "%P%\modules"
curl.exe -fL "%BASE%/hcdecor-core/hcdecor-core.php?v=%V%" -o "%P%\hcdecor-core.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/homepage-builder.php?v=%V%" -o "%P%\homepage-builder.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/assets/hcdecor-homepage.css?v=%V%" -o "%P%\assets\hcdecor-homepage.css.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/content-operations.php?v=%V%" -o "%P%\modules\content-operations.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/workflow-engine.php?v=%V%" -o "%P%\modules\workflow-engine.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/ai-providers.php?v=%V%" -o "%P%\modules\ai-providers.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/media-intelligence.php?v=%V%" -o "%P%\modules\media-intelligence.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/agent-intake.php?v=%V%" -o "%P%\modules\agent-intake.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/ai-workspace.php?v=%V%" -o "%P%\modules\ai-workspace.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/web-publisher.php?v=%V%" -o "%P%\modules\web-publisher.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/automation-hub.php?v=%V%" -o "%P%\modules\automation-hub.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/automation-recipes.php?v=%V%" -o "%P%\modules\automation-recipes.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/drive-vault.php?v=%V%" -o "%P%\modules\drive-vault.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/drive-inbox.php?v=%V%" -o "%P%\modules\drive-inbox.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/project-vault.php?v=%V%" -o "%P%\modules\project-vault.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/data-backup.php?v=%V%" -o "%P%\modules\data-backup.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/data-restore.php?v=%V%" -o "%P%\modules\data-restore.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/system-health.php?v=%V%" -o "%P%\modules\system-health.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/background-sync.php?v=%V%" -o "%P%\modules\background-sync.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/project-publishing.php?v=%V%" -o "%P%\modules\project-publishing.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/media-manager.php?v=%V%" -o "%P%\modules\media-manager.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/hub-dashboard.php?v=%V%" -o "%P%\modules\hub-dashboard.php.new" >>"%LOG%" 2>&1 || goto :syncfail
curl.exe -fL "%BASE%/hcdecor-core/modules/admin-cleanup.php?v=%V%" -o "%P%\modules\admin-cleanup.php.new" >>"%LOG%" 2>&1 || goto :syncfail
move /y "%P%\hcdecor-core.php.new" "%P%\hcdecor-core.php" >nul
move /y "%P%\homepage-builder.php.new" "%P%\homepage-builder.php" >nul
move /y "%P%\assets\hcdecor-homepage.css.new" "%P%\assets\hcdecor-homepage.css" >nul
move /y "%P%\modules\content-operations.php.new" "%P%\modules\content-operations.php" >nul
move /y "%P%\modules\workflow-engine.php.new" "%P%\modules\workflow-engine.php" >nul
move /y "%P%\modules\ai-providers.php.new" "%P%\modules\ai-providers.php" >nul
move /y "%P%\modules\media-intelligence.php.new" "%P%\modules\media-intelligence.php" >nul
move /y "%P%\modules\agent-intake.php.new" "%P%\modules\agent-intake.php" >nul
move /y "%P%\modules\ai-workspace.php.new" "%P%\modules\ai-workspace.php" >nul
move /y "%P%\modules\web-publisher.php.new" "%P%\modules\web-publisher.php" >nul
move /y "%P%\modules\automation-hub.php.new" "%P%\modules\automation-hub.php" >nul
move /y "%P%\modules\automation-recipes.php.new" "%P%\modules\automation-recipes.php" >nul
move /y "%P%\modules\drive-vault.php.new" "%P%\modules\drive-vault.php" >nul
move /y "%P%\modules\drive-inbox.php.new" "%P%\modules\drive-inbox.php" >nul
move /y "%P%\modules\project-vault.php.new" "%P%\modules\project-vault.php" >nul
move /y "%P%\modules\data-backup.php.new" "%P%\modules\data-backup.php" >nul
move /y "%P%\modules\data-restore.php.new" "%P%\modules\data-restore.php" >nul
move /y "%P%\modules\system-health.php.new" "%P%\modules\system-health.php" >nul
move /y "%P%\modules\background-sync.php.new" "%P%\modules\background-sync.php" >nul
move /y "%P%\modules\project-publishing.php.new" "%P%\modules\project-publishing.php" >nul
move /y "%P%\modules\media-manager.php.new" "%P%\modules\media-manager.php" >nul
move /y "%P%\modules\hub-dashboard.php.new" "%P%\modules\hub-dashboard.php" >nul
move /y "%P%\modules\admin-cleanup.php.new" "%P%\modules\admin-cleanup.php" >nul
echo [OK] Source Sync

rem SERVICE B - Plugin runtime (isolated)
echo [3/6] Runtime...
call wp plugin is-active hcdecor-core >nul 2>&1
if errorlevel 1 call wp plugin activate hcdecor-core >>"%LOG%" 2>&1
echo [OK] Runtime

rem SERVICE C - Data migrations/seed (non-destructive)
echo [4/6] Data...
call wp option get siteurl >>"%LOG%" 2>&1
if errorlevel 1 (echo [WARN] Data service deferred>>"%LOG%") else echo [OK] Data

rem SERVICE D - Website builder; failure does not stop Agent/Bridge
echo [5/6] Website...
call wp eval-file "%P%\homepage-builder.php" >>"%LOG%" 2>&1
if errorlevel 1 (echo [WARN] Website builder skipped. HUB services continue.) else echo [OK] Website

rem SERVICE E - Bridge/Agent quick health; no outbound
echo [6/6] Agent Bridge / Content / Media / Automation...
call wp option get hcdecor_bridge_token >nul 2>&1
if errorlevel 1 call wp eval "hcdecor_bridge_token(); echo 'BRIDGE_READY';" >>"%LOG%" 2>&1
echo [OK] Agent Bridge
echo [OK] Content Operations
echo [OK] AI Workspace
echo [OK] Media Manager
echo [OK] Automation HUB
echo [OK] Recipe Engine
echo [OK] Drive Vault
echo [OK] Drive Inbox
echo [OK] Project Vault
echo [OK] Data Backups
echo [OK] Restore Center
echo [OK] System Health
echo [OK] HUB V2 Dashboard
echo [SAFE] External publishing remains OFF

echo.
echo ==================================================
echo HCDECOR HUB SERVICES: READY
echo HUB     : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-hub
echo Content : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-content-operations
echo Agent   : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-agent
echo Bridge  : http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-bridge
echo Media   : http://hcdecor-hub.local/wp-admin/upload.php
echo Log     : %LOG%
echo ==================================================
exit /b 0

:syncfail
del /q "%P%\hcdecor-core.php.new" "%P%\homepage-builder.php.new" "%P%\assets\hcdecor-homepage.css.new" "%P%\modules\content-operations.php.new" "%P%\modules\workflow-engine.php.new" "%P%\modules\ai-providers.php.new" "%P%\modules\media-intelligence.php.new" "%P%\modules\agent-intake.php.new" "%P%\modules\ai-workspace.php.new" "%P%\modules\web-publisher.php.new" "%P%\modules\automation-hub.php.new" "%P%\modules\automation-recipes.php.new" "%P%\modules\drive-vault.php.new" "%P%\modules\drive-inbox.php.new" "%P%\modules\project-vault.php.new" "%P%\modules\data-backup.php.new" "%P%\modules\data-restore.php.new" "%P%\modules\system-health.php.new" "%P%\modules\background-sync.php.new" "%P%\modules\project-publishing.php.new" "%P%\modules\media-manager.php.new" "%P%\modules\hub-dashboard.php.new" "%P%\modules\admin-cleanup.php.new" >nul 2>&1
echo [WARN] Source Sync failed. Existing local files kept intact.
goto :continue_after_sync

:continue_after_sync
call wp plugin is-active hcdecor-core >nul 2>&1
echo [SAFE] Existing HUB remains available.
exit /b 1

:need_shell
echo [FAIL] Open LocalWP Site Shell, then run HCDecor-HUB-SERVICES.cmd
exit /b 2
:fail
echo [FAIL] WordPress is not ready. No HUB files were changed.
exit /b 1
