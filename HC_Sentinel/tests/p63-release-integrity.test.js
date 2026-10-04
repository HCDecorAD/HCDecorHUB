import test from "node:test";import assert from "node:assert/strict";import {releaseDigest,verifyReleaseDigest} from "../core/release-integrity.js";
test("release digest detects tampering",()=>{const p={version:"1.2.0",commit:"abc"};const d=releaseDigest(p);assert.equal(verifyReleaseDigest(p,d),true);assert.equal(verifyReleaseDigest({...p,commit:"x"},d),false);});
