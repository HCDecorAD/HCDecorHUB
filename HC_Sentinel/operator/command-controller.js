import {parseOperatorCommand} from "./command-parser.js";
export class CommandController{
  constructor({projects,baselines,execute}){this.projects=projects;this.baselines=baselines;this.execute=execute;}
  async run(input){
    const command=parseOperatorCommand(input,this.projects);
    if(!command.projectId) return {status:"REVIEW_REQUIRED",reason:"PROJECT_NOT_RESOLVED",command};
    const baseline=this.baselines.resolve(command.projectId,command.viewport);
    if(command.action==="COMPARE"&&!baseline?.approved) return {status:"REVIEW_REQUIRED",reason:"BASELINE_NOT_APPROVED",command};
    return this.execute({command,baseline});
  }
}
