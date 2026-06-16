# Admin Task Workflow

## Required Workflow

1. Read `.agent/INDEX.md`.
2. Read `.agent/MEMORY.md`.
3. Read `.agent/tasks/current-task.md`.
4. Read `.agent/agents/SHARED-INSTRUCTIONS.md`.
5. Read only the relevant docs and skills.
6. Confirm repository facts needed for the task.
7. Inspect the exact files to change.
8. Make the smallest coherent change.
9. Add or update narrow tests where relevant.
10. Run narrow validation.
11. Update task notes.
12. Update memory only for stable project facts.
13. Update handover if work is incomplete.

## Safe Development Rules

- no unrelated refactors
- no broad rewrites
- no unrelated formatting changes
- no speculative abstractions
- mark `TBC` for unverified assumptions
- prefer existing patterns over new ones

## Task File Rules

- every implementation task must create or update `.agent/tasks/current-task.md`
- keep notes short, factual, and current
- when work finishes, archive the completed task into `.agent/tasks/completed/` if useful
