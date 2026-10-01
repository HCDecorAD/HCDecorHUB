# G2 two-key safety

Staging cannot send.

A future controlled G2 live harness must satisfy both:
1. manifest.json has send_armed=true for the test build;
2. runtime/G2_LIVE_ARM.json exists with scope G2_SINGLE_COMMAND and the exact conversation_id.

The current repository ships send_armed=false and no arm token. The operator menu contains no arm action. This prevents accidental real sends while CP1 is still being validated.
