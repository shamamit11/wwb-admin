# Task: Refine template block editor UX

Status: Completed

## Goal

Apply the same clearer block editing pattern used in the post editor to the template create/edit drawer.

## Background

Template block editing still used a more technical raw form. The goal was to make the template authoring experience feel consistent with the improved post block editor.

## Required Context

- `.agent/UI-UX-RULES.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/forms-validation.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/API_INTEGRATION.md`
- `openapi.json`

## Files To Inspect

- `app/Livewire/Admin/Templates/Index.php`
- `resources/views/livewire/admin/templates/index.blade.php`
- `tests/Feature/Templates/TemplateIndexTest.php`

## Files To Change

- `app/Livewire/Admin/Templates/Index.php`
- `resources/views/livewire/admin/templates/index.blade.php`
- `tests/Feature/Templates/TemplateIndexTest.php`

## Implementation Steps

- add block-type-specific template block guidance, placeholders, and labels
- add a lightweight markdown snippet toolbar for default markdown fields
- keep settings and required-state controls, but present them in a more explanatory layout
- add focused tests for template block editing helpers and rendered guidance

## Acceptance Criteria

- template block authoring feels aligned with the improved post editor
- basic markdown affordances are available in template default content fields
- payload shape remains unchanged and contract-aligned

## Validation Commands

- `php artisan test tests/Feature/Templates/TemplateIndexTest.php`

## Risks

- template block settings remain JSON-driven because the API contract does not define per-block typed settings forms

## Completion Notes

- added block-type-specific guidance, placeholders, and labels to the template block editor
- added a lightweight markdown snippet toolbar for template default markdown fields
- kept settings and required-state controls intact while making the block editor more explanatory
- validated with `php artisan test tests/Feature/Templates/TemplateIndexTest.php`
