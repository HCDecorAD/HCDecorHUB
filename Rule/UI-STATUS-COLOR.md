# UI STATUS COLOR — CONSISTENT PASS / DONE SEMANTICS

Status: ACTIVE / HARD / GLOBAL
Priority: P2
Rule ID: UI_STATUS_COLOR
Version: 1.0
Scope: UI / DASHBOARD / CONSOLE / STATUS BADGES / REPORTING

## Canonical colors
- PASS = BLUE / XANH DƯƠNG.
- DONE = GREEN / XANH LÁ.

## Meaning
- PASS: acceptance/test/checkpoint succeeded, but the larger work item may still continue.
- DONE: the scoped work item is fully completed according to its DONE gate.

## UI requirements
- Any interface that renders PASS or DONE MUST preserve these semantic colors.
- Text labels remain visible; color alone must not carry status meaning.
- Maintain readable contrast in both Light and Dark modes.
- Do not reuse PASS blue or DONE green for an incompatible failure/warning state in the same status system.
- Existing product brand colors may remain; this rule governs status semantics, not the entire palette.

## Acceptance
Render a representative PASS and DONE state:
PASS -> blue + readable PASS label.
DONE -> green + readable DONE label.
