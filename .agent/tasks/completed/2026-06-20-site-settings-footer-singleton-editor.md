# Task: Implement site-settings footer singleton admin editor

Status: Completed

## Goal

Add admin UI support for the singleton `site-settings` service API, focused on editing footer configuration through the real `/admin/site-settings` endpoints.

## Background

The admin already has singleton editors for Homepage, About Page, and Contact Page. `site-settings` should follow that same service-backed singleton pattern rather than using Pages or inventing extra fields.

## Required Context

- `.agent/API-CONTRACT.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/shadcn-inspired-ui.md`

## Files To Inspect

- `app/Livewire/Admin/Homepage/Index.php`
- `app/Livewire/Admin/AboutPage/Index.php`
- `app/Livewire/Admin/ContactPage/Index.php`
- `app/Support/Navigation/AdminNavigation.php`
- `routes/web.php`
- `app/Services/WideWebBlogApi/Clients/*`
- related singleton editor tests

## Files To Change

- Site settings Livewire component, Blade view, and API client
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- focused feature, client, and navigation tests

## Implementation Steps

1. Add a dedicated service client for `GET` and `PUT /admin/site-settings`.
2. Implement a singleton footer editor with fields only for `footer.brand_name`, `footer.description`, `footer.social_links`, and `footer.legal_links`.
3. Surface revision context from `updated_at` and `updated_by`.
4. Wire route and CMS navigation entry.
5. Add focused tests for load, save, validation mapping, and navigation access.
6. Run narrow validation.

## Acceptance Criteria

- Admin can load and save footer settings through `/admin/site-settings`.
- Form state and payload match the service contract exactly without invented fields.
- Social links and legal links support ordered repeatable editing.
- Validation and transport errors surface cleanly.
- Screen follows current singleton editor conventions under `CMS`.

## Validation Commands

- `php artisan test --filter=SiteSettings`
- `php artisan test tests/Feature/Navigation/AdminNavigationTest.php`

## Risks

- Legal links support either `slug` or `url` in the contract, so the form must preserve both fields exactly without imposing unsupported assumptions.

## Completion Notes

- Added a dedicated `SiteSettingsClient` for `GET` and `PUT /admin/site-settings`.
- Built a singleton `Site Settings` editor under the settings area with footer brand name, description, social links, legal links, revision context, and ordered repeater controls.
- Kept `url` fields as generic strings to match the service contract, including support for values such as `mailto:` and relative paths.
- Updated the existing Settings summary screen to point editors to the dedicated Site Settings singleton instead of implying that no writable settings flows exist.
- Verified the new flow with focused client, feature, navigation, and settings-screen tests.
