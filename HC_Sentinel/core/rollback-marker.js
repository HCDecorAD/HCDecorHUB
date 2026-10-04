export class RollbackMarker{
  constructor(store){this.store=store;}
  async set(marker){if(!marker?.version||!marker?.commit)throw new Error("ROLLBACK_MARKER_REQUIRED");return this.store.write({...marker,createdAt:new Date().toISOString()});}
  async get(){return this.store.read(null);}
}
