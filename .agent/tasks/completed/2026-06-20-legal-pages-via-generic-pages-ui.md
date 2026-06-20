# Task: Improve legal page management through existing Pages UI

Status: Completed

## Goal

Make Privacy Policy and Terms and Conditions easy to manage through the existing generic Pages admin flow, using only the current Pages service APIs.

## Background

The admin already has a service-backed Pages list/editor using `/admin/pages`, including page type support. Legal content should stay in this existing system, but the UX should make legal pages visible and straightforward to create and edit without introducing a new singleton or backend model.

## Required Context

- `.agent/API-CONTRACT.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/shadcn-inspired-ui.md`

## Files To Inspect

- `app/Livewire/Admin/Pages/Index.php`
- `app/Livewire/Admin/Pages/Editor.php`
- `resources/views/livewire/admin/pages/index.blade.php`
- `resources/views/livewire/admin/pages/editor.blade.php`
- `tests/Feature/Pages/PageIndexTest.php`
- `tests/Feature/Pages/PageEditorTest.php`

## Files To Change

- `app/Livewire/Admin/Pages/Index.php`
- `app/Livewire/Admin/Pages/Editor.php`
- `resources/views/livewire/admin/pages/index.blade.php`
- focused Pages tests

## Implementation Steps

1. Keep legal content inside the existing Pages list/editor backed by `/admin/pages`.
2. Make it easier to find and create legal pages through the current Pages index flow.
3. Reuse or tighten `type=legal` filtering and optionally legal-page presets in the editor.
4. Add focused tests for legal filtering and legal page create/edit affordances.
5. Run narrow validation.

## Acceptance Criteria

- Privacy Policy and Terms and Conditions are manageable through the existing Pages UI only.
- The UI makes legal pages visible rather than burying them among unrelated pages.
- Legal page creation/editing uses the existing page fields and backend contract.
- No new singleton or legal-specific backend model is introduced.

## Validation Commands

- `php artisan test tests/Feature/Pages/PageIndexTest.php tests/Feature/Pages/PageEditorTest.php`

## Risks

- Legal-page affordances should remain additive to the generic Pages workflow and not accidentally narrow the list view for non-legal content.

## Completion Notes

- Kept legal content inside the existing generic Pages list/editor backed by the current `/admin/pages` APIs only.
- Added a dedicated `Legal Pages` management surface on the Pages index to make Privacy Policy and Terms and Conditions obvious, with quick create/edit actions.
- Reused the existing `type=legal` filter and added a one-click `Show Legal Only` action.
- Added lightweight legal-page presets in the existing page editor for Privacy Policy and Terms and Conditions, pre-filling recommended legal conventions and starter content.
- Verified the list, filter, preset, and existing editor flow with the focused Pages feature suite.
