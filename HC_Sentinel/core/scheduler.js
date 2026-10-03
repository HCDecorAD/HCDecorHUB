export class TriggerBroker {
  constructor() { this.queue=[]; this.seq=0; }
  push({ mode, payload={}, priority }) {
    const p = priority ?? (mode==="MANUAL_NOW"?100:mode==="COMMAND"?90:mode==="EVENT"?70:mode==="WATCH"?60:mode==="AUTO"?50:40);
    const item={ id:++this.seq, mode, payload, priority:p };
    this.queue.push(item);
    return item;
  }
  next() {
    this.queue.sort((a,b)=>b.priority-a.priority||a.id-b.id);
    return this.queue.shift() ?? null;
  }
}
