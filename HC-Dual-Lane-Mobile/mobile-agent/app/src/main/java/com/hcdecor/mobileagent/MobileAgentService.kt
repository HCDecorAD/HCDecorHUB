package com.hcdecor.mobileagent

import android.app.*
import android.content.Intent
import android.os.IBinder

class MobileAgentService: Service() {
 private lateinit var poller: GitHubCommandPoller
 override fun onCreate() {
  super.onCreate()
  val channelId="hc_mobile_agent"
  val nm=getSystemService(NotificationManager::class.java)
  nm.createNotificationChannel(NotificationChannel(channelId,"HC Mobile Agent",NotificationManager.IMPORTANCE_LOW))
  val pi=PendingIntent.getActivity(this,0,Intent(this,MainActivity::class.java),PendingIntent.FLAG_IMMUTABLE)
  val n=Notification.Builder(this,channelId).setContentTitle("HC Mobile Agent 24/7")
   .setContentText("Remote poller đang chạy").setSmallIcon(android.R.drawable.stat_notify_sync)
   .setContentIntent(pi).setOngoing(true).build()
  startForeground(2407,n)
  poller=GitHubCommandPoller(applicationContext){}
  poller.start()
 }
 override fun onStartCommand(intent:Intent?,flags:Int,startId:Int):Int = START_STICKY
 override fun onDestroy(){ if(::poller.isInitialized) poller.stop(); super.onDestroy() }
 override fun onBind(intent:Intent?):IBinder?=null
}
