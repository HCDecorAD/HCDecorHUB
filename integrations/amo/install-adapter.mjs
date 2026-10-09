import fs from 'node:fs';
import path from 'node:path';

const root = process.argv[2] || 'D:\\HCDecorHUB\\HC_Visual_Builder';
const app = path.join(root, 'src', 'App.tsx');
const target = path.join(root, 'src', 'amo-catalog.ts');
const adapter = path.resolve('integrations/amo/amo-catalog.ts');
if (!fs.existsSync(app) || !fs.existsSync(adapter)) throw Error('Missing Builder or adapter');
const source = fs.readFileSync(app, 'utf8');
if (!source.includes('dataBinding') || !source.includes('bindData')) {
  throw Error('Builder binding signature changed: refusing blind patch');
}
fs.copyFileSync(adapter, target);
console.log('AMO_ADAPTER_INSTALLED', target);
console.log('NEXT: wire dataBinding source and bindData resolver after inspecting App.tsx');
