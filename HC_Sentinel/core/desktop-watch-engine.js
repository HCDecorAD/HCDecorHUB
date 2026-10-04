export class DesktopWatchEngine{
  constructor({store,windowProvider,chatBridge,logs,tray,now=()=>Date.now()}){
    this.store=store;this.windowProvider=windowProvider;this.chatBridge=chatBridge;this.logs=logs;this.tray=tray;this.now=now;this.running=false;
  }
  async sourceSnapshot(w){
    if(w.sourceType==="CHATGPT"){
      if(!w.chatAlias)throw new Error("CHAT_ALIAS_REQUIRED");
      return this.chatBridge.snapshot(w.chatAlias);
    }
    const xs=await this.windowProvider.list();
    const title=String(w.titleContains??"").toLowerCase(),proc=String(w.processName??"").toLowerCase();
    const win=xs.find(x=>(!proc||String(x.processName).toLowerCase()===proc)&&(!title||String(x.title).toLowerCase().includes(title)));
    if(!win)return {online:false,state:"OFFLINE"};
    const cap=await this.windowProvider.capture(win);
    return {online:true,busy:false,state:"IDLE",window:win,hash:cap.hash,evidencePath:cap.path};
  }
  done(w,snap){
    if(!w.stopOnDone||w.sourceType!=="CHATGPT")return false;
    const tail=String(snap.assistant_tail??"").slice(-4000).toUpperCase();
    return (w.donePatterns??["DONE"]).some(p=>tail.includes(String(p).toUpperCase()));
  }
  async tickOne(w){
    const now=this.now(),rt0=w.runtime??{},interval=Math.max(0,Number(w.intervalSec??60))*1000;
    if(interval>0&&rt0.lastCheckedAtMs!==undefined&&now-rt0.lastCheckedAtMs<interval)return {id:w.id,state:"WAITING",previousState:rt0.state??null,skipped:true};
    let snap;
    try{snap=await this.sourceSnapshot(w);}catch(e){snap={online:false,state:"BLOCKED",error:String(e.message||e)};}
    if(!snap.online){
      const state=snap.state??"OFFLINE";await this.store.patch(w.id,{runtime:{...(w.runtime??{}),state,lastCheckedAt:new Date(now).toISOString(),lastCheckedAtMs:now,error:snap.error??null}});
      return {id:w.id,state};
    }
    if(snap.busy){
      await this.store.patch(w.id,{runtime:{...(w.runtime??{}),state:"BUSY",lastCheckedAt:new Date(now).toISOString(),lastCheckedAtMs:now,lastHash:snap.hash??w.runtime?.lastHash}});
      return {id:w.id,state:"BUSY"};
    }
    if(this.done(w,snap)){
      await this.store.patch(w.id,{enabled:false,runtime:{...(w.runtime??{}),state:"DONE",lastCheckedAt:new Date(now).toISOString(),lastCheckedAtMs:now,lastHash:snap.hash}});
      await this.tray?.send?.({title:`Sentinel Watch — ${w.name??w.id}`,message:"DONE detected. Watch stopped.",level:"info"});
      return {id:w.id,state:"DONE"};
    }
    const rt=w.runtime??{},changed=!rt.lastHash||rt.lastHash!==snap.hash;
    const lastChangeAt=changed?now:(rt.lastChangeAtMs??now);
    let state=changed?"ACTIVE":((now-lastChangeAt)>=Number(w.stuckSec??300)*1000?"STUCK":"IDLE");
    let sent=false,dispatch=null,sentEpoch=rt.sentEpoch??false;
    if(changed)sentEpoch=false;
    if(state==="STUCK"&&w.autoSend&&!sentEpoch){
      const lastSend=rt.lastSendAtMs??0,cooldown=Number(w.cooldownSec??600)*1000;
      if(now-lastSend>=cooldown){
        if(!w.dispatchAlias)state="WAITING_DISPATCHER";
        else{
          dispatch=await this.chatBridge.send(w.dispatchAlias,w.command);
          if(dispatch?.sent){sent=true;sentEpoch=true;state="SENT";await this.tray?.send?.({title:`Sentinel Auto Command — ${w.name??w.id}`,message:w.command,level:"warning"});}
          else state=dispatch?.status??"BLOCKED";
        }
      }else state="COOLDOWN";
    }
    const runtime={state,lastCheckedAt:new Date(now).toISOString(),lastCheckedAtMs:now,lastHash:snap.hash,lastChangeAtMs:lastChangeAt,lastSendAtMs:sent?now:(rt.lastSendAtMs??0),sentEpoch,evidencePath:snap.evidencePath??null,assistantTail:snap.assistant_tail?.slice(-1200)??null,error:null};
    await this.store.patch(w.id,{runtime});
    await this.logs?.append?.({level:state==="STUCK"?"warning":"info",type:"DESKTOP_WATCH",message:`${w.id} ${state}`});
    return {id:w.id,state,sent,dispatch};
  }
  async tick(){
    const xs=await this.store.list(),results=[];
    for(const w of xs)if(w.enabled)results.push(await this.tickOne(w));
    return results;
  }
  start(ms=5000){
    if(this.running)return;this.running=true;
    const loop=async()=>{if(!this.running)return;try{await this.tick();}catch{}finally{if(this.running)setTimeout(loop,ms);}};
    setTimeout(loop,250);
  }
  stop(){this.running=false;}
}
