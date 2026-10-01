@echo off
setlocal
if "%~1"=="" (echo Usage: 06_VERIFY_PREVIEW.bat https://preview-url& exit /b 1)
set "URL=%~1"
where agent-browser >nul 2>nul || (echo agent-browser not found& exit /b 2)
for %%P in (/ /projects /services /contact /faq) do (
  echo VERIFY %URL%%%P
  agent-browser open "%URL%%%P" || exit /b 3
  agent-browser wait --load networkidle || exit /b 3
  agent-browser get title || exit /b 3
)
echo PREVIEW VERIFY PASS
