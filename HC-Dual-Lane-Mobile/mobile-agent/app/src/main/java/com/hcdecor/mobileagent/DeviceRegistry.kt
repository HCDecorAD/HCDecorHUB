package com.hcdecor.mobileagent
import android.os.Build

data class DeviceRecord(val manufacturer:String,val model:String,val sdk:Int,val mode:String)
object DeviceRegistry {
 fun current()=DeviceRecord(Build.MANUFACTURER,Build.MODEL,Build.VERSION.SDK_INT,"ECO")
}
