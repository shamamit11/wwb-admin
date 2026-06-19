# Admin UI/UX Rules

## Layout

- use a left sidebar on desktop
- use a compact top bar with search, user menu, and status indicators
- use responsive stacked or collapsible navigation on smaller screens

## Table Behavior

- search in the header or toolbar
- filters adjacent to search
- sortable headers where relevant
- row actions on the right
- bulk actions only when clearly useful

## Action Label Vocabulary

- use `Review` for approval or editorial workflow screens
- use `Edit` for mutable editors and settings screens
- use `Details` for read-only drilldown or inspection screens
- use `Back to {resource name}` for secondary return navigation
- use user-facing workflow verbs like `Run Topic Discovery`, `Generate Brief`, and `Generate Draft` instead of implementation labels like `Create AI Job`

## List-To-Detail Entry Patterns

- use `Review` when the destination is primarily an editorial approval or workflow-advancement screen
- use `Details` when the destination is primarily read-only inspection, monitoring, or payload visibility
- use `Edit` when the destination is primarily a mutable content or settings editor
- choose the label based on the dominant task on the destination screen, even if the screen contains secondary actions
- keep destructive or side-effect actions separate from the primary list-to-detail entry action

## Fallback Copy

- use `Unknown` when the API omits a value that would normally be expected
- use `Not set` for optional editable metadata that has not been filled in yet
- use `{field} pending` or `Pending` for generated or derived values expected later
- use `None` for intentionally empty relationships, links, or optional resources
- use explicit lifecycle phrases like `Not published`, `Not approved`, and `Not started` when the state itself is the message
- use `Unavailable` only when a value exists conceptually but is not exposed in the current surface

## Validation Behavior

- inline field validation for obvious errors where appropriate
- full validation on submit
- global error banner for submit or transport failures

## Confirmation And Safety

- require confirmation for destructive actions
- require confirmation for publish and unpublish flows
- require confirmation for destructive media replacement or delete behavior

## Empty, Loading, And Error States

- every list screen needs an explicit empty state
- use loading skeletons or obvious loading states
- session-expired and unauthorized states must be explicit
- never expose raw exceptions in the UI

## MVP Screen Summary

- login: centered, minimal, professional
- dashboard: action-oriented operational overview
- categories and tags: table-first CRUD management
- posts: index plus editor with sticky side metadata/status panel
- media: upload plus searchable asset management
- templates: list plus structured block configuration
- knowledge base: searchable list plus markdown editing
- SEO: metadata editing, score display, schema and feed/sitemap visibility
- settings: placeholder or scoped operational settings only

Agents should check `docs/UI_UX_GUIDELINES.md` before UI-heavy tasks or when screen-specific behavior matters.
