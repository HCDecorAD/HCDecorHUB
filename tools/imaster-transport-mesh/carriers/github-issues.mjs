const API="https://api.github.com";
export class GitHubIssuesCarrier{
 constructor({repo,token,label="imaster-envelope"}){this.id="github-issues";this.repo=repo;this.token=token;this.label=label}
 headers(){return {"accept":"application/vnd.github+json","authorization":`Bearer ${this.token}`,"x-github-api-version":"2022-11-28","content-type":"application/json"}}
 async probe(){if(!this.repo||!this.token)return {health:"unavailable",reason:"missing_repo_or_token"};try{const r=await fetch(`${API}/repos/${this.repo}`,{headers:this.headers(),signal:AbortSignal.timeout(5000)});return {health:r.ok?"healthy":"down",status:r.status}}catch(e){return {health:"down",error:e.message}}}
 async pollRequests(){
  const u=`${API}/repos/${this.repo}/issues?state=open&labels=${encodeURIComponent(this.label)}&per_page=20`;
  const r=await fetch(u,{headers:this.headers(),signal:AbortSignal.timeout(8000)});if(!r.ok)throw new Error("github_poll_"+r.status);
  const xs=await r.json();return xs.filter(x=>!x.pull_request).map(x=>{try{return {carrierId:x.number,envelope:JSON.parse(x.body)}}catch{return null}}).filter(Boolean)
 }
 async publishResult(carrierId,result){
  const u=`${API}/repos/${this.repo}/issues/${carrierId}/comments`;
  const r=await fetch(u,{method:"POST",headers:this.headers(),body:JSON.stringify({body:"IMASTER_RESULT\n"+JSON.stringify(result)}),signal:AbortSignal.timeout(8000)});
  if(!r.ok)throw new Error("github_result_"+r.status);return {ok:true,status:r.status}
 }
 async ack(carrierId){
  const u=`${API}/repos/${this.repo}/issues/${carrierId}`;
  const r=await fetch(u,{method:"PATCH",headers:this.headers(),body:JSON.stringify({state:"closed"}),signal:AbortSignal.timeout(8000)});
  if(!r.ok)throw new Error("github_ack_"+r.status);return {ok:true,status:r.status}
 }
}
