# Activity/status policy

Presence state is derived from CDP discovery and timestamps.

DOM activity is a separate read-only signal contract:
- streaming=true -> WORKING
- stop_button=true -> WORKING
- composer_disabled=true -> WORKING
- conversation_ready=true -> IDLE
- otherwise UNKNOWN

V1-next does not infer WORKING from titles, screen coordinates, or timing alone. DOM observation must never authorize Send.
