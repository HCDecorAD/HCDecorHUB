@echo off
setlocal EnableExtensions
set ROOT=D:\HCDecorHUB
set REPO=%ROOT%\repos\HCDecorHUB
set LOG=%ROOT%\runtime\hcdr-root-fix
if not exist "%LOG%" mkdir "%LOG%"
echo ==== HCDR ROOT FIX DIAGNOSE ==== > "%LOG%\diagnose.txt"
where node >>"%LOG%\diagnose.txt" 2>&1
where git >>"%LOG%\diagnose.txt" 2>&1
where gh >>"%LOG%\diagnose.txt" 2>&1
node --version >>"%LOG%\diagnose.txt" 2>&1
git --version >>"%LOG%\diagnose.txt" 2>&1
gh auth status >>"%LOG%\diagnose.txt" 2>&1
if exist "%REPO%\tools\hcdr-relay\relay-agent.mjs" (echo PROD_AGENT=OK>>"%LOG%\diagnose.txt") else (echo PROD_AGENT=MISSING>>"%LOG%\diagnose.txt")
if exist "%REPO%\tools\hcdr-relay\relay-agent-v12.mjs" (echo V12_AGENT=OK>>"%LOG%\diagnose.txt") else (echo V12_AGENT=MISSING>>"%LOG%\diagnose.txt")
if exist "%ROOT%\runtime\hcdr-relay-heartbeat.json" type "%ROOT%\runtime\hcdr-relay-heartbeat.json" >>"%LOG%\diagnose.txt"
tasklist /FI "IMAGENAME eq node.exe" >>"%LOG%\diagnose.txt"
type "%LOG%\diagnose.txt"
