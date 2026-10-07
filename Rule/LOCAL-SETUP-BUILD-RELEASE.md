# LOCAL SETUP / BUILD / RELEASE — HOCUONG HARD RULE
Status: ACTIVE / HARD / GLOBAL
Priority: P0
Rule ID: LOCAL_SETUP_BUILD_RELEASE
Version: 1.0
Scope: HOCUONG_SETUP / DOWNLOAD / INSTALL / BUILD / TEST / RELEASE / SYNC

## Trigger
Any mission that targets files, source, dependencies, setup, build, test, packaging or release on HOCUONG.

## Hard execution law
1. Once execution reaches HOCUONG, all capabilities available locally MUST execute locally: download, git, curl, package install, unzip, file operations, setup, scripts, build, test, fix, retest, package and evidence.
2. Dispatch a coarse-grained local job/script once; do NOT remote-control every internal command.
3. Mesh/Gateway is control-plane/dispatch/status/fallback only. Do not hairpin local work through Mesh when HOCUONG can perform it itself.
4. RDC is GUI/visual/rescue only. Use it only when LOCAL_UNAVAILABLE, LOCAL_FAILED, or GUI_REQUIRED; record the reason.
5. Failure loop stays local: BUILD -> RUN -> REAL TEST -> FAIL -> FIX LOCAL -> RETEST, repeated until PASS or a true Owner Boundary.
6. Internal checks such as syntax, file exists, process running, HTTP 200, install success or unit tests alone are not product PASS unless they prove the requested capability.
7. Release order is mandatory:
   LOCAL SOURCE/DATA -> LOCAL SETUP -> LOCAL BUILD -> LOCAL RUN -> REAL TARGETED TEST -> FIX/RETEST -> LOCAL PASS -> PUBLIC -> VERIFY PUBLIC -> GITHUB SYNC -> DONE.
8. NO PUBLIC before LOCAL PASS.
9. NO DONE before PUBLIC VERIFY when Public is in mission scope.
10. GitHub sync/commit/push happens after accepted local build and public verification. GitHub/CI must not be used to bypass a local step HOCUONG can execute.
11. Preserve a known-good build; public failure must rollback or fix/retest. Never overwrite a proven production version with a failed candidate.
12. Downloaded third-party source must record source URL, revision/SHA, license and allowed use. Restricted/copyleft/reference-only source must be isolated and not embedded unless its license obligations are explicitly accepted.

## Setup bootstrap
Every setup/build mission on HOCUONG MUST load this rule before mutation. A setup script SHOULD emit:
RULE_LOCAL_SETUP=PASS
before doing mutable setup work after its prerequisites/routing checks pass.

## Acceptance
PASS = requested capability works on HOCUONG in a real targeted test.
PUBLIC PASS = deployed capability is verified on its public target.
DONE = PASS + required Public verification + GitHub sync, with evidence.

## Conflict / relationship
- Extends LOCAL_FIRST and FAST_DELIVERY.
- RULE_GUARD and RULE_BOOTSTRAP remain authoritative.
- This rule overrides lower-priority workflow guidance that sends locally-capable setup/build/test operations through Mesh/RDC/cloud.
