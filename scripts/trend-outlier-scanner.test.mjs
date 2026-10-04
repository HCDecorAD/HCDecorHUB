import assert from "node:assert/strict";
import {scanOutliers} from "../lib/trend-outlier-scanner.mjs";
const rows=[
{id:"a",views:1000,age_hours:24},{id:"b",views:1200,age_hours:20},{id:"c",views:900,age_hours:18},
{id:"d",views:1100,age_hours:16},{id:"winner",views:12000,age_hours:6,audience_fit:90,content_gap:85,production_speed:90,money_fit:90,evidence:["youtube"]}
];
const r=scanOutliers(rows);
assert.equal(r.status,"OBSERVED");assert.equal(r.baseline_views,1100);
const w=r.results.find(x=>x.id==="winner");assert.equal(w.outlier.is_outlier,true);assert.ok(w.outlier.ratio>10);assert.ok(["DO_NOW","DO"].includes(w.opportunity.decision));
assert.equal(scanOutliers(rows.slice(0,2)).status,"INSUFFICIENT_EVIDENCE");
console.log("trend outlier scanner PASS");
