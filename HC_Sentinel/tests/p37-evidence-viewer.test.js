import test from "node:test";import assert from "node:assert/strict";import {readFile} from "node:fs/promises";
test("command center includes evidence viewer hooks",async()=>{const html=await readFile(new URL("../ui/index.html",import.meta.url),"utf8");assert.match(html,/id="evidenceList"/);assert.match(html,/id="refreshEvidence"/);});
