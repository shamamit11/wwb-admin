# Task: Enhance settings tabs visual treatment

Status: Completed

## Goal

Improve the Settings tab strip so it feels more intentional, polished, and aligned with the shared admin UI primitives.

## Background

The current Settings tabs render correctly but look visually flat in the current admin theme. The request is to enhance the appearance without changing the read-only settings behavior or introducing a one-off page-specific pattern.

## Required Context

- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/UI_UX_GUIDELINES.md`

## Files To Inspect

- `resources/views/livewire/admin/settings/index.blade.php`
- `resources/views/livewire/admin/seo/index.blade.php`
- `resources/views/components/ui/tabs-list.blade.php`
- `resources/views/components/ui/tabs-trigger.blade.php`

## Files To Change

- `resources/views/components/ui/tabs-list.blade.php`
- `resources/views/components/ui/tabs-trigger.blade.php`

## Implementation Steps

1. Confirm where shared tabs primitives are used.
2. Improve container spacing, surface, and trigger states in the reusable tabs components.
3. Keep the change compatible with both link-based and button-based tab triggers.
4. Run narrow validation.
5. Tighten the visual hierarchy further if the first pass remains too subtle in rendered UI.

## Acceptance Criteria

- Settings tabs have a clearer shared container treatment and active state.
- Inactive tabs have clearer hover and focus affordances.
- SEO tabs continue to render correctly with the shared primitive.
- No page-specific markup changes are required for the enhancement.

## Validation Commands

- `php artisan test --filter=Seo`
- `php artisan test --filter=Settings`

## Risks

- Shared primitive changes also affect the SEO tab surface.
- Visual validation is limited to code inspection unless browser rendering is checked.

## Completion Notes

- First pass was too subtle in rendered UI and did not create a meaningfully stronger segmented control.
- Second pass switched the shared tabs primitive to a clearer pill-style segmented control with stronger active-state contrast.
- Updated both link-based and button-based tab triggers without changing behavior.
- Verified with `php artisan test --filter=Settings` and `php artisan test --filter=Seo`.
