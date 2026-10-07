package com.hcdecor.mobileagent
import android.content.Context
import android.os.Build
object CapabilityDiscovery {
 fun inspect(context:Context)=mapOf(
  "sdk" to Build.VERSION.SDK_INT.toString(),
  "manufacturer" to Build.MANUFACTURER,
  "model" to Build.MODEL,
  "mediaStore" to "true",
  "directMobile" to "true"
 )
}
