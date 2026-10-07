package com.hcdecor.mobileagent
enum class Route { DIRECT_MOBILE,LOCAL_PC,HOCUONG,CLOUD,MESH }
object Router {
 fun choose(target:String,heavy:Boolean=false,pcOnline:Boolean=false):Route =
  if(heavy && pcOnline) Route.HOCUONG
  else if(target=="MOBILE") Route.DIRECT_MOBILE
  else if(pcOnline) Route.LOCAL_PC
  else Route.MESH
}
