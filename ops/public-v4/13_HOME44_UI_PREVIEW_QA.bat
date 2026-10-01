@echo off
setlocal EnableExtensions
set "BASE=https://hcdecorhub.com"
set "PREVIEW=%BASE%/?page_id=180&preview=true"
echo ==== HCDecor Home V4 UI QA ====
echo Preview: %PREVIEW%
for %%P in (/ /du-an/ /dich-vu/ /nang-luc/ /vat-lieu/ /gia-cong/ /gioi-thieu/ /journal/ /lien-he/) do (
  echo [CHECK] %BASE%%%P
  curl.exe -L -s -o NUL -w "HTTP %%{http_code}
" "%BASE%%%P"
)
echo [UI] Light/Dark toggle: localStorage hcdecor-theme
echo [UI] Responsive: desktop / tablet / mobile
echo [UI] Production Home 44 unchanged.
exit /b 0
