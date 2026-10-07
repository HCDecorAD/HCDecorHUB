package com.hcdecor.mobileagent
import android.content.Context
import android.os.Handler
import android.os.Looper
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL
import java.util.concurrent.Executors

class GitHubCommandPoller(private val context:Context, private val status:(String)->Unit){
 private val worker=Executors.newSingleThreadExecutor()
 private val main=Handler(Looper.getMainLooper())
 @Volatile private var running=false
 private val prefs=context.getSharedPreferences("hc_remote",Context.MODE_PRIVATE)
 private var lastJob=prefs.getString("last_job","") ?: ""
 fun start(){ if(running)return; running=true; status("REMOTE GITHUB · STARTED"); tick() }
 fun stop(){ running=false; status("REMOTE GITHUB · STOPPED") }
 private fun tick(){ if(!running)return; worker.execute{
  try{
   fun get(url:String):String {
    val h=URL(url).openConnection() as HttpURLConnection
    h.setRequestProperty("Accept","application/vnd.github+json")
    h.setRequestProperty("User-Agent","HC-Mobile-Agent")
    return h.inputStream.bufferedReader().use{it.readText()}
   }
   val issueRaw=get("https://api.github.com/repos/HCDecorAD/HCDecorHUB/issues/112")
   val issueBody=JSONObject(issueRaw).optString("body")
   val comments=org.json.JSONArray(get("https://api.github.com/repos/HCDecorAD/HCDecorHUB/issues/112/comments?per_page=100"))
   var body=issueBody
   if(comments.length()>0){
    val latest=comments.getJSONObject(comments.length()-1).optString("body")
    if(latest.contains("{")&&latest.contains("}")) body=latest
   }
   val a=body.indexOf("{"); val b=body.lastIndexOf("}")
   if(a>=0&&b>a){
    val cmd=JSONObject(body.substring(a,b+1)); val id=cmd.optString("jobId"); val action=cmd.optString("action")
    if(id.isNotBlank()&&id!=lastJob&&action in setOf("device.info","media.list")){
     val result=RuntimeMvp.execute(context,cmd.toString()); lastJob=id; prefs.edit().putString("last_job",id).apply()
     main.post{status("REMOTE EXEC PASS · "+result)}
    }
   }
  }catch(e:Exception){ main.post{status("REMOTE WAIT · "+(e.message?:e.javaClass.simpleName))} }
  main.postDelayed({tick()},5000)
 }}
}
