import test from "node:test";import assert from "node:assert/strict";import {FindingRouter} from "../core/router.js";
test("visual diff routes to visual repair",()=>assert.equal(new FindingRouter().route({kind:"VISUAL_DIFF",confidence:.9}).lane,"visual-repair"));
test("low confidence requires review",()=>assert.equal(new FindingRouter().route({kind:"VISUAL_DIFF",confidence:.2}).status,"REVIEW_REQUIRED"));
