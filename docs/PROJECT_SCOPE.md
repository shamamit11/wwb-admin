# Wide Web Blog Admin Panel Project Scope

## Purpose

The Wide Web Blog Admin Panel is the internal editorial application for operating the publishing system. It is a Laravel 13 + Livewire admin client that consumes the Wide Web Blog Service API and provides a fast, low-noise interface for editors and administrators.

This application is not the system of record for content rules, persistence, or publishing workflows. The service API owns business logic, persistence, validation contracts, SEO computation, and publishing state transitions. The admin panel is responsible for authenticated workflows, data presentation, form interaction, and operational tooling.

## Relationship Between Systems

- Admin panel: internal UI/client for authenticated editorial and operational work.
- Service application: source of truth for domain logic, content persistence, media records, SEO metadata, publishing state, and API contracts.
- Frontend website: public presentation layer that consumes public-facing data from the service.

The admin must never bypass service business rules by writing directly to service data stores. Any exception to this rule should be documented explicitly in a future architecture update.

## What The Admin Panel Manages

- Admin authentication and session lifecycle
- Editorial dashboard and operational overview
- Categories
- Posts and publishing actions
- Media library
- Templates
- Knowledge base entries
- SEO metadata and SEO quality review flows
- Tags
- Site settings placeholders and integration visibility

## MVP Modules

The MVP should include the modules already supported by the current service API and required by the UI/UX specification:

1. Login and authenticated admin session
2. Dashboard
3. Categories
4. Posts
5. Media Library
6. Templates
7. Knowledge Base
8. SEO management
9. Tags
10. Settings placeholder

## MVP Outcomes

The first release should allow an authenticated admin to:

- sign in and maintain an admin session
- view their profile and authorization state
- review a publishing-focused dashboard
- create, edit, publish, schedule, unpublish, and delete posts
- manage categories and tags
- upload and manage media assets
- create and maintain templates, preview them, and seed posts from them
- maintain knowledge base entries and link them to posts
- manage SEO metadata and inspect SEO score/schema/sitemap or RSS-related outputs

## Later-Phase Modules

The UI/UX specification still includes some future-facing areas that are not fully represented in the current Admin contract. Treat only the unsupported items as planned or placeholder modules.

Implemented AI workflow modules:

- Topic Queue
- Content Briefs
- Prompt Templates
- AI Jobs

Still contract-limited:

- Draft Review as an AI-specific filtered module
- Advanced Settings
- Advanced SEO defaults and diagnostics
- Workflow improvements such as autosave, richer editorial review states, and recovery flows

## Non-MVP Boundaries

The following are out of scope for the first implementation unless a newer service phase adds them and product priorities change:

- full workflow orchestration beyond current post status transitions
- direct AI job execution from the admin
- advanced analytics or vanity dashboards
- direct secret management inside the UI
- frontend website rendering concerns
- custom page-builder style editing tools

## Module Boundary Rules

- The admin should mirror the service API contract rather than inventing competing domain models.
- Client-side validation should improve usability, but service validation remains authoritative.
- Admin screens may aggregate or reshape service data for UX purposes, but they should not redefine domain behavior.
- Missing service capabilities should surface as placeholders or deferred roadmap items, not guessed implementations. This still applies to AI-draft provenance gaps.
