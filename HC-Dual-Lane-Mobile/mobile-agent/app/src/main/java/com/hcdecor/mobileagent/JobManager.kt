package com.hcdecor.mobileagent
enum class JobStatus { QUEUED,RUNNING,WAITING_USER,PASS,FAIL,STOPPED }
data class ManagedJob(val id:String,val action:String,var status:JobStatus=JobStatus.QUEUED,var message:String="")
object JobManager {
 private val jobs=linkedMapOf<String,ManagedJob>()
 @Synchronized fun submit(j:ManagedJob){ jobs[j.id]=j }
 @Synchronized fun set(id:String,s:JobStatus,msg:String=""){ jobs[id]?.apply{status=s;message=msg} }
 @Synchronized fun all()=jobs.values.toList()
}
