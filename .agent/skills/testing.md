# Skill: Testing

## When To Use

Use this skill whenever changes require validation strategy, new tests, or updated regression coverage.

## Files To Inspect

- `.agent/TESTING.md`
- `docs/TESTING.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/api-client-integration.md`

## Implementation Rules

- prefer the narrowest meaningful tests for the changed area
- use Livewire tests for screen behavior
- use HTTP fakes for API client tests
- verify auth/session expiration where relevant
- verify validation mapping for service-backed forms
- add focused regression coverage rather than broad speculative suites

## Common Mistakes To Avoid

- adding unrelated tests during a small task
- asserting excessive styling details
- skipping validation of error or auth edge cases when the task touches them

## Validation Checklist

- changed behavior has focused coverage or explicit manual validation
- API client paths and payloads are checked where relevant
- auth, upload, or destructive flows are tested when affected
- remaining validation gaps are recorded in the task file
