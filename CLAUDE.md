<!-- AGENTFLOW_MANAGED_CLAUDE:BEGIN -->
# AgentFlow — Claude Code

> Token-efficient, two-stage planning + implementation workflow with optional Jira integration.
> Config: `workflow.config.json` · Designs: `designs/` · Skills: `.claude/skills/`

---

## 🪨 Caveman mode — ON
Drop articles (a/an/the), filler words, pleasantries, and hedging. Fragments OK. Short synonyms. Code, function names, error strings: never abbreviate.
Off only when user says: "stop caveman" / "normal mode".
Level: **full** (off | lite | full | ultra) — change in `workflow.config.json`.

---

## 🔧 rtk — ON
Prefix ALL shell commands with `rtk` to compress output before it hits context.
```
rtk git status        rtk ls .           rtk grep "X" .
rtk read file.ts      rtk npm test       rtk tsc
rtk jest              rtk deps           rtk diff a b
```
Aggressive mode for large files: `rtk read file.ts -l aggressive`

---

## 🧠 Headroom — ON
Headroom runs as a local proxy and compresses tool outputs, files, logs, and history before they reach the model — automatic, no action needed. When you need the exact, uncompressed original of something that was compressed, call the `headroom_retrieve` MCP tool instead of re-reading the file. Run `headroom learn` to mine failed sessions into corrections appended here.
- Runs as a local proxy on port **8787**; the orchestrator launches Claude through `headroom wrap claude` so compression is automatic.
- Output token shaping: **ON** (trims what the model writes back).
- Stack order: rtk (shell output) → Caveman (your prose) → Headroom (everything sent to the model). They compose; do not disable one for another.
- Need the exact original of something Headroom compressed? Call the `headroom_retrieve` MCP tool — do not re-run the command just to see full output.
- Config lives in `workflow.config.json` → `tokenOptimisation.headroom`.

---

## Workflow

### Stage 1 — Planning (`/stage1`)
Use skill: `/stage1 <requirements-file>`
- Reads requirement doc → explores codebase → splits into testable tasks
- Creates Jira issues via MCP when enabled, or local IDs when disabled → writes `designs/<ID>.md` for each
- Pauses for human review before any code is written
- Model: **high-tier** (set in `workflow.config.json` → `models.high`)

### Stage 2 — Implementation (`/stage2`)
Use skill: `/stage2 <JIRA-ID>`
- Reads Jira issue + design doc when enabled, or the design doc alone when disabled → implements → unit tests → code review → browser test cases + execution report → fix → branch-ready handoff
- Each step uses the appropriate model tier (see config)
- Model per step: read/implement/fix = **low**, review = **high**, test = **mid**
- Testing defaults from `workflow.config.json` → `testing` section:
  - `testing.unitTests.enabled` — generate & run unit tests (default: **ON**)
  - `testing.testCases.enabled` — generate browser test cases & run automated testing (default: **ON**)
- Manual per-issue overrides in the prompt **always override** project config:
  - "skip unit tests" → disable for this issue
  - "skip test cases" / "skip browser testing" → disable for this issue
  - "run unit tests" / "run browser tests" → enable for this issue
- If user says nothing about testing, project config values apply

---

## Model config
All model choices live in `workflow.config.json`.
Do NOT hardcode model names in agent prompts.
To change models mid-session, user can say:
- "Use opus for the review step"
- "Switch planner to gemini-2.5-pro"
- "Use haiku for everything"
Claude will acknowledge and apply for remainder of session.

---

## Jira
- Enabled: **false**
- Project key: **NONE**
- If enabled, MCP is configured in `.claude/settings.json`
- Status flow comes from `workflow.config.json`:
  - backlog: `jira.stages.todo`
  - active work: `jira.stages.inProgress`
- Update status at:
  - start of Step 3 → `jira.stages.inProgress`

---

## Design docs
- Location: `designs/<JIRA-ID>.md`
- Progress checkpoints: `designs/<JIRA-ID>.progress.md`
- Browser test cases: `test-cases/<JIRA-ID>.md`
- Browser execution reports: `test-reports/<JIRA-ID>.browser.md`
- Read design doc BEFORE implementing, always
- If doc missing: ask user to run Stage 1 first

---

## Git
- Branch: `NONE-{n}-{slug}` e.g. `proj-123-add-oauth`
- Commit: Conventional Commits ≤50 chars, WHY not WHAT
- Stage 2 agent does not create PRs or push branches directly; outer orchestrator handles approved check-in

---

## Context window management
When approaching context limit during any pipeline step:
1. Write checkpoint → `designs/<JIRA-ID>.progress.md`
   - Use YAML frontmatter with required fields like `stage`, `status`, `completed_steps`, `next_step`, `next_action`, and any stage-specific fields
2. Tell user: "Context limit near. Checkpoint in `designs/<ID>.progress.md`. If outer orchestrator is running, it should auto-resume. Manual fallback: start new session, run `/resume <ID>`." Then print `AGENTFLOW_CHECKPOINT` on its own line so the orchestrator detects the checkpoint immediately.
3. On `/resume <ID>`: read progress file first, then continue from checkpoint step

---

## Available skills
| Skill | Trigger | Purpose |
|---|---|---|
| `/stage1 <file>` | Stage 1 planning | Full planning pipeline |
| `/stage2 <ID>` | Stage 2 implementation | Full build pipeline |
| `/resume <ID>` | Resume from checkpoint | Continue after context reset |
| `/caveman [lite\|full\|ultra]` | Toggle caveman mode | Change compression level |
| `/models` | Show model config | Print current tier assignments |
<!-- AGENTFLOW_MANAGED_CLAUDE:END -->
