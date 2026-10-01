@echo off
setlocal
set "SRC=%~dp0"
set "DST=D:\HCDecorHUB\HCDecor_Public_Web_v4\ops"
if not exist "%DST%" mkdir "%DST%"
for %%F in (01_RUN_DEMO.bat 02_QA_SITE.bat 03_AUDIT_LINKS.bat 04_CHECKPOINT.bat 06_VERIFY_PREVIEW.bat 07_AUDIT_MEDIA.bat 08_AUDIT_SEO.bat 09_UI_QA.bat 10_THEME_QA.bat 11_RESPONSIVE_THEME_QA.bat) do (
  if exist "%SRC%%%F" copy /y "%SRC%%%F" "%DST%\%%F" >nul
)
if exist "%SRC%UI_THEME_SYSTEM.md" copy /y "%SRC%UI_THEME_SYSTEM.md" "%DST%\UI_THEME_SYSTEM.md" >nul
echo [HCDecor UI] Ops installed to %DST%
exit /b 0
