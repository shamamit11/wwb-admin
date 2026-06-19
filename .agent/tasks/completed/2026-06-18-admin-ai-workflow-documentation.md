# Task: WB-ADMIN-AI-016 Add Admin AI workflow documentation

Status: Completed

## Goal

Document the implemented Admin AI workflow so future agents and maintainers understand the available modules, service API boundaries, workflow sequence, and current contract gaps.

## Background

- The Admin AI workflow slices for Topic Queue, Content Briefs, Prompt Templates, AI Jobs, dashboard cards, and feedback states are implemented.
- Existing core docs still contained placeholder-era wording for Topic Queue and AI Jobs.
- The suggested AI-specific docs did not yet exist in `docs/`.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `WB_ADMIN_AI_WORKFLOW_TASKS.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/PROJECT_SCOPE.md`
- `.agent/AGENT-HANDOVER.md`
- completed AI slice task notes in `.agent/tasks/completed/`

## Files To Inspect

- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `app/Services/WideWebBlogApi/Clients/ContentTopicClient.php`
- `app/Services/WideWebBlogApi/Clients/ContentBriefClient.php`
- `app/Services/WideWebBlogApi/Clients/AiPromptClient.php`
- `app/Services/WideWebBlogApi/Clients/AiJobClient.php`
- `app/Livewire/Admin/TopicQueue/Index.php`
- `app/Livewire/Admin/TopicQueue/Show.php`
- `app/Livewire/Admin/ContentBriefs/Show.php`
- `app/Livewire/Admin/AiPrompts/Show.php`
- `app/Livewire/Admin/AiJobs/Show.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `.agent/MEMORY.md`
- `.agent/AGENT-HANDOVER.md`
- `docs/AI_WORKFLOW_ADMIN.md`
- `docs/TOPIC_QUEUE_ADMIN.md`
- `docs/AI_JOBS_ADMIN.md`
- `docs/PROMPT_TEMPLATES_ADMIN.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/PROJECT_SCOPE.md`

## Implementation Steps

1. Create AI workflow overview documentation covering module boundaries and workflow sequence.
2. Create focused module docs for Topic Queue, AI Jobs, and Prompt Templates.
3. Align core docs that still describe AI modules as placeholders.
4. Update reusable agent memory and handover notes with the new documentation entry points.
5. Run narrow validation on the changed markdown docs.

## Acceptance Criteria

- Documentation explains the Admin AI workflow clearly.
- Documentation states that Admin consumes Service APIs.
- Documentation states that Admin must not call AI providers directly.
- Documentation states that publishing remains manual.
- Documentation explains current contract gaps such as draft review provenance.

## Validation Commands

- `git diff --check`

## Risks

- Some older docs outside the edited set may still contain stale wording if they were not AI-specific enough to touch in this slice.

## Completion Notes

- Added new AI workflow docs:
  - `docs/AI_WORKFLOW_ADMIN.md`
  - `docs/TOPIC_QUEUE_ADMIN.md`
  - `docs/AI_JOBS_ADMIN.md`
  - `docs/PROMPT_TEMPLATES_ADMIN.md`
- Updated `docs/API_INTEGRATION.md` with AI workflow endpoint coverage and client guidance.
- Updated `docs/UI_UX_GUIDELINES.md` and `docs/PROJECT_SCOPE.md` to remove stale placeholder-era AI wording.
- Updated `.agent/MEMORY.md` and `.agent/AGENT-HANDOVER.md` so future agents load the new AI docs first and understand the remaining Draft Review contract gap.
- Validation passed:
  - `git diff --check`
