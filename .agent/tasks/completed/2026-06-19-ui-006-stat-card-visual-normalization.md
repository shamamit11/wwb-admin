# Task: UI-006 Normalize dashboard/stat-card visuals across operational list screens

Status: Completed

## Goal

Standardize operational summary cards on a single shared stat-card component with consistent padding, label treatment, value scale, and optional icon/badge slot.

## Background

UI-TASKS.md identifies repeated stat-card patterns across Topic Queue, AI Jobs, Content Briefs, AI Prompts, and Posts. Some screens render custom cards instead of using the shared admin stat-card component.

## Required Context

- .agent/UI-UX-RULES.md
- .agent/COMPONENT-SYSTEM.md
- .agent/skills/blade-components.md
- .agent/skills/shadcn-inspired-ui.md
- docs/COMPONENT_SYSTEM.md
- docs/UI_UX_GUIDELINES.md

## Files To Inspect

- UI-TASKS.md
- resources/views/components/admin/stat-card.blade.php
- resources/views/livewire/admin/topic-queue/index.blade.php
- resources/views/livewire/admin/ai-jobs/index.blade.php
- resources/views/livewire/admin/content-briefs/index.blade.php
- resources/views/livewire/admin/ai-prompts/index.blade.php
- resources/views/livewire/admin/posts/index.blade.php

## Files To Change

- .agent/tasks/current-task.md
- resources/views/components/admin/stat-card.blade.php
- resources/views/livewire/admin/topic-queue/index.blade.php
- resources/views/livewire/admin/ai-jobs/index.blade.php
- resources/views/livewire/admin/content-briefs/index.blade.php
- resources/views/livewire/admin/ai-prompts/index.blade.php
- resources/views/livewire/admin/posts/index.blade.php

## Implementation Steps

1. Inspect the shared stat-card component and current custom summary-card patterns.
2. Extend the shared component if needed to cover current operational list screen needs.
3. Replace repeated custom stat-card markup with the shared component.
4. Run narrow validation.

## Acceptance Criteria

- Operational list screen stat cards use a shared component API.
- Padding, label styling, and value scale are consistent across screens in scope.
- Existing informational icon or tone treatment is preserved through the shared component instead of inline custom markup.

## Validation Commands

- php artisan test --filter=View
- php artisan test --filter=Admin

## Risks

- Posts currently uses a richer icon treatment than the other list screens, so the shared component may need a flexible slot without becoming overgeneralized.

## Completion Notes

- Extended `x-admin.stat-card` to own shared operational stat-card styling, tone mapping, optional suffix support, and an optional lead icon slot.
- Replaced repeated custom summary-card markup in Topic Queue, AI Jobs, Content Briefs, Prompt Templates, and Posts with the shared component.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
