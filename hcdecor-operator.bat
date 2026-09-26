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

echo [4/8] PHP syntax checks
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

echo [5/8] Safety guard checks
findstr /c:"'social_enabled'=>false" "wordpress\hcdecor-core\modules\automation-hub.php" >nul || (echo ERROR: social default guard missing.& exit /b 40)
findstr /c:"'hc_outbound',false" "wordpress\hcdecor-core\modules\agent-intake.php" >nul || (echo ERROR: content outbound guard missing.& exit /b 41)
findstr /c:"Reviewer audit is required before publish." "wordpress\hcdecor-core\modules\web-publisher.php" >nul || (echo ERROR: publish review guard missing.& exit /b 42)
echo Safety guards OK.

echo [6/8] Vercel build filter
findstr /c:"ignoreCommand" "vercel.json" >nul || echo WARN: Vercel ignoreCommand not configured.

echo [7/8] Optional Git update
if /I "%~1"=="--pull" (
  git diff --quiet && git diff --cached --quiet
  if errorlevel 1 (
    echo SKIP pull: working tree has changes.
  ) else (
    git pull --ff-only || exit /b 50
  )
) else (
  echo Use: hcdecor-operator.bat --pull  to fast-forward before checks.
)

echo [8/8] Done
echo No social publish, restore, credential change, commit or push was executed.
exit /b 0
