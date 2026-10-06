package com.hcdecor.mobileagent
import android.content.Context
import android.provider.MediaStore

data class MediaItem(val id:Long,val name:String,val mime:String?)

class MediaRepository(private val context:Context) {
 fun listImages(limit:Int=100):List<MediaItem>{
  val out=mutableListOf<MediaItem>()
  val p=arrayOf(MediaStore.Images.Media._ID,MediaStore.Images.Media.DISPLAY_NAME,MediaStore.Images.Media.MIME_TYPE)
  context.contentResolver.query(MediaStore.Images.Media.EXTERNAL_CONTENT_URI,p,null,null,MediaStore.Images.Media.DATE_ADDED+" DESC")?.use{c->
   val a=c.getColumnIndexOrThrow(p[0]); val b=c.getColumnIndexOrThrow(p[1]); val m=c.getColumnIndexOrThrow(p[2])
   while(c.moveToNext() && out.size<limit) out+=MediaItem(c.getLong(a),c.getString(b),c.getString(m))
  }
  return out
 }
}
