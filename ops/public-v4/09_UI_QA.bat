@echo off
setlocal EnableExtensions
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
set "LOG=%ROOT%\UI_QA.log"
echo ==== HCDecor UI QA %date% %time% ==== > "%LOG%"
if not exist "%ROOT%\index.html" (echo [FAIL] V4 source missing>>"%LOG%" & exit /b 10)
pushd "%ROOT%"
node --check app.js >>"%LOG%" 2>&1 || (popd & exit /b 20)
for %%F in (index.html projects.html services.html materials.html fabrication.html about.html contact.html faq.html) do if not exist "%%F" echo [WARN] Missing %%F>>"%LOG%"
findstr /s /i /n /c:"f78da7" /c:"pink" *.css *.html >>"%LOG%" 2>&1
echo [INFO] Brand: #090A0B #151719 #EEEAE1 #A88B5D>>"%LOG%"
echo [INFO] Typography: EVO UTM + Arial fallback.>>"%LOG%"
echo [INFO] WordPress production theme: Hello Elementor.>>"%LOG%"
echo [PASS] UI source gate complete.>>"%LOG%"
type "%LOG%"
popd
exit /b 0
