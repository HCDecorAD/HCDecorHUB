# Long-run operations

- Auto refresh defaults to 5 seconds and is configurable in config/settings.json.
- Connection loss can trigger a dependency-free Windows beep; notifications can be disabled in settings.
- Queue compaction archives only PASS/FAILED/CANCELLED items and preserves active READY/RUNNING/RETRY work.
- Daily maintenance: scripts\84_DAILY_MAINTENANCE.bat.
- Support snapshot: scripts\85_SUPPORT_SNAPSHOT.bat.
- Long-run QA: scripts\46_LONG_RUN_QA.bat.

No maintenance script enables Real Send.
