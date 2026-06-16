# Skill: Auth Session

## When To Use

Use this skill for login, logout, guard, token, middleware, and current-user behavior.

## Files To Inspect

- `.agent/API-CONTRACT.md`
- `.agent/ARCHITECTURE.md`
- `docs/AUTHENTICATION.md`
- `docs/API_INTEGRATION.md`

## Implementation Rules

- use `/auth/login`, `/auth/logout`, `/auth/me`, and `/admin/me` as the auth contract
- store the bearer token server-side in the Laravel session
- clear session state on `401`
- treat `403` as an authorization problem
- keep redirect behavior explicit and centralized

## Common Mistakes To Avoid

- storing bearer tokens in browser local storage
- duplicating auth checks across many page components
- assuming remember-me semantics not supported by the service contract

## Validation Checklist

- login success path works
- invalid login path is safe and generic
- session-expired behavior clears auth state
- unauthorized-admin behavior is handled correctly
