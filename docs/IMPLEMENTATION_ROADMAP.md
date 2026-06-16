# Wide Web Blog Admin Panel Implementation Roadmap

## Phase 0: Project Bootstrap

- create the Laravel 13 application shell if not already present
- install and configure Livewire and Tailwind CSS
- establish base layouts, routing, session config, and environment config
- add `widewebblog.php` API configuration
- implement API manager, auth client, and shared exception handling

## Phase 1: Foundation UI and Auth

- build guest and admin layouts
- build first UI primitive set
- implement login/logout flow against service auth endpoints
- implement stored bearer-token session strategy
- add admin auth middleware and current-user hydration

Deliverable:

- a protected dashboard shell with working sign-in/sign-out

## Phase 2: Shared Resource Patterns

- build page header, filter toolbar, table, pagination, dialog, drawer, badge, and toast patterns
- establish standard index/create/edit conventions for Livewire modules
- add generic API error rendering and validation mapping support

Deliverable:

- repeatable scaffolding pattern for CRUD modules

## Phase 3: MVP Content Modules

Implement in this order:

1. Categories
2. Tags
3. Media Library
4. Templates
5. Posts
6. Knowledge Base

Rationale:

- categories and tags unblock structured content
- media and templates support post authoring
- posts are the central workflow and benefit from those dependencies already existing
- knowledge base follows once editorial reference patterns are stable

## Phase 4: SEO and Operational Views

- per-entity SEO metadata editing
- SEO score display
- schema inspection
- sitemap inspection
- RSS inspection
- dashboard summary widgets using available service endpoints

Deliverable:

- an operational publishing dashboard with SEO visibility

## Phase 5: Settings and Placeholder Modules

- add settings screen scaffold
- expose read-only or placeholder states for unsupported service-backed features
- add navigation and messaging for Topic Queue and AI Jobs as roadmap modules only

Deliverable:

- complete navigation structure aligned with product design, without faking missing backend features

## Phase 6: Hardening

- expand feature and integration tests
- improve loading and empty states
- standardize authorization fallbacks
- add audit-friendly UX details
- refine responsive behavior

## Deferred Work

- autosave and recovery for post editing
- richer editorial workflow states
- topic queue management
- AI jobs monitoring and retry flows
- advanced settings and integrations
- deeper SEO defaults management

## Implementation Rules During Build

- do not bypass the service API for content persistence
- do not introduce React/Next.js or import React shadcn packages
- keep component naming and Livewire patterns consistent from the start
- treat unsupported endpoints as roadmap items, not hidden assumptions
