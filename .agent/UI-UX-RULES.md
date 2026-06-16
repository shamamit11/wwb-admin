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
