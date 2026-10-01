@echo off
setlocal EnableExtensions
set "FONTROOT=D:\Softs\Font\Font Tong hop"
echo ==== HCDecor UTM EVO Font Check ====
if not exist "%FONTROOT%" (
  echo [WARN] Font folder not found: %FONTROOT%
  exit /b 10
)
where fc-list >nul 2>&1 && fc-list | findstr /i "UTM EVO" || echo [INFO] Windows font registry check requires PowerShell.
powershell -NoProfile -Command "Get-ChildItem -Path '%FONTROOT%' -Recurse -File -Include *.ttf,*.otf,*.woff,*.woff2 | Where-Object {$_.Name -match 'UTM.*EVO|EVO.*UTM'} | Select-Object FullName,Name"
echo.
echo [TARGET] Upload verified webfont (.woff2 preferred) to WordPress Media/approved font loader.
echo [TARGET] Display: UTM EVO. UI/body: Arial.
exit /b 0
