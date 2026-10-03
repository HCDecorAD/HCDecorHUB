# Core

Provider-independent HC Sentinel kernel.

Expected modules:
- control/
- missions/
- triggers/
- state/
- policy/
- evidence/
- routing/

Core must not directly depend on Playwright, Redis, Temporal, OpenTelemetry, GitHub, HCDR, or any single visual-diff engine.
