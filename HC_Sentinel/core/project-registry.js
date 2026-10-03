export class ProjectRegistry {
  constructor() { this.projects=new Map(); }
  register(project) {
    if (!project?.id) throw new Error("project id required");
    this.projects.set(project.id,{ enabled:true, lanes:["default"], ...project });
    return this.projects.get(project.id);
  }
  get(id) { return this.projects.get(id) ?? null; }
  eligible(capability) {
    return [...this.projects.values()].filter(p=>p.enabled && (!capability || (p.capabilities||[]).includes(capability)));
  }
}
