# HC GROUP remaining DONE program

Current verified baselines are not repeated. Remaining work is divided into six packages and three dependency waves.

| Package | Scope | Weight | Wave |
|---|---|---:|---:|
| D01 | Evidence durability final: archive index, rotation diagnostics, operator visibility | 2 | 1 |
| D02 | Live HCDR health roundtrip on HOCUONG with correlation IDs | 3 | 1 |
| D03 | Executable AutoDebug diagnosis/repair-verification runtime proof | 2 | 1 |
| D04 | Agent Control + AutoChat runtime implementation and local E2E | 4 | 2 |
| D05 | MediaFlow + Video Downloader + Design AI local runtime adapters | 4 | 2 |
| D06 | Portfolio final freeze/release | 3 | 3 |

Run all dependency-ready work with:

```bat
hc-group-remaining-all.bat
```

Wave 1 runs three packages in parallel; Wave 2 runs two packages in parallel; Wave 3 serializes the final freeze. A failed package does not become GREEN: the program writes `.runtime\remaining-done\research-request.json` for AutoDebug/plugin/web recovery.

D02 deliberately performs only the read-only HCDR `health` operation. D04/D05 fail as NOT_DONE until actual runtime implementations/tests exist; their source-contract tests alone are not accepted as runtime DONE.
