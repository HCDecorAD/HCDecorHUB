# RuleGuard
Status: ACTIVE / HARD / GLOBAL
Priority: P0

RuleGuard is the authority for rule consistency.
- NO_CONFLICTING_ACTIVE_RULES.
- New/changed rule must declare id, version, status, priority, scope, trigger/action, fallback/forbidden where relevant, supersedes/overrides.
- Cross-check every ACTIVE rule before publication.
- Same scope + trigger + incompatible action at same priority => CONFLICT, publication blocked.
- Higher priority wins only through explicit hierarchy/override.
- Superseded rules move to archived/inactive; never remain competing ACTIVE rules.
- Runtime loads only manifest entries with status ACTIVE.
- Rule change is not PUBLIC until committed to main and synced/verified on HOCUONG.
