# HCDR V1.2 isolated lane recovery

Root cause: hcdr-v12-test requires its own polling agent. Production hcdr-job relay does not consume that label.

Recovery design:
- keep production hcdr-job unchanged;
- V1.2 polls only hcdr-v12-test;
- schema must be hcdr-relay/v1.2;
- source is mandatory;
- workspace remains PROJECT guarded;
- install a separate Startup launcher;
- never consume production jobs from the test agent.

Acceptance: open V1.2 health issue -> claim -> hcdr-result/v1.2 -> close.
