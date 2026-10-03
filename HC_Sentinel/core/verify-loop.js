export class VerifyLoop {
  constructor({ verify, evidence }) { this.verify=verify; this.evidence=evidence; }
  async run(context) {
    const before = await this.evidence("before", context);
    const result = await this.verify(context);
    const after = await this.evidence("after", { ...context, result });
    if (result?.uncertainEffect) return { status:"UNCERTAIN_EFFECT", before, after, result };
    if (result?.ok === true) return { status:"GREEN", before, after, result };
    if (result?.blocked) return { status:"BLOCKED", before, after, result };
    return { status:"REVIEW_REQUIRED", before, after, result };
  }
}
