@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HCDecor HUB Batch Operator

set "ROOT=%~dp0"
cd /d "%ROOT%"

echo [1/8] HCDecor HUB batch checks
where git >nul 2>nul || (echo ERROR: git not found.& exit /b 10)
git rev-parse --is-inside-work-tree >nul 2>nul || (echo ERROR: run inside HCDecorHUB repo.& exit /b 11)

echo [2/8] Repository status
git status --short
for /f %%i in ('git rev-parse --short HEAD') do set "HEAD=%%i"
echo HEAD=!HEAD!

echo [3/8] Manifest validation
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$ErrorActionPreference='Stop'; $m=Get-Content -Raw 'wordpress/hcdecor-sync-manifest.json'|ConvertFrom-Json; if(-not $m.version -or -not $m.files){throw 'Invalid sync manifest'}; Write-Host ('Manifest '+$m.version+' / '+$m.files.Count+' files')" || exit /b 20

echo [4/9] Manifest file integrity
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$ErrorActionPreference='Stop'; $m=Get-Content -Raw 'wordpress/hcdecor-sync-manifest.json'|ConvertFrom-Json; foreach($f in $m.files){$p=Join-Path 'wordpress\hcdecor-core' $f.path; if(-not(Test-Path -LiteralPath $p)){throw ('Missing manifest file: '+$f.path)}; $actual=(& git hash-object -- $p).Trim(); if($actual -ne $f.git_sha1){throw ('Manifest SHA mismatch: '+$f.path+' expected '+$f.git_sha1+' actual '+$actual)}}; Write-Host ('Manifest integrity OK / '+$m.files.Count+' files')" || exit /b 25

echo [5/9] PHP syntax checks
where php >nul 2>nul
if errorlevel 1 (
  echo SKIP: php CLI not installed.
) else (
  set "PHPFAIL=0"
  for /r "wordpress\hcdecor-core" %%F in (*.php) do (
    php -l "%%F" >nul
    if errorlevel 1 (
      echo FAIL: %%F
      set "PHPFAIL=1"
    )
  )
  if "!PHPFAIL!"=="1" exit /b 30
  echo PHP syntax OK.
)

echo [6/9] Safety guard checks
findstr /c:"'social_enabled'=>false" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || (echo ERROR: social default guard missing.& exit /b 40)
findstr /c:"'hc_outbound',false" "wordpress\hcdecor-core\modules\agent-intake.php" >nul || (echo ERROR: content outbound guard missing.& exit /b 41)
findstr /c:"Reviewer audit is required before publish." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || (echo ERROR: publish review guard missing.& exit /b 42)
echo Safety guards OK.

echo [7/9] Vercel build filter
findstr /c:"ignoreCommand" "vercel.json" >nul || echo WARN: Vercel ignoreCommand not configured.

echo [8/9] Optional Git update
if /I "%~1"=="--pull" (
  git status --porcelain | findstr . >nul
  if not errorlevel 1 (
    echo SKIP pull: working tree has changes.
  ) else (
    git pull --ff-only || exit /b 50
  )
) else (
  echo Use: hcdecor-operator.bat --pull  to fast-forward before checks.
)

echo [9/9] Done
echo No social publish, restore, credential change, commit or push was executed.
exit /b 0
