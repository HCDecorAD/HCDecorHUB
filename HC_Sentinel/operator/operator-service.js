import { parseOperatorCommand } from "./command-parser.js";
export class OperatorService {
  constructor({projects, baselines, router, bridge}){this.projects=projects;this.baselines=baselines;this.router=router;this.bridge=bridge;}
  plan(input){
    const command=parseOperatorCommand(input,this.projects);
    if(!command.projectId) return {status:"REVIEW_REQUIRED",reason:"PROJECT_NOT_RESOLVED",command};
    const project=this.projects.get(command.projectId);
    const baseline=this.baselines.resolve(command.projectId,command.viewport);
    return {status:"READY",command,project,baseline};
  }
}
