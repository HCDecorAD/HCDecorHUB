import test from "node:test";import assert from "node:assert/strict";import {createConfigSnapshot} from "../core/config-snapshot.js";
test("config snapshot redacts secret-like fields",()=>{const x=createConfigSnapshot({theme:"dark",token:"abc",password:"x"});assert.equal(x.config.theme,"dark");assert.equal(x.config.token,"[REDACTED]");assert.equal(x.config.password,"[REDACTED]");});
