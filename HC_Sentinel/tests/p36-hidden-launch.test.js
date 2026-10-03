import test from "node:test";import assert from "node:assert/strict";import {readFile} from "node:fs/promises";
test("hidden launcher runs batch with hidden window style",async()=>{const s=await readFile(new URL("../runtime/start-hidden.vbs",import.meta.url),"utf8");assert.match(s,/shell\.Run cmd, 0, False/);assert.match(s,/start-sentinel\.bat/);assert.ok([...Buffer.from(s)].every(b=>b<128));});
