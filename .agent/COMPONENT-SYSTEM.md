# Admin Component System Rules

## Core Rule

Use shadcn/ui as a visual reference only. Build Blade components and Livewire-compatible UI patterns. Do not create React components and do not introduce React/Next.js into this repository.

## Component Locations

- shared UI primitives: `resources/views/components/ui`
- admin-specific composites: `resources/views/components/admin`
- Livewire components should compose these Blade components rather than duplicating styles inline

## Component Requirements

Where relevant, components should support:

- loading states
- disabled states
- error states
- accessible labels and focus behavior

## Expected Component Groups

- Button
- Badge
- Card
- Form inputs
- Dialog
- Alert Dialog
- Sheet
- Dropdown
- Table
- Pagination
- Tabs
- Alert
- Toast
- Sidebar
- Breadcrumb
- Skeleton
- Empty State
- Page Header
- Data Table
- Filter Bar
- Status Badge
- SEO Score Badge
- Media Picker
- Editor Shell

## Implementation Notes

- keep class and variant conventions consistent
- centralize repeated styling in components, not page templates
- prefer a small durable component set over many one-off abstractions

Read `docs/COMPONENT_SYSTEM.md` before component-system or design-heavy work.
