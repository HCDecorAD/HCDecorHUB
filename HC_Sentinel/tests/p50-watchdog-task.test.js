import test from "node:test";import assert from "node:assert/strict";import {readFile} from "node:fs/promises";
test("watchdog scheduled task uses compatible hidden 2-minute schtasks registration",async()=>{
  const s=await readFile(new URL("../runtime/register-watchdog-task.ps1",import.meta.url),"utf8");
  assert.match(s,/schtasks\.exe/);
  assert.match(s,/WindowStyle Hidden/);
  assert.match(s,/\/SC MINUTE/);
  assert.match(s,/\/MO 2/);
  assert.ok([...Buffer.from(s)].every(b=>b<128));
});
