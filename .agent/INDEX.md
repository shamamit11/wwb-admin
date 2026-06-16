# Admin Agent Index

This is the only file an agent should read first.

## Purpose

This `.agent` directory keeps agent context small, task-driven, and consistent for Codex, Claude, and similar coding agents working in `widewebblog/admin`.

## Fast Start

1. Read `.agent/MEMORY.md`.
2. Read `.agent/tasks/current-task.md`.
3. Read `.agent/agents/SHARED-INSTRUCTIONS.md`.
4. Read only the additional files required for the active task.
5. Inspect only the exact repository files needed for implementation or validation.

## Core Rules

1. Work from `widewebblog/admin` unless the task explicitly requires sibling context.
2. Do not scan the whole repository by default.
3. Load only the minimum files needed for the active task.
4. Prefer `.agent` docs and task files before reading broader repository files.
5. If sibling context is needed from `../service` or `../fe`, read the smallest relevant doc first.
6. Record why sibling context was needed in `.agent/tasks/current-task.md`.
7. Do not change sibling app files unless the task explicitly requests it.
8. Always create or update `.agent/tasks/current-task.md` before implementation work.
9. Update `.agent/MEMORY.md` only for stable knowledge likely to help future tasks.
10. Update `.agent/AGENT-HANDOVER.md` when work is incomplete, risky, or another agent may continue.

## Always-Read Files

- `.agent/MEMORY.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`

## Read-When-Relevant Files

- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/FOLDER-STRUCTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/KNOWLEDGE-BASE.md`
- `.agent/skills/*.md`

## Skill Selection Guide

For Laravel + Livewire page work:

- `.agent/ARCHITECTURE.md`
- `.agent/FOLDER-STRUCTURE.md`
- `.agent/skills/laravel-livewire.md`

For Blade or shared UI work:

- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`

For API integration, auth, or service-driven workflows:

- `.agent/API-CONTRACT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/auth-session.md`

For forms, tables, uploads, posts, or SEO screens:

- `.agent/skills/forms-validation.md`
- `.agent/skills/tables-filters-pagination.md`
- `.agent/skills/media-upload.md`
- `.agent/skills/post-editor.md`
- `.agent/skills/seo-admin.md`

For validation work:

- `.agent/TESTING.md`
- `.agent/skills/testing.md`

## Task Workflow Summary

1. Open the current task file.
2. Load only the relevant context docs and skills.
3. Confirm repository facts before assuming commands, folders, or tooling exist.
4. Make the smallest coherent change.
5. Run narrow validation.
6. Update task notes, handover notes, and memory only where appropriate.

## Memory Update Rules

Only add facts to `.agent/MEMORY.md` when they are stable, reusable, and likely to stay true across multiple tasks.

Do not store:

- temporary task notes
- speculative decisions
- one-off debugging details
- unverified assumptions

## Handover Rules

- update `.agent/AGENT-HANDOVER.md` when work stops incomplete
- record blockers, risks, changed files, and remaining validation gaps
- keep handovers short and factual

## Warning

Do not perform broad repository scans by default. Read only the files needed for the current task, and prefer `.agent` guidance over exploratory searching.
