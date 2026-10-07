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
 private var lastJob=""
 fun start(){ if(running)return; running=true; status("REMOTE GITHUB · STARTED"); tick() }
 fun stop(){ running=false; status("REMOTE GITHUB · STOPPED") }
 private fun tick(){ if(!running)return; worker.execute{
  try{
   val c=URL("https://api.github.com/repos/HCDecorAD/HCDecorHUB/issues/112").openConnection() as HttpURLConnection
   c.setRequestProperty("Accept","application/vnd.github+json")
   val raw=c.inputStream.bufferedReader().use{it.readText()}
   val body=JSONObject(raw).optString("body")
   val a=body.indexOf("{"); val b=body.lastIndexOf("}")
   if(a>=0&&b>a){
    val cmd=JSONObject(body.substring(a,b+1)); val id=cmd.optString("jobId"); val action=cmd.optString("action")
    if(id.isNotBlank()&&id!=lastJob&&action in setOf("device.info","media.list")){
     val result=RuntimeMvp.execute(context,cmd.toString()); lastJob=id
     main.post{status("REMOTE EXEC PASS · "+result)}
    }
   }
  }catch(e:Exception){ main.post{status("REMOTE WAIT · "+(e.message?:e.javaClass.simpleName))} }
  main.postDelayed({tick()},5000)
 }}
}
