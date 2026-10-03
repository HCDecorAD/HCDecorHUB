# HCDR Mobile Remote ONE-SHOT DONE

Run once on HOCUONG:

```bat
hcdr-mobile-remote-done.bat
```

The launcher self-elevates once if required, fast-forwards the repository, verifies the always-on and relay source contracts, installs the persistent Scheduled Task + watchdog, verifies watchdog health, performs D02 live HCDR proof, then runs D06 Final Freeze.

Terminal PASS marker:

`HCDR_MOBILE_REMOTE_DONE_PASS always_on=1 live_hcdr=1 final_freeze=1`

Evidence is written to `.runtime\hcdr-mobile-remote-done\summary.json`.

After this one-time installation, HOCUONG no longer depends on a chat command to start the relay. When Windows is running and the machine has Internet access, the Scheduled Task/watchdog keeps the HCDR receiver available for Mobile -> GitHub Relay -> HOCUONG jobs.

A UAC prompt may appear during this one-time installation. That is the only local administrative activation required by this package.
