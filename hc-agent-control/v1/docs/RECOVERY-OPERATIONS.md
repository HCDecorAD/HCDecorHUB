# Recovery operations

- 92_WATCHDOG_ONCE.bat checks CDP listener and runs a read-only probe.
- 93_WATCHDOG_LOOP.bat repeats every 30 seconds while its console is open.
- Watchdog may start managed Edge through the existing ensure script; it never sends a ChatGPT command.
- 91_LOG_CLEANUP.bat removes old .log files after 14 days and transient .exit markers after 2 days.
- 94_MAINTENANCE.bat runs cleanup, diagnostic and project validation.

Production service installation is intentionally excluded from V1 staging. The loop is explicit and stoppable.
