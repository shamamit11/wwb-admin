# Task: Template UI Refinement

Status: Completed

## Goal

Refine the template index toolbar layout and increase the create/edit drawer width.

## Background

The template module is now functional, but the list toolbar stacks the type and status selects vertically in a way that feels unfinished. The template editor also needs a wider drawer so the structured block form has more breathing room.

## Required Context

- `AGENTS.md`
- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/UI-UX-RULES.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `resources/views/livewire/admin/templates/index.blade.php`
- `resources/views/components/admin/filter-bar.blade.php`
- `resources/views/components/ui/drawer.blade.php`

## Files To Change

- `resources/views/livewire/admin/templates/index.blade.php`
- `resources/views/components/ui/drawer.blade.php`

## Implementation Steps

- Keep the template type and status filters on a single horizontal row in the toolbar.
- Increase the template drawer width using a shared drawer size that is still consistent with the admin UI system.
- Validate the affected Blade views.

## Acceptance Criteria

- template type and status selects render on a single row in the toolbar
- template create/edit drawer opens wider than the previous `lg` size

## Validation Commands

- `php artisan test tests/Feature/Templates/TemplateIndexTest.php`

## Risks

- Shared drawer width changes must not affect existing screens unless they opt into the new size.

## Completion Notes

- Template filters now stay on one horizontal row using explicit select widths inside the filter slot.
- Added an `xl` drawer width option to the shared drawer component.
- The template create/edit drawer now uses `width="xl"` for a roomier editing experience.
