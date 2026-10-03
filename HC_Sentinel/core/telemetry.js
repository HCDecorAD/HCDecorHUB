export class TelemetryBus {
  constructor({ exporters=[] }={}) { this.exporters=exporters; this.events=[]; }
  emit(type, attrs={}) {
    const evt={ type, at:new Date().toISOString(), attrs };
    this.events.push(evt);
    for (const x of this.exporters) { try { x.export(evt); } catch {} }
    return evt;
  }
}
