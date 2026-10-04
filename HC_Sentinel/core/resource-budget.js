export function checkResourceBudget({cpuPercent=0,memoryPercent=0,queueDepth=0},{maxCpu=85,maxMemory=85,maxQueue=100}={}){
  const reasons=[];if(cpuPercent>maxCpu)reasons.push("CPU");if(memoryPercent>maxMemory)reasons.push("MEMORY");if(queueDepth>maxQueue)reasons.push("QUEUE");
  return {ok:reasons.length===0,status:reasons.length?"THROTTLE":"OK",reasons};
}
