# API Contract Guidance

## Source Of Truth

The Wide Web Blog Service API is the source of truth for admin-facing endpoints, payloads, and response shapes.

Before making API-related changes, check the OpenAPI contract file referenced by the project docs.

## Base Rules

- base API URL must come from config or environment, not hard-coded in page components
- do not invent endpoints
- do not invent payload fields
- request and response naming should follow the service contract

## Auth Endpoints

- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`
- `GET /admin/me`

## Admin Module Coverage

- categories
- tags
- posts
- media
- templates
- knowledge base
- SEO metadata
- SEO score
- schema
- sitemap
- RSS

## Error Handling Rules

- `401`: clear session or token state and redirect to login
- `403`: show authorization failure state
- `404`: show not-found behavior appropriate to the screen
- `422`: map field errors to Livewire validation/messages
- `500` or transport failures: show safe retryable error messaging

## Multipart Rules

Media uploads must use multipart handling through the API client layer for:

- `POST /admin/media`
- `POST /admin/media/batch`

## Filters, Search, Sort, Pagination

These should follow the API contract when explicit query conventions exist. Where the OpenAPI file is not yet explicit, keep the client abstraction flexible and avoid scattering query construction across screens.

## Guardrails

- check the OpenAPI contract before adding or changing API behavior
- do not guess future service capabilities
- use `TBC` in docs or task notes when a contract detail is not yet confirmed

Read `docs/API_INTEGRATION.md` for the fuller contract summary before larger integration work.
