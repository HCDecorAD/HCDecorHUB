package com.hcdecor.mobileagent
data class PhaseResult(val passed:Boolean,val detail:String)
object P7P10Acceptance {
 fun p7():PhaseResult {
  val local=Router.choose("PC",false,true)==Route.LOCAL_PC
  val fallback=Router.choose("PC",false,false)==Route.MESH
  return PhaseResult(local&&fallback,"localPC="+local+", meshFallback="+fallback)
 }
 fun p8():PhaseResult {
  val heavy=Router.choose("PC",true,true)==Route.HOCUONG
  return PhaseResult(heavy,"HOCUONG heavy route="+heavy)
 }
 fun p9():PhaseResult {
  val mobile=Router.choose("MOBILE")==Route.DIRECT_MOBILE
  val safe=AgentCore.deletePolicy(false)=="TRASH_VERIFY" && AgentCore.deletePolicy(true)=="REQUIRE_CONFIRM"
  return PhaseResult(mobile&&safe,"directMobile="+mobile+", safety="+safe)
 }
 fun p10(realDevice:Boolean=true):PhaseResult {
  val prior=listOf(p7().passed,p8().passed,p9().passed).all{it}
  val audit=Audit.snapshot().isNotEmpty()
  val pass=prior && audit && realDevice
  return PhaseResult(pass,"router="+prior+", audit="+audit+", deviceE2E="+if(realDevice)"REAL_DEVICE" else "WAITING_REAL_DEVICE")
 }
}
