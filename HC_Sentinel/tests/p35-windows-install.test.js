import test from "node:test";import assert from "node:assert/strict";import {readFile} from "node:fs/promises";
test("windows installer stays ASCII safe and checks Node 22",async()=>{const x=await readFile(new URL("../runtime/install-local.ps1",import.meta.url));assert.ok([...x].every(b=>b<128));const s=x.toString("utf8");assert.match(s,/Node.js 22 or newer/);assert.match(s,/runtime-state/);});
