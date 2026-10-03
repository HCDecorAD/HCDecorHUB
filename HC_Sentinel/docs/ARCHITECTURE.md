# HC Sentinel — Architecture

## Goal
HC Sentinel is a local-first autonomous visual operations layer. It observes, captures, compares, diagnoses, routes work, verifies outcomes, and records evidence without making the schedule the owner of execution.

## Core principles
1. User/manual command can trigger an immediate run at any time.
2. Schedule is optional, never the START button.
3. Runtime state remains local; GitHub stores versionable sanitized DATA.
4. Fail closed on uncertain external side effects.
5. Adapters isolate tools/providers from the core.
6. Evidence is mandatory before GREEN/DONE.
7. One unhealthy lane must not block unrelated lanes.

## Runtime modes
- MANUAL_NOW
- COMMAND
- EVENT
- WATCH
- SCHEDULED
- AUTO

## Main layers

### 1. Control Plane
Responsibilities:
- command intake
- trigger arbitration
- mission state
- priority
- pause/resume/cancel
- idempotency/effect lock
- checkpoint and lease

### 2. Observer Plane
Responsibilities:
- browser/page attach
- screenshot capture
- DOM snapshot
- console/network collection
- viewport/device matrix
- optional session replay

### 3. Visual Intelligence Plane
Responsibilities:
- pixel diff
- region diff
- perceptual similarity
- layout drift
- typography/color/logo rules
- OCR only as fallback
- confidence and severity

### 4. Quality Plane
Responsibilities:
- accessibility
- responsive checks
- interaction smoke tests
- performance hooks
- broken media/link checks

### 5. Decision & Routing Plane
Responsibilities:
- classify finding
- deduplicate
- choose repair lane
- choose verification recipe
- escalate REVIEW_REQUIRED when confidence is insufficient

### 6. Evidence Plane
Responsibilities:
- before/after/diff
- trace/event timeline
- test result
- source revision
- target revision
- final status

### 7. Adapter Plane
Adapters may include:
- Playwright
- pixelmatch / Resemble.js
- axe-core
- rrweb
- OpenTelemetry
- local queue
- GitHub
- HCDR
- future Computer Use / browser agents

## State model
IDLE -> TRIGGERED -> OBSERVING -> FINDING -> DIAGNOSED -> ROUTED -> ACTIONED -> VERIFYING -> GREEN
Alternative terminal states:
- NO_CHANGE
- REVIEW_REQUIRED
- BLOCKED
- WAITING_CAPABILITY
- UNCERTAIN_EFFECT

## Data boundary
Local:
- live DB
- active queue
- locks
- browser/session profile
- raw screenshots/video
- caches

GitHub:
- source
- schemas
- sanitized manifests
- rules
- checkpoint summaries
- evidence indexes
- curated baselines
- docs
