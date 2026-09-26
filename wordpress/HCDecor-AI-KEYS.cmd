@echo off
setlocal EnableExtensions
title HCDecor HUB - Secure AI Keys
cd /d "%~dp0"

where wp >nul 2>&1 || (
  echo [FAIL] Open LocalWP Site Shell first.
  exit /b 2
)
call wp core is-installed >nul 2>&1 || (
  echo [FAIL] WordPress is not ready.
  exit /b 2
)

echo HCDecor HUB - Secure AI Provider Setup
echo Keys are NOT written to GitHub or this BAT file.
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$ErrorActionPreference='Stop';" ^
  "$o=Read-Host 'OpenAI API key' -AsSecureString;" ^
  "$g=Read-Host 'Gemini API key' -AsSecureString;" ^
  "$ob=[Runtime.InteropServices.Marshal]::SecureStringToBSTR($o);" ^
  "$gb=[Runtime.InteropServices.Marshal]::SecureStringToBSTR($g);" ^
  "try {" ^
  "  $env:HCDECOR_TMP_OPENAI=[Runtime.InteropServices.Marshal]::PtrToStringBSTR($ob);" ^
  "  $env:HCDECOR_TMP_GEMINI=[Runtime.InteropServices.Marshal]::PtrToStringBSTR($gb);" ^
  "  cmd /c "call wp eval \"update_option('hcdecor_ai_openai_key', getenv('HCDECOR_TMP_OPENAI'), false); update_option('hcdecor_ai_gemini_key', getenv('HCDECOR_TMP_GEMINI'), false); update_option('hcdecor_ai_primary','auto',false); echo 'AI_KEYS_SAVED';\"";" ^
  "} finally {" ^
  "  [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ob);" ^
  "  [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($gb);" ^
  "  Remove-Item Env:HCDECOR_TMP_OPENAI -ErrorAction SilentlyContinue;" ^
  "  Remove-Item Env:HCDECOR_TMP_GEMINI -ErrorAction SilentlyContinue;" ^
  "}"

if errorlevel 1 (
  echo [FAIL] AI keys were not saved.
  exit /b 1
)

echo.
echo [OK] OpenAI + Gemini keys saved locally.
echo [OK] Primary: Auto fallback
echo AI Providers: http://hcdecor-hub.local/wp-admin/admin.php?page=hcdecor-ai-providers
exit /b 0
