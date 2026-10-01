@echo off
setlocal
set "SRC=%~dp0"
set "DST=D:\HCDecorHUB\HCDecor_Public_Web_v4\ops"
if not exist "%DST%" mkdir "%DST%"
copy /y "%SRC%*.bat" "%DST%\" >nul || exit /b 1
echo Installed HCDecor Public V4 ops to %DST%
