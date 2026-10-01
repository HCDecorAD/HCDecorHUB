@echo off
setlocal
cd /d %~dp0..
call scripts\51_PACKAGE_STAGING.bat || exit /b 53
set P=dist\HC-Agent-Control-V1-Staging
python -c "import pathlib,sys;sys.path.insert(0,str(pathlib.Path(r'%P%').resolve()));import src.core.queue,src.core.registry,src.ui.app;print('PORTABLE_IMPORT_PASS')"
if errorlevel 1 exit /b 54
echo PORTABLE_STAGING_SMOKE_PASS
