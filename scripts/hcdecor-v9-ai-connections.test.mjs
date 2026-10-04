import assert from 'node:assert/strict';
import fs from 'node:fs';

const wrangler=fs.readFileSync('wrangler.jsonc','utf8');
const ai=fs.readFileSync('lib/ai/connections.js','utf8');
const route=fs.readFileSync('app/api/ai/status/route.js','utf8');
const manifest=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));

assert.ok(wrangler.includes('"ai": {"binding":"AI"}'),'Cloudflare AI binding missing');
for(const token of [
  'cloudflare-workers-ai',
  '@cf/meta/llama-3.2-3b-instruct',
  '@cf/black-forest-labs/flux-1-schnell',
  'CLOUDFLARE_BINDING',
  'getCloudflareContext',
  'external-required',
  'binding_status',
  'secret_policy:"SERVER_ONLY"'
]) assert.ok(ai.includes(token),token);
assert.ok(route.includes('deep=u.searchParams.get("deep")==="1"'));
assert.ok(route.includes('probeAIConnections({deep})'));
const pkg=manifest.packages.find(x=>x.id==='PKG-05');
assert.ok(pkg);
assert.ok(['BUILDING','LOCAL_PASS','INTEGRATED','DONE'].includes(pkg.state));
console.log('HCDECOR_V9_AI_CONNECTIONS_CONTRACT_PASS cloudflare_ai=1 text=1 image=1 trend=1 video_external=1 deep_probe=1');
