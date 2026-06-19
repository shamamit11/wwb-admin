# Task: UI-007 Standardize filter-toolbar structure and count presentation

Status: Completed

## Goal

Define one shared filter-toolbar recipe for management screens with a clear left search area, middle filter area, and right result-count or secondary action area.

## Background

UI-TASKS.md calls out inconsistent slot usage in the shared filter bar, especially on AI Jobs, plus Media still using a non-shared toolbar layout.

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
- resources/views/components/admin/filter-bar.blade.php
- resources/views/livewire/admin/pages/index.blade.php
- resources/views/livewire/admin/ai-jobs/index.blade.php
- resources/views/livewire/admin/media/index.blade.php
- related management screens already using `x-admin.filter-bar`

## Files To Change

- .agent/tasks/current-task.md
- resources/views/components/admin/filter-bar.blade.php
- resources/views/livewire/admin/media/index.blade.php
- resources/views/livewire/admin/categories/index.blade.php
- resources/views/livewire/admin/pages/index.blade.php
- resources/views/livewire/admin/templates/index.blade.php
- resources/views/livewire/admin/content-briefs/index.blade.php
- resources/views/livewire/admin/knowledge-base/index.blade.php
- resources/views/livewire/admin/tags/index.blade.php
- resources/views/livewire/admin/topic-queue/index.blade.php
- resources/views/livewire/admin/ai-jobs/index.blade.php
- resources/views/livewire/admin/ai-prompts/index.blade.php
- resources/views/livewire/admin/posts/index.blade.php

## Implementation Steps

1. Inspect current filter-bar usage patterns and define the standard slot structure.
2. Extend or tighten the shared `x-admin.filter-bar` API if needed.
3. Migrate inconsistent list screens to the shared toolbar recipe.
4. Run narrow validation.

## Acceptance Criteria

- Management list screens in scope use a consistent toolbar structure.
- Search, filters, and result count appear in predictable positions.
- Ad hoc toolbar markup is removed where the shared component should be used.

## Validation Commands

- php artisan test --filter=View
- php artisan test --filter=Admin

## Risks

- Some screens have multiple compact filters, so the shared layout must preserve wrapping behavior without forcing awkward widths.

## Completion Notes

- Extended `x-admin.filter-bar` with a dedicated `results` slot that applies consistent count styling and placement.
- Moved AI Jobs to the standard toolbar composition by reserving the filter area for all structured filters instead of treating them as search content.
- Replaced the Media Library’s custom toolbar panel with the shared filter bar.
- Migrated all current `x-admin.filter-bar` consumers to the shared count presentation via the `results` slot.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
