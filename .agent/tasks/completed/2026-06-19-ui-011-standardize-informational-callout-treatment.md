# Task: UI-011 Standardize informational callout treatment

Status: Completed

## Goal

Add one reusable informational or caution callout pattern and replace ad hoc bordered notice panels on scoped admin screens.

## Background

`UI-TASKS.md` identifies non-error informational notices that are currently rendered as one-off rounded panels. Recent UI passes already standardized actions and fallback copy, so this task should focus on explanatory notice treatment only.

## Required Context

- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/UI_UX_GUIDELINES.md`
- `UI-TASKS.md`

## Files To Inspect

- `resources/views/livewire/admin/settings/index.blade.php`
- `resources/views/livewire/admin/seo/index.blade.php`
- `resources/views/livewire/admin/knowledge-base/editor.blade.php`
- `resources/views/livewire/admin/topic-queue/index.blade.php`
- any existing shared alert or callout component that could be reused

## Files To Change

- `.agent/tasks/current-task.md`
- shared Blade component(s) needed for informational callouts
- scoped admin views that currently use ad hoc informational panels

## Implementation Steps

1. Inspect the scoped notice patterns and confirm whether a shared alert primitive already fits.
2. Create or extend a reusable informational callout pattern with consistent spacing, border, and tone treatment.
3. Replace scoped ad hoc informational notices with the shared pattern without changing unrelated error states or confirmations.
4. Run narrow validation.

## Acceptance Criteria

- Informational and caution-style explanatory notices in scope use one shared treatment.
- Error banners and confirmation dialogs remain unchanged.
- No unrelated page layout or workflow behavior changes are introduced.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- Some bordered panels may be structural containers rather than notices, so the shared callout should only replace panels whose main purpose is product explanation.

## Completion Notes

- Added a shared `x-admin.callout` component for explanatory info and warning notices with a consistent icon, border, spacing, and title treatment.
- Migrated scoped informational and caution notices in:
  - `settings/index.blade.php`
  - `seo/index.blade.php`
  - `knowledge-base/editor.blade.php`
  - `topic-queue/index.blade.php`
- Left error banners, empty states, and structural cards unchanged so the new callout remains scoped to product guidance and boundary messaging.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
