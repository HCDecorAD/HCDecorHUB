@echo off
setlocal
set "OPS=%~dp0"
call "%OPS%02_QA_SITE.bat" || goto :fail
call "%OPS%04_CHECKPOINT.bat" || goto :fail
call "%OPS%05_DEPLOY_PREVIEW.bat" || goto :fail
echo AUTO V4 COMPLETE
exit /b 0
:fail
echo AUTO V4 STOPPED - a gate failed. Production was not changed.
exit /b 1
