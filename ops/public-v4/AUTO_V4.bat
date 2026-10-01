@echo off
setlocal
set "OPS=%~dp0"
set "LOG=%OPS%AUTO_V4.log"
echo ==== HCDecor WordPress Migration Prep %%date%% %%time%% ====>>"%LOG%"
call "%OPS%02_QA_SITE.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%03_AUDIT_LINKS.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%07_AUDIT_MEDIA.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%08_AUDIT_SEO.bat" >>"%LOG%" 2>&1 || goto :fail
call "%OPS%04_CHECKPOINT.bat" >>"%LOG%" 2>&1 || goto :fail
echo STATIC V4 READY AS WORDPRESS DESIGN SOURCE>>"%LOG%"
echo NOTE: Vercel deployment intentionally removed.>>"%LOG%"
type "%LOG%"
exit /b 0
:fail
echo PIPELINE STOPPED - gate failed. WordPress production unchanged.>>"%LOG%"
type "%LOG%"
exit /b 1
