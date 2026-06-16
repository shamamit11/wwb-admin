# Wide Web Blog Admin Panel Architecture

## Architectural Style

The admin panel should be implemented as a Laravel 13 monolith using Livewire for interactive screens, Blade for rendering, Tailwind CSS for styling, and a reusable Blade component layer inspired by shadcn/ui patterns.

This is an admin client application, not a domain backend. The Wide Web Blog Service API is the backend source of truth. The admin should treat the API as the only persistence and business-rule boundary.

## Core Principles

- keep domain writes behind the service API
- build page behavior with Livewire, not ad hoc controller-heavy AJAX
- keep visual primitives in Blade components, not duplicated inside pages
- isolate API integration in dedicated client/service classes
- keep DTO/data objects explicit where payload normalization improves safety
- prefer predictable, testable composition over clever abstractions

## Recommended Structure

```txt
app/
├── Data/
│   ├── Auth/
│   ├── Categories/
│   ├── Media/
│   ├── Posts/
│   ├── Seo/
│   ├── Tags/
│   └── Templates/
├── Livewire/
│   └── Admin/
│       ├── Auth/
│       ├── Dashboard/
│       ├── Categories/
│       ├── KnowledgeBase/
│       ├── Media/
│       ├── Posts/
│       ├── Seo/
│       ├── Settings/
│       ├── Tags/
│       └── Templates/
├── Services/
│   └── WideWebBlogApi/
│       ├── Clients/
│       ├── Requests/
│       ├── Resources/
│       └── Exceptions/
├── Support/
│   ├── Auth/
│   ├── Navigation/
│   ├── Pagination/
│   └── Ui/
└── View/
    └── Components/
        ├── Admin/
        └── Ui/

resources/
├── css/
├── views/
│   ├── components/
│   │   ├── admin/
│   │   └── ui/
│   ├── layouts/
│   └── livewire/
│       └── admin/
└── js/
```

## Responsibility Split

### `app/Livewire/Admin`

Livewire components own screen behavior:

- query-string state for filters/search/sort
- form state and validation triggers
- submit and action methods
- loading, confirmation, and empty-state coordination
- mapping API results into screen view models

Keep one page-level Livewire component per screen or major editor flow. Use child Livewire components only where interaction boundaries are clear, such as media picker modals or SEO side panels.

### `app/View/Components` and `resources/views/components/ui`

Blade components own reusable UI primitives:

- buttons
- inputs
- fields
- cards
- badges
- tables
- dialogs/drawers
- tabs
- dropdowns
- pagination
- skeletons
- empty states

These components should implement shadcn-inspired structure and styling, but remain Blade-native and framework-agnostic inside the Laravel app.

### `app/Services/WideWebBlogApi`

This layer owns service communication:

- authenticated HTTP client configuration
- endpoint-specific clients grouped by module
- request payload building
- response normalization
- API exception translation
- multipart upload handling

Do not scatter `Http::` calls across Livewire components.

### `app/Data`

Use data objects for:

- typed request payload assembly
- typed response snapshots when service payloads are reused heavily
- select-option and table-row shaping where it prevents array sprawl

This does not need a heavy serialization framework on day one. Plain PHP data objects are sufficient.

### `app/Support`

Support classes hold cross-cutting concerns:

- admin navigation config
- token/session helpers
- date formatting helpers
- pagination parameter builders
- canonical filter parsing
- flash/toast mapping

## Layout Structure

Use a small number of stable layouts:

- guest auth layout for login
- admin app layout with sidebar and compact top bar
- full-width editor layout for post and template editing when needed

The layout should centralize:

- sidebar navigation
- top bar search slot
- user menu
- global flash/toast area
- global error banner region

## Auth and Session Flow

- The login screen submits credentials to `/auth/login`.
- The returned bearer token is stored server-side in the Laravel session, not in browser local storage.
- Subsequent API requests attach the bearer token through the API client layer.
- The app should hydrate the current admin user from `/auth/me` or `/admin/me`.
- Logout calls `/auth/logout`, clears the stored token, and invalidates the local Laravel session.

## Error Handling Strategy

Normalize API failures into a small set of admin-side exception types:

- unauthenticated
- unauthorized
- validation failed
- not found
- service unavailable or unexpected server error

Livewire pages should not parse raw HTTP responses inline. The API layer should translate failures into predictable objects or exceptions that screens can render consistently.

## Validation Strategy

- Use Livewire field-level validation for immediate UX help on blur where appropriate.
- Re-run full validation on submit.
- Map API `422` field errors back onto Livewire error bags.
- Preserve authoritative service messages when they are user-safe and field-specific.

## File Upload Strategy

Media uploads should use multipart requests through a dedicated media client:

- single upload via `/admin/media`
- batch upload via `/admin/media/batch`

Upload concerns:

- Livewire temporary upload handling in the admin app
- conversion to multipart request bodies in the API layer
- consistent progress/loading states
- size/type failure rendering based on API response or client-side prechecks

## Screen Composition Guidance

- page component: owns data loading, actions, filters, and view assembly
- section partials: own repeated layout chunks within the page
- UI components: own visual consistency and accessibility
- API clients: own transport, endpoint naming, and request/response handling

That separation prevents page classes from becoming transport + domain + UI blobs.
