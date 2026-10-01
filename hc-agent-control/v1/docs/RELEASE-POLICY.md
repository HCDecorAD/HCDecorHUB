# Production release policy

Only exact gate value PASS satisfies V1 production. PASS_STAGED, PASS_LOCAL, STAGED, OPEN and LOCKED never satisfy production.

A gate becomes PASS only from its required local/live evidence flow. GitHub staging commits alone cannot promote production.
