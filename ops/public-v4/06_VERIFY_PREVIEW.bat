@echo off
setlocal EnableExtensions
set "SITE=%~1"
if "%SITE%"=="" set "SITE=https://hcdecorhub.com"
echo [HCDecor UI] Verify WordPress interface: %SITE%
echo [INFO] Production changes remain approval-gated.
for %%P in (/ /projects /services /contact /faq) do (
  echo [CHECK] %SITE%%%P
)
echo [INFO] Manual/browser QA: header, Light/Dark, VN/EN, responsive, forms, project media.
exit /b 0
