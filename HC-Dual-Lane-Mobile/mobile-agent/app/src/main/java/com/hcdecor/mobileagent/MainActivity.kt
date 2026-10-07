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

 override fun onCreate(savedInstanceState: Bundle?) {
  super.onCreate(savedInstanceState)
  val root=LinearLayout(this).apply {
   orientation=LinearLayout.VERTICAL
   setPadding(40,70,40,40)
  }
  root.addView(TextView(this).apply { text="HC Mobile Control Center"; textSize=26f })
  status=TextView(this).apply { text=statusText(); textSize=17f; setPadding(0,30,0,30) }
  root.addView(status)
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
