# Task: Refine Legal Pages Shortcut Section UI

Status: Completed

## Goal

Refine the Legal Pages shortcut section on the Pages admin screen so it feels more compact, practical, and secondary to the main page actions without changing backend behavior.

## Background

The Pages screen already works functionally, but the Legal Pages utility block feels oversized and overly button-heavy for a compact admin shortcut surface.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/ARCHITECTURE.md`
- `.agent/FOLDER-STRUCTURE.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `resources/views/livewire/admin/pages/index.blade.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `resources/views/livewire/admin/pages/index.blade.php`

## Implementation Steps

1. Tighten the Legal Pages section layout, spacing, and card density.
2. Move the legal filter actions inline in the header and reduce their visual weight.
3. Simplify the legal shortcut cards while preserving status and actions.
4. Run narrow validation for the Pages screen.
5. Update task notes with validation and residual risk.

## Acceptance Criteria

- The Legal Pages section feels more compact and admin-friendly.
- Filter buttons sit inline in the header and read as secondary controls.
- Legal shortcut cards are smaller and easier to scan without losing status or actions.
- Existing Pages routes, actions, and filtering behavior continue to work.

## Validation Commands

- `php artisan test --filter=PageIndexTest`
- `php artisan view:cache`

## Risks

- The legal shortcut cards still depend on the page summary data currently exposed by the service, so this pass should stay presentational only.

## Completion Notes

- Reduced the Legal Pages section padding and internal spacing so it reads as a compact admin utility block instead of a large content section.
- Moved `Show Legal Only` and `Show All Pages` into the header area as small secondary controls and removed primary-orange competition with `Create Page`.
- Tightened the legal shortcut cards, kept the two-card desktop layout, and simplified each card to title, status, slug, one summary line, and a small item action.
- Preserved all existing filtering, edit/create actions, routes, and data bindings.
- Validation passed with `php artisan test --filter=PageIndexTest` and `php artisan view:cache`.
