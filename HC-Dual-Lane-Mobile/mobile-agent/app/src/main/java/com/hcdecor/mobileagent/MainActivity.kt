package com.hcdecor.mobileagent

import android.Manifest
import android.app.Activity
import android.os.Build
import android.os.Bundle
import android.content.pm.PackageManager
import android.widget.Button
import android.widget.LinearLayout
import android.widget.TextView

class MainActivity : Activity() {
 private lateinit var status: TextView
 private lateinit var githubPoller: GitHubCommandPoller

 override fun onCreate(savedInstanceState: Bundle?) {
  super.onCreate(savedInstanceState)
  val root=LinearLayout(this).apply {
   orientation=LinearLayout.VERTICAL
   setPadding(40,70,40,40)
  }
  root.addView(TextView(this).apply { text="HC Mobile Control Center"; textSize=26f })
  githubPoller=GitHubCommandPoller(this){ status.text=it+"\n"+statusText() }
  root.addView(Button(this).apply { text="REMOTE GITHUB · START"; setOnClickListener { githubPoller.start() } })
  root.addView(Button(this).apply { text="REMOTE GITHUB · STOP"; setOnClickListener { githubPoller.stop() } })
  status=TextView(this).apply { text=statusText(); textSize=17f; setPadding(0,30,0,30) }
  root.addView(status)
  githubPoller.start()
  val serviceIntent=android.content.Intent(this,MobileAgentService::class.java)
  if(Build.VERSION.SDK_INT>=26) startForegroundService(serviceIntent) else startService(serviceIntent)
  root.addView(Button(this).apply {
   text="P2 · CẤP QUYỀN MEDIA"
   setOnClickListener { requestMedia() }
  })
  root.addView(Button(this).apply {
   text="TEST · QUÉT ẢNH"
   setOnClickListener {
    try {
     val n=MediaRepository(this@MainActivity).listImages(100).size
     status.text="P2 MEDIA OK · tìm thấy $n ảnh\n"+statusText()
    } catch(e:Exception) {
     status.text="P2 WAITING_PERMISSION · "+(e.message ?: e.javaClass.simpleName)
    }
   }
  })
  root.addView(Button(this).apply {
   text="P3 · TEST FILEOPS"
   setOnClickListener {
    val r=P3Acceptance.run(this@MainActivity)
    status.text=(if(r.passed)"P3 FILEOPS PASS" else "P3 FILEOPS FAIL")+" · "+r.detail+"\n"+statusText()
   }
  })
  root.addView(Button(this).apply {
   text="P4 · TEST TRASH GUARD"
   setOnClickListener {
    val r=P4Acceptance.run(this@MainActivity)
    status.text=(if(r.passed)"P4 TRASH GUARD PASS" else "P4 TRASH GUARD FAIL")+" · "+r.detail+"\n"+statusText()
   }
  })
  root.addView(Button(this).apply {
   text="P5 · TEST DEVICE & MODES"
   setOnClickListener {
    val r=P5Acceptance.run(this@MainActivity)
    status.text=(if(r.passed)"P5 DEVICE MODE PASS" else "P5 DEVICE MODE FAIL")+" · "+r.detail+"\n"+statusText()
   }
  })
  root.addView(Button(this).apply {
   text="P6 · TEST SECURE JOB"
   setOnClickListener { val r=P6Acceptance.run(this@MainActivity); status.text=(if(r.passed)"P6 SECURE JOB PASS" else "P6 SECURE JOB FAIL")+" · "+r.detail+"\n"+statusText() }
  })
  root.addView(Button(this).apply {
   text="P7-P9 · TEST ROUTER"
   setOnClickListener {
    val a=P7P10Acceptance.p7(); val b=P7P10Acceptance.p8(); val c=P7P10Acceptance.p9()
    val ok=a.passed&&b.passed&&c.passed
    status.text=(if(ok)"P7-P9 ROUTER PASS" else "P7-P9 ROUTER FAIL")+" · "+a.detail+" · "+b.detail+" · "+c.detail+"\n"+statusText()
   }
  })
  root.addView(Button(this).apply {
   text="P10 · FINAL PRECHECK"
   setOnClickListener { val r=P7P10Acceptance.p10(); status.text=(if(r.passed)"P10 FINAL E2E PASS" else "P10 FINAL E2E FAIL")+" · "+r.detail+"\n"+statusText() }
  })
  root.addView(Button(this).apply {
   text="RUNTIME · DEVICE.INFO"
   setOnClickListener {
    try { status.text="RUNTIME REAL EXEC · "+RuntimeMvp.execute(this@MainActivity,"{\"jobId\":\"LIVE-DEVICE-1\",\"action\":\"device.info\"}") }
    catch(e:Exception){ status.text="RUNTIME FAIL · "+(e.message ?: e.javaClass.simpleName) }
   }
  })
  root.addView(Button(this).apply {
   text="RUNTIME · MEDIA.LIST"
   setOnClickListener {
    try { status.text="RUNTIME REAL EXEC · "+RuntimeMvp.execute(this@MainActivity,"{\"jobId\":\"LIVE-MEDIA-1\",\"action\":\"media.list\",\"args\":{\"limit\":10}}") }
    catch(e:Exception){ status.text="RUNTIME FAIL · "+(e.message ?: e.javaClass.simpleName) }
   }
  })
  root.addView(TextView(this).apply {
   text="Jobs · Devices · Logs\nMode: ECO\nDirect Mobile: READY"
   textSize=16f
   setPadding(0,35,0,0)
  })
  setContentView(root)
 }

 private fun requestMedia(){
  val p=if(Build.VERSION.SDK_INT>=33)
   arrayOf(Manifest.permission.READ_MEDIA_IMAGES,Manifest.permission.READ_MEDIA_VIDEO)
  else arrayOf(Manifest.permission.READ_EXTERNAL_STORAGE)
  requestPermissions(p,200)
 }

 override fun onRequestPermissionsResult(requestCode:Int,permissions:Array<out String>,grantResults:IntArray){
  super.onRequestPermissionsResult(requestCode,permissions,grantResults)
  status.text=statusText()
 }

 private fun statusText():String {
  val granted=if(Build.VERSION.SDK_INT>=33)
   checkSelfPermission(Manifest.permission.READ_MEDIA_IMAGES)==PackageManager.PERMISSION_GRANTED
  else checkSelfPermission(Manifest.permission.READ_EXTERNAL_STORAGE)==PackageManager.PERMISSION_GRANTED
  return "P1: PASS\nP2 Media permission: "+if(granted)"GRANTED" else "WAITING_USER"
 }
}
