@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
set "OUT=D:\HCDecorHUB\checkpoints"
if not exist "%OUT%" mkdir "%OUT%"
for /f %%T in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set TS=%%T
powershell -NoProfile -Command "Compress-Archive -Path '%ROOT%\*' -DestinationPath '%OUT%\HCDecor_Public_Web_v4_%TS%.zip' -Force" || exit /b 1
echo Checkpoint: %OUT%\HCDecor_Public_Web_v4_%TS%.zip
