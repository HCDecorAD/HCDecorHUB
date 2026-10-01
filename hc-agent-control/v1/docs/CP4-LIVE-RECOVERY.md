# CP4 live recovery

42_CAPTURE_CP4_RECOVERY.bat verifies the managed CDP endpoint is reachable after ensure/recovery and writes evidence.

43_CP4_MANAGED_EDGE_RESTART_TEST.bat is the stronger Windows acceptance test. It targets only msedge.exe processes whose command line contains this project's runtime\edge-profile, then verifies recovery. It must not target the user's normal Edge profile.

CP4 becomes PASS only from checkpoint evidence.
