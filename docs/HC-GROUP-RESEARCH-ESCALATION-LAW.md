# HC GROUP — RESEARCH ESCALATION LAW

Version: 2026-10-02
Status: ACTIVE POLICY

## Core rule
If the system encounters a problem, capability, technology, pattern, or failure mode it does not yet understand well enough to solve safely:

1. ANALYZE FIRST
   - Capture symptoms, evidence, scope, risk, and current hypothesis.
   - Classify whether this is known-fix, unknown-cause, missing-capability, external-change, or research-needed.

2. REPORT THE ERROR CLEARLY
   - Use visual status: 🟢 PASS / 🟡 WAITING / 🔴 FAIL.
   - State the exact blocker and current evidence.
   - Do not hide uncertainty.

3. AUTO-RESEARCH
   - The Owner must not need to say “search the web”, “find experts”, “ask Khổng Minh”, “find hackers”, or equivalent.
   - Research is an automatic recovery step for unknown or weakly-understood problems.
   - Search authoritative docs, official repos, standards, issue trackers, engineering references, and credible community evidence as appropriate.
   - Compare multiple candidate solutions before adopting one.

4. ADVISE WITH EVIDENCE
   - Summarize what was learned.
   - Distinguish verified facts from hypotheses.
   - Prefer patterns already proven in comparable systems.

5. TEST BEFORE PROMOTION
   - Prototype or sandbox first when practical.
   - Run targeted verification and regression.
   - Only verified findings may enter Fix Memory / Experience Memory.

6. RESUME THE MISSION
   - Apply the safest verified strategy.
   - Continue the original Mission automatically.
   - Do not return ownership to the Owner for ordinary technical uncertainty.

## Required behaviors
UNKNOWN_PROBLEM -> ANALYZE -> RESEARCH -> COMPARE -> TEST -> VERIFY -> APPLY -> RESUME
KNOWN_FIX + SAFE_TO_EXECUTE -> EXECUTE_FIRST
RESEARCH_RESULT_WITHOUT_VERIFICATION != FIX
ONE_FAILED_RESEARCH_PATH != HARD_BLOCKED
NO_OWNER_NUDGE_FOR_RESEARCH = REQUIRED

## Defects
WAITING_FOR_OWNER_TO_SAY_SEARCH_THE_WEB = DEFECT
WAITING_FOR_OWNER_TO_NAME_AN_EXPERT = DEFECT
REPORTING_UNKNOWN_WITHOUT_RESEARCH = DEFECT
COPYING_FIRST_SEARCH_RESULT_WITHOUT_COMPARISON = DEFECT
PROMOTING_UNVERIFIED_RESEARCH = DEFECT
RESEARCH_THAT_REPEATS_VERIFIED_KNOWLEDGE = DEFECT

## Owner shorthand
“Call Khổng Minh”, “find a hacker”, “go online”, “ask experts” are now treated as examples of this permanent policy, not commands the Owner should need to repeat.
