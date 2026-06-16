# Admin Architecture Summary

## Stack

- Laravel 13
- Livewire
- Blade
- Tailwind CSS
- shadcn/ui-inspired reusable Blade components

## Core Architecture

The admin is a Laravel monolith for internal UI workflows. It is not the domain backend. The service API remains the source of truth for persistence, validation contracts, and business behavior.

## Key Boundaries

- Livewire page components own screen state, form actions, filters, and user interactions.
- Blade components own reusable visual primitives and admin composites.
- A dedicated API client layer owns service communication.
- DTO or lightweight data objects should be used where payload shaping or typed state helps.
- Token and current-user handling should stay in session/auth support code, not inside page components.

## Layout Approach

- guest layout for login
- admin shell with left sidebar and compact top bar
- editor-oriented layouts for larger create/edit flows where needed

## Error And Validation Handling

- map API `422` errors into Livewire validation messages
- handle `401` centrally by clearing session state and redirecting to login
- treat `403` as an authorization failure
- render user-safe global error states for transport or server failures

## Media Upload Flow

- Livewire manages form/upload state
- the API client layer builds multipart requests for `/admin/media` and `/admin/media/batch`
- UI should expose loading or progress states and safe delete affordances

## Testing Approach

- Livewire tests for screen behavior
- HTTP fake tests for API clients
- narrow regression tests for changed modules

Read `docs/ARCHITECTURE.md` for the fuller architecture map before making structural changes.
