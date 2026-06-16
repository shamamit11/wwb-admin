# Admin Project Context

## Product

Wide Web Blog is a publishing system with a dedicated public frontend, a service API, and this admin panel for internal editorial work.

## Purpose Of The Admin Panel

The admin panel is the internal application for authenticated publishing operations. It gives editors and admins a structured interface for content management, media handling, templates, SEO operations, and future workflow tooling.

## System Relationship

- `admin`: internal UI/client application
- `service`: source of truth for business logic, persistence, publishing state, media records, and API contracts
- `fe`: public website or presentation layer

The admin should not bypass the service for content persistence or domain rules.

## Editorial Workflow

The admin is designed for a publishing-focused workflow:

- manage structure through categories and tags
- create and edit posts
- attach media and templates
- manage knowledge base context
- review and improve SEO metadata
- publish, schedule, and unpublish through service-backed actions

## MVP Modules

- login/authenticated admin session
- dashboard
- categories
- tags
- posts
- media library
- templates
- knowledge base
- SEO management
- settings placeholder

## Later Modules

- topic queue
- AI jobs
- advanced settings
- workflow/autosave improvements
- advanced SEO defaults

## User Roles

At a high level:

- admins authenticate into the panel
- editorial users manage publishing operations
- authorization details remain service-owned and should be confirmed through `/admin/me`

## UX Character

- publishing-focused, not generic CMS-heavy
- low visual noise
- fast navigation between content operations
- table-first for management screens
- editor-first for creation screens
- strong error feedback
- safe destructive actions
