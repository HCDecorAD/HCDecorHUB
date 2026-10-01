@echo off
setlocal
cd /d %~dp0..
python -c "import json,pathlib;p=pathlib.Path('config/settings.json');d=json.loads(p.read_text()) if p.exists() else {};d['first_run_done']=False;p.parent.mkdir(exist_ok=True);p.write_text(json.dumps(d,indent=2),encoding='utf-8');print('FIRST_RUN_RESET_PASS')"
exit /b %errorlevel%
