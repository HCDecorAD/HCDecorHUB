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
 private val actions=setOf("device.info","media.list")
 fun start(){ if(running)return; running=true; status("REMOTE GITHUB · STARTED"); tick() }
 fun stop(){ running=false; status("REMOTE GITHUB · STOPPED") }
 private fun get(url:String):String {
  val h=URL(url).openConnection() as HttpURLConnection
  h.connectTimeout=5000; h.readTimeout=5000
  h.setRequestProperty("Accept","application/vnd.github+json")
  h.setRequestProperty("User-Agent","HC-Mobile-Agent")
  return h.inputStream.bufferedReader().use{it.readText()}
 }
 private fun report(result:String):Boolean {
  val h=URL("http://127.0.0.1:8770/mobile/ack").openConnection() as HttpURLConnection
  h.requestMethod="POST"; h.doOutput=true; h.connectTimeout=3000; h.readTimeout=3000
  h.setRequestProperty("Content-Type","application/json"); h.setRequestProperty("Connection","close")
  h.outputStream.use{it.write(result.toByteArray(Charsets.UTF_8))}
  return h.responseCode in 200..299
 }
 private fun tick(){ if(!running)return; worker.execute{
  try{
   val issueRaw=get("https://api.github.com/repos/HCDecorAD/HCDecorHUB/issues/113")
   val issueBody=JSONObject(issueRaw).optString("body")
   val comments=org.json.JSONArray(get("https://api.github.com/repos/HCDecorAD/HCDecorHUB/issues/113/comments?per_page=100"))
   var cmd:JSONObject?=null
   for(i in comments.length()-1 downTo 0){
    val body=comments.getJSONObject(i).optString("body")
    val a=body.indexOf("{"); val b=body.lastIndexOf("}")
    if(a>=0&&b>a) try{
     val x=JSONObject(body.substring(a,b+1))
     if(x.optString("action") in actions && x.optString("jobId").isNotBlank()){cmd=x;break}
    }catch(_:Exception){}
   }
   if(cmd==null){
    val a=issueBody.indexOf("{"); val b=issueBody.lastIndexOf("}")
    if(a>=0&&b>a) cmd=JSONObject(issueBody.substring(a,b+1))
   }
   cmd?.let{
    val id=it.optString("jobId"); val action=it.optString("action")
    if(id.isNotBlank()&&id!=lastJob&&action in actions){
     val result=RuntimeMvp.execute(context,it.toString())
     if(report(result)){
      lastJob=id
      prefs.edit().putString("last_job",id).putString("last_result",result).apply()
      main.post{status("CLOSED LOOP PASS · "+result)}
     }else main.post{status("ACK RETRY · "+id)}
    }
   }
  }catch(e:Exception){ main.post{status("REMOTE WAIT · "+(e.message?:e.javaClass.simpleName))} }
  main.postDelayed({tick()},5000)
 }}
}
