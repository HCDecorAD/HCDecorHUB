# Safe update workflow

Before replacing V1 files:
- run scripts\65_PREPARE_UPDATE.bat;
- this backs up user state and code, then forces STOP ALL.

After replacing code:
- run scripts\66_POST_UPDATE_VALIDATE.bat;
- migration preserves compatible aliases/queue and self-tests the build.

If validation fails:
- run scripts\67_UPDATE_ROLLBACK.bat;
- rollback restores the previous code baseline and leaves STOP ALL enabled.

Never overwrite data/ from a release package during an update.
