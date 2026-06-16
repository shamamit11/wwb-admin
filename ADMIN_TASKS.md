# ADMIN_TASKS.md — Wide Web Blog Admin Panel

## Purpose

`ADMIN_TASKS.md` is the implementation roadmap for the Wide Web Blog Admin Panel. It is written for coding agents and should be used together with `.agent/INDEX.md`.

This file does not replace the service OpenAPI contract, the admin UI/UX specification, or the project docs under `docs/`. It provides a safe, incremental build plan so agents can implement the Laravel 13 + Livewire admin in small, verifiable steps.

## Current Context

- The service backend already exists and has implemented publishing and SEO services up to Phase 2.
- The admin app consumes the service API and must not duplicate service business logic.
- The admin app should focus on UI, session/auth, API client integration, and editorial workflows.
- The service API is the source of truth for endpoints, payloads, validation errors, and supported operations.
- The admin UX is publishing-focused: low noise, table-first management, editor-first creation, explicit status changes, and safe destructive actions.
- MVP admin modules:
  - auth
  - dashboard
  - categories
  - tags
  - media
  - templates
  - posts
  - publishing actions
  - SEO metadata
  - knowledge base
- Later or placeholder modules:
  - topic queue
  - AI jobs
  - advanced settings

## Implementation Rules

- Read `.agent/INDEX.md` first.
- Create or update `.agent/tasks/current-task.md` before implementation.
- Read only the relevant docs and skills for the active task.
- Follow the OpenAPI contract for endpoints and payloads.
- Follow the Admin UI/UX spec for screen behavior.
- Use reusable Blade components.
- Use Livewire for stateful admin interactions.
- Use Tailwind CSS.
- Use shadcn/ui as a visual reference only.
- Do not create React or Next.js code.
- Avoid unrelated refactors.
- Avoid broad repository scans.
- Add narrow tests for changed behavior.
- Run only relevant validation commands.
- Update `.agent/MEMORY.md` only for stable facts.
- Update `.agent/AGENT-HANDOVER.md` if work is incomplete.
- Mark unverified assumptions as `TBC`.

## Task Phases

### Phase 0 — Project Setup and Agent Readiness

#### Task 0.1 — Confirm Laravel app bootstrap state

**Goal**  
Establish whether the admin repository already contains a Laravel 13 application shell or still needs bootstrap work.

**Context**  
Current docs describe the target structure, but the repository may still be documentation-only.

**Relevant docs / skills**  
- `.agent/INDEX.md`
- `.agent/FOLDER-STRUCTURE.md`
- `.agent/COMMANDS.md`
- `.agent/skills/laravel-livewire.md`
- `docs/FOLDER_STRUCTURE.md`
- `docs/IMPLEMENTATION_ROADMAP.md`

**Files likely involved**  
- `composer.json`
- `package.json`
- `artisan`
- `bootstrap/`
- `config/`
- `routes/`
- `TBC if app not bootstrapped yet`

**Implementation notes**  
- verify whether the Laravel app exists
- verify whether Livewire, Tailwind, and Vite are already installed
- verify actual command availability before using docs examples
- record confirmed bootstrap facts in the task note

**Acceptance criteria**  
- [ ] repository bootstrap state is confirmed
- [ ] missing foundation pieces are identified without implementing features
- [ ] future tasks can assume a concrete starting point

**Validation**  
- inspect actual repo files and scripts
- `TBC until commands are confirmed`

**Risks / assumptions**  
- current repository may still be doc-only

#### Task 0.2 — Establish base config and environment scaffolding

**Goal**  
Create the non-feature foundation for app config, environment keys, and shared service integration config.

**Context**  
The admin depends on service API base URL, auth/session handling, and predictable environment wiring.

**Relevant docs / skills**  
- `.agent/ARCHITECTURE.md`
- `.agent/API-CONTRACT.md`
- `.agent/COMMANDS.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/auth-session.md`
- `docs/API_INTEGRATION.md`
- `docs/AUTHENTICATION.md`

**Files likely involved**  
- `config/widewebblog.php`
- `.env.example`
- `config/services.php`
- `config/session.php`
- `TBC`

**Implementation notes**  
- add base API config entries
- add timeout and auth-related config as needed
- keep all service URLs environment-driven
- do not hard-code dev URLs in page classes

**Acceptance criteria**  
- [ ] service base URL config exists
- [ ] environment keys for API access are documented in code
- [ ] no feature code depends on hard-coded service URLs

**Validation**  
- config inspection
- `php artisan config:clear` if available
- `TBC until commands are confirmed`

**Risks / assumptions**  
- final config file names may vary slightly after bootstrap

### Phase 1 — Layout, Authentication, and API Client Foundation

#### Task 1.1 — Build the service API client foundation

**Goal**  
Create the shared API client layer used by all admin modules.

**Context**  
Livewire pages should not scatter raw `Http::` calls. The admin needs module clients, error translation, auth header handling, and multipart support.

**Relevant docs / skills**  
- `.agent/ARCHITECTURE.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/testing.md`
- `docs/ARCHITECTURE.md`
- `docs/API_INTEGRATION.md`

**Files likely involved**  
- `app/Services/WideWebBlogApi/WideWebBlogApiManager.php`
- `app/Services/WideWebBlogApi/Clients/*`
- `app/Services/WideWebBlogApi/Exceptions/*`
- `app/Data/*`
- `tests/Integration/*`

**Implementation notes**  
- create a shared HTTP client factory
- centralize bearer token injection
- translate `401`, `403`, `404`, `422`, and server errors
- leave room for pagination/filter abstraction
- include multipart support for media endpoints

**Acceptance criteria**  
- [ ] API client layer exists and is reusable
- [ ] error translation is centralized
- [ ] future module work can call dedicated clients instead of raw HTTP

**Validation**  
- focused API client tests with HTTP fakes
- `TBC until commands are confirmed`

**Risks / assumptions**  
- pagination metadata shape is still partially `TBC` in the OpenAPI contract

#### Task 1.2 — Implement admin session and auth flow

**Goal**  
Implement login, logout, token storage, current-user hydration, and protected-route behavior.

**Context**  
The admin must use `/auth/login`, `/auth/logout`, `/auth/me`, and `/admin/me` with server-side session token storage.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/auth-session.md`
- `.agent/skills/forms-validation.md`
- `docs/AUTHENTICATION.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Auth/*`
- `app/Support/Auth/*`
- `app/Http/Middleware/*`
- `resources/views/livewire/admin/auth/*`
- `routes/web.php`
- `tests/Feature/Auth/*`

**Implementation notes**  
- create the login screen and submit flow
- store bearer tokens server-side
- fetch current admin identity
- handle `401` and `403` centrally
- keep invalid credentials generic and user-safe

**Acceptance criteria**  
- [ ] login works against the service API
- [ ] protected screens redirect guests
- [ ] logout clears local session state
- [ ] unauthorized admin state is handled safely

**Validation**  
- auth feature tests
- auth API client tests
- `TBC until commands are confirmed`

**Risks / assumptions**  
- remember-me behavior is limited by the current service contract

#### Task 1.3 — Create guest and admin layouts

**Goal**  
Create the baseline layout system for login and authenticated admin screens.

**Context**  
All later UI work depends on stable guest and admin shells.

**Relevant docs / skills**  
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/ARCHITECTURE.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `resources/views/layouts/*`
- `resources/views/components/admin/*`
- `resources/css/*`
- `TBC`

**Implementation notes**  
- create guest auth layout
- create admin shell with sidebar and compact top bar
- provide slots for search, user menu, page header, flash/toast, and error banner
- keep mobile navigation responsive and minimal

**Acceptance criteria**  
- [ ] guest and admin layouts exist
- [ ] layout regions match the UX spec
- [ ] later screens can mount into a stable shell

**Validation**  
- manual render verification
- narrow view or Livewire smoke tests if available
- `TBC until commands are confirmed`

**Risks / assumptions**  
- final visual tokens may evolve during component-system work

### Phase 2 — Reusable shadcn-inspired Blade Component System

#### Task 2.1 — Build the first UI primitive set

**Goal**  
Implement the first reusable Blade UI primitives needed across most screens.

**Context**  
The docs already define the first component set and naming conventions.

**Relevant docs / skills**  
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/blade-components.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/COMPONENT_SYSTEM.md`

**Files likely involved**  
- `resources/views/components/ui/*`
- `app/View/Components/Ui/*`
- `resources/css/*`

**Implementation notes**  
- build button, input, textarea, select, field, card, badge
- define variants and shared state classes
- keep accessibility and error states first-class
- avoid overbuilding component APIs

**Acceptance criteria**  
- [ ] core form and display primitives exist
- [ ] variants are consistent and reusable
- [ ] components support invalid, disabled, and focus states where relevant

**Validation**  
- component render checks
- narrow visual/manual verification
- `TBC until commands are confirmed`

**Risks / assumptions**  
- exact Tailwind token setup may be `TBC` until app bootstrap is complete

#### Task 2.2 — Build shared management primitives

**Goal**  
Create the shared structural components used on all management screens.

**Context**  
Categories, tags, media, templates, posts, and knowledge base all depend on the same table/filter/confirmation patterns.

**Relevant docs / skills**  
- `.agent/UI-UX-RULES.md`
- `.agent/COMPONENT-SYSTEM.md`
- `.agent/skills/tables-filters-pagination.md`
- `.agent/skills/blade-components.md`
- `docs/COMPONENT_SYSTEM.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `resources/views/components/ui/table*`
- `resources/views/components/admin/*`
- `resources/views/components/ui/dialog*`
- `resources/views/components/ui/pagination*`

**Implementation notes**  
- build table, pagination, empty state, skeleton, dialog, drawer, tabs, dropdown, sidebar pieces
- build admin page header, filter bar, row actions, status badge, SEO score badge
- standardize toolbar and action placement

**Acceptance criteria**  
- [ ] shared CRUD screen primitives exist
- [ ] confirmation and loading patterns are standardized
- [ ] table-first management screens can be composed without duplication

**Validation**  
- component smoke checks
- targeted manual checks
- `TBC until commands are confirmed`

**Risks / assumptions**  
- some composites may expand as module-specific needs become concrete

### Phase 3 — Dashboard and Navigation Shell

#### Task 3.1 — Implement sidebar navigation and route skeleton

**Goal**  
Create the admin navigation structure and placeholder route map for MVP modules.

**Context**  
The dashboard and all management sections need a stable route and nav foundation.

**Relevant docs / skills**  
- `.agent/PROJECT-CONTEXT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/FOLDER-STRUCTURE.md`
- `.agent/skills/laravel-livewire.md`
- `docs/FOLDER_STRUCTURE.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `routes/web.php`
- `app/Support/Navigation/*`
- `resources/views/components/admin/sidebar*`
- `app/Livewire/Admin/*`

**Implementation notes**  
- define dashboard, categories, tags, media, templates, posts, knowledge base, SEO, and settings nav entries
- mark topic queue and AI jobs as placeholders if added now
- keep auth and guest routes separate from protected admin routes

**Acceptance criteria**  
- [ ] route and nav skeleton covers MVP areas
- [ ] current-section highlighting is supported
- [ ] placeholder modules are visually clear and non-deceptive

**Validation**  
- route inspection
- layout/manual nav checks
- `TBC until commands are confirmed`

**Risks / assumptions**  
- exact route names may be refined during implementation

#### Task 3.2 — Implement the dashboard MVP

**Goal**  
Build the first working authenticated dashboard screen.

**Context**  
The dashboard should emphasize actions over vanity metrics and can use placeholders where service APIs do not yet exist.

**Relevant docs / skills**  
- `.agent/UI-UX-RULES.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/api-client-integration.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/PROJECT_SCOPE.md`

**Files likely involved**  
- `app/Livewire/Admin/Dashboard/*`
- `resources/views/livewire/admin/dashboard/*`
- `app/Services/WideWebBlogApi/Clients/*`
- `tests/Feature/Dashboard/*`

**Implementation notes**  
- show top summary cards with available data or `TBC` placeholders
- include recent drafts and published posts if supported by the current posts API usage pattern
- keep topic queue and AI jobs widgets placeholder-only unless service support is added

**Acceptance criteria**  
- [ ] dashboard loads after login
- [ ] dashboard uses available service data without inventing APIs
- [ ] placeholders are clearly labeled where backend support is absent

**Validation**  
- dashboard feature test or smoke test
- API client fake tests where needed
- `TBC until commands are confirmed`

**Risks / assumptions**  
- dashboard aggregation endpoints do not currently exist as dedicated APIs

### Phase 4 — Categories and Tags Management

#### Task 4.1 — Implement category index and form flows

**Goal**  
Build category listing, create, edit, and delete flows.

**Context**  
Categories are a foundational taxonomy and unblock post workflows.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/tables-filters-pagination.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Categories/*`
- `resources/views/livewire/admin/categories/*`
- `app/Services/WideWebBlogApi/Clients/CategoryClient.php`
- `tests/Feature/Categories/*`
- `tests/Integration/*Category*`

**Implementation notes**  
- implement index, create, edit, delete
- support search/filter/sort only if the API pattern is confirmed or the UI state is designed to be extensible
- use confirmation for delete or archive behavior
- do not invent embedded SEO fields on the category payload if they must come from SEO metadata endpoints

**Acceptance criteria**  
- [ ] category CRUD works against the documented endpoints
- [ ] validation errors map correctly
- [ ] delete flow is confirmed and safe

**Validation**  
- focused category feature tests
- category client HTTP fake tests

**Risks / assumptions**  
- category SEO fields are `TBC` and may require separate SEO integration work

#### Task 4.2 — Implement tag index and form flows

**Goal**  
Build tag listing, create, edit, and delete flows.

**Context**  
Tags share much of the category management pattern and should reuse the same management primitives.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/tables-filters-pagination.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Tags/*`
- `resources/views/livewire/admin/tags/*`
- `app/Services/WideWebBlogApi/Clients/TagClient.php`
- `tests/Feature/Tags/*`
- `tests/Integration/*Tag*`

**Implementation notes**  
- mirror the category management pattern where appropriate
- keep status and active-state presentation consistent
- reuse table, form, and confirmation primitives

**Acceptance criteria**  
- [ ] tag CRUD works against the documented endpoints
- [ ] tag screens match the shared management UX
- [ ] no duplicated ad hoc UI patterns are introduced

**Validation**  
- focused tag feature tests
- tag client HTTP fake tests

**Risks / assumptions**  
- filter and pagination behavior remain partially `TBC` until contract details are explicit

### Phase 5 — Media Library

#### Task 5.1 — Implement media index, detail, and metadata edit flows

**Goal**  
Build the media library browsing and metadata management experience.

**Context**  
Posts, templates, and knowledge base entries need media selection and reuse.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/media-upload.md`
- `.agent/skills/tables-filters-pagination.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Media/*`
- `resources/views/livewire/admin/media/*`
- `app/Services/WideWebBlogApi/Clients/MediaClient.php`
- `tests/Feature/Media/*`

**Implementation notes**  
- build searchable media list or grid
- include selected-asset detail panel or drawer
- support metadata editing and delete confirmation
- show usage count if available from the current API response

**Acceptance criteria**  
- [ ] media list renders service data correctly
- [ ] metadata editing works
- [ ] delete flow is safe and confirmed

**Validation**  
- media feature tests
- media client tests with HTTP fakes

**Risks / assumptions**  
- exact search/filter query parameters may be `TBC`

#### Task 5.2 — Implement single and batch media upload flows

**Goal**  
Support service-backed single and multi-file uploads with metadata and progress states.

**Context**  
The API already supports `/admin/media` and `/admin/media/batch`.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/skills/media-upload.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/testing.md`
- `docs/API_INTEGRATION.md`
- `docs/TESTING.md`

**Files likely involved**  
- `app/Livewire/Admin/Media/*`
- `app/Services/WideWebBlogApi/Clients/MediaClient.php`
- `tests/Feature/Media/*`
- `tests/Integration/*Media*`

**Implementation notes**  
- add single upload flow
- add batch upload flow
- map upload validation errors clearly
- keep multipart assembly inside the API client layer

**Acceptance criteria**  
- [ ] single upload uses the correct multipart payload
- [ ] batch upload uses the correct multipart payload
- [ ] upload errors and loading states are clear

**Validation**  
- upload-focused feature tests
- multipart client tests with HTTP fakes

**Risks / assumptions**  
- progress UX details may vary by Livewire version and setup

### Phase 6 — Template Management

#### Task 6.1 — Implement template index and editor flows

**Goal**  
Build template CRUD and ordered block configuration.

**Context**  
Templates support repeatable editorial structure and seed post creation.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Templates/*`
- `resources/views/livewire/admin/templates/*`
- `app/Services/WideWebBlogApi/Clients/TemplateClient.php`
- `tests/Feature/Templates/*`

**Implementation notes**  
- implement list, create, edit, delete
- support template type, status, meta defaults, and ordered block configuration
- avoid page-builder behavior; keep the editor structured

**Acceptance criteria**  
- [ ] template CRUD works against documented endpoints
- [ ] ordered block configuration is preserved in payloads
- [ ] UX remains structured and not drag-heavy by default

**Validation**  
- template feature tests
- template client tests with HTTP fakes

**Risks / assumptions**  
- block configuration UI details may need iteration after first pass

#### Task 6.2 — Implement template preview and seed-post actions

**Goal**  
Support previewing templates and seeding post payloads from them.

**Context**  
The service exposes `preview` and `seed-post` endpoints for templates.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/laravel-livewire.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Templates/*`
- `app/Services/WideWebBlogApi/Clients/TemplateClient.php`
- `tests/Feature/Templates/*`

**Implementation notes**  
- implement preview action
- implement seed-post action
- render preview safely and clearly
- ensure seeded post flow integrates cleanly with later post editor work

**Acceptance criteria**  
- [ ] preview action uses the documented endpoint
- [ ] seed-post action uses the documented endpoint
- [ ] output is usable without inventing extra backend behavior

**Validation**  
- template action tests
- client tests with HTTP fakes

**Risks / assumptions**  
- exact UX for preview rendering may be `TBC` until first implementation pass

### Phase 7 — Post Management and Block Editor

#### Task 7.1 — Implement posts index and management actions shell

**Goal**  
Build the post listing screen with filters, actions, and routing into the editor.

**Context**  
Posts are the central editorial workflow and depend on categories, tags, templates, and media support.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/tables-filters-pagination.md`
- `.agent/skills/post-editor.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Posts/Index.php`
- `resources/views/livewire/admin/posts/index.blade.php`
- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `tests/Feature/Posts/*`

**Implementation notes**  
- implement list rendering
- support search/filter/sort scaffolding
- show status, category, author, SEO score, publish/update timestamps where available
- include actions leading into edit, publish, schedule, unpublish, and delete flows

**Acceptance criteria**  
- [ ] posts index works against the documented API
- [ ] table behavior follows the shared management pattern
- [ ] action affordances are clear and safe

**Validation**  
- post index feature tests
- post client tests with HTTP fakes

**Risks / assumptions**  
- exact list query parameters may still be `TBC`

#### Task 7.2 — Implement the post editor foundation

**Goal**  
Create the post create/edit experience with block editing and a sticky metadata side panel.

**Context**  
This is the most complex MVP screen and should be built after dependencies are in place.

**Relevant docs / skills**  
- `.agent/UI-UX-RULES.md`
- `.agent/API-CONTRACT.md`
- `.agent/skills/post-editor.md`
- `.agent/skills/forms-validation.md`
- `.agent/skills/laravel-livewire.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Posts/*`
- `resources/views/livewire/admin/posts/*`
- `app/Data/Posts/*`
- `tests/Feature/Posts/*`

**Implementation notes**  
- support title, slug, excerpt, category, tags, template, featured media, visibility
- support block arrays aligned to the service contract
- include status/SEO/media metadata panel
- design the editor for future autosave without implementing autosave now

**Acceptance criteria**  
- [ ] create and edit flows work
- [ ] block payloads align to the service contract
- [ ] validation mapping is clear for nested block structures
- [ ] editor shell matches the documented UX direction

**Validation**  
- post editor feature tests
- nested validation tests
- `TBC until commands are confirmed`

**Risks / assumptions**  
- richer block editing affordances may evolve after the first contract-aligned version

### Phase 8 — Publishing Actions and SEO Management

#### Task 8.1 — Implement publish, schedule, unpublish, and delete flows

**Goal**  
Complete the post state transition actions and required confirmations.

**Context**  
The service exposes dedicated publish/schedule/unpublish endpoints and the UX requires explicit confirmation.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/post-editor.md`
- `.agent/skills/forms-validation.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Posts/*`
- `app/Services/WideWebBlogApi/Clients/PostClient.php`
- `tests/Feature/Posts/*`

**Implementation notes**  
- wire publish action
- wire schedule action with date-time validation
- wire unpublish action
- ensure delete flow remains separate and explicit
- centralize confirmation messaging

**Acceptance criteria**  
- [ ] publish flow works
- [ ] schedule flow validates required date-time
- [ ] unpublish flow works
- [ ] destructive or state-changing flows require confirmation

**Validation**  
- publish/schedule/unpublish feature tests
- post action client tests

**Risks / assumptions**  
- duplicate action support from the UX spec is `TBC` because no dedicated endpoint currently exists

#### Task 8.2 — Implement per-entity SEO metadata editing

**Goal**  
Enable metadata editing for supported seoable entities using the service SEO endpoints.

**Context**  
The service supports per-entity SEO metadata, score, and schema retrieval.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/skills/seo-admin.md`
- `.agent/skills/forms-validation.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Seo/*`
- `app/Livewire/Admin/Posts/*`
- `app/Services/WideWebBlogApi/Clients/SeoClient.php`
- `tests/Feature/Seo/*`

**Implementation notes**  
- support metadata retrieval and update
- support meta title, description, canonical URL, robots flags, OpenGraph fields, focus keyword
- integrate SEO editing into posts and later categories/knowledge base where needed

**Acceptance criteria**  
- [ ] SEO metadata can be loaded and updated for supported entity types
- [ ] unsupported global SEO settings are not faked
- [ ] validation and error states are clear

**Validation**  
- SEO client tests
- focused SEO feature tests

**Risks / assumptions**  
- global sitewide SEO defaults remain `TBC` until backed by service endpoints

#### Task 8.3 — Implement SEO score and schema visibility

**Goal**  
Expose SEO score and schema output in operational admin workflows.

**Context**  
The service already supports score and schema reads but not necessarily list-wide SEO review endpoints.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/skills/seo-admin.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Seo/*`
- `resources/views/livewire/admin/seo/*`
- `tests/Feature/Seo/*`

**Implementation notes**  
- display SEO score in post and SEO views where useful
- display schema output read-only
- keep presentation operational rather than raw or overly technical

**Acceptance criteria**  
- [ ] score data is surfaced meaningfully
- [ ] schema data can be inspected
- [ ] no unsupported “sitewide SEO issues” endpoint is invented

**Validation**  
- SEO screen tests
- API client tests with HTTP fakes

**Risks / assumptions**  
- low-score content index may remain `TBC` until a list endpoint exists

### Phase 9 — Knowledge Base

#### Task 9.1 — Implement knowledge base index and editor flows

**Goal**  
Build knowledge base list, create, edit, and delete flows.

**Context**  
Knowledge base entries support editorial quality and future AI-assisted workflows, but the admin implementation should stay within the current service contract.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `.agent/skills/forms-validation.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/KnowledgeBase/*`
- `resources/views/livewire/admin/knowledge-base/*`
- `app/Services/WideWebBlogApi/Clients/KnowledgeBaseClient.php`
- `tests/Feature/KnowledgeBase/*`

**Implementation notes**  
- support title, slug, type, status, summary, markdown content, source URL, metadata
- keep markdown editing practical rather than overbuilt
- keep list and editor patterns consistent with the rest of the admin

**Acceptance criteria**  
- [ ] knowledge base CRUD works against documented endpoints
- [ ] markdown content is preserved correctly
- [ ] validation and status handling are clear

**Validation**  
- knowledge base feature tests
- knowledge base client tests with HTTP fakes

**Risks / assumptions**  
- categories field for knowledge base entries remains `TBC`

#### Task 9.2 — Implement knowledge base linking actions

**Goal**  
Support linking knowledge base entries to posts and topics where supported.

**Context**  
The service exposes link-to-post and link-to-topic endpoints.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/skills/api-client-integration.md`
- `.agent/skills/forms-validation.md`
- `docs/API_INTEGRATION.md`

**Files likely involved**  
- `app/Livewire/Admin/KnowledgeBase/*`
- `app/Services/WideWebBlogApi/Clients/KnowledgeBaseClient.php`
- `tests/Feature/KnowledgeBase/*`

**Implementation notes**  
- implement link-to-post action
- implement link-to-topic action only if the relevant IDs are available in admin workflows
- keep UI explicit about what linking does

**Acceptance criteria**  
- [ ] post linking works
- [ ] topic linking is implemented only within confirmed service-backed data flows or marked `TBC`
- [ ] linking UI does not guess missing domain behavior

**Validation**  
- targeted action tests
- client tests with HTTP fakes

**Risks / assumptions**  
- topic sourcing inside the admin remains partially `TBC`

### Phase 10 — RSS, Sitemap, Schema, and Read-only SEO Utilities

#### Task 10.1 — Implement operational feed and sitemap screens

**Goal**  
Expose RSS and sitemap data in simple read-only admin utilities.

**Context**  
The service supports `/admin/feeds/rss` and `/admin/seo/sitemap`.

**Relevant docs / skills**  
- `.agent/API-CONTRACT.md`
- `.agent/skills/seo-admin.md`
- `.agent/skills/laravel-livewire.md`
- `docs/API_INTEGRATION.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/Seo/*`
- `resources/views/livewire/admin/seo/*`
- `app/Services/WideWebBlogApi/Clients/SeoClient.php`
- `tests/Feature/Seo/*`

**Implementation notes**  
- create read-only screens or tabs
- render feed and sitemap data clearly
- keep these utilities lightweight and operational

**Acceptance criteria**  
- [ ] RSS data can be viewed
- [ ] sitemap data can be viewed
- [ ] screens stay read-only and accurate to the service response

**Validation**  
- focused feature tests
- API client tests with HTTP fakes

**Risks / assumptions**  
- export/download behavior is `TBC` unless explicitly required

### Phase 11 — Topic Queue and AI Jobs Placeholder Screens

#### Task 11.1 — Add placeholder screens for unsupported future modules

**Goal**  
Reserve navigational and UX space for Topic Queue and AI Jobs without inventing backend support.

**Context**  
The UX spec includes these areas, but the current service API phase does not expose matching admin endpoints.

**Relevant docs / skills**  
- `.agent/PROJECT-CONTEXT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/shadcn-inspired-ui.md`
- `docs/PROJECT_SCOPE.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `app/Livewire/Admin/TopicQueue/*`
- `app/Livewire/Admin/AiJobs/*`
- `resources/views/livewire/admin/*`
- `routes/web.php`

**Implementation notes**  
- create placeholder screens or disabled navigation states
- make it explicit that backend support is pending
- do not invent fake data or fake job actions

**Acceptance criteria**  
- [ ] placeholder states exist if these sections are exposed in navigation
- [ ] unsupported workflows are clearly labeled
- [ ] no undocumented endpoints are called

**Validation**  
- manual screen verification
- `TBC until commands are confirmed`

**Risks / assumptions**  
- keeping these entirely hidden may also be acceptable depending on product preference: `TBC`

### Phase 12 — Settings, Polish, and Final QA

#### Task 12.1 — Implement settings placeholder and operational config views

**Goal**  
Build the settings surface without fabricating unsupported service settings.

**Context**  
The UX spec includes general, publishing, storage, AI, and integrations tabs, but the current service contract does not yet define broad settings endpoints.

**Relevant docs / skills**  
- `.agent/PROJECT-CONTEXT.md`
- `.agent/UI-UX-RULES.md`
- `.agent/skills/laravel-livewire.md`
- `docs/UI_UX_GUIDELINES.md`
- `docs/PROJECT_SCOPE.md`

**Files likely involved**  
- `app/Livewire/Admin/Settings/*`
- `resources/views/livewire/admin/settings/*`

**Implementation notes**  
- create a placeholder or read-only settings surface
- show integration summaries only when backed by real config or service data
- do not expose sensitive secrets directly unless explicitly supported later

**Acceptance criteria**  
- [ ] settings screen exists in an honest, non-fake form
- [ ] unsupported configuration areas are clearly marked
- [ ] screen matches the overall admin UX

**Validation**  
- manual verification
- `TBC until commands are confirmed`

**Risks / assumptions**  
- actual writable settings endpoints remain `TBC`

#### Task 12.2 — Final UX hardening and QA sweep

**Goal**  
Polish the implemented admin and close the most important UX, validation, and test gaps.

**Context**  
This phase is for tightening the implemented MVP, not for expanding scope.

**Relevant docs / skills**  
- `.agent/UI-UX-RULES.md`
- `.agent/TESTING.md`
- `.agent/skills/testing.md`
- `.agent/skills/forms-validation.md`
- `docs/TESTING.md`
- `docs/UI_UX_GUIDELINES.md`

**Files likely involved**  
- `tests/*`
- `resources/views/*`
- `app/Livewire/Admin/*`
- `TBC`

**Implementation notes**  
- improve empty, loading, and error states
- standardize destructive confirmations
- close important auth/session-expiry gaps
- verify responsive behavior
- add missing focused regression tests

**Acceptance criteria**  
- [ ] MVP screens meet the documented UX baseline
- [ ] important auth, validation, and destructive flows are covered
- [ ] known gaps are documented explicitly

**Validation**  
- targeted feature and integration tests
- manual QA against MVP workflows
- `TBC until commands are confirmed`

**Risks / assumptions**  
- broader design polish beyond MVP remains a separate effort

## Suggested Build Order

1. Phase 0
2. Phase 1
3. Phase 2
4. Phase 3
5. Phase 4
6. Phase 5
7. Phase 6
8. Phase 7
9. Phase 8
10. Phase 9
11. Phase 10
12. Phase 11
13. Phase 12

Practical dependency order inside the MVP:

1. bootstrap and config
2. API client foundation
3. auth and layouts
4. shared UI primitives
5. shared management primitives
6. navigation and dashboard
7. categories and tags
8. media
9. templates
10. posts
11. publishing and SEO
12. knowledge base
13. read-only utilities and placeholders
14. settings and QA hardening

## Cross-cutting Acceptance Criteria

- every implementation task starts from `.agent/INDEX.md` and updates `.agent/tasks/current-task.md`
- no task invents endpoints or payload fields not present in the OpenAPI contract
- Livewire owns stateful screen behavior
- Blade components own reusable visual primitives
- service API integration is centralized behind client classes
- `401`, `403`, `404`, and `422` handling is consistent
- destructive actions are explicitly confirmed
- empty, loading, and error states are present on management screens
- tests stay narrow and relevant to the changed behavior
- unsupported future modules are labeled honestly rather than guessed

## Validation Strategy

- prefer focused Livewire feature tests for changed screens
- prefer HTTP fake integration tests for API clients
- validate auth/session expiration behavior whenever auth-sensitive code changes
- validate `422` mapping for service-backed forms
- validate multipart request shape for media uploads
- validate publish/schedule/unpublish flows explicitly
- use manual verification for layout, responsive behavior, empty states, and visual consistency where automated coverage is not enough
- use `TBC until commands are confirmed` in task execution notes if the underlying Laravel app or scripts are not yet present

## Handover Rules

- update `.agent/tasks/current-task.md` before, during, and after task work
- record the exact docs and skills loaded
- record changed files, validation performed, and remaining gaps
- update `.agent/AGENT-HANDOVER.md` when stopping with incomplete or risky work
- update `.agent/MEMORY.md` only if a new fact is stable and likely to matter across future tasks
- do not treat this file as a substitute for the OpenAPI contract or the project docs
