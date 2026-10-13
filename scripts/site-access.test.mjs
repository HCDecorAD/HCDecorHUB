import assert from "node:assert/strict";
import {usersBySite} from "../lib/site-access.js";
const users=[
 {id:"nv_test",role:"staff",site_ids:["amo"],disabled:false,must_change_password:false},
 {id:"kh1",role:"guest",site_ids:["gsc","amo"],disabled:true,must_change_password:false},
 {id:"kh2",role:"guest",site_ids:["gsc"],disabled:false,must_change_password:true},
];
const r=usersBySite(users);
assert.deepEqual(r.amo.map(u=>u.id),["nv_test","kh1"]);
assert.deepEqual(r.gsc.map(u=>u.id),["kh1","kh2"]);
assert.equal(r.amo[1].status,"Khóa");
assert.equal(r.gsc[1].status,"Chờ đổi Pass");
assert.deepEqual(usersBySite(null).amo,[]);
console.log("site-access tests passed");
