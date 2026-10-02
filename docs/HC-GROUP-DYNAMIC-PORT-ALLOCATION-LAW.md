# HC GROUP — DYNAMIC PORT ALLOCATION LAW

Version: 2026-10-02
Status: ACTIVE POLICY

## Rule
Before starting any local dev/test/build server that binds a TCP port:
1. Probe the requested port.
2. If free -> reserve/use it.
3. If occupied -> inspect owner and choose the next allowed free port from the configured range.
4. Never kill an existing listener merely to satisfy a test unless ownership is proven and the action is explicitly safe.
5. Publish the chosen port as evidence and pass it to downstream tests through an environment variable or runtime contract.

## Required behavior
REQUEST_PORT -> PROBE -> FREE ? USE : FIND_NEXT_FREE -> START -> READINESS_PROBE -> TEST

## Defects
BIND_WITHOUT_PORT_PROBE = DEFECT
EADDRINUSE_AFTER_UNPROBED_START = DEFECT
KILL_UNKNOWN_LISTENER_FOR_TEST = DEFECT
HARDCODE_TEST_PORT_WITHOUT_FALLBACK = DEFECT
DOWNSTREAM_TEST_IGNORES_ALLOCATED_PORT = DEFECT

## Default local test range
3219-3299 unless a project defines a narrower approved range.
