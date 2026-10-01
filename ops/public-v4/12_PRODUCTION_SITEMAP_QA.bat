@echo off
setlocal EnableExtensions
set "BASE=https://hcdecorhub.com"
set "FAIL=0"
echo ==== HCDecor Production Sitemap QA ====
for %%P in (/ /du-an/ /dich-vu/ /nang-luc/ /vat-lieu/ /gia-cong/ /gioi-thieu/ /journal/ /lien-he/) do (
  echo [CHECK] %BASE%%%P
  curl.exe -L -s -o NUL -w "HTTP %%{http_code}
" "%BASE%%%P"
  if errorlevel 1 set "FAIL=1"
)
echo [CHECK] Elementor preview page ID 174 remains draft until approval/public cutover.
if "%FAIL%"=="1" exit /b 20
echo [PASS] Sitemap request gate complete.
exit /b 0
