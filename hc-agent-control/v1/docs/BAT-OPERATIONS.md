# BAT operations

Daily:
- HC_AutoChat.bat — launch UI.
- scripts\90_DIAGNOSTIC.bat — local health snapshot.
- scripts\RUN_PARALLEL_CHECKS.bat then WAIT_PARALLEL_CHECKS.bat — parallel checks.

Build:
- scripts\RUN_V1_PIPELINE.bat — aggregate staging pipeline.
- scripts\51_PACKAGE_STAGING.bat — staging portable folder only.
- scripts\50_BUILD.bat — production-aware build gate; expected to block while live gates are open.

Safety:
- scripts\80_RESET_SAFE_STATE.bat — force persistent STOP ALL.
- scripts\60_BACKUP_RELEASE.bat — snapshot.
- scripts\61_ROLLBACK_LATEST.bat — rollback latest snapshot.

Optional:
- scripts\70_START_WITH_WINDOWS_INSTALL.bat
- scripts\71_START_WITH_WINDOWS_REMOVE.bat

BAT files orchestrate only. Identity, queue, routing and verification logic remain Python modules.
