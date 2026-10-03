export class BaselineManager {
  constructor(){this.items=new Map();}
  key(projectId,viewport="default"){return `${projectId}:${viewport}`;}
  set(projectId,viewport,baseline){
    if(!baseline?.revision) throw new Error("BASELINE_REVISION_REQUIRED");
    const item={projectId,viewport,approved:baseline.approved===true,...baseline};
    this.items.set(this.key(projectId,viewport),item); return item;
  }
  resolve(projectId,viewport="default"){return this.items.get(this.key(projectId,viewport))??this.items.get(this.key(projectId,"default"))??null;}
  approve(projectId,viewport,revision){
    const item=this.resolve(projectId,viewport); if(!item||item.revision!==revision) return false;
    item.approved=true; return true;
  }
  canCompare(projectId,viewport){const item=this.resolve(projectId,viewport);return !!item?.approved;}
}
