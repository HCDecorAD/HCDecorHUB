@echo off
setlocal
set "ROOT=D:\HCDecorHUB\HCDecor_Public_Web_v4"
cd /d "%ROOT%" || exit /b 1
where vercel >nul 2>nul || (echo Vercel CLI not found& exit /b 2)
echo PREVIEW ONLY - no --prod flag will be used.
vercel deploy --yes
