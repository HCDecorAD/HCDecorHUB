// Deprecated: destructive smoke logic intentionally disabled.
// Use live read probes or a dedicated disposable test spreadsheet/folder.
console.log(JSON.stringify({ok:false,disabled:true,reason:"unsafe_legacy_smoke_retired"}));
process.exitCode=2;
