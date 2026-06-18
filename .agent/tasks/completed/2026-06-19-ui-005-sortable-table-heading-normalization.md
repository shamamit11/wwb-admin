# Task: UI-005 Normalize sortable table-header behavior

Status: Completed

## Goal

Extend the shared table-heading component so sortable headers do not duplicate button markup, arrow rendering, and Livewire sort trigger construction across admin tables.

## Background

UI-TASKS.md calls out repeated sortable-header markup across posts, topic queue, content briefs, pages, and similar index tables. The current `x-ui.table-heading` only supports simple link-based sorting and does not cover Livewire sort triggers.

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
- resources/views/components/ui/table-heading.blade.php
- sortable admin index views using `sortBy(...)`

## Files To Change

- .agent/tasks/current-task.md
- resources/views/components/ui/table-heading.blade.php
- resources/views/livewire/admin/categories/index.blade.php
- resources/views/livewire/admin/tags/index.blade.php
- resources/views/livewire/admin/media/index.blade.php
- resources/views/livewire/admin/templates/index.blade.php
- resources/views/livewire/admin/knowledge-base/index.blade.php
- resources/views/livewire/admin/pages/index.blade.php
- resources/views/livewire/admin/posts/index.blade.php
- resources/views/livewire/admin/topic-queue/index.blade.php
- resources/views/livewire/admin/content-briefs/index.blade.php
- resources/views/livewire/admin/ai-prompts/index.blade.php
- resources/views/livewire/admin/ai-jobs/index.blade.php

## Implementation Steps

1. Inspect the current sortable header patterns and identify the sort state conventions in use.
2. Extend `x-ui.table-heading` to support Livewire sortable headers and arrow rendering.
3. Replace repeated inline sort-button markup in admin index tables with the shared API.
4. Run narrow validation.

## Acceptance Criteria

- Sortable table headers use a shared `x-ui.table-heading` API instead of per-page repeated button markup.
- The shared API supports both current sort state conventions used in admin index screens.
- Sort behavior and visual arrow state remain correct after the refactor.

## Validation Commands

- php artisan test --filter=View
- php artisan test --filter=Admin

## Risks

- The admin currently mixes `sortColumn`/`sortDirection` state with a single prefixed `$sort` string, so the shared component must support both without breaking current behavior.

## Completion Notes

- Extended `x-ui.table-heading` with a shared Livewire sortable-header API.
- The shared heading now supports both `sortColumn`/`sortDirection` tables and the prefixed single-string `$sort` convention used by the AI workflow screens.
- Replaced repeated inline sort-button markup across sortable admin index tables with the shared component API.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
