const DEFAULTS={
  theme:"dark",
  watchEnabled:true,
  notifyMedium:true,
  autoRepairHigh:true,
  autoRepairMedium:false,
  releaseBlockCritical:true
};
export class SettingsStore{
  constructor(store){this.store=store;}
  async get(){return {...DEFAULTS,...await this.store.read({})};}
  async patch(changes){
    const allowed=Object.keys(DEFAULTS);
    const safe=Object.fromEntries(Object.entries(changes??{}).filter(([k])=>allowed.includes(k)));
    return this.store.update(x=>({...DEFAULTS,...x,...safe}),{});
  }
}
