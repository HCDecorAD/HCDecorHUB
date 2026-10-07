package com.hcdecor.mobileagent

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.os.Build

class BootReceiver: BroadcastReceiver() {
 override fun onReceive(context:Context,intent:Intent) {
  if(intent.action==Intent.ACTION_BOOT_COMPLETED || intent.action=="android.intent.action.LOCKED_BOOT_COMPLETED") {
   val i=Intent(context,MobileAgentService::class.java)
   if(Build.VERSION.SDK_INT>=26) context.startForegroundService(i) else context.startService(i)
  }
 }
}
