package com.hcdecor.mobileagent

data class DeviceState(val id:String, val mode:String="ECO", val online:Boolean=true)
data class Job(val id:String, val action:String, val target:String="MOBILE", val mutation:Boolean=false)

object PhaseServices {
 fun capabilities() = listOf("media.list","file.create","file.copy","file.move","file.rename","trash","restore","device.info")
 fun mutationPolicy(job:Job) = if(job.mutation) "PREVIEW_CONFIRM_EXECUTE_VERIFY" else "EXECUTE_VERIFY"
 fun route(job:Job) = when(job.target) {
  "MOBILE" -> "DIRECT_MOBILE"
  "PC" -> "HOCUONG"
  else -> "IMASTER"
 }
 fun mode(heavy:Boolean, transferring:Boolean) = if(heavy) "PC_OR_CLOUD" else if(transferring) "TURBO" else "ECO"
}
