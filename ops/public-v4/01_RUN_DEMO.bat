@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
cd /d "%ROOT%" || exit /b 1
echo [HCDecor V4] Local demo: http://127.0.0.1:4173
python -m http.server 4173
