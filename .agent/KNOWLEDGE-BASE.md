# Admin Knowledge Base

Use this file for stable admin implementation decisions and reusable conventions.

## API Facts

- The admin consumes the Wide Web Blog Service API.
- The OpenAPI contract is the source of truth for endpoint shapes and payloads.
- Current admin-facing API modules include auth, admin status, categories, tags, posts, media, templates, knowledge base, SEO metadata, SEO score, schema, sitemap, and RSS.

## UI Decisions

- The admin UX is publishing-focused and low-noise.
- Management screens are table-first.
- Create and edit flows are editor-first where appropriate.
- Destructive and publish-state changes require safe confirmation behavior.

## Component Decisions

- Use shadcn/ui as a design reference only.
- Build reusable Blade components and Livewire-compatible patterns.
- Do not create React components.

## Auth / Session Decisions

- The service handles authentication and admin authorization.
- The admin should store bearer tokens server-side in the Laravel session.
- `401` handling should clear session state and return the user to login.

## Testing Decisions

- Prefer Livewire tests for screen behavior.
- Prefer HTTP fake tests for API client behavior.
- Keep validation focused on the changed area.

## Open Questions

- Exact command set once the Laravel application is bootstrapped: `TBC`
- Final folder and file layout after app bootstrap: `TBC`
- Topic queue and AI jobs API coverage: `TBC`
