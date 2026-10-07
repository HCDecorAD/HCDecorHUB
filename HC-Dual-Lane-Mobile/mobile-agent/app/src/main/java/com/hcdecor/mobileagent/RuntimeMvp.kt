package com.hcdecor.mobileagent

import android.content.Context
import android.provider.MediaStore
import android.os.Build
import org.json.JSONObject

object RuntimeMvp {
 fun execute(context:Context, raw:String):String {
  val req=JSONObject(raw)
  val jobId=req.optString("jobId")
  val action=req.optString("action")
  require(jobId.isNotBlank()){"jobId required"}
  val result=when(action){
   "device.info" -> JSONObject()
    .put("manufacturer",Build.MANUFACTURER)
    .put("model",Build.MODEL)
    .put("sdk",Build.VERSION.SDK_INT)
   "media.list" -> {
    val limit=req.optJSONObject("args")?.optInt("limit",20)?.coerceIn(1,100) ?: 20
    val granted=if(Build.VERSION.SDK_INT>=33)
     context.checkSelfPermission(android.Manifest.permission.READ_MEDIA_IMAGES)==android.content.pm.PackageManager.PERMISSION_GRANTED
    else context.checkSelfPermission(android.Manifest.permission.READ_EXTERNAL_STORAGE)==android.content.pm.PackageManager.PERMISSION_GRANTED
    if(!granted) throw SecurityException("MEDIA_PERMISSION_REQUIRED")
    val items=MediaRepository(context).listImages(limit)
    JSONObject().put("count",items.size).put("limit",limit)
   }
   else -> throw IllegalArgumentException("action not allowlisted")
  }
  Audit.add(AuditEvent(jobId,action,"PASS",true))
  return JSONObject().put("jobId",jobId).put("ok",true).put("action",action).put("result",result).toString()
 }
}
