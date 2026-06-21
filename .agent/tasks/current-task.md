# Task: Improve Posts Admin Page Table Layout

Status: Completed

## Goal

Improve the Posts admin page table layout and readability so editors can scan posts comfortably without clipping or crowding, while keeping existing behavior intact.

## Background

The Posts screen works functionally, but the primary content column feels clipped and the row hierarchy is too dense for comfortable editorial scanning.

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

- `app/Livewire/Admin/Posts/Index.php`
- `resources/views/livewire/admin/posts/index.blade.php`
- `resources/views/components/ui/table.blade.php`
- `resources/views/components/ui/table-cell.blade.php`
- `resources/views/components/ui/table-row.blade.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `resources/views/livewire/admin/posts/index.blade.php`

## Implementation Steps

1. Fix the Posts table first-column layout and increase primary content clarity.
2. Improve row spacing, metadata hierarchy, and action-column stability on the Posts screen.
3. Refine filter-bar spacing where needed without redesigning it.
4. Run narrow validation for the Posts screen.
5. Update task notes with validation and residual risk.

## Acceptance Criteria

- The post title/content column is no longer clipped.
- Posts rows feel more readable and less crowded.
- Metadata columns are quieter and the action column remains stable.
- Existing Posts routes, actions, filters, and action menus continue to work.

## Validation Commands

- `php artisan test --filter=PostIndexTest`
- `php artisan view:cache`

## Risks

- The table still needs to stay dense enough for editorial inventory management, so spacing changes should remain moderate and presentational only.

## Completion Notes

- Expanded the Posts table primary content column and removed the title clipping risk by giving the first cell a stronger minimum width and readable text wrapping.
- Improved row hierarchy with cleaner title, slug, excerpt, and quieter metadata presentation in both standard and AI review modes.
- Added display-only date/time fields in the Posts Livewire mapper so published and updated timestamps render in a clearer stacked format.
- Stabilized the action column width and slightly refined filter-bar spacing and stat-card grid spacing on the Posts screen.
- Reused the existing shared action-menu/table behavior without duplicating logic.
- Validation passed with `php -l app/Livewire/Admin/Posts/Index.php`, `php artisan test --filter=PostIndexTest`, and `php artisan view:cache`.

## Completion Notes

- Reduced the Legal Pages section padding and internal spacing so it reads as a compact admin utility block instead of a large content section.
- Moved `Show Legal Only` and `Show All Pages` into the header area as small secondary controls and removed primary-orange competition with `Create Page`.
- Tightened the legal shortcut cards, kept the two-card desktop layout, and simplified each card to title, status, slug, one summary line, and a small item action.
- Preserved all existing filtering, edit/create actions, routes, and data bindings.
- Validation passed with `php artisan test --filter=PageIndexTest` and `php artisan view:cache`.
