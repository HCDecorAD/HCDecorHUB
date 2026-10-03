# HC Sentinel — Technology Radar

## Adopt early

### Playwright
Use for browser control, stable screenshot capture, viewport/device matrix, DOM/network/console collection, and baseline visual comparison.
Why: built-in screenshot assertions and pixel comparison.
Integration rule: normalize environment before comparing baselines.

### pixelmatch / Resemble.js
Use as lightweight diff engines behind a common VisualDiff adapter.
Why: fast and simple for deterministic UI regressions.
Do not hard-wire either into the core.

### axe-core
Use for automated accessibility checks inside browser test flows.
Why: lightweight and integrates into existing functional/browser tests.
Result must support PASS / VIOLATION / INCOMPLETE.

### rrweb
Optional recorder for reproducing UI state/interaction when screenshots alone are insufficient.
Keep off by default to avoid unnecessary storage/privacy cost.

## Prepare adapter, not core dependency

### OpenTelemetry
Use as the future event/trace/log export layer.
Core should emit structured events first; OTel adapter can be enabled when cross-project observability becomes useful.

### Local durable queue
Start with lightweight local durable storage and leases/checkpoints.
Keep QueueProvider interface replaceable.

### BullMQ
Useful later when Redis-backed distributed workers are justified.
Do not require Redis for v1 local operation.

### Temporal
Study and copy durable-execution patterns, but do not introduce the platform into early HC Sentinel.
Adopt only if workflow volume/complexity outgrows the local control plane.

## Avoid in the early core
- Kubernetes
- paid visual monitoring SaaS as hard dependency
- cloud-only runtime
- schedule-owned mission state
- giant agent framework
- automatic baseline acceptance
- blind retry of deploy/send/write operations

## Design pattern to steal
Observe -> Snapshot -> Compare -> Classify -> Route -> Act -> Verify -> Evidence

Every external tool must fit behind an adapter contract.
