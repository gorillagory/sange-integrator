# Workflow SOP (Standard)

Last updated: 2026-05-18

## Purpose

This is the mandatory operating model for User <-> Codex execution work in this repository.

Goals:
1. Reduce context loss.
2. Reduce execution mistakes.
3. Keep a traceable history of what was planned, executed, verified, and pending.

## Interaction Flow

1. User gives objective or phase command.
2. Codex creates one checklist file in `.knowledge/work/working`:
   - format: `(taskname)-(tasknumber)-checklist.md`
3. Codex executes checklist items in order.
4. Codex updates checklist item status progressively (`[ ]` -> `[x]`).
5. Codex runs verification relevant to the scope.
6. Codex records blockers explicitly (if any).
7. Codex marks checklist header status as `completed`.
8. Codex moves checklist file to `.knowledge/work/completed`.
9. Codex renames the completed artifact to `NN-History-<taskname>.md` so history sorts oldest (`01`) to latest.
10. Codex updates knowledge docs if architecture/process status changed.

No implementation phase is considered closed unless steps 7 through 9 are done.

## Checklist Standard

Every checklist must include:
1. Header:
   - title
   - `Status`
   - `Owner`
   - `Created` date
2. Task list with concrete verifiable outcomes.
3. Verification notes when runtime/testing is attempted.
4. Explicit blocker notes when execution is environment-limited.

## Verification Standard

Minimum:
1. Syntax check for changed PHP files.
2. Targeted tests for new services/logic.
3. Runtime checks in Sail for framework-dependent commands.

If any verification cannot run:
1. State exact reason.
2. State exact remaining action needed.
3. Keep that action in docs as pending.

## Documentation Update Standard

When significant work is completed, Codex must update:
1. `.knowledge/subtask/solution-draft.md` (execution status section)
2. `.knowledge/workflow-history.md` (history map)
3. `.knowledge/README.md` contents list (if new standard docs were added)

## Branch/Execution Hygiene

1. Keep changes scoped to checklist objective.
2. Do not silently change workflow rules; update this SOP if process changes.
3. Avoid mixed-phase execution without an explicit checklist entry.

## Definition of Done (Workflow)

A workflow cycle is done only when:
1. Checklist is fully checked.
2. Checklist status is `completed`.
3. Checklist file is moved to `completed`.
4. Validation results are recorded.
5. Remaining blockers are explicitly documented.
