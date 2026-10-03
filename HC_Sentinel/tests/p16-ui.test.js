import test from "node:test";import assert from "node:assert/strict";import {readFile} from "node:fs/promises";
test("command center contains required operator controls",async()=>{const html=await readFile(new URL("../ui/index.html",import.meta.url),"utf8");for(const s of ["Run Now","Compare","Verify","Route Repair","themeToggle","P15","P19"])assert.match(html,new RegExp(s));});
test("light theme uses gray-white surfaces",async()=>{const css=await readFile(new URL("../ui/styles.css",import.meta.url),"utf8");assert.match(css,/data-theme="light"/);assert.match(css,/#f3f5f7/);});
