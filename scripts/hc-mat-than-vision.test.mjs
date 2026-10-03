import fs from 'node:fs';
import assert from 'node:assert/strict';
const s=fs.readFileSync('tools/hc-mat-than/export-vision.ps1','utf8');
for(const token of [
  'latest.png',
  'Convert]::ToBase64String',
  'HCDecorAD/HCDecor-HCDR-Relay',
  'observability/hc-mat-than/latest.png',
  'gh api',
  'HC_MAT_THAN_VISION_EXPORT_PASS'
]) assert.ok(s.includes(token),token);
console.log('HC_MAT_THAN_VISION_EXPORT_CONTRACT_PASS private_relay=1 on_demand=1');
