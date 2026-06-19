# Task: UI-013 Standardize list-to-detail entry patterns

Status: Completed

## Goal

Define and apply consistent rules for when list rows lead to review, details, or edit destinations, and ensure the action text matches the destination purpose.

## Background

`UI-TASKS.md` notes that admin list screens mix `Review`, `Open`, `Inspect`, and `Edit` for next-step entry actions. UI-009 already normalized vocabulary, but this task needs to confirm the route semantics themselves and align the visible labels with those semantics.

## Required Context

- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `.agent/skills/tables-filters-pagination.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/UI_UX_GUIDELINES.md`
- `UI-TASKS.md`
- completed UI task notes for related standardization passes

## Files To Inspect

- list screens called out by `UI-TASKS.md`:
  - `topic-queue/index.blade.php`
  - `content-briefs/index.blade.php`
  - `ai-jobs/index.blade.php`
  - `pages/index.blade.php`
  - `knowledge-base/index.blade.php`
- related destination views or route targets for those row actions

## Files To Change

- `.agent/tasks/current-task.md`
- only the scoped list views and lightweight guidance needed to standardize entry patterns

## Implementation Steps

1. Inspect current row-action destinations and the purpose of each target screen.
2. Define the minimal rule for `Review` vs `Details` vs `Edit` based on destination behavior.
3. Update scoped list actions to match the destination semantics without changing unrelated actions.
4. Run narrow validation.

## Acceptance Criteria

- Scoped list-row actions use labels that match the purpose of the target screen.
- Similar destination types use the same entry pattern in scope.
- No unrelated route, layout, or workflow behavior changes are introduced.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- Some screens combine review and edit behavior on the same destination, so the chosen label must reflect the dominant user task rather than perfect purity.

## Completion Notes

- Reviewed the scoped list screens and their destination views:
  - Topic Queue and Content Briefs already route to review-first screens, so `Review` remains correct.
  - AI Jobs already routes to a read-only inspection screen, so `Details` remains correct.
  - Pages and Knowledge Base already route to mutable editors, so `Edit` remains correct.
- Added explicit shared guidance to `.agent/UI-UX-RULES.md` for choosing `Review`, `Details`, or `Edit` based on the dominant task of the destination screen.
- No scoped list-view code changes were required because the current row-action labels already match the destination semantics after the earlier UI-009 pass.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
