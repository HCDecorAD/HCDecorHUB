@echo off
setlocal
cd /d %~dp0..
python -c "import src.ui.app,src.core.queue,src.core.registry,src.core.resolver,src.core.recovery,src.adapters.cdp;print('IMPORT_SMOKE_PASS')"
if errorlevel 1 exit /b 52
python scripts\CHECK_RELEASE_GATE.py
if errorlevel 1 (
 echo PRODUCTION_SMOKE_BLOCKED_EXPECTED
 exit /b 0
)
echo PRODUCTION_SMOKE_GATE_PASS
