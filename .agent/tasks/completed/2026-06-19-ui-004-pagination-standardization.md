# Task: UI-004 Standardize pagination UI and reuse the shared pagination component

Status: Completed

## Goal

Consolidate paginated admin list screens onto a single shared pagination component and remove duplicated manual pagination markup.

## Background

UI-TASKS.md identifies duplicated pagination implementations across Topic Queue, AI Jobs, Content Briefs, and AI Prompts. The shared pagination component exists but is not consistently used.

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
- resources/views/components/ui/pagination.blade.php
- resources/views/livewire/admin/topic-queue/index.blade.php
- resources/views/livewire/admin/ai-jobs/index.blade.php
- resources/views/livewire/admin/content-briefs/index.blade.php
- resources/views/livewire/admin/ai-prompts/index.blade.php

## Files To Change

- .agent/tasks/current-task.md
- resources/views/components/ui/pagination.blade.php
- resources/views/livewire/admin/topic-queue/index.blade.php
- resources/views/livewire/admin/ai-jobs/index.blade.php
- resources/views/livewire/admin/content-briefs/index.blade.php
- resources/views/livewire/admin/ai-prompts/index.blade.php

## Implementation Steps

1. Inspect the shared pagination component API and current paginated screens.
2. Extend the component if needed to support the custom paginator data shape used by current screens.
3. Replace duplicated manual pagination blocks with the shared component.
4. Run narrow validation for affected views.

## Acceptance Criteria

- A single shared pagination presentation is used on paginated admin list screens in scope.
- Duplicated per-page pagination wrappers are removed.
- Pagination still renders correctly for the current custom paginator payloads.

## Validation Commands

- php artisan test --filter=View
- php artisan test --filter=Admin

## Risks

- Some screens may pass paginator data in slightly different shapes and require a careful shared API.

## Completion Notes

- Extended `x-ui.pagination` to support both Laravel paginator objects and the array-backed pagination payload used by the AI workflow index screens.
- Replaced duplicated manual pagination blocks in Topic Queue, AI Jobs, Content Briefs, and Prompt Templates with the shared pagination component.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
