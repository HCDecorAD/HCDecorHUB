import assert from "node:assert/strict";
import {opportunityScore,outlierScore,canScale} from "../lib/trend-opportunity.mjs";

const hot=opportunityScore({freshness:95,growth:92,audience_fit:85,content_gap:80,production_speed:90,money_fit:88,observed:true,evidence:["yt","trends"]});
assert.equal(hot.decision,"DO_NOW");
assert.equal(hot.observed,true);
assert.equal(hot.evidence_count,2);

const weak=opportunityScore({freshness:20,growth:15,audience_fit:30,content_gap:20,production_speed:80,money_fit:10});
assert.equal(weak.decision,"SKIP");

const out=outlierScore({views:50000,baseline_views:10000,age_hours:10});
assert.equal(out.is_outlier,true);
assert.equal(out.ratio,5);
assert.equal(out.velocity,5000);

assert.equal(canScale({opportunity:hot,verified_metrics:false,sample_size:5}),false);
assert.equal(canScale({opportunity:hot,verified_metrics:true,sample_size:2}),false);
assert.equal(canScale({opportunity:hot,verified_metrics:true,sample_size:3}),true);
console.log("trend opportunity gate PASS");
