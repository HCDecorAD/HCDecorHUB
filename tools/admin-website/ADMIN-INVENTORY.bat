@echo off
setlocal EnableExtensions
set "OUT=D:\HCDecorHUB\TransportMesh\AdminWebsite"
if not exist "%OUT%" mkdir "%OUT%"
set "LOG=%OUT%\inventory.log"
> "%LOG%" echo HC ADMIN WEBSITE - READ ONLY INVENTORY
>>"%LOG%" echo HOST=%COMPUTERNAME% DATE=%DATE% TIME=%TIME%
for %%D in ("D:\HCDecorHUB" "D:\HCDecorHUB\AdminWebsite" "D:\HCDecorHUB\Admin" "D:\HCDecorHUB\Websites" "D:\HCDecorHUB\Projects" "C:\Users\DELL\Documents\GitHub" "D:\GitHub") do (
 >>"%LOG%" echo === %%~D ===
 if exist "%%~D" (dir /a:d /b "%%~D" >>"%LOG%" 2>&1) else (>>"%LOG%" echo NOT_FOUND)
)
>>"%LOG%" echo === ROOT GIT ===
git -C "D:\HCDecorHUB" rev-parse --show-toplevel >>"%LOG%" 2>&1
>>"%LOG%" echo === COMMON ADMIN CONFIG ===
for %%F in ("D:\HCDecorHUB\AdminWebsite\package.json" "D:\HCDecorHUB\Admin\package.json" "D:\HCDecorHUB\package.json") do (
 if exist "%%~F" (>>"%LOG%" echo FOUND %%~F) else (>>"%LOG%" echo MISSING %%~F)
)
>>"%LOG%" echo INVENTORY_FINISHED
type "%LOG%"
endlocal
