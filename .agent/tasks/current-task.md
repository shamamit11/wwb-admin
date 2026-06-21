# Task: Improve AI Prompt Template Detail/Edit Page UI/UX

Status: Completed

## Goal

Improve the AI Prompt Template detail/edit screen so admins can safely inspect active prompts, edit future prompt versions, and understand workflow state without changing backend logic, routes, or removing existing fields.

## Background

The current prompt template screen is functional but reads as a long technical form. Active prompts are too expanded by default, version editing feels heavy, and the future-version workflow is not visually clear enough for an operational prompt management surface.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/TESTING.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `.agent/skills/testing.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `app/Livewire/Admin/AiPrompts/Show.php`
- `resources/views/livewire/admin/ai-prompts/show.blade.php`
- `tests/Feature/AiPrompts/AiPromptScreensTest.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `app/Livewire/Admin/AiPrompts/Show.php`
- `resources/views/livewire/admin/ai-prompts/show.blade.php`
- `tests/Feature/AiPrompts/AiPromptScreensTest.php` if focused regression coverage is needed

## Implementation Steps

1. Add minimal presenter helpers for workflow summaries, prompt previews, variable summaries, and version history readability.
2. Rework the screen layout so metadata, workflow state, active version inspection, and new-version editing are easier to scan.
3. Make long prompt and schema blocks summary-first or collapsible with simple copy actions, without changing form bindings or version actions.
4. Preserve existing metadata save, new version creation, and version activation flows.
5. Run narrow validation and record residual risk.

## Acceptance Criteria

- Active version prompt blocks no longer dominate the sidebar by default.
- The create/edit version form has clearer hierarchy and technical editor affordance.
- The future-version workflow is visually explained using existing status/version data only.
- Version history is easier to scan while preserving existing activation behavior.
- Existing bindings, fields, and actions remain intact.

## Validation Commands

- `php -l app/Livewire/Admin/AiPrompts/Show.php`
- `php artisan test --filter=AiPromptScreensTest`
- `php artisan view:cache`

## Risks

- Prompt preview summaries must stay generic enough not to hide meaningful technical content or misrepresent the underlying prompt data.
- Client-side collapsible behavior should remain lightweight and avoid introducing heavy editor dependencies or Livewire state churn.

## Completion Notes

- Added presenter helpers for workflow strips, active prompt previews, variable summaries, schema summaries, and compact version history data without changing API behavior or Livewire form contracts.
- Reworked the detail/edit screen so prompt lifecycle context appears before the heavy editor fields, and new-version editing is grouped into `Version Settings`, `Prompt Content`, and `Output Contract`.
- Applied technical-editor affordances to prompt and schema fields with monospace styling, taller readable textareas, and clearer helper text.
- Converted active-version prompt and schema previews into collapsible summary-first cards with simple `Copy` and expand/collapse actions.
- Tightened version history into more scannable cards with summarized variables and expandable schema JSON while preserving the existing activate action.
- Validation passed with `php -l app/Livewire/Admin/AiPrompts/Show.php`, `php artisan test --filter=AiPromptScreensTest`, and `php artisan view:cache`.
