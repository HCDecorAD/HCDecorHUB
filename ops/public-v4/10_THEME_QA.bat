@echo off
setlocal EnableExtensions
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
pushd "%ROOT%" || exit /b 10
echo ==== HCDecor Theme QA ====
findstr /s /i /n "data-theme theme-toggle prefers-color-scheme localStorage" index.html style.css app.js
echo [TARGET] Dark #090A0B #151719 #EEEAE1 #A88B5D
echo [TARGET] Light #F5F2EC #FFFFFF #151719 #8B7048
echo [TARGET] Selected state uses blue; no pink.
popd
exit /b 0
