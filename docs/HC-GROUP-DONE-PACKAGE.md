# HC GROUP DONE package

Run from the HCDecorHUB root:

```bat
hc-group-done.bat
```

The package runs six independent worker lanes in parallel: core, governor, evidence, registry, tools, and portfolio.

Failure flow:

`FAIL -> verified AutoDebug gates -> bounded retry -> research-request.json -> plugin/web analysis handoff -> fix -> rerun`.

The package never converts an unresolved failure to GREEN. If local retries are exhausted it exits with code 20 and writes `.runtime\hc-group-done\research-request.json`.

Terminal DONE requires every required lane to pass and then the serialized final gate: production build, Master Agent E2E, and production smoke. Use `-SkipProductionVerify` only for non-production/local diagnostics; that run must not be treated as production DONE.
