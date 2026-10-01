@echo off
setlocal
cd /d %~dp0..
echo ==== HC AGENT CONTROL V1 DIAGNOSTIC ====
python --version
echo --- CDP 9222 ---
netstat -ano | findstr "127.0.0.1:9222"
echo --- FILES ---
for %%F in (manifest.json release-gates.json data\chats.json data\queue.json) do if exist "%%F" (echo PASS %%F) else (echo MISSING %%F)
echo --- IMPORTS ---
python -c "import src.adapters.cdp,src.core.registry,src.core.queue,src.core.resolver,src.core.recovery,src.ui.app;print('IMPORTS_PASS')"
echo ==== END DIAGNOSTIC ====
