import fs from "node:fs";
const c=JSON.parse(fs.readFileSync(new URL("../config/dr-readiness.json",import.meta.url),"utf8"));
const fail=[];
if(c.backup?.local_operator_backup?.production_authority!==false)fail.push("local backup authority");
if(c.release_rule!=="production-mutation-remains-locked-until-all-required-controls-pass")fail.push("release rule");
const complete=Boolean(c.objectives?.rpo_minutes&&c.objectives?.rto_minutes&&c.backup?.off_device?.implemented&&c.restore?.automated_test_implemented&&c.monitoring?.centralized_durable_history);
console.log(JSON.stringify({contract:"DR_READINESS",complete,blockers:{rpo:c.objectives?.rpo_minutes==null,rto:c.objectives?.rto_minutes==null,off_device_backup:!c.backup?.off_device?.implemented,restore_test:!c.restore?.automated_test_implemented,durable_monitoring:!c.monitoring?.centralized_durable_history}},null,2));
if(fail.length){console.error("DR CONTRACT FAIL "+fail.join(","));process.exit(1)}
console.log("DR CONTRACT PASS; production mutation remains LOCKED until complete");
