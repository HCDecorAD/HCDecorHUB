export class NotificationCenter{
  constructor({sinks=[]}={}){this.sinks=sinks;this.items=[];}
  publish(event){
    const item={id:this.items.length+1,at:new Date().toISOString(),level:event.level??"info",...event};
    this.items.push(item);
    for(const sink of this.sinks){try{sink.send?.(item);}catch{}}
    return item;
  }
  unread(){return this.items.filter(x=>!x.read);}
  markRead(id){const x=this.items.find(i=>i.id===id);if(x)x.read=true;return x??null;}
}
