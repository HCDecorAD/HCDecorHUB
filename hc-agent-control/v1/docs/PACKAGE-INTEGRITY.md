# Package integrity

After packaging, run scripts\58_HASH_PACKAGE.bat. It records SHA-256 and size for every staging package file in logs\package-hashes.json.

Acceptance evidence should be captured from the same package fingerprint that is released. Rebuilds require a new fingerprint and smoke test.
