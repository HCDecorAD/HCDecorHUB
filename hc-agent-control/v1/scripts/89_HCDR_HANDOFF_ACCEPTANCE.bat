@echo off
setlocal
cd /d %~dp0..
echo HC Agent Control HCDR acceptance handoff
echo Expected workspace: D:\HCDecorHUB\HC_AutoChat or a parent containing this project.
call scripts\87_HOCUONG_RC_ACCEPTANCE.bat || exit /b 260
call scripts\88_HOCUONG_DEMO.bat || exit /b 261
echo HCDR_HANDOFF_ACCEPTANCE_PASS
echo Automated RC passed and demo launched. CP1 live evidence is still a separate gate.
exit /b 0
