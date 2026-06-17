# Task: Category And Tag Header Alignment

Status: Completed

## Goal

Align category and tag primary actions with the media page by moving `Create Category` and `Create Tag` beside the page header, and remove the duplicate heading now shown on both pages.

## Background

The media page now uses a cleaner page-level primary action placement. Categories and tags should follow the same pattern so the admin uses one consistent management-screen hierarchy.

## Required Context

- `AGENTS.md`
- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/UI-UX-RULES.md`

## Files To Inspect

- `resources/views/livewire/admin/categories/index.blade.php`
- `resources/views/livewire/admin/tags/index.blade.php`
- `app/Livewire/Admin/Categories/Index.php`
- `app/Livewire/Admin/Tags/Index.php`

## Files To Change

- `app/Livewire/Admin/Categories/Index.php`
- `app/Livewire/Admin/Tags/Index.php`

## Implementation Steps

- Move category and tag create actions out of the filter bar and into the page header row.
- Remove layout-provided `pageTitle` and `pageDescription` so the in-view header renders only once.
- Keep the existing drawer actions and all existing behavior unchanged.

## Acceptance Criteria

- category and tag primary actions render beside their page headers
- category and tag pages render a single visible heading
- filter bars remain focused on search, filters, and counts only

## Validation Commands

- `php -l app/Livewire/Admin/Categories/Index.php`
- `php -l app/Livewire/Admin/Tags/Index.php`

## Risks

- Presentation-only refinement; behavior should remain unchanged.

## Completion Notes

- Category and tag pages now use the same page-level header action placement as media.
- Duplicate headings were removed by keeping the inline Blade header and removing layout-supplied `pageTitle` and `pageDescription` from both Livewire render methods.
