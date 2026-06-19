# Task: UI-012 Improve Topic Queue index layout and control alignment

Status: Completed

## Goal

Do a dedicated layout pass on the Topic Queue index so header actions, filters, table rhythm, and pagination feel aligned with the now-standardized admin primitives.

## Background

`UI-TASKS.md` calls out Topic Queue as a concentrated example of alignment drift. The shared header, toolbar, pagination, action-label, fallback-copy, and callout systems are now standardized, so this pass should focus on page composition rather than inventing new primitives.

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

- `resources/views/livewire/admin/topic-queue/index.blade.php`
- shared components used directly by the page: page header, filter bar, pagination, table heading, row actions, callout

## Files To Change

- `.agent/tasks/current-task.md`
- `resources/views/livewire/admin/topic-queue/index.blade.php`
- only shared component files if a small adjustment is required to support the page cleanly

## Implementation Steps

1. Inspect the Topic Queue index against the current shared UI primitives.
2. Tighten header, CTA, filter, and pagination composition while preserving behavior.
3. Make the smallest supporting shared-component adjustment only if the page cannot align cleanly otherwise.
4. Run narrow validation.

## Acceptance Criteria

- Topic Queue header composition, control alignment, and spacing feel consistent with the shared admin system.
- Filters and pagination look appropriately weighted relative to the table.
- Existing sort, filter, dialog, and navigation behavior remain unchanged.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- A layout-only pass can drift into primitive changes if the page is used to compensate for component limitations, so shared changes should stay minimal and justified.

## Completion Notes

- Tightened Topic Queue header composition by adding an eyebrow and pairing the primary CTA with a short supporting line in the page-header action area.
- Smoothed page rhythm by changing the stats grid to a more flexible responsive layout and adding a lightweight review-flow callout between the summary cards and filters.
- Reworked the filter controls into a responsive grid so the three selects align more evenly across breakpoints.
- Reduced pagination visual weight on this page by using the shared pagination component with a quieter page-local wrapper treatment instead of changing the shared primitive.
- No shared component changes were required for this pass.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
