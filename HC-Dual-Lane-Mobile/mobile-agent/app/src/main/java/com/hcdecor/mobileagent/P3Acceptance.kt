package com.hcdecor.mobileagent

import android.content.Context

data class P3Result(val passed:Boolean,val detail:String)

object P3Acceptance {
 fun run(context:Context):P3Result {
  val ops=FileOps(context)
  val a=ops.create("p3-source.txt","HC-P3")
  val b=ops.copy("p3-source.txt","p3-copy.txt")
  val c=ops.rename("p3-copy.txt","p3-renamed.txt")
  val d=ops.trash("p3-renamed.txt")
  val pass=listOf(a,b,c,d).all { it.ok && it.verified }
  Audit.add(AuditEvent("P3-ACCEPTANCE","fileops",if(pass)"PASS" else "FAIL",pass))
  return P3Result(pass,"create="+a.verified+", copy="+b.verified+", rename="+c.verified+", trash="+d.verified)
 }
}
