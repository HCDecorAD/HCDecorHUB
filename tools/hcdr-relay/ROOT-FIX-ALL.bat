@echo off
setlocal EnableExtensions
cd /d D:\HCDecorHUB\repos\HCDecorHUB
echo [1/4] Diagnose
call tools\hcdr-relay\ROOT-FIX-DIAGNOSE.bat
echo [2/4] Repair production relay
call tools\hcdr-relay\ROOT-FIX-REPAIR-PROD.bat || exit /b 31
echo [3/4] Validate V1.2 lane
call tools\hcdr-relay\ROOT-FIX-V12-CHECK.bat || exit /b 32
call tools\hcdr-relay\ROOT-FIX-START-V12.bat || exit /b 33
echo [4/4] Production HCDR ready
echo HCDR_ROOT_FIX_DUAL_LANE_PASS
echo Next: use production HCDR to complete V1.2 worker and AutoChat live acceptance.
exit /b 0
