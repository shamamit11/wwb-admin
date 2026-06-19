# Task: UI-010 Normalize placeholder and fallback copy

Status: Completed

## Goal

Define and apply a consistent placeholder and fallback-copy system for missing, pending, unavailable, and unknown values across scoped admin screens.

## Background

`UI-TASKS.md` calls out drift between labels like `Unknown`, `TBC`, `None`, `Not linked`, `No source URL`, `Slug pending`, and `Not published`. The recent UI-009 pass standardized action vocabulary; this task should do the same for data-state copy.

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

- scoped admin Blade views called out in `UI-TASKS.md`
- any shared component or helper already used for placeholder rendering, if one exists

## Files To Change

- `.agent/tasks/current-task.md`
- only the scoped Blade views and lightweight UI guidance needed to normalize fallback copy

## Implementation Steps

1. Inspect fallback text in the scoped views and group usages by semantic state.
2. Define a minimal fallback-copy rule for missing, pending, unavailable, and unknown values.
3. Update the scoped views to use that rule without changing unrelated UI behavior.
4. Run narrow validation.

## Acceptance Criteria

- Similar empty-value states use consistent wording in scope.
- Distinct meanings remain distinct: missing value, pending generation, intentionally unavailable, and unknown from API.
- No unrelated layout, component, or workflow behavior changes are introduced.

## Validation Commands

- `php artisan test --filter=View`
- `php artisan test --filter=Admin`

## Risks

- Some current labels may encode domain nuance, so standardization needs to preserve meaning rather than flatten every empty state to one phrase.

## Completion Notes

- Added a reusable fallback-copy rule to `.agent/UI-UX-RULES.md`:
  - `Unknown` for missing API data
  - `Not set` for empty editable metadata
  - `... pending` for derived values expected later
  - `None` for intentionally empty relationships or optional resources
  - explicit lifecycle phrases like `Not published` and `Not started` where state-specific wording is clearer
  - `Unavailable` for values not exposed on the current surface
- Normalized scoped fallback copy across posts, topic queue, AI jobs, knowledge base, SEO, and content-brief detail views.
- Removed ambiguous placeholders like `TBC`, `Not linked`, `No source URL`, `Auto-generated slug`, and `Unknown agent` from the scoped surfaces.
- Validation passed:
  - `php artisan test --filter=View`
  - `php artisan test --filter=Admin`
