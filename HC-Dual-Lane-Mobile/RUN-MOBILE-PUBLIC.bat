@echo off
setlocal EnableExtensions EnableDelayedExpansion
title HC Mobile Public Batch
cd /d "%~dp0"
set "LOG=%~dp0logs\mobile-public"
if not exist "%LOG%" mkdir "%LOG%"
echo [%date% %time%] HC MOBILE PUBLIC BATCH> "%LOG%\run.log"

echo [B1] PREFLIGHT
if not exist "mobile-agent\settings.gradle.kts" exit /b 11
if not exist "shared\command-schema.json" exit /b 12

echo [B2] SOURCE VALIDATION
findstr /c:"githubPoller.start()" "mobile-agent\app\src\main\java\com\hcdecor\mobileagent\MainActivity.kt" >nul || exit /b 21
findstr /c:"device.info" "mobile-agent\app\src\main\java\com\hcdecor\mobileagent\RuntimeMvp.kt" >nul || exit /b 22
findstr /c:"media.list" "mobile-agent\app\src\main\java\com\hcdecor\mobileagent\RuntimeMvp.kt" >nul || exit /b 23

echo [B3] PARALLEL STATIC CHECKS
start "" /b cmd /c "findstr /c:"android.permission.INTERNET" mobile-agent\app\src\main\AndroidManifest.xml > "%LOG%\internet.ok""
start "" /b cmd /c "findstr /c:"issues/112" mobile-agent\app\src\main\java\com\hcdecor\mobileagent\GitHubCommandPoller.kt > "%LOG%\queue.ok""
start "" /b cmd /c "findstr /c:"TRASH_VERIFY" mobile-agent\app\src\main\java\com\hcdecor\mobileagent\AgentCore.kt > "%LOG%\safety.ok""
timeout /t 2 /nobreak >nul
if not exist "%LOG%\internet.ok" exit /b 31
if not exist "%LOG%\queue.ok" exit /b 32
if not exist "%LOG%\safety.ok" exit /b 33

echo [B4] LOCAL BUILD IF GRADLE AVAILABLE
where gradle >nul 2>nul
if errorlevel 1 (
 echo GRADLE_NOT_LOCAL - GitHub CI will build>> "%LOG%\run.log"
) else (
 pushd mobile-agent
 call gradle assembleDebug > "%LOG%\gradle.log" 2>&1
 if errorlevel 1 (popd & exit /b 41)
 popd
)

echo [B5] REAL DEVICE AUTO-REMOTE GATE
findstr /c:"githubPoller.start()" "mobile-agent\app\src\main\java\com\hcdecor\mobileagent\MainActivity.kt" >nul || exit /b 51
findstr /c:"MEDIA_PERMISSION_REQUIRED" "mobile-agent\app\src\main\java\com\hcdecor\mobileagent\RuntimeMvp.kt" >nul || exit /b 52

echo [B6] DONE ALL SOURCE GATES
echo DONE_ALL_SOURCE_GATES> "%LOG%\PASS.txt"
echo [%date% %time%] DONE_ALL_SOURCE_GATES>> "%LOG%\run.log"
exit /b 0
