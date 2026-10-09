@echo off
setlocal EnableExtensions
set "REPO=D:\HCDecorHUB\repos\HCDecorHUB"
set "SKILL=%REPO%\skills\hcdr-autorecovery\SKILL.md"
set "RULES=%REPO%\AGENTS.md"
set "OUT=D:\HCDecorHUB\evidence\imaster-skill-binding"
if not exist "%OUT%" mkdir "%OUT%"
if not exist "%SKILL%" (echo FAIL_SKILL_MISSING & exit /b 20)
if not exist "%RULES%" (echo FAIL_AGENTS_MISSING & exit /b 21)
powershell -NoProfile -Command "$a=Get-Content '%RULES%' -Raw;$s=Get-Content '%SKILL%' -Raw;if($a -notmatch 'skills/hcdr-autorecovery/SKILL.md' -or $s -notmatch 'CHECK' -or $s -notmatch 'PUBLIC GATE'){exit 22};$o=[ordered]@{repo_rules='PASS';skill_file='PASS';runtime_loader='UNKNOWN';tested_at=(Get-Date).ToUniversalTime().ToString('o')};$o|ConvertTo-Json|Set-Content '%OUT%\binding-check.json' -Encoding UTF8"
if errorlevel 1 (echo FAIL_SKILL_CONTRACT & exit /b 22)
echo PASS_REPO_SKILL_BINDING
echo RUNTIME_LOADER=UNKNOWN
echo EVIDENCE=%OUT%\binding-check.json
exit /b 0
