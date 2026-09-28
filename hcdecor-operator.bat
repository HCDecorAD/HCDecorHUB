@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor HUB Production Operator
set "ROOT=%~dp0"
cd /d "%ROOT%"
echo [1/7] Repository
where git >nul 2>nul || (echo ERROR: git not found.& exit /b 10)
git rev-parse --is-inside-work-tree >nul 2>nul || (echo ERROR: run inside HCDecorHUB repo.& exit /b 11)
git status --short
echo [2/7] Manifest
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; $m=Get-Content -Raw 'wordpress/hcdecor-sync-manifest.json'|ConvertFrom-Json; if(-not $m.version -or -not $m.files){throw 'Invalid sync manifest'}; Write-Host ('Manifest '+$m.version+' / '+$m.files.Count+' files')" || exit /b 20
echo [3/7] Integrity
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; $m=Get-Content -Raw 'wordpress/hcdecor-sync-manifest.json'|ConvertFrom-Json; foreach($f in $m.files){$p=Join-Path 'wordpress\hcdecor-core' $f.path; if(-not(Test-Path -LiteralPath $p)){throw ('Missing: '+$f.path)}; $actual=(& git hash-object -- $p).Trim(); if($actual -ne $f.git_sha1){throw ('SHA mismatch: '+$f.path)}}; Write-Host ('Integrity OK / '+$m.files.Count+' files')" || exit /b 25
echo [4/7] PHP syntax
where php >nul 2>nul
if errorlevel 1 (echo SKIP: php CLI not installed.) else (for /r "wordpress\hcdecor-core" %%F in (*.php) do (php -l "%%F" >nul || exit /b 30))
echo [5/7] Production guards
findstr /c:"'social_enabled'=>false" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 40
findstr /c:"'hc_outbound',false" "wordpress\hcdecor-core\modules\agent-intake.php" >nul || exit /b 41
findstr /c:"Reviewer audit is required before publish." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 42
findstr /c:"hc_publish_approved_at" "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 43
findstr /c:"Explicit production rollback approval is required." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 61
findstr /c:"Web rollback requires explicit admin approval in WordPress." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || exit /b 62
findstr /c:"production_approved" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 44
findstr /c:"Production approval required" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 46
findstr /c:"HCDECOR_APPROVE_HOMEPAGE_WRITE" "wordpress\hcdecor-core\homepage-builder.php" >nul || exit /b 47
findstr /c:"HCDECOR_APPROVE_HOMEPAGE_WRITE" "wordpress\hcdecor-core\hcdecor-core.php" >nul || exit /b 48
findstr /c:"'post_status'=>'draft'" "wordpress\hcdecor-core\modules\project-vault.php" >nul || exit /b 49
findstr /c:"approve_public_projects" "wordpress\hcdecor-core\modules\data-restore.php" >nul || exit /b 50
findstr /c:"HCDECOR_APPROVE_CODE_SYNC" "wordpress\hcdecor-core\modules\background-sync.php" >nul || exit /b 56
findstr /c:"live_health" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 51
findstr /c:"hcdecor_conn_public_https" "wordpress\hcdecor-core\modules\connection-center.php" >nul || exit /b 57
findstr /c:"wp_safe_remote_post" "wordpress\hcdecor-core\modules\publish-runtime.php" >nul || exit /b 58
findstr /c:"Webhook URL must be public HTTPS." "wordpress\hcdecor-core\modules\automation-hub.php" >nul || exit /b 59
findstr /c:"Unknown managed Drive folder" "wordpress\hcdecor-core\modules\drive-vault.php" >nul || exit /b 60
findstr /c:"live_health'=>'not_checked'" "wordpress\hcdecor-core\modules\social-connectors.php" >nul || exit /b 52
findstr /c:"HCDECOR_APPROVE_SITE_CONFIG_WRITE" "wordpress\hcdecor-auto.cmd" >nul || exit /b 53
findstr /c:"HCDECOR_APPROVE_SITE_CONFIG_WRITE" "wordpress\setup-hcdecor.cmd" >nul || exit /b 54
findstr /i /r /c:"eval-file.*homepage-builder.php" wordpress\*.cmd wordpress\*.bat wordpress\*.ps1 >nul 2>nul && exit /b 55
echo Guards OK.
echo [6/7] Next.js build
call npm run build || exit /b 45
echo [7/7] Optional Git update
if /I "%~1"=="--pull" (git status --porcelain | findstr . >nul && (echo SKIP pull: working tree has changes.) || git pull --ff-only)
echo READY: production source checks passed.
exit /b 0
