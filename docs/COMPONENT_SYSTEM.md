# Wide Web Blog Admin Panel Component System

## Design Reference

Use [shadcn/ui components](https://ui.shadcn.com/docs/components) as the visual and interaction reference, not as React code to install directly. The admin panel must translate those patterns into reusable Blade and Livewire components. The current shadcn catalog includes the primitives most relevant to this project, such as `Button`, `Card`, `Input`, `Field`, `Table`, `Dialog`, `Drawer`, `Dropdown Menu`, `Pagination`, `Tabs`, `Sidebar`, `Skeleton`, `Badge`, `Select`, `Textarea`, `Toast`, and `Tooltip`. Source: [shadcn/ui components](https://ui.shadcn.com/docs/components).

## Goal

Create a small, durable Blade-native component system that gives the admin a consistent publishing-dashboard feel without importing React assumptions.

## Component Layers

### UI Primitives

These are low-level Blade components under `resources/views/components/ui` and `app/View/Components/Ui`:

- button
- icon button
- input
- textarea
- select
- checkbox
- switch
- badge
- label
- field wrapper
- card
- separator
- tabs
- dialog
- drawer or sheet
- dropdown menu
- table
- pagination
- empty state
- skeleton
- toast or flash presenter

### Admin Composites

These are higher-level admin-specific components:

- app shell
- sidebar nav section
- page header
- filter toolbar
- search input with icon
- stats card
- resource table
- row action menu
- confirm action dialog
- metadata side panel
- media picker
- SEO score badge
- status badge

### Livewire Interaction Components

Use Livewire-backed components only when stateful behavior is meaningful:

- login form
- post editor
- media upload form
- media picker modal
- template builder
- SEO editor panel
- delete confirmation flow

Do not turn every styled element into a Livewire component. Prefer Blade for static primitives and Livewire for stateful workflows.

## Naming Conventions

Blade UI components:

- `<x-ui.button>`
- `<x-ui.input>`
- `<x-ui.card>`
- `<x-ui.table>`

Admin composites:

- `<x-admin.page-header>`
- `<x-admin.filter-toolbar>`
- `<x-admin.status-badge>`

Livewire pages:

- `App\Livewire\Admin\Posts\Index`
- `App\Livewire\Admin\Posts\Edit`

## Styling Rules

- centralize design tokens through Tailwind theme extensions and CSS variables
- use a restrained neutral-heavy palette suited for publishing operations
- rely on typography, spacing, subtle borders, and state color cues rather than decorative noise
- use consistent radius, focus rings, and disabled states across all controls
- avoid one-off utility stacks repeated across pages when a component variant is more appropriate

## Variant Strategy

Each primitive should define stable variants early:

### Buttons

- primary
- secondary
- ghost
- destructive
- outline

### Badges

- default
- success
- warning
- danger
- muted

### Inputs and Fields

- default
- invalid
- disabled
- compact if genuinely needed

## Table System

The table component system should support:

- sortable headers
- toolbar slot for search and filters
- empty state slot
- loading skeleton rows
- trailing actions column
- optional selectable rows, but only for modules that need bulk actions

## Dialog and Drawer System

Use a dialog for short confirmations and focused forms. Use a drawer or sheet for edit flows that should preserve list context. Do not mix both patterns arbitrarily within the same module.

Recommended defaults:

- dialog for delete/publish/unpublish confirmation
- drawer for metadata editing or quick-create flows
- full page for complex editors such as posts and templates

## Status and Domain Presentation

Standardize status presentation to avoid inconsistent labels or colors across screens.

Expected status surfaces include:

- post status
- template status
- knowledge base status
- active/inactive taxonomy state
- upload/media state

## Accessibility Expectations

- visible focus state on all interactive controls
- label every form field
- ensure icon-only buttons have accessible labels
- use semantic table markup for tabular data
- dialogs must trap focus and restore it correctly

## First Component Set To Build

1. `button`
2. `input`
3. `textarea`
4. `select`
5. `field`
6. `card`
7. `badge`
8. `table`
9. `pagination`
10. `dialog`
11. `drawer`
12. `sidebar`
13. `tabs`
14. `empty-state`
15. `skeleton`
16. `toast`

This is enough to support every MVP screen before adding niche variants.
