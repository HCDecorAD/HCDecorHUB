package com.hcdecor.mobileagent
data class AuditEvent(val jobId:String,val action:String,val result:String,val verified:Boolean)
object Audit {
 private val events=mutableListOf<AuditEvent>()
 @Synchronized fun add(e:AuditEvent){ events+=e }
 @Synchronized fun snapshot()=events.toList()
}
