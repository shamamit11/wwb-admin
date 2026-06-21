# Task: Optimize About And Contact CMS Editor UI

Status: Completed

## Goal

Apply the same structured, compact CMS editor pattern used on Homepage to the About Page and Contact Page singleton editors without changing backend behavior.

## Background

The About and Contact editors are functional but still use the older always-expanded layout. This task focuses on bringing them in line with the improved Homepage editing experience: collapsible sections, sticky sidebars, clearer summaries/navigation, and stronger save/editing confidence.

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

- `app/Livewire/Admin/AboutPage/Index.php`
- `resources/views/livewire/admin/about-page/index.blade.php`
- `app/Livewire/Admin/ContactPage/Index.php`
- `resources/views/livewire/admin/contact-page/index.blade.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `resources/views/livewire/admin/about-page/index.blade.php`
- `resources/views/livewire/admin/contact-page/index.blade.php`

## Implementation Steps

1. Restructure About and Contact editors into collapsible sections while preserving all bindings and fields.
2. Improve section headers, sidebar summary/navigation, sticky behavior, and top actions to match Homepage UX.
3. Add lightweight save-state feedback and clearer section summaries on both singleton editors.
4. Run narrow validation for About and Contact syntax and tests.
5. Update task notes with validation and residual risk.

## Acceptance Criteria

- About and Contact sections are no longer visually overwhelming because non-primary sections can collapse.
- Both pages gain a more useful sticky sidebar and section navigation.
- Preview actions become page-specific and clearer.
- Existing About and Contact save behavior and bindings continue to work.

## Validation Commands

- `php artisan test --filter=AboutPageIndexTest`
- `php artisan test --filter=ContactPageIndexTest`
- `php artisan view:cache`

## Risks

- The service payload does not expose rich per-section completion metadata, so section summaries and status chips must reflect only the currently available fields.

## Completion Notes

- Reworked the About Page and Contact Page editors into collapsible section cards with Hero expanded by default.
- Added saved/unsaved change indicators, page-specific preview labels, sticky desktop sidebars, and clickable section navigation on both pages.
- Improved sidebar summaries and section header context so singleton page state is easier to scan before expanding sections.
- Preserved all existing Livewire bindings, ordered list controls, save behavior, fields, and routes.
- Validation passed with `php artisan test --filter=AboutPageIndexTest`, `php artisan test --filter=ContactPageIndexTest`, and `php artisan view:cache`.
