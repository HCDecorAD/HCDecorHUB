# HCDecor HUB Batch Operator

Run from the repository root on Windows:

`hcdecor-operator.bat`

Optional fast-forward before checks:

`hcdecor-operator.bat --pull`

The batch validates Git/manifest, runs PHP syntax checks when PHP CLI is installed, verifies critical outbound/review safety guards, and checks the Vercel build filter.

It intentionally does **not** publish social content, restore backups, change credentials, commit, push, or trigger paid AI calls.
