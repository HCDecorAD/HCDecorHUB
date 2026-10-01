@echo off
setlocal EnableExtensions
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
pushd "%ROOT%" || exit /b 10
echo ==== HCDecor UI Theme QA ====
findstr /s /i /n "data-hc-theme hcdecor-theme prefers-color-scheme localStorage" index.html style.css app.js
echo [CHECK] Dark tokens: #090A0B #151719 #EEEAE1 #A88B5D
echo [CHECK] Light tokens: #F5F2EC #FFFFFF #151719 #8B7048
echo [CHECK] Selected state: blue; no pink.
echo [CHECK] Mobile toggle target >= 44px.
popd
exit /b 0
