import test from "node:test";import assert from "node:assert/strict";import {checkResourceBudget} from "../core/resource-budget.js";
test("resource budget throttles excessive usage",()=>{assert.equal(checkResourceBudget({cpuPercent:90,memoryPercent:20,queueDepth:0}).status,"THROTTLE");assert.equal(checkResourceBudget({cpuPercent:20,memoryPercent:20,queueDepth:1}).status,"OK");});
