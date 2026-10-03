export class ReplayAdapter {
  async start(_target) { throw new Error("start() not implemented"); }
  async stop() { throw new Error("stop() not implemented"); }
}
