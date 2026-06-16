# Wide Web Blog Admin Panel Authentication

## Objective

Provide a secure admin session experience while treating the service API as the authority for authentication and admin access.

## Service Endpoints

- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`
- `GET /admin/me`

## Recommended Model

Use Laravel session authentication for the admin app shell, backed by a service-issued bearer token stored in the server session.

The admin app should not issue its own independent user auth credentials for the editorial domain. It should broker the service token into a normal Laravel session experience.

## Login Flow

1. User submits email and password on the Livewire login screen.
2. The admin auth client sends the credentials to `/auth/login`.
3. On success, store the returned bearer token in the Laravel session.
4. Fetch `/admin/me` or `/auth/me` to hydrate the current user.
5. Store a normalized current-user snapshot in session or an auth support service.
6. Redirect to the dashboard.

## Token Storage

Store these server-side only:

- bearer token
- token type if needed
- normalized user snapshot
- optional ability list

Do not store bearer tokens in:

- local storage
- session storage
- client-readable plain cookies

## Middleware Expectations

Create admin auth middleware that:

- checks for a stored bearer token
- optionally refreshes current-user context when the session starts
- redirects guests to login
- clears invalid sessions on `401`

An additional middleware or gate layer should verify that the authenticated user is actually an admin by using `/admin/me`.

## Logout Flow

1. Call `/auth/logout` when a valid token exists.
2. Clear the stored token and current-user snapshot even if the remote logout call fails.
3. Invalidate and regenerate the Laravel session.
4. Redirect to login.

## Session Expiry Behavior

If any authenticated request returns `401`:

- clear the token immediately
- invalidate the local session
- redirect to login
- show a concise session-expired message

This should happen centrally in the API client or an exception handler, not inside each page.

## Unauthorized Admin Behavior

If `/admin/me` returns `403`:

- treat the user as authenticated but not authorized for this app
- prevent access to admin screens
- display an access-denied state or log out safely depending on product preference

## Remember Me

The UI spec includes a `remember me` checkbox. Because the service login request currently accepts `email`, `password`, and optional `device_name` only, treat remember-me behavior as an admin-app session duration concern unless the service adds a dedicated remember-token contract.

If implemented:

- extend Laravel session lifetime conditionally
- do not fabricate unsupported service-side semantics

## CSRF and Browser Security

- keep standard Laravel CSRF protection for the admin web app
- use secure cookies in non-local environments
- set `same_site` and session security config appropriately
- avoid exposing the service token to browser JavaScript unless a later feature absolutely requires it

## Audit Notes

When later requirements introduce audit trails, the admin should rely on the service’s authenticated identity model rather than inventing local user attribution separate from the service user record.
