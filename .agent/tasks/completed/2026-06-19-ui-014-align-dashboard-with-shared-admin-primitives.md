# Task: UI-014 Align dashboard with shared admin primitives

Status: Completed

## Goal

Pull the dashboard closer to the shared admin component language while preserving any intentional editorial energy that still serves the screen.

## Background

`UI-TASKS.md` calls out the dashboard as visually more custom than the module screens, especially around the header, summary cards, and action/list item treatments. This task should decide what stays intentionally distinct versus what should align with the shared admin system.

## Required Context

- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/UI_UX_GUIDELINES.md`
- `UI-TASKS.md`
- completed UI task notes for related standardization passes

## Files To Inspect

- `resources/views/livewire/admin/dashboard/index.blade.php`
- shared admin primitives most relevant to the dashboard:
  - page header
  - stat card
  - row/list action treatments
  - callout or badge components if applicable

## Files To Change

- `.agent/tasks/current-task.md`
- `resources/views/livewire/admin/dashboard/index.blade.php`
- only shared component files if a small adjustment is clearly justified by the dashboard use case

## Implementation Steps

1. Inspect the dashboard and identify which custom patterns should align with shared primitives.
2. Refactor the dashboard toward the shared admin language without making it feel generic or flattening useful hierarchy.
3. Make minimal supporting shared-component changes only if needed.
4. Run narrow validation.

## Acceptance Criteria

- The dashboard feels more consistent with the shared admin system.
- Summary, header, and actionable list sections align more clearly with established primitives.
- No unrelated module behavior or broad visual rework is introduced.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- The dashboard is allowed to retain some intentional character, so over-normalizing it into a generic module page would be a regression rather than an improvement.

## Completion Notes

- Replaced the custom dashboard hero with the shared `x-admin.page-header` and aligned the primary actions to `Create Post` and `Review Drafts`.
- Pulled the AI workflow snapshot closer to the shared admin language by rendering those entry points through `x-admin.stat-card` instead of bespoke metric-card markup.
- Simplified the draft-review and recent-activity sections to use shared `x-ui.card`, `x-ui.button`, `x-ui.badge`, and `x-admin.status-badge` patterns instead of custom icon pills and heavier list-item treatments.
- Kept the dashboard’s grouping and higher-level overview role intact instead of flattening it into a generic management page.
- No shared component changes were required for this pass.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
