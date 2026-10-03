export class OpenTelemetryExporter {
  constructor({ sink=null }={}) { this.sink=sink; }
  export(event) {
    if (this.sink) this.sink(event);
    return event;
  }
}
