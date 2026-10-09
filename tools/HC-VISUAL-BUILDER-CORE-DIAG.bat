@echo off
setlocal EnableExtensions
set "ROOT=D:\HCDecorHUB"
set "OUT=%ROOT%\evidence\builder-runtime-diagnostic"
if not exist "%OUT%" mkdir "%OUT%"
echo [1/4] HCDR service and Mesh processes
powershell -NoProfile -Command "Get-CimInstance Win32_Service -Filter \"Name='HCDRRemoteMCP'\" | Select-Object Name,State,StartName,PathName | Format-List" > "%OUT%\service.txt" 2>&1
echo [2/4] Visual Builder core files
powershell -NoProfile -Command "$p='D:\HCDecorHUB\HC_Visual_Builder'; 'APP='+ (Test-Path (Join-Path $p 'src\App.tsx')); 'PACKAGE='+ (Test-Path (Join-Path $p 'package.json')); 'RELEASE='+ (Test-Path (Join-Path $p 'RELEASE.json'))" > "%OUT%\builder-files.txt" 2>&1
echo [3/4] Transport Mesh policy evidence (no secrets)
powershell -NoProfile -Command "$p='D:\HCDecorHUB\TransportMesh\hc-local-gateway.mjs'; if(Test-Path $p){ Select-String -Path $p -Pattern 'READ_NOT_ALLOWED|EXEC_TASK_NOT_ALLOWED|selftest|allowlist' | Select-Object -First 12 LineNumber,@{N='Match';E={$_.Matches[0].Value}} | Format-Table -AutoSize }" > "%OUT%\policy-signatures.txt" 2>&1
echo [4/4] Check summary
type "%OUT%\builder-files.txt"
echo EVIDENCE=%OUT%
exit /b 0
