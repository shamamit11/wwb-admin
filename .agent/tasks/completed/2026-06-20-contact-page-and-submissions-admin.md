# Task: Implement Contact Page and Contact Submissions admin flows

Status: Completed

## Goal

Add Contact Page content management and Contact Submissions review/update flows using the existing service APIs and current admin UI patterns.

## Background

The admin already supports singleton content screens such as Homepage and About Page, plus list/detail operational screens. Contact Page content should follow the singleton content-settings pattern, while Contact Submissions should follow the existing service-backed list/detail management approach.

## Required Context

- `.agent/API-CONTRACT.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/shadcn-inspired-ui.md`

## Files To Inspect

- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- `app/Livewire/Admin/AboutPage/Index.php`
- `app/Livewire/Admin/Homepage/Index.php`
- `app/Livewire/Admin/Pages/Index.php`
- `app/Livewire/Admin/ContentBriefs/Show.php`
- `app/Livewire/Admin/TopicQueue/Show.php`
- `app/Services/WideWebBlogApi/Clients/*`
- related feature and client tests

## Files To Change

- Contact Page Livewire component, Blade view, and API client
- Contact Submissions Livewire list/detail components, Blade views, and API client
- `routes/web.php`
- `app/Support/Navigation/AdminNavigation.php`
- focused feature, client, and navigation tests

## Implementation Steps

1. Add dedicated service clients for Contact Page and Contact Submissions using the provided contract only.
2. Implement a singleton Contact Page content screen under `CMS`, following the existing content-settings pattern.
3. Implement Contact Submissions list and detail/update screens with exact status options: `new`, `read`, `archived`.
4. Wire routes and navigation.
5. Add focused tests for load, save, detail, patch/update, validation mapping, and navigation access.
6. Run narrow validation.

## Acceptance Criteria

- Contact Page content is manageable through a singleton admin screen backed by `/admin/contact-page`.
- Contact Submissions support list, detail, and PATCH update management using `/admin/contact-submissions`.
- UI follows existing admin patterns and does not invent backend fields.
- Validation errors from the service map cleanly into the forms.
- Contact Page appears under `CMS`.

## Validation Commands

- `php artisan test --filter=Contact`
- `php artisan test tests/Feature/Navigation/AdminNavigationTest.php`

## Risks

- The submissions detail flow includes nested metadata plus review audit fields, so response normalization must stay careful without guessing missing keys.

## Completion Notes

- Added dedicated `ContactPageClient` and `ContactSubmissionClient` integrations using only the provided `/admin/contact-page` and `/admin/contact-submissions` contracts.
- Built a singleton Contact Page editor under `CMS` following the existing content-settings pattern, including Hero, Contact Form, Contact Reasons, SEO, revision context, and ordered repeater editing for reasons.
- Built Contact Submissions list and detail/update screens, including full message display, metadata inspection, status updates, and admin notes patching with exact status options: `new`, `read`, and `archived`.
- Wired routes and navigation for Contact Page and Contact Submissions using existing admin layout and icon conventions.
- Verified the new flows with focused client, feature, and navigation tests.
