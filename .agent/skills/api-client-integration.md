# Skill: API Client Integration

## When To Use

Use this skill for any work that touches service API calls, request payloads, response mapping, or auth-aware client behavior.

## Files To Inspect

- `.agent/API-CONTRACT.md`
- `.agent/ARCHITECTURE.md`
- `docs/API_INTEGRATION.md`
- `docs/AUTHENTICATION.md`
- the current OpenAPI contract reference

## Implementation Rules

- keep service calls inside the API client layer
- follow the OpenAPI contract exactly
- map DTO or data objects where they reduce array sprawl
- handle `401`, `403`, `404`, and `422` consistently
- keep pagination, search, filters, and sort logic aligned with the contract
- do not invent endpoints or undocumented payload fields

## Common Mistakes To Avoid

- raw `Http::` calls scattered across Livewire components
- guessed request fields
- leaking raw HTTP client failures into page components

## Validation Checklist

- paths, verbs, and payload fields match the contract
- bearer token handling is correct where needed
- error mapping is consistent
- filters and pagination parameters are assembled correctly
