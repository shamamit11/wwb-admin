# Admin Testing Guidance

## Default Approach

- prefer narrow tests for the changed area
- use Livewire component tests for admin screens
- use HTTP fake tests for API clients
- verify validation mapping when forms talk to the service
- verify auth/session expiration behavior when auth or guarded pages change

## Areas To Cover When Relevant

- table filter/search/sort behavior
- pagination state
- media upload behavior
- destructive confirmation flows
- publish/schedule/unpublish flows
- SEO metadata editing flows

## Guardrails

- do not add broad unrelated test suites during small tasks
- do not rewrite existing tests unless the task requires it
- prioritize regression coverage around the behavior being changed

Read `docs/TESTING.md` and `.agent/skills/testing.md` before adding or changing validation coverage.
