# Task: UI-009 Standardize action-label vocabulary

Status: Completed

## Goal

Standardize action labels across admin list and detail screens so similar actions use consistent product language.

## Background

`UI-TASKS.md` calls out inconsistent action wording across AI workflow, SEO, and related admin screens. Recent UI passes already standardized row-action structure and confirmation dialogs, so this task should focus on product copy only.

## Required Context

- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/UI_UX_GUIDELINES.md`
- `UI-TASKS.md`
- completed UI task notes for related standardization passes

## Files To Inspect

- `resources/views/components/admin/row-action.blade.php`
- `resources/views/components/admin/row-actions.blade.php`
- admin Blade views called out in `UI-TASKS.md` for label inconsistency

## Files To Change

- `.agent/tasks/current-task.md`
- only the admin Blade views and shared docs/components needed to align label vocabulary

## Implementation Steps

1. Inspect current labels in the scoped admin views and identify the minimum shared vocabulary rules needed.
2. Standardize list-row, back-navigation, and workflow-trigger labels in scope without changing unrelated behavior.
3. Add or update lightweight guidance only if the vocabulary rule should be reused by future tasks.
4. Run narrow validation.

## Acceptance Criteria

- Similar destinations use consistent verbs in scope.
- Back-navigation labels follow one pattern in scope.
- Workflow-trigger labels use clear, non-arbitrary verbs in scope.
- No unrelated layout or interaction changes are introduced.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- Some label differences may reflect genuinely different destinations, so semantics must be preserved while reducing drift.

## Completion Notes

- Standardized scoped list-row labels on AI workflow screens to `Review`, `Edit`, and `Details` based on destination semantics.
- Standardized scoped back-navigation labels to the `Back to {resource name}` pattern.
- Replaced implementation-facing workflow labels like `Create AI Job` with user-facing verbs such as `Run Topic Discovery` and `Generate Draft`.
- Added reusable action-label vocabulary guidance to `.agent/UI-UX-RULES.md`.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
