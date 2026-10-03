import test from "node:test";import assert from "node:assert/strict";import {readFile} from "node:fs/promises";
test("P19 target config includes GSC and AMO public targets",async()=>{const x=JSON.parse(await readFile(new URL("../data/targets.json",import.meta.url),"utf8"));assert.deepEqual(x.targets.map(t=>t.projectId).sort(),["amo","gsc"]);});
