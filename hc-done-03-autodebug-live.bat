@echo off
setlocal
cd /d "%~dp0"
npm run test:fix-memory && npm run test:autodebug-implementation && npm run test:autodebug-repair
if errorlevel 1 exit /b 1
echo HC_DONE_D03_PASS autodebug_runtime_logic=1 production_mutation_not_claimed=1
