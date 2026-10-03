import test from "node:test";import assert from "node:assert/strict";import {inspectSnapshot} from "../core/quality.js";
test("network failures become quality finding",()=>{const r=inspectSnapshot({dom:"",failedRequests:[{url:"x"}]});assert.equal(r[0].kind,"NETWORK_FAILURE");});
test("broken media hint is detected",()=>{const r=inspectSnapshot({dom:'<img src="">',failedRequests:[]});assert.equal(r[0].kind,"BROKEN_MEDIA_HINT");});
