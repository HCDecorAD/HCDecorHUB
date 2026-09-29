# HCDecor HUB Runtime — Production Release Runbook

## Scope
Runtime plugin only: `hcdecor-hub-runtime.zip`. Never deploy `homepage-builder.php`, `hcdecor-core.php`, or `recovery-bootstrap.php` as part of this runtime release.

## Authority and gate
Production authority: WordPress at https://hcdecorhub.com.
Deployment and activation are production writes and require explicit approval. Building, hashing, inspecting, and probing public endpoints are read-only/preparation steps.

## Preflight
1. Run `powershell -NoProfile -ExecutionPolicy Bypass -File scripts/build-wordpress-runtime.ps1`.
2. Verify `dist/wordpress-runtime/release.json` version, file count, SHA-256, source commit, and forbidden file list.
3. Run `hcdecor-operator.bat`; require manifest integrity and production guards PASS. PHP syntax remains SKIP until PHP CLI exists.
4. Verify public WordPress REST is healthy and `hcdecor/v1` is absent before first install.
5. Record current plugin inventory and a rollback checkpoint before any mutation.

## Approved execution sequence
Only after explicit production approval:
1. Upload `hcdecor-hub-runtime.zip` through an authenticated WordPress/plugin deployment path.
2. Install without changing homepage content.
3. Activate `HCDecor HUB Runtime`.
4. Verify WordPress REST root exposes namespace `hcdecor/v1`.
5. Run read-only CMS status/inventory probes.
6. Verify custom content types are registered.
7. Verify automation/outbound integrations remain disabled unless separately configured and approved.
8. Record deployment version, package SHA-256, time, actor, and health result.

## Rollback
If activation causes a fatal error, degraded public site, unexpected write, or failed health gate:
1. Deactivate HCDecor HUB Runtime.
2. Remove only the newly installed runtime plugin directory/package.
3. Restore the recorded pre-deploy plugin state if needed.
4. Re-probe homepage and WordPress REST.
5. Record rollback reason and health result.
Do not run homepage builder/recovery scripts as an automatic rollback mechanism.

## Success criteria
- Public homepage remains healthy.
- WordPress REST remains healthy.
- `hcdecor/v1` is registered.
- HCDecor runtime version matches release manifest.
- No homepage overwrite occurred.
- No automation/publish/deploy action started implicitly.
- HUB reports source/production drift cleared for runtime installation.
