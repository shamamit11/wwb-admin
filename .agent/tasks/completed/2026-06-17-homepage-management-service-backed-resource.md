# Task: Implement Homepage Management from service-backed homepage resource

Status: Completed

## Goal

Build a dedicated admin homepage management screen for structured homepage curation using the documented service-backed homepage endpoints.

## Background

The service now exposes a singleton homepage resource in `openapi.json`. The admin should manage homepage composition through one structured editor, not through generic settings or a drag-and-drop builder.

## Required Context

- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`
- `openapi.json`

## Files To Inspect

- `openapi.json`
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- existing admin screen/client/test patterns closest to singleton editors

## Files To Change

- `app/Livewire/Admin/Homepage/*`
- `resources/views/livewire/admin/homepage/*`
- `app/Services/WideWebBlogApi/Clients/*`
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `tests/Feature/Homepage/*`
- focused client tests with HTTP fakes

## Implementation Steps

1. Read the homepage request/response contract from `openapi.json`.
2. Add a dedicated homepage API client for load/update.
3. Create a single structured Livewire editor screen with contract-backed sections.
4. Add focused tests for client calls, screen rendering, and update flow.
5. Run narrow validation and record any contract limitations.

## Acceptance Criteria

- [x] homepage screen exists as a dedicated admin module
- [x] homepage data loads from the service
- [x] homepage data can be updated through the documented endpoint
- [x] nested validation and error states are clear
- [x] UI stays structured and operational rather than page-builder-like

## Validation Commands

- `php artisan test --filter=Homepage`

## Risks

- The homepage schema may contain nested arrays and section modes that need careful validation/error mapping.

## Completion Notes

- Added a singleton `HomepageClient` for `GET /admin/homepage` and `PUT /admin/homepage`.
- Added a dedicated `/homepage` admin screen with structured section cards for hero, featured editorial, guide section, topic section, promo section, newsletter section, and homepage SEO.
- Kept homepage management separate from Settings and avoided any drag-and-drop or generic page-builder behavior.
- Preserved ordered arrays through explicit repeaters for curated IDs, bullet points, and promo stats.
- Current UX intentionally uses structured ID repeaters rather than richer post/category pickers because the contract only requires ordered ID arrays and this keeps the first implementation narrow.
- Normalized nullable nested arrays from the service response so the screen remains stable when sections return `null` list fields.
- Validation run: `php artisan test tests/Unit/WideWebBlogApi/Clients/HomepageClientTest.php tests/Feature/Homepage/HomepageIndexTest.php tests/Feature/Navigation/AdminNavigationTest.php`
