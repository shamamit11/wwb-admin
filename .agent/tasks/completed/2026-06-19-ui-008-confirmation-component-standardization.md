# Task: UI-008 Standardize destructive-action confirmation components

Status: Completed

## Goal

Standardize destructive and workflow-confirm dialogs on one shared confirmation API with consistent title, description, body spacing, and footer actions.

## Background

The admin currently mixes direct `x-ui.dialog` usage with `x-admin.confirm-dialog`. The wrapper already composes `x-ui.dialog`, so the main inconsistency is at the page-usage level.

## Required Context

- .agent/UI-UX-RULES.md
- .agent/COMPONENT-SYSTEM.md
- .agent/skills/blade-components.md
- .agent/skills/shadcn-inspired-ui.md
- docs/COMPONENT_SYSTEM.md
- docs/UI_UX_GUIDELINES.md

## Files To Inspect

- UI-TASKS.md
- resources/views/components/ui/dialog.blade.php
- resources/views/components/admin/confirm-dialog.blade.php
- destructive/workflow confirmation usages in admin Livewire views

## Files To Change

- .agent/tasks/current-task.md
- resources/views/livewire/admin/categories/index.blade.php
- resources/views/livewire/admin/tags/index.blade.php
- resources/views/livewire/admin/media/index.blade.php
- resources/views/livewire/admin/templates/index.blade.php
- resources/views/livewire/admin/pages/index.blade.php
- resources/views/livewire/admin/knowledge-base/index.blade.php
- resources/views/livewire/admin/posts/index.blade.php
- resources/views/livewire/admin/posts/editor.blade.php

## Implementation Steps

1. Inspect the two confirmation patterns and current usages.
2. Choose one shared confirmation API and extend it only if needed.
3. Migrate destructive/workflow confirmations in scope to the shared API.
4. Run narrow validation.

## Acceptance Criteria

- Destructive and workflow-confirm dialogs in scope use one shared confirmation API.
- Title, description, body padding, and footer actions are consistent.
- Existing Livewire actions and loading states remain intact.

## Validation Commands

- php artisan test --filter=View
- php artisan test --filter=Admin

## Risks

- Some dialogs currently use richer custom body content or width options, so the chosen shared API must preserve that flexibility.

## Completion Notes

- Standardized destructive and simple workflow-confirm dialogs on `x-admin.confirm-dialog`, which continues to compose `x-ui.dialog` underneath.
- Migrated delete confirmations in categories, tags, media, templates, pages, and knowledge base onto the shared confirmation API.
- Migrated post publish/schedule/unpublish/delete confirmation flows in the posts index and post editor onto the shared confirmation API.
- Left generic non-confirmation modal shells such as the media picker and richer AI/rewrite forms on `x-ui.dialog`.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
