package com.hcdecor.mobileagent

import android.content.Context

data class P4Result(val passed:Boolean,val detail:String)

object P4Acceptance {
 fun run(context:Context):P4Result {
  val ops=FileOps(context)
  val job="P4-TRASH-GUARD"
  val a=ops.create("p4-guard.txt","HC-P4-RECOVERY")
  Audit.add(AuditEvent(job,"create",if(a.ok)"PASS" else "FAIL",a.verified))
  val b=ops.trash("p4-guard.txt")
  Audit.add(AuditEvent(job,"trash",if(b.ok)"PASS" else "FAIL",b.verified))
  val c=ops.restore("p4-guard.txt")
  Audit.add(AuditEvent(job,"restore",if(c.ok)"PASS" else "FAIL",c.verified))
  val d=ops.trash("p4-guard.txt")
  Audit.add(AuditEvent(job,"retrash",if(d.ok)"PASS" else "FAIL",d.verified))
  val auditOk=Audit.snapshot().count { it.jobId==job && it.verified } >= 4
  val pass=listOf(a,b,c,d).all { it.ok && it.verified } && auditOk
  return P4Result(pass,"trash="+b.verified+", restore="+c.verified+", retrash="+d.verified+", audit="+auditOk)
 }
}
