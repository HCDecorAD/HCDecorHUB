export class LocalNotifier{
  constructor({write}){this.write=write;}
  async send(event){
    const level=event.level??"info";
    const title=event.title??"HC Sentinel";
    const message=event.message??event.type??"Notification";
    await this.write({level,title,message,at:new Date().toISOString(),event});
    return {delivered:true,level,title,message};
  }
}
