# UI Tasks

Source: code-review audit of the current Admin Blade and Livewire screens on 2026-06-18.

Scope: this file captures UI/UX inconsistency and polish tasks for the next Task 12.2 implementation pass. It is intentionally focused on layout, visual consistency, table behavior, button patterns, copy consistency, and component-system alignment.

## Priority 1

### UI-001 Standardize page header composition across all screens

- Problem:
  Most screens wrap [`x-admin.page-header`](/Users/amitsharma/Herd/widewebblog/admin/resources/views/components/admin/page-header.blade.php) inside an extra `flex` container, while some pages also place action controls outside the component slot. This creates inconsistent alignment and spacing between title blocks and right-side actions.
- Evidence:
  - [resources/views/livewire/admin/topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
  - [resources/views/livewire/admin/pages/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/pages/index.blade.php)
  - [resources/views/livewire/admin/posts/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/posts/index.blade.php)
  - [resources/views/livewire/admin/password/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/password/index.blade.php)
- Task:
  Refactor list/detail page headers so the shared page-header component owns title, description, eyebrow, and right-side actions consistently. Remove redundant outer layout wrappers unless they are genuinely required.

### UI-002 Standardize management-table row actions

- Problem:
  Row actions currently use at least four patterns:
  - large colored icon-only ghost buttons
  - outline text buttons
  - ghost text buttons
  - single-action text buttons
  This makes tables feel like different products.
- Evidence:
  - icon-only actions in [categories/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/categories/index.blade.php), [tags/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/tags/index.blade.php), [media/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/media/index.blade.php)
  - text actions in [pages/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/pages/index.blade.php), [knowledge-base/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/knowledge-base/index.blade.php)
  - single review/open actions in [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php), [content-briefs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/content-briefs/index.blade.php), [ai-jobs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-jobs/index.blade.php)
  - shared component mostly unused: [resources/views/components/admin/row-actions.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/components/admin/row-actions.blade.php)
- Task:
  Define one standard action pattern for dense tables:
  - either compact text buttons
  - or a shared dropdown-based row action menu
  Then migrate all table screens to that pattern.

### UI-003 Reduce oversized icon-button density in tables

- Problem:
  Icon-only table actions in categories, tags, and media use `h-12 w-12` buttons inside dense row layouts. They dominate the row visually and consume too much horizontal space compared with `size="sm"` text actions elsewhere.
- Evidence:
  - [categories/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/categories/index.blade.php)
  - [tags/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/tags/index.blade.php)
  - [media/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/media/index.blade.php)
  - base button sizes in [resources/views/components/ui/button.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/components/ui/button.blade.php)
- Task:
  Introduce a compact action-button size or standard table-action variant and reduce visual weight, padding, and color treatment for dense list actions.

### UI-004 Standardize pagination UI and reuse the shared pagination component

- Problem:
  Pagination is duplicated in multiple screens with different wrappers, borders, spacing, and text treatment. The shared pagination component exists but is effectively bypassed.
- Evidence:
  - duplicated manual pagination in [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php), [ai-jobs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-jobs/index.blade.php), [content-briefs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/content-briefs/index.blade.php), [ai-prompts/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-prompts/index.blade.php)
  - shared component: [resources/views/components/ui/pagination.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/components/ui/pagination.blade.php)
- Task:
  Consolidate all paginated list screens on a single pagination presentation and component API, even when the underlying paginator data is custom rather than a Laravel paginator object.

### UI-005 Normalize sortable table-header behavior

- Problem:
  Sortable headers are hand-built in every table with repeated button markup, repeated arrow logic, and slightly different interaction patterns. This increases drift risk and makes future polish harder.
- Evidence:
  - [posts/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/posts/index.blade.php)
  - [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
  - [content-briefs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/content-briefs/index.blade.php)
  - [pages/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/pages/index.blade.php)
  - current table heading base: [resources/views/components/ui/table-heading.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/components/ui/table-heading.blade.php)
- Task:
  Extend `x-ui.table-heading` to support Livewire-sort triggers and icon rendering so sortable headers do not have to be rebuilt inline on every page.

### UI-006 Normalize dashboard/stat-card visuals across operational list screens

- Problem:
  Stats cards are repeated with slightly different padding, typography, and layout choices. Some screens use custom cards, while the shared stat card component is underused.
- Evidence:
  - [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
  - [ai-jobs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-jobs/index.blade.php)
  - [content-briefs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/content-briefs/index.blade.php)
  - [ai-prompts/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-prompts/index.blade.php)
  - [posts/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/posts/index.blade.php)
  - shared component: [resources/views/components/admin/stat-card.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/components/admin/stat-card.blade.php)
- Task:
  Define one stat-card system for small operational summary cards:
  - standard padding
  - value scale
  - label tracking
  - optional badge/icon slot
  Then migrate dashboards and list-header summary strips to it.

## Priority 2

### UI-007 Standardize filter-toolbar structure and count presentation

- Problem:
  Filter bars use the shared component, but slot usage is inconsistent. Some pages treat filters as search, some place structured filters under `search`, some use a plain rounded panel instead of the shared filter bar.
- Evidence:
  - consistent pattern in [pages/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/pages/index.blade.php)
  - inconsistent composition in [ai-jobs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-jobs/index.blade.php)
  - non-shared toolbar in [media/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/media/index.blade.php)
  - shared component: [resources/views/components/admin/filter-bar.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/components/admin/filter-bar.blade.php)
- Task:
  Define a standard toolbar recipe:
  - left: search
  - middle: filters
  - right: result count and optional secondary actions
  Then align all management screens to it.

### UI-008 Standardize destructive-action confirmation components

- Problem:
  There are two competing modal patterns for confirmations:
  - `x-ui.dialog`
  - `x-admin.confirm-dialog`
  This causes inconsistency in destructive confirmations, copy layout, and footer actions.
- Evidence:
  - `x-ui.dialog` usage in [categories/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/categories/index.blade.php), [pages/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/pages/index.blade.php), [knowledge-base/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/knowledge-base/index.blade.php)
  - `x-admin.confirm-dialog` usage in [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php), [topic-queue/show.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/show.blade.php)
- Task:
  Choose one confirmation-dialog API for destructive and workflow-confirm actions and standardize title, description, body padding, and footer actions.

### UI-009 Standardize action-label vocabulary

- Problem:
  Similar actions use different labels: `Open`, `Inspect`, `Review`, `Edit`, `Open Post Editor`, `Back to Posts`, `Back to Queue`, `Create AI Job`, `Generate Brief`. Some are justified, but many are arbitrary and weaken scanability.
- Evidence:
  - [ai-jobs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-jobs/index.blade.php)
  - [content-briefs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/content-briefs/index.blade.php)
  - [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
  - [ai-prompts/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-prompts/index.blade.php)
  - [seo/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/seo/index.blade.php)
- Task:
  Establish product-language rules for:
  - list-row action labels
  - back-navigation labels
  - create/trigger workflow verbs
  - inspect vs edit vs review semantics

### UI-010 Normalize placeholder and fallback copy

- Problem:
  Placeholder language varies widely: `Unknown`, `TBC`, `Not linked`, `No source URL`, `Slug pending`, `Auto-generated`, `Unavailable`, `None`, `Not published`. Some are useful, but the system lacks a rule for when each should be used.
- Evidence:
  - [posts/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/posts/index.blade.php)
  - [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
  - [ai-jobs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-jobs/index.blade.php)
  - [knowledge-base/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/knowledge-base/index.blade.php)
  - [seo/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/seo/index.blade.php)
- Task:
  Define a fallback-copy style guide:
  - missing value
  - not yet generated
  - intentionally unavailable
  - unknown from API
  Then standardize lists and detail screens accordingly.

### UI-011 Standardize informational callout treatment

- Problem:
  Non-error informational notices are styled ad hoc as rounded panels in headers or body sections rather than using a shared info/caution callout component.
- Evidence:
  - [settings/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/settings/index.blade.php)
  - [seo/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/seo/index.blade.php)
  - [knowledge-base/editor.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/knowledge-base/editor.blade.php)
  - [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
- Task:
  Add a reusable informational/caution callout pattern and replace ad hoc bordered text boxes where the purpose is product explanation rather than form structure.

### UI-012 Improve Topic Queue index layout and control alignment

- Problem:
  The Topic Queue index is one of the clearest examples of alignment drift:
  - redundant page-header wrapper
  - CTA alignment feels detached from the title block
  - long filter row widths feel uneven
  - manual pagination block is visually heavier than the table itself
- Evidence:
  - [resources/views/livewire/admin/topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
- Task:
  Do a dedicated layout pass on Topic Queue after the shared header/toolbar/pagination/action systems are standardized.

## Priority 3

### UI-013 Standardize list-to-detail entry patterns

- Problem:
  Some tables lead with `Review`, some with `Inspect`, some with `Open`, some with direct `Edit`. The relationship between list rows and the next screen is not visually or semantically consistent.
- Evidence:
  - [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
  - [content-briefs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/content-briefs/index.blade.php)
  - [ai-jobs/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/ai-jobs/index.blade.php)
  - [pages/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/pages/index.blade.php)
  - [knowledge-base/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/knowledge-base/index.blade.php)
- Task:
  Define rules for whether a list row should route to:
  - review
  - detail/inspection
  - edit
  and make action text match the destination purpose.

### UI-014 Align dashboard with shared admin primitives

- Problem:
  The dashboard uses a more custom visual language than the module screens:
  - custom header instead of shared page header
  - custom cards instead of shared stat card
  - custom list-item action pills and icon links
  This is not inherently wrong, but it weakens the sense of a unified admin system.
- Evidence:
  - [resources/views/livewire/admin/dashboard/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/dashboard/index.blade.php)
- Task:
  Decide whether the dashboard should intentionally remain more editorial/marketing in feel, or whether it should be pulled closer to the shared admin component language.

### UI-015 Add a formal table-density and column-width system

- Problem:
  Tables choose widths ad hoc with `w-[24%]`, `w-[31%]`, `w-[34%]`, `w-[44%]`, `w-[12%]`, and custom combinations per screen. This is manageable now but will drift further as modules expand.
- Evidence:
  - [topic-queue/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/topic-queue/index.blade.php)
  - [posts/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/posts/index.blade.php)
  - [media/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/media/index.blade.php)
  - [tags/index.blade.php](/Users/amitsharma/Herd/widewebblog/admin/resources/views/livewire/admin/tags/index.blade.php)
- Task:
  Define reusable table-density conventions and preferred primary-column widths by screen type:
  - taxonomy tables
  - content tables
  - workflow/job tables
  - asset tables

## Suggested Execution Order

1. `UI-001`
2. `UI-002`
3. `UI-003`
4. `UI-004`
5. `UI-005`
6. `UI-007`
7. `UI-008`
8. `UI-009`
9. `UI-010`
10. `UI-012`
11. `UI-006`
12. `UI-011`
13. `UI-013`
14. `UI-014`
15. `UI-015`

## Notes

- This audit is source-review-driven. A follow-up browser QA pass should still verify:
  - responsive breakpoints
  - hover/focus states
  - sticky headers or scroll behaviors
  - button wrapping on smaller widths
  - visual rhythm in populated real-data tables
