package com.hcdecor.mobileagent

import android.content.Context

data class P5Result(val passed:Boolean,val detail:String)
object P5Acceptance {
 fun run(context:Context):P5Result {
  val registry=DeviceRegistry.current()
  val caps=CapabilityDiscovery.discover()
  val eco=AgentCore.mode(false,false)==Mode.ECO
  val active=AgentCore.mode(false,true)==Mode.ACTIVE
  val turbo=AgentCore.mode(true,false)==Mode.TURBO
  val pass=registry.model.isNotBlank() && caps.directMobile && eco && active && turbo
  Audit.add(AuditEvent("P5-DEVICE","registry-capability-mode",if(pass)"PASS" else "FAIL",pass))
  return P5Result(pass,"device="+registry.model+", sdk="+registry.sdk+", direct="+caps.directMobile+", modes="+eco+"/"+active+"/"+turbo)
 }
}
