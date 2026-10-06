# Parallel lanes

While P1 APK gate is running:
- Lane A: P2 Permissions/MediaStore/SAF design + code
- Lane B: P3 Command handlers and schemas
- Lane C: P4 Trash/Verify/Audit safety
- Lane D: P5 Device Registry/Capabilities/Modes
- Lane E: P6 Transport/Pairing/Queue contracts
- Lane F: P7 Mobile-PC bridge contracts
- Lane G: P8 HOCUONG/HCDR/ADB/scrcpy adapter contracts
- Lane H: P9 iMaster routing/policy
- Lane I: P10 E2E test matrix/release gate

Rule: parallel BUILD is allowed; PASS remains dependency-gated and real-device tests cannot be marked PASS before prerequisites.
