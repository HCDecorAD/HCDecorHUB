import fs from 'node:fs';
import assert from 'node:assert/strict';
const r = JSON.parse(fs.readFileSync('docs/tchc/capabilities.json', 'utf8'));
assert.equal(r.schema, 'hc.capabilities/v1');
assert.ok(Array.isArray(r.capabilities) && r.capabilities.length > 0);
const TUYEN = ['auto247', 'mcp', 'relay', 'connector', 'chrome', 'artifact', 'manual'];
const DUYET = ['none', 'creative', 'review', 'manual'];
const TRANG_THAI = ['active', 'protected', 'paused', 'archive'];
const ids = new Set();
for (const c of r.capabilities) {
  assert.ok(typeof c.id === 'string' && c.id, 'id');
  assert.ok(!ids.has(c.id), 'duplicate id ' + c.id);
  ids.add(c.id);
  for (const k of ['viec', 'cong_cu']) assert.ok(typeof c[k] === 'string' && c[k], c.id + '.' + k);
  assert.ok(Array.isArray(c.tu_khoa) && c.tu_khoa.length > 0, c.id + '.tu_khoa');
  assert.ok(TUYEN.includes(c.tuyen), c.id + '.tuyen');
  assert.ok(DUYET.includes(c.duyet), c.id + '.duyet');
  assert.ok(TRANG_THAI.includes(c.trang_thai), c.id + '.trang_thai');
  for (const k of ['du_phong', 'thay_the']) if (c[k] !== undefined) assert.ok(Array.isArray(c[k]), c.id + '.' + k);
}
console.log('TCHC_CAPABILITIES_PASS capabilities=' + r.capabilities.length);
