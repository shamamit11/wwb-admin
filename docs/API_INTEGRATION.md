# Wide Web Blog Admin Panel API Integration

## Source of Truth

The service OpenAPI document in `/Users/amitsharma/Downloads/document (5).json` is the contract source for admin integration. Admin request names, payload shapes, and response expectations should track the OpenAPI contract directly.

## Base Configuration

- Base URL default: `http://localhost:8000/api/v1`
- Environment variable: `WIDEWEBBLOG_API_BASE_URL`
- All admin endpoint requests should be routed through a dedicated service client factory

Recommended config keys:

```php
'widewebblog' => [
    'base_url' => env('WIDEWEBBLOG_API_BASE_URL', 'http://localhost:8000/api/v1'),
    'timeout' => env('WIDEWEBBLOG_API_TIMEOUT', 15),
]
```

## Authentication Flow

Relevant endpoints:

- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`
- `GET /admin/me`

### Login

`/auth/login` accepts:

- `email`
- `password`
- `device_name` optional

The response returns an `AdminAccessTokenResource` containing:

- `token`
- `token_type`
- `abilities`
- `user`

### Session Strategy

Store the bearer token in the Laravel server session, not in local storage or plain cookies. The admin app should behave as a server-rendered authenticated client:

- user submits login form to Livewire
- Livewire calls auth API client
- token stored in session
- current user cached in session or refreshed through `/auth/me` or `/admin/me`
- middleware or bootstrap code redirects to login if token is missing or invalid

### Logout

- call `/auth/logout`
- clear stored bearer token and cached user
- invalidate and regenerate Laravel session

## Authorization and Identity

Use `/admin/me` when the screen requires confirmed admin access semantics. Use `/auth/me` when only authenticated-user hydration is needed.

If `/admin/me` returns `403`, the UI should treat the user as authenticated but unauthorized for the admin panel and redirect to a dedicated access-denied screen or safe logout path.

## Failure Handling

The service defines these standard error response shapes:

- `401`: `{ "message": "..." }`
- `403`: `{ "message": "..." }`
- `404`: `{ "message": "..." }`
- `422`: `{ "message": "...", "errors": { "field": ["..."] } }`

Recommended handling:

- `401`: clear token, invalidate session, redirect to login, and show a session-expired message
- `403`: show forbidden state and suppress destructive UI actions
- `404`: show missing-resource state and redirect to list page when appropriate
- `422`: map field errors into Livewire validation bags
- `500` or transport errors: show global error banner with retry action where sensible

## Validation Error Mapping

For `422` responses:

- map `errors[field]` to the matching Livewire property
- keep top-level `message` for a global banner
- support nested array fields for blocks and repeaters

Examples requiring careful mapping:

- post block arrays
- template block arrays
- tag or metadata arrays
- schedule date-time fields

## Pagination, Filtering, Search, Sorting

The current OpenAPI file does not yet describe explicit pagination/filter query parameter schemas for the admin listing endpoints. The admin should still prepare a consistent integration pattern:

- use query-string-backed Livewire state for `search`, `sort`, `direction`, `page`, and filters
- encapsulate query building in module client methods
- avoid hard-coding UI assumptions into raw URLs

When service pagination metadata becomes explicit, update the client response wrappers rather than rewriting page components.

## Multipart Upload Handling

Media endpoints:

- `POST /admin/media`
- `POST /admin/media/batch`

Payload expectations:

- single upload uses `file` plus metadata
- batch upload uses `files[]` plus shared metadata
- `source_type` is required and currently supports `uploaded`, `ai_generated`, `stock`

The admin should:

- build multipart requests only in the media client
- normalize uploaded file metadata into typed request objects before dispatch
- expose upload progress and post-upload refresh states in Livewire

## Module Coverage

The current service API supports these admin-facing modules:

### Auth

- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`

### Admin Status/Profile

- `GET /admin/me`

### Categories

- `GET /admin/categories`
- `POST /admin/categories`
- `GET /admin/categories/{category}`
- `PUT /admin/categories/{category}`
- `DELETE /admin/categories/{category}`

### Posts

- `GET /admin/posts`
- `POST /admin/posts`
- `GET /admin/posts/{post}`
- `PUT /admin/posts/{post}`
- `DELETE /admin/posts/{post}`
- `POST /admin/posts/{post}/publish`
- `POST /admin/posts/{post}/schedule`
- `POST /admin/posts/{post}/unpublish`

### Media

- `GET /admin/media`
- `POST /admin/media`
- `POST /admin/media/batch`
- `GET /admin/media/{media}`
- `PUT /admin/media/{media}`
- `DELETE /admin/media/{media}`

### Templates

- `GET /admin/templates`
- `POST /admin/templates`
- `GET /admin/templates/{template}`
- `PUT /admin/templates/{template}`
- `DELETE /admin/templates/{template}`
- `POST /admin/templates/{template}/preview`
- `POST /admin/templates/{template}/seed-post`

### Knowledge Base

- `GET /admin/knowledge-base`
- `POST /admin/knowledge-base`
- `GET /admin/knowledge-base/{knowledgeBase}`
- `PUT /admin/knowledge-base/{knowledgeBase}`
- `DELETE /admin/knowledge-base/{knowledgeBase}`
- `POST /admin/knowledge-base/{knowledgeBase}/link-post`
- `POST /admin/knowledge-base/{knowledgeBase}/link-topic`

### SEO Metadata

- `GET /admin/seo/{seoableType}/{seoableId}`
- `PUT /admin/seo/{seoableType}/{seoableId}`

### SEO Score

- `GET /admin/seo/score/{seoableType}/{seoableId}`

### Schema

- `GET /admin/seo/schema/{seoableType}/{seoableId}`

### Sitemap

- `GET /admin/seo/sitemap`

### RSS Feed

- `GET /admin/feeds/rss`

### Tags

- `GET /admin/tags`
- `POST /admin/tags`
- `GET /admin/tags/{tag}`
- `PUT /admin/tags/{tag}`
- `DELETE /admin/tags/{tag}`

## Payload Notes By Module

### Posts

The admin must support:

- status values: `draft`, `scheduled`, `published`, `unpublished`, `archived`
- visibility values: `public`, `private`, `internal`
- structured `blocks` arrays
- explicit schedule flow through `POST /admin/posts/{post}/schedule`

### Templates

Template types currently include:

- `standard`
- `tutorial`
- `listicle`
- `comparison`
- `news`

Template blocks and post blocks share a constrained block vocabulary:

- `heading`
- `paragraph`
- `image`
- `quote`
- `list`
- `code`
- `faq`
- `callout`

### Knowledge Base

Knowledge base entry types currently include:

- `note`
- `research`
- `experience`
- `architecture`
- `code`
- `reference`
- `idea`

### SEO

`seoable_type` should follow the service enum values:

- `post`
- `category`
- `knowledge_base_entry`

## Recommended Client Abstraction

Use one top-level service gateway with module-specific clients underneath:

```txt
Services/WideWebBlogApi/
├── WideWebBlogApiManager.php
├── Clients/
│   ├── AuthClient.php
│   ├── CategoryClient.php
│   ├── PostClient.php
│   ├── MediaClient.php
│   ├── TemplateClient.php
│   ├── KnowledgeBaseClient.php
│   ├── SeoClient.php
│   └── TagClient.php
└── Exceptions/
```

Each client should:

- know endpoint paths and verbs
- accept typed request data or explicit method arguments
- return normalized arrays or data objects
- throw translated API exceptions instead of leaking raw HTTP client details
