# Task: Explicit admin table header sweep

Status: Completed

## Goal

Explicitly patch every admin table page so header labels themselves are uppercase and action columns use the shared three-dot dropdown pattern where applicable.

## Background

Shared primitive changes improved consistency, but a few pages still appeared mixed because the slot content itself remained Title Case. This pass makes the header text explicit in each relevant table view.

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

## Files To Inspect

- admin Blade pages that render `x-ui.table`

## Files To Change

- `.agent/tasks/current-task.md`
- admin Blade table views with mixed header labels

## Implementation Steps

1. Inspect every admin Blade page that renders a table.
2. Explicitly uppercase table header labels in pages that still look mixed.
3. Confirm action columns route through the shared three-dot dropdown pattern where applicable.
4. Run narrow validation.

## Acceptance Criteria

- [x] Relevant admin table pages now use explicit uppercase header labels.
- [x] Admin table action columns use the shared three-dot dropdown pattern where applicable.
- [x] Existing routes and Livewire action handlers are preserved.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- Explicit per-page labels increase markup duplication slightly, but remove ambiguity from inherited styling.

## Completion Notes

- Explicitly uppercased remaining mixed table headers on media, categories, pages, AI jobs, topic queue, templates, knowledge base, tags, and SEO tables.
- Prior shared row-action work remains in place for dropdown-based action menus on management tables.
- Validation passed with `php artisan test --filter=View` and `php artisan test --filter=Admin`.
