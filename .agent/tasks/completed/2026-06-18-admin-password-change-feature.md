# Task: Implement Admin Password Change Feature

Status: Completed

## Goal

Add a simple Admin Password page where the authenticated Admin user can change their password through the existing Service API.

## Background

- `openapi.json` now documents `POST /admin/change-password`.
- The request contract is `current_password`, `password`, and `password_confirmation`.
- The user dropdown already contains a `Change Password` placeholder entry.
- Scope is intentionally limited to password change only. No profile, email, avatar, or broader settings work is in scope.

## Required Context

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/auth-session.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/laravel-livewire.md`
- `docs/API_INTEGRATION.md`
- `docs/AUTHENTICATION.md`
- `openapi.json`

## Files To Inspect

- `app/Services/WideWebBlogApi/Clients/AuthClient.php`
- `routes/web.php`
- `resources/views/components/admin/user-chip.blade.php`
- `app/Livewire/Admin/Settings/Index.php`
- `tests/Feature/Auth/AuthFlowTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`

## Files To Change

- `.agent/tasks/current-task.md`
- `app/Services/WideWebBlogApi/Clients/AuthClient.php`
- `routes/web.php`
- `app/Livewire/Admin/Password/Index.php`
- `resources/views/livewire/admin/password/index.blade.php`
- `resources/views/components/admin/user-chip.blade.php`
- `tests/Feature/Auth/AdminPasswordTest.php`
- `tests/Feature/Navigation/AdminNavigationTest.php`
- `openapi.json`

## Implementation Steps

1. Add an authenticated auth-client method for the documented change-password endpoint.
2. Add a dedicated Admin Password route and Livewire screen.
3. Replace the user-dropdown placeholder with a real link to the password page.
4. Add field validation, submit loading state, and API validation/error mapping.
5. Add focused route and Livewire tests, then run narrow validation.

## Acceptance Criteria

- Authenticated Admin user can open a password-change page.
- Admin user can submit current password, new password, and confirmation.
- Success feedback is clear.
- Validation and transport errors are surfaced safely.
- No unrelated profile-management UI is introduced.

## Validation Commands

- `php artisan test tests/Feature/Auth/AdminPasswordTest.php tests/Feature/Navigation/AdminNavigationTest.php`

## Risks

- Service validation rules beyond the documented required fields may still reject weak passwords; the Admin screen should rely on API validation mapping rather than invent unsupported local rules.

## Completion Notes

- Added `AuthClient::changePassword()` for the documented `POST /admin/change-password` endpoint.
- Added a dedicated authenticated password screen and route.
- Replaced the `Change Password` dropdown placeholder with a real link to the new page.
- Kept the scope password-only with no profile-management spillover.
- Added focused feature coverage for route rendering, success submission, and API validation mapping.
- Validation passed:
  - `php artisan test tests/Feature/Auth/AdminPasswordTest.php tests/Feature/Navigation/AdminNavigationTest.php`
  - `git diff --check`
