# Task: Implement admin Pages module from service-backed page resources

Status: Completed

## Goal

Replace the placeholder `Pages` module with a real service-backed pages index and editor using the new `/admin/pages` API contract.

## Background

The service now exposes page CRUD endpoints documented in `openapi.json`. The admin should support managing static and evergreen pages such as privacy policy and FAQ through a proper publishing workflow, not through settings.

## Required Context

- `.agent/API-CONTRACT.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `docs/API_INTEGRATION.md`
- `openapi.json`

## Files To Inspect

- `openapi.json`
- `routes/web.php`
- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `app/Services/WideWebBlogApi/Clients/KnowledgeBaseClient.php`
- `app/Livewire/Admin/KnowledgeBase/*`
- `resources/views/livewire/admin/knowledge-base/*`

## Files To Change

- `routes/web.php`
- `app/Services/WideWebBlogApi/Clients/PageClient.php`
- `app/Livewire/Admin/Pages/*`
- `resources/views/livewire/admin/pages/*`
- `app/Support/Navigation/AdminNavigation.php`
- `resources/views/components/admin/nav-link.blade.php`
- `tests/Unit/WideWebBlogApi/Clients/PageClientTest.php`
- `tests/Feature/Pages/*`
- `tests/Feature/Navigation/AdminNavigationTest.php`

## Implementation Steps

1. Read the exact page endpoint and schema contract from `openapi.json`.
2. Add a pages API client for index/show/store/update/delete.
3. Replace the placeholder route with real index/create/edit routes.
4. Build a searchable index screen and a markdown-based editor aligned to the documented fields.
5. Add focused client and feature coverage.

## Acceptance Criteria

- [x] Pages can be listed from the service-backed endpoint
- [x] Pages can be created, edited, and deleted through the admin
- [x] The implementation does not invent unsupported page fields or behaviors

## Validation Commands

- `php artisan test tests/Unit/WideWebBlogApi/Clients/PageClientTest.php tests/Feature/Pages/PageIndexTest.php tests/Feature/Pages/PageEditorTest.php tests/Feature/Navigation/AdminNavigationTest.php`

## Risks

- The page editor must stay aligned to the request schema, especially where the response contains fields that are not writable.

## Completion Notes

- Added a `PageClient` for the documented `/admin/pages` CRUD endpoints.
- Replaced the pages placeholder route with real `pages.index`, `pages.create`, and `pages.edit` screens.
- Built a service-backed pages index with search, status/type/visibility filters, sorting, and delete confirmation.
- Built a markdown-based page editor for title, slug, type, status, summary, content, visibility, timestamps, and meta array editing.
- Kept `canonical_url` read-only because it is returned on the resource but not included in the documented create/update request schema.
- Validation run: `php artisan test tests/Unit/WideWebBlogApi/Clients/PageClientTest.php tests/Feature/Pages/PageIndexTest.php tests/Feature/Pages/PageEditorTest.php tests/Feature/Navigation/AdminNavigationTest.php`
