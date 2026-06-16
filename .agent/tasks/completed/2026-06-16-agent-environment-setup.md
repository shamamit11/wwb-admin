# Task: Set Up Shared Agent Environment For Wide Web Blog Admin Panel

Status: Completed

## Goal

Create a shared `.agent/` environment for the admin repository so future agents can work with minimal context, stable project memory, reusable skills, and consistent task workflow rules.

## Background

The admin repository already had project documentation under `docs/` but no agent-specific scaffold. The new environment needed to mirror the service repository’s task-driven operating model while staying admin-specific.

## Required Context

- task brief attachment
- `docs/PROJECT_SCOPE.md`
- `docs/ARCHITECTURE.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/FOLDER_STRUCTURE.md`
- `docs/AUTHENTICATION.md`
- `docs/COMMANDS.md`
- `docs/TESTING.md`
- `docs/IMPLEMENTATION_ROADMAP.md`
- `/Users/amitsharma/Herd/widewebblog/service/AGENTS.md`
- `/Users/amitsharma/Herd/widewebblog/service/.agent/INDEX.md`
- `/Users/amitsharma/Herd/widewebblog/service/.agent/agents/SHARED-INSTRUCTIONS.md`
- `/Users/amitsharma/Herd/widewebblog/service/.agent/MEMORY.md`
- `/Users/amitsharma/Herd/widewebblog/service/.agent/tasks/task-template.md`

## Files To Inspect

- `docs/`
- `wwb.admin.code-workspace`

## Files To Change

- `AGENTS.md`
- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/FOLDER-STRUCTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/AGENT-HANDOVER.md`
- `.agent/KNOWLEDGE-BASE.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/agents/CLAUDE.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/auth-session.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/tables-filters-pagination.md`
- `.agent/skills/media-upload.md`
- `.agent/skills/post-editor.md`
- `.agent/skills/seo-admin.md`
- `.agent/skills/testing.md`
- `.agent/tasks/README.md`
- `.agent/tasks/current-task.md`
- `.agent/tasks/task-template.md`
- `.agent/tasks/completed/README.md`

## Implementation Steps

1. Reviewed the admin documentation set and checked for any existing agent files.
2. Inspected the service repository’s `.agent` structure to reuse its operating model.
3. Created the admin root `AGENTS.md`, core `.agent` docs, agent-specific instructions, skills, knowledge base, and task workflow files.
4. Archived this completed task and reset `current-task.md` to the idle template state.

## Acceptance Criteria

- required root and `.agent/` files exist
- guidance is admin-specific and aligned with the service API/OpenAPI contract and admin UX spec
- task workflow discourages broad repository scans and unrelated refactors
- reusable skills cover the expected implementation areas

## Validation Commands

- `find '.agent' -maxdepth 3 -type f | sort`
- `sed -n '1,80p' AGENTS.md`
- `sed -n '1,80p' .agent/INDEX.md`
- `sed -n '1,80p' .agent/MEMORY.md`
- `sed -n '1,80p' .agent/tasks/current-task.md`
- `sed -n '1,80p' .agent/tasks/task-template.md`
- `sed -n '1,80p' .agent/agents/SHARED-INSTRUCTIONS.md`

Result: scaffold and key file contents verified locally.

## Risks

- command guidance is intentionally example-only until the Laravel app and its actual scripts exist in this repository
- future bootstrap work may require updating folder and command references from `TBC` to concrete values

## Completion Notes

Created the shared agent environment and aligned it with the existing admin docs plus the service repository’s agent workflow pattern. No application features were implemented.
