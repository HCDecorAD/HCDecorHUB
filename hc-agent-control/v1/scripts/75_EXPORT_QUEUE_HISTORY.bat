@echo off
setlocal
cd /d %~dp0..
python -c "from src.core.queue import CommandQueue;from src.core.history import export_queue;import pathlib;root=pathlib.Path('.');q=CommandQueue(root/'data'/'queue.json');print('QUEUE_HISTORY_EXPORT_PASS',export_queue(q,root/'logs'/'queue-history.json'))"
exit /b %errorlevel%
