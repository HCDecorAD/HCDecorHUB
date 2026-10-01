@echo off
setlocal
set "OPS=%~dp0"
set "LOG=%OPS%AUTO_V4.log"
echo ==== HCDecor Public V4 %%date%% %%time%% ====>>"%LOG%"
call "%OPS%02_QA_SITE.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%03_AUDIT_LINKS.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%07_AUDIT_MEDIA.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%08_AUDIT_SEO.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%04_CHECKPOINT.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%05_DEPLOY_PREVIEW.bat" >>"%LOG%" 2>&1 || goto :fail
echo AUTO V4 COMPLETE>>"%LOG%"
type "%LOG%"
exit /b 0
:fail
echo AUTO V4 STOPPED - gate failed. Production unchanged.>>"%LOG%"
type "%LOG%"
exit /b 1
