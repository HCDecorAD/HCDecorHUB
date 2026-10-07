package com.hcdecor.mobileagent

import android.content.Context

data class P5Result(val passed:Boolean,val detail:String)
object P5Acceptance {
 fun run(context:Context):P5Result {
  val registry=DeviceRegistry.current()
  val caps=CapabilityDiscovery.inspect(context)
  val eco=AgentCore.Mode.ECO.name=="ECO"
  val active=AgentCore.Mode.ACTIVE.name=="ACTIVE"
  val turbo=AgentCore.Mode.TURBO.name=="TURBO"
  val direct=caps["directMobile"]=="true"
  val pass=registry.model.isNotBlank() && direct && eco && active && turbo
  Audit.add(AuditEvent("P5-DEVICE","registry-capability-mode",if(pass)"PASS" else "FAIL",pass))
  return P5Result(pass,"device="+registry.model+", sdk="+registry.sdk+", direct="+direct+", modes="+eco+"/"+active+"/"+turbo)
 }
}
