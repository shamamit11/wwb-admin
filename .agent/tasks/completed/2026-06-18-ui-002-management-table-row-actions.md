# Task: UI-002 management table row actions

Status: Completed

## Goal

Standardize management-table row actions across admin list screens onto one compact text-button pattern using shared admin Blade components.

## Background

`UI-TASKS.md` identifies four competing row-action patterns across management tables: oversized icon-only buttons, outline text buttons, ghost text buttons, and single-action buttons with mixed variants. Dense tables should feel like one system and reuse shared components instead of page-local action markup.

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
- `.agent/skills/tables-filters-pagination.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/COMPONENT_SYSTEM.md`
- `UI-TASKS.md`

## Files To Inspect

- `resources/views/components/admin/row-actions.blade.php`
- `resources/views/components/ui/button.blade.php`
- table index views with action columns only

## Files To Change

- `.agent/tasks/current-task.md`
- `resources/views/components/admin/row-actions.blade.php`
- new shared row-action Blade component if needed
- admin index Blade views with right-side row actions

## Implementation Steps

1. Define one shared compact text-action pattern for dense management tables.
2. Update the shared admin row-actions component to own row action layout.
3. Add a shared row-action helper component if needed to centralize button variant, size, and destructive treatment.
4. Migrate existing table screens to the shared pattern without changing unrelated table behavior.
5. Run narrow validation.

## Acceptance Criteria

- [x] Dense management tables use one consistent row-action presentation.
- [x] Oversized icon-only row actions are removed from categories, tags, media, and templates.
- [x] Single-action and mixed text-action tables align with the same shared pattern.
- [x] No unrelated filter, sort, pagination, or dialog changes are introduced.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- Some tables have more actions than others, so spacing must still hold on narrower widths.
- Standardizing on visible text actions may widen certain action columns compared with the prior icon-only layout.

## Completion Notes

- Replaced the old dropdown-style `x-admin.row-actions` implementation with a shared flex layout wrapper for dense table action groups.
- Added `x-admin.row-action` to centralize compact text-button styling and destructive-action treatment.
- Migrated row actions on categories, tags, media, pages, knowledge base, topic queue, content briefs, AI jobs, AI prompts, templates, and posts index screens to the shared pattern.
- Validation passed with `php artisan test --filter=View` and `php artisan test --filter=Admin`.
