# Task: Implement singleton About Page admin editor

Status: Completed

## Goal

Build or update the admin About Page editor so it uses the singleton service API at `GET /api/v1/admin/about-page` and `PUT /api/v1/admin/about-page`, with form state and UI aligned exactly to the backend payload.

## Background

The admin currently has generic Pages CRUD/list screens and a singleton Homepage editor, but no dedicated About Page singleton editor wired to the new service contract. The navigation also needs Homepage, About Us, and Pages regrouped under a `CMS` section instead of Publishing.

## Required Context

- `.agent/API-CONTRACT.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `openapi.json`

## Files To Inspect

- `openapi.json`
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `app/Livewire/Admin/Homepage/Index.php`
- `resources/views/livewire/admin/homepage/index.blade.php`
- `app/Livewire/Admin/Pages/Editor.php`
- `app/Services/WideWebBlogApi/Clients/*`
- related feature and client tests

## Files To Change

- About Page Livewire component, Blade view, and API client files
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- focused About Page and navigation tests
- `.agent/tasks/current-task.md`

## Implementation Steps

1. Confirm the singleton About Page request and response shape from `openapi.json`.
2. Reuse the singleton-editor approach from Homepage rather than the generic Pages CRUD flow.
3. Implement About Page form state, validation, request mapping, repeatable ordered array editing, and API validation error handling.
4. Wire the admin route and navigation so Homepage, About Us, and Pages sit under a `CMS` section.
5. Add focused tests for load, save, validation mapping, ordering, and navigation.
6. Run narrow validation.

## Acceptance Criteria

- About Page is managed as a singleton screen backed only by `/admin/about-page`.
- The save payload matches the documented service schema exactly, with no invented fields.
- Ordered editing is supported for stats, values, and team members.
- Validation errors from the service map correctly into the form.
- Navigation groups Homepage, About Us, and Pages under `CMS`.

## Validation Commands

- `php artisan test --filter=About`
- `php artisan test tests/Feature/Navigation/AdminNavigationTest.php`

## Risks

- The service contract includes several nested repeatable arrays, so array normalization and validation mapping must stay precise to avoid losing ordering or nested error paths.

## Completion Notes

- Added a dedicated singleton `AboutPageClient` using `GET /admin/about-page` and `PUT /admin/about-page`, without reusing the generic Pages API.
- Built a service-backed Livewire About Page editor with Hero, Mission, Stats, Values, Team, and SEO sections.
- Implemented ordered repeater editing for `stats_section.items`, `values_section.items`, and `team_section.members`, preserving payload order on save.
- Added focused feature and client tests for singleton load/save behavior, nested validation error mapping, null array normalization, and navigation access.
- Regrouped Homepage, About Us, and Pages under a new `CMS` navigation section.
- Local `openapi.json` does not yet expose `/admin/about-page`; implementation follows the backend contract provided in the task request.
