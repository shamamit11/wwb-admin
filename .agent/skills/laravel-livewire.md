# Skill: Laravel Livewire

## When To Use

Use this skill for Laravel 13 + Livewire page, form, dashboard, and editor work.

## Files To Inspect

- `.agent/ARCHITECTURE.md`
- `.agent/FOLDER-STRUCTURE.md`
- `docs/ARCHITECTURE.md`
- `docs/FOLDER_STRUCTURE.md`
- `docs/UI_UX_GUIDELINES.md` when screen behavior matters

## Implementation Rules

- keep page-level state in Livewire components
- use query-string state for search, filters, sort, and pagination where relevant
- keep components focused; split out child components only at real interaction boundaries
- use Blade components for reusable visual primitives
- keep API calls out of views and minimize them in page components by using the API client layer

## Common Mistakes To Avoid

- bloated Livewire components that mix layout, API transport, and reusable UI logic
- skipping query-string state for management screens
- duplicating UI markup instead of composing shared components

## Validation Checklist

- page state updates correctly
- loading and error states are handled
- filters and sort persist where expected
- validation and API errors surface correctly
