# Task: UI-003 compact table action sizing

Status: Completed

## Goal

Introduce a formal compact action-button size for dense table actions and route shared row actions through it so table controls stay lighter and more consistent.

## Background

`UI-TASKS.md` calls out oversized icon-only actions in categories, tags, and media. `UI-002` replaced those with compact text actions, but the compact sizing still lives as ad hoc classes inside `x-admin.row-action` instead of a shared button size or standard table-action API.

## Required Context

- `AGENTS.md`
- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/COMPONENT_SYSTEM.md`
- `UI-TASKS.md`

## Files To Inspect

- `resources/views/components/ui/button.blade.php`
- `resources/views/components/admin/row-action.blade.php`
- current row-action consumers on dense index tables

## Files To Change

- `.agent/tasks/current-task.md`
- `resources/views/components/ui/button.blade.php`
- `resources/views/components/admin/row-action.blade.php`

## Implementation Steps

1. Add a formal compact button size for dense management-table actions.
2. Update the shared admin row-action helper to use the formal compact size instead of custom sizing classes.
3. Keep destructive treatment lightweight while preserving distinction.
4. Run narrow validation.

## Acceptance Criteria

- [x] `x-ui.button` exposes a compact size suitable for dense table actions.
- [x] `x-admin.row-action` uses shared sizing instead of duplicated one-off height and padding classes.
- [x] Dense table action buttons remain visually lighter than standard `sm` buttons.
- [x] No unrelated button variants or non-table controls are changed.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- Changing shared button sizing must not affect existing `sm`, `md`, or `lg` consumers.

## Completion Notes

- Added an `xs` size to `x-ui.button` for dense action treatments.
- Updated `x-admin.row-action` to consume the shared `xs` size instead of duplicating compact spacing classes locally.
- Kept the destructive row-action treatment lightweight and limited to color treatment only.
- Validation passed with `php artisan test --filter=View` and `php artisan test --filter=Admin`.
