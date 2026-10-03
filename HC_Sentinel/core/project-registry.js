export class ProjectRegistry {
  constructor(){this.projects=new Map();}
  register(project){
    if(!project?.id) throw new Error("project id required");
    const p={enabled:true,lanes:["default"],profiles:{},...project};
    this.projects.set(p.id,p);
    return p;
  }
  get(id){return this.projects.get(id)??null;}
  all(){return [...this.projects.values()];}
  eligible(capability){
    return this.all().filter(p=>p.enabled&&(!capability||(p.capabilities||[]).includes(capability)));
  }
}
