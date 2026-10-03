export class EventTimeline {
  constructor() { this.items = []; }
  emit(type, data = {}) {
    const event = { id: this.items.length + 1, type, at: new Date().toISOString(), ...data };
    this.items.push(event);
    return event;
  }
  all() { return [...this.items]; }
}
