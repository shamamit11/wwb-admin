# Task: UI-015 Add a formal table-density and column-width system

Status: Completed

## Goal

Define a reusable table-density and column-width system so admin tables stop relying on ad hoc per-screen percentage classes.

## Background

UI-TASKS.md calls out repeated `w-[24%]`, `w-[31%]`, `w-[34%]`, `w-[44%]`, and `w-[12%]` classes across content, workflow, taxonomy, and asset tables. The shared table components existed, but width and density decisions still lived inside individual views.

## Required Context

- .agent/UI-UX-RULES.md
- .agent/COMPONENT-SYSTEM.md
- .agent/skills/blade-components.md
- .agent/skills/shadcn-inspired-ui.md
- .agent/skills/tables-filters-pagination.md
- docs/COMPONENT_SYSTEM.md
- docs/UI_UX_GUIDELINES.md

## Files To Inspect

- UI-TASKS.md
- resources/views/components/ui/table.blade.php
- resources/views/components/ui/table-heading.blade.php
- resources/views/components/ui/table-cell.blade.php
- representative admin table screens with hardcoded widths

## Files To Change

- .agent/tasks/current-task.md
- resources/views/components/ui/table.blade.php
- resources/views/components/ui/table-heading.blade.php
- resources/views/components/ui/table-cell.blade.php
- resources/views/livewire/admin/categories/index.blade.php
- resources/views/livewire/admin/tags/index.blade.php
- resources/views/livewire/admin/pages/index.blade.php
- resources/views/livewire/admin/templates/index.blade.php
- resources/views/livewire/admin/knowledge-base/index.blade.php
- resources/views/livewire/admin/posts/index.blade.php
- resources/views/livewire/admin/topic-queue/index.blade.php
- resources/views/livewire/admin/content-briefs/index.blade.php
- resources/views/livewire/admin/media/index.blade.php
- resources/views/livewire/admin/seo/index.blade.php
- resources/views/livewire/admin/ai-jobs/index.blade.php
- resources/views/livewire/admin/ai-prompts/index.blade.php

## Implementation Steps

1. Inspect current width and density drift across admin tables.
2. Extend shared table primitives with formal density and width tokens.
3. Replace hardcoded per-screen width percentages with the shared token system.
4. Apply the shared density mode to current admin management tables.
5. Run narrow validation.

## Acceptance Criteria

- Shared table components expose a formal density system.
- Shared table heading/cell components expose named width tokens instead of relying on per-screen raw width percentages.
- Representative taxonomy, content, workflow, asset, and read-only feed tables use the shared width tokens.

## Validation Commands

- php artisan test --filter=View
- php artisan test --filter=Admin

## Risks

- Width normalization changes multiple dense tables at once, so the chosen token values must preserve readability without causing cramped action columns.

## Completion Notes

- Added `density` variants to `x-ui.table` so table padding now comes from the shared table primitive instead of each heading/cell.
- Added named width tokens to `x-ui.table-heading` and `x-ui.table-cell` for asset preview, workflow primary, feed primary, content primary, and taxonomy primary columns.
- Replaced ad hoc percentage widths in representative taxonomy, content, workflow, asset, and SEO feed tables with the shared token system.
- Applied `density="compact"` to current admin management and inspection tables in scope.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
