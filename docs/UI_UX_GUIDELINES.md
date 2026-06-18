# Wide Web Blog Admin Panel UI/UX Guidelines

This document converts the product UX specification in `/Users/amitsharma/Herd/widewebblog/service/docs/ADMIN_UI_UX.md` into implementation rules for the Laravel + Livewire admin.

## Product Character

- publishing-focused rather than generic CMS-heavy
- low visual noise
- quick movement between operational tasks
- table-first for management screens
- editor-first for creation and editing flows
- explicit content state and safe destructive actions

## Global Layout Rules

- desktop uses a persistent left sidebar
- top bar stays compact and contains search, user menu, and status indicators
- smaller screens use stacked or collapsible navigation
- keep content width controlled on management screens and more expansive on editor screens
- favor whitespace, separators, and typography over heavy card nesting

## Navigation Rules

- primary navigation belongs in the sidebar
- highlight the current section clearly
- group content modules separately from operational modules
- dashboard, posts, categories, media, templates, knowledge base, SEO, tags, and settings should be first-class nav items
- Topic Queue, Content Briefs, Prompt Templates, and AI Jobs are now service-backed Admin modules

## Table Behavior

Management screens should follow one table pattern:

- search field in the header or toolbar
- filters adjacent to search
- clickable sortable column headers
- row actions aligned to the right
- bulk actions only when genuinely useful
- preserve filters and sort in the query string
- empty state should replace the table body, not sit below it

## Form Behavior

- obvious field errors validate on blur where possible
- all forms validate fully on submit
- use a clear primary action and lower-emphasis secondary actions
- destructive actions must be visually distinct
- preserve unsaved input after validation failures
- long forms should be grouped into clear sections with headings
- editor screens should use a sticky side panel for status and secondary metadata when practical

## Validation Behavior

- inline field messages for direct correction
- global error banner for submit failures or transport problems
- API validation messages should remain close to their fields
- generic credential failures on login should not leak sensitive auth details

## Confirmation Rules

Use confirmation dialogs or sheets for:

- deleting records
- publishing posts
- unpublishing posts
- retrying cost-incurring AI actions later
- replacing destructive featured media relationships
- rejecting or marking topics used when topic management exists

## Loading Rules

- use skeletons for page and table loading
- use button-level loading states for mutations
- avoid full-page blocking overlays unless the entire screen is unusable
- uploads should expose progress or obvious activity feedback

## Empty-State Rules

- every index screen must have an explicit empty state
- empty states should explain the absence and point to the next meaningful action
- avoid decorative fluff; keep them editorial and operational

## Error-State Rules

- unauthorized and expired-session states should be explicit
- missing resources should redirect safely or render a compact not-found state
- transport or server errors should expose retry paths where possible
- avoid raw exception output in the UI

## Screen Expectations

### Login

- centered auth card
- brand mark and short product line
- fields: email, password, remember me
- optional password reset link if implemented later
- minimal and professional styling
- invalid credentials should show one generic message

### Dashboard

- summary cards at the top
- recent drafts and recent published posts lists
- dashboard AI widgets should reflect service-backed workflow state and link into Topic Queue, Content Briefs, Prompt Templates, and AI Jobs
- prioritize actionable items over vanity metrics
- primary actions should include create post and review drafts

### Category Management

- list screen with create action
- create/edit may use a drawer or dedicated page; pick one pattern and keep it consistent
- table columns: name, slug, parent, active, sort order, updated at, actions
- form fields: name, slug, description, parent category, active toggle, sort order, SEO title, SEO description
- show related post count later when the service exposes it

Note: the current category API contract covers category CRUD but not category-specific SEO fields inside the category payload. If SEO title/description are needed, route them through the SEO metadata endpoints.

### Post Management

- index screen with search and filters
- editor screen with central block editor and sticky side panel
- table columns: title, status, category, author, SEO score, published at, updated at, actions
- form fields: title, slug, excerpt, category, tags, template, featured image, visibility, scheduled publish date, blocks, SEO fields
- confirmation required for publish, unpublish, and delete
- autosave is a future enhancement, not an MVP assumption

### Media Library

- upload area above or beside the asset list
- searchable grid or list; choose based on implementation speed, but support metadata inspection
- detail drawer for selected media
- table/list columns: thumbnail, filename, source type, mime type, dimensions, usage count, created at, actions
- form fields: file, alt text, caption, source type, source URL, attribution text
- show usage count before delete

### Template Management

- list plus editor flow
- explicit ordered block configuration UI, not drag-heavy page-builder behavior unless justified later
- preview panel should consume the template preview endpoint
- support seed-post flow from template
- table columns: name, type, status, blocks count, updated at, actions

### Knowledge Base

- searchable list
- editing view with metadata and markdown content
- related links panel for posts and topics
- form fields: title, slug, type, status, summary, markdown content, tags or metadata, categories if later supported, source URL

Note: the current API supports metadata arrays and post/topic linking, but not a dedicated categories field for knowledge base entries.

### SEO Settings

- operational, not overly technical
- sitewide defaults tab may begin as a placeholder if service-wide defaults are not yet implemented
- should support reviewing low-score content and inspecting missing metadata once list endpoints exist
- current service contract already supports per-entity metadata, score, schema, sitemap, and RSS inspection

### Topic Queue

- list and detail review surfaces for service-backed topic suggestions
- list/detail/action layout
- approval flow should be quick and lightweight
- topic discovery should create a Service AI job and route the user to the related job detail where possible
- brief generation should remain available only from approved topics

### AI Jobs

- service-backed monitoring screen
- table plus detail page pattern
- clear distinction between completed, failed, and review-needed work
- job detail should expose payload visibility, generation-step inspection, retry affordances, and refresh behavior

### Settings

- use tabs for general, publishing, storage, AI, and integrations
- MVP can be a placeholder or read-only summary where service-backed settings are absent
- do not expose sensitive secrets directly in forms unless product and backend requirements explicitly support it
