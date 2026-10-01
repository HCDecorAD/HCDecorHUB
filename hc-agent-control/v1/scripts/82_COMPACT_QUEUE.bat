@echo off
setlocal
cd /d %~dp0..
python -c "from src.core.queue import CommandQueue;from src.core.queue_maintenance import compact;import pathlib,datetime;root=pathlib.Path('.');q=CommandQueue(root/'data'/'queue.json');stamp=datetime.datetime.now().strftime('%%Y%%m');print('QUEUE_COMPACT_PASS',compact(q,root/'logs'/('queue-archive-'+stamp+'.json'),500))"
exit /b %errorlevel%
