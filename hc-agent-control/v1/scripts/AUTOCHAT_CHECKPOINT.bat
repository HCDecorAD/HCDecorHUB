@echo off
setlocal
cd /d %~dp0..
if not exist logs mkdir logs
(
 echo HC AutoChat V1 checkpoint
 echo Date: %date% %time%
 git rev-parse --abbrev-ref HEAD
 git rev-parse HEAD
 git status --short
) > logs\autochat-checkpoint.txt 2>&1
python scripts\autochat_public_status.py >> logs\autochat-checkpoint.txt 2>&1
echo Checkpoint: logs\autochat-checkpoint.txt
exit /b 0
