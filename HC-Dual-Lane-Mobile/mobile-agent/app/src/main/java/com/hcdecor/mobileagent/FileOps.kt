package com.hcdecor.mobileagent
import android.content.Context
import java.io.File

data class FileOpResult(val ok:Boolean,val path:String,val verified:Boolean,val message:String="")
class FileOps(private val context:Context){
 private val root get()=File(context.filesDir,"hc-agent").apply{mkdirs()}
 fun create(name:String,text:String="HC Mobile Agent"):FileOpResult{
  val f=File(root,name); f.writeText(text)
  return FileOpResult(f.exists(),f.absolutePath,f.exists()&&f.readText()==text)
 }
 fun rename(from:String,to:String):FileOpResult{
  val a=File(root,from); val b=File(root,to); val ok=a.exists()&&a.renameTo(b)
  return FileOpResult(ok,b.absolutePath,ok&&b.exists()&&!a.exists())
 }
 fun copy(from:String,to:String):FileOpResult{
  val a=File(root,from); val b=File(root,to)
  if(!a.exists()) return FileOpResult(false,b.absolutePath,false,"source missing")
  a.copyTo(b,true); return FileOpResult(b.exists(),b.absolutePath,b.exists()&&a.readBytes().contentEquals(b.readBytes()))
 }
 fun trash(name:String):FileOpResult{
  val a=File(root,name); val t=File(root,".trash").apply{mkdirs()}; val b=File(t,name)
  val ok=a.exists()&&a.renameTo(b); return FileOpResult(ok,b.absolutePath,ok&&b.exists()&&!a.exists())
 }
}
