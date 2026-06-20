# Task: Align Homepage admin UI with automatic section contract

Status: Completed

## Goal

Update the Homepage admin management screen so it matches the current service contract for `GET /api/v1/admin/homepage` and `PUT /api/v1/admin/homepage`, especially the automatic-only behavior for featured editorial, recent articles, and topic sections.

## Background

The service no longer accepts manual curation fields for `featured_editorial`, `guide_section`, or `topic_section`. The admin UI currently still exposes manual ID controls and sends obsolete fields such as `mode`, `post_ids`, and `category_ids`.

## Required Context

- `.agent/API-CONTRACT.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `openapi.json`

## Files To Inspect

- `openapi.json`
- `app/Livewire/Admin/Homepage/Index.php`
- `resources/views/livewire/admin/homepage/index.blade.php`
- `tests/Feature/Homepage/HomepageIndexTest.php`
- `tests/Unit/WideWebBlogApi/Clients/HomepageClientTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`

## Files To Change

- `app/Livewire/Admin/Homepage/Index.php`
- `resources/views/livewire/admin/homepage/index.blade.php`
- `tests/Feature/Homepage/HomepageIndexTest.php`
- `tests/Unit/WideWebBlogApi/Clients/HomepageClientTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`

## Implementation Steps

1. Confirm the current homepage request and response shape from `openapi.json`.
2. Remove obsolete manual curation state, validation, and payload mapping for homepage auto-managed sections.
3. Update labels and help text so `guide_section` is presented as Recent Articles and all three sections are clearly backend-managed.
4. Adjust focused tests to assert only the supported payload is sent and automatic response fields remain tolerated on load.
5. Run narrow Homepage validation.

## Acceptance Criteria

- Homepage UI no longer exposes manual post/category selectors for featured editorial, recent articles, or topics.
- Homepage save payload excludes unsupported manual curation fields.
- `guide_section` is labeled Recent Articles in the admin UI.
- Automatic response-only fields from the service do not break form loading.
- Focused Homepage tests pass.

## Risks

- The service may still return normalized automatic-only fields in GET responses, so load-time normalization must remain tolerant without reintroducing those fields into PUT payloads.

## Validation Commands

- `php artisan test tests/Unit/WideWebBlogApi/Clients/HomepageClientTest.php tests/Feature/Homepage/HomepageIndexTest.php tests/Feature/Navigation/AdminNavigationTest.php`

## Completion Notes

- Removed obsolete manual homepage curation state, validation, and request payload fields for featured editorial, recent articles, and topic sections.
- Kept GET response normalization tolerant of service-managed `mode`, `post_ids`, and `category_ids` fields without exposing them in the form or sending them back on save.
- Renamed the admin `guide_section` presentation to Recent Articles and updated section copy to explain backend-managed automatic content.
- Verified the aligned payload and UI behavior with the focused Homepage and navigation test suite.
